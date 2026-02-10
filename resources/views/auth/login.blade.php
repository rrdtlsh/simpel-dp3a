<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login | SIMPEL DP3A</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <a href="{{ route('home') }}" class="btn-back">
        ← Kembali ke Beranda
    </a>

    <div class="login-container">

        <!-- KIRI -->
        <div class="login-left">
            <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A">
            <h2>
                DINAS PEMBERDAYAAN PEREMPUAN<br>
                DAN PERLINDUNGAN ANAK<br>
                KOTA BANJARMASIN
            </h2>
        </div>

        <!-- KANAN -->
        <div class="login-right">
            <div class="login-box">
                <h3>SIMPEL DP3A</h3>

                {{-- INI PENTING: pakai route login Laravel --}}
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label>NIP</label>
                        {{-- Ubah name='email' jadi name='nip' dan type='text' --}}
                        <input type="text" name="nip" class="form-control" required autofocus placeholder="Masukkan NIP">
                        @error('nip')
                        <small style="color:red">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <button type="submit" class="btn-login">Masuk</button>
                </form>

            </div>
        </div>

    </div>

    <footer>
        © Tim PKL TI FT ULM
    </footer>

    <script src="{{ asset('js/login.js') }}"></script>
</body>

</html>