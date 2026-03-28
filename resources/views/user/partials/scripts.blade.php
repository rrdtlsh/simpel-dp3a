<script>
/* ═══════════════════════════════════════════════════════════════════════════
   1. UTILITY GLOBAL (SAMA DENGAN ADMIN)
   ═══════════════════════════════════════════════════════════════════════════ */
function getCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

window.togglePw = function(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text';     icon.className = 'fa-regular fa-eye-slash'; }
    else                           { input.type = 'password'; icon.className = 'fa-regular fa-eye'; }
};

function setPwFieldError(fieldName, msg) {
    var inp = document.querySelector('#formUbahPassword [name="' + fieldName + '"]');
    var err = document.getElementById('err_' + fieldName);
    if (inp) inp.classList.add('pw-field-error');
    if (err) { err.textContent = msg; err.style.display = 'block'; }
}

function clearPwErrors() {
    ['current_password', 'password', 'password_confirmation'].forEach(function(n) {
        var inp = document.querySelector('#formUbahPassword [name="' + n + '"]');
        var err = document.getElementById('err_' + n);
        if (inp) inp.classList.remove('pw-field-error');
        if (err) { err.textContent = ''; err.style.display = 'none'; }
    });
}

/* ═══════════════════════════════════════════════════════════════════════════
   2. HANDLER NOTIFIKASI USER (SAMA DENGAN ADMIN)
   ═══════════════════════════════════════════════════════════════════════════ */
window.handleNotifClickUser = function(e, el) {
    e.preventDefault();
    var notifId     = el.dataset.notifId;
    var targetPage  = el.dataset.page || 'permintaan';
    var pengajuanId = el.dataset.pengajuanId;

    document.getElementById('notifDropdown')?.classList.remove('show');

    var urlMap = {
        'permintaan' : '/user/permintaan',
        'arsip'      : '/user/arsip',
        'dashboard'  : '/user/dashboard',
    };
    var targetUrl = urlMap[targetPage] || '/user/permintaan';
    if (pengajuanId) targetUrl += '?highlight=' + pengajuanId;

    if (notifId) {
        // ✅ Menggunakan getCsrf() seperti Admin
        fetch('/notifications/' + notifId + '/read', {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
        }).then(function() {
            var badge = document.querySelector('.notif-badge');
            var rem   = document.querySelectorAll('.notif-item.unread').length - 1;
            if (badge) { if (rem <= 0) badge.remove(); else badge.textContent = rem; }
            el.classList.remove('unread');
            
            window.location.href = targetUrl;
        }).catch(function(err) { 
            console.error('Error:', err); 
            window.location.href = targetUrl; 
        });
    } else {
        window.location.href = targetUrl;
    }
};

/* ═══════════════════════════════════════════════════════════════════════════
   3. SWEETALERT LOGOUT (SAMA DENGAN ADMIN)
   ═══════════════════════════════════════════════════════════════════════════ */
window.confirmLogout = function(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: "Sesi Anda akan diakhiri.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E74A3B',
        cancelButtonColor: '#858796',
        confirmButtonText: '<i class="fa-solid fa-right-from-bracket"></i> Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title:'Mengakhiri sesi...', allowOutsideClick:false, didOpen:()=>Swal.showLoading()});
            form.submit();
        }
    });
};

/* ═══════════════════════════════════════════════════════════════════════════
   4. DOM CONTENT LOADED (EVENT LISTENERS)
   ═══════════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {

    // === SIDEBAR TOGGLE ===
    var hamburger = document.getElementById('hamburger');
    var sidebar   = document.getElementById('sidebar');
    if (hamburger && sidebar) {
        hamburger.addEventListener('click', function() { sidebar.classList.toggle('hide'); });
    }

    // === PROFIL DROPDOWN ===
    var userDropdownBtn = document.getElementById('userDropdownBtn');
    var profileDropdown = document.getElementById('profileDropdown');
    if (userDropdownBtn) {
        userDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
        });
    }

    // === NOTIFIKASI DROPDOWN ===
    var notifBtn      = document.getElementById('notifButton');
    var notifDropdown = document.getElementById('notifDropdown');
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        });
    }

    // === MENUTUP DROPDOWN SAAT KLIK DI LUAR ===
    window.addEventListener('click', function(e) {
        if (profileDropdown && userDropdownBtn && !userDropdownBtn.contains(e.target)) {
            profileDropdown.style.display = 'none';
        }
        if (notifDropdown && notifBtn && !notifBtn.contains(e.target)) {
            notifDropdown.classList.remove('show');
        }
    });

    // === TANDAI SEMUA NOTIF DIBACA ===
    document.getElementById('markAllRead')?.addEventListener('click', function(e) {
        e.preventDefault();
        fetch('/notifications/mark-all-read', {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
        }).then(function() {
            document.querySelectorAll('.notif-item.unread').forEach(function(el) { el.classList.remove('unread'); });
            document.querySelector('.notif-badge')?.remove();
            document.getElementById('markAllRead')?.remove();
        });
    });

    // === UBAH PASSWORD (SAMA DENGAN ADMIN) ===
    var modalPw   = document.getElementById('modalUbahPassword');
    var formPw    = document.getElementById('formUbahPassword');
    var btnOpenPw = document.getElementById('btnOpenUbahPassword');

    function closePwModal() { modalPw.style.display = 'none'; formPw.reset(); clearPwErrors(); }

    if (btnOpenPw) {
        btnOpenPw.addEventListener('click', function(e) {
            e.preventDefault(); clearPwErrors(); formPw.reset();
            modalPw.style.display = 'flex';
            if (profileDropdown) profileDropdown.style.display = 'none';
        });
    }
    
    document.getElementById('closeModalPassword')?.addEventListener('click',  closePwModal);
    document.getElementById('cancelModalPassword')?.addEventListener('click', closePwModal);
    modalPw?.addEventListener('click', function(e) { if (e.target === modalPw) closePwModal(); });

    formPw?.addEventListener('submit', async function(e) {
        e.preventDefault(); clearPwErrors();
        var btnSubmit    = document.getElementById('btnSimpanPassword');
        var originalHtml = btnSubmit.innerHTML;

        btnSubmit.disabled  = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            var res = await fetch('{{ route("profile.change-password") }}', {
                method  : 'POST',
                body    : new FormData(this),
                headers : {
                    'X-Requested-With' : 'XMLHttpRequest',
                    'Accept'           : 'application/json',
                    'X-CSRF-TOKEN'     : getCsrf(), // ✅ Menggunakan fungsi getCsrf()
                }
            });

            if (res.ok) {
                closePwModal();
                Swal.fire({ icon: 'success', title: 'Password Diperbarui!', text: 'Password berhasil diubah.', confirmButtonColor: '#067fb2' });
            } else if (res.status === 422) {
                var data   = await res.json();
                var errors = data.errors || {};
                
                Object.keys(errors).forEach(function(field) {
                    setPwFieldError(field, errors[field][0]);
                });
                

            } else if (res.status === 419) {
                Swal.fire('Sesi Berakhir', 'Halaman perlu di-refresh.', 'warning').then(() => window.location.reload());
            } else {
                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
            }
        } catch (err) {
            Swal.fire('Gagal', 'Koneksi terputus.', 'error');
        } finally {
            btnSubmit.disabled  = false;
            btnSubmit.innerHTML = originalHtml;
        }
    });

}); // end DOMContentLoaded

/* ═══════════════════════════════════════════════════════════════════════════
   5. TOAST NOTIFICATION SESSION (LOGIN SUCCESS)
   ═══════════════════════════════════════════════════════════════════════════ */
@if(session('success'))
    Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    }).fire({
        icon: 'success',
        title: '{!! session('success') !!}'
    });
@endif

/* ═══════════════════════════════════════════════════════════════════════════
   6. FITUR HALAMAN PERMINTAAN USER (Pencarian & Detail Modal)
   ═══════════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
    
    // ✅ A. Fitur Pencarian (Search)
    const searchInput = document.getElementById('searchPermintaanUser');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tabelPermintaanUser tbody .row-data');
            rows.forEach(row => {
                let judul = row.dataset.judul || '';
                row.style.display = judul.includes(filter) ? '' : 'none';
            });
        });
    }

    // ✅ B. Fitur Klik Tombol "Lihat" (Event Delegation)
    document.body.addEventListener('click', function(e) {
        const btnLihat = e.target.closest('.action-lihat-user');
        if (btnLihat) {
            e.preventDefault();
            let rowId = btnLihat.dataset.id;
            let files = [];
            
            // Mengambil daftar file dari tag script JSON
            try { 
                let jsonText = document.getElementById('files-user-' + rowId)?.textContent;
                if (jsonText) {
                    files = JSON.parse(jsonText); 
                }
            } catch(err) {
                console.error('Gagal memproses JSON files:', err);
            }
            
            // Merakit HTML daftar file lampiran
            let listHtml = files.map(f => {
                let ext = f.name.split('.').pop().toLowerCase();
                let icon = ext === 'pdf' ? '<i class="fa-solid fa-file-pdf" style="color:#E74A3B;"></i>' : '<i class="fa-solid fa-file-word" style="color:#2563eb;"></i>';
                return `<li style="margin-bottom: 8px; display: flex; justify-content: space-between; align-items:center; background: #f9fafb; padding: 10px; border-radius: 6px; border: 1px solid #eee;">
                    <span style="font-size: 13px; color:#374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 70%;">${icon} ${f.name}</span>
                    <a href="/storage/${f.path}" target="_blank" download style="font-size: 12px; font-weight: bold; color: #067fb2; text-decoration: none; flex-shrink: 0;"><i class="fa-solid fa-download"></i> Unduh</a>
                </li>`;
            }).join('');

            // Merakit HTML Catatan Revisi Admin
            let catatanHtml = '';
            let catatanAdmin = btnLihat.dataset.catatan ? btnLihat.dataset.catatan.trim() : '';
            
            if (btnLihat.dataset.status === 'rejected') {
                catatanHtml = `
                    <div style="margin-top: 16px; background: #fee2e2; border-left: 4px solid #dc2626; padding: 12px; border-radius: 4px; text-align: left;">
                        <span style="color: #991b1b; font-weight: bold; font-size: 13px;"><i class="fa-solid fa-triangle-exclamation"></i> Catatan Revisi dari Admin:</span>
                        <div style="margin: 6px 0 0 0; color: #7f1d1d; font-size: 13.5px;">${catatanAdmin || 'Tidak ada pesan khusus.'}</div>
                    </div>`;
            } else if (catatanAdmin !== '') {
                catatanHtml = `
                    <div style="margin-top: 16px; background: #f0fdf4; border-left: 4px solid #16a34a; padding: 12px; border-radius: 4px; text-align: left;">
                        <span style="color: #166534; font-weight: bold; font-size: 13px;"><i class="fa-solid fa-comment-dots"></i> Catatan Admin:</span>
                        <div style="margin: 6px 0 0 0; color: #14532d; font-size: 13.5px;">${catatanAdmin}</div>
                    </div>`;
            }

            // ✅ Merakit HTML Catatan User
            let catatanUser = btnLihat.dataset.userNotes ? btnLihat.dataset.userNotes.trim() : '';
            let userNotesHtml = '';
            if (catatanUser !== '') {
                userNotesHtml = `
                    <div style="margin-top: 12px; background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; border-radius: 4px; text-align: left;">
                        <span style="color: #1d4ed8; font-weight: bold; font-size: 13px;"><i class="fa-solid fa-user-pen"></i> Pesan / Catatan Anda:</span>
                        <div style="margin: 6px 0 0 0; color: #1e3a8a; font-size: 13.5px;">${catatanUser}</div>
                    </div>`;
            }

            // Memunculkan Modal SweetAlert
            Swal.fire({
                title: 'Detail Permintaan',
                html: `
                    <div style="text-align: left; font-family: 'Poppins', sans-serif;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13.5px; margin-bottom: 12px;">
                            <tr><th style="text-align: left; padding: 4px 0; width: 35%;">Nama Dokumen</th><td>: ${btnLihat.dataset.nama}</td></tr>
                            <tr><th style="text-align: left; padding: 4px 0;">Tahun</th><td>: ${btnLihat.dataset.tahun}</td></tr>
                            <tr><th style="text-align: left; padding: 4px 0;">Tanggal Dibuat</th><td>: ${btnLihat.dataset.tgl}</td></tr>
                            <tr><th style="text-align: left; padding: 4px 0; vertical-align: top;">Instruksi</th><td style="vertical-align: top;">: ${btnLihat.dataset.deskripsi}</td></tr>
                        </table>
                        
                        <h4 style="margin: 16px 0 8px; font-size: 13.5px; color:#111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px;">File Anda:</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">${listHtml || '<li style="color:#888; font-size:13px; text-align:center;">Belum ada file.</li>'}</ul>
                        ${userNotesHtml}
                        ${catatanHtml}
                    </div>
                `,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#6b7280',
                width: '520px',
                willOpen: function () {
                    var container = document.querySelector('.swal2-container');
                    if (container) container.style.zIndex = '99999';
                },
            });
        }
    });

});

// Fungsi ini dipanggil dari modal-upload saat sukses upload
window.refreshPengajuanData = function () {
    window.location.reload(); 
};
</script>