document.addEventListener('DOMContentLoaded', function () {

    const list = document.getElementById('food-list');
    if (!list) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const PAGE   = 12;   // jumlah makanan yang ditampilkan per tahap

    const items    = Array.from(list.querySelectorAll('.food-item'));
    const input    = document.getElementById('searchFood');
    const clearBtn = document.getElementById('gzClear');
    const sortSel  = document.getElementById('gzSort');
    const chips    = Array.from(document.querySelectorAll('.gz-chip'));
    const viewBtns = Array.from(document.querySelectorAll('.gz-view-btn'));
    const countEl  = document.getElementById('gzCount');
    const moreWrap = document.getElementById('gzMore');
    const moreBtn  = document.getElementById('gzMoreBtn');
    const emptyEl  = document.getElementById('no-food-result');
    const resetBtn = document.getElementById('gzReset');
    const totalEl  = document.getElementById('gzTotal');
    const hero     = document.getElementById('gzHero');

    const state = { q: '', chip: 'all', sort: 'default', view: 'grid', shown: PAGE };
    let firstRender = true;


    /* ---------- Data tiap makanan ---------- */
    const num = function (v) {
        const n = parseFloat(String(v).replace(',', '.'));
        return isNaN(n) ? 0 : n;
    };

    items.forEach(function (el, i) {
        const nameEl = el.querySelector('.food-name');
        el._nameEl = nameEl;
        el._hl = '';
        el._d = {
            i: i,
            name: el.dataset.foodName || '',
            title: nameEl ? nameEl.textContent.trim() : '',
            kalori: num(el.dataset.kalori),
            protein: num(el.dataset.protein),
            lemak: num(el.dataset.lemak),
            karbo: num(el.dataset.karbo)
        };
    });

    function esc(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }


    /* ---------- Batang perbandingan (relatif terhadap nilai tertinggi) ---------- */
    const keys = ['kalori', 'protein', 'lemak', 'karbo'];
    const max = {};

    keys.forEach(function (k) {
        max[k] = items.reduce(function (m, el) { return Math.max(m, el._d[k]); }, 0);
    });

    items.forEach(function (el) {
        keys.forEach(function (k) {
            const row = el.querySelector('.nutrition-row[data-nutri="' + k + '"]');
            if (!row) return;
            let pct = max[k] > 0 ? (el._d[k] / max[k]) * 100 : 0;
            if (el._d[k] > 0) pct = Math.max(pct, 4);
            row.style.setProperty('--w', pct.toFixed(1) + '%');
        });
    });

    requestAnimationFrame(function () {
        requestAnimationFrame(function () { list.classList.add('gz-ready'); });
    });


    /* ---------- Filter cepat: sepertiga terendah / tertinggi dari daftar ---------- */
    function tercile(key) {
        const v = items.map(function (el) { return el._d[key]; }).sort(function (a, b) { return a - b; });
        if (!v.length) return { low: 0, high: 0 };
        return {
            low:  v[Math.max(0, Math.ceil(v.length / 3) - 1)],
            high: v[Math.min(v.length - 1, Math.floor(v.length * 2 / 3))]
        };
    }
    const T = { kalori: tercile('kalori'), protein: tercile('protein'), lemak: tercile('lemak'), karbo: tercile('karbo') };

    function passChip(el, chip) {
        const d = el._d;
        switch (chip) {
            case 'kalori-rendah':  return d.kalori  <= T.kalori.low;
            case 'protein-tinggi': return d.protein >= T.protein.high;
            case 'lemak-rendah':   return d.lemak   <= T.lemak.low;
            case 'karbo-rendah':   return d.karbo   <= T.karbo.low;
            default: return true;
        }
    }

    chips.forEach(function (btn) {
        const n = btn.querySelector('.gz-chip-n');
        if (!n || btn.dataset.chip === 'all') return;
        n.textContent = items.filter(function (el) { return passChip(el, btn.dataset.chip); }).length;
    });


    /* ---------- Urutan ---------- */
    const byNum = function (k, dir) {
        return function (a, b) { return (dir * (a._d[k] - b._d[k])) || (a._d.i - b._d.i); };
    };
    const sorters = {
        'default': function (a, b) { return a._d.i - b._d.i; },
        'nama': function (a, b) { return a._d.title.localeCompare(b._d.title, 'id') || (a._d.i - b._d.i); },
        'kalori-asc': byNum('kalori', 1),
        'kalori-desc': byNum('kalori', -1),
        'protein-desc': byNum('protein', -1),
        'lemak-asc': byNum('lemak', 1),
        'karbo-asc': byNum('karbo', 1)
    };


    /* ---------- Animasi muncul ---------- */
    function animateIn(el, idx) {
        el.style.setProperty('--d', (Math.min(idx, 12) * 45) + 'ms');
        el.classList.remove('gz-in');
        void el.offsetWidth;
        el.classList.add('gz-in');
        el.addEventListener('animationend', function (e) {
            if (e.target === el) el.classList.remove('gz-in');
        }, { once: true });
    }

    function highlight(el) {
        if (el._hl === state.q || !el._nameEl) return;
        el._hl = state.q;
        const t = el._d.title;
        const q = state.q;
        const i = q ? t.toLowerCase().indexOf(q) : -1;
        el._nameEl.innerHTML = i < 0
            ? esc(t)
            : esc(t.slice(0, i)) + '<mark>' + esc(t.slice(i, i + q.length)) + '</mark>' + esc(t.slice(i + q.length));
    }


    /* ---------- Tampilkan hasil ---------- */
    function render() {

        const matches = items.filter(function (el) {
            return (!state.q || el._d.name.indexOf(state.q) !== -1) && passChip(el, state.chip);
        });
        matches.sort(sorters[state.sort] || sorters['default']);

        // susun ulang urutan di DOM
        const frag = document.createDocumentFragment();
        matches.forEach(function (el) { frag.appendChild(el); });
        list.appendChild(frag);

        const visible = matches.slice(0, state.shown);
        const visibleSet = new Set(visible);
        let n = 0;

        items.forEach(function (el) {
            if (visibleSet.has(el)) {
                const wasHidden = el.hidden;
                el.hidden = false;
                if ((wasHidden || firstRender) && !reduce) animateIn(el, n++);
                highlight(el);
            } else if (!el.hidden) {
                el.hidden = true;
            }
        });
        firstRender = false;

        // keterangan jumlah
        if (matches.length === items.length) {
            countEl.textContent = 'Menampilkan ' + visible.length + ' dari ' + items.length + ' makanan';
        } else {
            countEl.textContent = matches.length + ' makanan cocok, menampilkan ' + visible.length;
        }

        // tombol "tampilkan lebih banyak"
        const sisa = matches.length - visible.length;
        moreWrap.hidden = sisa <= 0;
        if (sisa > 0) {
            moreBtn.textContent = 'Tampilkan ' + Math.min(PAGE, sisa) + ' makanan lagi (sisa ' + sisa + ')';
        }

        // kosong
        emptyEl.style.display = matches.length === 0 ? 'block' : 'none';
    }


    /* ---------- Pencarian ---------- */
    let timer = null;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            state.q = input.value.toLowerCase().trim();
            state.shown = PAGE;
            render();
        }, 120);
    });

    function clearSearch() {
        input.value = '';
        state.q = '';
        state.shown = PAGE;
        render();
        input.focus();
    }
    clearBtn.addEventListener('click', clearSearch);

    document.addEventListener('keydown', function (e) {
        const tag = document.activeElement ? document.activeElement.tagName : '';
        if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(tag)) {
            e.preventDefault();
            input.focus();
            input.select();
        } else if (e.key === 'Escape' && document.activeElement === input && input.value) {
            clearSearch();
        }
    });


    /* ---------- Urutkan ---------- */
    sortSel.addEventListener('change', function () {
        state.sort = sortSel.value;
        state.shown = PAGE;
        render();
    });


    /* ---------- Filter cepat ---------- */
    chips.forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.chip = btn.dataset.chip;
            state.shown = PAGE;
            chips.forEach(function (c) { c.classList.toggle('is-active', c === btn); });
            render();
        });
    });


    /* ---------- Tampilan kartu / daftar ---------- */
    function setView(v) {
        state.view = v;
        list.classList.toggle('is-list', v === 'list');
        viewBtns.forEach(function (b) { b.classList.toggle('is-active', b.dataset.view === v); });
        try { localStorage.setItem('nm_gizi_view', v); } catch (e) {}
    }
    viewBtns.forEach(function (b) {
        b.addEventListener('click', function () { setView(b.dataset.view); });
    });
    try {
        const saved = localStorage.getItem('nm_gizi_view');
        if (saved === 'list' || saved === 'grid') setView(saved);
    } catch (e) {}


    /* ---------- Tampilkan lebih banyak & atur ulang ---------- */
    moreBtn.addEventListener('click', function () {
        state.shown += PAGE;
        render();
    });

    resetBtn.addEventListener('click', function () {
        input.value = '';
        sortSel.value = 'default';
        state.q = '';
        state.chip = 'all';
        state.sort = 'default';
        state.shown = PAGE;
        chips.forEach(function (c) { c.classList.toggle('is-active', c.dataset.chip === 'all'); });
        render();
        input.focus();
    });


    /* ---------- Header: cahaya mengikuti kursor + angka total ---------- */
    if (hero && !reduce) {
        hero.addEventListener('mousemove', function (e) {
            const r = hero.getBoundingClientRect();
            hero.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            hero.style.setProperty('--my', (e.clientY - r.top) + 'px');
        });
        hero.addEventListener('mouseleave', function () {
            hero.style.removeProperty('--mx');
            hero.style.removeProperty('--my');
        });
    }

    if (totalEl) {
        const target = items.length;
        if (reduce || target === 0) {
            totalEl.textContent = target;
        } else {
            const mulai = performance.now();
            (function frame(now) {
                const t = Math.min((now - mulai) / 900, 1);
                totalEl.textContent = Math.round(target * (1 - Math.pow(1 - t, 3)));
                if (t < 1) requestAnimationFrame(frame);
            })(mulai);
        }
    }


    render();
});