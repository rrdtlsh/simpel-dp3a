<div class="content">
    <div class="verif-header">
        <h2>Verifikasi Dokumen Masuk</h2>
        
        {{-- ✅ FITUR PENCARIAN --}}
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchVerifikasiAdmin" placeholder="Cari nama dokumen...">
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

                        $filesArray = collect($file?->files ?? [])
                            ->map(fn($f) => [
                                'original_name' => $f['original_name'] ?? basename($f['path'] ?? 'File'),
                                'path'          => $f['path'] ?? '',
                            ])
                            ->values()
                            ->toArray();
                    @endphp

                <script type="application/json" id="files-verif-{{ $row->id }}">@json($filesArray)</script>
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
                            <span class="table-status-empty">— Belum Ada File</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div class="action-btn">
                            {{-- Tombol Lihat --}}
                            <button type="button" class="btn-view action-verifikasi {{ $status === 'belum_upload' ? 'btn-verif-disabled' : '' }}"
                                data-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                data-bidang="{{ $row->bidang->nama ?? '-' }}"
                                data-deadline="{{ \Carbon\Carbon::parse($row->due_date)->format('d M Y H:i') }}"
                                data-tanggal-upload="{{ $file ? $file->created_at->format('d M Y H:i') : '-' }}"
                                data-status="{{ $status }}"
                                data-user-notes="{{ $file ? ($file->user_notes ?? '') : '' }}"
                                title="Lihat & Beri Catatan"
                                @if($status === 'belum_upload') disabled @endif>
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            {{-- Tombol Terima --}}
                            <button type="button" class="btn-yes action-approve {{ $status === 'belum_upload' ? 'btn-verif-disabled' : '' }}"
                                data-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                title="Terima Dokumen"
                                @if($status === 'belum_upload') disabled @endif>
                                <i class="fa-solid fa-check"></i>
                            </button>

                            {{-- Tombol Tolak --}}
                            <button type="button" class="btn-no action-reject {{ $status === 'belum_upload' ? 'btn-verif-disabled' : '' }}"
                                data-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                title="Tolak Dokumen"
                                @if($status === 'belum_upload') disabled @endif>
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="verifikasi-empty-row">
                    <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">Belum ada pengajuan dokumen.</td>
                </tr>
                @endforelse
                <tr id="verifikasiSearchEmpty" style="display:none;">
                    <td colspan="6" style="text-align: center; padding: 40px 20px; background: #fff;">
                        <i class="fa-solid fa-magnifying-glass" style="font-size:3rem; color:#d1d5db; margin-bottom: 12px; display:block;"></i>
                        <h4 style="margin: 0 0 6px 0; color: #374151; font-size: 1.1rem;">Hasil Pencarian Tidak Ditemukan</h4>
                        <p style="margin: 0; color: #9ca3af; font-size: 0.85rem;">Coba gunakan kata kunci pencarian yang lain.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Detail Verifikasi --}}
<div class="modal-verifikasi" id="detailModal">
    <div class="modal-content modal-large">
        <div class="modal-verif-header">
            <h3>Detail & Verifikasi Dokumen</h3>
            <span class="modal-verif-close" id="closeDetail">&times;</span>
        </div>
        
        <div class="doc-info">
            <div><strong>Nama Dokumen:</strong> <span id="dNama"></span></div>
            <div><strong>Bidang:</strong> <span id="dBidang"></span></div>
            <div><strong>Tanggal Diupload:</strong> <span id="dTanggalUpload"></span></div>
            <div><strong>Deadline Target:</strong> <span id="dDeadline"></span></div>
            <div><strong>Status Saat Ini:</strong> <span id="dStatus"></span></div>
        </div>

        <div id="dUserNotesContainer" class="user-notes-box" style="display:none;">
            <span class="user-notes-title"><i class="fa-solid fa-envelope-open-text"></i> Pesan dari Bidang:</span>
            <div id="dUserNotesText" class="user-notes-text"></div>
        </div>

        <div class="admin-notes-label">
            Catatan Revisi / Penjelasan Admin: <span>*</span>
        </div>

        <div class="editor-toolbar" style="margin-top:5px;">
            <button type="button" data-cmd="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
            <button type="button" data-cmd="italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
            <button type="button" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
        </div>
        
        <div id="editorDetail" class="editor-area validate-editor" contenteditable="true" placeholder="Ketik catatan di sini..."></div>
        <div id="adminNotesCounter" class="admin-notes-counter">0/500 karakter</div>
        
        <div class="invalid-feedback" id="err_editor" style="display:none; color:#dc3545; font-size:11.5px; font-weight:600; margin-top:-8px; margin-bottom:12px;">
            <i class="fa-solid fa-circle-exclamation"></i> <span></span>
        </div>

        <div class="file-section">
            <h4>Lampiran Dokumen (Upload dari User)</h4>
            <ul id="lampiranList"></ul>
        </div>

        <div class="modal-verif-footer">
            <input type="hidden" id="verifikasiPengajuanId">
            <button type="button" class="btn-save" id="btnSaveNotes">Simpan Catatan Saja</button>
            <button type="button" class="btn-send" id="btnSendFeedback" style="background:var(--merah);">Tolak &amp; Kirim Revisi</button>
        </div>
    </div>
</div>