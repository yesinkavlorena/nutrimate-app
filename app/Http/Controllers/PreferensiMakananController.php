<?php

namespace App\Http\Controllers;

use App\Models\Makanan;
use App\Models\PreferensiMakanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PreferensiMakananController extends Controller
{
    /**
     * Halaman pilih preferensi makanan pertama kali
     */
    public function create()
    {
        $makanan = Makanan::orderBy('nama_makanan')->get();

        return view('preferensi.create', compact('makanan'));
    }

    /**
     * Simpan preferensi makanan pertama kali
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'makanan' => 'required|array|size:7',
            'makanan.*' => 'required|distinct|exists:makanan,id_makanan',
        ]);

        $userId = Auth::id();

        DB::transaction(function () use ($validated, $userId) {

            // Hapus preferensi lama jika ada
            PreferensiMakanan::where('user_id', $userId)->delete();

            // Simpan 7 makanan
            foreach ($validated['makanan'] as $makananId) {

                PreferensiMakanan::create([
                    'id' => 'PREF' . strtoupper(Str::random(12)),
                    'user_id' => $userId,
                    'makanan_id' => $makananId,
                ]);
            }
        });

        // Setelah simpan, kembali ke halaman Profil
        return redirect()
            ->route('profil.create')
            ->with('success', 'Preferensi makanan berhasil disimpan.');
    }

    /**
     * Halaman edit preferensi makanan
     */
    public function edit()
    {
        $userId = Auth::id();

        // Ambil semua makanan
        $makanan = Makanan::orderBy('nama_makanan')->get();

        // Ambil makanan yang sudah dipilih user
        $selected = PreferensiMakanan::where('user_id', $userId)
            ->pluck('makanan_id')
            ->toArray();

        // Pastikan selalu berupa array
        $selected = $selected ?? [];

        return view('preferensi.edit', compact(
            'makanan',
            'selected'
        ));
    }

    /**
     * Update preferensi makanan
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'makanan' => 'required|array|size:7',
            'makanan.*' => 'required|distinct|exists:makanan,id_makanan',
        ]);

        $userId = Auth::id();

        DB::transaction(function () use ($validated, $userId) {

            // Hapus preferensi lama
            PreferensiMakanan::where('user_id', $userId)->delete();

            // Simpan preferensi baru
            foreach ($validated['makanan'] as $makananId) {

                PreferensiMakanan::create([
                    'id' => 'PREF' . strtoupper(Str::random(12)),
                    'user_id' => $userId,
                    'makanan_id' => $makananId,
                ]);
            }
        });

        // Setelah update, kembali ke halaman Profil
        return redirect()
            ->route('profil.create')
            ->with('success', 'Preferensi makanan berhasil diperbarui.');
    }
}