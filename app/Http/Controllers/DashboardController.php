<?php

namespace App\Http\Controllers;

use App\Models\ProfilPengguna;
use App\Models\Makanan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil data profil pengguna
        $profil = ProfilPengguna::where('user_id', $user->id)->first();

        // Nilai awal
        $bmi = null;
        $kategoriBmi = null;
        $bmr = null;
        $tdee = null;

        if ($profil) {



            $tinggiMeter = $profil->tinggi_badan / 100;

            if ($tinggiMeter > 0) {

                $bmi = $profil->berat_badan /
                       ($tinggiMeter * $tinggiMeter);

                $bmi = round($bmi, 2);


                if ($bmi < 18.5) {

                    $kategoriBmi = 'Underweight';

                } elseif ($bmi <= 22.9) {

                    $kategoriBmi = 'Normal Weight';

                } else {

                    $kategoriBmi = 'Overweight';
                }
            }




            $beratBadan = $profil->berat_badan;
            $tinggiBadan = $profil->tinggi_badan;
            $usia = $profil->usia;

            if ($profil->jenis_kelamin === 'Laki-laki') {

                // Rumus BMR pria
                $bmr = 66
                     + (13.7 * $beratBadan)
                     + (5 * $tinggiBadan)
                     - (6.8 * $usia);

            } elseif ($profil->jenis_kelamin === 'Perempuan') {

                
                $bmr = 655
                     + (9.6 * $beratBadan)
                     + (1.8 * $tinggiBadan)
                     - (4.7 * $usia);
            }

            if ($bmr !== null) {
                $bmr = round($bmr, 2);
            }



            if ($bmr !== null) {

                $faktorAktivitas = match ($profil->aktivitas_fisik) {

                    'Sangat Ringan' => 1.2,
                    'Ringan'        => 1.375,
                    'Sedang'        => 1.55,
                    'Berat'         => 1.725,
                    'Sangat Berat'  => 1.9,

                    default => null,
                };

                if ($faktorAktivitas !== null) {

                    $tdee = $bmr * $faktorAktivitas;

                    $tdee = round($tdee, 2);
                }
            }
        }




        $makanan = Makanan::orderBy('nama_makanan', 'asc')
            ->take(4)
            ->get();



        return view('dashboard.index', compact(
            'user',
            'profil',
            'bmi',
            'kategoriBmi',
            'bmr',
            'tdee',
            'makanan'
        ));
    }
}