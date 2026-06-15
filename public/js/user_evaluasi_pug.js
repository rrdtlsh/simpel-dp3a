var UserEvaluasiPUG = (function () {
    "use strict";

    /* ── State ──────────────────────────────────────────────────────── */
    var _cfg = {};
    var _state = {
        pertanyaanId: null,
        jawabanId: null,
        tahun: null,
        pilihanKode: null,
        pilihanLabel: null,
        skorPilihan: 0,
        statusJawaban: null,
    };
    var _initialized = false;

    /* ── Helpers ────────────────────────────────────────────────────── */
    function getCsrf() {
        return (
            _cfg.csrf ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") ||
            ""
        );
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function showFieldError(elId, msg) {
        var el = document.getElementById(elId);
        if (!el) return;
        el.textContent = msg;
        el.style.display = msg ? "block" : "none";
    }

    function clearFieldErrors() {
        ["epugErrJawaban", "epugErrCatatan", "epugErrFile"].forEach(
            function (id) {
                showFieldError(id, "");
            },
        );
    }

    function fmtFileSize(bytes) {
        if (bytes < 1024) return bytes + " B";
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + " KB";
        return (bytes / 1048576).toFixed(2) + " MB";
    }

    function fmtTanggal(isoStr) {
        if (!isoStr) return "-";
        try {
            var d = new Date(isoStr);
            return d.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        } catch (e) {
            return isoStr;
        }
    }

    function setModalLoading(show) {
        var loader = document.getElementById("epugModalLoader");
        var body = document.getElementById("epugModalBody");
        var footer = document.getElementById("epugModalFooter");
        if (loader) loader.style.display = show ? "block" : "none";
        if (body) body.style.display = show ? "none" : "block";
        if (footer) footer.style.display = show ? "none" : "flex";
    }

    function openModal(modalId) {
        var el = document.getElementById(modalId);
        if (el) {
            el.style.display = "flex";
            document.body.style.overflow = "hidden";
        }
    }

    function closeModal(modalId) {
        var el = document.getElementById(modalId);
        if (el) {
            el.style.display = "none";
            document.body.style.overflow = "";
        }
    }

    /* ── API Fetch ──────────────────────────────────────────────────── */
    function apiFetch(url, options) {
        var defaults = {
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": getCsrf(),
                Accept: "application/json",
            },
        };
        if (options && options.body && !(options.body instanceof FormData)) {
            defaults.headers["Content-Type"] = "application/json";
        }
        var merged = Object.assign({}, defaults, options);
        if (options && options.headers) {
            merged.headers = Object.assign(
                {},
                defaults.headers,
                options.headers,
            );
        }
        return fetch(url, merged);
    }

    /* ══════════════════════════════════════════════════════════════════
       TREE: Toggle Komponen & Indikator
       ══════════════════════════════════════════════════════════════════ */
    function bindTreeToggle(container) {
        /* Komponen toggle */
        container.querySelectorAll("[data-toggle]").forEach(function (btn) {
            if (btn.dataset.epugBound) return;
            btn.dataset.epugBound = "1";

            btn.addEventListener("click", function () {
                var targetId = this.dataset.toggle;
                var target = document.getElementById(targetId);
                var chevron = document.getElementById("chevron-" + targetId);
                if (!target) return;

                var isOpen = target.style.display !== "none";
                target.style.display = isOpen ? "none" : "block";
                if (chevron) chevron.classList.toggle("open", !isOpen);
            });
        });

        /* Indikator toggle */
        container
            .querySelectorAll(".epug-indikator-header")
            .forEach(function (header) {
                if (header.dataset.epugBound) return;
                header.dataset.epugBound = "1";

                header.addEventListener("click", function () {
                    var targetId = this.dataset.toggle;
                    var target = document.getElementById(targetId);
                    var chevron = document.getElementById(
                        "chevron-" + targetId,
                    );
                    if (!target) return;
                    var isOpen = target.style.display !== "none";
                    target.style.display = isOpen ? "none" : "block";
                    if (chevron) chevron.classList.toggle("open", !isOpen);
                });
            });
    }

    /* ══════════════════════════════════════════════════════════════════
       PENCARIAN (SEARCH) PERTANYAAN
       ══════════════════════════════════════════════════════════════════ */
    function bindSearch() {
        var searchInput = document.getElementById("epugSearchInput");
        var emptyMessage = document.getElementById("epugSearchEmpty");
        if (!searchInput) return;

        searchInput.addEventListener("input", function (e) {
            var filter = e.target.value.toLowerCase().trim();
            var komponenBlocks = document.querySelectorAll(
                ".epug-komponen-block",
            );
            var totalMatch = 0;

            komponenBlocks.forEach(function (komp) {
                var kompHasMatch = false;
                var indikatorBlocks = komp.querySelectorAll(
                    ".epug-indikator-block",
                );

                indikatorBlocks.forEach(function (indk) {
                    var indkHasMatch = false;
                    var pertRows = indk.querySelectorAll(
                        ".epug-pertanyaan-row",
                    );

                    pertRows.forEach(function (pert) {
                        var text = pert
                            .querySelector(".epug-pert-text")
                            .textContent.toLowerCase();
                        if (text.includes(filter)) {
                            pert.style.display = "flex";
                            indkHasMatch = true;
                            kompHasMatch = true;
                            totalMatch++;
                        } else {
                            pert.style.display = "none";
                        }
                    });

                    if (indkHasMatch) {
                        indk.style.display = "block";
                        if (filter !== "") {
                            var indkBody = indk.querySelector(
                                ".epug-indikator-body",
                            );
                            var indkChevron =
                                indk.querySelector(".epug-chevron-sm");
                            if (indkBody) indkBody.style.display = "block";
                            if (indkChevron) indkChevron.classList.add("open");
                        }
                    } else {
                        indk.style.display = filter === "" ? "block" : "none";
                    }
                });

                if (kompHasMatch) {
                    komp.style.display = "block";
                    if (filter !== "") {
                        var kompBody = komp.querySelector(
                            ".epug-komponen-body",
                        );
                        var kompChevron = komp.querySelector(".epug-chevron");
                        if (kompBody) kompBody.style.display = "block";
                        if (kompChevron) kompChevron.classList.add("open");
                    }
                } else {
                    komp.style.display = filter === "" ? "block" : "none";
                }
            });

            if (emptyMessage) {
                if (filter !== "" && totalMatch === 0) {
                    emptyMessage.style.display = "block";
                } else {
                    emptyMessage.style.display = "none";
                }
            }
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       MODAL DETAIL PERTANYAAN (USER)
       ══════════════════════════════════════════════════════════════════ */
    function openDetailModal(pertanyaanId, tahun) {
        _state.pertanyaanId = pertanyaanId;
        _state.tahun = tahun;
        _state.jawabanId = null;
        _state.pilihanKode = null;
        _state.pilihanLabel = null;
        _state.skorPilihan = 0;

        clearFieldErrors();
        setModalLoading(true);
        openModal("epugModal");

        apiFetch(_cfg.routes.show + pertanyaanId + "?tahun=" + tahun)
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                renderModalContent(data);
                setModalLoading(false);
            })
            .catch(function (err) {
                console.error("[EPUG Modal]", err);
                document.getElementById("epugModalBody").innerHTML =
                    '<div class="epug-alert epug-alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Gagal memuat data. Silakan coba lagi.</div>';
                setModalLoading(false);
            });
    }

    function renderModalContent(data) {
        var pert = data.pertanyaan;
        var jwb = data.jawaban;
        var auditLog = data.audit_log || [];
        var versi = data.versi || [];
        var petunjukAlert = document.getElementById("epugPetunjukAlert");
        var petunjukText = document.getElementById("epugModalPetunjuk");

        if (petunjukAlert && petunjukText) {
            if (pert.petunjuk && pert.petunjuk.trim() !== "") {
                petunjukText.textContent = "Petunjuk: " + pert.petunjuk;
                petunjukAlert.style.display = "flex";
            } else {
                petunjukAlert.style.display = "none";
            }
        }

        var breadcrumb = document.getElementById("epugModalBreadcrumb");
        if (breadcrumb) {
            breadcrumb.textContent =
                (pert.indikator?.komponen?.nama || "") +
                " / " +
                (pert.indikator?.nama || "");
        }
        var kodeEl = document.getElementById("epugModalKode");
        if (kodeEl) kodeEl.textContent = pert.kode + ". " + pert.pertanyaan;

        /* Status alert */
        var alertEl = document.getElementById("epugStatusAlert");
        if (alertEl) {
            alertEl.style.display = "none";
            if (jwb) {
                if (jwb.status === "disetujui") {
                    alertEl.className = "epug-alert epug-alert-success";
                    alertEl.innerHTML =
                        '<i class="fa-solid fa-circle-check"></i> <span>Jawaban telah <strong>disetujui</strong> Admin. Tidak dapat diedit lagi.</span>';
                    alertEl.style.display = "flex";
                } else if (jwb.status === "ditolak") {
                    alertEl.className = "epug-alert epug-alert-danger";
                    alertEl.innerHTML =
                        '<i class="fa-solid fa-circle-xmark"></i> <span>Jawaban <strong>ditolak/direvisi</strong> oleh Admin. Silakan perbaiki dan simpan ulang.</span>';
                    alertEl.style.display = "flex";
                }
            }
        }

        /* Pilihan Jawaban */
        renderPilihan(pert.pilihan_jawaban || [], jwb);

        /* Catatan */
        var catatanEl = document.getElementById("epugCatatan");
        if (catatanEl) {
            catatanEl.value = jwb?.catatan || "";
            catatanEl.disabled = jwb?.status === "disetujui";
            updateCatatanCounter();
        }

        /* Lampiran */
        renderLampiran(jwb?.lampiran || [], jwb?.status === "disetujui");

        /* Skor tersimpan */
        var skorSection = document.getElementById("epugSkorSection");
        if (skorSection && jwb) {
            document.getElementById("epugSkorValue").textContent = jwb.skor;
            document.getElementById("epugJawabanSaved").textContent =
                jwb.jawaban_label || "-";
            skorSection.style.display = "block";
        } else if (skorSection) {
            skorSection.style.display = "none";
        }

        /* State */
        if (jwb) {
            _state.jawabanId = jwb.id;
            _state.statusJawaban = jwb.status;
            _state.pilihanKode = jwb.jawaban_kode;
            _state.pilihanLabel = jwb.jawaban_label;
            _state.skorPilihan = jwb.skor;
        }

        /* ✅ Fitur Khusus User: Menampilkan Catatan Verifikasi Admin (Read Only) */
        var verifReadonly = document.getElementById("epugCatatanAdminReadonly");
        var verifText = document.getElementById("epugCatatanAdminText");
        if (verifReadonly && verifText) {
            if (jwb && jwb.catatan_admin && jwb.catatan_admin.trim() !== "") {
                verifText.textContent = jwb.catatan_admin;
                verifReadonly.style.display = "block";
            } else {
                verifReadonly.style.display = "none";
            }
        }

        /* Simpan btn — disabled jika disetujui */
        var simpanBtn = document.getElementById("epugBtnSimpan");
        if (simpanBtn) simpanBtn.disabled = jwb?.status === "disetujui";

        /* Audit log */
        renderAuditLog(auditLog, versi);
    }

    function renderPilihan(pilihan, jwb) {
        var container = document.getElementById("epugPilihanContainer");
        if (!container) return;
        container.innerHTML = "";

        var isLocked = jwb?.status === "disetujui";

        pilihan.forEach(function (item) {
            var isSelected =
                jwb &&
                jwb.jawaban_kode &&
                jwb.jawaban_kode.startsWith(
                    item.kode || item.label.substr(0, 3),
                );

            var div = document.createElement("div");
            div.className =
                "epug-pilihan-option" + (isSelected ? " selected" : "");
            div.setAttribute("data-kode", item.kode || "");
            div.setAttribute("data-label", item.label || "");
            div.setAttribute("data-skor", item.skor || 0);

            div.innerHTML =
                '<input type="radio" name="epugPilihan" value="' +
                escHtml(item.kode || "") +
                '"' +
                (isSelected ? " checked" : "") +
                (isLocked ? " disabled" : "") +
                ">" +
                '<span class="epug-pilihan-label-text">' +
                escHtml(item.label || "") +
                "</span>";

            if (!isLocked) {
                div.addEventListener("click", function (e) {
                    onPilihanClick(div, item);
                });
            }
            container.appendChild(div);
        });
    }

    function onPilihanClick(div, item) {
        /* Deselect semua */
        div.closest(".epug-pilihan-wrap")
            .querySelectorAll(".epug-pilihan-option")
            .forEach(function (d) {
                d.classList.remove("selected");
                var radio = d.querySelector('input[type="radio"]');
                if (radio) radio.checked = false;
            });

        div.classList.add("selected");
        var radio = div.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        _state.pilihanKode = item.kode || item.label;
        _state.pilihanLabel = item.label;
        _state.skorPilihan = parseFloat(item.skor) || 0;

        showFieldError("epugErrJawaban", "");
    }

    function renderLampiran(files, isLocked) {
        var list = document.getElementById("epugLampiranList");
        var zone = document.getElementById("epugUploadZone");
        if (!list) return;

        list.innerHTML = "";
        files.forEach(function (f) {
            var item = document.createElement("div");
            item.className = "epug-lampiran-item";
            item.innerHTML =
                '<span><i class="fa-solid fa-file" style="margin-right:5px;color:#067fb2;"></i>' +
                escHtml(f.nama_file) +
                " (" +
                fmtFileSize(f.ukuran || 0) +
                ")</span>" +
                '<div style="display:flex;align-items:center;gap:8px;">' +
                '<a href="/storage/' +
                escHtml(f.path_file) +
                '" target="_blank" title="Lihat Berkas">' +
                '<i class="fa-solid fa-eye"></i></a>' +
                (isLocked
                    ? ""
                    : '<button class="epug-lampiran-del" data-lampiran-id="' +
                      f.id +
                      '" title="Hapus"><i class="fa-solid fa-trash-can"></i></button>') +
                "</div>";
            list.appendChild(item);
        });

        if (zone) zone.style.display = isLocked ? "none" : "block";
    }

    function renderAuditLog(logs, versi) {
        var auditList = document.getElementById("epugAuditList");
        if (!auditList) return;
        auditList.innerHTML = "";

        if (!logs.length && !versi.length) {
            auditList.innerHTML =
                '<p style="color:#9ca3af;font-size:.8rem;padding:8px;">Belum ada riwayat.</p>';
            return;
        }

        var aksiLabel = {
            isi_jawaban: "📝 Mengisi Jawaban",
            ubah_jawaban: "✏️ Mengubah Jawaban",
            setujui: "✅ Disetujui Admin",
            tolak: "❌ Ditolak/Revisi Admin",
            upload_lampiran: "📎 Upload Lampiran",
        };

        logs.forEach(function (log) {
            var div = document.createElement("div");
            div.className = "epug-audit-item";
            div.innerHTML =
                '<div class="epug-audit-aksi">' +
                (aksiLabel[log.aksi] || log.aksi) +
                "</div>" +
                '<div class="epug-audit-meta">' +
                escHtml(log.user?.name || "User") +
                " &bull; " +
                fmtTanggal(log.created_at) +
                "</div>";
            auditList.appendChild(div);
        });

        if (versi.length > 1) {
            var versiTitle = document.createElement("p");
            versiTitle.style.cssText =
                "font-weight:700;font-size:.78rem;margin:10px 0 6px;color:#6b7280;";
            versiTitle.textContent = "Riwayat Versi Jawaban:";
            auditList.appendChild(versiTitle);

            versi.forEach(function (v) {
                var vDiv = document.createElement("div");
                vDiv.className = "epug-audit-item";
                vDiv.style.borderLeftColor = "#f59e0b";
                vDiv.innerHTML =
                    '<div class="epug-audit-aksi">Versi ' +
                    v.versi +
                    " — " +
                    escHtml(v.jawaban_label || "-") +
                    "</div>" +
                    '<div class="epug-audit-meta">Skor: ' +
                    v.skor +
                    " &bull; " +
                    escHtml(v.user?.name || "-") +
                    " &bull; " +
                    fmtTanggal(v.created_at) +
                    "</div>";
                auditList.appendChild(vDiv);
            });
        }
    }

    /* ══════════════════════════════════════════════════════════════════
       VALIDASI & SIMPAN JAWABAN (USER)
       ══════════════════════════════════════════════════════════════════ */
    function updateCatatanCounter() {
        var el = document.getElementById("epugCatatan");
        var counter = document.getElementById("epugCatatanCounter");
        if (!el || !counter) return;
        var len = el.value.length;
        counter.textContent = len + "/1000";
        counter.style.color = len > 1000 ? "#ef4444" : "#9ca3af";

        var isOver = len > 1000;
        el.classList.toggle("error", isOver);
        showFieldError(
            "epugErrCatatan",
            isOver ? "Catatan melebihi batas 1000 karakter." : "",
        );
    }

    function validateAndSave() {
        clearFieldErrors();
        var hasError = false;

        if (!_state.pilihanKode) {
            showFieldError(
                "epugErrJawaban",
                "Anda wajib memilih salah satu pilihan jawaban.",
            );
            Swal.fire({
                icon: "warning",
                title: "Jawaban Kosong",
                text: "Silakan pilih salah satu jawaban terlebih dahulu sebelum menyimpan.",
                confirmButtonColor: "#067fb2",
            });
            hasError = true;
        }

        var catatan = document.getElementById("epugCatatan")?.value || "";
        var illegalChars = /[<>{}\[\]\\]/;

        if (catatan.length > 1000) {
            showFieldError("epugErrCatatan", "Catatan maksimal 1000 karakter.");
            hasError = true;
        } else if (illegalChars.test(catatan)) {
            showFieldError(
                "epugErrCatatan",
                "Catatan mengandung karakter yang tidak diizinkan.",
            );
            hasError = true;
        }

        if (hasError) return;

        var simpanBtn = document.getElementById("epugBtnSimpan");
        if (simpanBtn) {
            simpanBtn.disabled = true;
            simpanBtn.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        }

        apiFetch(_cfg.routes.simpan, {
            method: "POST",
            body: JSON.stringify({
                pertanyaan_id: _state.pertanyaanId,
                tahun: _state.tahun,
                jawaban_kode: _state.pilihanKode,
                jawaban_label: _state.pilihanLabel,
                catatan: catatan,
                skor: _state.skorPilihan,
            }),
        })
            .then(function (r) {
                return r.json().then(function (d) {
                    return { ok: r.ok, data: d, status: r.status };
                });
            })
            .then(function (res) {
                if (res.ok && res.data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Tersimpan!",
                        text: "Jawaban Anda berhasil dikirim.",
                        timer: 1800,
                        showConfirmButton: false,
                    });
                    closeModal("epugModal");
                    refreshRow(_state.pertanyaanId);
                } else if (res.status === 422) {
                    var errs = res.data.errors || {};
                    Object.keys(errs).forEach(function (field) {
                        showFieldError(
                            "epugErr" +
                                field.charAt(0).toUpperCase() +
                                field.slice(1),
                            errs[field][0],
                        );
                    });
                } else {
                    Swal.fire(
                        "Gagal",
                        res.data.message || "Terjadi kesalahan.",
                        "error",
                    );
                }
            })
            .catch(function () {
                Swal.fire("Error", "Koneksi bermasalah.", "error");
            })
            .finally(function () {
                if (simpanBtn) {
                    simpanBtn.disabled = false;
                    simpanBtn.innerHTML =
                        '<i class="fa-solid fa-floppy-disk"></i> Simpan Jawaban';
                }
            });
    }

    /* ══════════════════════════════════════════════════════════════════
       UPLOAD FILE (USER)
       ══════════════════════════════════════════════════════════════════ */
    function handleFileUpload(files) {
        var allowedTypes = [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "image/jpeg",
            "image/png",
        ];

        if (files.length > 10) {
            showFieldError("epugErrFile", "Maksimal pilih 10 file sekaligus.");
            return;
        }
        if (!_state.pertanyaanId) return;

        var formData = new FormData();
        formData.append("pertanyaan_id", _state.pertanyaanId);
        formData.append("tahun", _state.tahun);

        var valid = true;
        Array.from(files).forEach(function (file) {
            if (!allowedTypes.includes(file.type)) {
                showFieldError(
                    "epugErrFile",
                    "Format file tidak didukung: " + file.name,
                );
                valid = false;
            }
            if (file.size > 10 * 1024 * 1024) {
                showFieldError(
                    "epugErrFile",
                    "File melebihi 10 MB: " + file.name,
                );
                valid = false;
            }
            formData.append("file[]", file);
        });

        if (!valid) return;
        showFieldError("epugErrFile", "");

        var zoneSpan = document.querySelector(".epug-upload-label span");
        if (zoneSpan)
            zoneSpan.textContent = "Mengunggah " + files.length + " file...";

        apiFetch(_cfg.routes.uploadFile, { method: "POST", body: formData })
            .then(function (r) {
                return r.json().then(function (d) {
                    return { ok: r.ok, data: d, status: r.status };
                });
            })
            .then(function (res) {
                if (res.ok && res.data.success) {
                    _state.jawabanId = res.data.jawaban_id;
                    var list = document.getElementById("epugLampiranList");

                    res.data.lampirans.forEach(function (lmp) {
                        var newItem = document.createElement("div");
                        newItem.className = "epug-lampiran-item";
                        newItem.innerHTML =
                            '<span><i class="fa-solid fa-file" style="margin-right:5px;color:#067fb2;"></i>' +
                            escHtml(lmp.nama_file) +
                            "</span>" +
                            '<div style="display:flex;align-items:center;gap:8px;"><a href="' +
                            escHtml(lmp.url) +
                            '" target="_blank" title="Lihat Berkas"><i class="fa-solid fa-eye"></i></a>' +
                            '<button class="epug-lampiran-del" data-lampiran-id="' +
                            lmp.id +
                            '"><i class="fa-solid fa-trash-can"></i></button></div>';
                        list.appendChild(newItem);
                    });

                    if (zoneSpan)
                        zoneSpan.textContent =
                            "Klik untuk upload (PDF, DOC, XLS, IMG — maks 10 MB)";
                } else {
                    showFieldError(
                        "epugErrFile",
                        res.data.message || "Upload gagal.",
                    );
                    if (zoneSpan)
                        zoneSpan.textContent =
                            "Klik untuk upload (PDF, DOC, XLS, IMG — maks 10 MB)";
                }
            })
            .catch(function () {
                showFieldError("epugErrFile", "Koneksi bermasalah.");
                if (zoneSpan)
                    zoneSpan.textContent =
                        "Klik untuk upload (PDF, DOC, XLS, IMG — maks 10 MB)";
            });
    }

    /* ══════════════════════════════════════════════════════════════════
       REFRESH ROW (USER)
       ══════════════════════════════════════════════════════════════════ */
    function refreshRow(pertanyaanId) {
        var row = document.querySelector(
            '.epug-pertanyaan-row[data-id="' + pertanyaanId + '"]',
        );
        if (!row) return;

        apiFetch(_cfg.routes.show + pertanyaanId + "?tahun=" + _state.tahun)
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                var jwb = data.jawaban;
                var status = jwb ? jwb.status : "belum";
                var badge = row.querySelector(".epug-status-badge");
                var skorEl = row.querySelector(".epug-skor-text");

                var statusClass = {
                    disetujui: "epug-status-green",
                    diisi: "epug-status-blue",
                    ditolak: "epug-status-red",
                    belum: "epug-status-red",
                };
                var statusLabel = {
                    disetujui: "Disetujui",
                    diisi: "Sudah Diisi",
                    ditolak: "Ditolak",
                    belum: "Belum Diisi",
                };

                if (badge) {
                    badge.className =
                        "epug-status-badge " +
                        (statusClass[status] || "epug-status-red");
                    badge.textContent = statusLabel[status] || "Belum Diisi";
                }
                if (skorEl && jwb) {
                    skorEl.textContent =
                        "Skor: " +
                        jwb.skor +
                        " / " +
                        (row.dataset.skorMaks || "-");
                }
                row.dataset.status = status;
            })
            .catch(function () {
                /* silent fail */
            });
    }

    /* ══════════════════════════════════════════════════════════════════
       EVENT BINDING (USER)
       ══════════════════════════════════════════════════════════════════ */
    function bindEvents() {
        var mc = document.getElementById("main-content") || document.body;

        /* SINGLE listener on #main-content untuk klik */
        mc.addEventListener("click", function (e) {
            if (e.target.closest(".epug-btn-lihat-trigger")) {
                var btn = e.target.closest(".epug-btn-lihat-trigger");
                openDetailModal(btn.dataset.pertId, btn.dataset.tahun);
                return;
            }

            if (e.target.closest("#epugBtnClearPilihan")) {
                document
                    .querySelectorAll(".epug-pilihan-option")
                    .forEach(function (d) {
                        d.classList.remove("selected");
                        var radio = d.querySelector('input[type="radio"]');
                        if (radio) radio.checked = false;
                    });
                _state.pilihanKode = null;
                _state.pilihanLabel = null;
                _state.skorPilihan = 0;
                return;
            }

            if (
                e.target.closest("#epugModalClose") ||
                e.target.closest("#epugBtnTutup") ||
                e.target.id === "epugModal"
            ) {
                closeModal("epugModal");
                return;
            }

            if (e.target.closest("#epugBtnSimpan")) {
                validateAndSave();
                return;
            }

            if (e.target.closest(".epug-lampiran-del")) {
                var btnDel = e.target.closest(".epug-lampiran-del");
                var lid = btnDel.dataset.lampiranId;
                Swal.fire({
                    title: "Hapus lampiran?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ef4444",
                    confirmButtonText: "Ya, Hapus",
                    cancelButtonText: "Batal",
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    apiFetch(_cfg.routes.hapusFile + lid, { method: "DELETE" })
                        .then(function (res) {
                            return res.json();
                        })
                        .then(function (d) {
                            if (d.success)
                                btnDel.closest(".epug-lampiran-item").remove();
                            else
                                Swal.fire(
                                    "Gagal",
                                    "Tidak dapat menghapus file.",
                                    "error",
                                );
                        });
                });
                return;
            }

            if (e.target.closest("#epugAuditToggle")) {
                var body = document.getElementById("epugAuditBody");
                var chevron = document.getElementById("epugAuditChevron");
                if (body)
                    body.style.display =
                        body.style.display === "none" ? "block" : "none";
                if (chevron)
                    chevron.style.transform =
                        body?.style.display === "none" ? "" : "rotate(180deg)";
                return;
            }
        });

        /* Catatan counter */
        mc.addEventListener("input", function (e) {
            if (e.target.id === "epugCatatan") updateCatatanCounter();
        });

        /* File input & dropdown tahun */
        mc.addEventListener("change", function (e) {
            if (e.target.id === "epugFileInput") {
                var files = e.target.files;
                if (files && files.length > 0) handleFileUpload(files);
                e.target.value = "";
            }
            if (e.target.id === "epugTahunSelect") {
                // Di SPA User, redirect sesuai URL filter tahun
                window.location.href =
                    "/user/evaluasi-pug?tahun=" + e.target.value;
            }
        });

        /* Klik di luar modal dan Escape Key */
        window.addEventListener("click", function (e) {
            if (e.target.id === "epugModal") closeModal("epugModal");
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                closeModal("epugModal");
            }
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       EXPORT (USER)
       ══════════════════════════════════════════════════════════════════ */
    function bindExport(container) {
        container
            .querySelector("#epugBtnExportExcel")
            ?.addEventListener("click", function () {
                var tahun =
                    document.getElementById("epugTahunSelect")?.value ||
                    new Date().getFullYear();
                Swal.fire({
                    title: "Menyiapkan Excel...",
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    },
                });
                window.location.href =
                    _cfg.routes.exportExcel + "?tahun=" + tahun;
                setTimeout(function () {
                    Swal.close();
                }, 2000);
            });

        container
            .querySelector("#epugBtnExportPdf")
            ?.addEventListener("click", function () {
                var tahun =
                    document.getElementById("epugTahunSelect")?.value ||
                    new Date().getFullYear();
                Swal.fire({
                    title: "Menyiapkan PDF...",
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    },
                });
                window.location.href =
                    _cfg.routes.exportPdf + "?tahun=" + tahun;
                setTimeout(function () {
                    Swal.close();
                }, 3000);
            });
    }

    /* ══════════════════════════════════════════════════════════════════
       RENDER GRAFIK APEXCHARTS
       ══════════════════════════════════════════════════════════════════ */
    function renderChart() {
        var chartEl = document.getElementById("epugComponentChart");
        var dataEl = document.getElementById("epugChartData");
        if (!chartEl || !dataEl) return;

        chartEl.innerHTML = "";

        try {
            var categories = JSON.parse(dataEl.dataset.categories);
            var seriesData = JSON.parse(dataEl.dataset.series);

            var options = {
                series: [{ name: "Skor Capaian", data: seriesData }],
                chart: {
                    type: "bar",
                    height: 230,
                    toolbar: { show: false },
                    parentHeightOffset: 0,
                },
                colors: ["#067fb2"],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                        columnWidth: "45%",
                        dataLabels: { position: "top" },
                    },
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: { fontSize: "11px", colors: ["#304758"] },
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        style: {
                            fontSize: "11px",
                            fontFamily: "Poppins",
                            fontWeight: 600,
                        },
                    },
                },
                yaxis: {
                    labels: { style: { fontFamily: "Poppins" } },
                },
                tooltip: { theme: "light" },
            };

            var chart = new ApexCharts(chartEl, options);
            chart.render();
        } catch (e) {
            console.error("Gagal memuat grafik PUG:", e);
        }
    }

    /* ══════════════════════════════════════════════════════════════════
       PUBLIC INIT
       ══════════════════════════════════════════════════════════════════ */
    function init(config) {
        _cfg = config || {};
        _cfg.routes = _cfg.routes || {};

        var container = document.querySelector(".epug-wrap");
        if (!container) return;

        bindTreeToggle(container);
        bindSearch();
        bindExport(container);
        renderChart();

        if (!_initialized) {
            bindEvents();
            _initialized = true;
        }

        console.log(
            "[UserEvaluasiPUG] Modul User diinisialisasi. Tahun:",
            _cfg.tahun,
        );
    }

    return { init: init };
})();
