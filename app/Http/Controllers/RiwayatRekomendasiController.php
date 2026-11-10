<?php

namespace App\Http\Controllers;

use App\Models\RiwayatRekomendasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiwayatRekomendasiController extends Controller
{
    public function index()
    {
        $riwayat = RiwayatRekomendasi::with([
            'detailRekomendasi.makanan'
        ])
            ->where('user_id', Auth::user()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('riwayat.index', compact('riwayat'));
    }

    /**
     * Menghapus satu riwayat beserta detail makanannya.
     */
    public function destroy(string $id)
    {
        // Hanya riwayat milik pengguna yang sedang login.
        // Riwayat milik orang lain menghasilkan 404.
        $riwayat = RiwayatRekomendasi::where('id_rekomendasi', $id)
            ->where('user_id', Auth::user()->id)
            ->firstOrFail();

        DB::transaction(function () use ($riwayat) {
            $riwayat->detailRekomendasi()->delete();
            $riwayat->delete();
        });

        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Riwayat berhasil dihapus.');
    }
}