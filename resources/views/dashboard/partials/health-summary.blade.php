<div class="row">

    <div class="col-12">

        <h6 class="font-weight-bold text-gray-800 mb-3">
            Health Summary
        </h6>

    </div>

    {{-- BMI --}}
    <div class="col-xl-4 col-md-6 mb-4">

        <div class="card nm-meal border-left-success shadow h-100 py-3"
             style="--accent:#2bb5c9; --accent-dark:#1b8fa1; --accent-soft:rgba(43,181,201,.15); --accent-glow:rgba(43,181,201,.35);">

            <div class="card-body">

                <div class="row no-gutters align-items-center">

                    <div class="col mr-2">

                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Body Mass Index
                        </div>

                        <div class="h5 mb-1 font-weight-bold text-gray-800">
                            {{ $bmi !== null ? number_format($bmi, 2, ',', '.') : '-' }}
                        </div>

                        @if ($kategoriBmi)
                            <span class="nm-badge">{{ $kategoriBmi }}</span>
                        @else
                            <span class="nm-badge nm-badge-muted">Belum tersedia</span>
                        @endif

                    </div>

                    <div class="col-auto">

                        <i class="fas fa-weight fa-2x text-gray-300"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- BMR --}}
    <div class="col-xl-4 col-md-6 mb-4">

        <div class="card nm-meal border-left-success shadow h-100 py-3"
             style="--accent:#f5873b; --accent-dark:#d46a1f; --accent-soft:rgba(245,135,59,.15); --accent-glow:rgba(245,135,59,.35);">

            <div class="card-body">

                <div class="row no-gutters align-items-center">

                    <div class="col mr-2">

                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            BMR
                        </div>

                        <div class="h5 mb-1 font-weight-bold text-gray-800">
                            {{ $bmr !== null ? number_format($bmr, 2, ',', '.') : '-' }}
                        </div>

                        <small class="text-muted">
                            Calories/day at rest
                        </small>

                    </div>

                    <div class="col-auto">

                        <i class="fas fa-fire fa-2x text-gray-300"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- TDEE --}}
    <div class="col-xl-4 col-md-6 mb-4">

        <div class="card nm-meal border-left-success shadow h-100 py-3"
             style="--accent:#e9b020; --accent-dark:#c28f0b; --accent-soft:rgba(233,176,32,.17); --accent-glow:rgba(233,176,32,.38);">

            <div class="card-body">

                <div class="row no-gutters align-items-center">

                    <div class="col mr-2">

                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            TDEE
                        </div>

                        <div class="h5 mb-1 font-weight-bold text-gray-800">
                            {{ $tdee !== null ? number_format($tdee, 2, ',', '.') : '-' }}
                        </div>

                        <small class="text-muted">
                            Total Daily Energy Expenditure
                        </small>

                    </div>

                    <div class="col-auto">

                        <i class="fas fa-bolt fa-2x text-gray-300"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>