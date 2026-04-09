// ─────────────────────────────────────────────────────────────────
// LOGIKA PENCARIAN HALAMAN VERIFIKASI (EVENT DELEGATION UNTUK SPA)
// ─────────────────────────────────────────────────────────────────

function filterTableVerifikasi() {
    const searchInput = document.getElementById('searchVerifikasiAdmin');
    const tableBody = document.querySelector('#tabelVerifikasiAdmin tbody');

    // Hentikan eksekusi jika bukan sedang berada di halaman Verifikasi
    if (!tableBody) return;

    const tableRows = tableBody.querySelectorAll('tr.row-data');
    const emptyMessage = document.getElementById('verifikasiSearchEmpty');
    const emptyRowAsli = document.querySelector('.verifikasi-empty-row');

    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    let matchCount = 0;

    // Looping setiap baris tabel
    tableRows.forEach(row => {
        const namaDokumen = (row.getAttribute('data-judul') || '').toLowerCase();

        // Cek kecocokan teks
        if (searchTerm === '' || namaDokumen.includes(searchTerm)) {
            row.style.display = '';
            matchCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Menampilkan Pesan Kosong
    if (emptyMessage) {
        emptyMessage.style.display = 'none';

        // Jangan jalankan jika tabel memang aslinya kosong (Belum ada pengajuan)
        if (emptyRowAsli && emptyRowAsli.style.display !== 'none' && tableRows.length === 0) {
            return;
        }

        // Jika user mengetik sesuatu dan hasilnya nol
        if (searchTerm !== '') {
            if (matchCount === 0) {
                emptyMessage.style.display = 'table-row';
            }
        }
    }
}

// Menempelkan Telinga Global (Event Listener) ke Halaman SPA
document.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'searchVerifikasiAdmin') {
        filterTableVerifikasi();
    }
});