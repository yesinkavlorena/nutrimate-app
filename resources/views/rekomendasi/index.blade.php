@extends('layouts.dashboard')

@section('title', 'Rekomendasi Makanan - NutriMate')

@section('content')

<div class="container-fluid rk-page">

    {{-- =====================================================
         HEADER + TARGET KALORI HARIAN
    ====================================================== --}}
    <div class="rk-hero" id="rkHero">

        <div>

            <h1 class="rk-hero-title">
                Rekomendasi Makanan
            </h1>

            <p class="rk-hero-sub">
                Dapatkan rekomendasi makanan yang sesuai dengan
                kebutuhan energi dan waktu makanmu.
            </p>

        </div>

        <div class="rk-daily">

            <div class="rk-daily-icon">
                <i class="fas fa-fire-alt"></i>
            </div>

            <div>

                <div class="rk-daily-label">
                    Target Kalori Harian
                </div>

                <div class="rk-daily-value">

                    @if ($kebutuhanEnergi)

                        ~ <span data-count="{{ round($kebutuhanEnergi->target_kalori_harian) }}">{{ number_format(
                            $kebutuhanEnergi->target_kalori_harian,
                            0,
                            ',',
                            '.'
                        ) }}</span>

                        <small>
                            kcal/hari
                        </small>

                    @else

                        -

                    @endif

                </div>

                <small class="rk-daily-note">
                    Acuan kebutuhan energi harian kamu
                </small>

            </div>

        </div>

    </div>



    {{-- =====================================================
         MINTA REKOMENDASI
    ====================================================== --}}
    <div class="rk-panel mb-4">

        <div class="rk-panel-head">

            <h6 class="rk-panel-title">

                <i class="fas fa-utensils"></i>

                Minta Rekomendasi

            </h6>

        </div>


        <div class="rk-panel-body">

            <p class="rk-lead">

                Pilih waktu makan yang ingin kamu cari
                rekomendasinya.

            </p>


            <form
                action="{{ route('rekomendasi.index') }}"
                method="GET"
                id="rkForm"
            >

                <div class="row">


                    {{-- SARAPAN --}}
                    <div class="col-md-6 col-lg-3 mb-3">

                        <label class="meal-option w-100" data-meal="sarapan">

                            <input
                                type="radio"
                                name="waktu_makan"
                                value="sarapan"
                                {{ request('waktu_makan') == 'sarapan' ? 'checked' : '' }}
                            >

                            <div class="meal-content">

                                <div class="meal-icon">
                                    <i class="fas fa-sun"></i>
                                </div>

                                <div class="meal-text">

                                    <div class="meal-title">
                                        Sarapan
                                        <span class="meal-now">Sekarang</span>
                                    </div>

                                    <small class="text-muted">
                                        Makan pagi
                                    </small>

                                    @if ($kebutuhanEnergi && $kebutuhanEnergi->target_sarapan !== null)
                                        <div class="meal-kcal">
                                            {{ number_format($kebutuhanEnergi->target_sarapan, 0, ',', '.') }} kcal
                                        </div>
                                    @endif

                                </div>

                                <span class="meal-check">
                                    <i class="fas fa-check"></i>
                                </span>

                            </div>

                        </label>

                    </div>



                    {{-- MAKAN SIANG --}}
                    <div class="col-md-6 col-lg-3 mb-3">

                        <label class="meal-option w-100" data-meal="siang">

                            <input
                                type="radio"
                                name="waktu_makan"
                                value="makan_siang"
                                {{ request('waktu_makan') == 'makan_siang' ? 'checked' : '' }}
                            >

                            <div class="meal-content">

                                <div class="meal-icon">
                                    <i class="fas fa-cloud-sun"></i>
                                </div>

                                <div class="meal-text">

                                    <div class="meal-title">
                                        Makan Siang
                                        <span class="meal-now">Sekarang</span>
                                    </div>

                                    <small class="text-muted">
                                        Makan siang
                                    </small>

                                    @if ($kebutuhanEnergi && $kebutuhanEnergi->target_makan_siang !== null)
                                        <div class="meal-kcal">
                                            {{ number_format($kebutuhanEnergi->target_makan_siang, 0, ',', '.') }} kcal
                                        </div>
                                    @endif

                                </div>

                                <span class="meal-check">
                                    <i class="fas fa-check"></i>
                                </span>

                            </div>

                        </label>

                    </div>



                    {{-- MAKAN MALAM --}}
                    <div class="col-md-6 col-lg-3 mb-3">

                        <label class="meal-option w-100" data-meal="malam">

                            <input
                                type="radio"
                                name="waktu_makan"
                                value="makan_malam"
                                {{ request('waktu_makan') == 'makan_malam' ? 'checked' : '' }}
                            >

                            <div class="meal-content">

                                <div class="meal-icon">
                                    <i class="fas fa-moon"></i>
                                </div>

                                <div class="meal-text">

                                    <div class="meal-title">
                                        Makan Malam
                                        <span class="meal-now">Sekarang</span>
                                    </div>

                                    <small class="text-muted">
                                        Makan malam
                                    </small>

                                    @if ($kebutuhanEnergi && $kebutuhanEnergi->target_makan_malam !== null)
                                        <div class="meal-kcal">
                                            {{ number_format($kebutuhanEnergi->target_makan_malam, 0, ',', '.') }} kcal
                                        </div>
                                    @endif

                                </div>

                                <span class="meal-check">
                                    <i class="fas fa-check"></i>
                                </span>

                            </div>

                        </label>

                    </div>



                    {{-- CAMILAN --}}
                    <div class="col-md-6 col-lg-3 mb-3">

                        <label class="meal-option w-100" data-meal="camilan">

                            <input
                                type="radio"
                                name="waktu_makan"
                                value="camilan"
                                {{ request('waktu_makan') == 'camilan' ? 'checked' : '' }}
                            >

                            <div class="meal-content">

                                <div class="meal-icon">
                                    <i class="fas fa-cookie-bite"></i>
                                </div>

                                <div class="meal-text">

                                    <div class="meal-title">
                                        Camilan
                                        <span class="meal-now">Sekarang</span>
                                    </div>

                                    <small class="text-muted">
                                        Snack
                                    </small>

                                    @if ($kebutuhanEnergi && $kebutuhanEnergi->target_snack !== null)
                                        <div class="meal-kcal">
                                            {{ number_format($kebutuhanEnergi->target_snack, 0, ',', '.') }} kcal
                                        </div>
                                    @endif

                                </div>

                                <span class="meal-check">
                                    <i class="fas fa-check"></i>
                                </span>

                            </div>

                        </label>

                    </div>

                </div>


                {{-- PESAN BILA BELUM MEMILIH --}}
                <div class="rk-hint" id="rkHint" role="alert" hidden>
                    <i class="fas fa-info-circle"></i>
                    Pilih salah satu waktu makan dulu, lalu klik Buat Rekomendasi.
                </div>


                {{-- BUTTON --}}
                <div class="text-center mt-3">

                    <button
                        type="submit"
                        class="rk-btn"
                        id="rkSubmit"
                    >

                        <span class="rk-btn-label">
                            <i class="fas fa-magic"></i>
                            Buat Rekomendasi
                        </span>

                        <span class="rk-btn-wait" hidden>
                            <i class="fas fa-spinner fa-spin"></i>
                            Mencari rekomendasi...
                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>



    {{-- =====================================================
         HASIL REKOMENDASI
    ====================================================== --}}
    <div class="rk-panel mb-4" id="hasil-rekomendasi"
         data-has-result="{{ ($waktuMakan && $targetKalori) ? '1' : '0' }}">

        <div class="rk-panel-head">

            <h6 class="rk-panel-title">

                <i class="fas fa-star"></i>

                Hasil Rekomendasi

            </h6>

        </div>


        <div class="rk-panel-body">


            {{-- PESAN SETELAH MENYIMPAN RIWAYAT
                 (di dalam panel hasil, agar tetap terlihat saat halaman menggulir ke hasil) --}}
            @foreach (['success' => 'fa-check-circle', 'info' => 'fa-info-circle', 'error' => 'fa-exclamation-circle'] as $jenis => $ikon)
                @if (session($jenis))
                    <div class="rk-flash rk-flash-{{ $jenis }}" role="{{ $jenis === 'error' ? 'alert' : 'status' }}">
                        <i class="fas {{ $ikon }}"></i>
                        <span>{{ session($jenis) }}</span>
                    </div>
                @endif
            @endforeach


            {{-- BELUM MEMILIH WAKTU MAKAN --}}

            @if (!$waktuMakan || !$targetKalori)

                <div class="rk-empty">

                    <div class="recommendation-empty-icon">

                        <i class="fas fa-utensils"></i>

                    </div>


                    <h5>

                        Belum Ada Rekomendasi

                    </h5>


                    <p>

                        Pilih waktu makan terlebih dahulu,
                        kemudian klik tombol
                        <strong>Buat Rekomendasi</strong>.

                    </p>

                </div>


            @else


                {{-- INFORMASI TARGET --}}

                <div class="recommendation-header">

                    <div>

                        <div class="rk-eyebrow">

                            Rekomendasi Untuk

                        </div>


                        <h4 class="rk-result-title">

                            {{ $namaWaktuMakan }}

                        </h4>


                        <p class="rk-result-target">

                            Target energi:

                            <strong>

                                <span data-count="{{ round($targetKalori) }}">{{ number_format(
                                    $targetKalori,
                                    0,
                                    ',',
                                    '.'
                                ) }}</span>

                                kcal

                            </strong>

                        </p>

                        @if ($batasBawah !== null && $batasAtas !== null)

                            <span class="rk-range">

                                <i class="fas fa-bullseye"></i>

                                Rentang kalori:

                                {{ number_format(
                                    $batasBawah,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                                -

                                {{ number_format(
                                    $batasAtas,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                                kcal

                            </span>

                        @endif

                    </div>


                    <div class="recommendation-target-icon">

                        <i class="fas fa-bullseye"></i>

                    </div>

                </div>



                {{-- INFORMASI HEALTH-BASED FILTERING --}}

                <details class="rk-how">

                    <summary>

                        <span class="rk-how-icon">
                            <i class="fas fa-filter"></i>
                        </span>

                        <span>
                            Health-Based Filtering
                            <small>Lihat cara sistem memilih makanan</small>
                        </span>

                        <i class="fas fa-chevron-down rk-how-arrow"></i>

                    </summary>

                    <div class="rk-how-body">

                        <small>

                            Sistem menyaring makanan berdasarkan
                            waktu makan dan kandungan kalori dalam
                            rentang <strong>±20%</strong> dari target
                            energi waktu makan.

                            Selanjutnya, makanan diurutkan berdasarkan
                            selisih kalori terkecil dan ditampilkan
                            maksimal 5 makanan.

                        </small>

                    </div>

                </details>



                {{-- DAFTAR REKOMENDASI --}}

                @if ($hasilRekomendasi->count() > 0)

                    <div class="rk-list-head">

                        <div>

                            <h6>

                                {{ $hasilRekomendasi->count() }} Rekomendasi Makanan

                            </h6>


                            <small class="text-muted">

                                Makanan yang memiliki kandungan kalori
                                paling mendekati target.

                            </small>

                        </div>


                        <div class="rk-list-actions">

                            <div class="recommendation-count">

                                <i class="fas fa-utensils"></i>

                                {{ $hasilRekomendasi->count() }} makanan

                            </div>


                            <form action="{{ route('rekomendasi.simpan') }}" method="POST" class="rk-save">
                                @csrf

                                <input
                                    type="hidden"
                                    name="waktu_makan"
                                    value="{{ $waktuMakan }}"
                                >

                                <button type="submit" class="rk-btn rk-btn-outline">
                                    <span class="rk-btn-label">
                                        <i class="fas fa-history"></i>
                                        Simpan Riwayat
                                    </span>
                                    <span class="rk-btn-wait" hidden>
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Menyimpan...
                                    </span>
                                </button>
                            </form>

                        </div>

                    </div>


                    <div class="row">


                        @foreach ($hasilRekomendasi as $index => $item)

                            {{-- SELISIH, POSISI DALAM RENTANG, KOMPOSISI MAKRO --}}
                            @php

                                $selisihKalori = abs(
                                    $item->kalori - $targetKalori
                                );

                                $punyaRentang = $batasBawah !== null
                                    && $batasAtas !== null
                                    && $batasAtas > $batasBawah;

                                $posisiMakanan = $punyaRentang
                                    ? max(0, min(100, (($item->kalori - $batasBawah) / ($batasAtas - $batasBawah)) * 100))
                                    : 50;

                                $posisiTarget = $punyaRentang
                                    ? max(0, min(100, (($targetKalori - $batasBawah) / ($batasAtas - $batasBawah)) * 100))
                                    : 50;

                                $mProtein = (float) $item->protein;
                                $mLemak   = (float) $item->lemak;
                                $mKarbo   = (float) $item->karbohidrat;
                                $mTotal   = $mProtein + $mLemak + $mKarbo;

                                $pProtein = $mTotal > 0 ? round($mProtein / $mTotal * 100, 1) : 0;
                                $pLemak   = $mTotal > 0 ? round($mLemak / $mTotal * 100, 1) : 0;
                                $pKarbo   = $mTotal > 0 ? round($mKarbo / $mTotal * 100, 1) : 0;

                            @endphp

                            <div class="col-md-6 col-xl-4 mb-4">

                                <div class="recommendation-food-card h-100"
                                     style="--i: {{ $index }};">


                                    {{-- NOMOR --}}

                                    <div class="recommendation-number">

                                        {{ $index + 1 }}

                                    </div>



                                    <div class="recommendation-food-body">


                                        @if ($index === 0)

                                            <span class="rk-best">
                                                <i class="fas fa-trophy"></i>
                                                Paling mendekati target
                                            </span>

                                        @endif


                                        {{-- NAMA MAKANAN --}}

                                        <h5 class="rk-food-name">

                                            {{ $item->nama_makanan }}

                                        </h5>



                                        {{-- KALORI --}}

                                        <div class="food-calorie">

                                            <i class="fas fa-fire-alt"></i>

                                            {{ number_format(
                                                $item->kalori,
                                                0,
                                                ',',
                                                '.'
                                            ) }}

                                            kcal

                                        </div>



                                        {{-- POSISI KALORI DALAM RENTANG TARGET --}}

                                        @if ($punyaRentang)

                                            <div class="rk-gauge"
                                                 style="--pos: {{ round($posisiMakanan, 1) }}%; --pt: {{ round($posisiTarget, 1) }}%;">

                                                <div class="rk-gauge-label">
                                                    Posisi dalam rentang target
                                                </div>

                                                <div class="rk-gauge-track">
                                                    <span class="rk-gauge-target"></span>
                                                    <span class="rk-gauge-dot"></span>
                                                </div>

                                                <div class="rk-gauge-scale">
                                                    <span>{{ number_format($batasBawah, 0, ',', '.') }}</span>
                                                    <span>Target</span>
                                                    <span>{{ number_format($batasAtas, 0, ',', '.') }}</span>
                                                </div>

                                            </div>

                                        @endif



                                        {{-- KOMPOSISI MAKRO --}}

                                        <div class="rk-macro" title="Perbandingan protein, lemak, dan karbohidrat">

                                            <i class="m-p" style="--w: {{ $pProtein }}%"></i>
                                            <i class="m-l" style="--w: {{ $pLemak }}%"></i>
                                            <i class="m-k" style="--w: {{ $pKarbo }}%"></i>

                                        </div>



                                        {{-- INFORMASI GIZI --}}

                                        <div class="nutrition-info">

                                            <div class="nutrition-item">

                                                <span>
                                                    <i class="rk-dot m-p"></i>
                                                    Protein
                                                </span>

                                                <strong>
                                                    {{ $item->protein }} g
                                                </strong>

                                            </div>


                                            <div class="nutrition-item">

                                                <span>
                                                    <i class="rk-dot m-l"></i>
                                                    Lemak
                                                </span>

                                                <strong>
                                                    {{ $item->lemak }} g
                                                </strong>

                                            </div>


                                            <div class="nutrition-item">

                                                <span>
                                                    <i class="rk-dot m-k"></i>
                                                    Karbohidrat
                                                </span>

                                                <strong>
                                                    {{ $item->karbohidrat }} g
                                                </strong>

                                            </div>

                                        </div>



                                        {{-- SELISIH KALORI --}}

                                        <div class="food-distance">

                                            <i class="fas fa-bullseye"></i>

                                            Selisih dari target:

                                            <strong>

                                                {{ number_format(
                                                    $selisihKalori,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}

                                                kcal

                                            </strong>

                                            @if ($selisihKalori > 0)
                                                <span class="rk-dir">
                                                    ({{ $item->kalori > $targetKalori ? 'di atas' : 'di bawah' }} target)
                                                </span>
                                            @endif

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>



                    {{-- CATATAN --}}

                    <div class="rk-note">

                        <i class="fas fa-info-circle"></i>

                        <small>

                            Sistem menampilkan maksimal 5 makanan
                            yang memenuhi rentang kalori ±20%.
                            Urutan rekomendasi berdasarkan selisih
                            kalori terkecil terhadap target.

                        </small>

                    </div>


                @else


                    {{-- TIDAK ADA MAKANAN --}}

                    <div class="rk-empty">

                        <div class="recommendation-empty-icon">

                            <i class="fas fa-search"></i>

                        </div>


                        <h5>

                            Belum Ada Makanan yang Sesuai

                        </h5>


                        <p>

                            Belum terdapat makanan
                            {{ $namaWaktuMakan }}
                            yang berada dalam rentang
                            ±20% dari target kalori.

                        </p>

                    </div>

                @endif

            @endif

        </div>


        {{-- KERANGKA PEMUAT (muncul saat rekomendasi diproses) --}}
        <div class="rk-loading" id="rkLoading" hidden aria-hidden="true">

            <div class="rk-loading-title">
                <i class="fas fa-spinner fa-spin"></i>
                Mencari makanan yang paling mendekati target...
            </div>

            <div class="row">
                <div class="col-md-6 col-xl-4 mb-4"><div class="rk-skel"></div></div>
                <div class="col-md-6 col-xl-4 mb-4 d-none d-md-block"><div class="rk-skel"></div></div>
                <div class="col-xl-4 mb-4 d-none d-xl-block"><div class="rk-skel"></div></div>
            </div>

        </div>

    </div>

</div>

@endsection


@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/rekomendasi.css') }}?v={{ filemtime(public_path('css/rekomendasi.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/rekomendasi.js') }}?v={{ filemtime(public_path('js/rekomendasi.js')) }}" defer></script>
@endpush