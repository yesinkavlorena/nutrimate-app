<?php

namespace App\Http\Controllers;

use App\Models\ProfilPengguna;
use App\Models\PreferensiMakanan;
use App\Models\KebutuhanEnergi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProfilPenggunaController extends Controller
{
    /**
     * Menampilkan summary profil pengguna.
     */
    public function create()
    {
        $user = Auth::user();

        $profil = ProfilPengguna::where('user_id', $user->id)->first();

        $preferensi = PreferensiMakanan::where('user_id', $user->id)
            ->with('makanan')
            ->get();

        $bmi = null;
        $kategoriBmi = null;

        if ($profil) {
            $tinggiMeter = $profil->tinggi_badan / 100;

            if ($tinggiMeter > 0) {
                $bmi = round(
                    $profil->berat_badan /
                    ($tinggiMeter * $tinggiMeter),
                    2
                );

                $kategoriBmi = $this->tentukanKategoriBmi($bmi);
            }
        }

        return view('profil.create', compact(
            'user',
            'profil',
            'preferensi',
            'bmi',
            'kategoriBmi'
        ));
    }


    /**
     * Menampilkan halaman input profil pertama kali.
     */
    public function input()
    {
        $user = Auth::user();

        return view('profil.input', compact('user'));
    }


    /**
     * Menyimpan profil pengguna pertama kali.
     */
    public function storeInitial(Request $request)
    {
        $validated = $this->validasiProfil($request);

        $userId = Auth::id();

        $profil = ProfilPengguna::where('user_id', $userId)->first();

        if (!$profil) {
            $profil = ProfilPengguna::create([
                'id' => 'PROF-' . strtoupper(Str::random(10)),
                'user_id' => $userId,
                'usia' => $validated['usia'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'berat_badan' => $validated['berat_badan'],
                'tinggi_badan' => $validated['tinggi_badan'],
                'aktivitas_fisik' => $validated['aktivitas_fisik'],
            ]);

            $this->hitungDanSimpanKebutuhanEnergi($profil);
        }

        return redirect()->route('preferensi.create');
    }


    /**
     * Menampilkan halaman edit profil.
     */
    public function edit()
    {
        $user = Auth::user();

        $profil = ProfilPengguna::where('user_id', $user->id)->first();

        return view('profil.edit', compact(
            'user',
            'profil'
        ));
    }


    /**
     * Menyimpan perubahan profil pengguna.
     */
    public function store(Request $request)
    {
        $validated = $this->validasiProfil($request);

        $userId = Auth::id();

        $profil = ProfilPengguna::where('user_id', $userId)->first();

        if ($profil) {
            $profil->update([
                'usia' => $validated['usia'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'berat_badan' => $validated['berat_badan'],
                'tinggi_badan' => $validated['tinggi_badan'],
                'aktivitas_fisik' => $validated['aktivitas_fisik'],
            ]);

            $profil->refresh();

            $message = 'Profil pengguna berhasil diperbarui.';
        } else {
            $profil = ProfilPengguna::create([
                'id' => 'PROF-' . strtoupper(Str::random(10)),
                'user_id' => $userId,
                'usia' => $validated['usia'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'berat_badan' => $validated['berat_badan'],
                'tinggi_badan' => $validated['tinggi_badan'],
                'aktivitas_fisik' => $validated['aktivitas_fisik'],
            ]);

            $message = 'Profil pengguna berhasil disimpan.';
        }

        // Hitung ulang kebutuhan energi
        $this->hitungDanSimpanKebutuhanEnergi($profil);

        return redirect()
            ->route('profil.create')
            ->with('success', $message);
    }


    /**
     * Validasi data profil pengguna.
     */
    private function validasiProfil(Request $request)
    {
        return $request->validate([
            'usia' => 'required|integer|min:1|max:120',

            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',

            'berat_badan' => 'required|numeric|min:1',

            'tinggi_badan' => 'required|numeric|min:1',

            // 3 tingkat aktivitas sesuai Tabel 2.3
            'aktivitas_fisik' => 'required|in:Ringan,Sedang,Berat',
        ]);
    }


    /**
     * Menentukan kategori BMI.
     *
     * Underweight   : < 18,5
     * Normal Weight : 18,5 - 22,9
     * Overweight    : 23 - 24,9
     * Obesity       : >= 25
     */
    private function tentukanKategoriBmi($bmi)
    {
        if ($bmi < 18.5) {
            return 'Underweight';
        }

        if ($bmi < 23) {
            return 'Normal Weight';
        }

        if ($bmi < 25) {
            return 'Overweight';
        }

        return 'Obesity';
    }


    /**
     * Menghitung dan menyimpan kebutuhan energi pengguna.
     *
     * Perhitungan:
     * 1. BMI
     * 2. BMR
     * 3. TDEE
     * 4. Target energi berdasarkan waktu makan
     */
    private function hitungDanSimpanKebutuhanEnergi($profil)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. DATA PROFIL
        |--------------------------------------------------------------------------
        */

        $berat = $profil->berat_badan;
        $tinggi = $profil->tinggi_badan;
        $usia = $profil->usia;


        /*
        |--------------------------------------------------------------------------
        | 2. BMI
        |--------------------------------------------------------------------------
        */

        $tinggiMeter = $tinggi / 100;

        $bmi = round(
            $berat / ($tinggiMeter * $tinggiMeter),
            2
        );

        $kategoriBmi = $this->tentukanKategoriBmi($bmi);


        /*
        |--------------------------------------------------------------------------
        | 3. BMR
        |--------------------------------------------------------------------------
        */

        if ($profil->jenis_kelamin === 'Laki-laki') {
            $bmr = 66
                + (13.7 * $berat)
                + (5 * $tinggi)
                - (6.8 * $usia);
        } else {
            $bmr = 655
                + (9.6 * $berat)
                + (1.8 * $tinggi)
                - (4.7 * $usia);
        }

        $bmr = round($bmr, 2);


        /*
        |--------------------------------------------------------------------------
        | 4. FAKTOR AKTIVITAS FISIK (Tabel 2.3)
        |--------------------------------------------------------------------------
        | Ringan = 1,53 | Sedang = 1,76 | Berat = 2,25
        */

        $faktorAktivitas = match ($profil->aktivitas_fisik) {
            'Sedentary', 'Ringan' => 1.53,
            'Sedang' => 1.76,
            'Berat', 'Sangat Berat' => 2.25, // 'Sedentary' & 'Sangat Berat' hanya untuk data lama
            default => 1.53,
        };


        /*
        |--------------------------------------------------------------------------
        | 5. TDEE
        |--------------------------------------------------------------------------
        */

        $tdee = round(
            $bmr * $faktorAktivitas,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | 6. DISTRIBUSI ENERGI BERDASARKAN WAKTU MAKAN
        |--------------------------------------------------------------------------
        |
        | Sarapan     = 16,7%
        | Makan Siang = 30,5%
        | Makan Malam = 33,8%
        | Snack       = 19,0%
        |--------------------------------------------------------------------------
        */

        $distribusiMakan = [
            'sarapan' => 0.167,
            'makan_siang' => 0.305,
            'makan_malam' => 0.338,
            'snack' => 0.190,
        ];


        /*
        |--------------------------------------------------------------------------
        | 7. TARGET KALORI PER WAKTU MAKAN
        |--------------------------------------------------------------------------
        */

        $targetSarapan = round(
            $tdee * $distribusiMakan['sarapan'],
            2
        );

        $targetMakanSiang = round(
            $tdee * $distribusiMakan['makan_siang'],
            2
        );

        $targetMakanMalam = round(
            $tdee * $distribusiMakan['makan_malam'],
            2
        );

        $targetSnack = round(
            $tdee * $distribusiMakan['snack'],
            2
        );


        /*
        |--------------------------------------------------------------------------
        | 8. DATA KEBUTUHAN ENERGI
        |--------------------------------------------------------------------------
        */

        $data = [
            'bmi' => $bmi,

            'kategori_bmi' => $kategoriBmi,

            'bmr' => $bmr,

            'tdee' => $tdee,

            'target_kalori_harian' => $tdee,

            // Target energi per waktu makan
            'target_sarapan' => $targetSarapan,

            'target_makan_siang' => $targetMakanSiang,

            'target_makan_malam' => $targetMakanMalam,

            'target_snack' => $targetSnack,
        ];


        /*
        |--------------------------------------------------------------------------
        | 9. SIMPAN / UPDATE
        |--------------------------------------------------------------------------
        */

        $kebutuhanEnergi = KebutuhanEnergi::where(
            'user_id',
            $profil->user_id
        )->first();

        if ($kebutuhanEnergi) {

            $kebutuhanEnergi->update($data);

        } else {

            KebutuhanEnergi::create(
                array_merge(
                    [
                        'id' => 'ENE-' . strtoupper(Str::random(16)),
                        'user_id' => $profil->user_id,
                    ],
                    $data
                )
            );
        }
    }
}