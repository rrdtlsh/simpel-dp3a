<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIMPEL DP3A</title>

    {{-- CSS Login Custom --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    {{-- CDN SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- CDN Font Awesome (Untuk Ikon Mata) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .text-danger {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
            text-align: left;
        }

        .form-control.is-invalid {
            border: 1px solid #dc3545 !important;
            box-shadow: 0 0 5px rgba(220, 53, 69, 0.3);
        }

        /* Style untuk Password Wrapper (Agar ikon mata bisa di dalam input) */
        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #333;
        }
    </style>
</head>

<body>
    <a href="{{ route('home') }}" class="btn-back">
        ← Kembali ke Beranda
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
                            placeholder="Masukkan NIP (Angka)"
                            maxlength="20"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            value="{{ old('nip') }}">

                        @error('nip')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- INPUT PASSWORD DENGAN IKON MATA --}}
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password"
                                id="passwordInput"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                maxlength="20"
                                placeholder="Masukkan Password">

                            {{-- Ikon Mata --}}
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

    {{-- DATA HIDDEN UNTUK JS (ANTI MERAH VS CODE) --}}
    <div id="login-data"
        style="display: none;"
        data-success="{{ session('success') }}"
        data-user-name="{{ Auth::user() ? Auth::user()->name : '' }}"
        data-has-error="{{ $errors->any() ? 'true' : 'false' }}"
        data-error-messages="{{ json_encode($errors->all()) }}">
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // === LOGIC 1: Toggle Password (Lihat/Tutup) ===
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#passwordInput');

            togglePassword.addEventListener('click', function(e) {
                // Toggle tipe input antara password dan text
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                // Toggle ikon mata (slash atau tidak)
                this.classList.toggle('fa-eye-slash');
            });

            // === LOGIC 2: SweetAlert ===
            var dataDiv = document.getElementById('login-data');
            var sessionSuccess = dataDiv.getAttribute('data-success');
            var hasError = dataDiv.getAttribute('data-has-error') === 'true';
            var rawMessages = dataDiv.getAttribute('data-error-messages');
            var errorMessages = rawMessages ? JSON.parse(rawMessages) : [];

            // NOTIFIKASI SUKSES (Selamat Datang)
            if (sessionSuccess) {
                // Ambil nama user (opsional, jika ingin spesifik)
                // Pesan success biasanya sudah kita set di Controller
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: sessionSuccess, // "Selamat Datang..."
                    showConfirmButton: false,
                    timer: 2000
                });
            }

            // NOTIFIKASI GAGAL (Spesifik: NIP, Password, atau Throttle)
            if (hasError) {
                let title = 'Login Gagal';
                let text = 'Terjadi kesalahan.';
                let icon = 'error';
                let confirmColor = '#d33';

                // Gabungkan semua pesan error jadi satu string
                let fullMessage = errorMessages.join(' ');

                // Cek jenis error
                if (fullMessage.includes('seconds') || fullMessage.includes('detik')) {
                    // Kasus: Terlalu Banyak Percobaan
                    title = 'Akses Dibatasi Sementara';
                    text = fullMessage; // Tampilkan pesan detik/menit dari Laravel
                    icon = 'warning';
                    confirmColor = '#f39c12';
                } else if (fullMessage.includes('NIP')) {
                    // Kasus: NIP Salah
                    text = 'NIP tidak ditemukan dalam sistem.';
                } else if (fullMessage.includes('Password')) {
                    // Kasus: Password Salah
                    text = 'Password yang Anda masukkan salah.';
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