<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfilPenggunaController;
use App\Http\Controllers\PreferensiMakananController;
use App\Http\Controllers\InformasiGiziController;

Route::get('/', function () {
    return view('home');
})->name('home');

// Register
Route::get('/register', [RegisterController::class, 'create'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store');


// Login
Route::get('/login', [LoginController::class, 'create'])
    ->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');



Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');



Route::get('/profil', [ProfilPenggunaController::class, 'create'])
    ->middleware('auth')
    ->name('profil.create');


Route::get('/profil/input', [ProfilPenggunaController::class, 'input'])
    ->middleware('auth')
    ->name('profil.input');


Route::post('/profil/input', [ProfilPenggunaController::class, 'storeInitial'])
    ->middleware('auth')
    ->name('profil.storeInitial');


Route::get('/profil/edit', [ProfilPenggunaController::class, 'edit'])
    ->middleware('auth')
    ->name('profil.edit');


Route::post('/profil', [ProfilPenggunaController::class, 'store'])
    ->middleware('auth')
    ->name('profil.store');

Route::get('/preferensi', [PreferensiMakananController::class, 'create'])
    ->middleware('auth')
    ->name('preferensi.create');

Route::post('/preferensi', [PreferensiMakananController::class, 'store'])
    ->middleware('auth')
    ->name('preferensi.store');

Route::get('/preferensi/edit', [PreferensiMakananController::class, 'edit'])
    ->middleware('auth')
    ->name('preferensi.edit');

Route::post('/preferensi/update', [PreferensiMakananController::class, 'update'])
    ->middleware('auth')
    ->name('preferensi.update');

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Informasi Gizi Makanan
    Route::get('/informasi-gizi', [InformasiGiziController::class, 'index'])
        ->name('informasi.gizi');

});