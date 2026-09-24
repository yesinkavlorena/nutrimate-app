<?php

namespace App\Http\Controllers;

use App\Models\ProfilPengguna;
use App\Models\PreferensiMakanan;
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

    // Mengambil profil pengguna
    $profil = ProfilPengguna::where('user_id', $user->id)->first();

    // Mengambil preferensi makanan pengguna
    $preferensi = PreferensiMakanan::where('user_id', $user->id)
        ->with('makanan')
        ->get();

    // Nilai awal BMI
    $bmi = null;
    $kategoriBmi = null;

    // Hitung BMI jika profil sudah tersedia
    if ($profil) {

        // Konversi tinggi badan dari cm ke meter
        $tinggiMeter = $profil->tinggi_badan / 100;

        // Perhitungan BMI
        $bmi = $profil->berat_badan /
               ($tinggiMeter * $tinggiMeter);

        // Pembulatan 2 angka desimal
        $bmi = round($bmi, 2);

        // Klasifikasi BMI
        if ($bmi < 18.5) {

            $kategoriBmi = 'Underweight';

        } elseif ($bmi <= 22.9) {

            $kategoriBmi = 'Normal Weight';

        } else {

            $kategoriBmi = 'Overweight';
        }
    }

    return view(
        'profil.create',
        compact(
            'user',
            'profil',
            'preferensi',
            'bmi',
            'kategoriBmi'
        )
    );
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
     *
     * Setelah berhasil disimpan, pengguna diarahkan
     * ke halaman pemilihan 7 makanan.
     */
    public function storeInitial(Request $request)
    {
        $validated = $request->validate([
            'usia' => 'required|integer|min:1|max:120',

            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',

            'berat_badan' => 'required|numeric|min:1',

            'tinggi_badan' => 'required|numeric|min:1',

            'aktivitas_fisik' => 'required|in:Sedentary,Ringan,Sedang,Berat,Sangat Berat',
        ]);


        $userId = Auth::id();


        // Pastikan profil belum pernah dibuat
        $profil = ProfilPengguna::where('user_id', $userId)->first();


        if (!$profil) {

            ProfilPengguna::create([
                'id' => 'PROF-' . strtoupper(Str::random(10)),
                'user_id' => $userId,
                'usia' => $validated['usia'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'berat_badan' => $validated['berat_badan'],
                'tinggi_badan' => $validated['tinggi_badan'],
                'aktivitas_fisik' => $validated['aktivitas_fisik'],
            ]);

        }


        // Setelah profil tersimpan,
        // lanjut ke halaman pilih makanan.
        return redirect()->route('preferensi.create');
    }


    /**
     * Menampilkan halaman edit profil.
     */
    public function edit()
    {
        $user = Auth::user();

        $profil = ProfilPengguna::where('user_id', $user->id)->first();

        return view('profil.edit', compact('user', 'profil'));
    }


    /**
     * Menyimpan perubahan profil pengguna.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usia' => 'required|integer|min:1|max:120',

            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',

            'berat_badan' => 'required|numeric|min:1',

            'tinggi_badan' => 'required|numeric|min:1',

            'aktivitas_fisik' => 'required|in:Sedentary,Ringan,Sedang,Berat,Sangat Berat',
        ]);


        // Mengambil ID user yang sedang login
        $userId = Auth::id();


        // Mencari profil pengguna
        $profil = ProfilPengguna::where('user_id', $userId)->first();


        if ($profil) {

            // Update profil yang sudah ada
            $profil->update([
                'usia' => $validated['usia'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'berat_badan' => $validated['berat_badan'],
                'tinggi_badan' => $validated['tinggi_badan'],
                'aktivitas_fisik' => $validated['aktivitas_fisik'],
            ]);

            $message = 'Profil pengguna berhasil diperbarui.';

        } else {

            // Jika belum ada, buat profil baru
            ProfilPengguna::create([
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


        return redirect()
            ->route('profil.create')
            ->with('success', $message);
    }
}