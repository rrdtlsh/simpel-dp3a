document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburger");
    const modal = document.getElementById("modalUnggah");
    const btnTambah = document.getElementById("btnTambahUnggah");
    const closeModal = document.getElementById("closeModalUnggah");

    // Sidebar Toggle
    hamburger.onclick = () => sidebar.classList.toggle("hide");

    // Modal Control
    if (btnTambah) {
        btnTambah.onclick = () => modal.style.display = "block";
    }

    if (closeModal) {
        closeModal.onclick = () => modal.style.display = "none";
    }

    window.onclick = (e) => {
        if (e.target === modal) modal.style.display = "none";
    };

    // Aksi Tombol di Tabel
    document.addEventListener("click", function (e) {
        // Tombol Lihat
        if (e.target.closest(".btn-view")) {
            alert("Membuka tampilan dokumen...");
        }

        // Tombol Edit
        if (e.target.closest(".btn-edit")) {
            alert("Membuka form edit dokumen...");
        }

        // Tombol Hapus
        if (e.target.closest(".btn-delete")) {
            if (confirm("Apakah Anda yakin ingin menghapus dokumen ini?")) {
                e.target.closest("tr").remove();
                alert("Dokumen berhasil dihapus.");
            }
        }
    });
});