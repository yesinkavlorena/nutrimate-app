@extends('layouts.app')

@section('title', 'Register - NutriMate')

@section('body-class', 'lg-body')

{{-- Gaya dasar dipakai bersama halaman login, register.css hanya tambahan --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/register.js') }}" defer></script>
@endpush

@section('content')

<div class="lg-page">

    {{-- =========================================================
         LATAR BELAKANG (gradasi hijau, gelembung, ikon makanan)
    ========================================================== --}}
    <div class="lg-bg" id="lgBg" aria-hidden="true">

        <span class="lg-blob b1"></span>
        <span class="lg-blob b2"></span>
        <span class="lg-blob b3"></span>

        <span class="lg-float f1"><i class="fas fa-apple-alt"></i></span>
        <span class="lg-float f2"><i class="fas fa-carrot"></i></span>
        <span class="lg-float f3"><i class="fas fa-fish"></i></span>
        <span class="lg-float f4"><i class="fas fa-egg"></i></span>
        <span class="lg-float f5"><i class="fas fa-lemon"></i></span>
        <span class="lg-float f6"><i class="fas fa-bread-slice"></i></span>

    </div>


    {{-- =========================================================
         KARTU REGISTER
    ========================================================== --}}
    <main class="lg-main">

        <div class="lg-card rg-card" id="lgCard">

            <div class="lg-avatar">
                <i class="fas fa-user-plus"></i>
            </div>

            <h2 class="lg-title">Create an Account!</h2>

            <p class="lg-lead">Buat akun NutriMate kamu.</p>


            {{-- Error --}}
            @if($errors->any())
                <div class="lg-alert err" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="lg-alert-list">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif


            <!-- Form Register -->
            <form action="{{ route('register.store') }}" method="POST" id="lgForm">

                @csrf

                <!-- Nama -->
                <div class="lg-field @error('name') has-error @enderror">

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder=" "
                        required
                        autocomplete="name"
                        autofocus
                    >

                    <label for="name">Full Name</label>

                    <i class="fas fa-user lg-ico"></i>

                </div>


                <!-- Email -->
                <div class="lg-field @error('email') has-error @enderror">

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder=" "
                        required
                        autocomplete="email"
                    >

                    <label for="email">Email Address</label>

                    <i class="fas fa-envelope lg-ico"></i>

                </div>


                <!-- Password -->
                <div class="lg-field @error('password') has-error @enderror">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder=" "
                        required
                        autocomplete="new-password"
                    >

                    <label for="password">Password</label>

                    <i class="fas fa-lock lg-ico"></i>

                    <button type="button"
                            class="lg-eye"
                            data-target="password"
                            aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>

                </div>

                <!-- Kekuatan password -->
                <div class="rg-meter" id="rgMeter" data-level="0" hidden>
                    <div class="rg-bars"><span></span><span></span><span></span><span></span></div>
                    <small class="rg-meter-text" id="rgMeterText"></small>
                </div>


                <!-- Konfirmasi Password -->
                <div class="lg-field">

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder=" "
                        required
                        autocomplete="new-password"
                    >

                    <label for="password_confirmation">Repeat Password</label>

                    <i class="fas fa-shield-alt lg-ico"></i>

                    <button type="button"
                            class="lg-eye"
                            data-target="password_confirmation"
                            aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>

                </div>

                <div class="rg-match" id="rgMatch" hidden></div>


                <div class="lg-caps" id="lgCaps" hidden>
                    <i class="fas fa-exclamation-triangle"></i>
                    Caps Lock sedang aktif
                </div>


                <!-- Register Button -->
                <button type="submit" class="lg-btn rg-submit" id="lgSubmit">

                    <span class="lg-btn-label">
                        <i class="fas fa-user-plus"></i>
                        Register Account
                    </span>

                    <span class="lg-btn-wait" hidden>
                        <i class="fas fa-spinner fa-spin"></i>
                        Memproses...
                    </span>

                </button>

            </form>


            <!-- Login -->
            <p class="lg-foot">
                <a class="lg-link" href="{{ route('login') }}">
                    Already have an account? Login!
                </a>
            </p>

            <p class="lg-copy">
                &copy; NutriMate {{ date('Y') }}
            </p>

        </div>

    </main>

</div>

@endsection