<div class="row">

    <div class="col-12">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h6 class="font-weight-bold text-gray-800 mb-0">
                Food List
            </h6>

            <a href="{{ route('informasi.gizi') }}" class="small text-success">
                See All
            </a>

        </div>

    </div>


    @forelse($makanan as $item)

        <div class="col-xl-3 col-md-6 mb-4">

            <div class="card shadow-sm border-0 h-100">

                {{-- Gambar makanan --}}
                @if($item->gambar_makanan)

                    <img
                        src="{{ $item->gambar_makanan }}"
                        alt="{{ $item->nama_makanan }}"
                        class="card-img-top"
                        style="height: 180px; object-fit: cover;"
                    >

                @else

                    <div class="food-placeholder">

                        <i class="far fa-image"></i>

                    </div>

                @endif


                {{-- Informasi makanan --}}
                <div class="card-body">

                    <h6 class="font-weight-bold text-gray-800 mb-1">
                        {{ $item->nama_makanan }}
                    </h6>

                    <small class="text-muted">
                        {{ $item->kalori }} kcal
                    </small>

                </div>

            </div>

        </div>

    @empty

        <div class="col-12">

            <div class="alert alert-light text-center">
                Belum ada data makanan.
            </div>

        </div>

    @endforelse

</div>