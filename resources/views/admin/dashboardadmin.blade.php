@extends('layouts.admin')

@section('title', 'Dashboard | SIMPEL DP3A')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/permintaan_admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/verifikasi_admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dokumen_masuk.css') }}?v={{ time() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* CSS Penyelamat agar menu profile dropdown rapi saat di-klik */
        .profile-dropdown a:hover, .profile-dropdown button:hover { background-color: #f4f6f8 !important; }
        .user-info-wrapper { display: flex; align-items: center; }
    </style>
@endpush

@section('content')

<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A">
    </div>

    <div class="header-right">
        @php
            $unreadNotifs = auth()->user()->unreadNotifications ?? collect();
        @endphp

        <div class="notif-wrapper">
            <button class="btn-bell" id="notifButton">
                <i class="fa-regular fa-bell" style="font-size: 20px;"></i>
                @if($unreadNotifs->count() > 0)
                    <span class="notif-badge">{{ $unreadNotifs->count() }}</span>
                @endif
            </button>

            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifikasi</span>
                    @if($unreadNotifs->count() > 0)
                        <a href="#" id="markAllRead" style="font-size: 12px; font-weight:normal;">Tandai semua dibaca</a>
                    @endif
                </div>
                
                <div class="notif-body">
                    @forelse($unreadNotifs as $notif)
                        <a href="#" class="notif-item unread" onclick="loadPage('verifikasi', this)">
                            <div class="notif-icon" style="background: {{ $notif->data['color'] }}; color: {{ $notif->data['text_color'] }};">
                                <i class="fa-solid {{ $notif->data['icon'] }}"></i>
                            </div>
                            <div class="notif-text">
                                <p><b>{{ $notif->data['pengirim'] }}</b> {{ $notif->data['pesan'] }} <b>{{ $notif->data['judul'] }}</b>.</p>
                                <span>{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <div style="padding: 20px; text-align: center; color: #888; font-size: 13px;">
                            <i class="fa-regular fa-bell-slash" style="font-size: 24px; margin-bottom: 8px; color: #ccc;"></i><br>
                            Belum ada notifikasi baru.
                        </div>
                    @endforelse
                </div>
                <div class="notif-footer">
                    <a href="#" onclick="loadPage('verifikasi', this)">Lihat Halaman Verifikasi</a>
                </div>
            </div>
        </div>
        
        <div class="user-info-wrapper" style="position: relative; margin-left: 15px;">
            <div class="user-info" id="userDropdownBtn" style="cursor: pointer; padding: 5px 10px; border-radius: 6px; transition: background 0.2s; display:flex; align-items:center; gap:8px;">
                <div class="user-icon">
                    <img src="{{ asset('images/accicon.png') }}" alt="Akun" style="width:26px; height:26px;">
                </div>
                <span style="font-weight:600; font-size:14px; color:#2c2c2c;">{{ Auth::user()->name ?? 'Admin' }}</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 10px; color:#555;"></i>
            </div>

            <div class="profile-dropdown" id="profileDropdown" style="display: none; position: absolute; right: 0; top: 110%; background: #ffffff; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border-radius: 8px; width: 190px; z-index: 1000; overflow: hidden; border: 1px solid #e0e0e0;">
                
                <a href="#" id="btnOpenUbahPassword" style="display: block; padding: 12px 16px; color: #2c2c2c; text-decoration: none; border-bottom: 1px solid #f0f0f0; font-size:14px;">
                    <i class="fa-solid fa-key" style="margin-right: 8px; color: #067fb2; width: 16px;"></i> Ubah Password
                </a>
                
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="width: 100%; text-align: left; background: none; border: none; padding: 12px 16px; color: #E74A3B; cursor: pointer; font-weight: 600; font-family: inherit; font-size: 14px;">
                        <i class="fa-solid fa-right-from-bracket" style="margin-right: 8px; width: 16px;"></i> Logout
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
<div class="wrapper">
    
    <div class="sidebar" id="sidebar">
        <ul id="menu">
            <li class="active" onclick="loadPage('dashboard', this)">Dashboard</li>
            <li onclick="loadPage('permintaan', this)">Permintaan Dokumen</li>
            <li onclick="loadPage('verifikasi', this)">Verifikasi Dokumen</li>
            <li onclick="loadPage('dokumen-masuk', this)">Dokumen Masuk</li>
            <li onclick="loadPage('evaluasi-pug', this)">Pertanyaan Evaluasi PUG</li>
        </ul>
    </div>

    <div class="content">
        <div id="main-content">
            <h2>Halo, {{ Auth::user()->name ?? 'Admin' }}!</h2>

            <div class="card-container">
                <div class="card">
                    <h4>Permintaan Anda</h4>
                    <span>12</span>
                </div>
                <div class="card">
                    <h4>Total Arsip</h4>
                    <span>58</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalUbahPassword" class="modal-overlay">
    <div class="modal-card" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Ubah Password</h3>
            <button type="button" id="closeModalPassword" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formUbahPassword">
                @csrf
                <div class="modal-grid">
                    <div class="modal-field">
                        <label>Password Saat Ini <span style="color:red">*</span></label>
                        <input type="password" name="current_password" class="modal-input" required placeholder="Masukkan password lama">
                    </div>
                    <div class="modal-field">
                        <label>Password Baru <span style="color:red">*</span></label>
                        <input type="password" name="new_password" class="modal-input" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>
                    <div class="modal-field">
                        <label>Konfirmasi Password Baru <span style="color:red">*</span></label>
                        <input type="password" name="new_password_confirmation" class="modal-input" required minlength="6" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer" style="margin-top: 15px;">
                    <button type="button" id="cancelModalPassword" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function escapeHtml(input) {
        return String(input)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    // ==========================================
    // 1. FUNGSI PERMINTAAN DOKUMEN
    // ==========================================
    function initPermintaanPage() {
        const overlayAdd = document.getElementById('modalOverlay');
        const formAdd = document.getElementById('createPermintaanForm');
        
        document.getElementById('openModal')?.addEventListener('click', () => overlayAdd.classList.add('open'));
        document.getElementById('closeModal')?.addEventListener('click', () => overlayAdd.classList.remove('open'));
        document.getElementById('cancelModal')?.addEventListener('click', () => overlayAdd.classList.remove('open'));

        const dueInput = document.getElementById('due_date');
        if (dueInput) {
            const now = new Date();
            now.setSeconds(now.getSeconds() + 60);
            dueInput.min = now.toISOString().slice(0, 16);
        }

        const overlayEdit = document.getElementById('editModalOverlay');
        const formEdit = document.getElementById('editPermintaanForm');
        document.getElementById('closeEditModal')?.addEventListener('click', () => overlayEdit.classList.remove('open'));
        document.getElementById('cancelEditModal')?.addEventListener('click', () => overlayEdit.classList.remove('open'));

        document.querySelectorAll('.action-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_judul').value = this.dataset.judul;
                document.getElementById('edit_bidang_id').value = this.dataset.bidang_id;
                document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
                document.getElementById('edit_due_date').value = this.dataset.due_date;
                formEdit.action = `/admin/pengajuan/${this.dataset.id}`;
                overlayEdit.classList.add('open');
            });
        });

        const overlayView = document.getElementById('viewModalOverlay');
        document.getElementById('closeViewModal')?.addEventListener('click', () => overlayView.classList.remove('open'));
        document.getElementById('cancelViewModal')?.addEventListener('click', () => overlayView.classList.remove('open'));

        document.querySelectorAll('.action-view').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('view_judul').innerText = `: ${this.dataset.judul}`;
                document.getElementById('view_bidang').innerText = `: ${this.dataset.bidang}`;
                document.getElementById('view_tgl').innerText = `: ${this.dataset.tgl}`;
                document.getElementById('view_deadline').innerText = `: ${this.dataset.deadline}`;
                document.getElementById('view_status').innerHTML = `: <b>${this.dataset.status}</b>`;
                document.getElementById('view_deskripsi').innerText = `: ${this.dataset.deskripsi}`;
                overlayView.classList.add('open');
            });
        });

        document.querySelectorAll('.action-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                Swal.fire({
                    title: 'Apakah Anda yakin?', text: "Data dihapus permanen!", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#858796',
                    confirmButtonText: '<i class="fa-solid fa-trash"></i> Ya, Hapus!', cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/admin/pengajuan/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' }
                        }).then(res => res.json()).then(data => {
                            Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                            loadPage('permintaan'); 
                        }).catch(() => Swal.fire('Error!', 'Terjadi kesalahan.', 'error'));
                    }
                });
            });
        });

        function checkValidation(input) {
        const type = input.dataset.type;
        const value = input.value.trim();
        const errDiv = input.nextElementSibling; // Mengambil div.invalid-feedback di bawah input
        const errText = errDiv.querySelector('span');
        let isValid = true;
        let errorMsg = '';

        // Aturan Validasi
        if (type === 'judul') {
            const regex = /^[a-zA-Z0-9\s\-\/\(\)\,\.]+$/; // Hanya huruf, angka, spasi, dan tanda baca dasar
            if (value.length === 0) {
                isValid = false; errorMsg = 'Nama dokumen wajib diisi.';
            } else if (value.length < 5) {
                isValid = false; errorMsg = 'Terlalu pendek, minimal 5 karakter.';
            } else if (value.length > 50) {
                isValid = false; errorMsg = 'Terlalu panjang, maksimal 50 karakter.';
            } else if (!regex.test(value)) {
                isValid = false; errorMsg = 'Hanya boleh berisi huruf, angka, spasi, dan simbol dasar ( - / ( ) , . )';
            }
        } 
        else if (type === 'bidang') {
            if (value === '') {
                isValid = false; errorMsg = 'Bidang tujuan harus dipilih.';
            }
        } 
        else if (type === 'deskripsi') {
            const regex = /^[^<>{}*^]*$/; // Anti XSS (Mencegah input script < >)
            if (value.length > 250) {
                isValid = false; errorMsg = 'Deskripsi maksimal 250 karakter.';
            } else if (!regex.test(value)) {
                isValid = false; errorMsg = 'Karakter <, >, {, }, *, ^ tidak diizinkan untuk keamanan.';
            }
        } 
        else if (type === 'deadline') {
            if (value === '') {
                isValid = false; errorMsg = 'Batas waktu (Deadline) wajib ditentukan.';
            } else {
                const selectedDate = new Date(value);
                const now = new Date();
                if (selectedDate <= now) {
                    isValid = false; errorMsg = 'Deadline tidak boleh diatur ke waktu yang sudah lewat.';
                }
            }
        }

            if (!isValid) {
                input.classList.add('is-invalid');
                if(errText) errText.innerText = errorMsg;
                if(errDiv) errDiv.classList.add('show');
            } else {
                input.classList.remove('is-invalid');
                if(errDiv) errDiv.classList.remove('show');
            }
            return isValid;
        }

        document.querySelectorAll('.validate-input').forEach(input => {
            input.addEventListener('input', function() { checkValidation(this); });
            input.addEventListener('blur', function() { checkValidation(this); });
        });
        
        async function handleFormSubmit(e, form, modalOverlay) {
            e.preventDefault();
            let formIsValid = true;
            form.querySelectorAll('.validate-input').forEach(input => { if (!checkValidation(input)) formIsValid = false; });
            if (!formIsValid) { Swal.fire({icon: 'warning', title: 'Data Belum Lengkap'}); return; }

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true; submitBtn.innerText = 'Memproses...';

            try {
                const res = await fetch(form.action, {
                    method: 'POST', body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    modalOverlay.classList.remove('open');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', showConfirmButton: false, timer: 1500 });
                    await loadPage('permintaan');
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Periksa inputan Anda.' });
                }
            } finally {
                submitBtn.disabled = false; submitBtn.innerText = 'Submit Permintaan';
            }
        }

        formAdd?.addEventListener('submit', (e) => handleFormSubmit(e, formAdd, overlayAdd));
        formEdit?.addEventListener('submit', (e) => handleFormSubmit(e, formEdit, overlayEdit));
    }

    // ==========================================
    // 2. FUNGSI VERIFIKASI DOKUMEN
    // ==========================================
    function initVerifikasiPage() {
        const modal = document.getElementById("detailModal");
        const editor = document.getElementById("editorDetail");
        const errEditor = document.getElementById("err_editor");
        let activePengajuanId = null;

        function checkEditorValidation() {
            const rawText = editor.innerText.trim();
            const regex = /^[^<>{}*^]*$/;
            let isValid = true; let errorMsg = '';
            if (rawText.length > 1000) { isValid = false; errorMsg = 'Terlalu panjang.'; }
            else if (!regex.test(rawText)) { isValid = false; errorMsg = 'Karakter dilarang.'; }
            
            if (!isValid) {
                editor.style.borderColor = '#dc3545';
                errEditor.querySelector('span').innerText = errorMsg; errEditor.style.display = 'flex';
            } else {
                editor.style.borderColor = '#e0e0e0'; errEditor.style.display = 'none';
            }
            return isValid;
        }

        editor?.addEventListener('input', checkEditorValidation);
        editor?.addEventListener('blur', checkEditorValidation);

        document.querySelectorAll(".action-verifikasi").forEach(btn => {
            btn.addEventListener("click", () => {
                activePengajuanId = btn.dataset.id;
                document.getElementById("verifikasiPengajuanId").value = activePengajuanId;
                document.getElementById("dNama").innerText = btn.dataset.nama;
                document.getElementById("dBidang").innerText = btn.dataset.bidang;
                document.getElementById("dDeadline").innerText = btn.dataset.deadline;
                document.getElementById("dTanggalUpload").innerText = btn.dataset.tanggal_upload;
                
                let statusLabel = btn.dataset.status;
                if(statusLabel === 'approved') statusLabel = '<span style="color:#2D8659">Diterima</span>';
                if(statusLabel === 'rejected') statusLabel = '<span style="color:#E74A3B">Ditolak</span>';
                if(statusLabel === 'pending') statusLabel = '<span style="color:#fbc02d">Menunggu</span>';
                document.getElementById("dStatus").innerHTML = statusLabel;

                editor.innerHTML = btn.dataset.penjelasan || "";
                checkEditorValidation();

                const fileList = document.getElementById("lampiranList");
                fileList.innerHTML = "";
                const files = JSON.parse(btn.dataset.files || '[]');
                if (files.length === 0) {
                    fileList.innerHTML = '<li><span style="color:#888;">Belum ada file.</span></li>';
                } else {
                    files.forEach(f => {
                        fileList.innerHTML += `<li><span><i class="fa-solid fa-file-pdf" style="color:#E74A3B;"></i> ${f.name}</span>
                        <a href="/storage/${f.path}" target="_blank" download style="color: var(--biru-utama); font-weight:bold;">Download</a></li>`;
                    });
                }
                modal.classList.add("open");
            });
        });

        document.getElementById("closeDetail")?.addEventListener('click', () => modal.classList.remove("open"));
        window.addEventListener('click', e => { if (e.target === modal) modal.classList.remove("open"); });

        document.querySelectorAll(".editor-toolbar button").forEach(btn => {
            btn.addEventListener("click", () => { document.execCommand(btn.dataset.cmd, false, null); editor.focus(); });
        });

        async function submitVerifikasi(pengajuanId, status, successMsg) {
            if (!checkEditorValidation()) { Swal.fire('Gagal', 'Periksa catatan.', 'warning'); return; }
            try {
                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                const res = await fetch(`/admin/pengajuan/file/${pengajuanId}/review`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value, 'Accept': 'application/json' },
                    body: JSON.stringify({ status: status, admin_notes: editor.innerHTML })
                });
                if (res.ok) { Swal.fire('Berhasil!', successMsg, 'success'); modal.classList.remove("open"); loadPage('verifikasi'); }
                else { Swal.fire('Gagal', 'Terjadi kesalahan.', 'error'); }
            } catch (err) { Swal.fire('Error', 'Kesalahan server.', 'error'); }
        }

        document.getElementById("btnSaveNotes")?.addEventListener("click", () => submitVerifikasi(activePengajuanId, 'pending', 'Tersimpan.'));
        document.getElementById("btnSendFeedback")?.addEventListener("click", () => submitVerifikasi(activePengajuanId, 'rejected', 'Ditolak & dikirim.'));

        document.querySelectorAll(".action-approve").forEach(btn => {
            btn.addEventListener("click", function() {
                activePengajuanId = this.dataset.id; editor.innerHTML = "";
                submitVerifikasi(activePengajuanId, 'approved', 'DITERIMA!');
            });
        });

        document.querySelectorAll(".action-reject").forEach(btn => {
            btn.addEventListener("click", () => Swal.fire('Tulis Catatan', 'Gunakan ikon mata untuk tolak.', 'info'));
        });
    }

    // ==========================================
    // 3. FUNGSI DOKUMEN MASUK (ARSIP)
    // ==========================================
    function initDokumenMasukPage() {
        const modal = document.getElementById("arsipModal");
        document.querySelectorAll(".action-lihat-arsip").forEach(btn => {
            btn.addEventListener("click", () => {
                document.getElementById("arsipNama").innerText = btn.dataset.nama;
                document.getElementById("arsipBidang").innerText = btn.dataset.bidang;
                document.getElementById("arsipTanggalTerima").innerText = btn.dataset.tanggal_terima;
                document.getElementById("arsipPenjelasan").innerHTML = btn.dataset.penjelasan || "Tidak ada catatan.";
                
                const fileList = document.getElementById("arsipLampiranList");
                fileList.innerHTML = "";
                const files = JSON.parse(btn.dataset.files || '[]');
                files.forEach(f => {
                    fileList.innerHTML += `<li><span><i class="fa-solid fa-file-pdf" style="color:#E74A3B;"></i> ${f.name}</span>
                    <a href="/storage/${f.path}" target="_blank" download style="color: var(--biru-utama); font-weight:bold;">Download</a></li>`;
                });
                modal.classList.add("open");
            });
        });

        document.getElementById("closeArsipModal")?.addEventListener("click", () => modal.classList.remove("open"));
        window.addEventListener("click", e => { if (e.target === modal) modal.classList.remove("open"); });

        const filterBidang = document.getElementById("filterBidang");
        const filterTahun  = document.getElementById("filterTahun");

        function applyFilter() {
            document.querySelectorAll("#dokumenTable tbody tr").forEach(row => {
                if(row.querySelector('td[colspan]')) return;
                const matchBidang = filterBidang.value === "" || row.dataset.bidang.toLowerCase() === filterBidang.value.toLowerCase();
                const matchTahun  = filterTahun.value === "" || row.dataset.tahun === filterTahun.value;
                row.style.display = (matchBidang && matchTahun) ? "" : "none";
            });
        }
        filterBidang?.addEventListener("change", applyFilter);
        filterTahun?.addEventListener("change", applyFilter);

        // LOGIKA EXPORT PDF & EXCEL KE BACKEND SERVER
        function triggerServerExport(exportType) {
            const valBidang = document.getElementById("filterBidang")?.value || "";
            const valTahun = document.getElementById("filterTahun")?.value || "";

            // Bangun URL dengan parameter pencarian (Query String)
            let url = exportType === 'pdf' ? '{{ route("admin.export.pdf") }}' : '{{ route("admin.export.excel") }}';
            url += `?bidang=${encodeURIComponent(valBidang)}&tahun=${encodeURIComponent(valTahun)}`;

            // Tampilkan Notifikasi Loading (Karna server butuh waktu meng-generate PDF)
            Swal.fire({
                title: 'Menyiapkan Dokumen...',
                text: 'Mohon tunggu sebentar, server sedang memproses file Anda.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Redirect layar belakang untuk mengunduh file
            window.location.href = url;

            // Tutup SweetAlert setelah 2 detik (asumsi file mulai terdownload)
            setTimeout(() => { Swal.close(); }, 2000);
        }

        // Hubungkan tombol ke fungsi ekspor
        document.getElementById("btnExportPDF")?.addEventListener("click", () => triggerServerExport('pdf'));
        document.getElementById("btnExportExcel")?.addEventListener("click", () => triggerServerExport('excel'));
    }

    // ==========================================
    // 4. FUNGSI UTAMA LOAD PAGE (AJAX)
    // ==========================================
    function loadPage(page, element = null) {
        if (element) {
            document.querySelectorAll('#menu li').forEach(li => li.classList.remove('active'));
            element.classList.add('active');
        }

        if (page === 'dashboard') { window.location.href = '/admin/dashboard'; return; }

        document.getElementById('main-content').innerHTML = '<div style="padding: 24px; text-align: center;"><i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--biru-utama);"></i><p>Memuat data...</p></div>';

        return fetch('/admin/content/' + page, { credentials: 'same-origin' })
            .then(res => { if (!res.ok) throw new Error('Halaman tidak ditemukan'); return res.text(); })
            .then(html => {
                document.getElementById('main-content').innerHTML = html;
                if (page === 'permintaan') { initPermintaanPage(); } 
                else if (page === 'verifikasi') { initVerifikasiPage(); } 
                else if (page === 'dokumen-masuk') { initDokumenMasukPage(); }
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = '<div class="content"><h2>Terjadi Kesalahan</h2><p>Gagal memuat halaman.</p></div>';
            });
    }

    // ==========================================
    // 5. EVENT LOAD GLOBAL
    // ==========================================
    document.addEventListener('DOMContentLoaded', function () {
        // DROPDOWN PROFIL
        const userDropdownBtn = document.getElementById('userDropdownBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        const modalPw = document.getElementById('modalUbahPassword');
        const formPw = document.getElementById('formUbahPassword');

        userDropdownBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
        });

        window.addEventListener('click', (e) => {
            if (profileDropdown && !userDropdownBtn.contains(e.target)) profileDropdown.style.display = 'none';
        });

        // MODAL PASSWORD
        document.getElementById('btnOpenUbahPassword')?.addEventListener('click', (e) => {
            e.preventDefault(); modalPw.classList.add('open'); profileDropdown.style.display = 'none';
        });
        document.getElementById('closeModalPassword')?.addEventListener('click', () => modalPw.classList.remove('open'));
        document.getElementById('cancelModalPassword')?.addEventListener('click', () => modalPw.classList.remove('open'));

        formPw?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btnSubmit = this.querySelector('button[type="submit"]');
            btnSubmit.disabled = true; btnSubmit.innerText = 'Memproses...';
            try {
                const res = await fetch('{{ route("profile.change-password") }}', {
                    method: 'POST', body: new FormData(this),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    Swal.fire('Berhasil!', 'Password diperbarui.', 'success');
                    modalPw.classList.remove('open'); this.reset();
                } else if (res.status === 422) {
                    const data = await res.json();
                    let errors = Object.values(data.errors).map(err => `<li>${err[0]}</li>`).join('');
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: `<ul style="text-align:left;">${errors}</ul>` });
                } else { Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error'); }
            } catch (err) { Swal.fire('Gagal!', 'Koneksi terputus.', 'error'); } 
            finally { btnSubmit.disabled = false; btnSubmit.innerText = 'Simpan Password'; }
        });

        // NOTIFIKASI
        const notifBtn = document.getElementById('notifButton');
        const notifDropdown = document.getElementById('notifDropdown');
        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation(); notifDropdown.classList.toggle('show');
            });
            window.addEventListener('click', function(e) {
                if (!notifDropdown.contains(e.target)) notifDropdown.classList.remove('show');
            });
        }
    });
</script>
@endpush