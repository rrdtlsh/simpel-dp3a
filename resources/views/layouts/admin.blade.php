<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <title>@yield('title')</title>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- CSS Utama Admin --}}
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">

    {{-- Tempat untuk menyisipkan CSS khusus dari halaman lain --}}
    @stack('styles')
</head>
<body>

    {{-- Splash Screen Loader --}}
    <div id="loader">
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A" class="loader-logo">
        <div class="loader-spinner"></div>
    </div>

    {{-- Konten Utama Halaman --}}
    @yield('content')

    {{-- Script Utama Admin (Sekarang loader berjalan dari file ini) --}}
    <script src="{{ asset('js/dashboardadmin.js') }}"></script>

    {{-- Tempat untuk menyisipkan JS khusus dari halaman lain --}}
    @stack('scripts')
    
</body>
</html>