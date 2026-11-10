<link rel="stylesheet"
      href="{{ asset('css/footer.css') }}?v={{ @filemtime(public_path('css/footer.css')) }}">

<footer class="ft">

    <div class="ft-brand">

        <span class="ft-logo">
            <i class="fas fa-leaf"></i>
        </span>

        <div class="ft-text">

            <span class="ft-copy">
                Copyright &copy; NutriMate {{ date('Y') }}
            </span>

            <small class="ft-tag">
                Sistem Rekomendasi Makanan Personal
            </small>

        </div>

    </div>


    <button type="button"
            class="ft-top"
            id="ftTop"
            aria-label="Kembali ke atas">

        <span>Kembali ke atas</span>

        <i class="fas fa-arrow-up"></i>

    </button>

</footer>

<script src="{{ asset('js/footer.js') }}?v={{ @filemtime(public_path('js/footer.js')) }}" defer></script>