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

    @stack('styles')
</head>

<body class="@yield('body-class', '')">

    {{-- Navigasi ikon --}}
    @include('layouts.partials.navigation')

    {{-- Konten halaman --}}
    <main class="nutrimate-main">

        {{-- Topbar --}}
        @include('layouts.partials.topbar')

        @yield('content')

        {{-- Footer --}}
        @include('layouts.partials.footer')

    </main>

    {{-- Bootstrap 5 JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>