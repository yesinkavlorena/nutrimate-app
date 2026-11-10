<?php

namespace App\Http\Controllers;

use App\Models\KebutuhanEnergi;
use App\Models\Makanan;
use App\Models\PreferensiMakanan;
use App\Models\RiwayatRekomendasi;
use App\Models\DetailRekomendasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RekomendasiController extends Controller
{
    /**
     * Daftar atribut makanan untuk Content-Based Filtering.
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
     * Menampilkan halaman rekomendasi makanan.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | 1. Ambil kebutuhan energi pengguna
        |--------------------------------------------------------------------------
        */

        $kebutuhanEnergi = KebutuhanEnergi::where(
            'user_id',
            $user->id
        )->latest()->first();

        if (!$kebutuhanEnergi) {
            return redirect()
                ->route('kebutuhan-energi.hitung')
                ->with(
                    'error',
                    'Silakan hitung kebutuhan energi terlebih dahulu.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Ambil waktu makan dan target kalorinya
        |--------------------------------------------------------------------------
        */

        $waktuMakan = $request->get('waktu_makan');

        $targetKalori = match ($waktuMakan) {
            'sarapan' => $kebutuhanEnergi->target_sarapan,
            'makan_siang' => $kebutuhanEnergi->target_makan_siang,
            'makan_malam' => $kebutuhanEnergi->target_makan_malam,
            'camilan' => $kebutuhanEnergi->target_snack,
            default => null,
        };

        $namaWaktuMakan = match ($waktuMakan) {
            'sarapan' => 'Sarapan',
            'makan_siang' => 'Makan Siang',
            'makan_malam' => 'Makan Malam',
            'camilan' => 'Camilan',
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | 3. Jika waktu makan belum dipilih
        |--------------------------------------------------------------------------
        */

        if (!$waktuMakan || $targetKalori === null) {
            return view('rekomendasi.index', [
                'kebutuhanEnergi' => $kebutuhanEnergi,
                'waktuMakan' => null,
                'namaWaktuMakan' => null,
                'targetKalori' => null,
                'targetKaloriHarian' =>
                    $kebutuhanEnergi->target_kalori_harian,

                'batasBawah' => null,
                'batasAtas' => null,

                'targetKarbohidratHarianMin' => null,
                'targetKarbohidratHarianMax' => null,
                'targetProteinHarianMin' => null,
                'targetProteinHarianMax' => null,
                'targetLemakHarianMin' => null,
                'targetLemakHarianMax' => null,

                'targetKarbohidratMin' => null,
                'targetKarbohidratMax' => null,
                'targetProteinMin' => null,
                'targetProteinMax' => null,
                'targetLemakMin' => null,
                'targetLemakMax' => null,

                'hasilRekomendasi' => collect(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Target kalori harian dan waktu makan
        |--------------------------------------------------------------------------
        */

        $targetKaloriHarian = (float)
            $kebutuhanEnergi->target_kalori_harian;

        $targetKalori = (float) $targetKalori;

        /*
        |--------------------------------------------------------------------------
        | 5. Persentase AMDR
        |--------------------------------------------------------------------------
        */

        $amdrKarbohidratMin = 0.45;
        $amdrKarbohidratMax = 0.65;

        $amdrProteinMin = 0.10;
        $amdrProteinMax = 0.35;

        $amdrLemakMin = 0.20;
        $amdrLemakMax = 0.35;

        /*
        |--------------------------------------------------------------------------
        | 6. Menghitung target makronutrien harian dalam gram
        |--------------------------------------------------------------------------
        */

        $targetKarbohidratHarianMin =
            ($targetKaloriHarian * $amdrKarbohidratMin) / 4;

        $targetKarbohidratHarianMax =
            ($targetKaloriHarian * $amdrKarbohidratMax) / 4;

        $targetProteinHarianMin =
            ($targetKaloriHarian * $amdrProteinMin) / 4;

        $targetProteinHarianMax =
            ($targetKaloriHarian * $amdrProteinMax) / 4;

        $targetLemakHarianMin =
            ($targetKaloriHarian * $amdrLemakMin) / 9;

        $targetLemakHarianMax =
            ($targetKaloriHarian * $amdrLemakMax) / 9;

        /*
        |--------------------------------------------------------------------------
        | 7. Proporsi energi berdasarkan waktu makan
        |--------------------------------------------------------------------------
        */

        $proporsiWaktuMakan = match ($waktuMakan) {
            'sarapan' => 0.167,
            'makan_siang' => 0.305,
            'makan_malam' => 0.338,
            'camilan' => 0.190,
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | 8. Target makronutrien per waktu makan
        |--------------------------------------------------------------------------
        */

        $targetKarbohidratMin =
            $targetKarbohidratHarianMin * $proporsiWaktuMakan;

        $targetKarbohidratMax =
            $targetKarbohidratHarianMax * $proporsiWaktuMakan;

        $targetProteinMin =
            $targetProteinHarianMin * $proporsiWaktuMakan;

        $targetProteinMax =
            $targetProteinHarianMax * $proporsiWaktuMakan;

        $targetLemakMin =
            $targetLemakHarianMin * $proporsiWaktuMakan;

        $targetLemakMax =
            $targetLemakHarianMax * $proporsiWaktuMakan;

        /*
        |--------------------------------------------------------------------------
        | 9. Pembulatan target makronutrien
        |--------------------------------------------------------------------------
        */

        $targetKarbohidratMin = round($targetKarbohidratMin, 2);
        $targetKarbohidratMax = round($targetKarbohidratMax, 2);

        $targetProteinMin = round($targetProteinMin, 2);
        $targetProteinMax = round($targetProteinMax, 2);

        $targetLemakMin = round($targetLemakMin, 2);
        $targetLemakMax = round($targetLemakMax, 2);

        /*
        |--------------------------------------------------------------------------
        | 10. Health-Based Filtering: rentang kalori ±20%
        |--------------------------------------------------------------------------
        */

        $toleransi = 0.20;

        $batasBawah = $targetKalori * (1 - $toleransi);
        $batasAtas = $targetKalori * (1 + $toleransi);

        /*
        |--------------------------------------------------------------------------
        | 11. Mapping waktu makan ke nilai database
        |--------------------------------------------------------------------------
        */

        $waktuMakanDatabase = match ($waktuMakan) {
            'sarapan' => 'sarapan',
            'makan_siang' => 'makan siang',
            'makan_malam' => 'makan malam',
            'camilan' => 'cemilan',
            default => null,
        };

        /*
        |--------------------------------------------------------------------------
        | 12. Mengambil makanan berdasarkan waktu makan
        |--------------------------------------------------------------------------
        */

        $makananWaktu = Makanan::whereRaw(
            'FIND_IN_SET(?, waktu_makan)',
            [$waktuMakanDatabase]
        )->get();

        /*
        |--------------------------------------------------------------------------
        | 13. Filter makanan berdasarkan target kalori
        |--------------------------------------------------------------------------
        */

        $makananKandidat = $makananWaktu
            ->filter(function ($makanan) use (
                $batasBawah,
                $batasAtas
            ) {
                return (float) $makanan->kalori >= $batasBawah
                    && (float) $makanan->kalori <= $batasAtas;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 14. Mengambil preferensi makanan pengguna
        |--------------------------------------------------------------------------
        */

        $preferensi = PreferensiMakanan::with('makanan')
            ->where('user_id', $user->id)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 15. Membentuk profil preferensi pengguna
        |--------------------------------------------------------------------------
        */

        $profilPreferensi = [];

        foreach ($this->fiturMakanan as $fitur) {
            $jumlahAktif = 0;

            foreach ($preferensi as $item) {
                if (
                    $item->makanan &&
                    (int) $item->makanan->{$fitur} === 1
                ) {
                    $jumlahAktif++;
                }
            }

            $profilPreferensi[$fitur] =
                $preferensi->count() > 0
                    ? $jumlahAktif / $preferensi->count()
                    : 0;
        }

        /*
        |--------------------------------------------------------------------------
        | 16. Content-Based Filtering: Cosine Similarity
        |--------------------------------------------------------------------------
        */

        $makananKandidat->each(function ($makanan) use (
            $profilPreferensi
        ) {
            $dotProduct = 0;
            $normProfil = 0;
            $normMakanan = 0;

            foreach ($this->fiturMakanan as $fitur) {
                $nilaiProfil =
                    $profilPreferensi[$fitur] ?? 0;

                $nilaiMakanan =
                    (int) ($makanan->{$fitur} ?? 0);

                $dotProduct +=
                    $nilaiProfil * $nilaiMakanan;

                $normProfil += pow($nilaiProfil, 2);
                $normMakanan += pow($nilaiMakanan, 2);
            }

            if ($normProfil > 0 && $normMakanan > 0) {
                $similarity = $dotProduct /
                    (
                        sqrt($normProfil) *
                        sqrt($normMakanan)
                    );
            } else {
                $similarity = 0;
            }

            $makanan->similarity = round($similarity, 4);
        });

        /*
        |--------------------------------------------------------------------------
        | 17. Macro Suitability Score
        |--------------------------------------------------------------------------
        */

        $hitungSkorMakro = function (
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

                return max(
                    0.0,
                    1.0 - (($batasMin - $nilai) / $batasMin)
                );
            }

            if ($batasMax <= 0) {
                return 0.0;
            }

            return max(
                0.0,
                1.0 - (($nilai - $batasMax) / $batasMax)
            );
        };

        $makananKandidat->each(function ($makanan) use (
            $targetKarbohidratMin,
            $targetKarbohidratMax,
            $targetProteinMin,
            $targetProteinMax,
            $targetLemakMin,
            $targetLemakMax,
            $hitungSkorMakro
        ) {
            $karbohidrat = (float) ($makanan->karbohidrat ?? 0);
            $protein = (float) ($makanan->protein ?? 0);
            $lemak = (float) ($makanan->lemak ?? 0);

            $scoreKarbohidrat = $hitungSkorMakro(
                $karbohidrat,
                (float) $targetKarbohidratMin,
                (float) $targetKarbohidratMax
            );

            $scoreProtein = $hitungSkorMakro(
                $protein,
                (float) $targetProteinMin,
                (float) $targetProteinMax
            );

            $scoreLemak = $hitungSkorMakro(
                $lemak,
                (float) $targetLemakMin,
                (float) $targetLemakMax
            );

            $macroScore = (
                $scoreKarbohidrat +
                $scoreProtein +
                $scoreLemak
            ) / 3;

            $makanan->score_karbohidrat =
                round($scoreKarbohidrat, 4);

            $makanan->score_protein =
                round($scoreProtein, 4);

            $makanan->score_lemak =
                round($scoreLemak, 4);

            $makanan->macro_score =
                round($macroScore, 4);
        });

        /*
        |--------------------------------------------------------------------------
        | 18. Menghitung Hybrid Score
        |--------------------------------------------------------------------------
        */

        $weightCbf = 0.5;
        $weightMacro = 0.5;

        $makananKandidat->each(function ($makanan) use (
            $weightCbf,
            $weightMacro
        ) {
            $similarity = (float) ($makanan->similarity ?? 0);
            $macroScore = (float) ($makanan->macro_score ?? 0);

            $hybridScore =
                ($weightCbf * $similarity) +
                ($weightMacro * $macroScore);

            $makanan->hybrid_score =
                round($hybridScore, 4);
        });

        /*
        |--------------------------------------------------------------------------
        | 19. Ranking dan Top-5
        |--------------------------------------------------------------------------
        */

        $hasilRekomendasi = $makananKandidat
            ->sortByDesc('hybrid_score')
            ->take(5)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 20. Mengirim data ke Blade
        |--------------------------------------------------------------------------
        */

        return view('rekomendasi.index', compact(
            'kebutuhanEnergi',
            'waktuMakan',
            'namaWaktuMakan',
            'targetKalori',
            'targetKaloriHarian',
            'batasBawah',
            'batasAtas',
            'targetKarbohidratHarianMin',
            'targetKarbohidratHarianMax',
            'targetProteinHarianMin',
            'targetProteinHarianMax',
            'targetLemakHarianMin',
            'targetLemakHarianMax',
            'targetKarbohidratMin',
            'targetKarbohidratMax',
            'targetProteinMin',
            'targetProteinMax',
            'targetLemakMin',
            'targetLemakMax',
            'hasilRekomendasi'
        ));
    }

    /**
     * Menyimpan hasil rekomendasi ke tabel riwayat dan detail.
     *
     * Riwayat yang identik (waktu makan, target energi, dan makanan hasil
     * rekomendasi sama) tidak disimpan lagi selama belum 24 jam.
     */
    public function simpanRiwayat(Request $request)
    {
        $validated = $request->validate([
            'waktu_makan' => [
                'required',
                'in:sarapan,makan_siang,makan_malam,camilan',
            ],
        ]);

        $waktuMakan = $validated['waktu_makan'];

        /*
        |--------------------------------------------------------------------------
        | 1. Hitung kembali rekomendasi untuk waktu makan yang dipilih
        |--------------------------------------------------------------------------
        */

        $response = $this->index(
            Request::create('/rekomendasi', 'GET', [
                'waktu_makan' => $waktuMakan,
            ])
        );

        if (!$response instanceof View) {
            return $response;
        }

        $data = $response->getData();

        $hasilRekomendasi = $data['hasilRekomendasi'] ?? collect();
        $targetKalori = $data['targetKalori'] ?? null;

        $kembali = redirect()->route('rekomendasi.index', [
            'waktu_makan' => $waktuMakan,
        ]);

        if (
            $hasilRekomendasi->isEmpty() ||
            $targetKalori === null
        ) {
            return $kembali->with(
                'error',
                'Tidak ada rekomendasi yang dapat disimpan.'
            );
        }

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | 2. Daftar makanan hasil rekomendasi (urutan ranking dipertahankan)
        |--------------------------------------------------------------------------
        |
        | Jika tinggi badan, berat badan, atau preferensi makanan berubah,
        | target energi dan/atau makanan yang direkomendasikan ikut berubah,
        | sehingga riwayat otomatis dianggap berbeda.
        */

        $idHasil = $hasilRekomendasi
            ->pluck('id_makanan')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | 3. Simpan dalam satu transaksi, kecuali riwayat identik < 24 jam
        |--------------------------------------------------------------------------
        */

        $tersimpan = DB::transaction(function () use (
            $user,
            $waktuMakan,
            $targetKalori,
            $hasilRekomendasi,
            $idHasil
        ) {
            // Kunci baris pengguna agar dua permintaan bersamaan (klik ganda)
            // tidak sama-sama lolos pengecekan.
            User::whereKey($user->id)->lockForUpdate()->first();

            if (RiwayatRekomendasi::sudahTersimpan(
                $user->id,
                $waktuMakan,
                (float) $targetKalori,
                $idHasil
            )) {
                return false;
            }

            /*
             * Membuat ID riwayat yang unik.
             * Panjang ID: 20 karakter.
             */
            do {
                $idRekomendasi =
                    'RKM' . Str::upper(Str::random(17));
            } while (
                RiwayatRekomendasi::where(
                    'id_rekomendasi',
                    $idRekomendasi
                )->exists()
            );

            /*
             * Menyimpan data utama riwayat rekomendasi.
             */
            RiwayatRekomendasi::create([
                'id_rekomendasi' => $idRekomendasi,
                'user_id' => $user->id,
                'waktu_makan' => $waktuMakan,
                'target_energi' => $targetKalori,
            ]);

            /*
             * Menyimpan setiap makanan dan skor algoritmanya.
             */
            foreach ($hasilRekomendasi as $index => $makanan) {
                /*
                 * Membuat ID detail yang unik.
                 * Panjang ID: 20 karakter.
                 */
                do {
                    $idDetail =
                        'DTL' . Str::upper(Str::random(17));
                } while (
                    DetailRekomendasi::where(
                        'id_detail',
                        $idDetail
                    )->exists()
                );

                DetailRekomendasi::create([
                    'id_detail' => $idDetail,
                    'id_rekomendasi' => $idRekomendasi,
                    'id_makanan' => $makanan->id_makanan,
                    'ranking' => $index + 1,
                    'skor_similarity' =>
                        $makanan->similarity ?? 0,
                    'macro_score' =>
                        $makanan->macro_score ?? 0,
                    'hybrid_score' =>
                        $makanan->hybrid_score ?? 0,
                ]);
            }

            return true;
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Kembali ke halaman rekomendasi dengan pesan
        |--------------------------------------------------------------------------
        */

        if (!$tersimpan) {
            return $kembali->with(
                'info',
                'Riwayat ini sudah tersimpan.'
            );
        }

        return $kembali->with(
            'success',
            'Riwayat berhasil disimpan.'
        );
    }
}