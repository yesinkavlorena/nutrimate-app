<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no"
    >

    <meta
        name="description"
        content="NutriMate - Sistem Rekomendasi Makanan Personal"
    >

    <meta
        name="author"
        content="NutriMate"
    >

    <title>
        @yield('title', 'NutriMate')
    </title>


    <!-- ===================================================== -->
    <!-- SB ADMIN 2 - FONT AWESOME -->
    <!-- ===================================================== -->

    <link
        href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet"
        type="text/css"
    >


    <!-- ===================================================== -->
    <!-- SB ADMIN 2 - GOOGLE FONT -->
    <!-- ===================================================== -->

    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet"
    >


    <!-- ===================================================== -->
    <!-- SB ADMIN 2 CSS -->
    <!-- ===================================================== -->

    <link
        href="{{ asset('css/sb-admin-2.min.css') }}"
        rel="stylesheet"
    >


    <!-- CSS tambahan dari halaman -->
    @stack('styles')

</head>


<body class="@yield('body-class', '')">

    @yield('content')


    <!-- ===================================================== -->
    <!-- JQUERY -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('vendor/jquery/jquery.min.js') }}"
    ></script>


    <!-- ===================================================== -->
    <!-- BOOTSTRAP -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"
    ></script>


    <!-- ===================================================== -->
    <!-- JQUERY EASING -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"
    ></script>


    <!-- ===================================================== -->
    <!-- SB ADMIN 2 JAVASCRIPT -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('js/sb-admin-2.min.js') }}"
    ></script>


    <!-- JavaScript tambahan dari halaman -->
    @stack('scripts')

</body>

</html>