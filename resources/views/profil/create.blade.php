@extends('layouts.dashboard')

@section('title', 'Profil Pengguna - NutriMate')

@section('content')

<div class="container-fluid">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">

        <div>

            <h1 class="h3 mb-1 text-gray-800">
                Profil Pengguna
            </h1>

            <p class="mb-0 text-muted">
                Informasi profil kamu di NutriMate.
            </p>

        </div>

    </div>


    {{-- =========================================================
         PESAN SUKSES
    ========================================================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm">

            <i class="fas fa-check-circle mr-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif


    {{-- =========================================================
         PROFILE
    ========================================================== --}}
    <div class="row">


        {{-- =====================================================
             KARTU PENGGUNA
        ====================================================== --}}
        <div class="col-xl-4 col-lg-5 mb-4">

            <div class="card shadow border-0 h-100">

                <div class="card-body text-center">


                    {{-- Avatar --}}
                    <div class="profile-avatar mb-3">

                        <i class="fas fa-user"></i>

                    </div>


                    <p class="text-muted mb-1">

                        Good Morning,

                    </p>


                    <h2 class="font-weight-bold text-success mb-2">

                        {{ $user->name }}

                    </h2>


                    <p class="text-muted">

                        {{ $user->email }}

                    </p>


                    <hr>


                    {{-- STATUS PROFIL --}}
                    @if($profil)

                        <div class="text-left">

                            <div class="small text-muted mb-1">

                                STATUS PROFIL

                            </div>

                            <span class="badge badge-success px-3 py-2">

                                <i class="fas fa-check mr-1"></i>

                                Profil Lengkap

                            </span>

                        </div>

                    @else

                        <div class="text-left">

                            <div class="small text-muted mb-1">

                                STATUS PROFIL

                            </div>

                            <span class="badge badge-warning px-3 py-2">

                                <i class="fas fa-exclamation mr-1"></i>

                                Belum Diisi

                            </span>

                        </div>

                    @endif


                    {{-- TOMBOL EDIT PROFIL --}}
                    <div class="mt-4">

                        <a href="{{ route('profil.edit') }}"
                           class="btn btn-success btn-block">

                            <i class="fas fa-edit mr-1"></i>

                            {{ $profil ? 'Edit Profil' : 'Isi Profil' }}

                        </a>

                    </div>


                </div>

            </div>

        </div>


        {{-- =====================================================
             DATA PROFIL
        ====================================================== --}}
        <div class="col-xl-8 col-lg-7 mb-4">

            <div class="card shadow border-0">

                <div class="card-header bg-white py-3">

                    <h6 class="m-0 font-weight-bold text-success">

                        <i class="fas fa-id-card mr-2"></i>

                        Informasi Profil

                    </h6>

                </div>


                <div class="card-body">


                    @if($profil)


                        {{-- =================================================
                             NAMA
                        ================================================== --}}
                        <div class="profile-info-box mb-3">

                            <div class="profile-label">

                                NAMA

                            </div>

                            <div class="profile-value">

                                {{ $user->name }}

                            </div>

                        </div>


                        {{-- =================================================
                             USIA & JENIS KELAMIN
                        ================================================== --}}
                        <div class="row">


                            {{-- Usia --}}
                            <div class="col-md-6 mb-3">

                                <div class="profile-info-box h-100">

                                    <div class="profile-label">

                                        USIA

                                    </div>

                                    <div class="profile-value">

                                        {{ $profil->usia }}

                                        <span class="profile-unit">

                                            Tahun

                                        </span>

                                    </div>

                                </div>

                            </div>


                            {{-- Jenis Kelamin --}}
                            <div class="col-md-6 mb-3">

                                <div class="profile-info-box h-100">

                                    <div class="profile-label">

                                        JENIS KELAMIN

                                    </div>

                                    <div class="profile-value">

                                        {{ $profil->jenis_kelamin }}

                                    </div>

                                </div>

                            </div>


                            {{-- =================================================
                                 BERAT BADAN
                            ================================================== --}}
                            <div class="col-md-6 mb-3">

                                <div class="profile-info-box h-100">

                                    <div class="profile-label">

                                        BERAT BADAN

                                    </div>

                                    <div class="profile-value">

                                        {{ $profil->berat_badan }}

                                        <span class="profile-unit">

                                            kg

                                        </span>

                                    </div>

                                </div>

                            </div>


                            {{-- =================================================
                                 TINGGI BADAN
                            ================================================== --}}
                            <div class="col-md-6 mb-3">

                                <div class="profile-info-box h-100">

                                    <div class="profile-label">

                                        TINGGI BADAN

                                    </div>

                                    <div class="profile-value">

                                        {{ $profil->tinggi_badan }}

                                        <span class="profile-unit">

                                            cm

                                        </span>

                                    </div>

                                </div>

                            </div>


                        </div>


                        {{-- =================================================
                             AKTIVITAS FISIK
                        ================================================== --}}
                        <div class="profile-info-box">

                            <div class="profile-label">

                                TINGKAT AKTIVITAS

                            </div>

                            <div class="profile-value mb-3">

                                {{ $profil->aktivitas_fisik }}

                            </div>


                            @php

                                $activityProgress = [

                                    'Sedentary' => 20,

                                    'Ringan' => 40,

                                    'Sedang' => 60,

                                    'Berat' => 80,

                                    'Sangat Berat' => 100,

                                ];

                                $progress =
                                    $activityProgress[$profil->aktivitas_fisik]
                                    ?? 0;

                            @endphp


                            <div class="progress"
                                 style="height: 10px;">

                                <div class="progress-bar bg-success"
                                     style="width: {{ $progress }}%;">

                                </div>

                            </div>

                        </div>


                    @else


                        {{-- =================================================
                             BELUM ADA PROFIL
                        ================================================== --}}
                        <div class="text-center py-5">

                            <i class="fas fa-user-plus fa-4x
                                      text-gray-300 mb-4">
                            </i>


                            <h5 class="font-weight-bold text-gray-800">

                                Profil Belum Diisi

                            </h5>


                            <p class="text-muted mb-4">

                                Lengkapi data profil kamu terlebih dahulu.

                            </p>


                            <a href="{{ route('profil.edit') }}"
                               class="btn btn-success">

                                <i class="fas fa-user-edit mr-1"></i>

                                Isi Profil

                            </a>

                        </div>

                    @endif


                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         PREFERENSI MAKANAN
         Ditampilkan setelah data profil
    ========================================================== --}}
    @if($profil)


        <div class="row">

            <div class="col-12 mb-4">

                <div class="card shadow border-0">


                    {{-- =================================================
                         HEADER PREFERENSI
                    ================================================== --}}
                    <div class="card-header bg-white py-3
                                d-flex justify-content-between
                                align-items-center">


                        <h6 class="m-0 font-weight-bold text-success">

                            <i class="fas fa-utensils mr-2"></i>

                            Preferensi Makanan

                        </h6>


                        {{-- =================================================
                             TOMBOL EDIT PREFERENSI
                        ================================================== --}}
                        <a href="{{ route('preferensi.edit') }}"
                           class="btn btn-success btn-sm">

                            <i class="fas fa-edit mr-1"></i>

                            Edit Preferensi Makanan

                        </a>


                    </div>


                    {{-- =================================================
                         ISI PREFERENSI
                    ================================================== --}}
                    <div class="card-body">


                        @if(isset($preferensi) && $preferensi->count() > 0)


                            <div class="row">


                                @foreach($preferensi as $item)


                                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">


                                        <div class="food-preference-card h-100">


                                            {{-- =================================================
                                                 GAMBAR MAKANAN
                                            ================================================== --}}
                                            <div class="food-preference-image">


                                                @if(
                                                    $item->makanan &&
                                                    $item->makanan->gambar_makanan
                                                )

                                                    <img
                                                        src="{{ $item->makanan->gambar_makanan }}"
                                                        alt="{{ $item->makanan->nama_makanan }}"
                                                    >

                                                @else

                                                    <div class="food-image-placeholder">

                                                        <i class="fas fa-utensils"></i>

                                                    </div>

                                                @endif


                                            </div>


                                            {{-- =================================================
                                                 INFORMASI MAKANAN
                                            ================================================== --}}
                                            <div class="profile-info-box">


                                                <div class="profile-label">

                                                    MAKANAN

                                                </div>


                                                <div class="font-weight-bold text-gray-800">

                                                    {{ $item->makanan->nama_makanan ?? 'Makanan tidak ditemukan' }}

                                                </div>


                                                {{-- Kalori --}}
                                                @if($item->makanan)

                                                    <div class="text-muted small mt-1">

                                                        {{ $item->makanan->kalori }} kkal

                                                    </div>

                                                @endif


                                            </div>


                                        </div>


                                    </div>


                                @endforeach


                            </div>


                        @else


                            {{-- =================================================
                                 BELUM ADA PREFERENSI
                            ================================================== --}}
                            <div class="text-center py-4">


                                <i class="fas fa-utensils fa-3x
                                          text-gray-300 mb-3">
                                </i>


                                <h6 class="font-weight-bold text-gray-800">

                                    Belum Ada Preferensi Makanan

                                </h6>


                                <p class="text-muted mb-3">

                                    Pilih 7 makanan favorit kamu untuk
                                    mendapatkan rekomendasi makanan
                                    yang lebih personal.

                                </p>


                                <a href="{{ route('preferensi.create') }}"
                                   class="btn btn-success btn-sm">

                                    <i class="fas fa-plus mr-1"></i>

                                    Pilih Makanan

                                </a>


                            </div>


                        @endif


                    </div>


                </div>

            </div>

        </div>


    @endif


</div>


{{-- =============================================================
     STYLE KHUSUS HALAMAN PROFIL
============================================================= --}}
@push('styles')

<style>


    /* =========================================================
       AVATAR
    ========================================================== */

    .profile-avatar {

        width: 90px;

        height: 90px;

        margin-left: auto;

        margin-right: auto;

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

        background: rgba(46, 125, 50, 0.12);

        color: #2E7D32;

        font-size: 40px;

    }


    /* =========================================================
       PROFILE INFO BOX
    ========================================================== */

    .profile-info-box {

        padding: 18px 20px;

        background: #f8f9fc;

        border-radius: 8px;

        border-left: 4px solid #2E7D32;

    }


    /* =========================================================
       LABEL
    ========================================================== */

    .profile-label {

        font-size: 11px;

        font-weight: 700;

        color: #858796;

        letter-spacing: 0.5px;

        margin-bottom: 5px;

    }


    /* =========================================================
       VALUE
    ========================================================== */

    .profile-value {

        font-size: 18px;

        font-weight: 700;

        color: #2E7D32;

    }


    /* =========================================================
       UNIT
    ========================================================== */

    .profile-unit {

        font-size: 13px;

        font-weight: 400;

        color: #858796;

    }


    /* =========================================================
       PROGRESS
    ========================================================== */

    .progress {

        background-color: #eaecf4;

        border-radius: 10px;

    }


    .progress-bar {

        border-radius: 10px;

    }


    /* =========================================================
       KARTU PREFERENSI MAKANAN
    ========================================================== */

    .food-preference-card {

        height: 100%;

        transition: all 0.2s ease;

    }


    .food-preference-card:hover {

        transform: translateY(-3px);

    }


    /* =========================================================
       GAMBAR MAKANAN
    ========================================================== */

    .food-preference-image {

        width: 100%;

        height: 180px;

        margin-bottom: 10px;

        border-radius: 10px;

        overflow: hidden;

        background-color: #e8f5e9;

    }


    .food-preference-image img {

        width: 100%;

        height: 100%;

        object-fit: cover;

        display: block;

        transition: transform 0.2s ease;

    }


    .food-preference-card:hover
    .food-preference-image img {

        transform: scale(1.03);

    }


    /* =========================================================
       PLACEHOLDER JIKA GAMBAR TIDAK TERSEDIA
    ========================================================== */

    .food-image-placeholder {

        width: 100%;

        height: 100%;

        display: flex;

        align-items: center;

        justify-content: center;

        background: #e8f5e9;

        color: #2E7D32;

        font-size: 35px;

    }


</style>

@endpush


@endsection