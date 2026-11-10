<link rel="stylesheet"
      href="{{ asset('css/topbar.css') }}?v={{ @filemtime(public_path('css/topbar.css')) }}">

@php
    $zona   = now()->timezone('Asia/Jakarta');
    $jam    = $zona->hour;

    $sapaan = $jam < 11 ? 'Selamat Pagi'
            : ($jam < 15 ? 'Selamat Siang'
            : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));

    // judul halaman diambil dari @section('title'), tanpa akhiran " - NutriMate"
    $judulHalaman = trim(\Illuminate\Support\Str::before(
        \Illuminate\Support\Facades\View::yieldContent('title') ?: 'NutriMate',
        ' - NutriMate'
    ));

    $tanggal   = $zona->copy()->locale('id')->translatedFormat('l, d F Y');
    $namaDepan = explode(' ', trim(Auth::user()->name))[0];
@endphp

<nav class="tb" aria-label="Bilah atas">


    {{-- Page Title --}}
    <div class="tb-left">

        <div class="tb-hello">
            {{ $sapaan }}, {{ $namaDepan }}
        </div>

        <h2 class="tb-title">
            {{ $judulHalaman }}
        </h2>

    </div>


    {{-- Right Side --}}
    <div class="tb-right">

        <div class="tb-date d-none d-lg-inline-flex">

            <i class="fas fa-calendar-alt"></i>

            {{ $tanggal }}

        </div>

        <div class="tb-divider d-none d-lg-block"></div>


        {{-- User --}}
        <div class="dropdown tb-user">

            <button class="tb-user-btn dropdown-toggle"
                    type="button"
                    id="userDropdown"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false">

                <span class="tb-user-name">

                    {{ Auth::user()->name }}

                </span>

                <span class="tb-avatar">
                    <i class="fas fa-user"></i>
                </span>

                <i class="fas fa-chevron-down tb-chev"></i>

            </button>


            {{-- Dropdown --}}
            <div class="dropdown-menu dropdown-menu-end tb-menu"
                 aria-labelledby="userDropdown">

                <div class="tb-menu-inner">

                    <div class="tb-menu-head">

                        <span class="tb-avatar tb-avatar-lg">
                            <i class="fas fa-user"></i>
                        </span>

                        <div class="tb-menu-who">
                            <strong>{{ Auth::user()->name }}</strong>
                            <small>{{ Auth::user()->email }}</small>
                        </div>

                    </div>


                    {{-- Profil --}}
                    <a class="dropdown-item tb-item"
                       href="{{ route('profil.create') }}">

                        <span class="tb-item-icon">
                            <i class="fas fa-user"></i>
                        </span>

                        Profil

                    </a>


                    <div class="tb-sep"></div>


                    {{-- Logout --}}
                    <form action="{{ route('logout') }}"
                          method="POST">

                        @csrf

                        <button type="submit"
                                class="dropdown-item tb-item tb-item-danger">

                            <span class="tb-item-icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </span>

                            Logout

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</nav>