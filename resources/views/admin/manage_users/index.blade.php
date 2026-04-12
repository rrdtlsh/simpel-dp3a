{{-- FILE: resources/views/admin/manage_users/index.blade.php --}}

<div class="content-header">
    <h2 style="margin:0; font-family:'Poppins',sans-serif; font-weight:700; color:#2c2c2c;">
        Kelola Akun Sub Bidang
    </h2>
    <button class="btn-primary-admin" id="btnTambahUser">
        <i class="fa-solid fa-user-plus"></i> Tambah Akun
    </button>
</div>

<div class="table-box">
    <table id="tabelUsers" class="admin-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Email</th>
                <th>Bidang</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $i => $user)
            <tr>
                <td style="text-align:center; font-weight:700">{{ $i + 1 }}</td>
                <td title="{{ $user->name }}">{{ $user->name }}</td>
                <td title="{{ $user->nip ?? '-' }}">{{ $user->nip ?? '-' }}</td>
                <td title="{{ $user->email }}">{{ $user->email }}</td>
                <td title="{{ $user->bidang?->nama ?? '-' }}">{{ $user->bidang?->nama ?? '-' }}</td>
                <td style="text-align:center">
                    <span class="badge-role {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td>
                    <div class="action-btn-group">
                        <button class="btn-icon btn-edit-user"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-nip="{{ $user->nip ?? '' }}"
                            data-email="{{ $user->email }}"
                            data-bidang="{{ $user->bidang_id ?? '' }}"
                            data-role="{{ $user->role }}"
                            title="Edit Akun">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn-icon btn-reset-pw"
                            data-url="{{ route('admin.manage_users.reset-password', $user->id) }}"
                            data-name="{{ $user->name }}"
                            title="Reset Password ke 123456">
                            <i class="fa-solid fa-key"></i>
                        </button>
                        @if($user->id !== auth()->id())
                        <button class="btn-icon btn-hapus-user"
                            data-url="{{ route('admin.manage_users.destroy', $user->id) }}"
                            data-name="{{ $user->name }}"
                            title="Hapus Akun">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#888; padding:24px">
                    <i class="fa-regular fa-user" style="font-size:1.5rem; color:#d1d5db; display:block; margin-bottom:6px;"></i>
                    Belum ada akun yang terdaftar.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- MODAL TAMBAH --}}
<div id="modalTambahUser" class="modal-overlay-admin" style="display:none" role="dialog" aria-modal="true">
    <div class="modal-card-admin">
        <div class="modal-header-admin">
            <h3><i class="fa-solid fa-user-plus"></i> Tambah Akun Baru</h3>
            <button class="modal-close-admin" data-close="modalTambahUser">&times;</button>
        </div>
        <form id="formTambahUser" action="{{ route('admin.manage_users.store') }}" method="POST" class="modal-form-admin" novalidate>
            @csrf
            <div class="form-group-admin">
                <label>Nama Lengkap <span class="required">*</span></label>
                <input type="text" id="addName" name="name" class="input-admin val-nama" maxlength="50" required placeholder="Masukkan nama lengkap">
                <div class="err-msg">Minimal 3 karakter, maksimal 50 karakter.</div>
            </div>
            <div class="form-group-admin">
                <label>NIP <span class="required">*</span></label>
                <input type="text" id="addNip" name="nip" class="input-admin val-nip" maxlength="18" required placeholder="18 digit angka NIP" inputmode="numeric">
                <div class="err-msg">NIP harus tepat 18 digit angka.</div>
            </div>
            <div class="form-group-admin">
                <label>Alamat Email <span class="required">*</span></label>
                <input type="email" id="addEmail" name="email" class="input-admin val-email" maxlength="50" required placeholder="contoh@email.com">
                <div class="err-msg">Format email tidak valid (maks. 50 karakter).</div>
            </div>
            <div class="form-group-admin">
                <label>Bidang</label>
                <select id="addBidang" name="bidang_id" class="input-admin select-bidang" data-target="addBidangBaru">
                    <option value="">-- Tidak Ada --</option>
                    @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}">{{ $bidang->nama }}</option>
                    @endforeach
                    <option value="baru" style="font-weight:bold;color:#067fb2;">+ Tambah Bidang Baru</option>
                </select>
                <div id="addBidangBaru" class="input-bidang-baru" style="display:none;margin-top:8px">
                    <input type="text" name="nama_bidang_baru" class="input-admin" maxlength="50" placeholder="Nama bidang baru (maks. 50 karakter)">
                </div>
            </div>
            <div class="form-group-admin">
                <label>Peran (Role) <span class="required">*</span></label>
                <select id="addRole" name="role" class="input-admin" required>
                    <option value="user">User / Bidang</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            {{--
                Password default 123456 — admin bisa lihat/ubah sebelum simpan.
                Nilai ini diisi otomatis agar akun baru langsung punya password standar.
                Admin dapat mengubahnya atau membiarkan default sebelum menekan Simpan.
            --}}
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:.78rem;color:#92400e;">
                <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
                Password default <strong>123456</strong>. Ubah jika diperlukan sebelum menyimpan.
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group-admin">
                    <label>Password <span class="required">*</span></label>
                    <div class="input-pw-wrap">
                        <input type="password" id="pwAdd" name="password"
                            class="input-admin val-pw"
                            maxlength="18" required
                            value="123456"
                            autocomplete="new-password">
                        <button type="button" class="btn-toggle-pw" data-target="pwAdd"><i class="fa-regular fa-eye"></i></button>
                    </div>
                    <div class="err-msg">Minimal 6, maksimal 18 karakter.</div>
                </div>
                <div class="form-group-admin">
                    <label>Konfirmasi <span class="required">*</span></label>
                    <div class="input-pw-wrap">
                        <input type="password" id="pwAddConf" name="password_confirmation"
                            class="input-admin val-pw-conf"
                            maxlength="18" required
                            data-match="pwAdd"
                            value="123456"
                            autocomplete="new-password">
                        <button type="button" class="btn-toggle-pw" data-target="pwAddConf"><i class="fa-regular fa-eye"></i></button>
                    </div>
                    <div class="err-msg">Password tidak cocok.</div>
                </div>
            </div>
            <div class="modal-footer-admin">
                <button type="button" class="btn-secondary-admin" data-close="modalTambahUser">Batal</button>
                <button type="submit" class="btn-primary-admin" id="btnSimpanAdd">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modalEditUser" class="modal-overlay-admin" style="display:none" role="dialog" aria-modal="true">
    <div class="modal-card-admin">
        <div class="modal-header-admin">
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Akun</h3>
            <button class="modal-close-admin" data-close="modalEditUser">&times;</button>
        </div>
        <form id="formEditUser" method="POST" class="modal-form-admin" novalidate>
            @csrf
            @method('PUT')

            {{--
                Snapshot: menyimpan data awal dalam JSON.
                JS membandingkan nilai form saat submit vs snapshot ini
                untuk mendeteksi apakah ada perubahan.
            --}}
            <input type="hidden" id="editSnapshot">

            <div class="no-change-hint" id="noChangeHint">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Tidak ada perubahan yang terdeteksi. Ubah setidaknya satu field untuk menyimpan.
            </div>

            <div class="form-group-admin">
                <label>Nama Lengkap <span class="required">*</span></label>
                <input type="text" id="editName" name="name" class="input-admin val-nama" maxlength="50" required>
                <div class="err-msg">Minimal 3 karakter, maksimal 50 karakter.</div>
            </div>
            <div class="form-group-admin">
                <label>NIP <span class="required">*</span></label>
                <input type="text" id="editNip" name="nip" class="input-admin val-nip" maxlength="18" required inputmode="numeric">
                <div class="err-msg">NIP harus tepat 18 digit angka.</div>
            </div>
            <div class="form-group-admin">
                <label>Alamat Email <span class="required">*</span></label>
                <input type="email" id="editEmail" name="email" class="input-admin val-email" maxlength="50" required>
                <div class="err-msg">Format email tidak valid (maks. 50 karakter).</div>
            </div>
            <div class="form-group-admin">
                <label>Bidang</label>
                <select id="editBidang" name="bidang_id" class="input-admin select-bidang" data-target="editBidangBaru">
                    <option value="">-- Tidak Ada --</option>
                    @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}">{{ $bidang->nama }}</option>
                    @endforeach
                    <option value="baru" style="font-weight:bold;color:#067fb2;">+ Tambah Bidang Baru</option>
                </select>
                <div id="editBidangBaru" class="input-bidang-baru" style="display:none;margin-top:8px">
                    <input type="text" name="nama_bidang_baru" class="input-admin" maxlength="50" placeholder="Nama bidang baru">
                </div>
            </div>
            <div class="form-group-admin">
                <label>Peran (Role) <span class="required">*</span></label>
                <select id="editRole" name="role" class="input-admin" required>
                    <option value="user">User / Bidang</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            {{--
                Kolom ubah password DIHAPUS dari form edit.
                Admin hanya bisa mereset password via tombol 🔑 di tabel
                yang akan mengeset password kembali ke "123456".
            --}}
            <div class="modal-footer-admin">
                <button type="button" class="btn-secondary-admin" data-close="modalEditUser">Batal</button>
                <button type="submit" class="btn-primary-admin" id="btnSimpanEdit">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="/css/admin-manage-users.css">
<script src="/js/admin-manage-users.js"></script>
