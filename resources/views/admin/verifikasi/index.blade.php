<div class="content">
    <div class="section-header" style="margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin: 0;">Verifikasi Dokumen Masuk</h2>
        
        {{-- ✅ FITUR PENCARIAN --}}
        <div style="position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;"></i>
            <input type="text" id="searchVerifikasiAdmin" placeholder="Cari nama dokumen..." 
                   style="padding: 8px 10px 8px 35px; border-radius: 6px; border: 1px solid #d1d5db; outline: none; width: 250px; font-family:'Poppins', sans-serif; font-size: 13px;">
        </div>
    </div>

    <div class="table-box">
        <table id="tabelVerifikasiAdmin">
            <thead>
                <tr>
                    <th style="text-align:center; width:5%;">No</th>
                    <th style="text-align:left;">Nama Dokumen</th>
                    <th style="text-align:center;">Bidang</th>
                    <th style="text-align:center;">Deadline</th>
                    <th style="text-align:center;">Status Berkas</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $index => $row)
                    @php
                        $file    = $row->files->first();
                        $status  = $file ? $file->status : 'belum_upload';
                        $catatan = $file ? ($file->admin_notes ?? '') : '';

                        // Siapkan array file untuk di-embed sebagai JSON
                        // Tidak perlu base64 — akan dimasukkan ke <script type="application/json">
                        $filesArray = collect($file?->files ?? [])
                            ->map(fn($f) => [
                                'original_name' => $f['original_name'] ?? basename($f['path'] ?? 'File'),
                                'path'          => $f['path'] ?? '',
                            ])
                            ->values()
                            ->toArray();
                    @endphp

                {{--
                    ✅ Embed JSON file list sebagai script tag — AMAN untuk semua karakter
                    JS membaca: document.getElementById('files-verif-{{ $row->id }}').textContent
                --}}
                <script type="application/json" id="files-verif-{{ $row->id }}">@json($filesArray)</script>

                {{-- ✅ TAHAP 1A: Simpan Catatan HTML Admin dengan sangat aman di sini --}}
                <template id="admin-note-{{ $row->id }}">{!! $catatan !!}</template>

                <tr class="row-data" data-judul="{{ strtolower($row->judul) }}">
                    <td style="text-align:center; font-weight:700;">{{ $index + 1 }}</td>
                    <td style="text-align:left;">{{ $row->judul }}</td>
                    <td style="text-align:center;">{{ $row->bidang->nama ?? '-' }}</td>
                    <td style="text-align:center;">{{ \Carbon\Carbon::parse($row->due_date)->format('d M Y H:i') }}</td>
                    <td style="text-align:center;">
                        @if($status === 'approved')
                            <span class="status-diterima">Diterima</span>
                        @elseif($status === 'rejected')
                            <span class="status-ditolak">Ditolak</span>
                        @elseif($status === 'pending')
                            <span class="status-menunggu">Menunggu Review</span>
                        @else
                            <span style="color:#888; font-weight:600;">Belum Ada File</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div class="action-btn">
                            {{-- Tombol Lihat — data-files DIHAPUS, diganti id="files-verif-{id}" di atas --}}
                            <button
                                type="button"
                                class="btn-view action-verifikasi"
                                data-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                data-bidang="{{ $row->bidang->nama ?? '-' }}"
                                data-deadline="{{ \Carbon\Carbon::parse($row->due_date)->format('d M Y H:i') }}"
                                data-tanggal-upload="{{ $file ? $file->created_at->format('d M Y H:i') : '-' }}"
                                data-status="{{ $status }}"
                                data-user-notes="{{ $file ? ($file->user_notes ?? '') : '' }}"
                                title="Lihat & Beri Catatan"
                                @if($status === 'belum_upload') disabled style="opacity:0.5;cursor:not-allowed;" @endif
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            {{-- Tombol Terima --}}
                            <button
                                type="button"
                                class="btn-yes action-approve"
                                data-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                title="Terima Dokumen"
                                @if($status === 'belum_upload') disabled style="opacity:0.5;cursor:not-allowed;" @endif
                            >
                                <i class="fa-solid fa-check"></i>
                            </button>

                            {{-- Tombol Tolak --}}
                            <button
                                type="button"
                                class="btn-no action-reject"
                                data-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                title="Tolak Dokumen"
                                @if($status === 'belum_upload') disabled style="opacity:0.5;cursor:not-allowed;" @endif
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#888; padding:20px;">
                        Belum ada pengajuan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Detail Verifikasi --}}
<div class="modal-verifikasi" id="detailModal">
    <div class="modal-content modal-large">
        <div class="modal-header" style="position:relative; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
            <h3 style="margin:0;">Detail & Verifikasi Dokumen</h3>
            <span class="close-modal" id="closeDetail"
                style="position:absolute; right:0; top:-5px; font-size:24px; cursor:pointer;">&times;</span>
        </div>
        <div class="doc-info">
            <div><strong>Nama Dokumen:</strong> <span id="dNama"></span></div>
            <div><strong>Bidang:</strong> <span id="dBidang"></span></div>
            <div><strong>Tanggal Diupload:</strong> <span id="dTanggalUpload"></span></div>
            <div><strong>Deadline Target:</strong> <span id="dDeadline" style="color:#E74A3B"></span></div>
            <div><strong>Status Saat Ini:</strong> <span id="dStatus" style="font-weight:bold;"></span></div>
        </div>
        <div id="dUserNotesContainer" style="display:none; margin-top:12px; padding:12px; background:#eff6ff; border-left:4px solid #3b82f6; border-radius:6px;">
            <span style="color:#1d4ed8; font-size:13px; font-weight:bold;"><i class="fa-solid fa-envelope-open-text"></i> Pesan dari Bidang:</span>
            <div id="dUserNotesText" style="margin-top:4px; font-size:13.5px; color:#1e3a8a; word-break: break-word; white-space: pre-wrap; max-height: 80px; overflow-y: auto;"></div>
        </div>
        <div style="margin-top:15px;">
            <strong>Catatan Revisi / Penjelasan Admin:</strong>
            <span style="color:red">*</span>
        </div>
        <div class="editor-toolbar" style="margin-top:5px;">
            <button type="button" data-cmd="bold"      title="Bold"><i class="fa-solid fa-bold"></i></button>
            <button type="button" data-cmd="italic"    title="Italic"><i class="fa-solid fa-italic"></i></button>
            <button type="button" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
        </div>
        <div id="editorDetail" class="editor-area validate-editor"
            contenteditable="true" placeholder="Ketik catatan di sini..."></div>
        <div id="adminNotesCounter" style="font-size: 11px; color: #6b7280; text-align: right; margin-top: 4px; font-weight: 500;">
            0/500 karakter
        </div>
        <div class="invalid-feedback" id="err_editor"
            style="display:none; color:#dc3545; font-size:11.5px; font-weight:600; margin-top:-8px; margin-bottom:12px;">
            <i class="fa-solid fa-circle-exclamation"></i> <span></span>
        </div>
        <div class="file-section">
            <h4>Lampiran Dokumen (Upload dari User)</h4>
            <ul id="lampiranList"></ul>
        </div>
        <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
            <input type="hidden" id="verifikasiPengajuanId">
            <button type="button" class="btn-save" id="btnSaveNotes">Simpan Catatan Saja</button>
            <button type="button" class="btn-send" id="btnSendFeedback" style="background:var(--merah);">
                Tolak &amp; Kirim Revisi
            </button>
        </div>
    </div>
</div>
