{{-- Modal Ubah Password --}}
<div id="modalUbahPassword" class="pw-modal-overlay">
    <div class="pw-modal-card">
        <div class="pw-modal-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div class="pw-header-icon">
                    <i class="fa-solid fa-key" style="color:#fff; font-size:15px;"></i>
                </div>
                <div>
                    <h3 style="margin:0; color:#fff; font-size:1rem; font-weight:700;">Ubah Password</h3>
                    <p style="margin:0; color:rgba(255,255,255,.75); font-size:.72rem;">Gunakan kombinasi yang kuat</p>
                </div>
            </div>
            <button type="button" id="closeModalPassword" class="pw-btn-close">&times;</button>
        </div>
        <div style="padding:24px;">
            <form id="formUbahPassword" novalidate>
                @csrf
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:6px;">
                        Password Saat Ini <span style="color:#ef4444">*</span>
                    </label>
                    <div class="pw-input-wrapper">
                        <input type="password" name="current_password" id="pwCurrentAdmin"
                            class="pw-input" placeholder="Masukkan password lama" required>
                        <button type="button" onclick="togglePw('pwCurrentAdmin',this)" class="pw-toggle-btn">
                            <i class="fa-regular fa-eye" style="font-size:14px;"></i>
                        </button>
                    </div>
                    <p id="err_current_password" class="pw-field-err"></p>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:6px;">
                        Password Baru <span style="color:#ef4444">*</span>
                    </label>
                    <div class="pw-input-wrapper">
                        <input type="password" name="new_password" id="pwNewAdmin"
                            class="pw-input" placeholder="Minimal 6 karakter" required minlength="6">
                        <button type="button" onclick="togglePw('pwNewAdmin',this)" class="pw-toggle-btn">
                            <i class="fa-regular fa-eye" style="font-size:14px;"></i>
                        </button>
                    </div>
                    <p id="err_new_password" class="pw-field-err"></p>
                </div>
                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:6px;">
                        Konfirmasi Password Baru <span style="color:#ef4444">*</span>
                    </label>
                    <div class="pw-input-wrapper">
                        <input type="password" name="new_password_confirmation" id="pwConfirmAdmin"
                            class="pw-input" placeholder="Ulangi password baru" required minlength="6">
                        <button type="button" onclick="togglePw('pwConfirmAdmin',this)" class="pw-toggle-btn">
                            <i class="fa-regular fa-eye" style="font-size:14px;"></i>
                        </button>
                    </div>
                    <p id="err_new_password_confirmation" class="pw-field-err"></p>
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" id="cancelModalPassword" class="pw-btn-cancel">Batal</button>
                    <button type="submit" id="btnSimpanPassword" class="pw-btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>