@extends('layouts.dashboard')

@section('title', 'Edit Preferensi Makanan - NutriMate')

{{-- Memakai CSS dan JS yang sama dengan halaman pilih preferensi --}}
@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/preferensi.css') }}?v={{ @filemtime(public_path('css/preferensi.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/preferensi.js') }}?v={{ @filemtime(public_path('js/preferensi.js')) }}" defer></script>
@endpush

@section('content')

@php
    // pilihan yang tersimpan di database
    $awal = collect($selected ?? [])->map(fn ($v) => (string) $v)->values()->all();

    // jika validasi gagal, pilihan terakhir pengguna dipulihkan
    $dipilih = collect(old('makanan', $awal))->map(fn ($v) => (string) $v)->all();
@endphp

<div class="pf-page"
     id="pfPage"
     data-max="7"
     data-mode="edit"
     data-save-label="Simpan Perubahan"
     data-initial="{{ json_encode($awal) }}">

    {{-- Header --}}
    <header class="pf-head pf-head-split">

        <div>
            <h1 class="pf-title">Edit Preferensi Makanan</h1>
            <p class="pf-sub" id="pfHint">Ubah 7 makanan favorit kamu.</p>
        </div>

        <a href="{{ route('profil.create') }}" class="pf-back">
            <i class="fas fa-arrow-left"></i>
            Batal
        </a>

    </header>


    <section class="pf-panel">

        {{-- Pencarian + filter --}}
        <div class="pf-toolbar">

            <div class="pf-search">
                <i class="fas fa-search pf-search-ico"></i>

                <input type="text"
                       id="pfSearch"
                       placeholder="Cari makanan...  (tekan / untuk mencari)"
                       autocomplete="off"
                       aria-label="Cari makanan">

                <button type="button" class="pf-clear" id="pfClear" aria-label="Hapus pencarian" hidden>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="pf-tabs" role="group" aria-label="Filter makanan">
                <button type="button" class="pf-tab is-active" data-filter="all" aria-pressed="true">
                    Semua <span id="pfCountAll">{{ count($makanan) }}</span>
                </button>
                <button type="button" class="pf-tab" data-filter="selected" aria-pressed="false">
                    Terpilih <span id="pfCountSel">0</span>
                </button>
            </div>

        </div>

        <p class="pf-result" id="pfResult" aria-live="polite"></p>


        {{-- Error dari server --}}
        @if($errors->any())
            <div class="pf-alert" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif


        <form action="{{ route('preferensi.update') }}" method="POST" id="pfForm" novalidate>

            @csrf

            {{-- Daftar makanan --}}
            <div class="pf-grid" id="pfGrid">

                @foreach ($makanan as $item)

                    <label class="pf-item" data-name="{{ mb_strtolower($item->nama_makanan) }}">

                        <input type="checkbox"
                               class="pf-input"
                               name="makanan[]"
                               value="{{ $item->id_makanan }}"
                               {{ in_array((string) $item->id_makanan, $dipilih, true) ? 'checked' : '' }}>

                        <span class="pf-card">

                            <span class="pf-badge"><i class="fas fa-check"></i></span>

                            <span class="pf-img">
                                @if($item->gambar_makanan)
                                    <img src="{{ $item->gambar_makanan }}"
                                         alt="{{ $item->nama_makanan }}"
                                         loading="lazy"
                                         decoding="async">
                                @else
                                    <span class="pf-img-ph"><i class="fas fa-utensils"></i></span>
                                @endif
                            </span>

                            <span class="pf-name">{{ $item->nama_makanan }}</span>

                            <span class="pf-kcal">
                                <i class="fas fa-fire-alt"></i>
                                {{ $item->kalori }} kkal
                            </span>

                        </span>

                    </label>

                @endforeach

            </div>


            {{-- Tidak ditemukan --}}
            <div class="pf-empty" id="pfEmpty" hidden>
                <i class="fas fa-search"></i>
                <h3 id="pfEmptyTitle">Makanan tidak ditemukan</h3>
                <p id="pfEmptyText">Coba gunakan kata pencarian lain.</p>
            </div>


            {{-- Bilah bawah: selalu terlihat saat menggulir --}}
            <div class="pf-dock" id="pfDock" data-state="empty">

                {{-- Cincin progres + status --}}
                <div class="pf-dock-head">

                    <div class="pf-ring" id="pfRingWrap">
                        <svg viewBox="0 0 64 64" aria-hidden="true">
                            <circle class="pf-ring-bg" cx="32" cy="32" r="26"></circle>
                            <circle class="pf-ring-fg" id="pfRing" cx="32" cy="32" r="26"></circle>
                        </svg>
                        <span class="pf-ring-num" id="pfCount">0/7</span>
                    </div>

                    <div class="pf-dock-info">
                        <strong id="pfMeterTitle">Mulai memilih</strong>
                        <span id="pfMeterText">Klik kartu makanan untuk memilih</span>
                    </div>

                </div>

                {{-- Slot pilihan (diisi JS, satu slot per makanan) --}}
                <div class="pf-slots" id="pfSlots" role="group" aria-label="Makanan terpilih"></div>

                <div class="pf-dock-actions">
                    {{-- Kosongkan semua --}}
                    <button type="button" class="pf-reset" id="pfReset" aria-label="Kosongkan semua pilihan" disabled>
                        <i class="fas fa-eraser"></i>
                        <span>Kosongkan</span>
                    </button>

                    {{-- Kembali ke pilihan yang tersimpan --}}
                    <button type="button" class="pf-reset pf-restore" id="pfRestore" aria-label="Kembalikan ke pilihan tersimpan" disabled>
                        <i class="fas fa-undo"></i>
                        <span>Kembalikan</span>
                    </button>

                    <button type="submit" class="pf-save" id="pfSave" disabled>
                        <span class="pf-save-label">
                            <i class="fas fa-save"></i>
                            <span id="pfSaveLabel">Belum ada perubahan</span>
                        </span>
                        <span class="pf-save-wait" hidden>
                            <i class="fas fa-spinner fa-spin"></i>
                            Menyimpan...
                        </span>
                    </button>

                </div>

            </div>

        </form>

    </section>

</div>

<div class="pf-toast" id="pfToast" role="status" aria-live="polite"></div>

@endsection