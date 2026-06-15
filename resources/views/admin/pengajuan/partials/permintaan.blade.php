<div class="content">
    <div class="page-header">
        <h2 style="margin: 0;">Permintaan Dokumen</h2>
        
        <div class="header-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            {{-- FILTER BIDANG --}}
            <select id="filterBidang" class="filter-select" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; outline: none; cursor: pointer; background-color: #fff; font-family: inherit;">
                <option value="all">Semua Bidang</option>
                @foreach($bidangs as $bidang)
                    <option value="{{ $bidang->id }}">{{ $bidang->nama }}</option>
                @endforeach
            </select>

            {{-- FILTER STATUS --}}
            <select id="filterStatus" class="filter-select" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; outline: none; cursor: pointer; background-color: #fff; font-family: inherit;">
                <option value="all">Semua Status</option>
                <option value="belum_upload">Belum Diunggah</option>
                <option value="pending">Menunggu Review</option>
                <option value="approved">Selesai</option>
            </select>

            {{-- ✅ FITUR PENCARIAN ADMIN --}}
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchPermintaanAdmin" placeholder="Cari nama dokumen...">
            </div>

            <button type="button" id="openModal" class="btn-primary">
                + Tambah Permintaan
            </button>
        </div>
    </div>

    <div class="table-box">
        <table id="tabelPermintaanAdmin">
            <thead>
                <tr>
                    <th class="text-left">No</th>
                    <th class="text-left">Nama Dokumen</th>
                    <th class="text-left">Bidang</th>
                    <th class="text-left">Tanggal Dibuat</th>
                    <th class="text-left">Deadline</th>
                    <th class="text-left">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengajuans as $index => $p)
                    @php
                        $fileUpload = $p->files->first();
                        $statusFile = $fileUpload ? strtolower(trim($fileUpload->status)) : 'belum_upload';
                    @endphp
                    <tr class="row-data" 
                        data-judul="{{ strtolower($p->judul) }}"
                        data-bidang="{{ $p->bidang_id }}"
                        data-status="{{ $statusFile }}">
                        <td style="font-weight:700;">{{ $index + 1 }}</td>
                        <td>{{ $p->judul }}</td>
                        <td>{{ $p->bidang->nama ?? '-' }}</td>
                        <td>{{ $p->created_at?->format('d M Y') }}</td>
                        <td style="color: #E74A3B; font-weight: 600;">{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('d M Y H:i') : '-' }}</td>
                        
                        <td>
                            @if($statusFile === 'belum_upload')
                                <span class="status-badge belum">Belum Diunggah</span>
                            @elseif($statusFile === 'pending')
                                <span class="status-badge pending">Menunggu Review</span>
                            @elseif($statusFile === 'rejected')
                                <span class="status-badge rejected">Revisi (Ditolak)</span>
                            @else
                                <span class="status-badge approved">Selesai</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="action-buttons">
                                {{-- Tombol Lihat --}}
                                <button type="button" class="btn-icon btn-view action-view" title="Lihat Detail"
                                    data-judul="{{ $p->judul }}"
                                    data-bidang="{{ $p->bidang->nama ?? '-' }}"
                                    data-tgl="{{ $p->created_at?->format('d M Y H:i') }}"
                                    data-deadline="{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('d M Y H:i') : '-' }}"
                                    data-status="{{ $statusFile }}"
                                    data-deskripsi="{{ $p->deskripsi ?? 'Tidak ada deskripsi' }}">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                {{-- Tombol Edit --}}
                                <button type="button" class="btn-icon btn-edit action-edit" title="Edit Data"
                                    data-id="{{ $p->id }}"
                                    data-judul="{{ $p->judul }}"
                                    data-bidang_id="{{ $p->bidang_id }}"
                                    data-tahun="{{ $p->tahun }}"
                                    data-deskripsi="{{ $p->deskripsi }}"
                                    data-due_date="{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('Y-m-d\TH:i') : '' }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                
                                {{-- Tombol Hapus --}}
                                <button type="button" class="btn-icon btn-delete action-delete" title="Hapus Data" data-id="{{ $p->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="permintaan-empty-row">
                        <td colspan="7" class="text-center">Belum ada data permintaan</td>
                    </tr>
                @endforelse
                <tr id="permintaanSearchEmpty" style="display:none;">
                    <td colspan="7" style="text-align: center; padding: 40px 20px; background: #fff;">
                        <i class="fa-solid fa-magnifying-glass" style="font-size:3rem; color:#d1d5db; margin-bottom: 12px; display:block;"></i>
                        <h4 style="margin: 0 0 6px 0; color: #374151; font-size: 1.1rem;">Hasil Pencarian Tidak Ditemukan</h4>
                        <p style="margin: 0; color: #9ca3af; font-size: 0.85rem;">Coba gunakan kata kunci pencarian yang lain.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL TAMBAH (Create) --}}
<div id="modalOverlay" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Tambah Permintaan</h3>
            <button type="button" id="closeModal" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('admin.pengajuan.store') }}" method="POST" id="createPermintaanForm">
                @csrf
                <div class="modal-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px;">
                    <div class="modal-field">
                        <label>Nama Dokumen <span style="color:red">*</span></label>
                        <input id="judul" class="modal-input validate-input" type="text"name="judul" data-type="judul"
                            placeholder="Contoh: Laporan Evaluasi PUG" />
                        <div class="invalid-feedback" id="err_judul"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Bidang Tujuan <span style="color:red">*</span></label>
                        <select id="bidang_id" class="modal-select validate-input" name="bidang_id" data-type="bidang" required>
                            <option value="" disabled selected>Pilih Bidang Tujuan</option>
                            <option value="all" style="font-weight: 700; color: #d4af37; background-color: #fcf9f2;">
                                Kirim ke Semua Bidang
                            </option>
                            @foreach($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_bidang_id"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Tahun <span style="color:red">*</span></label>
                        <select id="tahun" class="modal-select validate-input" name="tahun" data-type="tahun">
                            <option value="" disabled selected>Pilih Tahun</option>
                            @php $currYear = date('Y'); @endphp
                            @for($i = $currYear + 1; $i >= 2020; $i--)
                                <option value="{{ $i }}" {{ $i == $currYear ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <div class="invalid-feedback" id="err_tahun"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                </div>
                <div class="modal-grid" style="margin-top:12px;">
                    <div class="modal-field">
                        <label>Deskripsi (Opsional)</label>
                        <textarea id="deskripsi" class="modal-textarea validate-input" name="deskripsi" data-type="deskripsi"
                            placeholder="Tambahkan keterangan singkat jika perlu..."></textarea>
                        <div class="invalid-feedback" id="err_deskripsi"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Deadline <span style="color:red">*</span></label>
                        <input id="due_date" class="modal-input validate-input" type="datetime-local" name="due_date" data-type="deadline" />
                        <div class="invalid-feedback" id="err_due_date"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancelModal" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Submit Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT (Update) --}}
<div id="editModalOverlay" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Edit Permintaan</h3>
            <button type="button" id="closeEditModal" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="#" method="POST" id="editPermintaanForm">
                @csrf
                @method('PUT')
                <div class="modal-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px;">
                    <div class="modal-field">
                        <label>Nama Dokumen <span style="color:red">*</span></label>
                        <input id="edit_judul" class="modal-input validate-input" type="text" name="judul" required minlength="5" maxlength="50" />
                        <div class="invalid-feedback" id="err_judul"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Bidang <span style="color:red">*</span></label>
                        <select id="edit_bidang_id" class="modal-select" name="bidang_id" required>
                            <option value="" disabled selected>Pilih Bidang</option>
                            @foreach($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_bidang_id"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Tahun <span style="color:red">*</span></label>
                        <select id="edit_tahun" class="modal-select validate-input" name="tahun" data-type="tahun" required>
                            @php $currYear = date('Y'); @endphp
                            @for($i = $currYear + 1; $i >= 2020; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        <div class="invalid-feedback" id="err_tahun"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                </div>
                <div class="modal-grid" style="margin-top:12px;">
                    <div class="modal-field">
                        <label>Deskripsi (Opsional)</label>
                        <textarea id="edit_deskripsi" class="modal-textarea" name="deskripsi" maxlength="250"></textarea>
                        <div class="invalid-feedback" id="err_deskripsi"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Deadline <span style="color:red">*</span></label>
                        <input id="edit_due_date" class="modal-input" type="datetime-local" name="due_date" required />
                        <div class="invalid-feedback" id="err_due_date"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancelEditModal" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Update Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL LIHAT (Detail View) --}}
<div id="viewModalOverlay" class="modal-overlay">
    <div class="modal-card" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Detail Permintaan Dokumen</h3>
            <button type="button" id="closeViewModal" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <tr><th style="width: 35%; padding: 8px 0;">Nama Dokumen</th><td id="view_judul">: -</td></tr>
                <tr><th style="padding: 8px 0;">Bidang</th><td id="view_bidang">: -</td></tr>
                <tr><th style="padding: 8px 0;">Tanggal Dibuat</th><td id="view_tgl">: -</td></tr>
                <tr><th style="padding: 8px 0;">Deadline</th><td id="view_deadline">: -</td></tr>
                <tr><th style="padding: 8px 0;">Status</th><td id="view_status">: -</td></tr>
                <tr><th style="padding: 8px 0; vertical-align: top;">Deskripsi</th><td id="view_deskripsi" style="vertical-align: top;">: -</td></tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" id="cancelViewModal" class="btn-primary">Tutup</button>
        </div>
    </div>
</div>