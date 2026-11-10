@extends('layouts.app')

@section('title', 'Login - NutriMate')

@section('body-class', 'lg-body')

{{-- CSS halaman login (butuh @stack('styles') di layouts.app) --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

{{-- JS halaman login (butuh @stack('scripts') di layouts.app) --}}
@push('scripts')
    <script src="{{ asset('js/login.js') }}" defer></script>
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
         KARTU LOGIN
    ========================================================== --}}
    <main class="lg-main">

        <div class="lg-card" id="lgCard">

            <div class="lg-avatar">
                <i class="fas fa-user"></i>
            </div>

            <h2 class="lg-title">Selamat Datang!</h2>

            <p class="lg-lead">Masuk untuk melanjutkan ke NutriMate.</p>


            {{-- Pesan sukses --}}
            @if(session('success'))
                <div class="lg-alert" role="status">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif


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


            <!-- Form Login -->
            <form action="{{ route('login.store') }}" method="POST" id="lgForm">

                @csrf

                <!-- Email -->
                <div class="lg-field @error('email') has-error @enderror">

                    <input
                        type="email"
                        id="exampleInputEmail"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder=" "
                        required
                        autocomplete="email"
                        autofocus
                    >

                    <label for="exampleInputEmail">Email Address</label>

                    <i class="fas fa-envelope lg-ico"></i>

                </div>


                <!-- Password -->
                <div class="lg-field @error('password') has-error @enderror">

                    <input
                        type="password"
                        id="exampleInputPassword"
                        name="password"
                        placeholder=" "
                        required
                        autocomplete="current-password"
                    >

                    <label for="exampleInputPassword">Password</label>

                    <i class="fas fa-lock lg-ico"></i>

                    <button type="button"
                            class="lg-eye"
                            id="lgEye"
                            aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>

                </div>


                <div class="lg-caps" id="lgCaps" hidden>
                    <i class="fas fa-exclamation-triangle"></i>
                    Caps Lock sedang aktif
                </div>


                <div class="lg-row">

                    <!-- Remember Me -->
                    <label class="lg-check" for="customCheck">

                        <input type="checkbox"
                               id="customCheck"
                               name="remember"
                               {{ old('remember') ? 'checked' : '' }}>

                        <span class="lg-box"><i class="fas fa-check"></i></span>

                        Remember Me

                    </label>

                    <!-- Forgot Password -->
                    <a class="lg-link" href="#">
                        Forgot Password?
                    </a>

                </div>


                <!-- Login Button -->
                <button type="submit" class="lg-btn" id="lgSubmit">

                    <span class="lg-btn-label">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </span>

                    <span class="lg-btn-wait" hidden>
                        <i class="fas fa-spinner fa-spin"></i>
                        Memproses...
                    </span>

                </button>

            </form>


            <!-- Register -->
            <p class="lg-foot">
                <a class="lg-link" href="{{ route('register') }}">
                    Create an Account!
                </a>
            </p>

            <p class="lg-copy">
                &copy; NutriMate {{ date('Y') }}
            </p>

        </div>

    </main>

</div>

@endsection