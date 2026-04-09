// Menggunakan pola Event Delegation untuk SPA/AJAX
function filterTablePermintaan() {
    const searchInput = document.getElementById('searchPermintaanAdmin');
    const tableBody = document.querySelector('#tabelPermintaanAdmin tbody');

    // Hentikan jika bukan berada di halaman Permintaan
    if (!tableBody) return;

    const tableRows = tableBody.querySelectorAll('tr.row-data');
    const emptyMessage = document.getElementById('permintaanSearchEmpty');
    const emptyRowAsli = document.querySelector('.permintaan-empty-row');

    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    let matchCount = 0;

    tableRows.forEach(row => {
        const namaDokumen = (row.getAttribute('data-judul') || '').toLowerCase();

        // Karena cuma ada 1 filter (Search text), logikanya lebih sederhana
        if (searchTerm === '' || namaDokumen.includes(searchTerm)) {
            row.style.display = '';
            matchCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (emptyMessage) {
        emptyMessage.style.display = 'none';

        // Jangan jalankan pesan jika tabelnya memang benar-benar kosong dari database
        if (emptyRowAsli && emptyRowAsli.style.display !== 'none' && tableRows.length === 0) {
            return;
        }

        // Jika sedang mengetik sesuatu dan hasil pencocokan = 0
        if (searchTerm !== '') {
            if (matchCount === 0) {
                emptyMessage.style.display = 'table-row';
            }
        }
    }
}

// Menempelkan telinga (Event Listener) ke halaman utama (Delegation)
document.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'searchPermintaanAdmin') {
        filterTablePermintaan();
    }
});