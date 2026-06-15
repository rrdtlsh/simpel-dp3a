function filterTablePermintaan() {
    const searchInput = document.getElementById("searchPermintaanAdmin");
    const filterBidang = document.getElementById("filterBidang");
    const filterStatus = document.getElementById("filterStatus");

    const tableBody = document.querySelector("#tabelPermintaanAdmin tbody");

    if (!tableBody) return;

    const tableRows = tableBody.querySelectorAll("tr.row-data");
    const emptyMessage = document.getElementById("permintaanSearchEmpty");
    const emptyRowAsli = document.querySelector(".permintaan-empty-row");

    const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : "";
    const bidangVal = filterBidang ? filterBidang.value : "all";
    const statusVal = filterStatus ? filterStatus.value : "all";

    let matchCount = 0;

    tableRows.forEach((row) => {
        const namaDokumen = (
            row.getAttribute("data-judul") || ""
        ).toLowerCase();
        const bidangId = row.getAttribute("data-bidang") || "";
        const statusFile = row.getAttribute("data-status") || "";

        const matchSearch = searchVal === "" || namaDokumen.includes(searchVal);
        const matchBidang = bidangVal === "all" || bidangId === bidangVal;
        const matchStatus = statusVal === "all" || statusFile === statusVal;

        if (matchSearch && matchBidang && matchStatus) {
            row.style.display = "";
            matchCount++;
        } else {
            row.style.display = "none";
        }
    });

    if (emptyMessage) {
        emptyMessage.style.display = "none";

        if (
            emptyRowAsli &&
            emptyRowAsli.style.display !== "none" &&
            tableRows.length === 0
        ) {
            return;
        }

        if (
            (searchVal !== "" || bidangVal !== "all" || statusVal !== "all") &&
            matchCount === 0
        ) {
            emptyMessage.style.display = "table-row";
        }
    }
}

document.addEventListener("input", function (e) {
    if (e.target && e.target.id === "searchPermintaanAdmin") {
        filterTablePermintaan();
    }
});

document.addEventListener("change", function (e) {
    if (
        e.target &&
        (e.target.id === "filterBidang" || e.target.id === "filterStatus")
    ) {
        filterTablePermintaan();
    }
});

// =========================================================================
// LOGIKA SUBMIT FORM TAMBAH PERMINTAAN DENGAN VALIDASI ERROR HANDLING
// =========================================================================
document.addEventListener("submit", function (e) {
    if (e.target && e.target.id === "createPermintaanForm") {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;

        // 1. Bersihkan pesan error merah dari percobaan sebelumnya
        document.querySelectorAll(".invalid-feedback").forEach((el) => {
            el.style.display = "none"; // Sembunyikan teks merah
            const span = el.querySelector("span");
            if (span) span.innerText = "";
        });
        document.querySelectorAll(".validate-input").forEach((el) => {
            el.classList.remove("border-red-500", "is-invalid"); // Hilangkan border merah dari input
            el.style.borderColor = ""; // Reset border color inline
        });

        // 2. Ubah state tombol menjadi loading
        submitBtn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;

        // Ambil CSRF Token
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";

        // 3. Kirim data ke Controller menggunakan AJAX (Fetch)
        fetch(form.action, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: formData,
        })
            .then(async (response) => {
                const data = await response.json();

                // Tangkap Error Validasi (Status 422 dari StorePengajuanRequest)
                if (response.status === 422) {
                    throw { isValidationError: true, errors: data.errors };
                }

                // Tangkap Error Server Lainnya
                if (!response.ok) {
                    throw new Error(
                        data.message || "Terjadi kesalahan sistem.",
                    );
                }

                return data;
            })
            .then((data) => {
                // JIKA BERHASIL DISIMPAN
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: data.message || "Permintaan dokumen berhasil dibuat.",
                    confirmButtonColor: "#d4af37",
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch((error) => {
                // JIKA GAGAL VALIDASI (Misal: Nama Dokumen Duplikat)
                if (error.isValidationError) {
                    // Looping untuk mencari kolom mana yang salah dan menampilkan teks merah
                    for (const field in error.errors) {
                        const errorMsg = error.errors[field][0]; // Ambil teks error dari Laravel
                        const errorDiv = document.getElementById(
                            "err_" + field,
                        ); // Cari elemen <div id="err_judul">
                        const inputEl = document.getElementById(field); // Cari elemen <input id="judul">

                        // Tampilkan tulisan merah di bawah input form
                        if (errorDiv) {
                            const span = errorDiv.querySelector("span");
                            if (span) {
                                span.innerText = errorMsg; // Masukkan teks error
                            } else {
                                errorDiv.innerText = errorMsg;
                            }
                            // Munculkan div error dengan flex agar ikon serunya sejajar
                            errorDiv.style.display = "flex";
                            errorDiv.style.alignItems = "center";
                            errorDiv.style.gap = "5px";
                            errorDiv.style.color = "#dc3545";
                            errorDiv.style.marginTop = "4px";
                            errorDiv.style.fontSize = "12px";
                        }

                        // Beri kotak merah pada input form yang salah
                        if (inputEl) {
                            inputEl.classList.add(
                                "border-red-500",
                                "is-invalid",
                            );
                            inputEl.style.borderColor = "#dc3545";
                        }
                    }
                    // HAPUS SWEETALERT DI SINI:
                    // Tidak ada lagi Swal.fire untuk validasi, sehingga user hanya melihat teks merah.
                } else {
                    // JIKA GAGAL SISTEM (Bukan Validasi, misal 500 Server Error)
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text:
                            error.message ||
                            "Terjadi kesalahan saat memproses data.",
                        confirmButtonColor: "#d4af37",
                    });
                }
            })
            .finally(() => {
                // Matikan loading tombol
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
    }
});
