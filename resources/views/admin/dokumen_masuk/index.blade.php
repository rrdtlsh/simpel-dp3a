<div class="content">
    <div class="arsip-header">
        <h2>Arsip Dokumen Masuk</h2>
        <div class="header-actions">
            {{-- ✅ FITUR PENCARIAN --}}
            <div class="search-box-arsip">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchArsipAdmin" placeholder="Cari nama dokumen...">
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

                <script type="application/json" id="files-arsip-{{ $row->id }}">@json($filesArray)</script>
                
                <tr class="row-data" 
                    data-row-id="{{ $row->id }}"
                    data-judul="{{ strtolower($row->judul) }}"
                    data-bidang="{{ $row->bidang->nama ?? '-' }}"
                    data-tahun="{{ $row->tahun }}">
                    
                    <td style="text-align:center; font-weight:700;">{{ $index + 1 }}</td>
                    <td style="text-align:left;">{{ $row->judul }}</td>
                    <td style="text-align:center;">{{ $row->bidang->nama ?? '-' }}</td>
                    <td style="text-align:center;">{{ $row->tahun ?? '-' }}</td>
                    <td style="text-align:center;" class="arsip-tgl-terima">{{ $tanggalTerima }}</td>
                    <td style="text-align:center;">
                        <div class="action-btn">
                            <button type="button" class="btn-view action-lihat-arsip"
                                data-row-id="{{ $row->id }}"
                                data-nama="{{ $row->judul }}"
                                data-bidang="{{ $row->bidang->nama ?? '-' }}"
                                data-tahun="{{ $row->tahun }}"
                                data-tanggal-terima="{{ $tanggalTerima }}"
                                data-penjelasan="{{ $file?->admin_notes ?? 'Tidak ada catatan tambahan.' }}"
                                data-user-notes="{{ $file?->user_notes ?? '' }}"
                                title="Lihat Detail Arsip">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="arsip-empty-row">
                    <td colspan="6">Belum ada arsip dokumen yang diterima.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Detail Arsip --}}
<div class="modal-verifikasi" id="arsipModal">
    <div class="modal-content modal-large">
        <div class="modal-verif-header">
            <h3>Detail Arsip Dokumen</h3>
            <span class="modal-verif-close" id="closeArsipModal">&times;</span>
        </div>
        
        <div class="doc-info">
            <div><strong>Nama Dokumen:</strong> <span id="arsipNama"></span></div>
            <div><strong>Bidang:</strong> <span id="arsipBidang"></span></div>
            <div>
                <strong>Diterima Tanggal:</strong>
                <span id="arsipTanggalTerima" class="arsip-tgl-terima"></span>
            </div>
            
            <div id="arsipUserNotesContainer" class="arsip-user-notes-box">
                <strong>Pesan dari Bidang:</strong>
                <div id="arsipUserNotesText" class="arsip-user-notes-content"></div>
            </div>
            
            <div class="arsip-admin-notes-box">
                <strong>Catatan Admin:</strong>
                <div id="arsipPenjelasan" class="arsip-admin-notes-content"></div>
            </div>
        </div>
        
        <div class="arsip-file-section">
            <h4>Lampiran Dokumen Final</h4>
            <ul id="arsipLampiranList" class="file-section"></ul>
        </div>
    </div>
</div>