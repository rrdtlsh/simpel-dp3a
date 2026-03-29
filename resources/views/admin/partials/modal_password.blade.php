{{-- Modal Ubah Password --}}
<div id="modalUbahPassword" class="pw-modal-overlay">
    <div class="pw-modal-card">
        
        <div class="pw-modal-header">
            <div class="pw-header-title-box">
                <div class="pw-header-icon">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="pw-header-title">Ubah Password</h3>
                    <p class="pw-header-subtitle">Gunakan kombinasi yang kuat</p>
                </div>
            </div>
            <button type="button" id="closeModalPassword" class="pw-btn-close">&times;</button>
        </div>
        
        <div class="pw-modal-body">
            <form id="formUbahPassword" novalidate>
                @csrf
                
                <div class="pw-form-group">
                    <label class="pw-form-label">
                        Password Saat Ini <span class="pw-required-mark">*</span>
                    </label>
                    <div class="pw-input-wrapper">
                        <input type="password" name="current_password" id="pwCurrentAdmin" class="pw-input" placeholder="Masukkan password lama" required>
                        <button type="button" onclick="togglePw('pwCurrentAdmin',this)" class="pw-toggle-btn">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p id="err_current_password" class="pw-field-err"></p>
                </div>
                
                <div class="pw-form-group">
                    <label class="pw-form-label">
                        Password Baru <span class="pw-required-mark">*</span>
                    </label>
                    <div class="pw-input-wrapper">
                        <input type="password" name="new_password" id="pwNewAdmin" class="pw-input" placeholder="Minimal 6 karakter" required minlength="6">
                        <button type="button" onclick="togglePw('pwNewAdmin',this)" class="pw-toggle-btn">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p id="err_new_password" class="pw-field-err"></p>
                </div>
                
                <div class="pw-form-group-last">
                    <label class="pw-form-label">
                        Konfirmasi Password Baru <span class="pw-required-mark">*</span>
                    </label>
                    <div class="pw-input-wrapper">
                        <input type="password" name="new_password_confirmation" id="pwConfirmAdmin" class="pw-input" placeholder="Ulangi password baru" required minlength="6">
                        <button type="button" onclick="togglePw('pwConfirmAdmin',this)" class="pw-toggle-btn">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p id="err_new_password_confirmation" class="pw-field-err"></p>
                </div>
                
                <div class="pw-modal-actions">
                    <button type="button" id="cancelModalPassword" class="pw-btn-cancel">Batal</button>
                    <button type="submit" id="btnSimpanPassword" class="pw-btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Password
                    </button>
                </div>
                
            </form>
        </div>
    </div>
</div>