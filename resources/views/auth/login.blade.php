<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIMPEL DP3A</title>

    {{-- CSS Login Custom (Sudah mencakup semua style) --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    {{-- CDN SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- CDN Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <a href="{{ route('home') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
    </a>

    <div class="login-container">
        <div class="login-left">
            <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A">
            <h2>
                DINAS PEMBERDAYAAN PEREMPUAN<br>
                DAN PERLINDUNGAN ANAK<br>
                KOTA BANJARMASIN
            </h2>
        </div>

        <div class="login-right">
            <div class="login-box">
                <h3>SIMPEL DP3A</h3>

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    {{-- INPUT NIP --}}
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text"
                            name="nip"
                            class="form-control @error('nip') is-invalid @enderror"
                            required
                            autofocus
                            placeholder="Masukkan NIP (18 Digit)"
                            maxlength="18"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            value="{{ old('nip') }}">

                        @error('nip')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- INPUT PASSWORD --}}
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password"
                                id="passwordInput"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                maxlength="12"
                                placeholder="Masukkan Password">
                            <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                        </div>

                        @error('password')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn-login">Masuk</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        © {{ date('Y') }} Tim PKL TI FT ULM
    </footer>

    {{-- DATA HIDDEN UNTUK JS --}}
    <div id="login-data" style="display: none;"
        data-success="{{ session('success') }}"
        data-has-error="{{ $errors->any() ? 'true' : 'false' }}"
        data-error-messages="{{ json_encode($errors->all()) }}">
    </div>

    {{-- SCRIPT LOGIC (Tidak Berubah) --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. TOGGLE PASSWORD
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#passwordInput');

            togglePassword.addEventListener('click', function(e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // 2. SWEETALERT LOGIC
            var dataDiv = document.getElementById('login-data');
            var sessionSuccess = dataDiv.getAttribute('data-success');
            var hasError = dataDiv.getAttribute('data-has-error') === 'true';
            var rawMessages = dataDiv.getAttribute('data-error-messages');
            var errorMessages = rawMessages ? JSON.parse(rawMessages) : [];

            if (sessionSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: sessionSuccess,
                    showConfirmButton: false,
                    timer: 2000
                });
            }

            if (hasError) {
                let title = 'Login Gagal';
                let text = 'Terjadi kesalahan.';
                let icon = 'error';
                let confirmColor = '#d33';
                let fullMessage = errorMessages.join(' ').toLowerCase();
                let timerInterval;

                // LOGIKA TIME OUT
                if (fullMessage.includes('seconds') || fullMessage.includes('detik')) {
                    title = 'Akses Dibatasi Sementara';
                    icon = 'warning';
                    confirmColor = '#f39c12';

                    let secondsMatch = fullMessage.match(/\d+/);
                    let secondsLeft = secondsMatch ? parseInt(secondsMatch[0]) : 60;

                    Swal.fire({
                        icon: icon,
                        title: title,
                        html: `Terlalu banyak percobaan gagal.<br>Silakan tunggu <b id="countdown" style="font-size: 1.5em;">${secondsLeft}</b> detik.`,
                        confirmButtonColor: confirmColor,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            timerInterval = setInterval(() => {
                                secondsLeft--;
                                const b = Swal.getHtmlContainer().querySelector('#countdown');
                                if (b) b.textContent = secondsLeft;
                                if (secondsLeft <= 0) {
                                    clearInterval(timerInterval);
                                    Swal.close();
                                    location.reload();
                                }
                            }, 1000);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                        }
                    });
                    return;
                }

                // LOGIKA ERROR TEXT
                if (fullMessage.includes('nip tidak ditemukan')) {
                    text = 'NIP tidak ditemukan.';
                } else if (fullMessage.includes('password yang anda masukkan salah') || fullMessage.includes('password yang dimasukkan salah')) {
                    text = 'Password yang dimasukkan salah.';
                } else if (fullMessage.includes('nama dan password yang dimasukkan salah')) {
                    text = 'Nama dan Password yang dimasukkan salah.';
                } else if (fullMessage.includes('wajib diisi')) {
                    text = 'NIP dan Password wajib diisi.';
                } else {
                    text = 'Nama dan Password yang dimasukkan salah.';
                }

                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonColor: confirmColor,
                });
            }
        });
    </script>
</body>

</html>