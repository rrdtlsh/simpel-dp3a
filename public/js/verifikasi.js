function filterTableVerifikasi() {
    const searchInput = document.getElementById("searchVerifikasiAdmin");
    const filterBidang = document.getElementById("filterBidangVerif");
    const filterStatus = document.getElementById("filterStatusVerif");

    const tableBody = document.querySelector("#tabelVerifikasiAdmin tbody");

    if (!tableBody) return;

    const tableRows = tableBody.querySelectorAll("tr.row-data");
    const emptyMessage = document.getElementById("verifikasiSearchEmpty");
    const emptyRowAsli = document.querySelector(".verifikasi-empty-row");

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
    if (e.target && e.target.id === "searchVerifikasiAdmin") {
        filterTableVerifikasi();
    }
});

document.addEventListener("change", function (e) {
    if (
        e.target &&
        (e.target.id === "filterBidangVerif" ||
            e.target.id === "filterStatusVerif")
    ) {
        filterTableVerifikasi();
    }
});
