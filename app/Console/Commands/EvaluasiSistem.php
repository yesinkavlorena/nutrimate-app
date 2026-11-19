<?php

namespace App\Console\Commands;

use App\Models\KebutuhanEnergi;
use App\Models\Makanan;
use App\Models\PreferensiMakanan;
use Illuminate\Console\Command;

/**
 * Evaluasi keseluruhan Hybrid Recommender System NutriMate dengan LOO-CV.
 *
 * Setiap pengguna harus memiliki tepat 7 makanan favorit (default).
 * Pada setiap iterasi, 1 makanan menjadi data uji dan 6 makanan lainnya
 * digunakan untuk membentuk profil preferensi.
 *
 * Alur setiap kasus:
 *  1. Bentuk profil dari makanan latih.
 *  2. Jalankan Health-Based Filtering (waktu makan dan kalori +/-20%).
 *  3. Hitung Cosine Similarity dan Macro Score pada kandidat.
 *  4. Hitung Hybrid Score berdasarkan variasi bobot.
 *  5. Ambil Top-K dan evaluasi posisi makanan uji serta kualitas nutrisinya.
 *
 * Makanan uji yang tidak lolos filter HBF tetap dihitung sebagai miss
 * (MRR, Hit Rate, dan NDCG = 0), bukan dihapus dari evaluasi.
 *
 * Command hanya membaca database.
 *
 * Penggunaan:
 *   php artisan evaluasi:sistem
 *   php artisan evaluasi:sistem --k=5
 *   php artisan evaluasi:sistem --jumlah-pref=7
 *   php artisan evaluasi:sistem --bobot=0,0.25,0.5,0.75,1
 *   php artisan evaluasi:sistem --csv=evaluasi_hybrid_loocv.csv
 */
class EvaluasiSistem extends Command
{
    protected $signature = 'evaluasi:sistem
                            {--k=5 : Jumlah rekomendasi teratas (Top-K)}
                            {--jumlah-pref=7 : Jumlah preferensi yang wajib dimiliki setiap pengguna}
                            {--bobot=0,0.1,0.2,0.3,0.4,0.5,0.6,0.7,0.8,0.9,1 : Daftar bobot CBF (bobot Macro = 1 - bobot CBF)}
                            {--csv= : Simpan detail ke storage/app/<nama>.csv}';

    protected $description = 'Evaluasi keseluruhan Hybrid Recommender System NutriMate menggunakan LOO-CV';

    private array $fiturMakanan = [
        'berkuah', 'digoreng', 'dibakar', 'dipanggang', 'direbus',
        'dikukus', 'ditumis', 'pedas', 'manis', 'gurih', 'asam',
        'bersantan', 'berbumbu_rempah', 'bertepung', 'berbahan_ayam',
        'berbahan_sapi', 'berbahan_kambing', 'berbahan_ikan',
        'berbahan_seafood', 'berbahan_bebek', 'berbahan_telur',
        'berbahan_sayur', 'berbahan_tahu_tempe', 'berbahan_dasar_tepung',
        'berbumbu_kacang', 'berbumbu_balado', 'berbumbu_bali',
    ];

    private array $waktuMakan = [
        'sarapan' => [
            'nama' => 'Sarapan',
            'db' => 'sarapan',
            'kolom' => 'target_sarapan',
            'proporsi' => 0.167,
        ],
        'makan_siang' => [
            'nama' => 'Makan Siang',
            'db' => 'makan siang',
            'kolom' => 'target_makan_siang',
            'proporsi' => 0.305,
        ],
        'makan_malam' => [
            'nama' => 'Makan Malam',
            'db' => 'makan malam',
            'kolom' => 'target_makan_malam',
            'proporsi' => 0.338,
        ],
        'camilan' => [
            'nama' => 'Camilan',
            'db' => 'cemilan',
            'kolom' => 'target_snack',
            'proporsi' => 0.190,
        ],
    ];

    private float $toleransi = 0.20;
    private const BOBOT_SISTEM = 0.5;

    public function handle(): int
    {
        $k = max(1, (int) $this->option('k'));
        $jumlahPrefWajib = max(2, (int) $this->option('jumlah-pref'));
        $csvNama = $this->option('csv');

        $daftarBobot = collect(explode(',', (string) $this->option('bobot')))
            ->map(fn ($v) => round((float) trim($v), 2))
            ->merge([0.0, self::BOBOT_SISTEM, 1.0])
            ->filter(fn ($v) => $v >= 0 && $v <= 1)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $semuaMakanan = Makanan::all();

        $prefPerUser = PreferensiMakanan::with('makanan')
            ->get()
            ->groupBy('user_id');

        if ($prefPerUser->isEmpty()) {
            $this->error('Tidak ada data preferensi makanan.');
            return self::FAILURE;
        }

        // hasil[user_id][metode] = daftar metrik per kasus LOO-CV.
        $hasil = [];
        $detail = [];
        $penggunaDilewati = 0;
        $alasanDilewati = [
            'jumlah_preferensi' => 0,
            'kebutuhan_energi' => 0,
        ];
        $totalKasus = 0;
        $kasusTargetLolosHbf = 0;
        $kasusTargetTidakLolosHbf = 0;
        $totalKandidat = 0;
        $kasusTanpaKandidat = 0;

        foreach ($prefPerUser as $userId => $daftarPref) {
            $makananPref = $daftarPref
                ->pluck('makanan')
                ->filter()
                ->unique('id_makanan')
                ->values();

            // Evaluasi mengikuti ketentuan aplikasi: tepat 7 makanan favorit.
            if ($makananPref->count() !== $jumlahPrefWajib) {
                $penggunaDilewati++;
                $alasanDilewati['jumlah_preferensi']++;
                $this->warn(
                    "Pengguna {$userId} dilewati: memiliki {$makananPref->count()} "
                    . "preferensi, seharusnya {$jumlahPrefWajib}."
                );
                continue;
            }

            $kebutuhan = KebutuhanEnergi::where('user_id', $userId)
                ->latest()
                ->first();

            if (!$kebutuhan) {
                $penggunaDilewati++;
                $alasanDilewati['kebutuhan_energi']++;
                $this->warn("Pengguna {$userId} dilewati: data kebutuhan energi tidak ditemukan.");
                continue;
            }

            $idSemuaPref = $makananPref
                ->map(fn ($m) => (string) $m->id_makanan)
                ->all();

            // Satu putaran untuk setiap makanan favorit yang ditahan sebagai data uji.
            foreach ($makananPref as $uji) {
                $idUji = (string) $uji->id_makanan;

                $makananLatih = $makananPref
                    ->filter(fn ($m) => (string) $m->id_makanan !== $idUji)
                    ->values();

                $idLatih = $makananLatih
                    ->map(fn ($m) => (string) $m->id_makanan)
                    ->all();

                $profil = $this->bentukProfil($makananLatih);

                // Uji dilakukan pada setiap konteks waktu makan yang dimiliki makanan uji.
                $konteks = array_values(array_unique(array_filter(
                    $this->daftarWaktu($uji),
                    fn ($w) => $w !== ''
                )));

                foreach ($konteks as $waktuDb) {
                    $info = $this->infoWaktuMakan($waktuDb);

                    // Jika nilai waktu makan pada data tidak dikenali, catat sebagai miss.
                    if ($info === null) {
                        $totalKasus++;
                        $kasusTargetTidakLolosHbf++;

                        $this->catatKasusKosong(
                            $hasil,
                            $detail,
                            $userId,
                            $idUji,
                            $waktuDb,
                            $daftarBobot,
                            $k,
                            $idSemuaPref
                        );
                        continue;
                    }

                    $targetKalori = (float) ($kebutuhan->{$info['kolom']} ?? 0);

                    if ($targetKalori <= 0) {
                        $totalKasus++;
                        $kasusTargetTidakLolosHbf++;

                        $this->catatKasusKosong(
                            $hasil,
                            $detail,
                            $userId,
                            $idUji,
                            $waktuDb,
                            $daftarBobot,
                            $k,
                            $idSemuaPref
                        );
                        continue;
                    }

                    $batasBawah = $targetKalori * (1 - $this->toleransi);
                    $batasAtas = $targetKalori * (1 + $this->toleransi);

                    $rentangMakro = $this->rentangMakro(
                        $kebutuhan,
                        $info['proporsi']
                    );

                    // HBF: filter waktu makan dan rentang kalori.
                    // Enam makanan latih dikeluarkan agar tidak terjadi kebocoran data.
                    $kandidat = $semuaMakanan->filter(
                        function ($m) use ($info, $batasBawah, $batasAtas, $idLatih) {
                            if (!in_array($info['db'], $this->daftarWaktu($m), true)) {
                                return false;
                            }

                            $kalori = (float) $m->kalori;

                            if ($kalori < $batasBawah || $kalori > $batasAtas) {
                                return false;
                            }

                            if (in_array((string) $m->id_makanan, $idLatih, true)) {
                                return false;
                            }

                            return true;
                        }
                    )->values();

                    $totalKasus++;
                    $totalKandidat += $kandidat->count();

                    if ($kandidat->isEmpty()) {
                        $kasusTanpaKandidat++;
                    }

                    $targetLolosHbf = $kandidat->contains(
                        fn ($m) => (string) $m->id_makanan === $idUji
                    );

                    if ($targetLolosHbf) {
                        $kasusTargetLolosHbf++;
                    } else {
                        $kasusTargetTidakLolosHbf++;
                    }

                    $item = [];

                    foreach ($kandidat as $idx => $m) {
                        $item[] = [
                            'id' => (string) $m->id_makanan,
                            'idx' => $idx,
                            'sim' => $this->cosineSimilarity($profil, $m),
                            'macro' => $this->macroScore($m, $rentangMakro),
                            'kalori' => (float) $m->kalori,
                            'pref' => in_array((string) $m->id_makanan, $idSemuaPref, true),
                        ];
                    }

                    // Metrik pembanding acak dihitung sebagai nilai harapan posisi acak
                    // pada kandidat yang sama, bukan rata-rata seluruh kandidat.
                    $metrikAcak = $this->metrikPeringkatAcak(
                        count($item),
                        $k,
                        $targetLolosHbf
                    );

                    foreach ($daftarBobot as $w) {
                        $urut = $item;

                        foreach ($urut as &$x) {
                            $x['skor'] = round(
                                ($w * $x['sim']) + ((1 - $w) * $x['macro']),
                                4
                            );
                        }
                        unset($x);

                        usort(
                            $urut,
                            fn ($a, $b) => [$b['skor'], $a['idx']]
                                <=> [$a['skor'], $b['idx']]
                        );

                        $topK = array_slice($urut, 0, $k);

                        $metrikPeringkat = $this->metrikPeringkatTarget(
                            $urut,
                            $idUji,
                            $k
                        );

                        $metrikKualitas = $this->metrikTopK(
                            $topK,
                            $targetKalori
                        );

                        $metrik = array_merge(
                            $metrikPeringkat,
                            $metrikKualitas
                        );

                        $kunci = $this->kunciBobot($w);
                        $hasil[$userId][$kunci][] = $metrik;

                        $detail[] = [
                            $userId,
                            $idUji,
                            $waktuDb,
                            $kunci,
                            $kandidat->count(),
                            $targetLolosHbf ? 1 : 0,
                            $metrik['rr'],
                            $metrik['hit'],
                            $metrik['ndcg'],
                            $metrik['macro'],
                            $metrik['dalam_rentang'],
                            $metrik['selisih_kalori'],
                            $metrik['similarity'],
                            $metrik['preferensi'],
                        ];
                    }

                    $hasil[$userId]['random'][] = $metrikAcak;

                    $detail[] = [
                        $userId,
                        $idUji,
                        $waktuDb,
                        'random',
                        $kandidat->count(),
                        $targetLolosHbf ? 1 : 0,
                        $metrikAcak['rr'],
                        $metrikAcak['hit'],
                        $metrikAcak['ndcg'],
                        null,
                        null,
                        null,
                        null,
                        null,
                    ];
                }
            }
        }

        if (empty($hasil)) {
            $this->error('Tidak ada kasus yang dapat dievaluasi.');
            $this->line(
                "Periksa apakah setiap pengguna memiliki tepat {$jumlahPrefWajib} "
                . 'preferensi makanan dan data kebutuhan energi.'
            );
            return self::FAILURE;
        }

        // Rata-rata metrik per pengguna agar pengguna dengan kasus lebih banyak
        // tidak otomatis memiliki bobot lebih besar pada ringkasan akhir.
        $perPengguna = [];

        foreach ($hasil as $userId => $perMetode) {
            foreach ($perMetode as $metode => $daftarKasus) {
                $perPengguna[$userId][$metode] = $this->ringkasKasus($daftarKasus);
            }
        }

        $this->newLine();
        $this->info('HASIL EVALUASI KESELURUHAN HYBRID RECOMMENDER SYSTEM NUTRIMATE');
        $this->line('Skenario                    : Leave-One-Out Cross-Validation');
        $this->line('Preferensi per pengguna     : ' . $jumlahPrefWajib);
        $this->line('Data latih / data uji       : '
            . ($jumlahPrefWajib - 1) . ' / 1 makanan');
        $this->line('Top-K                       : ' . $k);
        $this->line('Pengguna dievaluasi         : ' . count($perPengguna));
        $this->line('Pengguna dilewati           : ' . $penggunaDilewati);
        $this->line('  - Jumlah preferensi tidak sesuai: '
            . $alasanDilewati['jumlah_preferensi']);
        $this->line('  - Tidak ada kebutuhan energi    : '
            . $alasanDilewati['kebutuhan_energi']);
        $this->line('Total kasus uji             : ' . $totalKasus);
        $this->line('Target lolos filter HBF     : ' . $kasusTargetLolosHbf);
        $this->line('Target tidak lolos HBF      : ' . $kasusTargetTidakLolosHbf);
        $this->line('Kasus tanpa kandidat        : ' . $kasusTanpaKandidat);
        $this->line('Rata-rata jumlah kandidat   : '
            . number_format($totalKandidat / max(1, $totalKasus), 1));
        $this->line('Filter kalori               : +/-'
            . number_format($this->toleransi * 100, 0) . '% dari target waktu makan');
        $this->newLine();

        // Tabel 1: keberhasilan menemukan makanan uji.
        $this->line('Tabel 1. Kualitas peringkat makanan uji (rata-rata +/- SD antar pengguna)');

        $barisPeringkat = [];

        foreach ($daftarBobot as $w) {
            $kunci = $this->kunciBobot($w);
            $barisPeringkat[] = [
                $this->labelBobot($w),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'rr')),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'hit')),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'ndcg')),
            ];
        }

        $barisPeringkat[] = [
            'Acak (pembanding)',
            $this->meanSd($this->ambilKolom($perPengguna, 'random', 'rr')),
            $this->meanSd($this->ambilKolom($perPengguna, 'random', 'hit')),
            $this->meanSd($this->ambilKolom($perPengguna, 'random', 'ndcg')),
        ];

        $this->table(
            ['Metode (bobot CBF/Macro)', 'MRR', 'Hit Rate@' . $k, 'NDCG@' . $k],
            $barisPeringkat
        );

        // Tabel 2: kualitas nutrisi dan preferensi pada rekomendasi Top-K.
        $this->newLine();
        $this->line('Tabel 2. Kualitas rekomendasi Top-' . $k
            . ' (rata-rata +/- SD antar pengguna)');

        $barisKualitas = [];

        foreach ($daftarBobot as $w) {
            $kunci = $this->kunciBobot($w);
            $barisKualitas[] = [
                $this->labelBobot($w),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'macro')),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'dalam_rentang')),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'selisih_kalori')),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'similarity')),
                $this->meanSd($this->ambilKolom($perPengguna, $kunci, 'preferensi')),
            ];
        }

        $this->table(
            [
                'Metode (bobot CBF/Macro)',
                'Macro Score',
                '% seluruh makro sesuai',
                'Selisih kalori (%)',
                'Similarity',
                '% makanan favorit',
            ],
            $barisKualitas
        );

        // Tabel 3: perbandingan berpasangan antar pengguna.
        $this->newLine();
        $this->line('Tabel 3. Uji Wilcoxon signed-rank eksak (dua arah)');

        $hybrid = $this->kunciBobot(self::BOBOT_SISTEM);
        $cbf = $this->kunciBobot(1.0);
        $macro = $this->kunciBobot(0.0);

        $perbandingan = [
            ['Hybrid vs CBF saja', 'Macro Score', $hybrid, $cbf, 'macro'],
            ['Hybrid vs CBF saja', '% seluruh makro sesuai', $hybrid, $cbf, 'dalam_rentang'],
            ['Hybrid vs Macro saja', 'Similarity', $hybrid, $macro, 'similarity'],
            ['Hybrid vs Macro saja', 'Hit Rate@' . $k, $hybrid, $macro, 'hit'],
        ];

        $barisWilcoxon = [];

        foreach ($perbandingan as [$nama, $namaMetrik, $a, $b, $kolom]) {
            $selisih = [];

            foreach ($perPengguna as $m) {
                if (isset($m[$a][$kolom], $m[$b][$kolom])) {
                    $selisih[] = $m[$a][$kolom] - $m[$b][$kolom];
                }
            }

            $uji = $this->wilcoxon($selisih);

            $barisWilcoxon[] = [
                $nama,
                $namaMetrik,
                $uji['n'],
                $uji['n'] > 0 ? number_format($uji['p'], 4) : '-',
                count($selisih) > 0
                    ? ($this->rata($selisih) >= 0 ? 'Hybrid lebih tinggi' : 'Hybrid lebih rendah')
                    : '-',
            ];
        }

        $this->table(
            ['Perbandingan', 'Metrik', 'n (selisih != 0)', 'p-value', 'Arah rata-rata'],
            $barisWilcoxon
        );

        $this->line(
            'Catatan: Random merupakan nilai harapan peringkat acak pada kandidat yang sama. '
            . 'Metrik kualitas nutrisi dihitung dari Top-K hasil tiap bobot. '
            . 'Makanan uji yang tidak lolos HBF tetap dihitung sebagai miss.'
        );
        $this->line(
            'Uji Wilcoxon dilakukan pada ringkasan per pengguna; interpretasikan p-value '
            . 'secara hati-hati karena jumlah pengguna dapat terbatas.'
        );

        if ($csvNama) {
            $this->simpanCsv($csvNama, $detail);
        }

        return self::SUCCESS;
    }

    private function infoWaktuMakan(string $waktuDb): ?array
    {
        foreach ($this->waktuMakan as $info) {
            if ($info['db'] === $waktuDb) {
                return $info;
            }
        }

        return null;
    }

    private function daftarWaktu($makanan): array
    {
        return array_map(
            fn ($w) => mb_strtolower(trim($w)),
            explode(',', (string) $makanan->waktu_makan)
        );
    }

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
            $similarity = $dotProduct / (sqrt($normProfil) * sqrt($normMakanan));
        } else {
            $similarity = 0;
        }

        return round($similarity, 4);
    }

    private function rentangMakro($kebutuhan, float $proporsi): array
    {
        $kaloriHarian = (float) $kebutuhan->target_kalori_harian;

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

    private function skorMakro(float $nilai, float $batasMin, float $batasMax): float
    {
        if ($nilai >= $batasMin && $nilai <= $batasMax) {
            return 1.0;
        }

        if ($nilai < $batasMin) {
            return $batasMin > 0
                ? max(0.0, 1.0 - (($batasMin - $nilai) / $batasMin))
                : 0.0;
        }

        return $batasMax > 0
            ? max(0.0, 1.0 - (($nilai - $batasMax) / $batasMax))
            : 0.0;
    }

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

        return round(($skorKarbohidrat + $skorProtein + $skorLemak) / 3, 4);
    }

    /**
     * Metrik peringkat untuk satu makanan uji.
     * Posisi skor yang sama ditangani sebagai posisi seri (expected rank).
     */
    private function metrikPeringkatTarget(array $urut, string $idUji, int $k): array
    {
        $n = count($urut);
        $target = null;

        foreach ($urut as $x) {
            if ($x['id'] === $idUji) {
                $target = $x;
                break;
            }
        }

        if ($target === null || $n === 0) {
            return ['rr' => 0.0, 'hit' => 0.0, 'ndcg' => 0.0];
        }

        $lebih = 0;
        $sama = 0;

        foreach ($urut as $x) {
            if ($x['skor'] > $target['skor'] + 1e-9) {
                $lebih++;
            } elseif (abs($x['skor'] - $target['skor']) <= 1e-9) {
                $sama++;
            }
        }

        $rr = 0.0;
        $hit = 0.0;
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
            'rr' => $rr / $sama,
            'hit' => $hit / $sama,
            'ndcg' => $ndcg / $sama,
        ];
    }

    /**
     * Nilai harapan metrik jika target berada pada posisi acak 1..n.
     */
    private function metrikPeringkatAcak(int $n, int $k, bool $targetAda): array
    {
        if (!$targetAda || $n <= 0) {
            return ['rr' => 0.0, 'hit' => 0.0, 'ndcg' => 0.0];
        }

        $rr = 0.0;
        $hit = 0.0;
        $ndcg = 0.0;

        for ($posisi = 1; $posisi <= $n; $posisi++) {
            $rr += 1 / $posisi;

            if ($posisi <= $k) {
                $hit += 1;
                $ndcg += 1 / log($posisi + 1, 2);
            }
        }

        return [
            'rr' => $rr / $n,
            'hit' => $hit / $n,
            'ndcg' => $ndcg / $n,
        ];
    }

    private function metrikTopK(array $topK, float $targetKalori): array
    {
        $jumlah = count($topK);

        if ($jumlah === 0) {
            return [
                'macro' => 0.0,
                'dalam_rentang' => 0.0,
                'selisih_kalori' => 0.0,
                'similarity' => 0.0,
                'preferensi' => 0.0,
            ];
        }

        $macro = 0.0;
        $dalamRentang = 0;
        $selisihKalori = 0.0;
        $similarity = 0.0;
        $preferensi = 0;

        foreach ($topK as $x) {
            $macro += $x['macro'];
            $similarity += $x['sim'];

            if ($x['macro'] >= 0.9999) {
                $dalamRentang++;
            }

            if ($targetKalori > 0) {
                $selisihKalori += abs($x['kalori'] - $targetKalori)
                    / $targetKalori * 100;
            }

            if ($x['pref']) {
                $preferensi++;
            }
        }

        return [
            'macro' => $macro / $jumlah,
            'dalam_rentang' => $dalamRentang / $jumlah * 100,
            'selisih_kalori' => $selisihKalori / $jumlah,
            'similarity' => $similarity / $jumlah,
            'preferensi' => $preferensi / $jumlah * 100,
        ];
    }

    private function catatKasusKosong(
        array &$hasil,
        array &$detail,
        string $userId,
        string $idUji,
        string $waktu,
        array $daftarBobot,
        int $k,
        array $idSemuaPref
    ): void {
        $nolPeringkat = ['rr' => 0.0, 'hit' => 0.0, 'ndcg' => 0.0];
        $nolKualitas = [
            'macro' => 0.0,
            'dalam_rentang' => 0.0,
            'selisih_kalori' => 0.0,
            'similarity' => 0.0,
            'preferensi' => 0.0,
        ];

        foreach ($daftarBobot as $w) {
            $kunci = $this->kunciBobot($w);
            $metrik = array_merge($nolPeringkat, $nolKualitas);
            $hasil[$userId][$kunci][] = $metrik;

            $detail[] = [
                $userId, $idUji, $waktu, $kunci, 0, 0,
                0, 0, 0, 0, 0, 0, 0, 0,
            ];
        }

        $hasil[$userId]['random'][] = $nolPeringkat;
        $detail[] = [
            $userId, $idUji, $waktu, 'random', 0, 0,
            0, 0, 0, null, null, null, null, null,
        ];
    }

    private function ringkasKasus(array $daftarKasus): array
    {
        $kolom = array_keys($daftarKasus[0] ?? []);
        $ringkasan = [];

        foreach ($kolom as $nama) {
            $ringkasan[$nama] = $this->rata(
                array_map(fn ($x) => (float) ($x[$nama] ?? 0), $daftarKasus)
            );
        }

        return $ringkasan;
    }

    private function ambilKolom(array $perPengguna, string $metode, string $kolom): array
    {
        $nilai = [];

        foreach ($perPengguna as $m) {
            if (isset($m[$metode][$kolom])) {
                $nilai[] = $m[$metode][$kolom];
            }
        }

        return $nilai;
    }

    private function kunciBobot(float $w): string
    {
        return 'w' . number_format($w, 2, '.', '');
    }

    private function labelBobot(float $w): string
    {
        $teks = number_format($w, 2, ',', '') . ' / '
            . number_format(1 - $w, 2, ',', '');

        if (abs($w) < 1e-9) {
            return $teks . ' (Macro saja)';
        }

        if (abs($w - 1) < 1e-9) {
            return $teks . ' (CBF saja)';
        }

        if (abs($w - self::BOBOT_SISTEM) < 1e-9) {
            return $teks . ' (Hybrid sistem)';
        }

        return $teks;
    }

    private function rata(array $nilai): float
    {
        return count($nilai) > 0 ? array_sum($nilai) / count($nilai) : 0.0;
    }

    private function simpanganBaku(array $nilai): float
    {
        $n = count($nilai);

        if ($n < 2) {
            return 0.0;
        }

        $rata = $this->rata($nilai);
        $jumlahKuadrat = 0.0;

        foreach ($nilai as $v) {
            $jumlahKuadrat += pow($v - $rata, 2);
        }

        return sqrt($jumlahKuadrat / ($n - 1));
    }

    private function meanSd(array $nilai): string
    {
        return number_format($this->rata($nilai), 4)
            . ' +/- '
            . number_format($this->simpanganBaku($nilai), 4);
    }

    private function wilcoxon(array $selisih): array
    {
        $selisih = array_values(array_filter(
            $selisih,
            fn ($d) => abs($d) > 1e-12
        ));

        $n = count($selisih);

        if ($n === 0) {
            return ['n' => 0, 'wPlus' => 0.0, 'p' => 1.0];
        }

        $absolut = array_map('abs', $selisih);
        $urutan = $absolut;
        asort($urutan);

        $peringkat = [];
        $indeks = array_keys($urutan);
        $i = 0;

        while ($i < $n) {
            $j = $i;

            while (
                $j + 1 < $n
                && abs($absolut[$indeks[$j + 1]] - $absolut[$indeks[$i]]) <= 1e-12
            ) {
                $j++;
            }

            $rataPeringkat = (($i + 1) + ($j + 1)) / 2;

            for ($x = $i; $x <= $j; $x++) {
                $peringkat[$indeks[$x]] = $rataPeringkat;
            }

            $i = $j + 1;
        }

        $peringkat2 = [];

        foreach ($peringkat as $idx => $p) {
            $peringkat2[$idx] = (int) round($p * 2);
        }

        $wPlus2 = 0;

        foreach ($selisih as $idx => $d) {
            if ($d > 0) {
                $wPlus2 += $peringkat2[$idx];
            }
        }

        $total2 = array_sum($peringkat2);
        $sebaran = [0 => 1];

        foreach ($peringkat2 as $r2) {
            $baru = [];

            foreach ($sebaran as $jumlah => $cara) {
                $baru[$jumlah] = ($baru[$jumlah] ?? 0) + $cara;
                $baru[$jumlah + $r2] = ($baru[$jumlah + $r2] ?? 0) + $cara;
            }

            $sebaran = $baru;
        }

        $tengah2 = $total2 / 2;
        $jarakAmati = abs($wPlus2 - $tengah2);
        $ekstrem = 0;
        $semua = pow(2, $n);

        foreach ($sebaran as $jumlah => $cara) {
            if (abs($jumlah - $tengah2) >= $jarakAmati - 1e-9) {
                $ekstrem += $cara;
            }
        }

        return [
            'n' => $n,
            'wPlus' => $wPlus2 / 2,
            'p' => min(1.0, $ekstrem / $semua),
        ];
    }

    private function simpanCsv(string $nama, array $detail): void
    {
        $path = storage_path('app/' . ltrim($nama, '/'));
        $file = fopen($path, 'w');

        fputcsv($file, [
            'user_id',
            'id_makanan_uji',
            'waktu_makan',
            'metode',
            'jumlah_kandidat',
            'target_lolos_hbf',
            'mrr',
            'hit_rate_at_k',
            'ndcg_at_k',
            'macro_score_top_k',
            'persen_seluruh_makro_sesuai',
            'selisih_kalori_persen',
            'similarity_top_k',
            'persen_makanan_favorit_top_k',
        ]);

        foreach ($detail as $baris) {
            fputcsv($file, $baris);
        }

        fclose($file);
        $this->info('Detail evaluasi disimpan di: ' . $path);
    }
}