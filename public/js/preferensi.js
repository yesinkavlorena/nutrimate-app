document.addEventListener('DOMContentLoaded', function () {

    const page = document.getElementById('pfPage');
    if (!page) return;

    const MAX = parseInt(page.dataset.max, 10) || 7;
    const $ = function (id) { return document.getElementById(id); };
    const root = document.documentElement;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const grid       = $('pfGrid');
    const search     = $('pfSearch');
    const clearBtn   = $('pfClear');
    const result     = $('pfResult');
    const empty      = $('pfEmpty');
    const emptyTitle = $('pfEmptyTitle');
    const emptyText  = $('pfEmptyText');
    const hint       = $('pfHint');
    const dock       = $('pfDock');
    const countEl    = $('pfCount');
    const meterTitle = $('pfMeterTitle');
    const meterText  = $('pfMeterText');
    const ring       = $('pfRing');
    const ringWrap   = $('pfRingWrap');
    const slotsBox   = $('pfSlots');
    const resetBtn   = $('pfReset');
    const restoreBtn = $('pfRestore');
    const saveBtn    = $('pfSave');
    const saveLabel  = $('pfSaveLabel');
    const countAll   = $('pfCountAll');
    const countSel   = $('pfCountSel');
    const toastEl    = $('pfToast');
    const form       = $('pfForm');
    const tabs       = Array.from(page.querySelectorAll('.pf-tab'));

    // mode "edit": tombol simpan baru aktif kalau pilihan berbeda dari yang tersimpan
    const isEdit   = page.dataset.mode === 'edit';
    const saveText = page.dataset.saveLabel || 'Simpan Preferensi';
    let initial = null;      // Set id makanan yang tersimpan (mode edit)


    /* ---------- Data awal (dibaca sekali, lalu dipakai ulang) ---------- */
    const items = Array.from(grid.querySelectorAll('.pf-item')).map(function (el) {
        const nameEl = el.querySelector('.pf-name');
        const imgEl = el.querySelector('.pf-img img');
        const label = nameEl.textContent.trim();
        return {
            el: el,
            input: el.querySelector('.pf-input'),
            nameEl: nameEl,
            label: label,
            key: label.toLowerCase(),
            thumb: imgEl ? imgEl.getAttribute('src') : null
        };
    });

    const byInput = new Map();
    const byValue = new Map();
    items.forEach(function (it) {
        byInput.set(it.input, it);
        byValue.set(it.input.value, it);
    });

    countAll.textContent = items.length;

    let order = [];          // urutan pilihan (sesuai waktu dipilih)
    let filter = 'all';
    let keyword = '';
    let submitting = false;
    let wasReady = null;
    let leaving = false;      // true saat pengguna memilih membuang perubahan dan pergi
    let guardPushed = false;  // entri riwayat penjaga untuk tombol "kembali"


    /* ---------- Cincin progres ---------- */
    const ringLen = 2 * Math.PI * ring.r.baseVal.value;
    ring.style.strokeDasharray = ringLen;
    ring.style.strokeDashoffset = ringLen;


    /* ---------- Slot pilihan: satu slot per makanan ---------- */
    slotsBox.style.setProperty('--n', MAX);

    const slotEls = [];
    for (let i = 0; i < MAX; i++) {
        const s = document.createElement('button');
        s.type = 'button';
        s.className = 'pf-slot';
        s.dataset.id = '';
        s.disabled = true;
        s.textContent = i + 1;
        s.setAttribute('aria-label', 'Slot ' + (i + 1) + ' kosong');
        slotsBox.appendChild(s);
        slotEls.push(s);
    }

    function isiSlot(slot, it) {
        slot.textContent = '';

        if (!it) {
            slot.classList.remove('is-filled');
            slot.disabled = true;
            slot.title = '';
            slot.textContent = slotEls.indexOf(slot) + 1;
            slot.setAttribute('aria-label', 'Slot ' + (slotEls.indexOf(slot) + 1) + ' kosong');
            return;
        }

        function ikon() {
            const ph = document.createElement('i');
            ph.className = 'fas fa-utensils pf-slot-ph';
            return ph;
        }

        if (it.thumb) {
            const img = document.createElement('img');
            img.src = it.thumb;
            img.alt = '';
            img.addEventListener('error', function () { img.replaceWith(ikon()); });
            slot.appendChild(img);
        } else {
            slot.appendChild(ikon());
        }

        const x = document.createElement('span');
        x.className = 'pf-slot-x';
        x.innerHTML = '<i class="fas fa-times"></i>';
        slot.appendChild(x);

        slot.classList.add('is-filled');
        slot.disabled = false;
        slot.title = 'Hapus ' + it.label;
        slot.setAttribute('aria-label', 'Hapus ' + it.label);
    }

    function renderSlots() {
        slotEls.forEach(function (slot, i) {
            const it = order[i];
            const id = it ? it.input.value : '';
            if (slot.dataset.id === id) return;

            const barusTerisi = slot.dataset.id === '' && id !== '';
            slot.dataset.id = id;
            isiSlot(slot, it);

            if (barusTerisi) {
                slot.classList.remove('is-pop');
                void slot.offsetWidth; // ulangi animasi
                slot.classList.add('is-pop');
            }
        });
    }

    slotsBox.addEventListener('click', function (e) {
        const slot = e.target.closest('.pf-slot.is-filled');
        if (!slot) return;
        hapus(byValue.get(slot.dataset.id));
    });

    slotsBox.addEventListener('animationend', function (e) {
        if (e.animationName === 'pfPop') e.target.classList.remove('is-pop');
    });


    /* ---------- Percikan saat pilihan lengkap ---------- */
    function burst() {
        if (reduce) return;
        const warna = ['#1cc88a', '#2fd9a0', '#f6c23e', '#ff8a4c', '#4ee3b0'];

        for (let k = 0; k < 14; k++) {
            const s = document.createElement('span');
            s.className = 'pf-spark';
            s.style.background = warna[k % warna.length];
            s.style.setProperty('--a', (k * (360 / 14)) + 'deg');
            s.style.setProperty('--d', Math.round(40 + Math.random() * 22));
            ringWrap.appendChild(s);
            setTimeout(function () { s.remove(); }, 750);
        }
    }


    /* ---------- Jendela konfirmasi (modal) ---------- */
    let modalEl = null;
    let modalSelesai = null;
    let modalBatalId = null;
    let modalFokusAsal = null;
    let modalTimer = null;

    function buatModal() {
        modalEl = document.createElement('div');
        modalEl.className = 'pf-modal';
        modalEl.hidden = true;
        modalEl.innerHTML =
            '<div class="pf-modal-box" role="alertdialog" aria-modal="true" ' +
                'aria-labelledby="pfModalTitle" aria-describedby="pfModalText">' +
                '<span class="pf-modal-icon"><i class="fas"></i></span>' +
                '<h3 class="pf-modal-title" id="pfModalTitle"></h3>' +
                '<p class="pf-modal-text" id="pfModalText"></p>' +
                '<div class="pf-modal-actions"></div>' +
            '</div>';
        document.body.appendChild(modalEl);

        // klik latar gelap = batal
        modalEl.addEventListener('mousedown', function (e) {
            if (e.target === modalEl) tutupModal(modalBatalId);
        });

        modalEl.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                tutupModal(modalBatalId);
            } else if (e.key === 'Tab') {
                // fokus tetap berputar di dalam jendela
                const tombol = modalEl.querySelectorAll('.pf-modal-btn');
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

    function tutupModal(hasil) {
        if (!modalEl || !modalSelesai) return;

        const selesai = modalSelesai;
        modalSelesai = null;

        modalEl.classList.remove('is-show');
        root.classList.remove('pf-lock');

        clearTimeout(modalTimer);
        modalTimer = setTimeout(function () { modalEl.hidden = true; }, 200);

        if (modalFokusAsal && modalFokusAsal.focus) modalFokusAsal.focus();
        selesai(hasil);
    }

    /*
     * Menampilkan jendela konfirmasi. Mengembalikan Promise berisi id tombol yang ditekan.
     * opsi: { ikon, warna ('danger'|'warn'), judul, teks, batalId,
     *         tombol: [{ id, label, gaya ('primary'|'danger'|'discard'|'ghost'), fokus }] }
     */
    function konfirmasi(opsi) {
        if (!modalEl) buatModal();
        if (modalSelesai) tutupModal(opsi.batalId);

        const icon = modalEl.querySelector('.pf-modal-icon');
        icon.className = 'pf-modal-icon is-' + (opsi.warna || 'danger');
        icon.querySelector('i').className = 'fas ' + (opsi.ikon || 'fa-question');

        modalEl.querySelector('.pf-modal-title').textContent = opsi.judul;
        modalEl.querySelector('.pf-modal-text').textContent = opsi.teks;

        const aksi = modalEl.querySelector('.pf-modal-actions');
        aksi.textContent = '';
        aksi.classList.toggle('is-stack', opsi.tombol.length > 2);

        let tombolFokus = null;
        opsi.tombol.forEach(function (t) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'pf-modal-btn pf-modal-' + (t.gaya || 'ghost');
            b.textContent = t.label;
            b.addEventListener('click', function () { tutupModal(t.id); });
            aksi.appendChild(b);
            if (t.fokus || !tombolFokus) tombolFokus = b;
        });

        modalBatalId = opsi.batalId;

        return new Promise(function (resolve) {
            modalSelesai = resolve;
            modalFokusAsal = document.activeElement;

            clearTimeout(modalTimer);
            modalEl.hidden = false;
            root.classList.add('pf-lock');
            void modalEl.offsetWidth; // mulai animasi masuk
            modalEl.classList.add('is-show');

            tombolFokus.focus();
        });
    }


    /* ---------- Toast ---------- */
    let toastTimer = null;
    function toast(message, type) {
        toastEl.textContent = message;
        toastEl.className = 'pf-toast is-show' + (type ? ' is-' + type : '');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toastEl.classList.remove('is-show');
        }, 2400);
    }


    /* ---------- Hindari tabrakan dengan menu tetap di bawah layar ---------- */
    // Mencari elemen position:fixed yang menempel di bawah layar (menu navigasi HP)
    // lalu bilah simpan diangkat setinggi menu itu.
    function ukurMenuBawah() {
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        let atas = vh;

        document.body.querySelectorAll(':scope > *, :scope > * > *, :scope > * > * > *').forEach(function (n) {
            if (page.contains(n) || n.contains(page) || n.classList.contains('pf-toast')) return;

            const cs = getComputedStyle(n);
            if (cs.position !== 'fixed' || cs.display === 'none' || cs.visibility === 'hidden') return;

            const r = n.getBoundingClientRect();
            if (r.height <= 0 || r.width < vw * 0.4) return;        // bukan bilah lebar
            if (r.bottom < vh - 40 || r.top < vh * 0.5) return;     // tidak menempel di bawah

            atas = Math.min(atas, r.top);
        });

        const tinggi = Math.round(vh - atas);

        if (tinggi > 0) root.style.setProperty('--pf-dock-offset', tinggi + 'px');
        else root.style.removeProperty('--pf-dock-offset');

        root.style.setProperty('--pf-dock-h', dock.offsetHeight + 'px');
    }

    let ukurFrame = null;
    function jadwalkanUkur() {
        cancelAnimationFrame(ukurFrame);
        ukurFrame = requestAnimationFrame(ukurMenuBawah);
    }

    window.addEventListener('resize', jadwalkanUkur);
    window.addEventListener('orientationchange', jadwalkanUkur);
    window.addEventListener('load', jadwalkanUkur);
    if ('ResizeObserver' in window) new ResizeObserver(jadwalkanUkur).observe(dock);


    /* ---------- Sinkronkan urutan dengan kotak centang ---------- */
    function syncOrder() {
        order = order.filter(function (it) { return it.input.checked; });
        items.forEach(function (it) {
            if (it.input.checked && order.indexOf(it) === -1) order.push(it);
        });
        // jaga agar tidak pernah melebihi batas (misalnya data lama dari browser)
        while (order.length > MAX) {
            order.pop().input.checked = false;
        }
    }


    /* ---------- Apakah pilihan berbeda dari yang tersimpan? ---------- */
    function isDirty() {
        if (!isEdit || !initial) return true;
        if (order.length !== initial.size) return true;
        return order.some(function (it) { return !initial.has(it.input.value); });
    }


    /* ---------- Perbarui seluruh tampilan hitungan ---------- */
    function updateUI() {
        const n = order.length;
        const sisa = MAX - n;
        const full = n === MAX;
        const dirty = isDirty();
        const siap = full && dirty;
        const terakhir = order[n - 1];

        countEl.textContent = n + '/' + MAX;
        countSel.textContent = n;
        ring.style.strokeDashoffset = ringLen * (1 - n / MAX);
        dock.dataset.state = siap ? 'full' : (full ? 'same' : (n === 0 ? 'empty' : 'progress'));
        grid.classList.toggle('is-maxed', full);

        if (n === 0) {
            hint.textContent = isEdit
                ? 'Pilih ' + MAX + ' makanan baru, atau kembalikan pilihan awal.'
                : 'Pilih ' + MAX + ' makanan yang paling kamu sukai.';
            meterTitle.textContent = isEdit ? 'Pilihan kosong' : 'Mulai memilih';
            meterText.textContent = isEdit ? 'Pilih ' + MAX + ' makanan baru' : 'Klik kartu makanan untuk memilih';
        } else if (!full) {
            hint.textContent = 'Bagus! Pilih ' + sisa + ' makanan lagi.';
            meterTitle.textContent = 'Kurang ' + sisa + ' lagi';
            meterText.textContent = 'Terakhir: ' + terakhir.label;
        } else if (isEdit && !dirty) {
            hint.textContent = 'Ini pilihan kamu saat ini. Ubah salah satunya untuk bisa menyimpan.';
            meterTitle.textContent = 'Belum ada perubahan';
            meterText.textContent = 'Ubah salah satu untuk menyimpan';
        } else {
            hint.textContent = isEdit
                ? 'Perubahan siap disimpan.'
                : 'Sudah lengkap, tinggal simpan preferensimu.';
            meterTitle.textContent = isEdit ? 'Siap disimpan' : 'Pilihan lengkap!';
            meterText.textContent = 'Tekan tombol simpan untuk melanjutkan';
        }

        saveBtn.disabled = !siap || submitting;
        saveLabel.textContent = siap ? saveText : (full ? 'Belum ada perubahan' : 'Pilih ' + sisa + ' lagi');
        resetBtn.disabled = n === 0 || submitting;
        if (restoreBtn) restoreBtn.disabled = !dirty || submitting;

        renderSlots();

        if (wasReady !== null && siap && !wasReady) burst();
        wasReady = siap;

        pasangGuard();

        jadwalkanUkur();
    }


    /* ---------- Sorot kata yang dicari ---------- */
    function setName(it) {
        const el = it.nameEl;
        const idx = keyword ? it.key.indexOf(keyword) : -1;

        if (idx < 0) {
            if (el.textContent !== it.label) el.textContent = it.label;
            return;
        }

        el.textContent = '';
        el.appendChild(document.createTextNode(it.label.slice(0, idx)));

        const mark = document.createElement('mark');
        mark.textContent = it.label.slice(idx, idx + keyword.length);
        el.appendChild(mark);

        el.appendChild(document.createTextNode(it.label.slice(idx + keyword.length)));
    }


    /* ---------- Saring daftar (pencarian + tab) ---------- */
    function applyFilter() {
        let visible = 0;

        items.forEach(function (it) {
            const cocokTab = filter === 'all' || it.input.checked;
            const cocokKata = !keyword || it.key.indexOf(keyword) !== -1;
            const tampil = cocokTab && cocokKata;

            it.el.hidden = !tampil;
            if (tampil) visible++;
            setName(it);
        });

        clearBtn.hidden = search.value.length === 0;
        empty.hidden = visible > 0;

        if (visible === 0) {
            if (filter === 'selected' && !keyword) {
                emptyTitle.textContent = 'Belum ada yang dipilih';
                emptyText.textContent = 'Klik kartu makanan untuk memilihnya.';
            } else {
                emptyTitle.textContent = 'Makanan tidak ditemukan';
                emptyText.textContent = 'Coba gunakan kata pencarian lain.';
            }
        }

        if (filter === 'selected') {
            result.textContent = keyword
                ? visible + ' makanan terpilih cocok dengan pencarian'
                : order.length + ' makanan terpilih';
        } else if (keyword) {
            result.textContent = 'Menampilkan ' + visible + ' dari ' + items.length + ' makanan';
        } else {
            result.textContent = items.length + ' makanan tersedia';
        }
    }


    /* ---------- Hapus satu pilihan ---------- */
    function hapus(it) {
        if (!it) return;
        it.input.checked = false;
        order = order.filter(function (x) { return x !== it; });
        updateUI();
        if (filter === 'selected') applyFilter();
    }


    /* ---------- Memilih / membatalkan ---------- */
    grid.addEventListener('change', function (e) {
        const it = byInput.get(e.target);
        if (!it) return;

        if (it.input.checked) {
            if (order.length >= MAX) {
                it.input.checked = false;
                toast('Maksimal ' + MAX + ' makanan. Hapus salah satu dulu.', 'warn');
                it.el.classList.remove('is-shake');
                void it.el.offsetWidth; // ulangi animasi
                it.el.classList.add('is-shake');
                return;
            }
            order.push(it);
            if (order.length === MAX && isDirty()) {
                toast(isEdit ? 'Pilihan baru lengkap, tinggal simpan.' : 'Mantap! ' + MAX + ' makanan sudah terpilih.', 'ok');
            }
        } else {
            order = order.filter(function (x) { return x !== it; });
        }

        updateUI();
        if (filter === 'selected') applyFilter();
    });

    grid.addEventListener('animationend', function (e) {
        if (e.animationName === 'pfShake') e.target.classList.remove('is-shake');
    });


    /* ---------- Reset ---------- */
    function kosongkan() {
        items.forEach(function (it) { it.input.checked = false; });
        order = [];
        updateUI();
        if (filter === 'selected') applyFilter();
        toast('Semua pilihan dihapus.');
    }

    resetBtn.addEventListener('click', function () {
        // di halaman edit, mengosongkan perlu konfirmasi
        if (isEdit && order.length > 0) {
            konfirmasi({
                ikon: 'fa-eraser',
                warna: 'danger',
                judul: 'Kosongkan semua pilihan?',
                teks: 'Kamu akan menghapus ' + order.length + ' makanan yang sedang dipilih. ' +
                      'Pilihan yang tersimpan tidak berubah sampai kamu menekan simpan, ' +
                      'dan bisa dipulihkan dengan tombol Kembalikan.',
                batalId: 'batal',
                tombol: [
                    { id: 'batal', label: 'Batal', gaya: 'ghost', fokus: true },
                    { id: 'ya', label: 'Ya, kosongkan', gaya: 'danger' }
                ]
            }).then(function (hasil) {
                if (hasil === 'ya') kosongkan();
            });
            return;
        }

        kosongkan();
    });


    /* ---------- Kembalikan ke pilihan yang tersimpan (mode edit) ---------- */
    if (restoreBtn) {
        restoreBtn.addEventListener('click', function () {
            if (!initial) return;

            items.forEach(function (it) { it.input.checked = initial.has(it.input.value); });
            order = [];
            syncOrder();
            updateUI();
            applyFilter();
            toast('Pilihan dikembalikan seperti semula.');
        });
    }


    /* ---------- Tab Semua / Terpilih ---------- */
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            filter = tab.dataset.filter;
            tabs.forEach(function (t) {
                const aktif = t === tab;
                t.classList.toggle('is-active', aktif);
                t.setAttribute('aria-pressed', aktif ? 'true' : 'false');
            });
            applyFilter();
        });
    });


    /* ---------- Pencarian (ditunda sebentar agar ringan) ---------- */
    let searchTimer = null;
    search.addEventListener('input', function () {
        clearBtn.hidden = search.value.length === 0;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            keyword = search.value.trim().toLowerCase();
            applyFilter();
        }, 120);
    });

    search.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        } else if (e.key === 'Escape') {
            if (search.value) {
                search.value = '';
                keyword = '';
                applyFilter();
            } else {
                search.blur();
            }
        }
    });

    clearBtn.addEventListener('click', function () {
        search.value = '';
        keyword = '';
        applyFilter();
        search.focus();
    });

    // tekan "/" di mana saja untuk langsung mencari
    document.addEventListener('keydown', function (e) {
        if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;

        const t = e.target;
        const mengetik = t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable);
        if (mengetik) return;

        e.preventDefault();
        search.focus();
        search.select();
    });


    /* ---------- Gambar gagal dimuat: ganti ikon ---------- */
    function gambarRusak(img) {
        const box = img.parentNode;
        if (!box) return;
        box.innerHTML = '<span class="pf-img-ph"><i class="fas fa-utensils"></i></span>';
    }

    grid.addEventListener('error', function (e) {
        if (e.target && e.target.tagName === 'IMG') gambarRusak(e.target);
    }, true);

    grid.querySelectorAll('.pf-img img').forEach(function (img) {
        if (img.complete && img.naturalWidth === 0) gambarRusak(img);
    });


    /* ---------- Kirim formulir ---------- */
    form.addEventListener('submit', function (e) {
        if (submitting) {
            e.preventDefault();
            return;
        }

        if (order.length !== MAX) {
            e.preventDefault();
            toast('Silakan pilih tepat ' + MAX + ' makanan.', 'warn');
            return;
        }

        if (isEdit && !isDirty()) {
            e.preventDefault();
            toast('Belum ada perubahan untuk disimpan.');
            return;
        }

        // cegah klik ganda setelah formulir terkirim
        submitting = true;
        requestAnimationFrame(function () {
            saveBtn.dataset.busy = '1';
            saveBtn.querySelector('.pf-save-label').hidden = true;
            saveBtn.querySelector('.pf-save-wait').hidden = false;
            saveBtn.disabled = true;
            resetBtn.disabled = true;
            if (restoreBtn) restoreBtn.disabled = true;
        });
    });

    /* ---------- Peringatan perubahan belum disimpan (halaman edit) ---------- */
    function tanyaKeluar(lanjut) {
        const n = order.length;
        const opsi = n === MAX
            ? {
                ikon: 'fa-save',
                warna: 'warn',
                judul: 'Simpan perubahan?',
                teks: 'Kamu sudah mengubah pilihan makanan favoritmu, tetapi belum menyimpannya. ' +
                      'Simpan sekarang sebelum meninggalkan halaman?',
                batalId: 'tetap',
                tombol: [
                    { id: 'simpan', label: 'Simpan perubahan', gaya: 'primary', fokus: true },
                    { id: 'buang', label: 'Buang perubahan', gaya: 'discard' },
                    { id: 'tetap', label: 'Tetap di halaman', gaya: 'ghost' }
                ]
            }
            : {
                ikon: 'fa-exclamation-triangle',
                warna: 'warn',
                judul: 'Perubahan belum lengkap',
                teks: 'Pilihanmu baru ' + n + ' dari ' + MAX + ' makanan, jadi belum bisa disimpan. ' +
                      'Kalau keluar sekarang, perubahan akan dibuang.',
                batalId: 'tetap',
                tombol: [
                    { id: 'tetap', label: 'Lanjut memilih', gaya: 'ghost', fokus: true },
                    { id: 'buang', label: 'Buang & keluar', gaya: 'discard' }
                ]
            };

        konfirmasi(opsi).then(function (hasil) {
            if (hasil === 'simpan') {
                saveBtn.click();
            } else if (hasil === 'buang') {
                leaving = true;
                lanjut();
            }
        });
    }

    function adaPerubahan() {
        return isEdit && !leaving && !submitting && isDirty();
    }

    // klik tautan mana pun (menu, topbar, tombol Batal, dll.)
    document.addEventListener('click', function (e) {
        if (!adaPerubahan()) return;
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

        const a = e.target.closest('a[href]');
        if (!a) return;

        const href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) return;
        if (a.target && a.target !== '_self') return;
        if (a.hasAttribute('download') || a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-toggle')) return;

        e.preventDefault();
        e.stopPropagation();

        tanyaKeluar(function () {
            // replace: entri penjaga diganti, jadi tombol kembali tidak perlu ditekan dua kali
            if (guardPushed) window.location.replace(a.href);
            else window.location.href = a.href;
        });
    }, true);

    // formulir lain di halaman (mis. Logout di topbar)
    document.addEventListener('submit', function (e) {
        if (e.target === form || !adaPerubahan()) return;

        e.preventDefault();
        e.stopPropagation();

        const f = e.target;
        tanyaKeluar(function () { f.submit(); });
    }, true);

    // tutup tab, muat ulang, atau ketik alamat lain: hanya bisa memakai peringatan bawaan browser
    window.addEventListener('beforeunload', function (e) {
        if (!adaPerubahan()) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // tombol "kembali" (termasuk gestur kembali di Android)
    function pasangGuard() {
        if (!isEdit || guardPushed || !isDirty() || !window.history.pushState) return;
        window.history.pushState({ pfGuard: true }, '', window.location.href);
        guardPushed = true;
    }

    window.addEventListener('popstate', function (e) {
        if (!guardPushed || leaving) return;
        if (e.state && e.state.pfGuard) return; // maju kembali ke entri penjaga

        if (isDirty() && !submitting) {
            // tetap di halaman ini, lalu tanya dulu
            window.history.pushState({ pfGuard: true }, '', window.location.href);
            tanyaKeluar(function () { window.history.go(-2); });
        } else {
            // tidak ada perubahan lagi: lanjutkan kembali seperti biasa
            guardPushed = false;
            window.history.back();
        }
    });


    // kembali dari riwayat browser: pulihkan tombol dan sinkronkan pilihan
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;

        submitting = false;
        leaving = false;
        delete saveBtn.dataset.busy;
        saveBtn.querySelector('.pf-save-label').hidden = false;
        saveBtn.querySelector('.pf-save-wait').hidden = true;

        syncOrder();
        updateUI();
        applyFilter();
    });


    /* ---------- Keadaan awal ---------- */
    syncOrder();

    if (isEdit) {
        let awal = [];
        try { awal = JSON.parse(page.dataset.initial || '[]'); } catch (err) { awal = []; }
        initial = awal.length
            ? new Set(awal.map(String))
            : new Set(order.map(function (it) { return it.input.value; }));
    }

    updateUI();
    applyFilter();
    ukurMenuBawah();

});