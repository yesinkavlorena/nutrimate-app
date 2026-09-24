<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Memproses login.
     */
    public function store(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Mencoba login
        if (Auth::attempt($credentials)) {

            // Membuat session ID baru
            $request->session()->regenerate();

            return redirect('/dashboard')
                ->with('success', 'Login berhasil.');
        }

        // Jika email atau password salah
        return back()
            ->withErrors([
                'email' => 'Email atau password salah.',
            ])
            ->onlyInput('email');
    }

    /**
     * Logout.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Berhasil logout.');
    }
}