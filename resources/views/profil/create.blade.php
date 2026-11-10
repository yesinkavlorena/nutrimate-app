@extends('layouts.dashboard')

@section('title', 'Profil Pengguna - NutriMate')

@section('content')

<div class="container-fluid pf-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4 pf-hero">

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
                    data-bs-dismiss="alert"
                    aria-label="Tutup">

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

            <div class="card pf-panel shadow border-0 h-100">

                <div class="card-body text-center">


                    {{-- Avatar --}}
                    <div class="profile-avatar mb-3">

                        <i class="fas fa-user"></i>

                    </div>


                    @php
                        $jam = now()->timezone('Asia/Jakarta')->hour;
                        $sapaan = $jam < 11 ? 'Selamat Pagi'
                                : ($jam < 15 ? 'Selamat Siang'
                                : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));
                    @endphp

                    <p class="text-muted mb-1">{{ $sapaan }},</p>

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

            <div class="card pf-panel shadow border-0">

                <div class="card-header bg-white py-3">

                    <h6 class="m-0 font-weight-bold text-success">

                        <i class="fas fa-id-card mr-2"></i>

                        Informasi Profil

                    </h6>

                </div>


                <div class="card-body">


                    @if($profil)


                        {{-- NAMA --}}
                        <div class="profile-info-box mb-3">

                            <div class="profile-label">

                                NAMA

                            </div>

                            <div class="profile-value">

                                {{ $user->name }}

                            </div>

                        </div>


                        {{-- USIA, JENIS KELAMIN, BERAT, TINGGI --}}
                        <div class="row">


                            {{-- Usia --}}
                            <div class="col-md-6 mb-3">

                                <div class="profile-info-box pf-teal h-100">

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

                                <div class="profile-info-box pf-purple h-100">

                                    <div class="profile-label">

                                        JENIS KELAMIN

                                    </div>

                                    <div class="profile-value">

                                        {{ $profil->jenis_kelamin }}

                                    </div>

                                </div>

                            </div>


                            {{-- Berat Badan --}}
                            <div class="col-md-6 mb-3">

                                <div class="profile-info-box pf-orange h-100">

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


                            {{-- Tinggi Badan --}}
                            <div class="col-md-6 mb-3">

                                <div class="profile-info-box pf-yellow h-100">

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


                        {{-- AKTIVITAS FISIK (3 TINGKAT) --}}
                        <div class="profile-info-box">

                            <div class="profile-label">

                                TINGKAT AKTIVITAS

                            </div>

                            <div class="profile-value mb-3">

                                {{ $profil->aktivitas_fisik }}

                            </div>


                            @php

                                // Faktor aktivitas: Ringan 1,53 | Sedang 1,76 | Berat 2,25
                                $activityProgress = [

                                    'Ringan' => 33,

                                    'Sedang' => 66,

                                    'Berat' => 100,

                                    // Cadangan untuk data lama di database
                                    'Sedentary' => 33,

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


                        {{-- BELUM ADA PROFIL --}}
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

                <div class="card pf-panel shadow border-0">


                    {{-- HEADER PREFERENSI --}}
                    <div class="card-header bg-white py-3
                                d-flex justify-content-between
                                align-items-center">


                        <h6 class="m-0 font-weight-bold text-success">

                            <i class="fas fa-utensils mr-2"></i>

                            Preferensi Makanan

                        </h6>


                        {{-- TOMBOL EDIT PREFERENSI --}}
                        <a href="{{ route('preferensi.edit') }}"
                           class="btn btn-success btn-sm">

                            <i class="fas fa-edit mr-1"></i>

                            Edit Preferensi Makanan

                        </a>


                    </div>


                    {{-- ISI PREFERENSI --}}
                    <div class="card-body">


                        @if(isset($preferensi) && $preferensi->count() > 0)


                            <div class="row">


                                @foreach($preferensi as $item)


                                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">


                                        <div class="food-preference-card h-100">


                                            {{-- GAMBAR MAKANAN --}}
                                            <div class="food-preference-image">


                                                @if(
                                                    $item->makanan &&
                                                    $item->makanan->gambar_makanan
                                                )

                                                    <img
                                                        src="{{ $item->makanan->gambar_makanan }}"
                                                        alt="{{ $item->makanan->nama_makanan }}"
                                                        loading="lazy"
                                                    >

                                                @else

                                                    <div class="food-image-placeholder">

                                                        <i class="fas fa-utensils"></i>

                                                    </div>

                                                @endif


                                            </div>


                                            {{-- INFORMASI MAKANAN --}}
                                            <div class="profile-info-box">


                                                <div class="profile-label">

                                                    MAKANAN

                                                </div>


                                                <div class="font-weight-bold text-gray-800 food-pref-name">

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


                            {{-- BELUM ADA PREFERENSI --}}
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

@endsection

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/profil-show.css') }}?v={{ filemtime(public_path('css/profil-show.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/profil-show.js') }}?v={{ filemtime(public_path('js/profil-show.js')) }}" defer></script>
@endpush