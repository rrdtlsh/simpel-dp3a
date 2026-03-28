<div class="content">
    <div class="section-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <h2>Arsip Dokumen Masuk</h2>
        <div class="header-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            {{-- ✅ FITUR PENCARIAN --}}
            <div style="position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;"></i>
                <input type="text" id="searchArsipAdmin" placeholder="Cari nama dokumen..." 
                       style="padding: 8px 10px 8px 35px; border-radius: 6px; border: 1px solid #d1d5db; outline: none; width: 200px; font-family:'Poppins', sans-serif; font-size: 13px;">
            </div>
            <select id="filterBidang">
                <option value="">Semua Bidang</option>
                @foreach($bidangs as $b)
                    <option value="{{ $b->nama }}">{{ $b->nama }}</option>
                @endforeach
            </select>
            <select id="filterTahun">
                <option value="">Semua Tahun</option>
                @foreach($tahuns as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
            <button class="btn-export btn-pdf" id="btnExportPDF">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </button>
            <button class="btn-export btn-excel" id="btnExportExcel">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </button>
        </div>
    </div>

    <div class="table-box">
        <table id="dokumenTable">
            <thead>
                <tr>
                    <th style="text-align:center; width:5%;">No</th>
                    <th style="text-align:left;">Nama Dokumen</th>
                    <th style="text-align:center;">Bidang</th>
                    <th style="text-align:center;">Tahun</th>
                    <th style="text-align:center;">Tanggal Diterima</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $index => $row)
                    @php
                        $file = $row->files->first();

                        $filesArray = collect($file?->files ?? [])
                            ->map(fn($f) => [
                                'original_name' => $f['original_name'] ?? basename($f['path'] ?? 'File'),
                                'path'          => $f['path'] ?? '',
                            ])
                            ->values()
                            ->toArray();

                        $tanggalTerima = $file?->updated_at
                            ? $file->updated_at->format('d M Y H:i')
                            : '-';
                    @endphp

                {{-- ✅ Embed JSON — aman untuk semua karakter termasuk UTF-8 --}}
                <script type="application/json" id="files-arsip-{{ $row->id }}">@json($filesArray)</script>
                
                {{-- ✅ FIX: Tag tr DITUTUP DENGAN BENAR ( > ) --}}
                <tr class="row-data" 
                    data-row-id="{{ $row->id }}"
                    data-judul="{{ strtolower($row->judul) }}"
                    data-bidang="{{ $row->bidang->nama ?? '-' }}"
                    data-tahun="{{ $row->tahun }}">
                    
                    <td style="text-align:center; font-weight:700;">{{ $index + 1 }}</td>
                    <td style="text-align:left;">{{ $row->judul }}</td>
                    <td style="text-align:center;">{{ $row->bidang->nama ?? '-' }}</td>
                    <td style="text-align:center;">{{ $row->tahun ?? '-' }}</td>
                    <td style="text-align:center; color:#2D8659; font-weight:600;">{{ $tanggalTerima }}</td>
                    <td style="text-align:center;">
                        <div class="action-btn">
                            {{-- data-files DIHAPUS — JS baca dari #files-arsip-{rowId} --}}
                            <button
                                type="button"
                                class="btn-view action-lihat-arsip"
                                data-row-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                data-bidang="{{ $row->bidang->nama ?? '-' }}"
                                data-tahun="{{ $row->tahun }}"
                                data-tanggal-terima="{{ $tanggalTerima }}"
                                data-penjelasan="{{ $file?->admin_notes ?? 'Tidak ada catatan tambahan.' }}"
                               data-user-notes="{{ $file?->user_notes ?? '' }}"
                                title="Lihat Detail Arsip"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#888; padding:20px;">
                        Belum ada arsip dokumen yang diterima.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Detail Arsip --}}
<div class="modal-verifikasi" id="arsipModal">
    <div class="modal-content modal-large">
        <div class="modal-header" style="position:relative; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
            <h3 style="margin:0;">Detail Arsip Dokumen</h3>
            <span class="close-modal" id="closeArsipModal"
                style="position:absolute; right:0; top:-5px; font-size:24px; cursor:pointer;">&times;</span>
        </div>
        <div class="doc-info">
            <div><strong>Nama Dokumen:</strong> <span id="arsipNama"></span></div>
            <div><strong>Bidang:</strong> <span id="arsipBidang"></span></div>
            <div>
                <strong>Diterima Tanggal:</strong>
                <span id="arsipTanggalTerima" style="color:#2D8659; font-weight:bold;"></span>
            </div>
            <div id="arsipUserNotesContainer" style="margin-top:12px; display:none;">
                <strong>Pesan dari Bidang:</strong>
                <div id="arsipUserNotesText"
                    style="margin-top:6px; padding:10px 14px; background:#eff6ff;
                           border-left:4px solid #3b82f6; border-radius:8px;
                           font-size:.875rem; color:#1e3a8a; min-height:36px;
                           word-break: break-word; white-space: pre-wrap; max-height: 80px; overflow-y: auto;">
                </div>
            </div>
            <div style="margin-top:8px;">
                <strong>Catatan Admin:</strong>
                <div id="arsipPenjelasan"
                    style="margin-top:6px; padding:10px 14px; background:#f9fafb;
                           border:1px solid #e5e7eb; border-radius:8px;
                           font-size:.875rem; color:#374151; min-height:36px;
                           word-break: break-word; white-space: pre-wrap; max-height: 80px; overflow-y: auto;">
                </div>
            </div>
        </div>
        <div class="file-section" style="margin-top:16px;">
            <h4>Lampiran Dokumen Final</h4>
            <ul id="arsipLampiranList"></ul>
        </div>
    </div>
</div>
