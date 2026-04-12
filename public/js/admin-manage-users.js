(function () {
    'use strict';

    /* ── CSRF ────────────────────────────────────────────────────────── */
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /* ════════════════════════════════════════════════════════════════════
       HELPERS
       ════════════════════════════════════════════════════════════════════ */

    function openModal(id) {
        var el = document.getElementById(id);
        if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
    }

    function showErr(input, show) {
        var errEl = input.parentElement.querySelector('.err-msg')
            || input.closest('.form-group-admin')?.querySelector('.err-msg')
            || input.closest('.input-pw-wrap')?.parentElement?.querySelector('.err-msg');
        if (!errEl) return;
        if (show) {
            input.classList.add('input-error');
            errEl.style.display = 'block';
        } else {
            input.classList.remove('input-error');
            errEl.style.display = 'none';
        }
    }

    function clearFormErrors(form) {
        form.querySelectorAll('.input-error').forEach(function (el) { el.classList.remove('input-error'); });
        form.querySelectorAll('.err-msg').forEach(function (el) { el.style.display = 'none'; });
    }

    /* ════════════════════════════════════════════════════════════════════
       VALIDASI FIELD
       ════════════════════════════════════════════════════════════════════ */

    /**
     * Validasi satu input. Return true jika valid.
     * @param {HTMLInputElement} input
     */
    function validateField(input) {
        var val = input.value.trim();
        var valid = true;
        var cls = input.className;

        if (cls.includes('val-nama')) {
            valid = val.length >= 3 && val.length <= 50;

        } else if (cls.includes('val-nip')) {
            valid = /^\d{18}$/.test(val);

        } else if (cls.includes('val-email')) {
            valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) && val.length <= 50;

        } else if (cls.includes('val-pw') && !cls.includes('val-pw-conf')) {
            /* Password wajib (form tambah) */
            valid = val.length >= 6 && val.length <= 18;

        } else if (cls.includes('val-pw-conf')) {
            /* Konfirmasi password wajib (form tambah) */
            var matchId = input.dataset.match;
            var matchVal = matchId ? document.getElementById(matchId).value : '';
            valid = val.length >= 6 && val === matchVal;
        }

        showErr(input, !valid);
        return valid;
    }

    /* ════════════════════════════════════════════════════════════════════
       SNAPSHOT — Deteksi "Tidak Ada Perubahan"
       ════════════════════════════════════════════════════════════════════ */

    /**
     * Membuat snapshot data awal form edit sebagai objek JSON.
     * Hanya field yang relevan (bukan password) yang di-snapshot.
     */
    function buildSnapshot(data) {
        return {
            name: data.name || '',
            nip: data.nip || '',
            email: data.email || '',
            bidang: String(data.bidang || ''),
            role: data.role || 'user',
        };
    }

    /**
     * Baca snapshot saat ini dari form.
     */
    function readCurrentFormValues() {
        return {
            name: (document.getElementById('editName')?.value || '').trim(),
            nip: (document.getElementById('editNip')?.value || '').trim(),
            email: (document.getElementById('editEmail')?.value || '').trim(),
            bidang: (document.getElementById('editBidang')?.value || '').trim(),
            role: (document.getElementById('editRole')?.value || '').trim(),
        };
    }

    /**
     * Cek apakah ada perubahan dibanding snapshot awal.
     * Password dianggap perubahan jika diisi (berapapun karakternya).
     * Return true jika ADA perubahan.
     */
    function hasChanges() {
        var snapshotEl = document.getElementById('editSnapshot');
        if (!snapshotEl || !snapshotEl.value) return true; // tidak ada snapshot → anggap ada perubahan

        var original = JSON.parse(snapshotEl.value);
        var current = readCurrentFormValues();

        /* Cek field non-password */
        for (var key in original) {
            if (original[key] !== current[key]) return true;
        }

        /* Password tidak ada di form edit — tidak perlu dicek di sini */

        return false;
    }

    /**
     * Tampilkan/sembunyikan hint "tidak ada perubahan".
     */
    function updateNoChangeHint() {
        var hint = document.getElementById('noChangeHint');
        if (!hint) return;
        hint.style.display = hasChanges() ? 'none' : 'flex';
    }

    /* ════════════════════════════════════════════════════════════════════
       TOGGLE PASSWORD VISIBILITY
       ════════════════════════════════════════════════════════════════════ */

    /* Delegasi: satu listener untuk semua tombol toggle di halaman */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-toggle-pw');
        if (!btn) return;

        var targetId = btn.dataset.target;
        var input = targetId ? document.getElementById(targetId) : null;
        if (!input) return;

        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'fa-regular fa-eye';
        }
    });

    /* ════════════════════════════════════════════════════════════════════
       MODAL: BUKA & TUTUP
       ════════════════════════════════════════════════════════════════════ */

    /* Tombol tutup (data-close) */
    document.querySelectorAll('[data-close]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(this.dataset.close); });
    });

    /* Klik overlay luar modal → tutup */
    ['modalTambahUser', 'modalEditUser'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', function (e) {
                if (e.target === el) closeModal(id);
            });
        }
    });

    /* ── Buka Modal TAMBAH ──────────────────────────────────────────── */
    document.getElementById('btnTambahUser')?.addEventListener('click', function () {
        var form = document.getElementById('formTambahUser');
        form.reset();
        clearFormErrors(form);
        document.getElementById('addBidangBaru').style.display = 'none';

        /*
         * form.reset() menghapus semua value termasuk value="123456" yang
         * sudah ditulis di HTML. Restore manual setelah reset agar password
         * default tetap terisi saat modal dibuka.
         */
        document.getElementById('pwAdd').value = '123456';
        document.getElementById('pwAddConf').value = '123456';

        openModal('modalTambahUser');
    });

    /* ── Buka Modal EDIT ────────────────────────────────────────────── */
    document.querySelectorAll('.btn-edit-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById('formEditUser');

            /* Set action URL */
            form.action = '/admin/manage_users/' + this.dataset.id;

            /* Reset form dan error terlebih dahulu */
            form.reset();
            clearFormErrors(form);
            document.getElementById('noChangeHint').style.display = 'none';
            document.getElementById('editBidangBaru').style.display = 'none';

            /* ✅ Populate semua field dengan data dari dataset */
            document.getElementById('editName').value = this.dataset.name || '';
            document.getElementById('editNip').value = this.dataset.nip || '';
            document.getElementById('editEmail').value = this.dataset.email || '';
            document.getElementById('editRole').value = this.dataset.role || 'user';

            /* Bidang */
            var bidangSel = document.getElementById('editBidang');
            bidangSel.value = this.dataset.bidang || '';

            /* Password tidak ada di form edit — hanya bisa direset via tombol 🔑 */

            /* Simpan snapshot data awal untuk deteksi perubahan */
            var snapshot = buildSnapshot({
                name: this.dataset.name,
                nip: this.dataset.nip,
                email: this.dataset.email,
                bidang: this.dataset.bidang,
                role: this.dataset.role,
            });
            document.getElementById('editSnapshot').value = JSON.stringify(snapshot);

            openModal('modalEditUser');
        });
    });

    /* ════════════════════════════════════════════════════════════════════
       BIDANG BARU: tampil/sembunyikan input dinamis
       ════════════════════════════════════════════════════════════════════ */
    document.querySelectorAll('.select-bidang').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var targetId = this.dataset.target;
            var inputContainer = targetId ? document.getElementById(targetId) : null;
            if (!inputContainer) return;

            var inputField = inputContainer.querySelector('input');
            if (this.value === 'baru') {
                inputContainer.style.display = 'block';
                inputField?.setAttribute('required', 'required');
            } else {
                inputContainer.style.display = 'none';
                inputField?.removeAttribute('required');
                if (inputField) inputField.value = '';
            }
        });
    });

    /* ════════════════════════════════════════════════════════════════════
       VALIDASI REAL-TIME (event delegation — lebih efisien)
       ════════════════════════════════════════════════════════════════════ */
    var validationClasses = [
        'val-nama', 'val-nip', 'val-email',
        'val-pw', 'val-pw-conf',
        /* val-pw-opt dan val-pw-conf-opt dihapus —
           field password tidak lagi ada di form edit */
    ];

    document.addEventListener('input', function (e) {
        var input = e.target;
        if (validationClasses.some(function (c) { return input.classList.contains(c); })) {
            validateField(input);
        }
        /* Jika mengetik di form edit → update hint perubahan */
        if (document.getElementById('formEditUser')?.contains(input)) {
            updateNoChangeHint();
        }
    });

    document.addEventListener('blur', function (e) {
        var input = e.target;
        if (validationClasses.some(function (c) { return input.classList.contains(c); })) {
            validateField(input);
        }
    }, true); /* capture phase untuk blur */

    /* Saat select bidang/role di form edit berubah → update hint */
    ['editBidang', 'editRole'].forEach(function (id) {
        document.getElementById(id)?.addEventListener('change', updateNoChangeHint);
    });

    /* ════════════════════════════════════════════════════════════════════
       SUBMIT FORM (Tambah & Edit)
       ════════════════════════════════════════════════════════════════════ */

    /**
     * Handler submit generik.
     * @param {Event}  e
     * @param {string} formId
     * @param {string} btnId
     * @param {string} modalId
     * @param {boolean} isEdit
     */
    async function handleFormSubmit(e, formId, btnId, modalId, isEdit) {
        e.preventDefault();

        var form = document.getElementById(formId);
        var btn = document.getElementById(btnId);
        if (btn.disabled) return;

        /* ── 1. Cek "tidak ada perubahan" (hanya untuk form edit) ── */
        if (isEdit && !hasChanges()) {
            /* Tampilkan hint dan animasi shake ringan */
            var hint = document.getElementById('noChangeHint');
            hint.style.display = 'flex';
            hint.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            Swal.fire({
                icon: 'info',
                title: 'Tidak Ada Perubahan',
                text: 'Tidak ada data yang diubah. Silakan ubah setidaknya satu field.',
                confirmButtonColor: '#067fb2',
            });
            return;
        }

        /* ── 2. Validasi frontend ── */
        var requiredClasses = isEdit
            ? ['val-nama', 'val-nip', 'val-email']  /* edit: tidak ada field password */
            : ['val-nama', 'val-nip', 'val-email', 'val-pw', 'val-pw-conf'];

        var allValid = true;
        form.querySelectorAll('input, select').forEach(function (inp) {
            if (requiredClasses.some(function (c) { return inp.classList.contains(c); })) {
                if (!validateField(inp)) allValid = false;
            }
        });

        if (!allValid) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Valid',
                text: 'Periksa kembali kolom yang berwarna merah.',
                confirmButtonColor: '#067fb2',
            });
            return;
        }

        /* ── 3. Kirim ke server ── */
        var origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            var response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
            });

            var result = await response.json();

            if (response.ok && result.success) {
                closeModal(modalId);
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false,
                });
                /* Reload konten SPA */
                if (typeof loadPage === 'function') {
                    loadPage('manage_users');
                }
            } else {
                /* Tampilkan error validasi dari Laravel */
                var errorMsg = result.message || 'Terjadi kesalahan.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    html: errorMsg,
                    confirmButtonColor: '#ef4444',
                });
            }
        } catch (err) {
            console.error('[ManageUser] Fetch error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Jaringan',
                text: 'Tidak dapat menghubungi server. Periksa koneksi Anda.',
                confirmButtonColor: '#ef4444',
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }

    /* Pasang listener submit */
    document.getElementById('formTambahUser')?.addEventListener('submit', function (e) {
        handleFormSubmit(e, 'formTambahUser', 'btnSimpanAdd', 'modalTambahUser', false);
    });

    document.getElementById('formEditUser')?.addEventListener('submit', function (e) {
        handleFormSubmit(e, 'formEditUser', 'btnSimpanEdit', 'modalEditUser', true);
    });

    /* ════════════════════════════════════════════════════════════════════
       RESET PASSWORD (AJAX)
       ════════════════════════════════════════════════════════════════════ */
    document.querySelectorAll('.btn-reset-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = this.dataset.url;
            var name = this.dataset.name;

            Swal.fire({
                title: 'Reset Password?',
                html: 'Password akun <b>' + name + '</b> akan direset menjadi <code>123456</code>.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b45309',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: '<i class="fa-solid fa-key"></i> Ya, Reset!',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        Swal.fire({
                            icon: data.success ? 'success' : 'error',
                            title: data.success ? 'Berhasil' : 'Gagal',
                            text: data.message,
                            confirmButtonColor: '#067fb2',
                        });
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                    });
            });
        });
    });

    /* ════════════════════════════════════════════════════════════════════
       HAPUS AKUN (AJAX)
       ════════════════════════════════════════════════════════════════════ */
    document.querySelectorAll('.btn-hapus-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = this.dataset.url;
            var name = this.dataset.name;
            var row = this.closest('tr');

            Swal.fire({
                title: 'Hapus Akun?',
                html: 'Akun <b>' + name + '</b> akan dihapus secara permanen dan tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            /* Hapus baris dari DOM langsung — tidak perlu reload */
                            row?.remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false,
                            });
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(function () {
                        Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                    });
            });
        });
    });

})();
