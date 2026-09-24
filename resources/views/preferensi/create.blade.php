@extends('layouts.dashboard')

@section('title', 'Pilih Makanan Favorit - NutriMate')

@section('content')

<div class="container-fluid">

```
{{-- Header --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">Pilih Makanan Favorit</h1>
        <p class="mb-0 text-gray-600">
            Pilih 7 makanan yang paling kamu sukai.
        </p>
    </div>
</div>

{{-- Card utama --}}
<div class="card shadow mb-4">
    <div class="card-body">

        {{-- Search --}}
        <div class="search-food-wrapper mb-4">
            <label for="searchFood" class="font-weight-bold text-gray-800">
                Cari Makanan
            </label>

            <div class="search-food-box">
                <i class="fas fa-search"></i>

                <input
                    type="text"
                    id="searchFood"
                    class="form-control"
                    placeholder="Ketik nama makanan..."
                    autocomplete="off"
                >
            </div>

            <small class="text-muted">
                Cari berdasarkan nama makanan.
            </small>
        </div>

        {{-- Counter --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="font-weight-bold text-gray-800">
                    Pilih Makanan
                </span>
            </div>

            <div>
                <span id="selected-count" class="badge badge-success px-3 py-2">
                    0/7
                </span>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('preferensi.store') }}" method="POST">
            @csrf

            <div class="food-selection-box">

                <div class="row" id="food-list">

                    @foreach ($makanan as $item)

                        <div
                            class="col-xl-3 col-lg-4 col-md-6 mb-4 food-item"
                            data-food-name="{{ strtolower($item->nama_makanan) }}"
                        >

                            <label class="food-card-wrapper">

                                <input
                                    type="checkbox"
                                    name="makanan[]"
                                    value="{{ $item->id_makanan }}"
                                    class="food-checkbox"
                                >

                                <div class="food-card">

                                    <div class="food-image">
                                        @if($item->gambar_makanan)
                                            <img
                                                src="{{ $item->gambar_makanan }}"
                                                alt="{{ $item->nama_makanan }}"
                                            >
                                        @else
                                            <div class="food-image-placeholder">
                                                <i class="fas fa-utensils"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="food-name">
                                        {{ $item->nama_makanan }}
                                    </div>

                                    <div class="food-calorie">
                                        {{ $item->kalori }} kkal
                                    </div>

                                </div>

                            </label>

                        </div>

                    @endforeach

                </div>

                {{-- Tidak ditemukan --}}
                <div
                    id="no-food-result"
                    class="text-center py-5"
                    style="display: none;"
                >
                    <i class="fas fa-search fa-3x text-gray-300 mb-3"></i>

                    <h5 class="text-gray-600">
                        Makanan tidak ditemukan
                    </h5>

                    <p class="text-muted mb-0">
                        Coba gunakan kata pencarian lain.
                    </p>
                </div>

            </div>

            {{-- Tombol --}}
            <div class="text-right mt-4">

                <button
                    type="submit"
                    id="save-button"
                    class="btn btn-success px-4"
                    disabled
                >
                    <i class="fas fa-check mr-2"></i>
                    Simpan Preferensi
                </button>

            </div>

        </form>

    </div>
</div>
```

</div>

@endsection

@push('styles')

<style>

    /* ==============================
       SEARCH
    ============================== */

    .search-food-wrapper {
        max-width: 600px;
    }

    .search-food-box {
        position: relative;
        margin-top: 8px;
    }

    .search-food-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 2;
    }

    .search-food-box input {
        padding-left: 42px;
        height: 45px;
        border-radius: 8px;
        border: 1px solid #d1d3e2;
    }

    .search-food-box input:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
    }


    /* ==============================
       FOOD SELECTION
    ============================== */

    .food-selection-box {
        height: 450px;
        overflow-y: auto;
        padding: 10px;
    }

    .food-selection-box::-webkit-scrollbar {
        width: 7px;
    }

    .food-selection-box::-webkit-scrollbar-thumb {
        background: #b7d8bd;
        border-radius: 10px;
    }


    /* ==============================
       FOOD CARD
    ============================== */

    .food-card-wrapper {
        display: block;
        cursor: pointer;
        margin: 0;
        height: 100%;
    }

    .food-card-wrapper input {
        display: none;
    }

    .food-card {
        position: relative;
        height: 100%;
        padding: 10px;
        border: 2px solid #e3e6f0;
        border-radius: 12px;
        background: #fff;
        transition: all 0.2s ease;
    }

    .food-card:hover {
        border-color: #28a745;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    /* Checkbox selected */
    .food-card-wrapper input:checked + .food-card {
        border-color: #28a745;
        background-color: #f0fff3;
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.12);
    }

    .food-card-wrapper input:checked + .food-card::after {
        content: "\f00c";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;

        position: absolute;
        top: 8px;
        right: 8px;

        width: 28px;
        height: 28px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;
        background: #28a745;
        color: white;

        font-size: 13px;
    }


    /* ==============================
       FOOD IMAGE
    ============================== */

    .food-image {
        width: 100%;
        height: 150px;
        margin-bottom: 12px;

        border-radius: 10px;
        overflow: hidden;

        background-color: #e8f5e9;
    }

    .food-image img {
        width: 100%;
        height: 100%;

        object-fit: cover;
        display: block;

        transition: transform 0.2s ease;
    }

    .food-card:hover .food-image img {
        transform: scale(1.03);
    }

    .food-image-placeholder {
        width: 100%;
        height: 100%;

        display: flex;
        align-items: center;
        justify-content: center;

        color: #2E7D32;
        font-size: 35px;
    }


    /* ==============================
       FOOD TEXT
    ============================== */

    .food-name {
        font-weight: 700;
        color: #3a3b45;
        margin-bottom: 5px;
    }

    .food-calorie {
        font-size: 13px;
        color: #858796;
    }

</style>

@endpush

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchFood');
    const foodItems = document.querySelectorAll('.food-item');
    const noFoodResult =
