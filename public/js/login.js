document.addEventListener('DOMContentLoaded', function () {

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const canHover = window.matchMedia('(hover: hover)').matches;

    const card  = document.getElementById('lgCard');
    const page  = document.querySelector('.lg-page');
    const bg    = document.getElementById('lgBg');
    const form  = document.getElementById('lgForm');
    const btn   = document.getElementById('lgSubmit');
    const pass  = document.getElementById('exampleInputPassword');
    const eye   = document.getElementById('lgEye');
    const caps  = document.getElementById('lgCaps');


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


    /* ---------- Lihat / sembunyikan password ---------- */
    if (eye && pass) {
        eye.addEventListener('click', function () {
            const tampil = pass.type === 'password';
            pass.type = tampil ? 'text' : 'password';
            eye.querySelector('i').className = tampil ? 'fas fa-eye-slash' : 'fas fa-eye';
            eye.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
            pass.focus();
        });
    }


    /* ---------- Peringatan Caps Lock ---------- */
    if (pass && caps) {
        function cekCaps(e) {
            if (e.getModifierState) caps.hidden = !e.getModifierState('CapsLock');
        }
        pass.addEventListener('keydown', cekCaps);
        pass.addEventListener('keyup', cekCaps);
        pass.addEventListener('blur', function () { caps.hidden = true; });
    }


    /* ---------- Hapus tanda error saat pengguna mulai mengetik ---------- */
    document.querySelectorAll('.lg-field.has-error input').forEach(function (input) {
        input.addEventListener('input', function () {
            const field = input.closest('.lg-field');
            field.classList.remove('has-error');
            const msg = field.querySelector('.lg-msg');
            if (msg) msg.remove();
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