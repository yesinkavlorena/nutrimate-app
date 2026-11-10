<?php

namespace App\Http\Controllers;

use App\Models\ProfilPengguna;
use App\Models\KebutuhanEnergi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KebutuhanEnergiController extends Controller
{
    public function hitung()
    {
        // Mengambil ID user yang sedang login
        $userId = auth()->id();

        // Mengambil data profil pengguna
        $profil = ProfilPengguna::where('user_id', $userId)->first();

        // Pastikan profil sudah tersedia
        if (!$profil) {
            return redirect()
                ->route('profil.create')
                ->with('error', 'Silakan lengkapi profil terlebih dahulu.');
        }

        /*
        |--------------------------------------------------------------------------
        | 1. HITUNG BMI
        |--------------------------------------------------------------------------
        */

        $berat = $profil->berat_badan;
        $tinggiCm = $profil->tinggi_badan;

        // Konversi tinggi dari cm ke meter
        $tinggiMeter = $tinggiCm / 100;

        // Rumus BMI
        $bmi = $berat / ($tinggiMeter * $tinggiMeter);

        /*
        |--------------------------------------------------------------------------
        | 2. TENTUKAN KATEGORI BMI
        |--------------------------------------------------------------------------
        | Underweight   : < 18,5
        | Normal Weight : 18,5 - 22,9
        | Overweight    : 23 - 24,9
        | Obesity       : >= 25
        */

        if ($bmi < 18.5) {
            $kategoriBmi = 'Underweight';
        } elseif ($bmi < 23) {
            $kategoriBmi = 'Normal Weight';
        } elseif ($bmi < 25) {
            $kategoriBmi = 'Overweight';
        } else {
            $kategoriBmi = 'Obesity';
        }

        /*
        |--------------------------------------------------------------------------
        | 3. HITUNG BMR
        |--------------------------------------------------------------------------
        */

        $usia = $profil->usia;
        $jenisKelamin = $profil->jenis_kelamin;

        if ($jenisKelamin === 'Laki-laki') {

            $bmr = 66
                + (13.7 * $berat)
                + (5 * $tinggiCm)
                - (6.8 * $usia);

        } else {

            $bmr = 655
                + (9.6 * $berat)
                + (1.8 * $tinggiCm)
                - (4.7 * $usia);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. TENTUKAN FAKTOR AKTIVITAS (Tabel 2.3)
        |--------------------------------------------------------------------------
        | Ringan (Sedentary) = 1,53 | Sedang = 1,76 | Berat = 2,25
        */

        switch ($profil->aktivitas_fisik) {

            case 'Ringan':
                $faktorAktivitas = 1.53;
                break;

            case 'Sedang':
                $faktorAktivitas = 1.76;
                break;

            case 'Berat':
                $faktorAktivitas = 2.25;
                break;

            default:
                $faktorAktivitas = 1.53;
        }

        /*
        |--------------------------------------------------------------------------
        | 5. HITUNG TDEE
        |--------------------------------------------------------------------------
        */

        $tdee = $bmr * $faktorAktivitas;

        /*
        |--------------------------------------------------------------------------
        | 6. TARGET KALORI HARIAN
        |--------------------------------------------------------------------------
        */

        $targetKaloriHarian = $tdee;

        /*
        |--------------------------------------------------------------------------
        | 7. SIMPAN KE DATABASE
        |--------------------------------------------------------------------------
        */

        $kebutuhanEnergi = KebutuhanEnergi::firstOrNew(['user_id' => $userId]);

        // ID hanya dibuat sekali, saat record pertama kali dibuat
        if (!$kebutuhanEnergi->exists) {
            $kebutuhanEnergi->id = 'ENE' . strtoupper(Str::random(17));
        }

        $kebutuhanEnergi->fill([
            'bmi' => $bmi,
            'kategori_bmi' => $kategoriBmi,
            'bmr' => $bmr,
            'tdee' => $tdee,
            'target_kalori_harian' => $targetKaloriHarian,
        ]);

        $kebutuhanEnergi->save();

        /*
        |--------------------------------------------------------------------------
        | 8. KEMBALIKAN HASIL
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'Kebutuhan energi berhasil dihitung.',
            'data' => $kebutuhanEnergi
        ]);
    }
}