<div class="content">
    <div class="page-header">
        <h2>Permintaan Dokumen</h2>
        <button type="button" id="openModal" class="btn-primary">
            + Tambah Permintaan
        </button>
    </div>

    <div class="table-box">
        <table>
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
                    <tr>
                        <td style="font-weight:700;">{{ $index + 1 }}</td>
                        <td>{{ $p->judul }}</td>
                        <td>{{ $p->bidang->nama ?? '-' }}</td>
                        <td>{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('Y-m-d H:i') : '-' }}</td>
                        <td>
                            @if($p->status === 'open')
                                <span class="status-wait">Menunggu</span>
                            @else
                                <span class="status-ok">Selesai</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                {{-- Tombol Lihat (Kirim data lewat atribut data-*) --}}
                                <button type="button" class="btn-icon btn-view action-view" title="Lihat Detail"
                                    data-judul="{{ $p->judul }}"
                                    data-bidang="{{ $p->bidang->nama ?? '-' }}"
                                    data-tgl="{{ $p->created_at?->format('d M Y H:i') }}"
                                    data-deadline="{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('d M Y H:i') : '-' }}"
                                    data-status="{{ $p->status === 'open' ? 'Menunggu' : 'Selesai' }}"
                                    data-deskripsi="{{ $p->deskripsi ?? 'Tidak ada deskripsi' }}">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                {{-- Tombol Edit --}}
                                <button type="button" class="btn-icon btn-edit action-edit" title="Edit Data"
                                    data-id="{{ $p->id }}"
                                    data-judul="{{ $p->judul }}"
                                    data-bidang_id="{{ $p->bidang_id }}"
                                    data-deskripsi="{{ $p->deskripsi }}"
                                    data-due_date="{{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('Y-m-d\TH:i') : '' }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                {{-- Tombol Hapus --}}
                                <button type="button" class="btn-icon btn-delete action-delete" title="Hapus Data"
                                    data-id="{{ $p->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data permintaan</td>
                    </tr>
                @endforelse
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
                <div class="modal-grid modal-grid-2">
                    <div class="modal-field">
                        <label>Nama Dokumen <span style="color:red">*</span></label>
                        <input id="judul" class="modal-input validate-input" type="text" name="judul" data-type="judul" placeholder="Contoh: Laporan Evaluasi PUG 2025" />
                        <div class="invalid-feedback" id="err_judul"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Bidang <span style="color:red">*</span></label>
                        <select id="bidang_id" class="modal-select validate-input" name="bidang_id" data-type="bidang">
                            <option value="" disabled selected>Pilih Bidang</option>
                            @foreach($bidangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_bidang_id"><i class="fa-solid fa-circle-exclamation"></i> <span></span></div>
                    </div>
                </div>
                <div class="modal-grid" style="margin-top:12px;">
                    <div class="modal-field">
                        <label>Deskripsi (Opsional)</label>
                        <textarea id="deskripsi" class="modal-textarea validate-input" name="deskripsi" data-type="deskripsi" placeholder="Tambahkan keterangan singkat jika perlu..."></textarea>
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
            {{-- Action URL akan diisi oleh JavaScript --}}
            <form action="#" method="POST" id="editPermintaanForm">
                @csrf
                @method('PUT')
                <div class="modal-grid modal-grid-2">
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