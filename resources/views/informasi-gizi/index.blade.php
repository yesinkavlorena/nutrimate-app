@extends('layouts.dashboard')

@section('title', 'Informasi Gizi Makanan - NutriMate')

@section('content')

<div class="container-fluid gz-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="gz-hero" id="gzHero">

        <div>
            <h1 class="gz-hero-title">
                Informasi Gizi Makanan
            </h1>

            <p class="gz-hero-sub">
                Lihat informasi gizi dari berbagai makanan.
            </p>
        </div>

        <div class="gz-hero-stat">
            <strong id="gzTotal">0</strong>
            <span>makanan tersedia</span>
        </div>

    </div>


    {{-- =========================================================
         TOOLBAR: cari, urutkan, tampilan, filter cepat
    ========================================================== --}}
    <div class="gz-toolbar" id="gzToolbar">

        <div class="gz-search">

            <i class="fas fa-search"></i>

            <input
                type="text"
                id="searchFood"
                class="gz-search-input"
                placeholder="Cari makanan, tekan / untuk mulai mengetik"
                autocomplete="off"
                aria-label="Cari makanan"
            >

            <button type="button"
                    id="gzClear"
                    class="gz-clear"
                    aria-label="Hapus pencarian">
                <i class="fas fa-times"></i>
            </button>

            <kbd class="gz-kbd">/</kbd>

        </div>

        <div class="gz-controls">

            <select id="gzSort" class="gz-select" aria-label="Urutkan makanan">
                <option value="default">Urutan awal</option>
                <option value="nama">Nama A-Z</option>
                <option value="kalori-asc">Kalori terendah</option>
                <option value="kalori-desc">Kalori tertinggi</option>
                <option value="protein-desc">Protein tertinggi</option>
                <option value="lemak-asc">Lemak terendah</option>
                <option value="karbo-asc">Karbohidrat terendah</option>
            </select>

            <div class="gz-view" role="group" aria-label="Pilih tampilan">

                <button type="button"
                        class="gz-view-btn is-active"
                        data-view="grid"
                        title="Tampilan kartu"
                        aria-label="Tampilan kartu">
                    <i class="fas fa-th-large"></i>
                </button>

                <button type="button"
                        class="gz-view-btn"
                        data-view="list"
                        title="Tampilan daftar"
                        aria-label="Tampilan daftar">
                    <i class="fas fa-list"></i>
                </button>

            </div>

        </div>

        <div class="gz-chips" id="gzChips">

            <button type="button" class="gz-chip is-active" data-chip="all">
                Semua
            </button>

            <button type="button" class="gz-chip" data-chip="kalori-rendah">
                Rendah kalori <span class="gz-chip-n"></span>
            </button>

            <button type="button" class="gz-chip" data-chip="protein-tinggi">
                Tinggi protein <span class="gz-chip-n"></span>
            </button>

            <button type="button" class="gz-chip" data-chip="lemak-rendah">
                Rendah lemak <span class="gz-chip-n"></span>
            </button>

            <button type="button" class="gz-chip" data-chip="karbo-rendah">
                Rendah karbohidrat <span class="gz-chip-n"></span>
            </button>

        </div>

    </div>


    {{-- =========================================================
         DAFTAR MAKANAN
    ========================================================== --}}
    <div class="gz-panel">

        <div class="gz-panel-head">

            <h6 class="gz-panel-title">
                Daftar Makanan
            </h6>

            <span class="gz-count" id="gzCount" aria-live="polite"></span>

        </div>

        <div class="gz-panel-body">

            <div class="row" id="food-list">

                @foreach($makanan as $item)

                    <div
                        class="col-xl-3 col-lg-4 col-md-6 mb-4 food-item"
                        data-food-name="{{ strtolower($item->nama_makanan) }}"
                        data-kalori="{{ $item->kalori }}"
                        data-protein="{{ $item->protein }}"
                        data-lemak="{{ $item->lemak }}"
                        data-karbo="{{ $item->karbohidrat }}"
                    >

                        <div class="food-card">

                            {{-- Gambar --}}
                            <div class="food-image">

                                @if($item->gambar_makanan)

                                    <img
                                        src="{{ $item->gambar_makanan }}"
                                        alt="{{ $item->nama_makanan }}"
                                        loading="lazy"
                                        decoding="async"
                                    >

                                @else

                                    <div class="food-image-placeholder">
                                        <i class="fas fa-utensils"></i>
                                    </div>

                                @endif

                            </div>


                            {{-- Nama --}}
                            <div class="food-name" title="{{ $item->nama_makanan }}">
                                {{ $item->nama_makanan }}
                            </div>


                            {{-- Informasi gizi --}}
                            <div class="nutrition-info">

                                <div class="nutrition-row" data-nutri="kalori">
                                    <span>Kalori</span>
                                    <strong>{{ $item->kalori }} kkal</strong>
                                    <i class="nutri-bar"><b></b></i>
                                </div>

                                <div class="nutrition-row" data-nutri="protein">
                                    <span>Protein</span>
                                    <strong>{{ $item->protein }} g</strong>
                                    <i class="nutri-bar"><b></b></i>
                                </div>

                                <div class="nutrition-row" data-nutri="lemak">
                                    <span>Lemak</span>
                                    <strong>{{ $item->lemak }} g</strong>
                                    <i class="nutri-bar"><b></b></i>
                                </div>

                                <div class="nutrition-row" data-nutri="karbo">
                                    <span>Karbohidrat</span>
                                    <strong>{{ $item->karbohidrat }} g</strong>
                                    <i class="nutri-bar"><b></b></i>
                                </div>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- Tampilkan lebih banyak --}}
            <div class="gz-more" id="gzMore" hidden>

                <button type="button" class="gz-more-btn" id="gzMoreBtn">
                    Tampilkan lebih banyak
                </button>

            </div>


            {{-- Tidak ditemukan --}}
            <div
                id="no-food-result"
                class="gz-empty"
                style="display: none;"
            >

                <i class="fas fa-search"></i>

                <h5>Makanan tidak ditemukan</h5>

                <p>Coba gunakan kata pencarian lain atau hapus filter.</p>

                <button type="button" class="gz-more-btn" id="gzReset">
                    Atur ulang pencarian
                </button>

            </div>

        </div>

    </div>

</div>

@endsection

@push('styles')

<style>

    /* =========================================================
       NutriMate - Informasi Gizi
       Satu gaya dengan dashboard: hijau lembut, hover terangkat,
       kilau menyapu, garis bawah terisi, gambar zoom halus.
    ========================================================= */

    .gz-page {
        --gz-green: #1cc88a;
        --gz-green-d: #13a46f;
        --gz-ink: #2e3b34;
        --gz-muted: #7d8984;
        --gz-line: #e4ede8;
        --gz-soft: #f3fbf7;

        --c-kalori: #f5873b;  --c-kalori-d: #d46a1f;
        --c-protein: #5a67d8; --c-protein-d: #434fb5;
        --c-lemak: #e9b020;   --c-lemak-d: #c28f0b;
        --c-karbo: #2bb5c9;   --c-karbo-d: #1b8fa1;
    }

    .gz-page [hidden] { display: none !important; }

    .gz-page button:focus-visible,
    .gz-page select:focus-visible {
        outline: 3px solid rgba(28, 200, 138, .45);
        outline-offset: 2px;
    }


    /* ---------- Header ---------- */
    .gz-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px 24px;
        margin-bottom: 1.5rem;
        padding: 1.6rem 1.9rem;
        border: 1px solid var(--gz-line);
        border-radius: 22px;
        background: linear-gradient(135deg, #eafaf3 0%, #f7fffb 60%, #ffffff 100%);
        box-shadow: 0 6px 24px rgba(30, 80, 55, .07);
        transition: box-shadow .3s ease;
    }
    .gz-hero:hover { box-shadow: 0 14px 34px rgba(30, 80, 55, .14); }

    /* cahaya hijau mengikuti kursor */
    .gz-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: radial-gradient(
            320px circle at var(--mx, 90%) var(--my, 10%),
            rgba(28, 200, 138, .24),
            transparent 70%
        );
    }
    .gz-hero > * { position: relative; }

    .gz-hero-title {
        margin: 0 0 .3rem;
        font-size: clamp(1.5rem, 3vw, 2.1rem);
        font-weight: 800;
        background: linear-gradient(90deg, #1f6f50, var(--gz-green));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .gz-hero-sub {
        margin: 0;
        color: var(--gz-muted);
    }
    .gz-hero-stat {
        display: flex;
        align-items: baseline;
        gap: .5rem;
        color: var(--gz-muted);
        font-weight: 600;
    }
    .gz-hero-stat strong {
        font-size: 2rem;
        font-weight: 800;
        color: var(--gz-green-d);
        line-height: 1;
    }


    /* ---------- Toolbar ---------- */
    .gz-toolbar {
        position: sticky;
        top: 12px;
        z-index: 30;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px 14px;
        margin-bottom: 1.5rem;
        padding: 14px 16px;
        border: 1px solid var(--gz-line);
        border-radius: 18px;
        background: rgba(255, 255, 255, .94);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        box-shadow: 0 6px 20px rgba(30, 80, 55, .08);
    }

    /* pencarian */
    .gz-search {
        position: relative;
        flex: 1 1 280px;
        min-width: 0;
    }
    .gz-search > .fa-search {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gz-muted);
        transition: color .2s ease;
        z-index: 2;
    }
    .gz-search:focus-within > .fa-search { color: var(--gz-green-d); }

    .gz-search-input {
        width: 100%;
        height: 46px;
        padding: 0 46px 0 44px;
        border: 1px solid var(--gz-line);
        border-radius: 12px;
        background: var(--gz-soft);
        color: var(--gz-ink);
        font: inherit;
        outline: none;
        transition: background .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .gz-search-input::placeholder { color: #9aa5a0; }
    .gz-search-input:hover { border-color: rgba(28, 200, 138, .6); }
    .gz-search-input:focus {
        background: #fff;
        border-color: var(--gz-green);
        box-shadow: 0 0 0 .25rem rgba(28, 200, 138, .16);
    }

    .gz-kbd {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        min-width: 24px;
        padding: 1px 7px;
        border: 1px solid var(--gz-line);
        border-radius: 6px;
        background: #fff;
        color: var(--gz-muted);
        font: inherit;
        font-size: .78rem;
        text-align: center;
        pointer-events: none;
    }
    .gz-search-input:focus ~ .gz-kbd,
    .gz-search-input:not(:placeholder-shown) ~ .gz-kbd { display: none; }

    .gz-clear {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        display: none;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        background: rgba(28, 200, 138, .14);
        color: var(--gz-green-d);
        cursor: pointer;
        transition: background .2s ease, color .2s ease, transform .3s ease;
    }
    .gz-search-input:not(:placeholder-shown) ~ .gz-clear { display: inline-flex; }
    .gz-clear:hover {
        background: var(--gz-green-d);
        color: #fff;
        transform: translateY(-50%) rotate(90deg);
    }

    /* urutkan + tampilan */
    .gz-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .gz-select {
        height: 46px;
        padding: 0 38px 0 14px;
        border: 1px solid var(--gz-line);
        border-radius: 12px;
        background-color: var(--gz-soft);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' fill='none' stroke='%2313a46f' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        color: var(--gz-ink);
        font: inherit;
        font-weight: 600;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .gz-select:hover {
        border-color: var(--gz-green);
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(28, 200, 138, .18);
    }

    .gz-view {
        display: inline-flex;
        padding: 4px;
        gap: 4px;
        border: 1px solid var(--gz-line);
        border-radius: 12px;
        background: var(--gz-soft);
    }
    .gz-view-btn {
        width: 38px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: var(--gz-muted);
        cursor: pointer;
        transition: background .25s ease, color .25s ease, transform .25s ease, box-shadow .25s ease;
    }
    .gz-view-btn:hover { color: var(--gz-green-d); transform: translateY(-2px); }
    .gz-view-btn.is-active {
        background: linear-gradient(135deg, #2fd9a0, var(--gz-green-d));
        color: #fff;
        box-shadow: 0 6px 14px rgba(28, 200, 138, .3);
    }

    /* filter cepat */
    .gz-chips {
        flex: 1 1 100%;
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 2px 2px 4px;
        scrollbar-width: none;
    }
    .gz-chips::-webkit-scrollbar { display: none; }

    .gz-chip {
        flex: none;
        padding: .4rem .95rem;
        border: 1px solid var(--gz-line);
        border-radius: 999px;
        background: #fff;
        color: var(--gz-ink);
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, color .25s ease, background .25s ease;
    }
    .gz-chip:hover {
        transform: translateY(-2px);
        border-color: var(--gz-green);
        color: var(--gz-green-d);
        box-shadow: 0 6px 14px rgba(28, 200, 138, .18);
    }
    .gz-chip.is-active {
        border-color: transparent;
        background: linear-gradient(135deg, #2fd9a0, var(--gz-green-d));
        color: #fff;
        box-shadow: 0 6px 14px rgba(28, 200, 138, .3);
    }
    .gz-chip-n {
        margin-left: .25rem;
        padding: 0 .4rem;
        border-radius: 999px;
        background: rgba(28, 200, 138, .14);
        font-size: .75rem;
    }
    .gz-chip-n:empty { display: none; }
    .gz-chip.is-active .gz-chip-n { background: rgba(255, 255, 255, .28); }


    /* ---------- Panel daftar ---------- */
    .gz-panel {
        border: 1px solid var(--gz-line);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 6px 24px rgba(30, 80, 55, .07);
    }
    .gz-panel-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 6px 16px;
        padding: 1rem 1.4rem;
        border-bottom: 1px solid var(--gz-line);
    }
    .gz-panel-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: var(--gz-green-d);
    }
    .gz-count {
        font-size: .85rem;
        color: var(--gz-muted);
    }
    .gz-panel-body { padding: 1.4rem 1.4rem .6rem; }


    /* ---------- Kartu makanan ---------- */
    .food-card {
        position: relative;
        overflow: hidden;
        height: 100%;
        padding: 12px;
        border: 1px solid var(--gz-line);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(30, 80, 55, .06);
        transition: transform .35s cubic-bezier(.22, 1, .36, 1),
                    box-shadow .35s ease,
                    border-color .35s ease;
    }
    .food-card:hover {
        transform: translateY(-8px);
        border-color: var(--gz-green);
        box-shadow: 0 18px 34px rgba(28, 200, 138, .28);
    }

    /* kilau menyapu */
    .food-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: -120%;
        width: 55%;
        height: 100%;
        background: linear-gradient(105deg, transparent, rgba(255, 255, 255, .55), transparent);
        transform: skewX(-18deg);
        pointer-events: none;
        z-index: 3;
    }
    .food-card:hover::before { animation: gzSweep .9s ease; }
    @keyframes gzSweep { to { left: 140%; } }

    /* garis bawah terisi */
    .food-card::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 0;
        height: 4px;
        border-radius: 0 4px 4px 0;
        background: linear-gradient(90deg, var(--gz-green-d), #4ee3b0);
        transition: width .8s cubic-bezier(.22, 1, .36, 1);
        z-index: 3;
    }
    .food-card:hover::after { width: 100%; }
    @media (hover: none) {
        .food-card::after { width: 100%; }
    }

    /* gambar */
    .food-image {
        width: 100%;
        height: 170px;
        margin-bottom: 12px;
        border-radius: 12px;
        overflow: hidden;
        background-color: #e8f5e9;
    }
    .food-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .7s cubic-bezier(.22, 1, .36, 1), filter .4s ease;
    }
    .food-card:hover .food-image img {
        transform: scale(1.1);
        filter: saturate(1.12) brightness(1.03);
    }
    .food-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 2.4rem;
        background: linear-gradient(135deg, #7be3bf, var(--gz-green-d));
        transition: transform .7s cubic-bezier(.22, 1, .36, 1);
    }
    .food-card:hover .food-image-placeholder { transform: scale(1.1); }

    /* nama */
    .food-name {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7em;
        margin-bottom: 10px;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.35;
        color: var(--gz-ink);
        transition: color .3s ease, transform .3s ease;
    }
    .food-card:hover .food-name {
        color: var(--gz-green-d);
        transform: translateX(4px);
    }
    .food-name mark {
        padding: 0 2px;
        border-radius: 4px;
        background: rgba(28, 200, 138, .28);
        color: inherit;
    }

    /* gizi: angka + batang perbandingan */
    .nutrition-info {
        display: grid;
        gap: 9px;
        padding-top: 10px;
        border-top: 1px solid var(--gz-line);
    }
    .nutrition-row {
        display: grid;
        grid-template-columns: 1fr auto;
        row-gap: 5px;
        font-size: .82rem;
    }
    .nutrition-row span { color: var(--gz-muted); }
    .nutrition-row strong {
        color: var(--gz-ink);
        font-weight: 800;
        transition: color .3s ease;
    }
    .nutrition-row[data-nutri="kalori"]  { --bar: var(--c-kalori);  --bar-d: var(--c-kalori-d); }
    .nutrition-row[data-nutri="protein"] { --bar: var(--c-protein); --bar-d: var(--c-protein-d); }
    .nutrition-row[data-nutri="lemak"]   { --bar: var(--c-lemak);   --bar-d: var(--c-lemak-d); }
    .nutrition-row[data-nutri="karbo"]   { --bar: var(--c-karbo);   --bar-d: var(--c-karbo-d); }

    .food-card:hover .nutrition-row strong { color: var(--bar-d); }

    .nutri-bar {
        grid-column: 1 / -1;
        display: block;
        height: 5px;
        border-radius: 5px;
        background: #edf3ef;
        overflow: hidden;
    }
    .nutri-bar b {
        display: block;
        width: 0;
        height: 100%;
        border-radius: 5px;
        background: var(--bar);
        transition: width 1s cubic-bezier(.22, 1, .36, 1);
    }
    .gz-ready .nutri-bar b { width: var(--w, 0%); }


    /* ---------- Tampilan daftar (list) ---------- */
    #food-list.is-list .food-item {
        flex: 0 0 100% !important;
        width: 100% !important;
        max-width: 100% !important;
        margin-bottom: .75rem !important;
    }
    #food-list.is-list .food-card {
        display: flex;
        align-items: center;
        gap: 18px;
        height: auto;
        padding: 10px 18px 10px 10px;
    }
    #food-list.is-list .food-card:hover { transform: translateX(6px); }
    #food-list.is-list .food-image {
        flex: none;
        width: 92px;
        height: 92px;
        margin: 0;
    }
    #food-list.is-list .food-name {
        flex: 1 1 180px;
        min-width: 0;
        min-height: 0;
        margin: 0;
    }
    #food-list.is-list .nutrition-info {
        flex: 3 1 460px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px 22px;
        padding-top: 0;
        border-top: 0;
    }
    @media (max-width: 767.98px) {
        #food-list.is-list .food-card { flex-wrap: wrap; }
        #food-list.is-list .nutrition-info {
            flex-basis: 100%;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }


    /* ---------- Muncul bertahap ---------- */
    @keyframes gzIn {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: none; }
    }
    .food-item.gz-in {
        animation: gzIn .55s cubic-bezier(.22, 1, .36, 1) both;
        animation-delay: var(--d, 0ms);
    }


    /* ---------- Tombol & keadaan kosong ---------- */
    .gz-more {
        display: flex;
        justify-content: center;
        padding: .4rem 0 1.2rem;
    }
    .gz-more-btn {
        padding: .7rem 1.6rem;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #2fd9a0, var(--gz-green-d));
        color: #fff;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(28, 200, 138, .3);
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }
    .gz-more-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(28, 200, 138, .4);
        filter: brightness(1.05);
    }
    .gz-more-btn:active { transform: translateY(0); }

    .gz-empty {
        padding: 3rem 1rem;
        text-align: center;
    }
    .gz-empty > i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 84px;
        height: 84px;
        margin-bottom: 1rem;
        border-radius: 50%;
        background: rgba(28, 200, 138, .13);
        color: var(--gz-green-d);
        font-size: 2rem;
    }
    .gz-empty h5 { font-weight: 800; color: var(--gz-ink); }
    .gz-empty p { margin-bottom: 1.2rem; color: var(--gz-muted); }


    /* ---------- Layar kecil ---------- */
    @media (max-width: 767.98px) {
        .gz-toolbar { position: static; }
        .gz-controls { flex: 1 1 100%; }
        .gz-select { flex: 1 1 auto; }
        .gz-hero { padding: 1.3rem 1.2rem; }
    }


    /* ---------- Hormati pengaturan "kurangi animasi" ---------- */
    @media (prefers-reduced-motion: reduce) {
        .gz-page *, .gz-page *::before, .gz-page *::after {
            animation: none !important;
            transition: none !important;
        }
        .food-card:hover,
        #food-list.is-list .food-card:hover { transform: none; }
    }

</style>

@endpush

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const list = document.getElementById('food-list');
    if (!list) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const PAGE   = 12;   // jumlah makanan yang ditampilkan per tahap

    const items    = Array.from(list.querySelectorAll('.food-item'));
    const input    = document.getElementById('searchFood');
    const clearBtn = document.getElementById('gzClear');
    const sortSel  = document.getElementById('gzSort');
    const chips    = Array.from(document.querySelectorAll('.gz-chip'));
    const viewBtns = Array.from(document.querySelectorAll('.gz-view-btn'));
    const countEl  = document.getElementById('gzCount');
    const moreWrap = document.getElementById('gzMore');
    const moreBtn  = document.getElementById('gzMoreBtn');
    const emptyEl  = document.getElementById('no-food-result');
    const resetBtn = document.getElementById('gzReset');
    const totalEl  = document.getElementById('gzTotal');
    const hero     = document.getElementById('gzHero');

    const state = { q: '', chip: 'all', sort: 'default', view: 'grid', shown: PAGE };
    let firstRender = true;


    /* ---------- Data tiap makanan ---------- */
    const num = function (v) {
        const n = parseFloat(String(v).replace(',', '.'));
        return isNaN(n) ? 0 : n;
    };

    items.forEach(function (el, i) {
        const nameEl = el.querySelector('.food-name');
        el._nameEl = nameEl;
        el._hl = '';
        el._d = {
            i: i,
            name: el.dataset.foodName || '',
            title: nameEl ? nameEl.textContent.trim() : '',
            kalori: num(el.dataset.kalori),
            protein: num(el.dataset.protein),
            lemak: num(el.dataset.lemak),
            karbo: num(el.dataset.karbo)
        };
    });

    function esc(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }


    /* ---------- Batang perbandingan (relatif terhadap nilai tertinggi) ---------- */
    const keys = ['kalori', 'protein', 'lemak', 'karbo'];
    const max = {};

    keys.forEach(function (k) {
        max[k] = items.reduce(function (m, el) { return Math.max(m, el._d[k]); }, 0);
    });

    items.forEach(function (el) {
        keys.forEach(function (k) {
            const row = el.querySelector('.nutrition-row[data-nutri="' + k + '"]');
            if (!row) return;
            let pct = max[k] > 0 ? (el._d[k] / max[k]) * 100 : 0;
            if (el._d[k] > 0) pct = Math.max(pct, 4);
            row.style.setProperty('--w', pct.toFixed(1) + '%');
        });
    });

    requestAnimationFrame(function () {
        requestAnimationFrame(function () { list.classList.add('gz-ready'); });
    });


    /* ---------- Filter cepat: sepertiga terendah / tertinggi dari daftar ---------- */
    function tercile(key) {
        const v = items.map(function (el) { return el._d[key]; }).sort(function (a, b) { return a - b; });
        if (!v.length) return { low: 0, high: 0 };
        return {
            low:  v[Math.max(0, Math.ceil(v.length / 3) - 1)],
            high: v[Math.min(v.length - 1, Math.floor(v.length * 2 / 3))]
        };
    }
    const T = { kalori: tercile('kalori'), protein: tercile('protein'), lemak: tercile('lemak'), karbo: tercile('karbo') };

    function passChip(el, chip) {
        const d = el._d;
        switch (chip) {
            case 'kalori-rendah':  return d.kalori  <= T.kalori.low;
            case 'protein-tinggi': return d.protein >= T.protein.high;
            case 'lemak-rendah':   return d.lemak   <= T.lemak.low;
            case 'karbo-rendah':   return d.karbo   <= T.karbo.low;
            default: return true;
        }
    }

    chips.forEach(function (btn) {
        const n = btn.querySelector('.gz-chip-n');
        if (!n || btn.dataset.chip === 'all') return;
        n.textContent = items.filter(function (el) { return passChip(el, btn.dataset.chip); }).length;
    });


    /* ---------- Urutan ---------- */
    const byNum = function (k, dir) {
        return function (a, b) { return (dir * (a._d[k] - b._d[k])) || (a._d.i - b._d.i); };
    };
    const sorters = {
        'default': function (a, b) { return a._d.i - b._d.i; },
        'nama': function (a, b) { return a._d.title.localeCompare(b._d.title, 'id') || (a._d.i - b._d.i); },
        'kalori-asc': byNum('kalori', 1),
        'kalori-desc': byNum('kalori', -1),
        'protein-desc': byNum('protein', -1),
        'lemak-asc': byNum('lemak', 1),
        'karbo-asc': byNum('karbo', 1)
    };


    /* ---------- Animasi muncul ---------- */
    function animateIn(el, idx) {
        el.style.setProperty('--d', (Math.min(idx, 12) * 45) + 'ms');
        el.classList.remove('gz-in');
        void el.offsetWidth;
        el.classList.add('gz-in');
        el.addEventListener('animationend', function (e) {
            if (e.target === el) el.classList.remove('gz-in');
        }, { once: true });
    }

    function highlight(el) {
        if (el._hl === state.q || !el._nameEl) return;
        el._hl = state.q;
        const t = el._d.title;
        const q = state.q;
        const i = q ? t.toLowerCase().indexOf(q) : -1;
        el._nameEl.innerHTML = i < 0
            ? esc(t)
            : esc(t.slice(0, i)) + '<mark>' + esc(t.slice(i, i + q.length)) + '</mark>' + esc(t.slice(i + q.length));
    }


    /* ---------- Tampilkan hasil ---------- */
    function render() {

        const matches = items.filter(function (el) {
            return (!state.q || el._d.name.indexOf(state.q) !== -1) && passChip(el, state.chip);
        });
        matches.sort(sorters[state.sort] || sorters['default']);

        // susun ulang urutan di DOM
        const frag = document.createDocumentFragment();
        matches.forEach(function (el) { frag.appendChild(el); });
        list.appendChild(frag);

        const visible = matches.slice(0, state.shown);
        const visibleSet = new Set(visible);
        let n = 0;

        items.forEach(function (el) {
            if (visibleSet.has(el)) {
                const wasHidden = el.hidden;
                el.hidden = false;
                if ((wasHidden || firstRender) && !reduce) animateIn(el, n++);
                highlight(el);
            } else if (!el.hidden) {
                el.hidden = true;
            }
        });
        firstRender = false;

        // keterangan jumlah
        if (matches.length === items.length) {
            countEl.textContent = 'Menampilkan ' + visible.length + ' dari ' + items.length + ' makanan';
        } else {
            countEl.textContent = matches.length + ' makanan cocok, menampilkan ' + visible.length;
        }

        // tombol "tampilkan lebih banyak"
        const sisa = matches.length - visible.length;
        moreWrap.hidden = sisa <= 0;
        if (sisa > 0) {
            moreBtn.textContent = 'Tampilkan ' + Math.min(PAGE, sisa) + ' makanan lagi (sisa ' + sisa + ')';
        }

        // kosong
        emptyEl.style.display = matches.length === 0 ? 'block' : 'none';
    }


    /* ---------- Pencarian ---------- */
    let timer = null;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            state.q = input.value.toLowerCase().trim();
            state.shown = PAGE;
            render();
        }, 120);
    });

    function clearSearch() {
        input.value = '';
        state.q = '';
        state.shown = PAGE;
        render();
        input.focus();
    }
    clearBtn.addEventListener('click', clearSearch);

    document.addEventListener('keydown', function (e) {
        const tag = document.activeElement ? document.activeElement.tagName : '';
        if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(tag)) {
            e.preventDefault();
            input.focus();
            input.select();
        } else if (e.key === 'Escape' && document.activeElement === input && input.value) {
            clearSearch();
        }
    });


    /* ---------- Urutkan ---------- */
    sortSel.addEventListener('change', function () {
        state.sort = sortSel.value;
        state.shown = PAGE;
        render();
    });


    /* ---------- Filter cepat ---------- */
    chips.forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.chip = btn.dataset.chip;
            state.shown = PAGE;
            chips.forEach(function (c) { c.classList.toggle('is-active', c === btn); });
            render();
        });
    });


    /* ---------- Tampilan kartu / daftar ---------- */
    function setView(v) {
        state.view = v;
        list.classList.toggle('is-list', v === 'list');
        viewBtns.forEach(function (b) { b.classList.toggle('is-active', b.dataset.view === v); });
        try { localStorage.setItem('nm_gizi_view', v); } catch (e) {}
    }
    viewBtns.forEach(function (b) {
        b.addEventListener('click', function () { setView(b.dataset.view); });
    });
    try {
        const saved = localStorage.getItem('nm_gizi_view');
        if (saved === 'list' || saved === 'grid') setView(saved);
    } catch (e) {}


    /* ---------- Tampilkan lebih banyak & atur ulang ---------- */
    moreBtn.addEventListener('click', function () {
        state.shown += PAGE;
        render();
    });

    resetBtn.addEventListener('click', function () {
        input.value = '';
        sortSel.value = 'default';
        state.q = '';
        state.chip = 'all';
        state.sort = 'default';
        state.shown = PAGE;
        chips.forEach(function (c) { c.classList.toggle('is-active', c.dataset.chip === 'all'); });
        render();
        input.focus();
    });


    /* ---------- Header: cahaya mengikuti kursor + angka total ---------- */
    if (hero && !reduce) {
        hero.addEventListener('mousemove', function (e) {
            const r = hero.getBoundingClientRect();
            hero.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            hero.style.setProperty('--my', (e.clientY - r.top) + 'px');
        });
        hero.addEventListener('mouseleave', function () {
            hero.style.removeProperty('--mx');
            hero.style.removeProperty('--my');
        });
    }

    if (totalEl) {
        const target = items.length;
        if (reduce || target === 0) {
            totalEl.textContent = target;
        } else {
            const mulai = performance.now();
            (function frame(now) {
                const t = Math.min((now - mulai) / 900, 1);
                totalEl.textContent = Math.round(target * (1 - Math.pow(1 - t, 3)));
                if (t < 1) requestAnimationFrame(frame);
            })(mulai);
        }
    }


    render();
});

</script>

@endpush