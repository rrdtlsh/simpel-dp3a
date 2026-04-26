window.initPengumumanPage = function () {
    'use strict';

    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var LIMIT = { judul: 100, konten: 1000, badge_label: 50 };
    var BLOCKED_CHARS = /[<>"'`\\;{}]/;

    // ── HELPER: Modal ──
    function openModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.add('open');
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('open');
        el.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.pgm-modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(overlay.id); });
    });
    document.querySelectorAll('[data-pgm-close]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(this.dataset.pgmClose); });
    });

    // ── HELPER: UI Feedback ──
    function showFieldError(fieldEl, msg) {
        fieldEl.classList.add('is-invalid');
        var err = fieldEl.parentElement.querySelector('.pgm-field-error');
        if (err) { err.textContent = msg; err.classList.add('show'); }
    }

    function clearFieldErrors(formEl) {
        formEl.querySelectorAll('.pgm-input, .pgm-textarea, .pgm-select').forEach(function (el) { el.classList.remove('is-invalid'); });
        formEl.querySelectorAll('.pgm-field-error').forEach(function (el) { el.textContent = ''; el.classList.remove('show'); });
    }

    function applyServerErrors(formEl, errors) {
        Object.keys(errors).forEach(function (field) {
            var input = formEl.querySelector('[name="' + field + '"]');
            if (input) showFieldError(input, errors[field][0]);
        });
    }

    // ── HELPER: Char Counter & Filter ──
    function attachCounter(inputEl, max) {
        var counterId = inputEl.id + 'Counter';
        var counter = document.getElementById(counterId);
        if (!counter) return;

        function update() {
            var len = inputEl.value.length;
            counter.textContent = len + ' / ' + max;
            counter.classList.remove('warn', 'limit');
            if (len >= max) counter.classList.add('limit');
            else if (len >= max * .8) counter.classList.add('warn');
        }
        inputEl.addEventListener('input', update);
        update();
    }

    function attachBlockedCharFilter(inputEl) {
        inputEl.addEventListener('input', function () {
            if (BLOCKED_CHARS.test(this.value)) {
                this.value = this.value.replace(new RegExp(BLOCKED_CHARS.source, 'g'), '');
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Karakter dilarang dihapus otomatis.', showConfirmButton: false, timer: 2000 });
            }
        });
    }

    function setLoading(btn, loading) {
        btn.disabled = loading;
        btn.innerHTML = loading ? '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...' : (btn.id === 'pgmBtnUpdate' ? 'Simpan Perubahan' : 'Simpan');
    }

    function escHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function fetchForm(url, method, formData) {
        return fetch(url, {
            method: method === 'PUT' ? 'POST' : method,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData,
        }).then(function (r) {
            return r.json().then(function (data) { return { status: r.status, data: data }; });
        });
    }

    // ── INITIALIZE COUNTERS (TAMBAH & EDIT) ──
    ['tambahJudul', 'tambahKonten', 'tambahBadgeLabel', 'editJudul', 'editKonten', 'editBadgeLabel'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            // Tentukan limit berdasarkan tipe (judul, konten, atau badge)
            var key = id.toLowerCase().includes('judul') ? 'judul' : (id.toLowerCase().includes('konten') ? 'konten' : 'badge_label');
            attachCounter(el, LIMIT[key]);
            attachBlockedCharFilter(el);
        }
    });

    // ── TAMBAH PENGUMUMAN ──
    var btnTambah = document.getElementById('pgmBtnTambah');
    var formTambah = document.getElementById('pgmFormTambah');
    var btnSimpan = document.getElementById('pgmBtnSimpan');

    btnTambah?.addEventListener('click', function () {
        formTambah.reset();
        clearFieldErrors(formTambah);
        ['tambahJudul', 'tambahKonten', 'tambahBadgeLabel'].forEach(function (id) {
            document.getElementById(id)?.dispatchEvent(new Event('input'));
        });
        openModal('pgmModalTambah');
    });

    formTambah?.addEventListener('submit', function (e) {
        e.preventDefault();
        clearFieldErrors(formTambah);
        setLoading(btnSimpan, true);

        fetchForm(formTambah.dataset.action, 'POST', new FormData(formTambah))
            .then(function (res) {
                if (res.status === 201 && res.data.success) {
                    closeModal('pgmModalTambah');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.data.message, timer: 1500, showConfirmButton: false });
                    loadPage('pengumuman');
                } else if (res.status === 422) {
                    applyServerErrors(formTambah, res.data.errors || {});
                } else {
                    throw new Error(res.data.message || 'Gagal menyimpan.');
                }
            })
            .catch(function (err) { Swal.fire({ icon: 'error', title: 'Gagal', text: err.message }); })
            .finally(function () { setLoading(btnSimpan, false); });
    });

    // ── EDIT PENGUMUMAN ──
    var formEdit = document.getElementById('pgmFormEdit');
    var btnUpdate = document.getElementById('pgmBtnUpdate');

    function attachEditListener(btn) {
        if (!btn) return;
        btn.addEventListener('click', function () {
            clearFieldErrors(formEdit);
            formEdit.dataset.editId = this.dataset.id;
            formEdit.dataset.action = this.dataset.url;

            // ✅ SIMPAN SNAPSHOT DATA AWAL (Untuk mendeteksi perubahan)
            formEdit.dataset.originalJudul = this.dataset.judul || '';
            formEdit.dataset.originalKonten = this.dataset.konten || '';
            formEdit.dataset.originalBadgeLabel = this.dataset.badgeLabel || '';
            formEdit.dataset.originalBadgeColor = this.dataset.badgeColor || '#067fb2';
            formEdit.dataset.originalIsActive = this.dataset.isActive === '1' ? '1' : '0';

            // Isi ke form
            document.getElementById('editJudul').value = this.dataset.judul;
            document.getElementById('editKonten').value = this.dataset.konten;
            document.getElementById('editBadgeLabel').value = this.dataset.badgeLabel;
            document.getElementById('editBadgeColor').value = this.dataset.badgeColor;
            document.getElementById('editIsActive').checked = this.dataset.isActive === '1';

            var img = document.getElementById('editGambarImg');
            if (this.dataset.gambarUrl) { img.src = this.dataset.gambarUrl; img.style.display = 'block'; }
            else { img.style.display = 'none'; }

            // Kosongkan input file jika sebelumnya ada
            var fileInput = document.getElementById('editGambar');
            if (fileInput) fileInput.value = '';

            ['editJudul', 'editKonten', 'editBadgeLabel'].forEach(function (id) {
                document.getElementById(id)?.dispatchEvent(new Event('input'));
            });

            openModal('pgmModalEdit');
        });
    }
    document.querySelectorAll('.pgm-btn-edit').forEach(attachEditListener);

    formEdit?.addEventListener('submit', function (e) {
        e.preventDefault();

        // ✅ DETEKSI PERUBAHAN
        var currentJudul = document.getElementById('editJudul').value.trim();
        var currentKonten = document.getElementById('editKonten').value.trim();
        var currentBadgeLabel = document.getElementById('editBadgeLabel').value.trim();
        var currentBadgeColor = document.getElementById('editBadgeColor').value.trim();
        var currentIsActive = document.getElementById('editIsActive').checked ? '1' : '0';
        var hasNewImage = document.getElementById('editGambar').files.length > 0;

        if (!hasNewImage &&
            currentJudul === formEdit.dataset.originalJudul &&
            currentKonten === formEdit.dataset.originalKonten &&
            currentBadgeLabel === formEdit.dataset.originalBadgeLabel &&
            currentBadgeColor === formEdit.dataset.originalBadgeColor &&
            currentIsActive === formEdit.dataset.originalIsActive) {

            Swal.fire({
                icon: 'info',
                title: 'Tidak Ada Perubahan',
                text: 'Anda belum mengubah data apapun pada pengumuman ini.',
                confirmButtonColor: '#067fb2'
            });
            return; // Hentikan proses simpan
        }

        clearFieldErrors(formEdit);
        setLoading(btnUpdate, true);

        var fd = new FormData(formEdit);
        fd.append('_method', 'PUT');

        fetchForm(formEdit.dataset.action, 'PUT', fd)
            .then(function (res) {
                if (res.data.success) {
                    closeModal('pgmModalEdit');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.data.message, timer: 1500, showConfirmButton: false });
                    loadPage('pengumuman');
                } else if (res.status === 422) {
                    if (res.data.message && !res.data.errors) {
                        Swal.fire('Info', res.data.message, 'info'); // Tangkap error jika backend menolak karena tdk ada perubahan
                    } else {
                        applyServerErrors(formEdit, res.data.errors || {});
                    }
                }
            })
            .catch(function (err) { Swal.fire({ icon: 'error', title: 'Gagal', text: err.message }); })
            .finally(function () { setLoading(btnUpdate, false); });
    });

    // ── TOGGLE STATUS ──
    function attachToggleListener(btn) {
        if (!btn) return;
        btn.addEventListener('click', function () {
            var url = this.dataset.url;
            var btnEl = this;
            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) throw new Error(data.message);
                    btnEl.dataset.active = data.is_active ? '1' : '0';
                    btnEl.innerHTML = data.is_active ? '<span class="pgm-badge-aktif">Aktif</span>' : '<span class="pgm-badge-nonaktif">Non-aktif</span>';
                    Swal.fire({ icon: 'success', title: 'Status Diperbarui', text: data.message, timer: 1500, showConfirmButton: false });
                })
                .catch(function () { Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan sistem.' }); });
        });
    }
    document.querySelectorAll('.pgm-btn-toggle').forEach(attachToggleListener);

    // ── HAPUS PENGUMUMAN ──
    function attachHapusListener(btn) {
        if (!btn) return;
        btn.addEventListener('click', function () {
            var url = this.dataset.url;
            var judul = this.dataset.judul;

            Swal.fire({
                title: 'Hapus Pengumuman?',
                html: 'Pengumuman <b>' + escHtml(judul) + '</b> akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, timer: 1500, showConfirmButton: false });
                            loadPage('pengumuman');
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        }
                    })
                    .catch(function () { Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghapus data.' }); });
            });
        });
    }
    document.querySelectorAll('.pgm-btn-hapus').forEach(attachHapusListener);

};