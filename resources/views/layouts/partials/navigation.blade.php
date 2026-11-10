<nav class="nutrimate-navigation" aria-label="Navigasi utama">

    {{-- Logo NutriMate --}}
    <a href="{{ route('dashboard') }}"
       class="nutrimate-nav-logo"
       aria-label="NutriMate"
       title="NutriMate">
        <i class="fas fa-leaf"></i>
    </a>

    <div class="nutrimate-nav-menu">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="nutrimate-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           aria-label="Dashboard"
           title="Dashboard">
            <i class="fas fa-home"></i>
        </a>

        {{-- Profil --}}
<a href="{{ route('profil.create') }}"
   class="nutrimate-nav-item {{ request()->routeIs('profil.*') ? 'active' : '' }}"
   aria-label="Profil"
   title="Profil">
    <i class="fas fa-user"></i>
</a>

        {{-- Rekomendasi --}}
        <a href="{{ route('rekomendasi.index') }}"
           class="nutrimate-nav-item {{ request()->routeIs('rekomendasi.*') ? 'active' : '' }}"
           aria-label="Rekomendasi makanan"
           title="Rekomendasi makanan">
            <i class="fas fa-utensils"></i>
        </a>

        {{-- Riwayat --}}
        <a href="{{ route('riwayat.index') }}"
           class="nutrimate-nav-item {{ request()->routeIs('riwayat.*') ? 'active' : '' }}"
           aria-label="Riwayat"
           title="Riwayat">
            <i class="fas fa-history"></i>
        </a>

        {{-- Informasi gizi --}}
        <a href="{{ route('informasi.gizi') }}"
           class="nutrimate-nav-item {{ request()->routeIs('informasi.gizi') ? 'active' : '' }}"
           aria-label="Informasi gizi"
           title="Informasi gizi">
            <i class="fas fa-apple-alt"></i>
        </a>

    </div>

    {{-- Logout --}}
    <form action="{{ route('logout') }}" method="POST" class="nutrimate-nav-logout">
        @csrf
        <button type="submit"
                class="nutrimate-nav-item"
                aria-label="Logout"
                title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </form>

</nav>
