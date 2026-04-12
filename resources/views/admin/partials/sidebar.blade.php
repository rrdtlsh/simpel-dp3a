<div class="sidebar" id="sidebar">
    <ul id="menu">
        {{-- MENU UTAMA --}}
        <li class="active" onclick="loadPage('dashboard', this)">Dashboard</li>
        <li onclick="loadPage('permintaan', this)">Permintaan Dokumen</li>
        <li onclick="loadPage('verifikasi', this)">Verifikasi Dokumen</li>
        <li onclick="loadPage('dokumen-masuk', this)">Dokumen Masuk</li>
        <li onclick="loadPage('evaluasi-pug', this)">Pertanyaan Evaluasi PUG</li>

        {{-- PEMBATAS MENU SUPER ADMIN --}}
        <li style="margin-top: 15px; padding: 10px 15px; font-size: 12px; color: #9ca3af; font-weight: bold; text-transform: uppercase; cursor: default; pointer-events: none;">
            Super Admin
        </li>
        
        {{-- MENU SUPER ADMIN --}}
        <li onclick="loadPage('manage_users', this)">Kelola Akun</li>
        <li onclick="loadPage('pengumuman', this)">Kelola Pengumuman</li>
    </ul>
</div>