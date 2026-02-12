<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">

    <!-- CSS KHUSUS HALAMAN -->
    @stack('styles')
</head>
<body>

<!-- ================= HEADER ================= -->
<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo">
    </div>

    <div class="header-right">

        <div class="notif-wrapper">
        <button class="btn-bell">
            <img src="{{ asset('images/bell.png') }}" alt="Notifikasi">
        </button>
    </div>
        <div class="user-info">
            <div class="user-icon">
                <img src="{{ asset('images/accicon.png') }}" alt="User">
            </div>
            <span>KHP</span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </div>
</div>

<!-- ================= WRAPPER ================= -->
<div class="wrapper">

<div class="sidebar" id="sidebar">
    <ul>
        <li>Permintaan Dokumen</li>
        <li>Unggah Dokumen</li>
        <li>Pertanyaan Evaluasi PUG</li>
    </ul>
</div>

    <!-- ================= CONTENT ================= -->
    <main class="content">
        @yield('content')
    </main>

</div>

<!-- ================= JS GLOBAL ================= -->
<script src="{{ asset('js/dashboardadmin.js') }}"></script>

<!-- JS KHUSUS HALAMAN -->
@stack('scripts')

<script>
    // Toggle sidebar
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');

    if (hamburger && sidebar) {
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('hide');
        });
    }
</script>

</body>
</html>
