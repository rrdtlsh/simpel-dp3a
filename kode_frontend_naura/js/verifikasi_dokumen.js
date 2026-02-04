document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburger");
    const modal = document.getElementById("detailModal");
    const closeDetail = document.getElementById("closeDetail");
    const editor = document.getElementById("editorDetail");
    const btnSend = document.querySelector(".btn-send");
    const btnSave = document.querySelector(".btn-save");

    // ================= SIDEBAR =================
    hamburger.onclick = () => sidebar.classList.toggle("hide");

    // ================= BUKA MODAL =================
    document.querySelectorAll(".btn-view").forEach(btn => {
        btn.addEventListener("click", () => {
            document.getElementById("dNama").innerText = btn.dataset.nama;
            document.getElementById("dBidang").innerText = btn.dataset.bidang;
            document.getElementById("dTahun").innerText = btn.dataset.tahun;
            editor.innerHTML = btn.dataset.penjelasan || "";

            // 🔥 LOGIKA STATUS → TAMPILKAN / SEMBUNYIKAN TOMBOL KIRIM
            const status = btn.dataset.status;

            if (btnSend) {
                if (status === "Ditolak") {
                    btnSend.style.display = "inline-block";
                } else {
                    btnSend.style.display = "none";
                }
            }

            modal.style.display = "block";
        });
    });

    // ================= TUTUP MODAL =================
    closeDetail.onclick = () => modal.style.display = "none";

    window.onclick = e => {
        if (e.target === modal) modal.style.display = "none";
    };

    // ================= TOOLBAR EDITOR =================
    document.querySelectorAll(".editor-toolbar button").forEach(btn => {
        btn.addEventListener("click", () => {
            document.execCommand(btn.dataset.cmd, false, null);
            editor.focus();
        });
    });

    // ================= TOMBOL SIMPAN =================
    if (btnSave) {
        btnSave.addEventListener("click", () => {
            alert("Catatan berhasil disimpan.");
        });
    }

    // ================= TOMBOL KIRIM =================
    if (btnSend) {
        btnSend.addEventListener("click", () => {
            const catatan = editor.innerHTML;

            // 🔴 nanti bisa diganti AJAX / fetch
            alert(
                "Catatan berhasil dikirim ke bidang.\n\n" +
                "Bidang diminta mengunggah ulang dokumen."
            );

            modal.style.display = "none";
        });
    }
});
