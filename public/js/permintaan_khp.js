document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburger");

    hamburger?.addEventListener("click", () => {
        sidebar.classList.toggle("hide");
    });

    // ===== HAPUS PERMINTAAN (SIMULASI) =====
    document.addEventListener("click", function (e) {
        const btnDelete = e.target.closest(".btn-delete");

        if (btnDelete) {
            e.preventDefault();

            if (confirm("Yakin ingin menghapus permintaan ini?")) {
                btnDelete.closest("tr").remove();
                alert("Permintaan berhasil dihapus (simulasi)");
            }
        }
    });

});
