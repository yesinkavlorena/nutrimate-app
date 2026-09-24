<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
          content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description"
          content="NutriMate - Sistem Rekomendasi Makanan Personal">

    <meta name="author" content="NutriMate">

    <title>
        @yield('title', 'Dashboard - NutriMate')
    </title>

    {{-- Font Awesome --}}
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}"
          rel="stylesheet">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900"
          rel="stylesheet">

    {{-- SB Admin 2 --}}
    <link href="{{ asset('css/sb-admin-2.min.css') }}"
          rel="stylesheet">

    {{-- NutriMate Custom CSS --}}
    <link href="{{ asset('css/nutrimate.css') }}"
          rel="stylesheet">

    @stack('styles')

</head>

<body id="page-top">

<div id="wrapper">

    {{-- Sidebar --}}
    @include('layouts.partials.sidebar')

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            {{-- Topbar --}}
            @include('layouts.partials.topbar')

            {{-- Page Content --}}
            <div class="container-fluid">

                @yield('content')

            </div>

        </div>

        {{-- Footer --}}
        @include('layouts.partials.footer')

    </div>

</div>

{{-- Scroll to Top --}}
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

@stack('scripts')

</body>

</html>