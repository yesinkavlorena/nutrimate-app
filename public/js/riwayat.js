document.addEventListener('DOMContentLoaded', function () {

    const page = document.querySelector('.rw-page');
    if (!page) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const list     = document.getElementById('rwList');
    const items    = list ? Array.from(list.querySelectorAll('.rw-item')) : [];
    const search   = document.getElementById('rwSearch');
    const clearBtn = document.getElementById('rwClear');
    const sortSel  = document.getElementById('rwSort');
    const chips    = Array.from(page.querySelectorAll('.rw-chip'));
    const countEl  = document.getElementById('rwCount');
    const noResult = document.getElementById('rwNoResult');
    const resetBtn = document.getElementById('rwReset');
    const openAll  = document.getElementById('rwOpenAll');
    const closeAll = document.getElementById('rwCloseAll');
    const hero     = document.getElementById('rwHero');

    const state = { q: '', chip: 'all', sort: 'default' };


    /* ---------- Cahaya hijau mengikuti kursor ---------- */
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


    /* ---------- Batang hybrid score terisi ---------- */
    requestAnimationFrame(function () {
        requestAnimationFrame(function () { page.classList.add('rw-ready'); });
    });


    /* ---------- Keterangan waktu relatif (Hari ini, Kemarin, dst.) ---------- */
    function tengahMalam(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); }

    page.querySelectorAll('.rw-rel').forEach(function (el) {
        const ts = parseInt(el.dataset.ts, 10);
        if (!ts) return;
        const selisih = Math.round((tengahMalam(new Date()) - tengahMalam(new Date(ts * 1000))) / 86400000);
        let teks = '';
        if (selisih === 0) teks = 'Hari ini';
        else if (selisih === 1) teks = 'Kemarin';
        else if (selisih > 1 && selisih <= 30) teks = selisih + ' hari lalu';
        el.textContent = teks;
    });


    if (!list || !items.length) return;


    /* ---------- Data tiap riwayat ---------- */
    items.forEach(function (el, i) {
        el._i = i;
        el._ts = parseInt(el.dataset.ts, 10) || 0;
        el._card = el.querySelector('details');
        el._rows = Array.from(el.querySelectorAll('tbody tr[data-name]'));
    });

    function esc(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }


    /* ---------- Jumlah di tiap filter ---------- */
    chips.forEach(function (btn) {
        const n = btn.querySelector('.rw-chip-n');
        if (!n) return;
        const jumlah = btn.dataset.chip === 'all'
            ? items.length
            : items.filter(function (el) { return el.dataset.waktu === btn.dataset.chip; }).length;

        if (btn.dataset.chip !== 'all' && jumlah === 0) { btn.hidden = true; return; }
        n.textContent = jumlah;
    });


    /* ---------- Tampilkan hasil ---------- */
    function render() {

        const q = state.q;

        const cocok = items.filter(function (el) {
            const okChip = state.chip === 'all' || el.dataset.waktu === state.chip;
            const okQ = !q || (el.dataset.foods || '').indexOf(q) !== -1;
            return okChip && okQ;
        });

        cocok.sort(function (a, b) {
            if (state.sort === 'oldest') return (a._ts - b._ts) || (b._i - a._i);
            return a._i - b._i;   // urutan awal dari server (terbaru dulu)
        });

        const frag = document.createDocumentFragment();
        cocok.forEach(function (el) { frag.appendChild(el); });
        list.appendChild(frag);

        const tampil = new Set(cocok);
        items.forEach(function (el) {
            el.hidden = !tampil.has(el);

            // sorot makanan yang cocok dan buka kartunya
            el._rows.forEach(function (tr) {
                const nameEl = tr.querySelector('.rw-name');
                const nama = tr.dataset.name || '';
                const hit = q && nama.indexOf(q) !== -1;
                tr.classList.toggle('is-match', !!hit);

                if (nameEl) {
                    if (!nameEl._asli) nameEl._asli = nameEl.textContent;
                    const t = nameEl._asli;
                    const idx = hit ? t.toLowerCase().indexOf(q) : -1;
                    nameEl.innerHTML = idx < 0
                        ? esc(t)
                        : esc(t.slice(0, idx)) + '<mark>' + esc(t.slice(idx, idx + q.length)) + '</mark>' + esc(t.slice(idx + q.length));
                }
            });
            if (q && !el.hidden && el._card && el.dataset.foods.indexOf(q) !== -1) el._card.open = true;
        });

        if (countEl) {
            countEl.textContent = cocok.length === items.length
                ? items.length + ' riwayat tersimpan'
                : 'Menampilkan ' + cocok.length + ' dari ' + items.length + ' riwayat';
        }
        if (noResult) noResult.style.display = cocok.length === 0 ? 'block' : 'none';
    }


    /* ---------- Pencarian ---------- */
    if (search) {
        let timer = null;
        search.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                state.q = search.value.toLowerCase().trim();
                render();
            }, 120);
        });

        function hapusCari() {
            search.value = '';
            state.q = '';
            render();
            search.focus();
        }
        if (clearBtn) clearBtn.addEventListener('click', hapusCari);

        document.addEventListener('keydown', function (e) {
            const tag = document.activeElement ? document.activeElement.tagName : '';
            if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(tag)) {
                e.preventDefault();
                search.focus();
                search.select();
            } else if (e.key === 'Escape' && document.activeElement === search && search.value) {
                hapusCari();
            }
        });
    }


    /* ---------- Urutkan ---------- */
    if (sortSel) {
        sortSel.addEventListener('change', function () {
            state.sort = sortSel.value;
            render();
        });
    }


    /* ---------- Filter waktu makan ---------- */
    chips.forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.chip = btn.dataset.chip;
            chips.forEach(function (c) { c.classList.toggle('is-active', c === btn); });
            render();
        });
    });


    /* ---------- Buka / tutup semua ---------- */
    function aturSemua(buka) {
        items.forEach(function (el) {
            if (!el.hidden && el._card) el._card.open = buka;
        });
    }
    if (openAll)  openAll.addEventListener('click', function () { aturSemua(true); });
    if (closeAll) closeAll.addEventListener('click', function () { aturSemua(false); });


    /* ---------- Atur ulang ---------- */
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (search) search.value = '';
            if (sortSel) sortSel.value = 'default';
            state.q = '';
            state.chip = 'all';
            state.sort = 'default';
            chips.forEach(function (c) { c.classList.toggle('is-active', c.dataset.chip === 'all'); });
            render();
        });
    }


    /* ---------- Hapus riwayat (jendela konfirmasi) ---------- */
    function pasangHapus() {

        const formHapus = document.getElementById('rwDeleteForm');

        if (!formHapus || !document.querySelector('.rw-delete')) {
            return;
        }

        const root = document.documentElement;

        let modalEl = null;
        let modalFokusAsal = null;
        let modalTimer = null;
        let alamatHapus = null;
        let sedangMenghapus = false;


        function buatModal() {
            modalEl = document.createElement('div');
            modalEl.className = 'rw-modal';
            modalEl.hidden = true;
            modalEl.innerHTML =
                '<div class="rw-modal-box" role="alertdialog" aria-modal="true" ' +
                    'aria-labelledby="rwModalTitle" aria-describedby="rwModalText">' +
                    '<span class="rw-modal-icon"><i class="fas fa-trash-alt"></i></span>' +
                    '<h3 class="rw-modal-title" id="rwModalTitle">Hapus riwayat ini?</h3>' +
                    '<p class="rw-modal-text" id="rwModalText"></p>' +
                    '<div class="rw-modal-actions">' +
                        '<button type="button" class="rw-modal-btn rw-modal-ghost" data-aksi="batal">Batal</button>' +
                        '<button type="button" class="rw-modal-btn rw-modal-danger" data-aksi="hapus">' +
                            '<span class="rw-modal-label">Ya, hapus</span>' +
                            '<span class="rw-modal-wait" hidden>' +
                                '<i class="fas fa-spinner fa-spin"></i> Menghapus...' +
                            '</span>' +
                        '</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(modalEl);

            modalEl.addEventListener('click', function (e) {
                const b = e.target.closest('.rw-modal-btn');
                if (!b) return;

                if (b.dataset.aksi === 'hapus') kirimHapus();
                else tutupModal();
            });

            // klik latar gelap = batal
            modalEl.addEventListener('mousedown', function (e) {
                if (e.target === modalEl) tutupModal();
            });

            modalEl.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    tutupModal();
                } else if (e.key === 'Tab') {
                    // fokus tetap berputar di dalam jendela
                    const tombol = modalEl.querySelectorAll('.rw-modal-btn:not(:disabled)');
                    if (!tombol.length) return;

                    const pertama = tombol[0];
                    const terakhir = tombol[tombol.length - 1];

                    if (e.shiftKey && document.activeElement === pertama) {
                        e.preventDefault();
                        terakhir.focus();
                    } else if (!e.shiftKey && document.activeElement === terakhir) {
                        e.preventDefault();
                        pertama.focus();
                    }
                }
            });
        }


        function bukaModal(tombol) {
            if (!modalEl) buatModal();

            alamatHapus = tombol.dataset.action;
            modalFokusAsal = tombol;

            modalEl.querySelector('.rw-modal-text').textContent =
                'Riwayat ' + tombol.dataset.judul +
                ' yang disimpan pada ' + tombol.dataset.tanggal +
                ' beserta ' + tombol.dataset.jumlah +
                ' makanan di dalamnya akan dihapus permanen. ' +
                'Tindakan ini tidak bisa dibatalkan.';

            aturKeadaanTombol(false);

            clearTimeout(modalTimer);
            modalEl.hidden = false;
            root.classList.add('rw-lock');
            void modalEl.offsetWidth; // mulai animasi masuk
            modalEl.classList.add('is-show');

            // fokus awal ke "Batal" supaya tidak terhapus tanpa sengaja
            modalEl.querySelector('.rw-modal-ghost').focus();
        }


        function tutupModal() {
            if (!modalEl || sedangMenghapus) return;

            modalEl.classList.remove('is-show');
            root.classList.remove('rw-lock');

            clearTimeout(modalTimer);
            modalTimer = setTimeout(function () { modalEl.hidden = true; }, 200);

            if (modalFokusAsal && modalFokusAsal.focus) modalFokusAsal.focus();
        }


        function aturKeadaanTombol(sibuk) {
            sedangMenghapus = sibuk;

            modalEl.querySelectorAll('.rw-modal-btn').forEach(function (b) {
                b.disabled = sibuk;
            });

            modalEl.querySelector('.rw-modal-label').hidden = sibuk;
            modalEl.querySelector('.rw-modal-wait').hidden = !sibuk;
        }


        function kirimHapus() {
            if (sedangMenghapus || !alamatHapus) return;

            aturKeadaanTombol(true);

            formHapus.action = alamatHapus;
            formHapus.submit();
        }


        // klik ikon tempat sampah (ada di dalam <summary>, jadi jangan ikut membuka/menutup kartu)
        document.addEventListener('click', function (e) {
            const tombol = e.target.closest('.rw-delete');
            if (!tombol) return;

            e.preventDefault();
            e.stopPropagation();

            bukaModal(tombol);
        });


        // kembali dari riwayat browser: pastikan jendela tertutup dan tombol aktif
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted || !modalEl) return;

            aturKeadaanTombol(false);
            modalEl.hidden = true;
            modalEl.classList.remove('is-show');
            root.classList.remove('rw-lock');
        });
    }

    pasangHapus();


    render();
});