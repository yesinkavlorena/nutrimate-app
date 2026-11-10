/* =========================================================
   NutriMate - Profil Pengguna
========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    const page = document.querySelector('.pf-page');
    if (!page) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Cahaya hijau mengikuti kursor (header dan kartu besar) ---------- */
    if (!reduce) {
        page.querySelectorAll('.pf-hero, .pf-panel').forEach(function (el) {
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

    /* ---------- Angka naik dari 0 (usia, berat, tinggi) ---------- */
    if (!reduce) {
        page.querySelectorAll('.profile-info-box .profile-value').forEach(function (el) {
            if (el.closest('.food-preference-card')) return;

            const node = Array.from(el.childNodes).find(function (n) {
                return n.nodeType === Node.TEXT_NODE && /^\s*\d+([.,]\d+)?\s*$/.test(n.nodeValue);
            });
            if (!node) return;

            const asli = node.nodeValue;
            const teks = asli.trim().replace(',', '.');
            const target = parseFloat(teks);
            if (isNaN(target)) return;

            const desimal = (teks.split('.')[1] || '').length;
            const mulai = performance.now();
            const durasi = 1100;

            node.nodeValue = '0 ';

            function frame(sekarang) {
                const t = Math.min((sekarang - mulai) / durasi, 1);
                const ease = 1 - Math.pow(1 - t, 3);
                node.nodeValue = (target * ease).toFixed(desimal) + ' ';
                if (t < 1) requestAnimationFrame(frame);
                else node.nodeValue = asli;     // kembalikan teks asli
            }
            requestAnimationFrame(frame);
        });
    }

    /* ---------- Progress aktivitas terisi pelan ---------- */
    if (!reduce) {
        page.querySelectorAll('.progress-bar').forEach(function (bar) {
            const lebar = bar.style.width;
            if (!lebar) return;
            bar.style.width = '0%';
            setTimeout(function () { bar.style.width = lebar; }, 350);
        });
    }

    /* ---------- Pesan sukses hilang sendiri setelah beberapa detik ---------- */
    const alertEl = page.querySelector('.alert-success');
    if (alertEl) {
        setTimeout(function () {
            if (!document.body.contains(alertEl)) return;
            if (window.bootstrap && window.bootstrap.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(alertEl).close();
            } else {
                alertEl.style.display = 'none';
            }
        }, 7000);
    }
});