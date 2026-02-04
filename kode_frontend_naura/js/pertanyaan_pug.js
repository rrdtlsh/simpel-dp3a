document.addEventListener("DOMContentLoaded", function () {

    console.log("PERTANYAAN PUG JS AKTIF");

    /* ===============================
       SIDEBAR
    ================================ */
    const sidebar   = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburger");

    if (hamburger && sidebar) {
        hamburger.addEventListener("click", () => {
            sidebar.classList.toggle("hide");
        });
    }

    /* ===============================
       MODAL
    ================================ */
    const modalTambah = document.getElementById("modalTambah");
    const modalDetail = document.getElementById("modalDetail");
    const btnTambah   = document.getElementById("btnTambah");

    if (btnTambah && modalTambah) {
        btnTambah.addEventListener("click", () => {
            modalTambah.style.display = "block";
        });
    }

    /* ===============================
       OPEN MODAL DETAIL (LIHAT / UBAH)
    ================================ */
    document.querySelectorAll(".btn-view").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            // KODE
            const kodeEl = document.getElementById("detailKode");
            if (kodeEl) kodeEl.innerText = this.dataset.kode;

            // PERTANYAAN
            const pertanyaanEl = document.getElementById("detailPertanyaan");
            if (pertanyaanEl) pertanyaanEl.innerText = this.dataset.teks;

            // JAWABAN (EDITOR)
            const editor = document.getElementById("editorJawaban");
            if (editor) {
                editor.innerHTML = this.dataset.jawaban
                    ? `<p>${this.dataset.jawaban}</p>`
                    : "<p>Belum ada jawaban</p>";
            }

            // LAMPIRAN
            const lampiranList = document.getElementById("lampiranList");
            if (lampiranList) {
                lampiranList.innerHTML = "";

                if (this.dataset.file) {
                    lampiranList.innerHTML = `
                        <li>
                            <span>${this.dataset.file}</span>
                            <a href="#">Download</a>
                        </li>
                    `;
                } else {
                    lampiranList.innerHTML = "<li>Tidak ada lampiran</li>";
                }
            }

            // TOMBOL KIRIM
            const btnSend = document.querySelector(".btn-send");
            if (btnSend) {
                btnSend.style.display =
                    this.dataset.status === "belum" ? "inline-block" : "none";
            }

            modalDetail.style.display = "block";
        });
    });

    /* ===============================
       CLOSE MODAL
    ================================ */
    document.querySelectorAll(".close-modal, [data-close]").forEach(btn => {
        btn.addEventListener("click", () => {
            if (modalTambah) modalTambah.style.display = "none";
            if (modalDetail) modalDetail.style.display = "none";
        });
    });

    window.addEventListener("click", e => {
        if (e.target === modalTambah) modalTambah.style.display = "none";
        if (e.target === modalDetail) modalDetail.style.display = "none";
    });

    /* ===============================
       EDITOR TOOLBAR
    ================================ */
    document.querySelectorAll(".editor-toolbar button").forEach(btn => {
        btn.addEventListener("click", () => {
            document.execCommand(btn.dataset.cmd, false, null);
        });
    });

});
