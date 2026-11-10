@extends('layouts.dashboard')

@section('title', 'Riwayat Rekomendasi - NutriMate')

@section('content')
<div class="container-fluid rw-page">

    {{-- =====================================================
         HEADER
    ====================================================== --}}
    <div class="rw-hero" id="rwHero">

        <div>
            <h1 class="rw-hero-title">Riwayat Rekomendasi</h1>
            <p class="rw-hero-sub">
                Lihat kembali hasil rekomendasi makanan yang telah disimpan.
            </p>
        </div>

        <a href="{{ route('rekomendasi.index') }}"
           class="btn btn-success">
            <i class="fas fa-utensils mr-2"></i>
            Buat Rekomendasi
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert"
                    aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($riwayat->isEmpty())
        <div class="rw-empty">
            <div class="rw-empty-icon">
                <i class="fas fa-history"></i>
            </div>
            <h5>
                Belum Ada Riwayat
            </h5>
            <p>
                Kamu belum menyimpan hasil rekomendasi makanan.
            </p>
            <a href="{{ route('rekomendasi.index') }}"
               class="btn btn-success">
                Mulai Rekomendasi
            </a>
        </div>
    @else


        {{-- =====================================================
             TOOLBAR: cari, urutkan, buka/tutup semua, filter
        ====================================================== --}}
        @if ($riwayat->count() > 1)

            <div class="rw-toolbar" id="rwToolbar">

                <div class="rw-search">

                    <i class="fas fa-search"></i>

                    <input
                        type="text"
                        id="rwSearch"
                        class="rw-search-input"
                        placeholder="Cari nama makanan di riwayat, tekan / untuk mulai mengetik"
                        autocomplete="off"
                        aria-label="Cari makanan di riwayat"
                    >

                    <button type="button" id="rwClear" class="rw-clear" aria-label="Hapus pencarian">
                        <i class="fas fa-times"></i>
                    </button>

                    <kbd class="rw-kbd">/</kbd>

                </div>

                <div class="rw-controls">

                    <select id="rwSort" class="rw-select" aria-label="Urutkan riwayat">
                        <option value="default">Terbaru dulu</option>
                        <option value="oldest">Terlama dulu</option>
                    </select>

                    <div class="rw-toggle" role="group" aria-label="Buka atau tutup semua">
                        <button type="button" class="rw-toggle-btn" id="rwOpenAll" title="Buka semua" aria-label="Buka semua">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <button type="button" class="rw-toggle-btn" id="rwCloseAll" title="Tutup semua" aria-label="Tutup semua">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                </div>

                <div class="rw-chips" id="rwChips">

                    <button type="button" class="rw-chip is-active" data-chip="all">
                        Semua <span class="rw-chip-n"></span>
                    </button>

                    <button type="button" class="rw-chip" data-chip="sarapan" data-meal="sarapan">
                        <i class="fas fa-sun"></i> Sarapan <span class="rw-chip-n"></span>
                    </button>

                    <button type="button" class="rw-chip" data-chip="makan_siang" data-meal="siang">
                        <i class="fas fa-cloud-sun"></i> Makan Siang <span class="rw-chip-n"></span>
                    </button>

                    <button type="button" class="rw-chip" data-chip="makan_malam" data-meal="malam">
                        <i class="fas fa-moon"></i> Makan Malam <span class="rw-chip-n"></span>
                    </button>

                    <button type="button" class="rw-chip" data-chip="camilan" data-meal="camilan">
                        <i class="fas fa-cookie-bite"></i> Camilan <span class="rw-chip-n"></span>
                    </button>

                </div>

            </div>

            <div class="rw-count" id="rwCount" aria-live="polite"></div>

        @endif


        <div class="row" id="rwList">
            @foreach ($riwayat as $item)

                @php

                    $peta = [
                        'sarapan'     => ['sarapan', 'fa-sun'],
                        'makan_siang' => ['siang', 'fa-cloud-sun'],
                        'makan_malam' => ['malam', 'fa-moon'],
                        'camilan'     => ['camilan', 'fa-cookie-bite'],
                    ];

                    $kunci = $peta[$item->waktu_makan][0] ?? 'siang';
                    $ikon  = $peta[$item->waktu_makan][1] ?? 'fa-history';

                    $detailUrut  = $item->detailRekomendasi->sortBy('ranking');
                    $teratas     = $detailUrut->first();
                    $namaTeratas = ($teratas && $teratas->makanan) ? $teratas->makanan->nama_makanan : null;

                    $maxHybrid = (float) $detailUrut->max(fn ($d) => (float) $d->hybrid_score);

                    $namaMakanan = $detailUrut
                        ->map(fn ($d) => strtolower($d->makanan->nama_makanan ?? ''))
                        ->implode('|');

                    $ts = $item->created_at ? $item->created_at->timestamp : 0;

                @endphp

                <div class="col-12 mb-4 rw-item"
                     data-meal="{{ $kunci }}"
                     data-waktu="{{ $item->waktu_makan }}"
                     data-ts="{{ $ts }}"
                     data-foods="{{ $namaMakanan }}"
                     style="--i: {{ $loop->index }};">

                    <details class="rw-card" @if ($loop->first) open @endif>

                        <summary class="rw-head">

                            <div class="rw-meal-icon">
                                <i class="fas {{ $ikon }}"></i>
                            </div>

                            <div class="rw-head-main">

                                <h6 class="rw-title">
                                    {{ ucfirst(str_replace('_', ' ', $item->waktu_makan)) }}
                                </h6>

                                <small class="rw-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Disimpan:
                                    {{ $item->created_at
                                        ? $item->created_at->format('d-m-Y H:i')
                                        : '-' }}
                                    <span class="rw-rel" data-ts="{{ $ts }}"></span>
                                </small>

                                @if ($namaTeratas)
                                    <div class="rw-top">
                                        <i class="fas fa-trophy"></i>
                                        Teratas: <strong>{{ $namaTeratas }}</strong>
                                    </div>
                                @endif

                            </div>

                            <div class="rw-head-side">
                                <div class="rw-small">
                                    Target energi
                                </div>
                                <div class="rw-energy">
                                    {{ number_format((float) $item->target_energi, 2, ',', '.') }}
                                    kkal
                                </div>
                                <span class="badge badge-success">
                                    {{ $item->detailRekomendasi->count() }}
                                    makanan
                                </span>
                            </div>

                            {{-- Hapus riwayat: konfirmasi ditangani riwayat.js --}}
                            <button type="button"
                                    class="rw-delete"
                                    data-action="{{ route('riwayat.destroy', $item->id_rekomendasi) }}"
                                    data-judul="{{ ucfirst(str_replace('_', ' ', $item->waktu_makan)) }}"
                                    data-tanggal="{{ $item->created_at ? $item->created_at->format('d-m-Y H:i') : '-' }}"
                                    data-jumlah="{{ $item->detailRekomendasi->count() }}"
                                    title="Hapus riwayat"
                                    aria-label="Hapus riwayat {{ ucfirst(str_replace('_', ' ', $item->waktu_makan)) }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>

                            <i class="fas fa-chevron-down rw-arrow"></i>

                        </summary>

                        <div class="rw-body">
                            <div class="table-responsive">
                                <table class="table rw-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Peringkat</th>
                                            <th>Nama Makanan</th>
                                            <th class="text-center">Kalori</th>
                                            <th class="text-center">Similarity</th>
                                            <th class="text-center">Macro Score</th>
                                            <th class="text-center">Hybrid Score</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($detailUrut as $detail)
                                            <tr data-name="{{ strtolower($detail->makanan->nama_makanan ?? '') }}">
                                                <td class="text-center align-middle" data-label="Peringkat">
                                                    <span class="rw-rank" data-rank="{{ $detail->ranking }}">
                                                        {{ $detail->ranking }}
                                                    </span>
                                                </td>

                                                <td class="align-middle" data-label="Nama Makanan">
                                                    <span class="rw-name">{{ $detail->makanan->nama_makanan ?? 'Makanan tidak tersedia' }}</span>
                                                </td>

                                                <td class="text-center align-middle" data-label="Kalori">
                                                    @if ($detail->makanan)
                                                        {{ number_format((float) $detail->makanan->kalori, 2, ',', '.') }}
                                                        kkal
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td class="text-center align-middle" data-label="Similarity">
                                                    {{ number_format((float) $detail->skor_similarity, 4, ',', '.') }}
                                                </td>

                                                <td class="text-center align-middle" data-label="Macro Score">
                                                    {{ $detail->macro_score !== null
                                                        ? number_format((float) $detail->macro_score, 4, ',', '.')
                                                        : '-' }}
                                                </td>

                                                <td class="text-center align-middle rw-hybrid" data-label="Hybrid Score">
                                                    @if ($detail->hybrid_score !== null)
                                                        <span class="rw-hybrid-num">
                                                            {{ number_format((float) $detail->hybrid_score, 4, ',', '.') }}
                                                        </span>
                                                        <span class="rw-bar">
                                                            <i style="--w: {{ $maxHybrid > 0 ? round(((float) $detail->hybrid_score / $maxHybrid) * 100, 1) : 0 }}%"></i>
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    class="text-center text-muted py-4">
                                                    Tidak ada detail makanan pada riwayat ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </details>

                </div>
            @endforeach
        </div>


        {{-- Tidak ada riwayat yang cocok dengan pencarian atau filter --}}
        <div class="rw-empty" id="rwNoResult" style="display: none;">
            <div class="rw-empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h5>Riwayat tidak ditemukan</h5>
            <p>Coba kata pencarian lain atau pilih filter Semua.</p>
            <button type="button" class="btn btn-success" id="rwReset">
                Atur ulang pencarian
            </button>
        </div>


        {{-- Formulir hapus: alamat diisi dan dikirim oleh riwayat.js setelah konfirmasi --}}
        <form id="rwDeleteForm" method="POST" action="" hidden>
            @csrf
            @method('DELETE')
        </form>

    @endif

</div>
@endsection


@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/riwayat.css') }}?v={{ filemtime(public_path('css/riwayat.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/riwayat.js') }}?v={{ filemtime(public_path('js/riwayat.js')) }}" defer></script>
@endpush