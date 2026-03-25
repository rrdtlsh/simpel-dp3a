<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px;">
    <h2>Permintaan Dokumen</h2>

    <button
        type="button"
        id="openModal"
        style="background: var(--biru-utama); color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; font-weight:700;"
    >
        + Tambah Permintaan
    </button>
</div>

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">No</th>
                <th style="text-align:left;">Nama Dokumen</th>
                <th style="text-align:left;">Bidang</th>
                <th style="text-align:left;">Tanggal Dibuat</th>
                <th style="text-align:left;">Deadline</th>
                <th style="text-align:left;">Status</th>
                <th style="text-align:left;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $index => $p)
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
                    <td>-</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Belum ada data permintaan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-card {
        width: 100%;
        max-width: 720px;
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--abu-border);
        box-shadow: 0 12px 30px rgba(0,0,0,0.18);
        overflow: hidden;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        border-bottom: 1px solid var(--abu-border);
        background: #f9fafb;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #2c2c2c;
    }

    .modal-close {
        border: none;
        background: transparent;
        font-size: 22px;
        cursor: pointer;
        line-height: 1;
        padding: 2px 8px;
        color: #2c2c2c;
    }

    .modal-body {
        padding: 16px;
    }

    .modal-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    @media (min-width: 768px) {
        .modal-grid-2 {
            grid-template-columns: 1fr 1fr;
        }
    }

    .modal-field label {
        display: block;
        font-weight: 800;
        margin-bottom: 6px;
        font-size: 13px;
        color: #2c2c2c;
    }

    .modal-input, .modal-textarea, .modal-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--abu-border);
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
        outline: none;
    }

    .modal-input:focus, .modal-textarea:focus, .modal-select:focus {
        border-color: var(--biru-utama);
        box-shadow: 0 0 0 3px rgba(6, 127, 178, 0.15);
    }

    .modal-textarea {
        min-height: 110px;
        resize: vertical;
    }

    .modal-footer {
        padding: 14px 16px;
        border-top: 1px solid var(--abu-border);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #fff;
    }

    #permintaanErrors {
        display: none;
        margin-bottom: 12px;
        padding: 12px;
        border: 1px solid #fecaca;
        background: #fee2e2;
        border-radius: 8px;
        color: #991b1b;
        font-weight: 600;
        font-size: 13px;
    }
</style>

<div id="modalOverlay" class="modal-overlay">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Permintaan</h3>
            <button type="button" id="closeModal" class="modal-close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-body">
            <div id="permintaanErrors"></div>

            <form
                action="{{ route('admin.pengajuan.store') }}"
                method="POST"
                id="createPermintaanForm"
            >
                @csrf

                <div class="modal-grid modal-grid-2">
                    <div class="modal-field">
                        <label for="judul">Nama Dokumen</label>
                        <input
                            id="judul"
                            class="modal-input"
                            type="text"
                            name="judul"
                            value="{{ old('judul') }}"
                            required
                        />
                    </div>

                    <div class="modal-field">
                        <label for="bidang_id">Bidang</label>
                        @if ($bidangs->isEmpty())
                            @dd($bidangs)
                        @endif
                        <select id="bidang_id" class="modal-select" name="bidang_id" required>
                            <option value="" disabled {{ old('bidang_id') ? '' : 'selected' }}>Pilih Bidang</option>
                            @foreach($bidangs as $b)
                                <option value="{{ $b->id }}" {{ (string) old('bidang_id') === (string) $b->id ? 'selected' : '' }}>
                                    {{ $b->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-grid" style="margin-top:12px;">
                    <div class="modal-field">
                        <label for="deskripsi">Deskripsi (optional)</label>
                        <textarea
                            id="deskripsi"
                            class="modal-textarea"
                            name="deskripsi"
                            maxlength="500"
                        >{{ old('deskripsi') }}</textarea>
                    </div>

                    <div class="modal-field">
                        <label for="due_date">Deadline (datetime-local)</label>
                        <input
                            id="due_date"
                            class="modal-input"
                            type="datetime-local"
                            name="due_date"
                            required
                            value="{{ old('due_date') }}"
                        />
                        <div style="margin-top:6px; font-size:12px; color:#666;">
                            Deadline harus di masa depan (minimal 60 detik dari sekarang).
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        id="cancelModal"
                        style="background:transparent; border:1px solid var(--abu-border); padding:10px 14px; border-radius:8px; cursor:pointer; font-weight:700;"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        style="background: var(--biru-utama); color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; font-weight:800;"
                    >
                        Submit Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

