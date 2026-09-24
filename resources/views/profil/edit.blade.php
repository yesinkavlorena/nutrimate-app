@extends('layouts.dashboard')

@section('title', 'Edit Profil - NutriMate')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">

        <div>

            <h1 class="h3 mb-1 text-gray-800">
                {{ $profil ? 'Edit Profil' : 'Isi Profil' }}
            </h1>

            <p class="mb-0 text-muted">
                Masukkan data diri kamu untuk membantu NutriMate memberikan rekomendasi makanan personal.
            </p>

        </div>

        <a href="{{ route('profil.create') }}"
           class="btn btn-secondary btn-sm">

            <i class="fas fa-arrow-left mr-1"></i>

            Kembali

        </a>

    </div>


    {{-- Error --}}
    @if($errors->any())

        <div class="alert alert-danger shadow-sm">

            <div class="font-weight-bold mb-2">

                <i class="fas fa-exclamation-circle mr-2"></i>

                Terdapat kesalahan:

            </div>

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    <div class="row justify-content-center">

        <div class="col-xl-8 col-lg-10">

            <div class="card shadow border-0 mb-4">

                <div class="card-header bg-white py-3">

                    <h6 class="m-0 font-weight-bold text-success">

                        <i class="fas fa-user-edit mr-2"></i>

                        Data Diri

                    </h6>

                </div>


                <div class="card-body">

                    <form
                        action="{{ route('profil.store') }}"
                        method="POST"
                    >

                        @csrf


                        {{-- USIA --}}
                        <div class="form-group">

                            <label
                                for="usia"
                                class="font-weight-bold"
                            >
                                Usia
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    id="usia"
                                    name="usia"
                                    class="form-control"
                                    placeholder="Masukkan usia"
                                    min="1"
                                    max="120"
                                    value="{{ old('usia', $profil->usia ?? '') }}"
                                    required
                                >

                                <div class="input-group-append">

                                    <span class="input-group-text">
                                        Tahun
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- JENIS KELAMIN --}}
                        <div class="form-group">

                            <label
                                for="jenis_kelamin"
                                class="font-weight-bold"
                            >
                                Jenis Kelamin
                            </label>

                            <select
                                id="jenis_kelamin"
                                name="jenis_kelamin"
                                class="form-control"
                                required
                            >

                                <option value="">
                                    -- Pilih Jenis Kelamin --
                                </option>

                                <option
                                    value="Laki-laki"
                                    {{ old('jenis_kelamin', $profil->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}
                                >
                                    Laki-laki
                                </option>

                                <option
                                    value="Perempuan"
                                    {{ old('jenis_kelamin', $profil->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}
                                >
                                    Perempuan
                                </option>

                            </select>

                        </div>


                        <div class="row">

                            {{-- BERAT BADAN --}}
                            <div class="col-md-6">

                                <div class="form-group">

                                    <label
                                        for="berat_badan"
                                        class="font-weight-bold"
                                    >
                                        Berat Badan
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="number"
                                            id="berat_badan"
                                            name="berat_badan"
                                            class="form-control"
                                            placeholder="Contoh: 55"
                                            min="1"
                                            step="0.01"
                                            value="{{ old('berat_badan', $profil->berat_badan ?? '') }}"
                                            required
                                        >

                                        <div class="input-group-append">

                                            <span class="input-group-text">
                                                kg
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- TINGGI BADAN --}}
                            <div class="col-md-6">

                                <div class="form-group">

                                    <label
                                        for="tinggi_badan"
                                        class="font-weight-bold"
                                    >
                                        Tinggi Badan
                                    </label>

                                    <div class="input-group">

                                        <input
                                            type="number"
                                            id="tinggi_badan"
                                            name="tinggi_badan"
                                            class="form-control"
                                            placeholder="Contoh: 165"
                                            min="1"
                                            step="0.01"
                                            value="{{ old('tinggi_badan', $profil->tinggi_badan ?? '') }}"
                                            required
                                        >

                                        <div class="input-group-append">

                                            <span class="input-group-text">
                                                cm
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- AKTIVITAS --}}
                        <div class="form-group">

                            <label
                                for="aktivitas_fisik"
                                class="font-weight-bold"
                            >
                                Tingkat Aktivitas Fisik
                            </label>

                            <select
                                id="aktivitas_fisik"
                                name="aktivitas_fisik"
                                class="form-control"
                                required
                            >

                                <option value="">
                                    -- Pilih Tingkat Aktivitas --
                                </option>

                                <option
                                    value="Sedentary"
                                    {{ old('aktivitas_fisik', $profil->aktivitas_fisik ?? '') == 'Sedentary' ? 'selected' : '' }}
                                >
                                    Sedentary - Sangat sedikit aktivitas
                                </option>

                                <option
                                    value="Ringan"
                                    {{ old('aktivitas_fisik', $profil->aktivitas_fisik ?? '') == 'Ringan' ? 'selected' : '' }}
                                >
                                    Ringan - Aktivitas ringan
                                </option>

                                <option
                                    value="Sedang"
                                    {{ old('aktivitas_fisik', $profil->aktivitas_fisik ?? '') == 'Sedang' ? 'selected' : '' }}
                                >
                                    Sedang - Aktivitas sedang
                                </option>

                                <option
                                    value="Berat"
                                    {{ old('aktivitas_fisik', $profil->aktivitas_fisik ?? '') == 'Berat' ? 'selected' : '' }}
                                >
                                    Berat - Aktivitas berat
                                </option>

                                <option
                                    value="Sangat Berat"
                                    {{ old('aktivitas_fisik', $profil->aktivitas_fisik ?? '') == 'Sangat Berat' ? 'selected' : '' }}
                                >
                                    Sangat Berat - Aktivitas sangat berat
                                </option>

                            </select>

                            <small class="form-text text-muted">

                                Pilih tingkat aktivitas yang paling sesuai
                                dengan aktivitas sehari-hari kamu.

                            </small>

                        </div>


                        <hr>


                        {{-- BUTTON --}}
                        <div class="d-flex justify-content-end">

                            <a
                                href="{{ route('profil.create') }}"
                                class="btn btn-light mr-2"
                            >

                                Batal

                            </a>


                            <button
                                type="submit"
                                class="btn btn-success"
                            >

                                <i class="fas fa-save mr-1"></i>

                                Simpan Profil

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection