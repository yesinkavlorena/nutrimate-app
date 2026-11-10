<div class="row mb-4">

    <div class="col-12">

        <div class="card nm-glow nm-meal-wrap shadow border-0">

            <div class="card-body">
                
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h5 class="font-weight-bold text-gray-800 mb-1">
                            Target Kalori Berdasarkan Waktu Makan
                        </h5>

                        <p class="text-muted mb-0">
                            Acuan kebutuhan energi untuk setiap waktu makan berdasarkan
                            kebutuhan energi harian kamu.
                        </p>

                    </div>

                    <div class="d-none d-md-block">

                        <i class="fas fa-utensils fa-3x text-gray-200"></i>

                    </div>

                </div>


                {{-- Target Kalori --}}
                <div class="row">


                    {{-- Sarapan --}}
                    <div class="col-xl-3 col-md-6 mb-3">

                        <div class="card border-left-success shadow-sm h-100">

                            <div class="card-body">

                                <div class="row no-gutters align-items-center">

                                    <div class="col mr-2">

                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Sarapan
                                        </div>

                                        <div class="h5 mb-1 font-weight-bold text-gray-800">

                                            @if ($kebutuhanEnergi && $kebutuhanEnergi->target_sarapan !== null)

                                                {{ number_format($kebutuhanEnergi->target_sarapan, 0, ',', '.') }}

                                                <small class="text-muted">
                                                    kcal
                                                </small>

                                            @else

                                                -

                                            @endif

                                        </div>

                                        <small class="text-muted">
                                            16,7% dari kebutuhan harian
                                        </small>

                                    </div>

                                    <div class="col-auto">

                                        <i class="fas fa-sun fa-2x text-gray-300"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Makan Siang --}}
                    <div class="col-xl-3 col-md-6 mb-3">

                        <div class="card border-left-success shadow-sm h-100">

                            <div class="card-body">

                                <div class="row no-gutters align-items-center">

                                    <div class="col mr-2">

                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Makan Siang
                                        </div>

                                        <div class="h5 mb-1 font-weight-bold text-gray-800">

                                            @if ($kebutuhanEnergi && $kebutuhanEnergi->target_makan_siang !== null)

                                                {{ number_format($kebutuhanEnergi->target_makan_siang, 0, ',', '.') }}

                                                <small class="text-muted">
                                                    kcal
                                                </small>

                                            @else

                                                -

                                            @endif

                                        </div>

                                        <small class="text-muted">
                                            30,5% dari kebutuhan harian
                                        </small>

                                    </div>

                                    <div class="col-auto">

                                        <i class="fas fa-cloud-sun fa-2x text-gray-300"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Makan Malam --}}
                    <div class="col-xl-3 col-md-6 mb-3">

                        <div class="card border-left-success shadow-sm h-100">

                            <div class="card-body">

                                <div class="row no-gutters align-items-center">

                                    <div class="col mr-2">

                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Makan Malam
                                        </div>

                                        <div class="h5 mb-1 font-weight-bold text-gray-800">

                                            @if ($kebutuhanEnergi && $kebutuhanEnergi->target_makan_malam !== null)

                                                {{ number_format($kebutuhanEnergi->target_makan_malam, 0, ',', '.') }}

                                                <small class="text-muted">
                                                    kcal
                                                </small>

                                            @else

                                                -

                                            @endif

                                        </div>

                                        <small class="text-muted">
                                            33,8% dari kebutuhan harian
                                        </small>

                                    </div>

                                    <div class="col-auto">

                                        <i class="fas fa-moon fa-2x text-gray-300"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Snack --}}
                    <div class="col-xl-3 col-md-6 mb-3">

                        <div class="card border-left-success shadow-sm h-100">

                            <div class="card-body">

                                <div class="row no-gutters align-items-center">

                                    <div class="col mr-2">

                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Snack
                                        </div>

                                        <div class="h5 mb-1 font-weight-bold text-gray-800">

                                            @if ($kebutuhanEnergi && $kebutuhanEnergi->target_snack !== null)

                                                {{ number_format($kebutuhanEnergi->target_snack, 0, ',', '.') }}

                                                <small class="text-muted">
                                                    kcal
                                                </small>

                                            @else

                                                -

                                            @endif

                                        </div>

                                        <small class="text-muted">
                                            19,0% dari kebutuhan harian
                                        </small>

                                    </div>

                                    <div class="col-auto">

                                        <i class="fas fa-apple-alt fa-2x text-gray-300"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Informasi --}}
                <div class="alert alert-light border-left-success mt-2 mb-0">

                    <div class="d-flex align-items-center">

                        <i class="fas fa-info-circle text-success mr-2"></i>

                        <small class="text-muted">

                            Nilai kalori di atas merupakan <strong>acuan target energi</strong>
                            untuk setiap waktu makan dan tidak harus dipenuhi secara persis.

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>