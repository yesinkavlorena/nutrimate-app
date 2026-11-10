<?php

namespace App\Console\Commands;

use App\Models\KebutuhanEnergi;
use App\Models\Makanan;
use App\Models\PreferensiMakanan;
use Illuminate\Console\Command;

/**
 * Evaluasi sistem rekomendasi NutriMate dengan Leave-One-Out Cross Validation.
 *
 * Untuk setiap pengguna dan setiap makanan preferensinya:
 *  1. Makanan tersebut disembunyikan (makanan uji).
 *  2. Profil preferensi dibentuk dari makanan preferensi yang tersisa.
 *  3. Alur rekomendasi dijalankan (filter waktu makan, filter kalori ±20%,
 *     cosine similarity, macro score, hybrid score) pada waktu makan
 *     makanan uji, dengan makanan latih dikeluarkan dari kandidat.
 *  4. Dicatat posisi makanan uji pada daftar hasil.
 *
 * Command ini hanya MEMBACA data, tidak mengubah database.
 *
 * Penggunaan:
 *   php artisan evaluasi:loocv
 *   php artisan evaluasi:loocv --k=5 --min-pref=3
 *   php artisan evaluasi:loocv --hitung-gugur --csv=loocv_detail.csv
 */
class EvaluasiLooCv extends Command
{
    protected $signature = 'evaluasi:loocv
                            {--k=5 : Jumlah rekomendasi teratas (Top-K)}
                            {--min-pref=3 : Minimal jumlah preferensi per pengguna}
                            {--hitung-gugur : Kasus yang gugur filter kalori dihitung sebagai gagal (nilai 0)}
                            {--csv= : Simpan detail per kasus ke storage/app/<nama-file>.csv}';

    protected $description = 'Evaluasi sistem rekomendasi dengan Leave-One-Out Cross Validation (LOO-CV)';

    /**
     * Harus sama dengan $fiturMakanan pada RekomendasiController.
     */
    private array $fiturMakanan = [
        'berkuah',
        'digoreng',
        'dibakar',
        'dipanggang',
        'direbus',
        'dikukus',
        'ditumis',
        'pedas',
        'manis',
        'gurih',
        'asam',
        'bersantan',
        'berbumbu_rempah',
        'bertepung',
        'berbahan_ayam',
        'berbahan_sapi',
        'berbahan_kambing',
        'berbahan_ikan',
        'berbahan_seafood',
        'berbahan_bebek',
        'berbahan_telur',
        'berbahan_sayur',
        'berbahan_tahu_tempe',
        'berbahan_dasar_tepung',
        'berbumbu_kacang',
        'berbumbu_balado',
        'berbumbu_bali',
    ];

    /**
     * Waktu makan: nilai di database, kolom target kalori, dan proporsi energi.
     * Harus sama dengan RekomendasiController (Langkah 7 dan 11).
     */
    private array $waktuMakan = [
        'sarapan' => [
            'db' => 'sarapan',
            'kolom' => 'target_sarapan',
            'proporsi' => 0.167,
        ],
        'makan_siang' => [
            'db' => 'makan siang',
            'kolom' => 'target_makan_siang',
            'proporsi' => 0.305,
        ],
        'makan_malam' => [
            'db' => 'makan malam',
            'kolom' => 'target_makan_malam',
            'proporsi' => 0.338,
        ],
        'camilan' => [
            'db' => 'cemilan',
            'kolom' => 'target_snack',
            'proporsi' => 0.190,
        ],
    ];

    /** Harus sama dengan RekomendasiController (Langkah 10 dan 18). */
    private float $toleransi = 0.20;
    private float $weightCbf = 0.5;
    private float $weightMacro = 0.5;

    private const METODE = [
        'hybrid' => 'Hybrid (CBF + Macro)',
        'cbf' => 'CBF saja (cosine similarity)',
        'macro' => 'Macro Score saja',
        'random' => 'Acak (pembanding)',
    ];

    public function handle(): int
    {
        $k = max(1, (int) $this->option('k'));
        $minPref = max(2, (int) $this->option('min-pref'));
        $hitungGugur = (bool) $this->option('hitung-gugur');
        $csvNama = $this->option('csv');

        $semuaMakanan = Makanan::all();

        $prefPerUser = PreferensiMakanan::with('makanan')
            ->get()
            ->groupBy('user_id');

        if ($prefPerUser->isEmpty()) {
            $this->error('Tidak ada data preferensi makanan.');
            return self::FAILURE;
        }

        // $hasil[user_id][metode][] = ['hit' => , 'rr' => , 'ndcg' => ]
        $hasil = [];
        $detail = [];

        $penggunaDilewati = 0;
        $totalKasus = 0;
        $gugurFilter = 0;
        $dievaluasi = 0;

        foreach ($prefPerUser as $userId => $daftarPref) {
            $makananPref = $daftarPref
                ->pluck('makanan')
                ->filter()
                ->unique('id_makanan')
                ->values();

            if ($makananPref->count() < $minPref) {
                $penggunaDilewati++;
                continue;
            }

            $kebutuhan = KebutuhanEnergi::where('user_id', $userId)
                ->latest()
                ->first();

            if (!$kebutuhan) {
                $penggunaDilewati++;
                continue;
            }

            foreach ($makananPref as $uji) {
                $idUji = (string) $uji->id_makanan;

                $latih = $makananPref
                    ->filter(fn ($m) => (string) $m->id_makanan !== $idUji)
                    ->values();

                $idLatih = $latih
                    ->map(fn ($m) => (string) $m->id_makanan)
                    ->all();

                $profil = $this->bentukProfil($latih);

                foreach ($this->waktuMakan as $kodeWaktu => $info) {
                    // Makanan uji hanya diuji pada waktu makan yang tercatat.
                    if (!in_array($info['db'], $this->daftarWaktu($uji), true)) {
                        continue;
                    }

                    $totalKasus++;

                    $targetKalori = (float) $kebutuhan->{$info['kolom']};
                    $batasBawah = $targetKalori * (1 - $this->toleransi);
                    $batasAtas = $targetKalori * (1 + $this->toleransi);

                    // Kandidat: waktu makan sesuai, kalori dalam rentang,
                    // makanan latih dikeluarkan.
                    $kandidat = $semuaMakanan->filter(
                        function ($m) use ($info, $batasBawah, $batasAtas, $idLatih) {
                            $kalori = (float) $m->kalori;

                            return in_array($info['db'], $this->daftarWaktu($m), true)
                                && $kalori >= $batasBawah
                                && $kalori <= $batasAtas
                                && !in_array((string) $m->id_makanan, $idLatih, true);
                        }
                    );

                    $lolosFilter = $kandidat->contains(
                        fn ($m) => (string) $m->id_makanan === $idUji
                    );

                    if (!$lolosFilter) {
                        $gugurFilter++;

                        if ($hitungGugur) {
                            foreach (array_keys(self::METODE) as $metode) {
                                $hasil[$userId][$metode][] = [
                                    'hit' => 0.0,
                                    'rr' => 0.0,
                                    'ndcg' => 0.0,
                                ];
                            }
                        }

                        $detail[] = [
                            $userId,
                            $idUji,
                            $kodeWaktu,
                            $kandidat->count(),
                            'gugur_filter',
                            '',
                            '',
                            '',
                        ];

                        continue;
                    }

                    $dievaluasi++;

                    $rentang = $this->rentangMakro(
                        $kebutuhan,
                        $info['proporsi']
                    );

                    // Skor semua kandidat
                    $skor = [];

                    foreach ($kandidat as $m) {
                        $sim = $this->cosineSimilarity($profil, $m);
                        $macro = $this->macroScore($m, $rentang);

                        $skor[(string) $m->id_makanan] = [
                            'cbf' => $sim,
                            'macro' => $macro,
                            'hybrid' => round(
                                ($this->weightCbf * $sim) +
                                ($this->weightMacro * $macro),
                                4
                            ),
                        ];
                    }

                    $n = count($skor);
                    $posisi = [];

                    foreach (['hybrid', 'cbf', 'macro'] as $metode) {
                        $nilaiUji = $skor[$idUji][$metode];
                        $lebih = 0;
                        $sama = 0;

                        foreach ($skor as $s) {
                            $selisih = $s[$metode] - $nilaiUji;

                            if ($selisih > 1e-9) {
                                $lebih++;
                            } elseif (abs($selisih) <= 1e-9) {
                                $sama++;
                            }
                        }

                        $hasil[$userId][$metode][] =
                            $this->hitungMetrik($lebih, $sama, $k);

                        // Posisi rata-rata (jika ada skor kembar)
                        $posisi[$metode] = $lebih + (($sama + 1) / 2);
                    }

                    // Pembanding acak: semua posisi berpeluang sama
                    $hasil[$userId]['random'][] =
                        $this->hitungMetrik(0, $n, $k);

                    $detail[] = [
                        $userId,
                        $idUji,
                        $kodeWaktu,
                        $n,
                        'dievaluasi',
                        $posisi['hybrid'],
                        $posisi['cbf'],
                        $posisi['macro'],
                    ];
                }
            }
        }

        if (empty($hasil)) {
            $this->error('Tidak ada kasus yang dapat dievaluasi.');
            return self::FAILURE;
        }

        // Rata-rata per pengguna, lalu rata-rata antar pengguna
        $ringkasan = [];

        foreach (array_keys(self::METODE) as $metode) {
            $hitPengguna = [];
            $rrPengguna = [];
            $ndcgPengguna = [];

            foreach ($hasil as $kasusPengguna) {
                if (empty($kasusPengguna[$metode])) {
                    continue;
                }

                $hitPengguna[] = $this->rata(
                    array_column($kasusPengguna[$metode], 'hit')
                );
                $rrPengguna[] = $this->rata(
                    array_column($kasusPengguna[$metode], 'rr')
                );
                $ndcgPengguna[] = $this->rata(
                    array_column($kasusPengguna[$metode], 'ndcg')
                );
            }

            $ringkasan[] = [
                self::METODE[$metode],
                number_format($this->rata($hitPengguna), 4),
                number_format($this->rata($rrPengguna), 4),
                number_format($this->rata($ndcgPengguna), 4),
            ];
        }

        $this->newLine();
        $this->info('HASIL EVALUASI LOO-CV');
        $this->line('Top-K                       : ' . $k);
        $this->line('Pengguna dievaluasi         : ' . count($hasil));
        $this->line('Pengguna dilewati           : ' . $penggunaDilewati
            . ' (preferensi < ' . $minPref . ' atau belum ada kebutuhan energi)');
        $this->line('Total kasus uji             : ' . $totalKasus);
        $this->line('Gugur filter waktu/kalori   : ' . $gugurFilter
            . ($totalKasus > 0
                ? ' (' . number_format($gugurFilter / $totalKasus * 100, 2) . '%)'
                : ''));
        $this->line('Kasus dievaluasi            : ' . $dievaluasi);
        $this->line('Kasus gugur ' . ($hitungGugur ? 'DIHITUNG sebagai gagal' : 'TIDAK dihitung'));
        $this->newLine();

        $this->table(
            ['Metode', 'Hit Rate@' . $k, 'MRR', 'NDCG@' . $k],
            $ringkasan
        );

        if ($csvNama) {
            $this->simpanCsv($csvNama, $detail);
        }

        return self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | Logika yang sama dengan RekomendasiController
    |--------------------------------------------------------------------------
    */

    /**
     * Daftar waktu makan suatu makanan (kolom waktu_makan dipisah koma).
     */
    private function daftarWaktu($makanan): array
    {
        return array_map(
            fn ($w) => mb_strtolower(trim($w)),
            explode(',', (string) $makanan->waktu_makan)
        );
    }

    /**
     * Langkah 15: profil preferensi dari makanan latih.
     */
    private function bentukProfil($makananLatih): array
    {
        $profil = [];
        $jumlah = $makananLatih->count();

        foreach ($this->fiturMakanan as $fitur) {
            $aktif = 0;

            foreach ($makananLatih as $m) {
                if ((int) $m->{$fitur} === 1) {
                    $aktif++;
                }
            }

            $profil[$fitur] = $jumlah > 0 ? $aktif / $jumlah : 0;
        }

        return $profil;
    }

    /**
     * Langkah 16: cosine similarity.
     */
    private function cosineSimilarity(array $profil, $makanan): float
    {
        $dotProduct = 0;
        $normProfil = 0;
        $normMakanan = 0;

        foreach ($this->fiturMakanan as $fitur) {
            $nilaiProfil = $profil[$fitur] ?? 0;
            $nilaiMakanan = (int) ($makanan->{$fitur} ?? 0);

            $dotProduct += $nilaiProfil * $nilaiMakanan;
            $normProfil += pow($nilaiProfil, 2);
            $normMakanan += pow($nilaiMakanan, 2);
        }

        if ($normProfil > 0 && $normMakanan > 0) {
            $similarity = $dotProduct /
                (sqrt($normProfil) * sqrt($normMakanan));
        } else {
            $similarity = 0;
        }

        return round($similarity, 4);
    }

    /**
     * Langkah 5-9: rentang target makronutrien per waktu makan.
     */
    private function rentangMakro($kebutuhan, float $proporsi): array
    {
        $kaloriHarian = (float) $kebutuhan->target_kalori_harian;

        // [persen min, persen maks, kkal per gram]
        $amdr = [
            'karbohidrat' => [0.45, 0.65, 4],
            'protein' => [0.10, 0.35, 4],
            'lemak' => [0.20, 0.35, 9],
        ];

        $rentang = [];

        foreach ($amdr as $nama => [$min, $max, $kkal]) {
            $rentang[$nama] = [
                round((($kaloriHarian * $min) / $kkal) * $proporsi, 2),
                round((($kaloriHarian * $max) / $kkal) * $proporsi, 2),
            ];
        }

        return $rentang;
    }

    /**
     * Langkah 17: skor kesesuaian satu makronutrien.
     */
    private function skorMakro(
        float $nilai,
        float $batasMin,
        float $batasMax
    ): float {
        if ($nilai >= $batasMin && $nilai <= $batasMax) {
            return 1.0;
        }

        if ($nilai < $batasMin) {
            if ($batasMin <= 0) {
                return 0.0;
            }

            return max(0.0, 1.0 - (($batasMin - $nilai) / $batasMin));
        }

        if ($batasMax <= 0) {
            return 0.0;
        }

        return max(0.0, 1.0 - (($nilai - $batasMax) / $batasMax));
    }

    /**
     * Langkah 17: Macro Score.
     */
    private function macroScore($makanan, array $rentang): float
    {
        $skorKarbohidrat = $this->skorMakro(
            (float) ($makanan->karbohidrat ?? 0),
            $rentang['karbohidrat'][0],
            $rentang['karbohidrat'][1]
        );

        $skorProtein = $this->skorMakro(
            (float) ($makanan->protein ?? 0),
            $rentang['protein'][0],
            $rentang['protein'][1]
        );

        $skorLemak = $this->skorMakro(
            (float) ($makanan->lemak ?? 0),
            $rentang['lemak'][0],
            $rentang['lemak'][1]
        );

        return round(
            ($skorKarbohidrat + $skorProtein + $skorLemak) / 3,
            4
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Metrik evaluasi
    |--------------------------------------------------------------------------
    */

    /**
     * Metrik untuk satu makanan uji.
     *
     * $lebih = jumlah kandidat dengan skor LEBIH TINGGI dari makanan uji.
     * $sama  = jumlah kandidat dengan skor SAMA (termasuk makanan uji).
     *
     * Jika ada skor kembar, makanan uji dianggap berada pada salah satu
     * posisi kembar dengan peluang sama (nilai harapan), sehingga hasil
     * tidak bergantung pada urutan data di database.
     */
    private function hitungMetrik(int $lebih, int $sama, int $k): array
    {
        $hit = 0.0;
        $rr = 0.0;
        $ndcg = 0.0;

        for ($i = 1; $i <= $sama; $i++) {
            $posisi = $lebih + $i;

            $rr += 1 / $posisi;

            if ($posisi <= $k) {
                $hit += 1;
                $ndcg += 1 / log($posisi + 1, 2);
            }
        }

        return [
            'hit' => $hit / $sama,
            'rr' => $rr / $sama,
            'ndcg' => $ndcg / $sama,
        ];
    }

    private function rata(array $nilai): float
    {
        return count($nilai) > 0
            ? array_sum($nilai) / count($nilai)
            : 0.0;
    }

    private function simpanCsv(string $nama, array $detail): void
    {
        $path = storage_path('app/' . ltrim($nama, '/'));
        $file = fopen($path, 'w');

        fputcsv($file, [
            'user_id',
            'id_makanan_uji',
            'waktu_makan',
            'jumlah_kandidat',
            'status',
            'posisi_hybrid',
            'posisi_cbf',
            'posisi_macro',
        ]);

        foreach ($detail as $baris) {
            fputcsv($file, $baris);
        }

        fclose($file);

        $this->info('Detail per kasus disimpan di: ' . $path);
    }
}