<div class="wrapper">
        {{-- ✅ SIDEBAR --}}
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
            </ul>
        </div>

        {{-- ✅ KONTEN UTAMA --}}
        <main class="content" style="flex:1; padding:24px; background:#f4f6f8; min-height:calc(100vh - 64px);">
            @yield('content')
        </main>
    </div>