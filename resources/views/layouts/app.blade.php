<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="NutriMate - Sistem Rekomendasi Makanan Personal">

    <title>@yield('title', 'NutriMate')</title>

    {{-- Bootstrap 5 --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    {{-- Font Awesome --}}
    <link
        href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet"
    >

    {{-- Font Nunito --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- CSS NutriMate --}}
    <link href="{{ asset('css/nutrimate.css') }}" rel="stylesheet">

    {{-- CSS topbar dan footer dimuat di sini (bukan lewat @push di partial,
         karena @stack('styles') sudah selesai dicetak sebelum partial dirender) --}}
    <link href="{{ asset('css/topbar.css') }}?v={{ @filemtime(public_path('css/topbar.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/footer.css') }}?v={{ @filemtime(public_path('css/footer.css')) }}" rel="stylesheet">

    {{-- CSS tambahan dari halaman --}}
    @stack('styles')
</head>

<body class="@yield('body-class', '')">

    {{-- Navigasi ikon (hanya untuk pengguna yang sudah login) --}}
    @auth
        @include('layouts.partials.navigation')
    @endauth

    <main class="nutrimate-main">

        {{-- Topbar --}}
        @auth
            @include('layouts.partials.topbar')
        @endauth

        {{-- Konten halaman --}}
        @yield('content')

        {{-- Footer --}}
        @auth
            @include('layouts.partials.footer')
        @endauth

    </main>

    {{-- Bootstrap 5 JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JavaScript tambahan dari halaman --}}
    @stack('scripts')
</body>

</html>