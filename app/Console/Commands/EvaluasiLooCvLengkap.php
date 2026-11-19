<?php

namespace App\Console\Commands;

use App\Models\Makanan;
use App\Models\PreferensiMakanan;
use Illuminate\Console\Command;

/**
 * Evaluasi Content-Based Filtering (cosine similarity) dengan
 * Leave-One-Out Cross Validation, dirancang untuk jumlah pengguna kecil.
 *
 * Untuk setiap pengguna, setiap makanan preferensinya (dan setiap waktu
 * makan yang tercatat pada makanan tersebut bila filter waktu aktif):
 *  1. Makanan tersebut disembunyikan (makanan uji).
 *  2. Profil preferensi dibentuk dari makanan preferensi yang tersisa.
 *  3. Kandidat diurutkan berdasarkan cosine similarity terhadap profil
 *     (makanan latih dikeluarkan dari kandidat).
 *  4. Dicatat posisi makanan uji.
 *
 * Keluaran:
 *  - Rata-rata ± simpangan baku ANTAR PENGGUNA untuk MRR, Percentile Rank,
 *    Hit Rate@K, dan NDCG@K (CBF dibanding pembanding acak).
 *  - Tabel hasil per pengguna.
 *  - Uji Wilcoxon signed-rank eksak (dua arah), CBF vs acak, antar pengguna.
 *
 * Tidak memakai filter kalori maupun Macro Score.
 * Command ini hanya MEMBACA data, tidak mengubah database.
 *
 * Penggunaan:
 *   php artisan evaluasi:loocv-lengkap
 *   php artisan evaluasi:loocv-lengkap --k=5,10
 *   php artisan evaluasi:loocv-lengkap --tanpa-filter-waktu
 *   php artisan evaluasi:loocv-lengkap --csv=loocv.csv
 */
class EvaluasiLooCvLengkap extends Command
{
    protected $signature = 'evaluasi:loocv-lengkap
                            {--k=5,10 : Daftar Top-K, dipisah koma}
                            {--min-pref=3 : Minimal jumlah preferensi per pengguna}
                            {--tanpa-filter-waktu : Kandidat = seluruh makanan (tanpa filter waktu makan)}
                            {--csv= : Simpan detail ke storage/app/<nama>.csv dan <nama>_pengguna.csv}';

    protected $description = 'Evaluasi CBF dengan LOO-CV: rata-rata ± SD antar pengguna, percentile rank, dan uji Wilcoxon';

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
        $filterWaktu = !$this->option('tanpa-filter-waktu');
        $csvNama = $this->option('csv');

        $semuaMakanan = Makanan::all();

        $prefPerUser = PreferensiMakanan::with('makanan')
            ->get()
            ->groupBy('user_id');

        if ($prefPerUser->isEmpty()) {
            $this->error('Tidak ada data preferensi makanan.');
            return self::FAILURE;
        }

        // $kasus[user_id]['cbf' | 'random'][] = metrik satu kasus
        $kasus = [];
        $kandidatPengguna = [];
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

                // Konteks pengujian: satu kasus per waktu makan makanan uji
                // (filter waktu aktif), atau satu kasus untuk seluruh makanan.
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

                    $kasus[$userId]['cbf'][] =
                        $this->hitungMetrik($lebih, $sama, $n, $ks);

                    // Pembanding acak: semua posisi berpeluang sama
                    $kasus[$userId]['random'][] =
                        $this->hitungMetrik(0, $n, $n, $ks);

                    $kandidatPengguna[$userId][] = $n;

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

        if (empty($kasus)) {
            $this->error('Tidak ada kasus yang dapat dievaluasi.');
            return self::FAILURE;
        }

        // Ringkasan per pengguna (rata-rata atas kasus milik pengguna itu)
        $perPengguna = [];

        foreach ($kasus as $userId => $perMetode) {
            foreach (['cbf', 'random'] as $metode) {
                $perPengguna[$userId][$metode] =
                    $this->ringkasPengguna($perMetode[$metode], $ks);
            }
        }

        $jumlahPengguna = count($perPengguna);

        /*
        |--------------------------------------------------------------------------
        | Keluaran
        |--------------------------------------------------------------------------
        */

        $this->newLine();
        $this->info('HASIL EVALUASI LOO-CV: COSINE SIMILARITY (CBF)');
        $this->line('Mode kandidat               : ' . ($filterWaktu
            ? 'makanan pada waktu makan yang sama dengan makanan uji'
            : 'seluruh makanan (tanpa filter waktu makan dan kalori)'));
        $this->line('Top-K                       : ' . implode(', ', $ks));
        $this->line('Pengguna dievaluasi         : ' . $jumlahPengguna);
        $this->line('Pengguna dilewati           : ' . $penggunaDilewati
            . ' (preferensi < ' . $minPref . ')');
        $this->line('Total kasus uji             : ' . $totalKasus);
        $this->line('Rata-rata jumlah kandidat   : '
            . number_format($totalKandidat / max(1, $totalKasus), 1));
        $this->newLine();

        // Tabel 1: rata-rata ± SD antar pengguna
        $this->line('Tabel 1. Rata-rata ± simpangan baku antar pengguna');

        $header = ['Metode', 'MRR', 'Percentile Rank'];

        foreach ($ks as $k) {
            $header[] = 'Hit Rate@' . $k;
        }

        foreach ($ks as $k) {
            $header[] = 'NDCG@' . $k;
        }

        $baris = [];

        foreach (['cbf' => 'CBF (cosine similarity)', 'random' => 'Acak (pembanding)'] as $metode => $nama) {
            $r = [
                $nama,
                $this->meanSd(array_map(fn ($u) => $u[$metode]['rr'], $perPengguna)),
                $this->meanSd(array_map(fn ($u) => $u[$metode]['pct'], $perPengguna)),
            ];

            foreach ($ks as $k) {
                $r[] = $this->meanSd(
                    array_map(fn ($u) => $u[$metode]['hit'][$k], $perPengguna)
                );
            }

            foreach ($ks as $k) {
                $r[] = $this->meanSd(
                    array_map(fn ($u) => $u[$metode]['ndcg'][$k], $perPengguna)
                );
            }

            $baris[] = $r;
        }

        $this->table($header, $baris);

        // Tabel 2: per pengguna (CBF)
        $this->newLine();
        $this->line('Tabel 2. Hasil CBF per pengguna');

        $header2 = ['Pengguna', 'Kasus', 'Rata2 kandidat', 'MRR', 'Percentile Rank'];

        foreach ($ks as $k) {
            $header2[] = 'Hit Rate@' . $k;
        }

        $baris2 = [];

        foreach ($perPengguna as $userId => $m) {
            $r = [
                $userId,
                count($kasus[$userId]['cbf']),
                number_format($this->rata($kandidatPengguna[$userId]), 1),
                number_format($m['cbf']['rr'], 4),
                number_format($m['cbf']['pct'], 4),
            ];

            foreach ($ks as $k) {
                $r[] = number_format($m['cbf']['hit'][$k], 4);
            }

            $baris2[] = $r;
        }

        $this->table($header2, $baris2);

        // Uji Wilcoxon (CBF vs acak, berpasangan antar pengguna)
        $this->newLine();
        $this->line('Tabel 3. Uji Wilcoxon signed-rank eksak (dua arah), CBF vs acak');

        $baris3 = [];

        foreach (['rr' => 'MRR', 'pct' => 'Percentile Rank'] as $kunci => $nama) {
            $selisih = [];

            foreach ($perPengguna as $m) {
                $selisih[] = $m['cbf'][$kunci] - $m['random'][$kunci];
            }

            $w = $this->wilcoxon($selisih);

            $baris3[] = [
                $nama,
                $w['n'],
                $w['n'] > 0 ? number_format($w['wPlus'], 1) : '-',
                $w['n'] > 0 ? number_format($w['p'], 4) : '-',
                $this->rata($selisih) >= 0 ? 'CBF > acak' : 'CBF < acak',
            ];
        }

        $this->table(
            ['Metrik', 'n (selisih ≠ 0)', 'W+', 'p-value', 'Arah rata-rata'],
            $baris3
        );

        $this->line('Catatan: dengan pengguna sedikit, daya uji rendah. '
            . 'p-value terkecil yang mungkin = 2/2^n '
            . '(n=10 -> 0,0020; n=5 -> 0,0625).');

        if ($csvNama) {
            $this->simpanCsv($csvNama, $detail, $perPengguna, $kasus, $kandidatPengguna, $ks);
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
     * $n     = jumlah seluruh kandidat.
     *
     * Jika ada skor kembar, makanan uji dianggap berada pada salah satu
     * posisi kembar dengan peluang sama (nilai harapan), sehingga hasil
     * tidak bergantung pada urutan data di database.
     *
     * Percentile Rank: 1 = peringkat teratas, 0 = peringkat terbawah,
     * nilai harapan acak = 0,5.
     */
    private function hitungMetrik(int $lebih, int $sama, int $n, array $ks): array
    {
        $rr = 0.0;
        $pct = 0.0;
        $hit = array_fill_keys($ks, 0.0);
        $ndcg = array_fill_keys($ks, 0.0);

        for ($i = 1; $i <= $sama; $i++) {
            $posisi = $lebih + $i;

            $rr += 1 / $posisi;
            $pct += $n > 1 ? 1 - (($posisi - 1) / ($n - 1)) : 1.0;

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
            'pct' => $pct / $sama,
            'hit' => $hit,
            'ndcg' => $ndcg,
        ];
    }

    /**
     * Rata-rata metrik seluruh kasus milik satu pengguna.
     */
    private function ringkasPengguna(array $daftarKasus, array $ks): array
    {
        $hit = [];
        $ndcg = [];

        foreach ($ks as $k) {
            $hit[$k] = $this->rata(array_map(fn ($c) => $c['hit'][$k], $daftarKasus));
            $ndcg[$k] = $this->rata(array_map(fn ($c) => $c['ndcg'][$k], $daftarKasus));
        }

        return [
            'rr' => $this->rata(array_column($daftarKasus, 'rr')),
            'pct' => $this->rata(array_column($daftarKasus, 'pct')),
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

    /**
     * Simpangan baku sampel (n - 1).
     */
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
        $nilai = array_values($nilai);

        return number_format($this->rata($nilai), 4)
            . ' ± '
            . number_format($this->simpanganBaku($nilai), 4);
    }

    /**
     * Uji Wilcoxon signed-rank eksak (dua arah) untuk data berpasangan.
     *
     * $selisih = selisih tiap pasangan (CBF - acak). Selisih nol dibuang.
     * Peringkat kembar diberi peringkat rata-rata. Sebaran W+ dihitung eksak
     * dengan pemrograman dinamis atas semua kombinasi tanda.
     */
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

        // Peringkat dari |selisih|, kembar = peringkat rata-rata
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

        // Peringkat dikali 2 supaya bilangan bulat
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

        // Sebaran W+ eksak: jumlah cara memperoleh tiap nilai jumlah
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

    private function simpanCsv(
        string $nama,
        array $detail,
        array $perPengguna,
        array $kasus,
        array $kandidatPengguna,
        array $ks
    ): void {
        $nama = ltrim($nama, '/');
        $pathKasus = storage_path('app/' . $nama);
        $pathPengguna = storage_path(
            'app/' . preg_replace('/\.csv$/i', '', $nama) . '_pengguna.csv'
        );

        $file = fopen($pathKasus, 'w');

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

        $file = fopen($pathPengguna, 'w');

        $header = ['user_id', 'jumlah_kasus', 'rata2_kandidat'];

        foreach (['cbf', 'random'] as $metode) {
            $header[] = "mrr_$metode";
            $header[] = "percentile_rank_$metode";

            foreach ($ks as $k) {
                $header[] = "hit_rate_{$k}_$metode";
            }

            foreach ($ks as $k) {
                $header[] = "ndcg_{$k}_$metode";
            }
        }

        fputcsv($file, $header);

        foreach ($perPengguna as $userId => $m) {
            $baris = [
                $userId,
                count($kasus[$userId]['cbf']),
                round($this->rata($kandidatPengguna[$userId]), 1),
            ];

            foreach (['cbf', 'random'] as $metode) {
                $baris[] = round($m[$metode]['rr'], 6);
                $baris[] = round($m[$metode]['pct'], 6);

                foreach ($ks as $k) {
                    $baris[] = round($m[$metode]['hit'][$k], 6);
                }

                foreach ($ks as $k) {
                    $baris[] = round($m[$metode]['ndcg'][$k], 6);
                }
            }

            fputcsv($file, $baris);
        }

        fclose($file);

        $this->info('Detail per kasus     : ' . $pathKasus);
        $this->info('Ringkasan per pengguna: ' . $pathPengguna);
    }
}