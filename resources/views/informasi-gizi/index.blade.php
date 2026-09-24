@extends('layouts.dashboard')

@section('title', 'Informasi Gizi Makanan - NutriMate')

@section('content')

<div class="container-fluid">

```
{{-- Header --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">
            Informasi Gizi Makanan
        </h1>

        <p class="mb-0 text-gray-600">
            Lihat informasi gizi dari berbagai makanan.
        </p>
    </div>
</div>


{{-- Search --}}
<div class="card shadow mb-4">

    <div class="card-body">

        <div class="search-food-wrapper">

            <label
                for="searchFood"
                class="font-weight-bold text-gray-800"
            >
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

        </div>

    </div>

</div>


{{-- Daftar makanan --}}
<div class="card shadow mb-4">

    <div class="card-header py-3">

        <h6 class="m-0 font-weight-bold text-success">
            Daftar Makanan
        </h6>

    </div>

    <div class="card-body">

        <div class="row" id="food-list">

            @foreach($makanan as $item)

                <div
                    class="col-xl-3 col-lg-4 col-md-6 mb-4 food-item"
                    data-food-name="{{ strtolower($item->nama_makanan) }}"
                >

                    <div class="food-card">

                        {{-- Gambar --}}
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


                        {{-- Nama --}}
                        <div class="food-name">
                            {{ $item->nama_makanan }}
                        </div>


                        {{-- Informasi gizi --}}
                        <div class="nutrition-info">

                            <div class="nutrition-row">
                                <span>Kalori</span>
                                <strong>
                                    {{ $item->kalori }} kkal
                                </strong>
                            </div>

                            <div class="nutrition-row">
                                <span>Protein</span>
                                <strong>
                                    {{ $item->protein }} g
                                </strong>
                            </div>

                            <div class="nutrition-row">
                                <span>Lemak</span>
                                <strong>
                                    {{ $item->lemak }} g
                                </strong>
                            </div>

                            <div class="nutrition-row">
                                <span>Karbohidrat</span>
                                <strong>
                                    {{ $item->karbohidrat }} g
                                </strong>
                            </div>

                        </div>

                    </div>

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
       FOOD CARD
    ============================== */

    .food-card {
        height: 100%;
        padding: 12px;

        border: 1px solid #e3e6f0;
        border-radius: 12px;

        background: #fff;

        transition: all 0.2s ease;
    }

    .food-card:hover {
        transform: translateY(-3px);

        box-shadow: 0 5px 15px rgba(0,0,0,0.08);

        border-color: #28a745;
    }


    /* ==============================
       FOOD IMAGE
    ============================== */

    .food-image {
        width: 100%;
        height: 170px;

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
       FOOD NAME
    ============================== */

    .food-name {
        font-size: 16px;
        font-weight: 700;

        color: #3a3b45;

        margin-bottom: 12px;
    }


    /* ==============================
       NUTRITION
    ============================== */

    .nutrition-info {
        border-top: 1px solid #e3e6f0;
        padding-top: 8px;
    }

    .nutrition-row {
        display: flex;
        justify-content: space-between;

        font-size: 13px;

        padding: 3px 0;
    }

    .nutrition-row span {
        color: #858796;
    }

    .nutrition-row strong {
        color: #3a3b45;
    }

</style>

@endpush

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput =
        document.getElementById('searchFood');

    const foodItems =
        document.querySelectorAll('.food-item');

    const noFoodResult =
        document.getElementById('no-food-result');


    searchInput.addEventListener('input', function () {

        const keyword =
            this.value.toLowerCase().trim();

        let visibleCount = 0;


        foodItems.forEach(function (item) {

            const foodName =
                item.dataset.foodName;


            if (foodName.includes(keyword)) {

                item.style.display = '';

                visibleCount++;

            } else {

                item.style.display = 'none';

            }

        });


        if (visibleCount === 0) {

            noFoodResult.style.display = 'block';

        } else {

            noFoodResult.style.display = 'none';

        }

    });

});

</script>

@endpush
