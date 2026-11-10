document.addEventListener('DOMContentLoaded', function () {

    const page = document.querySelector('.rk-page');
    if (!page) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const form    = document.getElementById('rkForm');
    const submit  = document.getElementById('rkSubmit');
    const hint    = document.getElementById('rkHint');
    const panel   = document.getElementById('hasil-rekomendasi');
    const loading = document.getElementById('rkLoading');
    const options = Array.from(page.querySelectorAll('.meal-option'));
    const optWrap = options.length ? options[0].closest('.row') : null;


    /* ---------- Cahaya hijau mengikuti kursor ---------- */
    if (!reduce) {
        page.querySelectorAll('.rk-hero, .rk-panel').forEach(function (el) {
            el.addEventListener('mousemove', function (e) {
                const r = el.getBoundingClientRect();
                el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                el.style.setProperty('--my', (e.clientY - r.top) + 'px');
            });
            el.addEventListener('mouseleave', function () {
                el.style.removeProperty('--mx');
                el.style.removeProperty('--my');
            });
        });
    }


    /* ---------- Tandai waktu makan yang sesuai jam sekarang ---------- */
    (function () {
        const jam = new Date().getHours();
        let sekarang = null;
        if (jam >= 5 && jam < 11)       sekarang = 'sarapan';
        else if (jam >= 11 && jam < 15) sekarang = 'siang';
        else if (jam >= 15 && jam < 18) sekarang = 'camilan';
        else if (jam >= 18 && jam < 22) sekarang = 'malam';

        options.forEach(function (o) {
            o.classList.toggle('is-now', o.dataset.meal === sekarang);
        });
    })();


    /* ---------- Angka naik (target harian dan target waktu makan) ---------- */
    function hitungNaik(el) {
        const target = parseFloat(el.dataset.count);
        if (isNaN(target) || reduce) return;
        const asli = el.textContent;
        const mulai = performance.now();
        (function frame(now) {
            const t = Math.min((now - mulai) / 1000, 1);
            const ease = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(target * ease).toLocaleString('id-ID');
            if (t < 1) requestAnimationFrame(frame);
            else el.textContent = asli;
        })(mulai);
    }
    page.querySelectorAll('[data-count]').forEach(hitungNaik);


    /* ---------- Batang dan penanda terisi setelah halaman siap ---------- */
    requestAnimationFrame(function () {
        requestAnimationFrame(function () { page.classList.add('rk-ready'); });
    });


    /* ---------- Gulir ke hasil setelah rekomendasi dibuat ---------- */
    if (panel && panel.dataset.hasResult === '1' && /waktu_makan=/.test(location.search)) {
        setTimeout(function () {
            panel.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        }, 250);
    }


    /* ---------- Tombol: keadaan memuat ---------- */
    function setWait(btn, on) {
        if (!btn) return;
        const label = btn.querySelector('.rk-btn-label');
        const wait  = btn.querySelector('.rk-btn-wait');
        if (label) label.hidden = on;
        if (wait)  wait.hidden = !on;
        btn.disabled = on;
    }


    /* ---------- Formulir permintaan ---------- */
    if (form) {
        form.addEventListener('submit', function (e) {
            const dipilih = form.querySelector('input[name="waktu_makan"]:checked');

            if (!dipilih) {
                e.preventDefault();
                if (hint) hint.hidden = false;
                if (optWrap && !reduce) {
                    optWrap.classList.remove('meal-options-shake');
                    void optWrap.offsetWidth;
                    optWrap.classList.add('meal-options-shake');
                }
                return;
            }

            // cegah klik ganda, tampilkan kerangka pemuat
            requestAnimationFrame(function () {
                setWait(submit, true);
                if (panel && loading) {
                    panel.classList.add('is-loading');
                    loading.hidden = false;
                    panel.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
                }
            });
        });

        form.addEventListener('change', function () {
            if (hint) hint.hidden = true;
        });
    }


    /* ---------- Tombol Simpan Riwayat ---------- */
    page.querySelectorAll('form.rk-save').forEach(function (f) {
        f.addEventListener('submit', function () {
            const btn = f.querySelector('button[type="submit"]');
            requestAnimationFrame(function () { setWait(btn, true); });
        });
    });


    /* ---------- Kembali dari riwayat browser: pulihkan tombol ---------- */
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        setWait(submit, false);
        page.querySelectorAll('form.rk-save button[type="submit"]').forEach(function (b) { setWait(b, false); });
        if (panel) panel.classList.remove('is-loading');
        if (loading) loading.hidden = true;
    });

});