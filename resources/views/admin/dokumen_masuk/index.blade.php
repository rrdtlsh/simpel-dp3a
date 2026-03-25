<div class="content">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <h2>Arsip Dokumen Masuk</h2>

        <div class="header-actions">
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

            <button class="btn-export btn-pdf" id="btnExportPDF" title="Fitur dalam pengembangan">
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
                    <th style="text-align: center; width: 5%;">No</th>
                    <th style="text-align: left;">Nama Dokumen</th>
                    <th style="text-align: center;">Bidang</th>
                    <th style="text-align: center;">Tahun</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuans as $index => $row)
                    @php
                        $file = $row->files->where('status', 'approved')->first();
                    @endphp
                <tr data-bidang="{{ $row->bidang->nama ?? '-' }}" data-tahun="{{ $row->created_at->format('Y') }}">
                    <td style="text-align: center; font-weight:700;">{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $row->judul }}</td>
                    <td style="text-align: center;">{{ $row->bidang->nama ?? '-' }}</td>
                    <td style="text-align: center;">{{ $row->created_at->format('Y') }}</td>
                    <td style="text-align: center;">
                        <div class="action-btn">
                            <button type="button" class="btn-view action-lihat-arsip"
                                data-nama="{{ $row->judul }}"
                                data-bidang="{{ $row->bidang->nama ?? '-' }}"
                                data-tahun="{{ $row->created_at->format('Y') }}"
                                data-tanggal_terima="{{ $file->updated_at->format('d M Y H:i') }}"
                                data-penjelasan="{{ $file->admin_notes ?? 'Tidak ada catatan tambahan.' }}"
                                data-files="{{ json_encode($file->files) }}"
                                title="Lihat Detail Arsip">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada arsip dokumen yang diterima.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-verifikasi" id="arsipModal">
    <div class="modal-content modal-large">
        <div class="modal-header" style="position: relative; border-bottom: 1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
            <h3 style="margin: 0;">Detail Arsip Dokumen</h3>
            <span class="close-modal" id="closeArsipModal" style="position: absolute; right: 0; top: -5px; font-size: 24px; cursor: pointer;">&times;</span>
        </div>

        <div class="doc-info">
            <div><strong>Nama Dokumen:</strong> <span id="arsipNama"></span></div>
            <div><strong>Bidang:</strong> <span id="arsipBidang"></span></div>
            <div><strong>Diterima Tanggal:</strong> <span id="arsipTanggalTerima" style="color:#2D8659; font-weight:bold;"></span></div>
        </div>
        
        <div class="file-section">
            <h4>Lampiran Dokumen Final</h4>
            <ul id="arsipLampiranList">
                </ul>
        </div>
    </div>
</div>