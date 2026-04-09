// 1. FUNGSI FILTER & PENCARIAN
function filterTableDokumen() {
    const searchInput = document.getElementById('searchArsipAdmin');
    const filterBidang = document.getElementById('filterBidang');
    const filterTahun = document.getElementById('filterTahun');

    const tableBody = document.querySelector('#dokumenTable tbody');
    if (!tableBody) return; // Hentikan jika bukan di halaman Dokumen Masuk

    const tableRows = tableBody.querySelectorAll('tr.row-data');
    const emptyMessage = document.getElementById('arsipSearchEmpty');
    const emptyRowAsli = document.querySelector('.arsip-empty-row');

    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const bidangTerm = filterBidang ? filterBidang.value.toLowerCase().trim() : '';
    const tahunTerm = filterTahun ? filterTahun.value.trim() : '';

    let matchCount = 0;

    tableRows.forEach(row => {
        const namaDokumen = (row.getAttribute('data-judul') || '').toLowerCase();
        const rowBidang = (row.getAttribute('data-bidang') || '').toLowerCase();
        const rowTahun = row.getAttribute('data-tahun') || '';

        const matchSearch = searchTerm === '' || namaDokumen.includes(searchTerm);
        const matchBidang = bidangTerm === '' || rowBidang === bidangTerm;
        const matchTahun = tahunTerm === '' || rowTahun === tahunTerm;

        if (matchSearch && matchBidang && matchTahun) {
            row.style.display = '';
            matchCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (emptyMessage) {
        emptyMessage.style.display = 'none';
        if (emptyRowAsli && emptyRowAsli.style.display !== 'none' && tableRows.length === 0) {
            return;
        }
        if (searchTerm !== '' || bidangTerm !== '' || tahunTerm !== '') {
            if (matchCount === 0) {
                emptyMessage.style.display = 'table-row';
            }
        }
    }
}

// 2. EVENT LISTENER GLOBAL (EVENT DELEGATION UNTUK SPA)
// Akan menangkap ketikan dari elemen manapun yang punya ID 'searchArsipAdmin'
document.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'searchArsipAdmin') {
        filterTableDokumen();
    }
});

document.addEventListener('change', function (e) {
    if (e.target && (e.target.id === 'filterBidang' || e.target.id === 'filterTahun')) {
        filterTableDokumen();
    }
});