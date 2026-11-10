document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('profileForm');
    const submitButton = document.getElementById('submitProfile');

    if (!form || !submitButton) {
        return;
    }

    const labelAwal = submitButton.innerHTML;

    // Hapus status error saat pengguna memperbaiki input.
    form.querySelectorAll('input').forEach(function (input) {
        input.addEventListener('input', function () {
            input.setCustomValidity('');
        });

        input.addEventListener('change', function () {
            input.setCustomValidity('');
        });
    });

    // Pastikan pengguna memilih jenis kelamin.
    const genderInputs = form.querySelectorAll(
        'input[name="jenis_kelamin"]'
    );

    genderInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            genderInputs.forEach(function (gender) {
                gender.setCustomValidity('');
            });
        });
    });

    // Validasi sebelum formulir dikirim ke Laravel.
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        // Cegah pengiriman berulang karena tombol diklik berkali-kali.
        submitButton.disabled = true;
        submitButton.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i>' +
            '<span>Menyimpan profil...</span>';
    });


    /* =========================================================
       PERINGATAN PERUBAHAN BELUM DISIMPAN
       Muncul jika pengguna mengubah data lalu mencoba pindah halaman.
       Gaya jendela ada di file CSS profil (kelas ug-*).
    ========================================================= */

    const root = document.documentElement;

    let leaving = false;       // pengguna memilih membuang perubahan dan pergi
    let submitting = false;    // formulir sedang dikirim
    let guardPushed = false;   // entri riwayat penjaga untuk tombol "kembali"
    let userTouched = false;   // pengguna sudah mengubah sesuatu
    let snapshot = '';

    let modalEl = null;
    let modalSelesai = null;
    let modalFokusAsal = null;
    let modalTimer = null;


    /* ---------- Isi formulir sebagai teks untuk dibandingkan ---------- */
    function ambilNilai() {
        const out = [];

        Array.prototype.forEach.call(form.elements, function (el) {
            if (!el.name || el.disabled || el.hasAttribute('data-guard-ignore')) return;
            if (el.name === '_token' || el.name === '_method') return;

            const tag = el.tagName;
            const type = (el.type || '').toLowerCase();

            if (tag === 'FIELDSET' || tag === 'OBJECT' || tag === 'OUTPUT') return;
            if (type === 'submit' || type === 'button' || type === 'reset' || type === 'image') return;

            if (type === 'checkbox' || type === 'radio') {
                out.push([el.name, el.value, el.checked]);
            } else if (type === 'file') {
                out.push([el.name, Array.from(el.files || []).map(function (f) {
                    return f.name + ':' + f.size;
                }).join('|')]);
            } else if (tag === 'SELECT' && el.multiple) {
                out.push([el.name, Array.from(el.selectedOptions).map(function (o) {
                    return o.value;
                }).join('|')]);
            } else {
                out.push([el.name, el.value]);
            }
        });

        return JSON.stringify(out);
    }

    function isDirty() {
        return ambilNilai() !== snapshot;
    }

    function adaPerubahan() {
        return !leaving && !submitting && isDirty();
    }

    snapshot = ambilNilai();

    // isian otomatis browser kadang masuk setelah halaman selesai dimuat
    window.addEventListener('load', function () {
        setTimeout(function () {
            if (!userTouched) snapshot = ambilNilai();
        }, 400);
    });

    ['input', 'change'].forEach(function (nama) {
        form.addEventListener(nama, function (e) {
            if (e.isTrusted) userTouched = true;
            pasangGuard();
        });
    });


    /* ---------- Jendela konfirmasi ---------- */
    function buatModal() {
        modalEl = document.createElement('div');
        modalEl.className = 'ug-modal';
        modalEl.hidden = true;
        modalEl.innerHTML =
            '<div class="ug-box" role="alertdialog" aria-modal="true" ' +
                'aria-labelledby="ugTitle" aria-describedby="ugText">' +
                '<span class="ug-icon"><i class="fas fa-save"></i></span>' +
                '<h3 class="ug-title" id="ugTitle">Simpan perubahan profil?</h3>' +
                '<p class="ug-text" id="ugText">Kamu sudah mengubah data profil, tetapi belum ' +
                    'menyimpannya. Simpan sekarang sebelum meninggalkan halaman?</p>' +
                '<div class="ug-actions">' +
                    '<button type="button" class="ug-btn ug-primary" data-id="simpan">Simpan perubahan</button>' +
                    '<button type="button" class="ug-btn ug-discard" data-id="buang">Buang perubahan</button>' +
                    '<button type="button" class="ug-btn ug-ghost" data-id="tetap">Tetap di halaman</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modalEl);

        modalEl.addEventListener('click', function (e) {
            const b = e.target.closest('.ug-btn');
            if (b) tutupModal(b.dataset.id);
        });

        // klik latar gelap = tetap di halaman
        modalEl.addEventListener('mousedown', function (e) {
            if (e.target === modalEl) tutupModal('tetap');
        });

        modalEl.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                tutupModal('tetap');
            } else if (e.key === 'Tab') {
                // fokus tetap berputar di dalam jendela
                const tombol = modalEl.querySelectorAll('.ug-btn');
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
        root.classList.remove('ug-lock');

        clearTimeout(modalTimer);
        modalTimer = setTimeout(function () { modalEl.hidden = true; }, 200);

        if (modalFokusAsal && modalFokusAsal.focus) modalFokusAsal.focus();
        selesai(hasil);
    }

    function tampilkanModal() {
        if (!modalEl) buatModal();
        if (modalSelesai) tutupModal('tetap');

        return new Promise(function (resolve) {
            modalSelesai = resolve;
            modalFokusAsal = document.activeElement;

            clearTimeout(modalTimer);
            modalEl.hidden = false;
            root.classList.add('ug-lock');
            void modalEl.offsetWidth; // mulai animasi masuk
            modalEl.classList.add('is-show');

            modalEl.querySelector('.ug-primary').focus();
        });
    }

    function simpan() {
        // kolom wajib yang belum benar ditunjukkan dulu, bukan dikirim begitu saja
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            submitButton.click();
        }
    }

    function tanyaKeluar(lanjut) {
        tampilkanModal().then(function (hasil) {
            if (hasil === 'simpan') {
                simpan();
            } else if (hasil === 'buang') {
                leaving = true;
                lanjut();
            }
        });
    }


    /* ---------- Klik tautan mana pun (menu, topbar, dll.) ---------- */
    document.addEventListener('click', function (e) {
        if (!adaPerubahan()) return;
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

        const a = e.target.closest('a[href]');
        if (!a || (modalEl && modalEl.contains(a))) return;

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


    /* ---------- Formulir lain di halaman (mis. Logout di topbar) ---------- */
    document.addEventListener('submit', function (e) {
        if (e.target === form || !adaPerubahan()) return;

        e.preventDefault();
        e.stopPropagation();

        const f = e.target;
        tanyaKeluar(function () { f.submit(); });
    }, true);

    // formulir profil: tandai "sedang dikirim" hanya jika pengiriman tidak dibatalkan
    document.addEventListener('submit', function (e) {
        if (e.target === form && !e.defaultPrevented) submitting = true;
    });


    /* ---------- Tutup tab, muat ulang, atau ketik alamat lain ---------- */
    // hanya bisa memakai peringatan bawaan browser
    window.addEventListener('beforeunload', function (e) {
        if (!adaPerubahan()) return;
        e.preventDefault();
        e.returnValue = '';
    });


    /* ---------- Tombol "kembali" (termasuk gestur kembali di Android) ---------- */
    function pasangGuard() {
        if (guardPushed || !window.history.pushState || !isDirty()) return;
        window.history.pushState({ ugGuard: true }, '', window.location.href);
        guardPushed = true;
    }

    window.addEventListener('popstate', function (e) {
        if (!guardPushed || leaving) return;
        if (e.state && e.state.ugGuard) return; // maju kembali ke entri penjaga

        if (isDirty() && !submitting) {
            // tetap di halaman ini, lalu tanya dulu
            window.history.pushState({ ugGuard: true }, '', window.location.href);
            tanyaKeluar(function () { window.history.go(-2); });
        } else {
            // tidak ada perubahan lagi: lanjutkan kembali seperti biasa
            guardPushed = false;
            window.history.back();
        }
    });


    /* ---------- Kembali dari riwayat browser: pulihkan tombol ---------- */
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;

        submitting = false;
        leaving = false;
        submitButton.disabled = false;
        submitButton.innerHTML = labelAwal;
    });
});