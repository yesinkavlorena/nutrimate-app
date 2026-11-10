<?php

namespace App\Http\Controllers;

use App\Models\ProfilPengguna;
use App\Models\Makanan;
use App\Models\KebutuhanEnergi;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil data profil pengguna
        $profil = ProfilPengguna::where('user_id', $user->id)->first();

        // Ambil hasil kebutuhan energi dari database
        $kebutuhanEnergi = KebutuhanEnergi::where(
            'user_id',
            $user->id
        )->first();

        // Nilai awal
        $bmi = null;
        $kategoriBmi = null;
        $bmr = null;
        $tdee = null;
        $targetKaloriHarian = null;

        if ($kebutuhanEnergi) {

            $bmi = $kebutuhanEnergi->bmi;

            $kategoriBmi = $kebutuhanEnergi->kategori_bmi;

            $bmr = $kebutuhanEnergi->bmr;

            $tdee = $kebutuhanEnergi->tdee;

            $targetKaloriHarian =
                $kebutuhanEnergi->target_kalori_harian;
        }

        // Ambil 4 makanan untuk dashboard
        $makanan = Makanan::orderBy('nama_makanan', 'asc')
            ->take(4)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'profil',
            'kebutuhanEnergi',
            'bmi',
            'kategoriBmi',
            'bmr',
            'tdee',
            'targetKaloriHarian',
            'makanan'
        ));
    }
}