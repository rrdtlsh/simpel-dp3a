<div class="content">
    <div class="page-header">
        <h2>Permintaan Dokumen</h2>
        <button type="button" id="openModal" class="btn-primary">
            + Tambah Permintaan
        </button>
    </div>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <div class="alert-title">Terjadi kesalahan</div>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

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
                        {{-- Container Tombol Aksi --}}
                        <div class="action-buttons">
                            <a href="#" class="btn-icon btn-view" title="Lihat Detail">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="#" class="btn-icon btn-edit" title="Edit Data">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="#" method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-delete" title="Hapus Data">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
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

        <div style="margin-top:16px;">
            {{ $pengajuans->links() }}
        </div>
    </div>
</div>

<div id="modalOverlay" class="modal-overlay {{ $errors->any() ? 'open' : '' }}">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Permintaan</h3>
            <button type="button" id="closeModal" class="modal-close" aria-label="Close">&times;</button>
        </div>

        <div class="modal-body">
            <form action="{{ route('admin.pengajuan.store') }}" method="POST" id="createPermintaanForm">
                @csrf
                <div class="modal-grid modal-grid-2">
                    <div class="modal-field">
                        <label for="judul">Nama Dokumen</label>
                        <input id="judul" class="modal-input" type="text" name="judul" value="{{ old('judul') }}" required minlength="5" maxlength="100" />
                    </div>

                    <div class="modal-field">
                        <label for="bidang_id">Bidang</label>
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
                        <textarea id="deskripsi" class="modal-textarea" name="deskripsi" maxlength="500">{{ old('deskripsi') }}</textarea>
                    </div>

                    <div class="modal-field">
                        <label for="due_date">Deadline (datetime-local)</label>
                        <input id="due_date" class="modal-input" type="datetime-local" name="due_date" required value="{{ old('due_date') }}" />
                        <div style="margin-top:6px; font-size:12px; color:#666;">
                            Deadline tidak boleh sebelum hari ini.
                        </div>
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
@endsection