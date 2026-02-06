<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SIMPEL DP3A')</title>

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="{{ asset('css/start.css') }}">

    <!-- CSS PER HALAMAN -->
    @stack('styles')
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="header">
    <div class="header-left">
        <img src="{{ asset('images/DPPPA ver2.png') }}" class="logo" alt="Logo DP3A">
    </div>

    <nav class="header-nav">
        <a href="{{ url('/') }}">Home</a>
        <a href="{{ route('login') }}" class="btn-login">Masuk</a>
    </nav>
</header>

<!-- ===== CONTENT ===== -->
<main>
    @yield('content')
</main>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    © {{ date('Y') }} Tim PKL TI FT ULM
</footer>

<!-- JS GLOBAL -->
<script src="{{ asset('js/start.js') }}"></script>

<!-- JS PER HALAMAN -->
@stack('scripts')

</body>
</html>
