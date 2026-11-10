<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfilPenggunaController;
use App\Http\Controllers\PreferensiMakananController;
use App\Http\Controllers\InformasiGiziController;
use App\Http\Controllers\KebutuhanEnergiController;
use App\Http\Controllers\RekomendasiController;
use App\Http\Controllers\RiwayatRekomendasiController;


/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home');
})->name('home');


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::get('/register', [RegisterController::class, 'create'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store');


Route::get('/login', [LoginController::class, 'create'])
    ->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');


Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| PROFIL PENGGUNA
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profil', [ProfilPenggunaController::class, 'create'])
        ->name('profil.create');

    Route::get('/profil/input', [ProfilPenggunaController::class, 'input'])
        ->name('profil.input');

    Route::post('/profil/input', [ProfilPenggunaController::class, 'storeInitial'])
        ->name('profil.storeInitial');

    Route::get('/profil/edit', [ProfilPenggunaController::class, 'edit'])
        ->name('profil.edit');

    Route::post('/profil', [ProfilPenggunaController::class, 'store'])
        ->name('profil.store');
    
    
});


/*
|--------------------------------------------------------------------------
| PREFERENSI MAKANAN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/preferensi', [PreferensiMakananController::class, 'create'])
        ->name('preferensi.create');

    Route::post('/preferensi', [PreferensiMakananController::class, 'store'])
        ->name('preferensi.store');

    Route::get('/preferensi/edit', [PreferensiMakananController::class, 'edit'])
        ->name('preferensi.edit');

    Route::post('/preferensi/update', [PreferensiMakananController::class, 'update'])
        ->name('preferensi.update');


Route::get('/rekomendasi', [RekomendasiController::class, 'index'])
    ->name('rekomendasi.index');

Route::post('/rekomendasi/simpan', [RekomendasiController::class, 'simpanRiwayat'])
    ->name('rekomendasi.simpan');

    Route::get('/riwayat', [RiwayatRekomendasiController::class, 'index'])
    ->name('riwayat.index');

    Route::get('/riwayat', [RiwayatRekomendasiController::class, 'index'])->name('riwayat.index');

Route::delete('/riwayat/{id}', [RiwayatRekomendasiController::class, 'destroy'])
    ->where('id', '[A-Za-z0-9]+')
    ->name('riwayat.destroy');

});


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | INFORMASI GIZI
    |--------------------------------------------------------------------------
    */

    Route::get('/informasi-gizi', [InformasiGiziController::class, 'index'])
        ->name('informasi.gizi');


    /*
    |--------------------------------------------------------------------------
    | REKOMENDASI MAKANAN
    |--------------------------------------------------------------------------
    */

    Route::get('/rekomendasi', [RekomendasiController::class, 'index'])
        ->name('rekomendasi.index');


    /*
    |--------------------------------------------------------------------------
    | HITUNG KEBUTUHAN ENERGI
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/hitung-kebutuhan-energi',
        [KebutuhanEnergiController::class, 'hitung']
    )
        ->name('kebutuhan-energi.hitung');

});
