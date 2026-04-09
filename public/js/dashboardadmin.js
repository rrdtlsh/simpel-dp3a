document.addEventListener("DOMContentLoaded", function () {
    const hamburger = document.getElementById("hamburger");
    const sidebar = document.getElementById("sidebar");
    const menuItems = document.querySelectorAll("#menu li");

    hamburger.addEventListener("click", function () {
        sidebar.classList.toggle("hide");
    });

    menuItems.forEach(item => {
        item.addEventListener("click", () => {
            menuItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
        });
    });
});

/* ==========================================================================
   SPLASH SCREEN LOADER LOGIC
   ========================================================================== */
window.addEventListener('load', function () {
    var loader = document.getElementById('loader');
    if (loader) {
        setTimeout(function () {
            loader.style.opacity = '0';
            setTimeout(function () {
                loader.style.display = 'none';
            }, 500);
        }, 300);
    }
});

/* ==========================================================================
   REAL-TIME VALIDATION UBAH PASSWORD (SEMUA KOLOM)
   ========================================================================== */
document.addEventListener("input", function (e) {
    const id = e.target.id;

    if (id === "pwCurrentAdmin" || id === "pwNewAdmin" || id === "pwConfirmAdmin") {
        let val = e.target.value;
        let errEl = document.getElementById("err_" + e.target.name);

        // Reset tampilan setiap ada ketikan baru
        e.target.classList.remove("pw-field-error");
        errEl.textContent = "";
        errEl.style.color = "#ef4444"; // Set standar warna teks menjadi Merah

        if (val.length > 0) {

            // 1. Peringatan jika kurang dari 6 karakter
            if (val.length < 6) {
                errEl.textContent = "Terlalu pendek! Minimal 6 karakter.";
                e.target.classList.add("pw-field-error");
            }
            // 2. Info peringatan jika ketikan sudah mentok 18 karakter
            else if (val.length === 18) {
                errEl.textContent = "Batas maksimal 18 karakter tercapai.";
                errEl.style.color = "#f59e0b"; // Warna Oren/Kuning Peringatan
            }

            // 3. Pengecekan khusus kecocokan Konfirmasi Password
            if (id === "pwConfirmAdmin") {
                let newPw = document.getElementById("pwNewAdmin").value;
                if (val !== newPw) {
                    // Jika tidak sama, paksa error merah (bahkan jika panjangnya sudah 18)
                    errEl.textContent = "Konfirmasi password belum cocok.";
                    errEl.style.color = "#ef4444";
                    e.target.classList.add("pw-field-error");
                } else if (val.length >= 6) {
                    // Jika sama persis dan minimal karakter terpenuhi
                    errEl.textContent = "Password cocok! ✔";
                    errEl.style.color = "#10b981"; // Warna Hijau Sukses
                    e.target.classList.remove("pw-field-error");
                }
            }

            // 4. Trigger pengecekan otomatis ke kotak konfirmasi jika password baru diubah
            if (id === "pwNewAdmin") {
                let confirmInput = document.getElementById("pwConfirmAdmin");
                if (confirmInput.value.length > 0) {
                    confirmInput.dispatchEvent(new Event('input'));
                }
            }
        }
    }
});
