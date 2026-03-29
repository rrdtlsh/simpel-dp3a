{{--
==========================================================================
  FILE: resources/views/admin/partials/scripts.blade.php
  
  FIX 3 BUG SEKALIGUS:
  
  BUG 1 + 2 — Persist halaman & Dashboard via SPA (tidak full reload):
    - Setiap loadPage() menyimpan nama page ke sessionStorage
    - DOMContentLoaded membaca sessionStorage dan restore halaman terakhir
    - Klik menu "Dashboard" tidak lagi pakai window.location.href,
      melainkan render konten dashboard default di dalam #main-content
      (sama seperti halaman lain) → tidak ada splash screen

  BUG 3 — Hamburger:
    - Event delegation di document.body sudah benar, tapi CSS class
      yang di-toggle harus konsisten. Tambahkan CSS fallback langsung
      di sini agar pasti berfungsi tanpa bergantung pada dashboardadmin.css
==========================================================================
--}}
<style>
/*
 * ✅ FIX BUG 3 — Hamburger CSS
 * Pastikan class .hide benar-benar menyembunyikan sidebar.
 * Jika dashboardadmin.css sudah ada aturan ini, tidak akan konflik.
 */
#sidebar.hide {
    display: none !important;
}

/* Transisi halus saat sidebar muncul/sembunyi */
#sidebar {
    transition: transform 0.25s ease, opacity 0.25s ease;
}
</style>

<script>
/* ═══════════════════════════════════════════════════════════════════════
   UTILITY
   ═══════════════════════════════════════════════════════════════════════ */
function getCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) console.error('[CSRF] meta[name="csrf-token"] tidak ditemukan!');
    return meta ? meta.getAttribute('content') : '';
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

window.togglePw = function(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text';     icon.className = 'fa-regular fa-eye-slash'; }
    else                           { input.type = 'password'; icon.className = 'fa-regular fa-eye'; }
};

function getFilesFromScript(prefix, id) {
    var el = document.getElementById(prefix + id);
    if (!el) return [];
    try   { return JSON.parse(el.textContent) || []; }
    catch (e) { console.warn('[getFilesFromScript] parse error', prefix + id, e); return []; }
}

function renderFileList(ulElement, files) {
    ulElement.innerHTML = '';
    if (!files || !files.length) {
        ulElement.innerHTML = '<li><span style="color:#888;">Belum ada file yang diunggah.</span></li>';
        return;
    }
    files.forEach(function(f) {
        var name = f.original_name || f.name || 'File';
        var path = f.path || '';
        var ext  = name.split('.').pop().toLowerCase();
        var ic   = ext === 'pdf' ? '#E74A3B' : (ext === 'doc' || ext === 'docx') ? '#2563eb' : '#16a34a';
        ulElement.innerHTML +=
            '<li>'
            + '<span><i class="fa-solid fa-file" style="color:'+ic+';margin-right:5px;"></i>'
            + escapeHtml(name) + '</span>'
            + '<a href="/storage/'+escapeHtml(path)+'" target="_blank" download '
            + 'style="color:#067fb2;font-weight:bold;">'
            + '<i class="fa-solid fa-download"></i> Download</a>'
            + '</li>';
    });
}

function setPwFieldError(fieldName, msg) {
    var inp = document.querySelector('#formUbahPassword [name="' + fieldName + '"]');
    var err = document.getElementById('err_' + fieldName);
    if (inp) inp.classList.add('pw-field-error');
    if (err) { err.textContent = msg; err.style.display = 'block'; }
}
function clearPwErrors() {
    ['current_password','new_password','new_password_confirmation'].forEach(function(n) {
        var inp = document.querySelector('#formUbahPassword [name="' + n + '"]');
        var err = document.getElementById('err_' + n);
        if (inp) inp.classList.remove('pw-field-error');
        if (err) { err.textContent = ''; err.style.display = 'none'; }
    });
}


/* ═══════════════════════════════════════════════════════════════════════
   1. PERMINTAAN DOKUMEN
   ═══════════════════════════════════════════════════════════════════════ */
function initPermintaanPage() {
    var overlayAdd  = document.getElementById('modalOverlay');
    var formAdd     = document.getElementById('createPermintaanForm');
    var overlayEdit = document.getElementById('editModalOverlay');
    var formEdit    = document.getElementById('editPermintaanForm');
    var overlayView = document.getElementById('viewModalOverlay');

    document.getElementById('openModal')?.addEventListener('click',   () => overlayAdd.classList.add('open'));
    document.getElementById('closeModal')?.addEventListener('click',  () => overlayAdd.classList.remove('open'));
    document.getElementById('cancelModal')?.addEventListener('click', () => overlayAdd.classList.remove('open'));

    var dueInput = document.getElementById('due_date');
    if (dueInput) {
        var now = new Date(); now.setSeconds(now.getSeconds() + 60);
        dueInput.min = now.toISOString().slice(0, 16);
    }

    document.getElementById('closeEditModal')?.addEventListener('click',  () => overlayEdit.classList.remove('open'));
    document.getElementById('cancelEditModal')?.addEventListener('click', () => overlayEdit.classList.remove('open'));
    document.querySelectorAll('.action-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_judul').value      = this.dataset.judul;
            document.getElementById('edit_bidang_id').value  = this.dataset.bidang_id;
            var tahunEl = document.getElementById('edit_tahun');
            if (tahunEl) tahunEl.value = this.dataset.tahun || '';
            document.getElementById('edit_deskripsi').value  = this.dataset.deskripsi;
            document.getElementById('edit_due_date').value   = this.dataset.due_date;
            formEdit.action = '/admin/pengajuan/' + this.dataset.id;
            overlayEdit.classList.add('open');
        });
    });

    document.getElementById('closeViewModal')?.addEventListener('click',  () => overlayView.classList.remove('open'));
    document.getElementById('cancelViewModal')?.addEventListener('click', () => overlayView.classList.remove('open'));
    document.querySelectorAll('.action-view').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('view_judul').innerText     = ': ' + this.dataset.judul;
            document.getElementById('view_bidang').innerText    = ': ' + this.dataset.bidang;
            document.getElementById('view_tgl').innerText       = ': ' + this.dataset.tgl;
            document.getElementById('view_deadline').innerText  = ': ' + this.dataset.deadline;
            document.getElementById('view_status').innerHTML    = ': <b>' + this.dataset.status + '</b>';
            document.getElementById('view_deskripsi').innerText = ': ' + this.dataset.deskripsi;
            overlayView.classList.add('open');
        });
    });

    document.querySelectorAll('.action-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            Swal.fire({
                title:'Apakah Anda yakin?', text:'Data dihapus permanen!', icon:'warning',
                showCancelButton:true, confirmButtonColor:'#d33', cancelButtonColor:'#858796',
                confirmButtonText:'<i class="fa-solid fa-trash"></i> Ya, Hapus!', cancelButtonText:'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('/admin/pengajuan/' + id, {
                        method:'DELETE',
                        headers:{ 'X-CSRF-TOKEN': getCsrf(), 'Accept':'application/json' }
                    })
                    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function() {
                        Swal.fire('Terhapus!','Data berhasil dihapus.','success');
                        loadPage('permintaan');
                    })
                    .catch(function(err) {
                        console.error('[DELETE]', err);
                        Swal.fire('Error!','Gagal menghapus.','error');
                    });
                }
            });
        });
    });

    function checkValidation(input) {
        const type = input.dataset.type;
        const value = input.value.trim();
        const errDiv = input.nextElementSibling;
        const errText = errDiv ? errDiv.querySelector('span') : null;
        let isValid = true, errorMsg = '';

        if (type === 'judul') {
            const regex = /^[a-zA-Z0-9\s\-\/\(\)\,\.]+$/;
            if (!value)               { isValid=false; errorMsg='Nama dokumen wajib diisi.'; }
            else if (value.length<5)  { isValid=false; errorMsg='Minimal 5 karakter.'; }
            else if (value.length>50) { isValid=false; errorMsg='Maksimal 50 karakter.'; }
            else if (!regex.test(value)) { isValid=false; errorMsg='Karakter tidak valid.'; }
        } else if (type === 'bidang') {
            if (!value) { isValid=false; errorMsg='Bidang tujuan harus dipilih.'; }
        } else if (type === 'tahun') {
            if (!value) { isValid=false; errorMsg='Tahun dokumen harus dipilih.'; }
        } else if (type === 'deskripsi') {
            const regex = /^[^<>{}*^]*$/;
            if (value.length>250) { isValid=false; errorMsg='Deskripsi maksimal 250 karakter.'; }
            else if (!regex.test(value)) { isValid=false; errorMsg='Karakter dilarang.'; }
        } else if (type === 'deadline') {
            if (!value) { isValid=false; errorMsg='Deadline wajib ditentukan.'; }
            else if (new Date(value) <= new Date()) { isValid=false; errorMsg='Waktu sudah lewat.'; }
        }

        if (!isValid) {
            input.classList.add('is-invalid');
            if (errText) errText.innerText = errorMsg;
            if (errDiv)  errDiv.classList.add('show');
        } else {
            input.classList.remove('is-invalid');
            if (errDiv) errDiv.classList.remove('show');
        }
        return isValid;
    }

    document.querySelectorAll('.validate-input').forEach(function(inp) {
        inp.addEventListener('input', function() { checkValidation(this); });
        inp.addEventListener('blur',  function() { checkValidation(this); });
    });

    async function handleFormSubmit(e, form, overlay) {
        e.preventDefault();
        var ok = true;
        form.querySelectorAll('.validate-input').forEach(function(inp) { if (!checkValidation(inp)) ok = false; });
        if (!ok) { Swal.fire({icon:'warning',title:'Data Belum Lengkap'}); return; }
        var btn = form.querySelector('button[type="submit"]'), orig = btn.innerText;
        btn.disabled = true; btn.innerText = 'Memproses...';
        try {
            var res = await fetch(form.action, {
                method:'POST', body:new FormData(form),
                headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
            });
            if (res.ok) {
                overlay.classList.remove('open');
                Swal.fire({icon:'success',title:'Berhasil!',showConfirmButton:false,timer:1500});
                await loadPage('permintaan');
            } else {
                Swal.fire({icon:'error',title:'Gagal',text:'Periksa inputan Anda.'});
            }
        } finally { btn.disabled = false; btn.innerText = orig; }
    }
    formAdd?.addEventListener('submit',  e => handleFormSubmit(e, formAdd,  overlayAdd));
    formEdit?.addEventListener('submit', e => handleFormSubmit(e, formEdit, overlayEdit));
}


/* ═══════════════════════════════════════════════════════════════════════
   2. VERIFIKASI DOKUMEN
   ═══════════════════════════════════════════════════════════════════════ */
function initVerifikasiPage() {
    var modal     = document.getElementById('detailModal');
    var editor    = document.getElementById('editorDetail');
    var errEditor = document.getElementById('err_editor');
    var activePengajuanId = null;
    var activeNama        = '';

    document.getElementById('searchVerifikasiAdmin')?.addEventListener('input', function() {
        var filter = this.value.toLowerCase();
        document.querySelectorAll('#tabelVerifikasiAdmin tbody .row-data').forEach(function(row) {
            row.style.display = (row.dataset.judul || '').includes(filter) ? '' : 'none';
        });
    });

    var counterAdmin = document.getElementById('adminNotesCounter');

    function checkEditorValidation() {
        var plainText = editor ? (editor.innerText || editor.textContent).trim() : '';
        var len = plainText.length;
        var isValid = len <= 500;
        if (counterAdmin) {
            counterAdmin.innerText = len + '/500 karakter';
            counterAdmin.style.color = isValid ? '#6b7280' : '#ef4444';
        }
        if (!isValid) {
            if (editor) { editor.style.borderColor = '#ef4444'; editor.style.boxShadow = '0 0 0 2px rgba(239,68,68,.2)'; }
            if (errEditor) { errEditor.querySelector('span').innerText = 'Catatan melebihi 500 karakter.'; errEditor.style.display = 'flex'; }
        } else {
            if (editor) { editor.style.borderColor = '#e0e0e0'; editor.style.boxShadow = 'none'; }
            if (errEditor) errEditor.style.display = 'none';
        }
        return isValid;
    }

    editor?.addEventListener('input', checkEditorValidation);
    editor?.addEventListener('blur',  checkEditorValidation);

    document.querySelectorAll('.action-verifikasi').forEach(function(btn) {
        btn.addEventListener('click', function() {
            activePengajuanId = this.dataset.id;
            activeNama        = this.dataset.nama || 'dokumen ini';

            document.getElementById('verifikasiPengajuanId').value = activePengajuanId;
            document.getElementById('dNama').innerText          = this.dataset.nama;
            document.getElementById('dBidang').innerText        = this.dataset.bidang;
            document.getElementById('dDeadline').innerText      = this.dataset.deadline;
            document.getElementById('dTanggalUpload').innerText = this.dataset.tanggalUpload || '-';

            var statusMap = {
                approved:'<span style="color:#2D8659;font-weight:700;">✓ Diterima</span>',
                rejected:'<span style="color:#E74A3B;font-weight:700;">✕ Ditolak</span>',
                pending: '<span style="color:#fbc02d;font-weight:700;">⏳ Menunggu Review</span>',
                belum_upload:'<span style="color:#6b7280;font-weight:700;">— Belum Ada File</span>',
            };
            document.getElementById('dStatus').innerHTML = statusMap[this.dataset.status] || this.dataset.status;

            var noteTemplate = document.getElementById('admin-note-' + activePengajuanId);
            if (editor) { editor.innerHTML = noteTemplate ? noteTemplate.innerHTML : ''; checkEditorValidation(); }

            var catatanUser = this.dataset.userNotes || '';
            var uContainer  = document.getElementById('dUserNotesContainer');
            var uText       = document.getElementById('dUserNotesText');
            if (uContainer && uText) {
                uText.innerText         = catatanUser;
                uContainer.style.display = catatanUser.trim() ? 'block' : 'none';
            }

            var fileList = document.getElementById('lampiranList');
            if (fileList) renderFileList(fileList, getFilesFromScript('files-verif-', activePengajuanId));

            if (modal) modal.classList.add('open');
        });
    });

    document.getElementById('closeDetail')?.addEventListener('click', () => modal?.classList.remove('open'));
    window.addEventListener('click', function(e) { if (e.target === modal) modal?.classList.remove('open'); });

    document.querySelectorAll('.editor-toolbar button').forEach(function(btn) {
        btn.addEventListener('click', function() { document.execCommand(this.dataset.cmd, false, null); editor?.focus(); });
    });

    async function submitVerifikasi(pengajuanId, status, successMsg) {
        var modalOpen = modal && modal.classList.contains('open');
        var currentId = document.getElementById('verifikasiPengajuanId')?.value;
        var finalNotes = '';

        if (modalOpen && currentId == pengajuanId) {
            if (!checkEditorValidation()) { Swal.fire('Gagal','Periksa batas maksimal catatan.','warning'); return; }
            finalNotes = editor ? editor.innerHTML : '';
        } else {
            var noteTemplate = document.getElementById('admin-note-' + pengajuanId);
            finalNotes = noteTemplate ? noteTemplate.innerHTML : '';
        }

        Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
        try {
            var res = await fetch('/admin/pengajuan/file/' + pengajuanId + '/review', {
                method:'POST',
                headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':getCsrf(), 'Accept':'application/json' },
                body: JSON.stringify({ status: status, admin_notes: finalNotes })
            });
            if (res.ok) {
                Swal.fire({ icon:'success', title:'Berhasil!', text:successMsg, timer:1800, showConfirmButton:false });
                modal?.classList.remove('open');
                loadPage('verifikasi');
            } else {
                var errBody = await res.json().catch(() => ({}));
                Swal.fire('Gagal', errBody.message || 'Terjadi kesalahan.', 'error');
            }
        } catch(err) { Swal.fire('Error','Koneksi bermasalah.','error'); }
    }

    document.getElementById('btnSaveNotes')?.addEventListener('click', () => submitVerifikasi(activePengajuanId, 'pending', 'Catatan tersimpan.'));

    document.getElementById('btnSendFeedback')?.addEventListener('click', function() {
        var plainText = editor ? editor.textContent.trim() : '';
        if (!plainText) {
            Swal.fire({ icon:'warning', title:'Catatan Kosong', text:'Wajib isi catatan revisi sebelum menolak.' });
            if (editor) editor.focus(); return;
        }
        Swal.fire({
            title:'Tolak Dokumen?',
            html:'Dokumen <b>'+escapeHtml(activeNama)+'</b> akan <b style="color:#E74A3B">ditolak</b>.',
            icon:'warning', showCancelButton:true, confirmButtonColor:'#E74A3B', cancelButtonColor:'#858796',
            confirmButtonText:'<i class="fa-solid fa-xmark"></i> Ya, Tolak!', cancelButtonText:'Batal',
        }).then(function(r) { if (r.isConfirmed) submitVerifikasi(activePengajuanId, 'rejected', 'Ditolak & revisi dikirim.'); });
    });

    document.querySelectorAll('.action-approve').forEach(function(btn) {
        btn.addEventListener('click', function() {
            activePengajuanId = this.dataset.id;
            activeNama        = this.dataset.nama || 'dokumen ini';
            Swal.fire({
                title:'Terima Dokumen?',
                html:'Dokumen <b>'+escapeHtml(activeNama)+'</b> akan <b style="color:#2D8659">diterima</b> dan dipindah ke arsip.',
                icon:'question', showCancelButton:true, confirmButtonColor:'#2D8659', cancelButtonColor:'#858796',
                confirmButtonText:'<i class="fa-solid fa-check"></i> Ya, Terima!', cancelButtonText:'Batal',
            }).then(function(r) { if (r.isConfirmed) submitVerifikasi(activePengajuanId, 'approved', 'Dokumen DITERIMA!'); });
        });
    });

    document.querySelectorAll('.action-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetBtn = document.querySelector('.action-verifikasi[data-id="'+this.dataset.id+'"]');
            if (targetBtn) {
                targetBtn.click();
                setTimeout(function() {
                    if (editor) editor.focus();
                    Swal.fire({ icon:'info', title:'Tulis Catatan Revisi', text:'Tulis alasan, lalu klik "Tolak & Kirim Revisi".', timer:3000, showConfirmButton:false, toast:true, position:'top-end' });
                }, 300);
            }
        });
    });
}


/* ═══════════════════════════════════════════════════════════════════════
   3. DOKUMEN MASUK
   ═══════════════════════════════════════════════════════════════════════ */
function initDokumenMasukPage() {
    var modal = document.getElementById('arsipModal');

    document.querySelectorAll('.action-lihat-arsip').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var rowId = this.dataset.rowId;
            document.getElementById('arsipNama').innerText          = this.dataset.nama;
            document.getElementById('arsipBidang').innerText        = this.dataset.bidang;
            document.getElementById('arsipTanggalTerima').innerText = this.dataset.tanggalTerima || '-';

            var penjelasanEl = document.getElementById('arsipPenjelasan');
            if (penjelasanEl) penjelasanEl.innerHTML = this.dataset.penjelasan || 'Tidak ada catatan.';

            var uNotes = this.dataset.userNotes || '';
            var uContainer = document.getElementById('arsipUserNotesContainer');
            if (uContainer) {
                var uText = document.getElementById('arsipUserNotesText');
                if (uText) uText.innerText = uNotes;
                uContainer.style.display = uNotes.trim() ? 'block' : 'none';
            }

            var fileList = document.getElementById('arsipLampiranList');
            if (fileList) renderFileList(fileList, getFilesFromScript('files-arsip-', rowId));
            if (modal) modal.classList.add('open');
        });
    });

    document.getElementById('closeArsipModal')?.addEventListener('click', () => modal?.classList.remove('open'));
    window.addEventListener('click', function(e) { if (e.target === modal) modal?.classList.remove('open'); });

    var filterBidang = document.getElementById('filterBidang');
    var filterTahun  = document.getElementById('filterTahun');
    var searchArsip  = document.getElementById('searchArsipAdmin');

    function applyFilter() {
        var valSearch = searchArsip ? searchArsip.value.toLowerCase() : '';
        document.querySelectorAll('#dokumenTable tbody .row-data').forEach(function(row) {
            var mb = !filterBidang?.value || row.dataset.bidang?.toLowerCase() === filterBidang.value.toLowerCase();
            var mt = !filterTahun?.value  || row.dataset.tahun === filterTahun.value;
            var ms = !valSearch || (row.dataset.judul && row.dataset.judul.includes(valSearch));
            row.style.display = (mb && mt && ms) ? '' : 'none';
        });
    }
    filterBidang?.addEventListener('change', applyFilter);
    filterTahun?.addEventListener('change',  applyFilter);
    searchArsip?.addEventListener('input',   applyFilter);

    function triggerServerExport(type) {
        var b = document.getElementById('filterBidang')?.value || '';
        var t = document.getElementById('filterTahun')?.value  || '';
        var url = (type === 'pdf' ? '{{ route("admin.export.pdf") }}' : '{{ route("admin.export.excel") }}')
                + '?bidang=' + encodeURIComponent(b) + '&tahun=' + encodeURIComponent(t);
        Swal.fire({ title:'Menyiapkan Dokumen...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
        window.location.href = url;
        setTimeout(function() { Swal.close(); }, 2000);
    }
    document.getElementById('btnExportPDF')?.addEventListener('click',   () => triggerServerExport('pdf'));
    document.getElementById('btnExportExcel')?.addEventListener('click', () => triggerServerExport('excel'));
}


/* ═══════════════════════════════════════════════════════════════════════
   4. LOAD PAGE (SPA)
   
   ✅ FIX BUG 1 + 2:
   - loadPage('dashboard') TIDAK lagi full reload ke /admin/dashboard.
     Sebaliknya, render ulang konten dashboard langsung di #main-content
     menggunakan HTML yang sudah di-render server saat page load pertama.
   - Setiap kali loadPage() dipanggil, nama page disimpan ke sessionStorage.
   - Saat DOMContentLoaded, halaman terakhir di sessionStorage di-restore.
   ═══════════════════════════════════════════════════════════════════════ */

/* Simpan HTML konten dashboard saat pertama kali halaman dimuat.
   Ini dilakukan SEBELUM SPA replace konten apapun. */
var _dashboardHTML = null;

function captureDashboardHTML() {
    var mc = document.getElementById('main-content');
    if (mc && !_dashboardHTML) {
        _dashboardHTML = mc.innerHTML;
    }
}

function renderDashboardContent() {
    var mc = document.getElementById('main-content');
    if (!mc) return;

    /* Restore HTML dashboard yang sudah di-capture */
    if (_dashboardHTML) {
        mc.innerHTML = _dashboardHTML;
    }

    /* Re-render charts (karena DOM baru) */
    var barEl   = document.getElementById('adminChartBar');
    var donutEl = document.getElementById('adminChartDonut');
    if (barEl && donutEl && typeof ApexCharts !== 'undefined') {
        var adminStats = {
            pending  : {{ \App\Models\PengajuanFile::where('status','pending')->count() }},
            approved : {{ \App\Models\PengajuanFile::where('status','approved')->count() }},
            rejected : {{ \App\Models\PengajuanFile::where('status','rejected')->count() }},
            open     : {{ \App\Models\Pengajuan::doesntHave('files')->count() }},
        };
        var total = (adminStats.pending + adminStats.approved + adminStats.rejected + adminStats.open) || 1;
        var pct   = Math.round((adminStats.approved / total) * 100);

        new ApexCharts(barEl, {
            chart: { type:'bar', height:220, toolbar:{show:false}, fontFamily:'Poppins' },
            series: [{ name:'Dokumen', data:[adminStats.open, adminStats.pending, adminStats.rejected, adminStats.approved] }],
            colors: ['#9ca3af','#f59e0b','#ef4444','#22c55e'],
            plotOptions: { bar:{ distributed:true, borderRadius:6, columnWidth:'55%' } },
            dataLabels: { enabled:true, style:{ fontFamily:'Poppins', fontSize:'12px' } },
            xaxis: { categories:['Belum Upload','Menunggu','Ditolak','Diterima'], labels:{style:{fontFamily:'Poppins',fontSize:'11px'}} },
            legend: { show:false }, grid: { borderColor:'#f3f4f6' },
        }).render();

        new ApexCharts(donutEl, {
            chart: { type:'donut', height:220, fontFamily:'Poppins' },
            series: [adminStats.approved, total - adminStats.approved],
            labels: ['Selesai','Belum'], colors: ['#22c55e','#f3f4f6'],
            plotOptions: { pie:{ donut:{ size:'75%', labels:{ show:true,
                total:{ show:true, label:'Ketuntasan', fontFamily:'Poppins', fontSize:'12px', color:'#6b7280',
                    formatter: function() { return pct + '%'; }
                }
            }}}},
            dataLabels: { enabled:false },
            legend: { position:'bottom', fontFamily:'Poppins', fontSize:'12px' },
        }).render();
    }
}

function loadPage(page, element) {
    /* Update active menu */
    if (element) {
        document.querySelectorAll('#menu li').forEach(function(li) { li.classList.remove('active'); });
        element.classList.add('active');
    }

    /*
     * ✅ FIX BUG 2: Dashboard tidak lagi full reload.
     * Render langsung dari HTML yang sudah di-capture, tidak ada splash screen.
     */
    if (page === 'dashboard') {
        /* Simpan ke sessionStorage */
        sessionStorage.setItem('adminLastPage', 'dashboard');

        /* Update active menu item */
        document.querySelectorAll('#menu li').forEach(function(li, idx) {
            li.classList.toggle('active', idx === 0);
        });

        renderDashboardContent();
        return Promise.resolve();
        /* ❌ DIHAPUS: window.location.href = '/admin/dashboard' */
    }

    /* ✅ FIX BUG 1: Simpan halaman terakhir ke sessionStorage */
    sessionStorage.setItem('adminLastPage', page);

    /* Tampilkan loading spinner di area konten (bukan splash screen) */
    document.getElementById('main-content').innerHTML =
        '<div style="padding:40px;text-align:center;">'
        + '<i class="fa-solid fa-spinner fa-spin fa-2x" style="color:#067fb2;"></i>'
        + '<p style="margin-top:12px;color:#6b7280;">Memuat data...</p></div>';

    return fetch('/admin/content/' + page, { credentials: 'same-origin' })
        .then(function(res) { if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
        .then(function(html) {
            document.getElementById('main-content').innerHTML = html;
            if      (page === 'permintaan')    initPermintaanPage();
            else if (page === 'verifikasi')    initVerifikasiPage();
            else if (page === 'dokumen-masuk') initDokumenMasukPage();
        })
        .catch(function(err) {
            console.error('[loadPage]', err);
            document.getElementById('main-content').innerHTML =
                '<div style="padding:24px;"><h3>Gagal Memuat Halaman</h3>'
                + '<p style="color:#888;">Silakan coba lagi atau refresh halaman.</p></div>';
        });
}

function handleLoadVerifikasi(e) {
    e.preventDefault();
    document.getElementById('notifDropdown')?.classList.remove('show');
    var verifikasiMenu = document.querySelector('#menu li:nth-child(3)');
    loadPage('verifikasi', verifikasiMenu);
}

function handleNotifClick(e, el) {
    e.preventDefault();
    var notifId     = el.dataset.notifId;
    var pengajuanId = el.dataset.pengajuanId;
    document.getElementById('notifDropdown')?.classList.remove('show');
    if (notifId) {
        fetch('/notifications/' + notifId + '/read', {
            method:'POST', headers:{ 'X-CSRF-TOKEN': getCsrf(), 'Accept':'application/json' }
        }).then(function() {
            var badge = document.querySelector('.notif-badge');
            var rem   = document.querySelectorAll('.notif-item.unread').length - 1;
            if (badge) { if (rem <= 0) badge.remove(); else badge.textContent = rem; }
            el.classList.remove('unread');
        }).catch(function(err) { console.error('[markRead]', err); });
    }
    var verifikasiMenu = document.querySelector('#menu li:nth-child(3)');
    loadPage('verifikasi', verifikasiMenu).then(function() {
        if (pengajuanId) {
            var targetBtn = document.querySelector('.action-verifikasi[data-id="' + pengajuanId + '"]');
            if (targetBtn) setTimeout(function() { targetBtn.click(); }, 200);
        }
    });
}


/* ═══════════════════════════════════════════════════════════════════════
   5. EVENT DELEGATION (SPA SAFE — satu listener, tidak hilang)
   ═══════════════════════════════════════════════════════════════════════ */
document.body.addEventListener('click', function(e) {

    /* ✅ FIX BUG 3 — HAMBURGER via event delegation
       Elemen hamburger ada di luar #main-content, tapi event delegation
       di document.body memastikan listener ini TIDAK PERNAH hilang
       meskipun SPA me-replace innerHTML */
    if (e.target.closest('#hamburger')) {
        var sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.classList.toggle('hide');
        return;
    }

    /* PROFIL DROPDOWN */
    var profileDropdown = document.getElementById('profileDropdown');
    if (e.target.closest('#userDropdownBtn')) {
        e.preventDefault();
        if (profileDropdown) {
            profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
        }
        return;
    }

    /* NOTIF DROPDOWN */
    var notifDropdown = document.getElementById('notifDropdown');
    if (e.target.closest('#notifButton')) {
        if (notifDropdown) notifDropdown.classList.toggle('show');
        return;
    }

    /* Tutup dropdown jika klik di luar */
    if (profileDropdown && !e.target.closest('#userDropdownBtn') && !e.target.closest('#profileDropdown')) {
        profileDropdown.style.display = 'none';
    }
    if (notifDropdown && !e.target.closest('#notifButton') && !e.target.closest('#notifDropdown')) {
        notifDropdown.classList.remove('show');
    }

    /* BUKA MODAL PASSWORD */
    if (e.target.closest('#btnOpenUbahPassword')) {
        e.preventDefault();
        var modalPw = document.getElementById('modalUbahPassword');
        var formPw  = document.getElementById('formUbahPassword');
        if (modalPw && formPw) {
            clearPwErrors(); formPw.reset();
            modalPw.style.display = 'flex';
            if (profileDropdown) profileDropdown.style.display = 'none';
        }
        return;
    }

    /* TUTUP MODAL PASSWORD */
    var modalPw = document.getElementById('modalUbahPassword');
    if (modalPw && (
        e.target.closest('#closeModalPassword') ||
        e.target.closest('#cancelModalPassword') ||
        e.target === modalPw
    )) {
        modalPw.style.display = 'none';
        var formPw = document.getElementById('formUbahPassword');
        if (formPw) formPw.reset();
        clearPwErrors();
        return;
    }

    /* TANDAI SEMUA NOTIF DIBACA */
    if (e.target.closest('#markAllRead')) {
        e.preventDefault();
        fetch('/notifications/mark-all-read', {
            method:'POST', headers:{ 'X-CSRF-TOKEN': getCsrf(), 'Accept':'application/json' }
        }).then(function() {
            document.querySelectorAll('.notif-item.unread').forEach(function(el) { el.classList.remove('unread'); });
            document.querySelector('.notif-badge')?.remove();
            document.getElementById('markAllRead')?.remove();
        }).catch(function(err) { console.error('[markAllRead]', err); });
        return;
    }
});


/* ═══════════════════════════════════════════════════════════════════════
   6. DOMContentLoaded
   ═══════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {

    /* ✅ Capture HTML dashboard sebelum apapun di-replace */
    captureDashboardHTML();

    /* ✅ FIX BUG 1: Restore halaman terakhir dari sessionStorage */
    var lastPage = sessionStorage.getItem('adminLastPage');

    if (lastPage && lastPage !== 'dashboard') {
        /* Update active menu sesuai halaman terakhir */
        var pageToMenuIndex = { 'permintaan': 1, 'verifikasi': 2, 'dokumen-masuk': 3, 'evaluasi-pug': 4 };
        var menuIndex = pageToMenuIndex[lastPage];
        if (menuIndex !== undefined) {
            var menuItems = document.querySelectorAll('#menu li');
            menuItems.forEach(function(li, i) { li.classList.toggle('active', i === menuIndex); });
        }
        /* Load halaman terakhir tanpa menulis ke sessionStorage lagi */
        document.getElementById('main-content').innerHTML =
            '<div style="padding:40px;text-align:center;">'
            + '<i class="fa-solid fa-spinner fa-spin fa-2x" style="color:#067fb2;"></i>'
            + '<p style="margin-top:12px;color:#6b7280;">Memuat data...</p></div>';

        fetch('/admin/content/' + lastPage, { credentials: 'same-origin' })
            .then(function(res) { if (!res.ok) throw new Error(); return res.text(); })
            .then(function(html) {
                document.getElementById('main-content').innerHTML = html;
                if      (lastPage === 'permintaan')    initPermintaanPage();
                else if (lastPage === 'verifikasi')    initVerifikasiPage();
                else if (lastPage === 'dokumen-masuk') initDokumenMasukPage();
            })
            .catch(function() {
                /* Jika gagal restore, tampilkan dashboard default */
                renderDashboardContent();
            });
    } else {
        /* Default: render dashboard */
        renderDashboardContent();
    }

    /* Form password submit */
    var formPw = document.getElementById('formUbahPassword');
    if (formPw) {
        formPw.addEventListener('submit', async function(e) {
            e.preventDefault(); clearPwErrors();
            var btnSubmit = document.getElementById('btnSimpanPassword');
            var origHtml  = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            try {
                var res = await fetch('{{ route("profile.change-password") }}', {
                    method:'POST', body:new FormData(this),
                    headers:{ 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json', 'X-CSRF-TOKEN':getCsrf() }
                });
                if (res.ok) {
                    document.getElementById('modalUbahPassword').style.display = 'none';
                    formPw.reset();
                    Swal.fire({ icon:'success', title:'Berhasil!', text:'Password berhasil diperbarui.', confirmButtonColor:'#067fb2' });
                } else if (res.status === 422) {
                    var data = await res.json();
                    Object.keys(data.errors).forEach(function(field) { setPwFieldError(field, data.errors[field][0]); });
                } else if (res.status === 419) {
                    Swal.fire('Sesi Berakhir','Halaman perlu di-refresh.','warning').then(function() { window.location.reload(); });
                } else {
                    Swal.fire('Gagal!','Terjadi kesalahan sistem.','error');
                }
            } catch(err) {
                Swal.fire('Gagal!','Koneksi terputus.','error');
            } finally { btnSubmit.disabled = false; btnSubmit.innerHTML = origHtml; }
        });
    }
});


/* ═══════════════════════════════════════════════════════════════════════
   SEARCH & MISC
   ═══════════════════════════════════════════════════════════════════════ */
window.searchTableAdmin = function() {
    var filter = document.getElementById('searchPermintaanAdmin').value.toLowerCase();
    document.querySelectorAll('#tabelPermintaanAdmin tbody .row-data').forEach(function(row) {
        row.style.display = (row.dataset.judul || '').includes(filter) ? '' : 'none';
    });
};

window.confirmLogout = function(e, form) {
    e.preventDefault();
    Swal.fire({
        title:'Yakin ingin keluar?', text:'Sesi Anda akan diakhiri.', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#E74A3B', cancelButtonColor:'#858796',
        confirmButtonText:'<i class="fa-solid fa-right-from-bracket"></i> Ya, Logout', cancelButtonText:'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({ title:'Mengakhiri sesi...', allowOutsideClick:false, didOpen:function(){ Swal.showLoading(); } });
            form.submit();
        }
    });
};

@if(session('success'))
    Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 3000, timerProgressBar: true,
    }).fire({ icon: 'success', title: '{!! session('success') !!}' });
@endif
</script>
