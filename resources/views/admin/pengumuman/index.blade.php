
{{-- ── CSS & JS: inject ke layout via @stack (jika layout mendukung) ─── --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pengumuman.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/pengumuman.js') }}" defer></script>
@endpush

{{-- ════════════════════════════════════════════════════════════════════
     HEADER
     ════════════════════════════════════════════════════════════════════ --}}
<div class="pgm-header">
    <h2 class="pgm-title">
        Kelola Pengumuman & Informasi
    </h2>
    
    {{-- Sembunyikan tombol jika data sudah 6 --}}
    @if($pengumumans->count() < 6)
        <button class="pgm-btn-tambah" id="pgmBtnTambah">
            <i class="fa-solid fa-plus"></i> Tambah Pengumuman
        </button>
    @else
        <button class="pgm-btn-tambah" style="background: #94a3b8; cursor: not-allowed;" title="Kuota Penuh" disabled>
            <i class="fa-solid fa-lock"></i> Kuota Penuh
        </button>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════════
     TABEL
     ════════════════════════════════════════════════════════════════════ --}}
<div class="pgm-table-wrapper">
    <table class="pgm-table">
        <thead>
            <tr>
                <th style="width:48px">No</th>
                <th>Judul & Konten</th>
                <th style="width:130px" class="center">Badge</th>
                <th style="width:110px" class="center">Status</th>
                <th style="width:110px" class="center">Tanggal</th>
                <th style="width:110px" class="center">Aksi</th>
            </tr>
        </thead>
        <tbody id="pgmTbody">
            @forelse($pengumumans as $i => $item)
            <tr id="row-pengumuman-{{ $item->id }}">
                <td class="pgm-td-no">{{ $i + 1 }}</td>
                <td>
                    <div class="pgm-td-judul">{{ $item->judul }}</div>
                    <div class="pgm-td-konten-hint">{{ Str::limit($item->konten, 60) }}</div>
                </td>
                <td class="center">
                    <span class="pgm-badge {{ $item->badge_color }}">{{ $item->badge_label }}</span>
                </td>
                <td class="center">
                    <button class="pgm-btn-toggle"
                        data-id="{{ $item->id }}"
                        data-url="{{ route('admin.pengumuman.toggle-status', $item->id) }}"
                        data-active="{{ $item->is_active ? '1' : '0' }}">
                        @if($item->is_active)
                            <span class="pgm-badge-aktif">Aktif</span>
                        @else
                            <span class="pgm-badge-nonaktif">Non-aktif</span>
                        @endif
                    </button>
                </td>
                <td class="pgm-td-date">{{ $item->created_at->format('d M Y') }}</td>
                <td class="center">
                    <div class="pgm-action-group">
                        <button class="pgm-btn-icon pgm-btn-edit"
                            data-id="{{ $item->id }}"
                            data-judul="{{ $item->judul }}"
                            data-konten="{{ e($item->konten) }}"
                            data-badge-label="{{ $item->badge_label }}"
                            data-badge-color="{{ $item->badge_color }}"
                            data-is-active="{{ $item->is_active ? '1' : '0' }}"
                            data-gambar-url="{{ $item->gambar_url }}"
                            data-url="{{ route('admin.pengumuman.update', $item->id) }}"
                            title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="pgm-btn-icon pgm-btn-hapus"
                            data-id="{{ $item->id }}"
                            data-judul="{{ $item->judul }}"
                            data-url="{{ route('admin.pengumuman.destroy', $item->id) }}"
                            title="Hapus">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr id="pgmEmptyRow">
                <td colspan="6" class="pgm-empty">
                    <i class="fa-regular fa-bell-slash"></i>
                    <p>Belum ada pengumuman.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>


{{-- ════════════════════════════════════════════════════════════════════
     MODAL TAMBAH PENGUMUMAN
     ════════════════════════════════════════════════════════════════════ --}}
<div id="pgmModalTambah" class="pgm-modal-overlay">
    <div class="pgm-modal-card">

        <div class="pgm-modal-header">
            <h3><i class="fa-solid fa-bullhorn"></i> Tambah Pengumuman</h3>
            <button class="pgm-modal-close" data-pgm-close="pgmModalTambah" title="Tutup">&times;</button>
        </div>

        <form id="pgmFormTambah"
              data-action="{{ route('admin.pengumuman.store') }}"
              enctype="multipart/form-data"
              novalidate>
            @csrf

            <div class="pgm-modal-body">

                {{-- Judul --}}
                <div class="pgm-form-group">
                    <label for="tambahJudul">
                        Judul Pengumuman <span class="req">*</span>
                    </label>
                    <input
                        type="text"
                        id="tambahJudul"
                        name="judul"
                        class="pgm-input"
                        maxlength="{{ \App\Models\Pengumuman::JUDUL_MAX ?? 100 }}"
                        placeholder="Masukkan judul pengumuman"
                        autocomplete="off"
                        required>
                    <div class="pgm-char-counter" id="tambahJudulCounter">0 / 100</div>
                    <div class="pgm-field-error" id="tambahJudulError"></div>
                </div>

                {{-- Konten --}}
                <div class="pgm-form-group">
                    <label for="tambahKonten">
                        Konten / Isi <span class="req">*</span>
                    </label>
                    <textarea
                        id="tambahKonten"
                        name="konten"
                        class="pgm-textarea"
                        maxlength="1000"
                        placeholder="Tulis isi pengumuman..."
                        required></textarea>
                    <div class="pgm-char-counter" id="tambahKontenCounter">0 / 1000</div>
                    <div class="pgm-field-error" id="tambahKontenError"></div>
                </div>

                {{-- Badge Label & Warna --}}
                <div class="pgm-form-row">
                    <div class="pgm-form-group">
                        <label for="tambahBadgeLabel">
                            Label Badge <span class="req">*</span>
                        </label>
                        <input
                            type="text"
                            id="tambahBadgeLabel"
                            name="badge_label"
                            class="pgm-input"
                            maxlength="50"
                            placeholder="Penting / Sistem / Kegiatan"
                            required>
                        <div class="pgm-char-counter" id="tambahBadgeLabelCounter">0 / 50</div>
                        <div class="pgm-field-error" id="tambahBadgeLabelError"></div>
                    </div>
                    <div class="pgm-form-group">
                        <label for="tambahBadgeColor">
                            Warna Badge <span class="req">*</span>
                        </label>
                        <select id="tambahBadgeColor" name="badge_color" class="pgm-select" required>
                            <option value="blue">🔵 Biru</option>
                            <option value="green">🟢 Hijau</option>
                            <option value="red">🔴 Merah</option>
                        </select>
                        <div class="pgm-field-error" id="tambahBadgeColorError"></div>
                    </div>
                </div>

                {{-- Gambar --}}
                <div class="pgm-form-group">
                    <label for="tambahGambar">
                        Gambar <span class="req">*</span>
                        <small style="font-weight:400; color:#94a3b8">(Maks. 2 MB — JPG/JPEG/PNG)</small>
                    </label>
                    <input
                        type="file"
                        id="tambahGambar"
                        name="gambar"
                        class="pgm-file-input"
                        accept=".jpg,.jpeg,.png"
                        required>
                    <div class="pgm-field-error" id="tambahGambarError"></div>
                </div>

                {{-- Status Aktif --}}
                <div class="pgm-form-group">
                    <label class="pgm-checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Tampilkan di Halaman Publik (Aktif)
                    </label>
                </div>

            </div>{{-- /pgm-modal-body --}}

            <div class="pgm-modal-footer">
                <button type="button" class="pgm-btn-batal" data-pgm-close="pgmModalTambah">
                    Batal
                </button>
                <button type="submit" class="pgm-btn-simpan" id="pgmBtnSimpan">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>

    </div>
</div>


{{-- ════════════════════════════════════════════════════════════════════
     MODAL EDIT PENGUMUMAN
     ════════════════════════════════════════════════════════════════════ --}}
<div id="pgmModalEdit" class="pgm-modal-overlay">
    <div class="pgm-modal-card">

        <div class="pgm-modal-header">
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Pengumuman</h3>
            <button class="pgm-modal-close" data-pgm-close="pgmModalEdit" title="Tutup">&times;</button>
        </div>

        {{--
            data-action dan data-edit-id diisi via JavaScript saat tombol edit diklik.
            _method PUT disertakan sebagai hidden input untuk Laravel method spoofing.
        --}}
        <form id="pgmFormEdit"
              data-action=""
              data-edit-id=""
              enctype="multipart/form-data"
              novalidate>
            @csrf
            <input type="hidden" name="_method" value="PUT">

            <div class="pgm-modal-body">

                {{-- Judul --}}
                <div class="pgm-form-group">
                    <label for="editJudul">
                        Judul Pengumuman <span class="req">*</span>
                    </label>
                    <input
                        type="text"
                        id="editJudul"
                        name="judul"
                        class="pgm-input"
                        maxlength="100"
                        autocomplete="off"
                        required>
                    <div class="pgm-char-counter" id="editJudulCounter">0 / 100</div>
                    <div class="pgm-field-error" id="editJudulError"></div>
                </div>

                {{-- Konten --}}
                <div class="pgm-form-group">
                    <label for="editKonten">
                        Konten / Isi <span class="req">*</span>
                    </label>
                    <textarea
                        id="editKonten"
                        name="konten"
                        class="pgm-textarea"
                        maxlength="1000"
                        required></textarea>
                    <div class="pgm-char-counter" id="editKontenCounter">0 / 1000</div>
                    <div class="pgm-field-error" id="editKontenError"></div>
                </div>

                {{-- Badge --}}
                <div class="pgm-form-row">
                    <div class="pgm-form-group">
                        <label for="editBadgeLabel">
                            Label Badge <span class="req">*</span>
                        </label>
                        <input
                            type="text"
                            id="editBadgeLabel"
                            name="badge_label"
                            class="pgm-input"
                            maxlength="50"
                            required>
                        <div class="pgm-char-counter" id="editBadgeLabelCounter">0 / 50</div>
                        <div class="pgm-field-error" id="editBadgeLabelError"></div>
                    </div>
                    <div class="pgm-form-group">
                        <label for="editBadgeColor">
                            Warna Badge <span class="req">*</span>
                        </label>
                        <select id="editBadgeColor" name="badge_color" class="pgm-select" required>
                            <option value="blue">🔵 Biru</option>
                            <option value="green">🟢 Hijau</option>
                            <option value="red">🔴 Merah</option>
                        </select>
                        <div class="pgm-field-error" id="editBadgeColorError"></div>
                    </div>
                </div>

                {{-- Gambar --}}
                <div class="pgm-form-group">
                    <img id="editGambarImg" class="pgm-img-preview" src="" alt="Gambar saat ini">
                    <label for="editGambar">
                        Ganti Gambar
                        <small style="font-weight:400; color:#94a3b8">(Kosongkan jika tidak diganti — Maks. 2 MB, JPG/JPEG/PNG)</small>
                    </label>
                    <input
                        type="file"
                        id="editGambar"
                        name="gambar"
                        class="pgm-file-input"
                        accept=".jpg,.jpeg,.png">
                    <div class="pgm-field-error" id="editGambarError"></div>
                </div>

                {{-- Status Aktif --}}
                <div class="pgm-form-group">
                    <label class="pgm-checkbox-label">
                        <input type="checkbox" id="editIsActive" name="is_active" value="1">
                        Tampilkan di Halaman Publik (Aktif)
                    </label>
                </div>

            </div>{{-- /pgm-modal-body --}}

            <div class="pgm-modal-footer">
                <button type="button" class="pgm-btn-batal" data-pgm-close="pgmModalEdit">
                    Batal
                </button>
                <button type="submit" class="pgm-btn-simpan" id="pgmBtnUpdate">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>
<link rel="stylesheet" href="/css/pengumuman.css">
<script src="/js/pengumuman.js"></script>