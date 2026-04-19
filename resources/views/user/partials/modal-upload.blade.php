{{--
===========================================================================
  FILE: resources/views/user/partials/modal-upload.blade.php
  
  FIX v3 — Dua perbaikan utama:
  1. [BUG SQL]  Response sukses disesuaikan dengan struktur JSON controller
                yang sudah diperbaiki (root-level, bukan nested .data.files)
  2. [BUG UX]   SweetAlert2 selalu tampil di ATAS modal upload.
                Solusi: didOpen() + willOpen() menaikkan z-index container Swal
                agar selalu > z-index modal (#uploadModal = 9050).
===========================================================================
--}}

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- 1. HTML MODAL (tidak berubah dari v2)                               --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div
    id="uploadModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="umModalTitle"
    style="
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9050;
        background: rgba(0,0,0,.55);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    "
>
    <div style="
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 24px 64px rgba(0,0,0,.2), 0 4px 16px rgba(0,0,0,.1);
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    ">

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 22px 14px; border-bottom:1px solid #f0f0f0;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; background:#eef2ff; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#4f46e5; flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <div>
                    <h5 id="umModalTitle" style="margin:0; font-size:1rem; font-weight:700; color:#111827;">Unggah Dokumen</h5>
                    <p style="margin:2px 0 0; font-size:.75rem; color:#9ca3af;">Pengajuan #<span id="umPengajuanId">—</span></p>
                </div>
            </div>
            <button type="button" id="umBtnClose" aria-label="Tutup modal" style="width:32px; height:32px; border:none; border-radius:8px; background:#f3f4f6; color:#6b7280; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div style="padding:18px 22px 22px;">

            {{-- Info badge --}}
            <div style="display:flex; align-items:flex-start; gap:8px; background:#eef2ff; color:#4f46e5; border-radius:8px; padding:10px 14px; font-size:.8rem; line-height:1.5; margin-bottom:16px;">
                <svg style="flex-shrink:0; margin-top:1px;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>Maks. <strong>5 file</strong> &nbsp;·&nbsp; Maks. <strong>8 MB/file</strong> &nbsp;·&nbsp; Format: <strong>PDF, DOC, DOCX, XLS, XLSX</strong></span>
            </div>

            <div id="umAdminInstruction" style="display: none; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 6px; padding: 12px; margin-bottom: 16px;">
                <strong style="color: #991b1b; font-size: 13px;"><i class="fa-solid fa-circle-info"></i> Instruksi / Pesan Admin:</strong>
                <div id="umAdminText" style="margin-top: 4px; font-size: 13px; color: #7f1d1d; word-break: break-word;"></div>
            </div>

            {{-- ✅ INPUT CATATAN USER (Dengan Indikator 500 Karakter) --}}
            <div style="margin-bottom: 16px;">
                <label for="umUserNotes" style="display:block; font-size:.85rem; font-weight:600; margin-bottom:6px; color:#374151;">
                    Pesan / Catatan Balasan <span style="color:#9ca3af; font-weight:normal;">(Opsional)</span>
                </label>
                <textarea id="umUserNotes" maxlength="250" rows="2" placeholder="Ketik keterangan untuk Admin DP3A di sini (Maks. 250 karakter)..." style="width:100%; box-sizing:border-box; padding:10px; border-radius:8px; border:1px solid #d1d5db; font-size:.85rem; font-family:inherit; outline:none; resize:vertical; transition: border-color 0.2s;"></textarea>
                
                {{-- Teks Indikator Karakter --}}
                <div id="umUserNotesCounter" style="font-size: 11px; color: #6b7280; text-align: right; margin-top: 4px; font-weight: 500;">
                    0/250 karakter
                </div>
            </div>

            {{-- Drop zone --}}
            <div id="umDropZone" style="border:2px dashed #d1d5db; border-radius:10px; padding:32px 20px; text-align:center; cursor:pointer; background:#f9fafb; transition:border-color .2s, background .2s; margin-bottom:14px;">
                <div style="color:#d1d5db; margin-bottom:10px;">
                    <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                </div>
                <p style="margin:0 0 12px; font-size:.87rem; color:#6b7280;">Seret &amp; letakkan file di sini, atau</p>
                <label for="umFileInput" style="display:inline-block; padding:8px 20px; background:#4f46e5; color:#fff; border-radius:7px; font-size:.85rem; font-weight:600; cursor:pointer; user-select:none;">
                    Pilih File
                </label>
                <input type="file" id="umFileInput" name="files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx" style="display:none;">
            </div>

            {{-- File list --}}
            <div id="umFileListWrap" style="display:none; margin-bottom:14px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; font-size:.8rem;">
                    <span id="umFileCount" style="color:#374151; font-weight:600;"></span>
                    <button type="button" id="umBtnClearAll" style="background:none; border:none; color:#ef4444; font-size:.78rem; font-weight:600; cursor:pointer; padding:2px 6px; border-radius:4px;">Hapus Semua</button>
                </div>
                <ul id="umFileList" style="list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:6px;"></ul>
            </div>

            {{-- Footer --}}
            <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:14px; border-top:1px solid #f0f0f0;">
                <button type="button" id="umBtnCancel" style="padding:9px 20px; background:#f3f4f6; color:#374151; border:none; border-radius:8px; font-size:.875rem; font-weight:600; cursor:pointer;">Batal</button>
                <button type="button" id="umBtnSubmit" disabled style="display:inline-flex; align-items:center; gap:7px; padding:9px 20px; background:#4f46e5; color:#fff; border:none; border-radius:8px; font-size:.875rem; font-weight:600; cursor:pointer; opacity:.5;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Unggah Sekarang
                </button>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- 2. CSS                                                               --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<style>
/* ── Animasi modal ─────────────────────────────────────────────────── */
@keyframes umFadeIn  { from { opacity:0 } to { opacity:1 } }
@keyframes umSlideUp {
    from { opacity:0; transform:translateY(20px) scale(.97) }
    to   { opacity:1; transform:translateY(0) scale(1) }
}

/* ── Hover states ─────────────────────────────────────────────────── */
#umDropZone:hover, #umDropZone.um-drag-over {
    border-color: #4f46e5 !important;
    background: #eef2ff !important;
}
#umBtnClose:hover  { background:#fee2e2 !important; color:#ef4444 !important; }
#umBtnCancel:hover { background:#e5e7eb !important; }
#umBtnSubmit:not([disabled]) { opacity:1 !important; cursor:pointer !important; }
#umBtnSubmit:not([disabled]):hover { background:#4338ca !important; }

/*
 * ✅ FIX BUG Z-INDEX:
 * SweetAlert2 menggunakan class .swal2-container dengan z-index default 1060.
 * Modal kustom kita pakai z-index 9050 → Swal tertutup di belakang.
 *
 * Solusi: paksa semua .swal2-container naik ke z-index 99999
 * sehingga selalu tampil di atas layer apapun di halaman ini.
 */
.swal2-container {
    z-index: 99999 !important;
}
</style>


{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- 3. JAVASCRIPT                                                        --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<script>
(function (w, d) {
    'use strict';

    /* ── Konstanta ──────────────────────────────────────────────────── */
    var MAX_FILES   = 5;
    var MAX_BYTES   = 8 * 1024 * 1024;
    var ALLOWED_EXT = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    /* ── State ──────────────────────────────────────────────────────── */
    var pengajuanId   = null;
    var selectedFiles = [];

    /* ── Elemen DOM ─────────────────────────────────────────────────── */
    var elModal     = d.getElementById('uploadModal');
    var elIdDisplay = d.getElementById('umPengajuanId');
    var elDropZone  = d.getElementById('umDropZone');
    var elFileInput = d.getElementById('umFileInput');
    var elFileList  = d.getElementById('umFileList');
    var elListWrap  = d.getElementById('umFileListWrap');
    var elFileCount = d.getElementById('umFileCount');
    var elBtnSubmit = d.getElementById('umBtnSubmit');
    var elBtnCancel = d.getElementById('umBtnCancel');
    var elBtnClose  = d.getElementById('umBtnClose');
    var elBtnClear  = d.getElementById('umBtnClearAll');

    /* ── Helper: format bytes ───────────────────────────────────────── */
    function fmtBytes(b) {
        if (b < 1024)    return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        return (b / 1048576).toFixed(2) + ' MB';
    }

    /* ── Helper: ekstensi file ──────────────────────────────────────── */
    function getExt(filename) {
        return filename.split('.').pop().toLowerCase();
    }

    /* ── Helper: CSRF token ─────────────────────────────────────────── */
    function csrfToken() {
        var m = d.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    /*
     * ✅ FIX BUG Z-INDEX (JS side):
     * Fungsi pembantu untuk memastikan setiap Swal.fire() yang kita panggil
     * selalu menyertakan didOpen() yang menaikkan z-index container Swal.
     * Ini sebagai lapisan proteksi kedua selain CSS di atas.
     */
    function swalOnTop() {
        return {
            didOpen: function () {
                // Pastikan spinner loading aktif (jika ada)
                var popup = Swal.getPopup();
                if (popup) {
                    var container = popup.closest('.swal2-container');
                    if (container) container.style.zIndex = '99999';
                }
            }
        };
    }

    /* ── PUBLIC API: buka modal ─────────────────────────────────────── */
    // ✅ Tambah parameter ke-4: oldUserNotes
    w.openUploadModal = function (id, deskripsi, filesDataId, oldUserNotes) {
        pengajuanId = id;
        elIdDisplay.textContent = id;

        // Tampilkan deskripsi admin jika ada
        var elAdminInstruction = d.getElementById('umAdminInstruction');
        var elAdminText        = d.getElementById('umAdminText');
        
        if (elAdminInstruction && elAdminText) {
            if (deskripsi && deskripsi.trim() !== '') {
                elAdminText.innerHTML = deskripsi;
                elAdminInstruction.style.display = 'block';
            } else {
                elAdminInstruction.style.display = 'none';
            }
        }

        _reset(); // Reset form

        // ✅ KEMBALIKAN CATATAN LAMA KE TEXTAREA
        var noteInput = d.getElementById('umUserNotes');
        if (noteInput && oldUserNotes) {
            noteInput.value = oldUserNotes;
            // Pancing event 'input' agar counter karakter langsung ter-update
            noteInput.dispatchEvent(new Event('input')); 
        }

        // MUAT FILE LAMA KE DALAM MODAL
        if (filesDataId) {
            try {
                let jsonText = d.getElementById(filesDataId)?.textContent;
                let oldFiles = JSON.parse(jsonText || '[]');
                if (oldFiles.length > 0) {
                    oldFiles.forEach(function(f) {
                        selectedFiles.push({
                            name: f.name,
                            size: 0,
                            is_old_file: true, 
                            path: f.path
                        });
                    });
                    _render(); 
                }
            } catch (err) {
                console.error("Gagal load file lama", err);
            }
        }

        elModal.style.display = 'flex';
        d.body.style.overflow = 'hidden';
    };

    /* ── PUBLIC API: tutup modal ────────────────────────────────────── */
    w.closeUploadModal = function () {
        elModal.style.display = 'none';
        d.body.style.overflow = '';
        _reset();
    };

    /* ── Reset state ────────────────────────────────────────────────── */
    function _reset() {
        selectedFiles     = [];
        elFileInput.value = '';
        var noteInput     = d.getElementById('umUserNotes');
        if (noteInput) {
            noteInput.value = '';
            noteInput.style.borderColor = '#d1d5db';
            noteInput.style.boxShadow = 'none';
        }
        var noteCounter = d.getElementById('umUserNotesCounter');
        if (noteCounter) {
            noteCounter.textContent = '0/250 karakter';
            noteCounter.style.color = '#6b7280';
        }
        _render();
    }

    /* ── Render daftar file yang dipilih ────────────────────────────── */
    function _render() {
        elFileList.innerHTML = '';

        if (selectedFiles.length === 0) {
            elListWrap.style.display = 'none';
            elBtnSubmit.disabled     = true;
            return;
        }

        elListWrap.style.display = '';
        elFileCount.textContent  = selectedFiles.length + ' file dipilih';

        var hasError = (selectedFiles.length > MAX_FILES);

        selectedFiles.forEach(function (file, idx) {
            var fileExt   = getExt(file.name);
            var isTooBig  = file.size > MAX_BYTES;
            var isBadExt  = ALLOWED_EXT.indexOf(fileExt) === -1;
            var errMsg    = '';

            if (isTooBig)      errMsg = '✕ ' + fmtBytes(file.size) + ' — melebihi batas 8 MB';
            else if (isBadExt) errMsg = '✕ Ekstensi .' + fileExt + ' tidak diizinkan';
            if (isTooBig || isBadExt) hasError = true;

            /* Warna ikon per ekstensi */
            var colors = {
                pdf:  { bg: '#fef2f2', fg: '#dc2626' },
                doc:  { bg: '#eff6ff', fg: '#2563eb' },
                docx: { bg: '#eff6ff', fg: '#2563eb' },
                xls:  { bg: '#f0fdf4', fg: '#16a34a' },
                xlsx: { bg: '#f0fdf4', fg: '#16a34a' },
            };
            var c = colors[fileExt] || { bg: '#f3f4f6', fg: '#6b7280' };

            /* Li item */
            var li = d.createElement('li');
            li.style.cssText = 'display:flex;align-items:center;gap:10px;'
                + 'background:' + (errMsg ? '#fef2f2' : '#f9fafb') + ';'
                + 'border:1px solid ' + (errMsg ? '#fca5a5' : '#e5e7eb') + ';'
                + 'border-radius:8px;padding:8px 12px;font-size:.82rem;';

            /* Ikon ekstensi */
            var icon = d.createElement('div');
            icon.style.cssText = 'width:32px;height:32px;border-radius:6px;'
                + 'background:' + c.bg + ';color:' + c.fg + ';'
                + 'display:flex;align-items:center;justify-content:center;'
                + 'font-size:.65rem;font-weight:800;flex-shrink:0;text-transform:uppercase;';
            icon.textContent = fileExt;

            /* Info */
            var info = d.createElement('div');
            info.style.cssText = 'flex:1;min-width:0;';

            var nameEl = d.createElement('span');
            nameEl.style.cssText = 'display:block;color:#374151;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
            nameEl.textContent   = file.name;
            nameEl.title         = file.name;

            var metaEl = d.createElement('span');
            metaEl.style.cssText = 'display:block;font-size:.74rem;margin-top:1px;';
            if (errMsg) {
                metaEl.style.color      = '#ef4444';
                metaEl.style.fontWeight = '600';
                metaEl.textContent      = errMsg;
            } else {
                metaEl.style.color = '#9ca3af';
                metaEl.textContent = fmtBytes(file.size);
            }

            info.appendChild(nameEl);
            info.appendChild(metaEl);

            /* Tombol hapus per file */
            var rmBtn = d.createElement('button');
            rmBtn.type  = 'button';
            rmBtn.title = 'Hapus file ini';
            rmBtn.style.cssText = 'width:24px;height:24px;background:none;border:none;color:#9ca3af;cursor:pointer;border-radius:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0;';
            rmBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            rmBtn.addEventListener('click', (function (i) {
                return function () { selectedFiles.splice(i, 1); _render(); };
            })(idx));

            li.appendChild(icon);
            li.appendChild(info);
            li.appendChild(rmBtn);
            elFileList.appendChild(li);
        });

        /* Peringatan melebihi batas jumlah */
        if (selectedFiles.length > MAX_FILES) {
            var warn = d.createElement('li');
            warn.style.cssText = 'background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:8px 12px;font-size:.8rem;color:#ef4444;font-weight:600;text-align:center;';
            warn.textContent   = '⚠ Maksimal ' + MAX_FILES + ' file. Hapus ' + (selectedFiles.length - MAX_FILES) + ' file lagi.';
            elFileList.prepend(warn);
        }

        elBtnSubmit.disabled = hasError;
    }

    /* ── Tambahkan file baru (hindari duplikat) ─────────────────────── */
    function _addFiles(newFiles) {
        newFiles.forEach(function (f) {
            var isDup = selectedFiles.some(function (s) {
                return s.name === f.name && s.size === f.size;
            });
            if (!isDup) selectedFiles.push(f);
        });
        _render();
    }

    /* ── Event listeners: tutup modal ───────────────────────────────── */
    elBtnClose.addEventListener('click',  w.closeUploadModal);
    elBtnCancel.addEventListener('click', w.closeUploadModal);
    elBtnClear.addEventListener('click',  _reset);

    elModal.addEventListener('click', function (e) {
        if (e.target === elModal) w.closeUploadModal();
    });

    d.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && elModal.style.display !== 'none') {
            w.closeUploadModal();
        }
    });

    /* ── Event listeners: input & drag-drop ─────────────────────────── */
    elDropZone.addEventListener('click', function (e) {
        if (e.target.tagName !== 'LABEL') elFileInput.click();
    });

    elFileInput.addEventListener('change', function () {
        _addFiles(Array.from(this.files));
        this.value = ''; // reset agar file yang sama bisa dipilih ulang
    });

    elDropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        elDropZone.classList.add('um-drag-over');
    });
    elDropZone.addEventListener('dragleave', function () {
        elDropZone.classList.remove('um-drag-over');
    });
    elDropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        elDropZone.classList.remove('um-drag-over');
        _addFiles(Array.from(e.dataTransfer.files));
    });

    /* ── Event listener: klik "Unggah Sekarang" ─────────────────────── */
    elBtnSubmit.addEventListener('click', _doUpload);

    /* ── Event listener: Menghitung Karakter Catatan User ──────────────── */
    var elUserNotes = d.getElementById('umUserNotes');
    var elNotesCounter = d.getElementById('umUserNotesCounter');

    if (elUserNotes && elNotesCounter) {
        elUserNotes.addEventListener('input', function() {
            var currentLength = this.value.length;
            elNotesCounter.textContent = currentLength + '/250 karakter';
            
            // Ubah jadi merah jika menyentuh batas
            if (currentLength >= 250) {
                elNotesCounter.style.color = '#ef4444'; // Teks Merah
                elUserNotes.style.borderColor = '#ef4444'; // Border Merah
                elUserNotes.style.boxShadow = '0 0 0 2px rgba(239, 68, 68, 0.2)';
            } else {
                elNotesCounter.style.color = '#6b7280'; // Teks Abu-abu
                elUserNotes.style.borderColor = '#d1d5db'; // Border Normal
                elUserNotes.style.boxShadow = 'none';
            }
        });
    }

    /* ── Fungsi inti upload ──────────────────────────────────────────── */
    function _doUpload() {

        /* Validasi client-side (Minimal 1 file total gabungan) */
        if (selectedFiles.length === 0) {
            Swal.fire(Object.assign({ icon:'warning', title:'Pilih File Dulu', text:'Minimal harus ada 1 file yang diunggah.' }, swalOnTop()));
            return;
        }
        if (selectedFiles.length > MAX_FILES) {
            Swal.fire(Object.assign({ icon:'error', title:'Terlalu Banyak File', text:'Maksimal ' + MAX_FILES + ' file per unggahan.' }, swalOnTop()));
            return;
        }

        // Cek ukuran & ekstensi HANYA untuk file baru (file lama sudah divalidasi sebelumnya)
        var oversized = selectedFiles.filter(function (f) { return !f.is_old_file && f.size > MAX_BYTES; });
        if (oversized.length > 0) {
            Swal.fire(Object.assign({
                icon : 'error', title: 'File Terlalu Besar',
                html : '<strong>' + oversized.length + ' file</strong> melebihi batas <strong>8 MB</strong>:<br><br>'
                     + oversized.map(function (f) { return '• ' + f.name + ' (' + fmtBytes(f.size) + ')'; }).join('<br>'),
            }, swalOnTop()));
            return;
        }

        var badExt = selectedFiles.filter(function (f) { return !f.is_old_file && ALLOWED_EXT.indexOf(getExt(f.name)) === -1; });
        if (badExt.length > 0) {
            Swal.fire(Object.assign({
                icon : 'error', title: 'Format Tidak Diizinkan',
                html : 'File berikut memiliki ekstensi tidak valid:<br><br>'
                     + badExt.map(function (f) { return '• ' + f.name; }).join('<br>'),
            }, swalOnTop()));
            return;
        }

        /* ✅ LOGIKA BARU: Pisahkan file baru dan file lama yang dipertahankan */
        var formData = new FormData();
        var newFilesToUpload = selectedFiles.filter(function(file) { return !file.is_old_file; });
        var retainedFiles    = selectedFiles.filter(function(file) { return file.is_old_file; });

        // 1. Masukkan file FISIK baru ke form
        newFilesToUpload.forEach(function (file) {
            formData.append('files[]', file, file.name);
        });

        // 2. Beritahu server file lama apa saja yang TIDAK DIHAPUS oleh user
        retainedFiles.forEach(function (file) {
            formData.append('retained_files[]', file.path); // Kirim path sebagai penanda
        });

        formData.append('user_notes', document.getElementById('umUserNotes').value);

        /* ── Tampilkan SweetAlert LOADING ── */
        Swal.fire({
            title : 'Sedang Memproses...',
            html  : '<div style="font-size:.85rem;color:#6b7280;margin-top:6px;">Menyimpan <strong>' + selectedFiles.length + ' file</strong>.<br>Mohon tunggu sebentar.</div>',
            allowOutsideClick : false, allowEscapeKey : false, showConfirmButton : false,
            willOpen: function () {
                var container = d.querySelector('.swal2-container');
                if (container) container.style.zIndex = '99999';
            },
            didOpen: function () {
                Swal.showLoading();
            },
        });

        elBtnSubmit.disabled = true;

        /* ── Kirim ke server via fetch ── */
        fetch('/pengajuan/' + pengajuanId + '/upload', {
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            body: formData,
        })
        .then(function (response) {
            return response.json().then(function (data) { return { httpStatus: response.status, ok: response.ok, data: data }; });
        })
        .then(function (res) {
            if (res.ok && res.data.success) {
                var files = res.data.files || [];
                var fileLines = files.map(function (f) { return '<small style="color:#6b7280;">✓ ' + f.name + '</small>'; }).join('<br>');

                Swal.fire({
                    icon : 'success', title: 'Berhasil Diupload!',
                    html : '<strong>' + (res.data.total_files || files.length) + ' file</strong> berhasil disimpan.' + (fileLines ? '<br><br>' + fileLines : ''),
                    confirmButtonText: 'Oke', confirmButtonColor: '#4f46e5', allowOutsideClick: false,
                    willOpen: function () {
                        var container = d.querySelector('.swal2-container');
                        if (container) container.style.zIndex = '99999';
                    },
                }).then(function () {
                    w.closeUploadModal();
                    if (typeof w.refreshPengajuanData === 'function') w.refreshPengajuanData();
                    else w.location.reload();
                });
            } else if (res.httpStatus === 422) {
                var errData = res.data.errors || {};
                var messages = [];
                if (typeof errData === 'object' && !Array.isArray(errData)) {
                    Object.values(errData).forEach(function (arr) { if (Array.isArray(arr)) arr.forEach(function (msg) { messages.push(msg); }); else messages.push(arr); });
                } else if (Array.isArray(errData)) messages = errData;
                if (messages.length === 0 && res.data.message) messages = [res.data.message];

                Swal.fire({ icon : 'error', title: 'Validasi Gagal', html : messages.map(function (m) { return '• ' + m; }).join('<br>'), confirmButtonColor: '#ef4444',
                    willOpen: function () { var container = d.querySelector('.swal2-container'); if (container) container.style.zIndex = '99999'; }
                });
                elBtnSubmit.disabled = false;
            } else if (res.httpStatus === 403) {
                Swal.fire({ icon : 'warning', title: 'Akses Ditolak', text : res.data.message || 'Anda tidak berhak melakukan aksi ini.', confirmButtonColor: '#f59e0b',
                    willOpen: function () { var container = d.querySelector('.swal2-container'); if (container) container.style.zIndex = '99999'; }
                });
                elBtnSubmit.disabled = false;
            } else { throw new Error(res.data.message || 'Terjadi kesalahan pada server (HTTP ' + res.httpStatus + ').'); }
        })
        .catch(function (err) {
            Swal.fire({ icon : 'error', title: 'Gagal Memproses', text : err.message || 'Tidak dapat terhubung ke server.', confirmButtonColor: '#ef4444',
                willOpen: function () { var container = d.querySelector('.swal2-container'); if (container) container.style.zIndex = '99999'; }
            });
            elBtnSubmit.disabled = false;
        });
    } /* end _doUpload */

}(window, document));
</script>
