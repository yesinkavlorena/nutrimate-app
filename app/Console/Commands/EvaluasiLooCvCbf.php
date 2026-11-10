<?php

namespace App\Console\Commands;

use App\Models\Makanan;
use App\Models\PreferensiMakanan;
use Illuminate\Console\Command;

/**
 * Evaluasi Content-Based Filtering (cosine similarity) dengan
 * Leave-One-Out Cross Validation.
 *
 * Untuk setiap pengguna dan setiap makanan preferensinya:
 *  1. Makanan tersebut disembunyikan (makanan uji).
 *  2. Profil preferensi dibentuk dari makanan preferensi yang tersisa.
 *  3. Seluruh makanan (kecuali makanan latih) diurutkan berdasarkan
 *     cosine similarity terhadap profil.
 *  4. Dicatat posisi makanan uji.
 *
 * Tidak memakai filter kalori maupun Macro Score.
 * Command ini hanya MEMBACA data, tidak mengubah database.
 *
 * Penggunaan:
 *   php artisan evaluasi:loocv-cbf
 *   php artisan evaluasi:loocv-cbf --k=5,10
 *   php artisan evaluasi:loocv-cbf --filter-waktu
 *   php artisan evaluasi:loocv-cbf --csv=loocv_cbf.csv
 */
class EvaluasiLooCvCbf extends Command
{
    protected $signature = 'evaluasi:loocv-cbf
                            {--k=5,10 : Daftar Top-K, dipisah koma}
                            {--min-pref=3 : Minimal jumlah preferensi per pengguna}
                            {--filter-waktu : Batasi kandidat pada waktu makan yang sama dengan makanan uji}
                            {--csv= : Simpan detail per kasus ke storage/app/<nama-file>.csv}';

    protected $description = 'Evaluasi cosine similarity (CBF) dengan Leave-One-Out Cross Validation';

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

    public function handle(): int
    {
        $ks = collect(explode(',', (string) $this->option('k')))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (empty($ks)) {
            $ks = [5];
        }

        $minPref = max(2, (int) $this->option('min-pref'));
        $filterWaktu = (bool) $this->option('filter-waktu');
        $csvNama = $this->option('csv');

        $semuaMakanan = Makanan::all();

        $prefPerUser = PreferensiMakanan::with('makanan')
            ->get()
            ->groupBy('user_id');

        if ($prefPerUser->isEmpty()) {
            $this->error('Tidak ada data preferensi makanan.');
            return self::FAILURE;
        }

        // $hasil[user_id]['cbf' | 'random'][] = metrik satu kasus
        $hasil = [];
        $detail = [];

        $penggunaDilewati = 0;
        $totalKasus = 0;
        $totalKandidat = 0;

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

            foreach ($makananPref as $uji) {
                $idUji = (string) $uji->id_makanan;

                $latih = $makananPref
                    ->filter(fn ($m) => (string) $m->id_makanan !== $idUji)
                    ->values();

                $idLatih = $latih
                    ->map(fn ($m) => (string) $m->id_makanan)
                    ->all();

                $profil = $this->bentukProfil($latih);

                // Konteks pengujian: tanpa filter waktu (satu kasus), atau
                // satu kasus untuk tiap waktu makan milik makanan uji.
                $daftarKonteks = [null];

                if ($filterWaktu) {
                    $daftarKonteks = array_values(array_filter(
                        $this->daftarWaktu($uji),
                        fn ($w) => $w !== ''
                    ));
                }

                foreach ($daftarKonteks as $waktu) {
                    $kandidat = $semuaMakanan->filter(
                        function ($m) use ($idLatih, $waktu) {
                            if (in_array((string) $m->id_makanan, $idLatih, true)) {
                                return false;
                            }

                            return $waktu === null
                                || in_array($waktu, $this->daftarWaktu($m), true);
                        }
                    );

                    $skor = [];

                    foreach ($kandidat as $m) {
                        $skor[(string) $m->id_makanan] =
                            $this->cosineSimilarity($profil, $m);
                    }

                    if (!isset($skor[$idUji])) {
                        continue;
                    }

                    $n = count($skor);
                    $nilaiUji = $skor[$idUji];
                    $lebih = 0;
                    $sama = 0;

                    foreach ($skor as $s) {
                        $selisih = $s - $nilaiUji;

                        if ($selisih > 1e-9) {
                            $lebih++;
                        } elseif (abs($selisih) <= 1e-9) {
                            $sama++;
                        }
                    }

                    $totalKasus++;
                    $totalKandidat += $n;

                    $hasil[$userId]['cbf'][] =
                        $this->hitungMetrik($lebih, $sama, $ks);

                    // Pembanding acak: semua posisi berpeluang sama
                    $hasil[$userId]['random'][] =
                        $this->hitungMetrik(0, $n, $ks);

                    $detail[] = [
                        $userId,
                        $idUji,
                        $waktu ?? '-',
                        $n,
                        $lebih + (($sama + 1) / 2),
                        $sama,
                    ];
                }
            }
        }

        if (empty($hasil)) {
            $this->error('Tidak ada kasus yang dapat dievaluasi.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('HASIL EVALUASI LOO-CV: COSINE SIMILARITY (CBF)');
        $this->line('Mode kandidat               : '
            . ($filterWaktu
                ? 'dibatasi waktu makan yang sama dengan makanan uji'
                : 'seluruh makanan (tanpa filter waktu makan dan kalori)'));
        $this->line('Top-K                       : ' . implode(', ', $ks));
        $this->line('Pengguna dievaluasi         : ' . count($hasil));
        $this->line('Pengguna dilewati           : ' . $penggunaDilewati
            . ' (preferensi < ' . $minPref . ')');
        $this->line('Total kasus uji             : ' . $totalKasus);
        $this->line('Rata-rata jumlah kandidat   : '
            . number_format($totalKandidat / max(1, $totalKasus), 1));
        $this->newLine();

        $header = ['Metode', 'MRR'];

        foreach ($ks as $k) {
            $header[] = 'Hit Rate@' . $k;
        }

        foreach ($ks as $k) {
            $header[] = 'NDCG@' . $k;
        }

        $baris = [];

        foreach (['cbf' => 'CBF (cosine similarity)', 'random' => 'Acak (pembanding)'] as $kunci => $nama) {
            $rrPengguna = [];
            $hitPengguna = array_fill_keys($ks, []);
            $ndcgPengguna = array_fill_keys($ks, []);

            foreach ($hasil as $kasusPengguna) {
                $kasus = $kasusPengguna[$kunci] ?? [];

                if (empty($kasus)) {
                    continue;
                }

                $rrPengguna[] = $this->rata(array_column($kasus, 'rr'));

                foreach ($ks as $k) {
                    $hitPengguna[$k][] = $this->rata(
                        array_map(fn ($c) => $c['hit'][$k], $kasus)
                    );
                    $ndcgPengguna[$k][] = $this->rata(
                        array_map(fn ($c) => $c['ndcg'][$k], $kasus)
                    );
                }
            }

            $r = [$nama, number_format($this->rata($rrPengguna), 4)];

            foreach ($ks as $k) {
                $r[] = number_format($this->rata($hitPengguna[$k]), 4);
            }

            foreach ($ks as $k) {
                $r[] = number_format($this->rata($ndcgPengguna[$k]), 4);
            }

            $baris[] = $r;
        }

        $this->table($header, $baris);

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
    private function hitungMetrik(int $lebih, int $sama, array $ks): array
    {
        $rr = 0.0;
        $hit = array_fill_keys($ks, 0.0);
        $ndcg = array_fill_keys($ks, 0.0);

        for ($i = 1; $i <= $sama; $i++) {
            $posisi = $lebih + $i;

            $rr += 1 / $posisi;

            foreach ($ks as $k) {
                if ($posisi <= $k) {
                    $hit[$k] += 1;
                    $ndcg[$k] += 1 / log($posisi + 1, 2);
                }
            }
        }

        foreach ($ks as $k) {
            $hit[$k] /= $sama;
            $ndcg[$k] /= $sama;
        }

        return [
            'rr' => $rr / $sama,
            'hit' => $hit,
            'ndcg' => $ndcg,
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
            'posisi_cbf',
            'jumlah_skor_kembar',
        ]);

        foreach ($detail as $baris) {
            fputcsv($file, $baris);
        }

        fclose($file);

        $this->info('Detail per kasus disimpan di: ' . $path);
    }
}