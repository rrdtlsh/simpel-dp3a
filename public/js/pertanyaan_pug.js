document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburger");

    const modalTambah = document.getElementById("modal");
    const modalEdit   = document.getElementById("modalEdit");

    const btnTambah   = document.getElementById("btnTambah");

    const editNama    = document.getElementById("editNama");
    const editBidang  = document.getElementById("editBidang");
    const editTanggal = document.getElementById("editTanggal");

    // ===== SIDEBAR =====
    hamburger?.addEventListener("click", () => {
        sidebar.classList.toggle("hide");
    });

    // ===== TAMBAH =====
    btnTambah?.addEventListener("click", () => {
        modalTambah.style.display = "block";
    });

    // ===== 🔥 EVENT GLOBAL (KUNCI UTAMA) =====
    document.addEventListener("click", function (e) {

        // ===== EDIT =====
        const btnEdit = e.target.closest(".btn-edit");
        if (btnEdit) {
            e.preventDefault();

            editNama.value    = btnEdit.dataset.nama;
            editBidang.value  = btnEdit.dataset.bidang;
            editTanggal.value = btnEdit.dataset.tanggal;

            modalEdit.style.display = "block";
            return;
        }

        // ===== CLOSE TAMBAH =====
        if (e.target.id === "closeModal") {
            modalTambah.style.display = "none";
        }

        // ===== CLOSE EDIT =====
        if (e.target.id === "closeEdit") {
            modalEdit.style.display = "none";
        }

        // ===== CLICK OUTSIDE =====
        if (e.target === modalTambah) modalTambah.style.display = "none";
        if (e.target === modalEdit) modalEdit.style.display = "none";
    });
});
