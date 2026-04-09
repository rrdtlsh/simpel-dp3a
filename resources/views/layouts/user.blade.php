<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>@yield('title') | SIMPEL DP3A</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Poppins', sans-serif; }
        .swal2-container { z-index: 99999 !important; }
        .profile-dropdown a:hover, .profile-dropdown button:hover { background-color: #f4f6f8 !important; }
        
        /* CSS SPLASH SCREEN LOADER */
        #loader {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: #ffffff; z-index: 999999; 
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            transition: opacity 0.5s ease;
        }
        .loader-logo { width: 120px; margin-bottom: 20px; animation: pulse 1.5s infinite alternate; }
        .loader-spinner {
            width: 40px; height: 40px; border: 4px solid var(--abu-bg, #f4f6f8);
            border-top: 4px solid var(--biru-utama, #067fb2); border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin  { to { transform: rotate(360deg); } }
        @keyframes pulse { to { transform: scale(1.05); } }
    </style>

    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/modal_password.css') }}?v={{ time() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')
</head>

<body>
    {{-- ✅ HTML & JS SPLASH SCREEN (Dengan Trik SessionStorage) --}}
    <div id="loader">
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A" class="loader-logo">
        <div class="loader-spinner"></div>
    </div>

    <script>
        // Cek apakah halaman ini dimuat karena klik dari Sidebar
        if (sessionStorage.getItem('sidebar_click') === 'true') {
            // Jika YA: Matikan splash screen logo secara instan
            document.getElementById('loader').style.display = 'none';
            sessionStorage.removeItem('sidebar_click'); // Bersihkan memori
        } else {
            // Jika TIDAK (Misal: Refresh manual (F5) atau baru login): Jalankan animasi logo
            window.addEventListener('load', function () {
                var loader = document.getElementById('loader');
                if (loader) {
                    setTimeout(function () {
                        loader.style.opacity = '0';
                        setTimeout(function () { loader.style.display = 'none'; }, 500);
                    }, 300);
                }
            });
        }
    </script>
    {{-- ✅ AKHIR SPLASH SCREEN --}}


    {{-- HEADER (Termasuk Notifikasi & Profil) --}}
    <div class="header">
        <div class="header-left">
            <div class="hamburger" id="hamburger">☰</div>
            <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo">
        </div>

        <div class="header-right">
            @php
                $unreadNotifs = auth()->user()->unreadNotifications ?? collect();
                $namaBidang = Auth::user()->bidang ? Auth::user()->bidang->nama : Auth::user()->name;
            @endphp

            <div class="notif-wrapper">
                <button class="btn-bell" id="notifButton">
                    <i class="fa-regular fa-bell" style="font-size: 20px;"></i>
                    @if($unreadNotifs->count() > 0)
                        <span class="notif-badge">{{ $unreadNotifs->count() }}</span>
                    @endif
                </button>

                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span>Notifikasi</span>
                        @if($unreadNotifs->count() > 0)
                            <a href="#" id="markAllRead" style="font-size: 12px; font-weight:normal;">Tandai semua dibaca</a>
                        @endif
                    </div>
                    <div class="notif-body">
                        @forelse($unreadNotifs as $notif)
                            <a href="#" class="notif-item unread"
                                data-notif-id="{{ $notif->id }}"
                                data-page="{{ $notif->data['page'] ?? 'permintaan' }}"
                                data-pengajuan-id="{{ $notif->data['pengajuan_id'] ?? '' }}"
                                onclick="handleNotifClickUser(event, this)">
                                <div class="notif-icon" style="background: {{ $notif->data['color'] }}; color: {{ $notif->data['text_color'] }};">
                                    <i class="fa-solid {{ $notif->data['icon'] }}"></i>
                                </div>
                                <div class="notif-text">
                                    <p><b>{{ $notif->data['pengirim'] ?? '' }}</b> {{ $notif->data['pesan'] }} <b>{{ $notif->data['judul'] }}</b>.</p>
                                    <span>{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div style="padding: 20px; text-align: center; color: #888; font-size: 13px;">
                                <i class="fa-regular fa-bell-slash" style="font-size: 24px; margin-bottom: 8px; color: #ccc;"></i><br>
                                Belum ada notifikasi baru.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="user-info-wrapper" style="position: relative; margin-left: 15px;">
                <div class="user-info" id="userDropdownBtn" style="cursor: pointer; padding: 5px 10px; border-radius: 6px; transition: background 0.2s; display:flex; align-items:center; gap:8px;">
                    <div class="user-icon">
                        <img src="{{ asset('images/accicon.png') }}" alt="User" style="width:26px; height:26px;">
                    </div>
                    <span style="font-weight:600; font-size:14px; color:#2c2c2c;">{{ $namaBidang }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px; color:#555;"></i>
                </div>

                <div class="profile-dropdown" id="profileDropdown" style="display: none; position: absolute; right: 0; top: 110%; background: #ffffff; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border-radius: 8px; width: 190px; z-index: 1000; overflow: hidden; border: 1px solid #e0e0e0;">
                    <a href="#" id="btnOpenUbahPassword" style="display: block; padding: 12px 16px; color: #2c2c2c; text-decoration: none; border-bottom: 1px solid #f0f0f0; font-size:14px;">
                        <i class="fa-solid fa-key" style="margin-right: 8px; color: #067fb2; width: 16px;"></i> Ubah Password
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" id="formLogout" style="margin: 0;" onsubmit="confirmLogout(event, this)">
                        @csrf
                        <button type="submit" style="width: 100%; text-align: left; background: none; border: none; padding: 12px 16px; color: #E74A3B; cursor: pointer; font-weight: 600; font-family: inherit; font-size: 14px;">
                            <i class="fa-solid fa-right-from-bracket" style="margin-right: 8px; width: 16px;"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper">
        {{-- SIDEBAR --}}
        <div class="sidebar" id="sidebar">
            <ul id="menu">
                <li class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('user.dashboard') }}">Dashboard Bidang</a>
                </li>
                <li class="{{ request()->routeIs('user.permintaan') ? 'active' : '' }}">
                    <a href="{{ route('user.permintaan') }}">Daftar Permintaan</a>
                </li>
                <li class="{{ request()->routeIs('user.arsip') ? 'active' : '' }}">
                    <a href="{{ route('user.arsip') }}">Arsip Dokumen</a>
                </li>
                <li class="{{ request()->routeIs('user.evaluasi-pug.*') ? 'active' : '' }}">
                    <a href="{{ route('user.evaluasi-pug.index') }}">Pertanyaan Evaluasi PUG</a>
                </li>
            </ul>
        </div>

        {{-- ✅ KONTEN UTAMA (Tambahkan min-width: 0 agar grafik bisa mengecil) --}}
        <main class="content" id="main-content" style="flex:1; padding:24px; background:#f4f6f8; min-height:calc(100vh - 64px); min-width: 0; overflow-x: hidden;">
            @yield('content')
        </main>
    </div>

    @include('admin.partials.modal_password')
    @include('user.partials.modal-upload')

    {{-- ✅ JS INTERCEPTOR KLIK SIDEBAR UNTUK EFEK LOADING --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#menu a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var href = this.getAttribute('href');
                    if (href && href !== '#') {
                        // 1. Titipkan pesan ke browser agar halaman selanjutnya tidak pakai splash screen
                        sessionStorage.setItem('sidebar_click', 'true');
                        
                        // 2. Ganti konten di sebelah kanan dengan animasi loading persis seperti admin
                        var mainContent = document.getElementById('main-content');
                        if (mainContent) {
                            mainContent.innerHTML = 
                                '<div style="padding:40px; text-align:center; height:50vh; display:flex; flex-direction:column; justify-content:center; align-items:center;">' +
                                '<i class="fa-solid fa-spinner fa-spin fa-2x" style="color:#067fb2;"></i>' +
                                '<p style="margin-top:12px; color:#6b7280; font-weight:500;">Memuat data...</p>' +
                                '</div>';
                        }
                    }
                });
            });
        });
    </script>

    @stack('scripts')
    @include('user.partials.scripts')

</body>
</html>