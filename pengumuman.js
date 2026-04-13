/**
 * FILE: public/js/admin/pengumuman.js
 *
 * Kelola Pengumuman — Full AJAX SPA
 * Pola identik dengan admin-manage-users.js:
 *   - Setelah store / update / hapus → loadPage('pengumuman') me-refresh tabel
 *   - SweetAlert2 tengah layar (bukan toast), kecuali toggle-status yang
 *     hanya membutuhkan notifikasi ringan → tetap toast
 *   - Validasi karakter berbahaya & counter real-time dipertahankan
 */

(function () {
    'use strict';

    /* ────────────────────────────────────────────────────────────
       KONSTANTA
    ──────────────────────────────────────────────────────────── */
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var LIMIT = { judul: 100, konten: 1000, badge_label: 50 };

    // Karakter yang diblokir: < > " ' ` ; { } \
    var BLOCKED_CHARS = /[<>"'`\\;{}]/g;

    /* ────────────────────────────────────────────────────────────
       HELPER: Modal
    ──────────────────────────────────────────────────────────── */
    function openModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.pgm-modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal(overlay.id);
        });
    });

    document.querySelectorAll('[data-pgm-close]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(this.dataset.pgmClose); });
    });

    /* ────────────────────────────────────────────────────────────
       HELPER: Field Error (422 dari server)
    ──────────────────────────────────────────────────────────── */
    function clearFieldErrors(formEl) {
        formEl.querySelectorAll('.pgm-input, .pgm-textarea, .pgm-select').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        formEl.querySelectorAll('.pgm-field-error').forEach(function (el) {
            el.textContent = '';
            el.classList.remove('show');
        });
    }

    function applyServerErrors(formEl, errors) {
        Object.keys(errors).forEach(function (field) {
            var input = formEl.querySelector('[name="' + field + '"]');
            if (!input) return;
            input.classList.add('is-invalid');
            var group = input.closest('.pgm-form-group');
            var errEl = group ? group.querySelector('.pgm-field-error') : null;
            if (errEl) { errEl.textContent = errors[field][0]; errEl.classList.add('show'); }
        });
    }

    /* ────────────────────────────────────────────────────────────
       HELPER: Char Counter
    ──────────────────────────────────────────────────────────── */
    function attachCounter(inputEl, max) {
        var counter = document.getElementById(inputEl.id + 'Counter');
        if (!counter) return;
        function update() {
            var len = inputEl.value.length;
            counter.textContent = len + ' / ' + max;
            counter.classList.toggle('warn',  len >= max * 0.8 && len < max);
            counter.classList.toggle('limit', len >= max);
        }
        inputEl.addEventListener('input', update);
        update();
    }

    /* ────────────────────────────────────────────────────────────
       HELPER: Blokir karakter berbahaya
    ──────────────────────────────────────────────────────────── */
    function attachBlockedCharFilter(inputEl) {
        if (!inputEl) return;
        inputEl.addEventListener('input', function () {
            if (BLOCKED_CHARS.test(this.value)) {
                this.value = this.value.replace(BLOCKED_CHARS, '');
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'warning',
                    title: 'Karakter tidak diizinkan dihapus otomatis.',
                    showConfirmButton: false, timer: 2000,
                });
            }
        });
    }

    /* ────────────────────────────────────────────────────────────
       HELPER: Fetch FormData (POST selalu, PUT via _method spoofing)
    ──────────────────────────────────────────────────────────── */
    async function fetchForm(url, formData) {
        var response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData,
        });
        var data = await response.json();
        return { status: response.status, ok: response.ok, data: data };
    }

    /* ────────────────────────────────────────────────────────────
       HELPER: Loading state tombol submit
    ──────────────────────────────────────────────────────────── */
    function setLoading(btn, loading, label) {
        btn.disabled  = loading;
        btn.innerHTML = loading
            ? '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...'
            : '<i class="fa-solid fa-floppy-disk"></i> ' + (label || 'Simpan');
    }

    /* ────────────────────────────────────────────────────────────
       INISIALISASI: Counter & filter — form Tambah
    ──────────────────────────────────────────────────────────── */
    var _tambahMap = { tambahJudul: 'judul', tambahKonten: 'konten', tambahBadgeLabel: 'badge_label' };
    Object.keys(_tambahMap).forEach(function (id) {
        var el = document.getElementById(id);
        if (el) { attachCounter(el, LIMIT[_tambahMap[id]]); attachBlockedCharFilter(el); }
    });

    /* ────────────────────────────────────────────────────────────
       INISIALISASI: Counter & filter — form Edit
    ──────────────────────────────────────────────────────────── */
    var _editMap = { editJudul: 'judul', editKonten: 'konten', editBadgeLabel: 'badge_label' };
    Object.keys(_editMap).forEach(function (id) {
        var el = document.getElementById(id);
        if (el) { attachCounter(el, LIMIT[_editMap[id]]); attachBlockedCharFilter(el); }
    });

    /* ════════════════════════════════════════════════════════════
       MODAL TAMBAH
    ════════════════════════════════════════════════════════════ */
    var formTambah = document.getElementById('pgmFormTambah');
    var btnSimpan  = document.getElementById('pgmBtnSimpan');

    document.getElementById('pgmBtnTambah')?.addEventListener('click', function () {
        formTambah.reset();
        clearFieldErrors(formTambah);
        openModal('pgmModalTambah');
    });

    formTambah?.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (btnSimpan.disabled) return;

        clearFieldErrors(formTambah);
        setLoading(btnSimpan, true, 'Simpan');

        try {
            var res = await fetchForm(formTambah.dataset.action, new FormData(formTambah));

            if (res.status === 201 && res.data.success) {
                closeModal('pgmModalTambah');
                formTambah.reset();

                // Tampilkan sukses → refresh tabel via SPA (sama persis manage_users)
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.data.message,
                    timer: 1500,
                    showConfirmButton: false,
                });

                if (typeof loadPage === 'function') loadPage('pengumuman');

            } else if (res.status === 422) {
                applyServerErrors(formTambah, res.data.errors || {});
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Valid',
                    text: 'Periksa kembali kolom yang ditandai merah.',
                    confirmButtonColor: '#6366f1',
                });
            } else {
                throw new Error(res.data.message || 'Terjadi kesalahan.');
            }

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: err.message,
                confirmButtonColor: '#ef4444',
            });
        } finally {
            setLoading(btnSimpan, false, 'Simpan');
        }
    });

    /* ════════════════════════════════════════════════════════════
       MODAL EDIT — buka & isi field
    ════════════════════════════════════════════════════════════ */
    var formEdit  = document.getElementById('pgmFormEdit');
    var btnUpdate = document.getElementById('pgmBtnUpdate');

    document.querySelectorAll('.pgm-btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            clearFieldErrors(formEdit);
            formEdit.dataset.action = this.dataset.url;

            document.getElementById('editJudul').value      = this.dataset.judul;
            document.getElementById('editKonten').value     = this.dataset.konten;
            document.getElementById('editBadgeLabel').value = this.dataset.badgeLabel;
            document.getElementById('editBadgeColor').value = this.dataset.badgeColor;
            document.getElementById('editIsActive').checked = this.dataset.isActive === '1';

            var img = document.getElementById('editGambarImg');
            img.src           = this.dataset.gambarUrl || '';
            img.style.display = this.dataset.gambarUrl ? 'block' : 'none';

            // Trigger counter agar hitungan langsung benar saat modal terbuka
            Object.keys(_editMap).forEach(function (id) {
                document.getElementById(id)?.dispatchEvent(new Event('input'));
            });

            openModal('pgmModalEdit');
        });
    });

    formEdit?.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (btnUpdate.disabled) return;

        clearFieldErrors(formEdit);
        setLoading(btnUpdate, true, 'Simpan Perubahan');

        try {
            var fd = new FormData(formEdit);
            fd.append('_method', 'PUT');             // Laravel method spoofing

            var res = await fetchForm(formEdit.dataset.action, fd);

            if (res.ok && res.data.success) {
                closeModal('pgmModalEdit');

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.data.message,
                    timer: 1500,
                    showConfirmButton: false,
                });

                if (typeof loadPage === 'function') loadPage('pengumuman');

            } else if (res.status === 422) {
                applyServerErrors(formEdit, res.data.errors || {});
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Valid',
                    text: 'Periksa kembali kolom yang ditandai merah.',
                    confirmButtonColor: '#6366f1',
                });
            } else {
                throw new Error(res.data.message || 'Terjadi kesalahan.');
            }

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memperbarui',
                text: err.message,
                confirmButtonColor: '#ef4444',
            });
        } finally {
            setLoading(btnUpdate, false, 'Simpan Perubahan');
        }
    });

    /* ════════════════════════════════════════════════════════════
       TOGGLE STATUS
       Aksi ringan (tidak mengubah data utama) → tetap toast,
       update badge langsung di DOM tanpa reload halaman.
    ════════════════════════════════════════════════════════════ */
    document.querySelectorAll('.pgm-btn-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url   = this.dataset.url;
            var btnEl = this;

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message);

                // Update badge di DOM langsung — tidak perlu reload tabel
                btnEl.dataset.active = data.is_active ? '1' : '0';
                btnEl.innerHTML      = data.is_active
                    ? '<span class="pgm-badge-aktif">Aktif</span>'
                    : '<span class="pgm-badge-nonaktif">Non-aktif</span>';

                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: data.message,
                    showConfirmButton: false, timer: 2000, timerProgressBar: true,
                });
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Gagal mengubah status.', confirmButtonColor: '#ef4444' });
            });
        });
    });

    /* ════════════════════════════════════════════════════════════
       HAPUS — konfirmasi tengah layar → reload via loadPage
    ════════════════════════════════════════════════════════════ */
    document.querySelectorAll('.pgm-btn-hapus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url   = this.dataset.url;
            var judul = this.dataset.judul;

            Swal.fire({
                title: 'Hapus Pengumuman?',
                html : 'Pengumuman <b>' + judul + '</b> akan dihapus permanen.',
                icon : 'warning',
                showCancelButton  : true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor : '#9ca3af',
                confirmButtonText : '<i class="fa-solid fa-trash-can"></i> Ya, Hapus!',
                cancelButtonText  : 'Batal',
                reverseButtons    : true,
            }).then(function (result) {
                if (!result.isConfirmed) return;

                fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) throw new Error(data.message);

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(function () {
                        if (typeof loadPage === 'function') loadPage('pengumuman');
                    });
                })
                .catch(function () {
                    Swal.fire({ icon: 'error', title: 'Gagal menghapus.', confirmButtonColor: '#ef4444' });
                });
            });
        });
    });

})();
