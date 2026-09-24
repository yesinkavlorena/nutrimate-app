<ul class="navbar-nav nutrimate-sidebar sidebar sidebar-dark accordion"
    id="accordionSidebar">

    {{-- Brand --}}
    <a class="sidebar-brand d-flex align-items-center justify-content-center"
       href="{{ route('dashboard') }}">

        <div class="sidebar-brand-icon">
            <i class="fas fa-leaf"></i>
        </div>

        <div class="sidebar-brand-text mx-3">
            NutriMate
        </div>

    </a>

    <hr class="sidebar-divider my-0">


    {{-- Dashboard --}}
    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">

        <a class="nav-link" href="{{ route('dashboard') }}">

            <i class="fas fa-fw fa-home"></i>

            <span>Dashboard</span>

        </a>

    </li>


    <hr class="sidebar-divider">


    <div class="sidebar-heading">
        Menu
    </div>


    {{-- Profil --}}
    <li class="nav-item {{ request()->routeIs('profil.*') ? 'active' : '' }}">

        <a class="nav-link" href="{{ route('profil.create') }}">

            <i class="fas fa-fw fa-user"></i>

            <span>Profil</span>

        </a>

    </li>


    {{-- Rekomendasi --}}
    <li class="nav-item {{ request()->routeIs('rekomendasi.*') ? 'active' : '' }}">

        <a class="nav-link" href="#">

            <i class="fas fa-fw fa-utensils"></i>

            <span>Rekomendasi Makanan</span>

        </a>

    </li>


    {{-- Riwayat --}}
    <li class="nav-item {{ request()->routeIs('riwayat.*') ? 'active' : '' }}">

        <a class="nav-link" href="#">

            <i class="fas fa-fw fa-history"></i>

            <span>Riwayat</span>

        </a>

    </li>


    <hr class="sidebar-divider">


    <div class="sidebar-heading">
        Informasi
    </div>


    {{-- Informasi Gizi --}}
    <li class="nav-item {{ request()->routeIs('informasi.gizi') ? 'active' : '' }}">

        <a class="nav-link" href="{{ route('informasi.gizi') }}">

            <i class="fas fa-fw fa-apple-alt"></i>

            <span>Informasi Gizi</span>

        </a>

    </li>


    {{-- Logout --}}
    <li class="nav-item">

        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button type="submit"
                    class="nav-link btn btn-link text-left w-100 border-0">

                <i class="fas fa-fw fa-sign-out-alt"></i>

                <span>Logout</span>

            </button>

        </form>

    </li>

</ul>