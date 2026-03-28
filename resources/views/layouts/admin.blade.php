<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">

    @stack('styles')

    <style>
        body, button, input, textarea {
            font-family: 'Poppins', sans-serif !important;
        }
        #loader {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #ffffff;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease;
        }
        .loader-logo   { width: 120px; margin-bottom: 20px; animation: pulse 1.5s infinite alternate; }
        .loader-spinner {
            width: 40px; height: 40px;
            border: 4px solid var(--abu-bg, #f4f6f8);
            border-top: 4px solid var(--biru-utama, #067fb2);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin  { to { transform: rotate(360deg); } }
        @keyframes pulse { to { transform: scale(1.05); }   }
    </style>
</head>
<body>

    <div id="loader">
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A" class="loader-logo">
        <div class="loader-spinner"></div>
    </div>

    <script>
        window.addEventListener('load', function () {
            var loader = document.getElementById('loader');
            if (loader) {
                setTimeout(function () {
                    loader.style.opacity = '0';
                    setTimeout(function () { loader.style.display = 'none'; }, 500);
                }, 300);
            }
        });
    </script>

    @yield('content')

    <script src="{{ asset('js/dashboardadmin.js') }}"></script>

    @stack('scripts')
</body>
</html>
