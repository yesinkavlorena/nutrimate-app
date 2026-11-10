document.addEventListener('DOMContentLoaded', function () {

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Welcome: tandai elemen + cahaya mengikuti kursor ---------- */
    const label = document.querySelector('.nutrimate-main .text-uppercase.text-muted.small');
    const welcome = label ? label.closest('.mb-4') : null;

    if (welcome) {
        welcome.classList.add('nm-welcome');

        if (!reduceMotion) {
            welcome.addEventListener('mousemove', function (e) {
                const r = welcome.getBoundingClientRect();
                welcome.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                welcome.style.setProperty('--my', (e.clientY - r.top) + 'px');
            });
            welcome.addEventListener('mouseleave', function () {
                welcome.style.removeProperty('--mx');
                welcome.style.removeProperty('--my');
            });
        }
    }

    /* ---------- Muncul bertahap saat di-scroll ---------- */
    if (reduceMotion || !('IntersectionObserver' in window)) return;

    const targets = document.querySelectorAll('.nm-welcome, .nutrimate-main .card');

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            el.classList.add('is-visible');
            observer.unobserve(el);

            // lepas class reveal supaya efek hover normal lagi
            setTimeout(function () {
                el.classList.remove('js-reveal', 'is-visible');
                el.style.transitionDelay = '';
            }, 900 + parseInt(el.style.transitionDelay || 0));
        });
    }, { threshold: 0.12 });

    targets.forEach(function (el, i) {
        el.classList.add('js-reveal');
        el.style.transitionDelay = (i % 6) * 80 + 'ms';
        observer.observe(el);
    });
});

/* =========================================================
   Target Kalori per Waktu Makan
========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const canHover     = window.matchMedia('(hover: hover)').matches;

    // tandai kartu lewat ikonnya
    const peta = {
        'fa-sun': 'sarapan',
        'fa-cloud-sun': 'siang',
        'fa-moon': 'malam',
        'fa-apple-alt': 'snack'
    };

    const kartu = [];

    Object.keys(peta).forEach(function (ikon) {
        document.querySelectorAll('.card.border-left-success .' + ikon).forEach(function (el) {
            const card = el.closest('.card');
            if (!card || card.classList.contains('nm-meal')) return;

            card.classList.add('nm-meal');
            card.dataset.meal = peta[ikon];
            kartu.push(card);

            const pembungkus = card.parentElement ? card.parentElement.closest('.card') : null;
            if (pembungkus) pembungkus.classList.add('nm-meal-wrap');
        });
    });

    if (!kartu.length) return;

    /* ---------- Panjang garis bawah mengikuti persentase ---------- */
    function ambilPersen(card) {
        const kecil = Array.from(card.querySelectorAll('small'))
            .find(function (s) { return /%/.test(s.textContent); });
        if (!kecil) return null;
        const m = kecil.textContent.match(/([\d.,]+)\s*%/);
        return m ? parseFloat(m[1].replace(',', '.')) : null;
    }

    const persen = kartu.map(ambilPersen);
    const terbesar = Math.max.apply(null, persen.filter(function (p) { return p !== null; }));

    kartu.forEach(function (card, i) {
        if (persen[i] !== null && isFinite(terbesar) && terbesar > 0) {
            card.style.setProperty('--bar', (persen[i] / terbesar * 100) + '%');
        }
    });

    /* ---------- Tilt 3D mengikuti kursor ---------- */
    if (canHover && !reduceMotion) {
        kartu.forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                card.style.setProperty('--ry', (x * 8) + 'deg');
                card.style.setProperty('--rx', (-y * 8) + 'deg');
            });
            card.addEventListener('mouseleave', function () {
                card.style.setProperty('--rx', '0deg');
                card.style.setProperty('--ry', '0deg');
            });
        });
    }

    /* ---------- Angka kalori naik dari 0 ---------- */
    if (reduceMotion || !('IntersectionObserver' in window)) return;

    function ambilNode(card) {
        const h5 = card.querySelector('.h5');
        if (!h5) return null;
        return Array.from(h5.childNodes).find(function (n) {
            return n.nodeType === Node.TEXT_NODE && /^\s*[\d.]+\s*$/.test(n.nodeValue);
        }) || null;
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);

            const node = ambilNode(entry.target);
            if (!node) return;

            const target = parseInt(node.nodeValue.replace(/\./g, ''), 10);
            const mulai = performance.now();
            const durasi = 1100;

            function frame(sekarang) {
                const t = Math.min((sekarang - mulai) / durasi, 1);
                const ease = 1 - Math.pow(1 - t, 3);
                node.nodeValue = Math.round(target * ease).toLocaleString('id-ID') + ' ';
                if (t < 1) requestAnimationFrame(frame);
            }
            requestAnimationFrame(frame);
        });
    }, { threshold: 0.1 });

    kartu.forEach(function (card) {
        const node = ambilNode(card);
        if (!node) return;      // tanda "-" (data kosong) dibiarkan

        const asli = node.nodeValue;
        node.nodeValue = '0 ';
        observer.observe(card);

        // jaring pengaman: kalau animasi belum jalan setelah 4 detik, tampilkan angka aslinya
        setTimeout(function () {
            if (node.nodeValue.trim() === '0') node.nodeValue = asli;
        }, 4000);
    });
});

/* =========================================================
   Health Summary: BMI, BMR, TDEE
========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const canHover     = window.matchMedia('(hover: hover)').matches;

    // kenali kartu dari teksnya
    const aturan = [
        { kunci: 'bmi',  cocok: /BODY MASS INDEX/i },
        { kunci: 'bmr',  cocok: /\bBMR\b/i },
        { kunci: 'tdee', cocok: /\bTDEE\b/i }
    ];

    const stat = [];

    document.querySelectorAll('.card').forEach(function (card) {
        if (card.querySelector('.card')) return;          // lewati kartu pembungkus
        if (card.classList.contains('nm-meal')) return;   // lewati kartu waktu makan

        const teks = card.textContent;
        const hit = aturan.find(function (a) { return a.cocok.test(teks); });
        if (!hit) return;

        card.classList.add('nm-stat');
        card.dataset.stat = hit.kunci;
        stat.push(card);
    });

    if (!stat.length) return;

    // cari teks angka (format Indonesia: 1.362,90 atau 21,48)
    function cariAngka(card) {
        const walker = document.createTreeWalker(card, NodeFilter.SHOW_TEXT);
        let node;
        while ((node = walker.nextNode())) {
            if (/^\s*\d[\d.,]*\s*$/.test(node.nodeValue)) return node;
        }
        return null;
    }

    stat.forEach(function (card) {
        const node = cariAngka(card);
        if (node && node.parentElement && node.parentElement !== card) {
            node.parentElement.classList.add('nm-stat-value');
        }
    });

    /* ---------- Tilt 3D ---------- */
    if (canHover && !reduceMotion) {
        stat.forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                card.style.setProperty('--ry', (x * 8) + 'deg');
                card.style.setProperty('--rx', (-y * 8) + 'deg');
            });
            card.addEventListener('mouseleave', function () {
                card.style.setProperty('--rx', '0deg');
                card.style.setProperty('--ry', '0deg');
            });
        });
    }

    /* ---------- Angka naik dari 0 ---------- */
    if (reduceMotion || !('IntersectionObserver' in window)) return;

    function jalankan(card) {
        const node = card._nmNode;
        if (!node || card._nmDone) return;
        card._nmDone = true;

        const mulai = performance.now();
        const durasi = 1200;

        function frame(sekarang) {
            const t = Math.min((sekarang - mulai) / durasi, 1);
            const ease = 1 - Math.pow(1 - t, 3);
            node.nodeValue = (card._nmTarget * ease).toLocaleString('id-ID', {
                minimumFractionDigits: card._nmDesimal,
                maximumFractionDigits: card._nmDesimal
            }) + ' ';
            if (t < 1) requestAnimationFrame(frame);
            else node.nodeValue = card._nmAsli;
        }
        requestAnimationFrame(frame);
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);
            jalankan(entry.target);
        });
    }, { threshold: 0.1 });

    stat.forEach(function (card) {
        const node = cariAngka(card);
        if (!node) return;

        const asli = node.nodeValue;
        const bersih = asli.trim().replace(/\./g, '').replace(',', '.');
        const target = parseFloat(bersih);
        if (isNaN(target)) return;

        card._nmNode = node;
        card._nmAsli = asli;
        card._nmTarget = target;
        card._nmDesimal = (bersih.split('.')[1] || '').length;

        node.nodeValue = '0 ';
        observer.observe(card);
    });
});

/* =========================================================
   Food List
========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const canHover     = window.matchMedia('(hover: hover)').matches;

    const kartu = document.querySelectorAll('.card.nm-food');
    if (!kartu.length) return;

    /* ---------- Gambar: blur ke jelas ---------- */
    if (!reduceMotion) {
        kartu.forEach(function (card) {
            const img = card.querySelector('.card-img-top');
            if (!img || (img.complete && img.naturalWidth > 0)) return;

            img.classList.add('nm-img-loading');
            const tampil = function () { img.classList.remove('nm-img-loading'); };
            img.addEventListener('load', tampil);
            img.addEventListener('error', tampil);
            setTimeout(tampil, 4000);   // pengaman
        });
    }

    /* ---------- Tilt 3D halus ---------- */
    if (canHover && !reduceMotion) {
        kartu.forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                card.style.setProperty('--ry', (x * 6) + 'deg');
                card.style.setProperty('--rx', (-y * 6) + 'deg');
            });
            card.addEventListener('mouseleave', function () {
                card.style.setProperty('--rx', '0deg');
                card.style.setProperty('--ry', '0deg');
            });
        });
    }

    /* ---------- Kalori naik dari 0 ---------- */
    if (reduceMotion || !('IntersectionObserver' in window)) return;

    function ambilNode(card) {
        const kcal = card.querySelector('.nm-food-kcal');
        if (!kcal) return null;
        return Array.from(kcal.childNodes).find(function (n) {
            return n.nodeType === Node.TEXT_NODE && /[\d]/.test(n.nodeValue);
        }) || null;
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);

            const card = entry.target;
            const node = card._nmNode;
            if (!node) return;

            const mulai = performance.now();
            const durasi = 900;

            function frame(sekarang) {
                const t = Math.min((sekarang - mulai) / durasi, 1);
                const ease = 1 - Math.pow(1 - t, 3);
                node.nodeValue = ' ' + (card._nmTarget * ease).toFixed(card._nmDesimal) + ' kcal ';
                if (t < 1) requestAnimationFrame(frame);
                else node.nodeValue = card._nmAsli;   // kembalikan teks asli
            }
            requestAnimationFrame(frame);
        });
    }, { threshold: 0.1 });

    kartu.forEach(function (card) {
        const node = ambilNode(card);
        if (!node) return;

        const cocok = node.nodeValue.match(/(\d+(?:[.,]\d+)?)/);
        if (!cocok) return;

        const angka = cocok[1].replace(',', '.');
        const target = parseFloat(angka);
        if (isNaN(target)) return;

        card._nmNode = node;
        card._nmAsli = node.nodeValue;
        card._nmTarget = target;
        card._nmDesimal = (angka.split('.')[1] || '').length;

        node.nodeValue = ' 0 kcal ';
        observer.observe(card);

        // pengaman: kalau animasi tidak jalan, kembalikan angka asli
        setTimeout(function () {
            if (node.nodeValue.trim().startsWith('0 ')) node.nodeValue = card._nmAsli;
        }, 4000);
    });
});

/* =========================================================
   Target Kalori: cahaya mengikuti kursor
========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const pembungkus = document.querySelector('.card.nm-glow');
    if (!pembungkus || reduceMotion) return;

    pembungkus.addEventListener('mousemove', function (e) {
        const r = pembungkus.getBoundingClientRect();
        pembungkus.style.setProperty('--mx', (e.clientX - r.left) + 'px');
        pembungkus.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });

    pembungkus.addEventListener('mouseleave', function () {
        pembungkus.style.removeProperty('--mx');
        pembungkus.style.removeProperty('--my');
    });
});