document.addEventListener('DOMContentLoaded', function () {

    const reduce   = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const canHover = window.matchMedia('(hover: hover)').matches;

    const page  = document.querySelector('.lg-page');
    const bg    = document.getElementById('lgBg');
    const card  = document.getElementById('lgCard');
    const form  = document.getElementById('lgForm');
    const btn   = document.getElementById('lgSubmit');
    const caps  = document.getElementById('lgCaps');
    const pass  = document.getElementById('password');
    const conf  = document.getElementById('password_confirmation');
    const meter = document.getElementById('rgMeter');
    const meterText = document.getElementById('rgMeterText');
    const match = document.getElementById('rgMatch');


    /* ---------- Kartu: miring halus + cahaya mengikuti kursor ---------- */
    if (card && canHover && !reduce) {
        card.addEventListener('mousemove', function (e) {
            const r = card.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width - 0.5;
            const y = (e.clientY - r.top) / r.height - 0.5;
            card.style.setProperty('--ry', (x * 4) + 'deg');
            card.style.setProperty('--rx', (-y * 4) + 'deg');
            card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            card.style.setProperty('--my', (e.clientY - r.top) + 'px');
        });
        card.addEventListener('mouseleave', function () {
            card.style.setProperty('--rx', '0deg');
            card.style.setProperty('--ry', '0deg');
            card.style.removeProperty('--mx');
            card.style.removeProperty('--my');
        });
    }


    /* ---------- Latar: gelembung dan ikon bergeser mengikuti kursor ---------- */
    if (page && bg && canHover && !reduce) {
        const bergerak = Array.from(bg.querySelectorAll('.lg-blob, .lg-float'));

        page.addEventListener('mousemove', function (e) {
            const x = e.clientX / window.innerWidth - 0.5;
            const y = e.clientY / window.innerHeight - 0.5;

            bergerak.forEach(function (el, i) {
                const kekuatan = 14 + (i % 4) * 8;
                el.style.transform = 'translate(' + (x * kekuatan) + 'px,' + (y * kekuatan) + 'px)';
            });
        });
        page.addEventListener('mouseleave', function () {
            bergerak.forEach(function (el) { el.style.transform = ''; });
        });
    }


    /* ---------- Lihat / sembunyikan password (untuk kedua kolom) ---------- */
    document.querySelectorAll('.lg-eye').forEach(function (eye) {
        const input = document.getElementById(eye.dataset.target);
        if (!input) return;

        eye.addEventListener('click', function () {
            const tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            eye.querySelector('i').className = tampil ? 'fas fa-eye-slash' : 'fas fa-eye';
            eye.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
            input.focus();
        });
    });


    /* ---------- Peringatan Caps Lock ---------- */
    if (caps) {
        function cekCaps(e) {
            if (e.getModifierState) caps.hidden = !e.getModifierState('CapsLock');
        }
        [pass, conf].forEach(function (input) {
            if (!input) return;
            input.addEventListener('keydown', cekCaps);
            input.addEventListener('keyup', cekCaps);
            input.addEventListener('blur', function () { caps.hidden = true; });
        });
    }


    /* ---------- Kekuatan password ---------- */
    function hitungKekuatan(v) {
        let skor = 0;
        if (v.length >= 8) skor++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) skor++;
        if (/\d/.test(v)) skor++;
        if (/[^A-Za-z0-9]/.test(v)) skor++;
        if (v.length > 0 && skor === 0) skor = 1;
        return skor;
    }

    if (pass && meter && meterText) {
        const label = ['', 'Lemah', 'Cukup', 'Bagus', 'Kuat'];

        pass.addEventListener('input', function () {
            const level = hitungKekuatan(pass.value);
            meter.hidden = pass.value.length === 0;
            meter.dataset.level = level;
            meterText.textContent = label[level];
            cekCocok();
        });
    }


    /* ---------- Password dan konfirmasi harus sama ---------- */
    function cekCocok() {
        if (!conf || !match) return;

        if (conf.value.length === 0) {
            match.hidden = true;
            return;
        }

        const sama = conf.value === pass.value;
        match.hidden = false;
        match.className = 'rg-match ' + (sama ? 'ok' : 'bad');
        match.innerHTML = sama
            ? '<i class="fas fa-check-circle"></i>Password cocok'
            : '<i class="fas fa-times-circle"></i>Password belum sama';
    }

    if (conf) conf.addEventListener('input', cekCocok);


    /* ---------- Hapus tanda error saat pengguna mulai mengetik ---------- */
    document.querySelectorAll('.lg-field.has-error input').forEach(function (input) {
        input.addEventListener('input', function () {
            input.closest('.lg-field').classList.remove('has-error');
        });
    });


    /* ---------- Tombol: keadaan memuat ---------- */
    function setWait(on) {
        if (!btn) return;
        btn.querySelector('.lg-btn-label').hidden = on;
        btn.querySelector('.lg-btn-wait').hidden = !on;
        btn.disabled = on;
    }

    if (form) {
        form.addEventListener('submit', function () {
            // cegah klik ganda setelah formulir terkirim
            requestAnimationFrame(function () { setWait(true); });
        });
    }

    // kembali dari riwayat browser: pulihkan tombol
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) setWait(false);
    });

});