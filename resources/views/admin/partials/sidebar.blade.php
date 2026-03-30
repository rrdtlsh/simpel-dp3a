<div class="sidebar" id="sidebar">
    <ul id="menu">
        <li class="active" onclick="loadPage('dashboard', this)">Dashboard</li>
        <li onclick="loadPage('permintaan', this)">Permintaan Dokumen</li>
        <li onclick="loadPage('verifikasi', this)">Verifikasi Dokumen</li>
        <li onclick="loadPage('dokumen-masuk', this)">Dokumen Masuk</li>
        <li onclick="loadPage('evaluasi-pug', this)" class="{{ request()->is('admin/content/evaluasi-pug') ? 'active' : '' }}">
    <a href="#">Pertanyaan Evaluasi PUG</a>
    </ul>
</div>