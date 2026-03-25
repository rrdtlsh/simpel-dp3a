<div class="content">
    <div class="section-header" style="margin-bottom: 18px;">
        <h2>Verifikasi Dokumen Masuk</h2>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th style="text-align: center; width: 5%;">No</th>
                    <th style="text-align: left;">Nama Dokumen</th>
                    <th style="text-align: center;">Bidang</th>
                    <th style="text-align: center;">Deadline</th>
                    <th style="text-align: center;">Status Berkas</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $index => $row)
                    @php
                        // Ambil file terakhir yang diupload (jika ada)
                        $file = $row->files->first();
                        $status = $file ? $file->status : 'belum_upload';
                        $catatan = $file ? $file->admin_notes : '';
                        $fileId = $file ? $file->id : '';
                    @endphp
                <tr>
                    <td style="text-align: center; font-weight:700;">{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $row->judul }}</td>
                    <td style="text-align: center;">{{ $row->bidang->nama ?? '-' }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($row->due_date)->format('d M Y H:i') }}</td>
                    
                    <td style="text-align: center;">
                        @if($status === 'approved')
                            <span class="status-diterima">Diterima</span>
                        @elseif($status === 'rejected')
                            <span class="status-ditolak">Ditolak</span>
                        @elseif($status === 'pending')
                            <span class="status-menunggu">Menunggu Review</span>
                        @else
                            <span style="color: #888; font-weight: 600;">Belum Ada File</span>
                        @endif
                    </td>
                    
                    <td style="text-align: center;">
                        <div class="action-btn">
                            {{-- Tombol Lihat (Membuka Modal) --}}
                            <button type="button" class="btn-view action-verifikasi"
                                data-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                data-bidang="{{ $row->bidang->nama ?? '-' }}"
                                data-deadline="{{ \Carbon\Carbon::parse($row->due_date)->format('d M Y H:i') }}"
                                data-tanggal_upload="{{ $file ? $file->created_at->format('d M Y H:i') : '-' }}"
                                data-status="{{ $status }}"
                                data-penjelasan="{{ $catatan }}"
                                data-files="{{ $file ? json_encode($file->files) : '[]' }}"
                                title="Lihat & Beri Catatan"
                                {{ $status === 'belum_upload' ? 'disabled style=opacity:0.5;' : '' }}>
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            {{-- Tombol Langsung Terima --}}
                            <button type="button" class="btn-yes action-approve" data-id="{{ $row->id }}" title="Terima Dokumen" {{ $status === 'belum_upload' ? 'disabled style=opacity:0.5;' : '' }}>
                                <i class="fa-solid fa-check"></i>
                            </button>

                            {{-- Tombol Langsung Tolak --}}
                            <button type="button" class="btn-no action-reject" data-id="{{ $row->id }}" title="Tolak Dokumen" {{ $status === 'belum_upload' ? 'disabled style=opacity:0.5;' : '' }}>
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Belum ada pengajuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-verifikasi" id="detailModal">
    <div class="modal-content modal-large">
        <div class="modal-header" style="position: relative; border-bottom: 1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
            <h3 style="margin: 0;">Detail & Verifikasi Dokumen</h3>
            <span class="close-modal" id="closeDetail" style="position: absolute; right: 0; top: -5px; font-size: 24px; cursor: pointer;">&times;</span>
        </div>

        <div class="doc-info">
            <div><strong>Nama Dokumen:</strong> <span id="dNama"></span></div>
            <div><strong>Bidang:</strong> <span id="dBidang"></span></div>
            <div><strong>Tanggal Diupload:</strong> <span id="dTanggalUpload"></span></div>
            <div><strong>Deadline Target:</strong> <span id="dDeadline" style="color:#E74A3B"></span></div>
            <div><strong>Status Saat Ini:</strong> <span id="dStatus" style="font-weight: bold;"></span></div>
        </div>

        <div style="margin-top: 15px;"><strong>Catatan Revisi / Penjelasan Admin:</strong> <span style="color:red">*</span></div>
        
        <div class="editor-toolbar" style="margin-top: 5px;">
            <button type="button" data-cmd="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
            <button type="button" data-cmd="italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
            <button type="button" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
        </div>

        <div id="editorDetail" class="editor-area validate-editor" contenteditable="true" placeholder="Ketik catatan di sini..."></div>
        <div class="invalid-feedback" id="err_editor" style="display:none; color: #dc3545; font-size: 11.5px; font-weight: 600; margin-top:-8px; margin-bottom:12px;">
            <i class="fa-solid fa-circle-exclamation"></i> <span></span>
        </div>

        <div class="file-section">
            <h4>Lampiran Dokumen (Upload dari User)</h4>
            <ul id="lampiranList">
                <li><i class="fa-solid fa-file-pdf"></i> Dokumen_Terkait.pdf <a href="#" style="color: var(--biru-utama);"><i class="fa-solid fa-download"></i> Download</a></li>
            </ul>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <input type="hidden" id="verifikasiPengajuanId">
            <button type="button" class="btn-save" id="btnSaveNotes">Simpan Catatan Saja</button>
            <button type="button" class="btn-send" id="btnSendFeedback" style="background:var(--merah);">Tolak & Kirim Revisi</button>
        </div>
    </div>
</div>