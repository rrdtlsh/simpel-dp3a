@extends('start.layout')

@section('title', 'Beranda | SIMPEL DP3A')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@section('content')

<div id="loader">
    <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A" class="loader-logo">
    <div class="loader-spinner"></div>
</div>

<nav class="bjm-navbar">
    <div class="nav-brand">
        <img src="{{ asset('images/DPPPA ver2.png') }}" alt="Logo DP3A">
        <div class="brand-text">
            <h2>DP3A</h2>
            <p>Kota Banjarmasin</p>
        </div>
    </div>

    <div class="nav-links">
        <a href="#pengumuman" class="nav-item">Informasi</a>

        <div class="dropdown">
            <span class="nav-item">Bidang Organisasi <i class="fa-solid fa-chevron-down" style="font-size:10px; margin-left:5px;"></i></span>
            <div class="dropdown-content">
                {{-- Link Kosong (Sesuai Permintaan) --}}
                <a href="#">Sekretariat</a>
                <a href="#">Perencanaan</a>

                {{-- Link Website Eksternal --}}
                <a href="https://dpppa.banjarmasinkota.go.id/search/label/Kualitas%20Hidup%20Perempuan" target="_blank">
                    Kualitas Hidup Perempuan
                </a>

                <a href="https://dpppa.banjarmasinkota.go.id/search/label/Pemenuhan%20Hak%20Anak" target="_blank">
                    Pemenuhan Hak Anak
                </a>

                <a href="https://dpppa.banjarmasinkota.go.id/search/label/Perlindungan%20Perempuan" target="_blank">
                    Perlindungan Perempuan
                </a>

                <a href="https://dpppa.banjarmasinkota.go.id/search/label/Perlindungan%20Khusus%20Anak" target="_blank">
                    Perlindungan Khusus Anak
                </a>
            </div>
        </div>

        <a href="#manual-book" class="nav-item">Manual Book</a>
        <a href="#lokasi-kantor" class="nav-item">Tentang Kami</a>

        <a href="https://wa.me/6281250003039" target="_blank" class="nav-item btn-wa">
            <i class="fa-brands fa-whatsapp" style="font-size: 18px;"></i> Kontak
        </a>
    </div>

    <a href="{{ route('login') }}" class="btn-masuk">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk
    </a>
</nav>

<section class="hero-section">
    <div class="hero-overlay"></div>

    <div class="hero-container">
        <div class="hero-left fade-in">
            <span class="label-welcome"><i class="fa-solid fa-lock"></i> Portal Internal</span>

            <h1>SIMPEL <span>DP3A</span></h1>

            <h2 class="hero-subtitle">Sistem Monitoring dan Pengelolaan<br>Dokumen Perencanaan</h2>
            <p class="hero-instansi">Dinas Pemberdayaan Perempuan dan Perlindungan Anak<br>Kota Banjarmasin</p>

            <div class="hero-narasi">
                <p>
                    Hadir untuk <strong>mengintegrasikan data perencanaan</strong> dan mempermudah pencarian arsip.
                    Dilengkapi fitur pemantauan kinerja unit kerja secara <em>real-time</em>.
                </p>
            </div>

            <ul class="hero-features">
                <li><i class="fa-solid fa-circle-check"></i> Hak Akses Terstruktur</li>
                <li><i class="fa-solid fa-circle-check"></i> Verifikasi Dokumen Digital</li>
                <li><i class="fa-solid fa-circle-check"></i> Database Terpusat</li>
                <li><i class="fa-solid fa-circle-check"></i> Dashboard Real-time</li>
            </ul>

            <a href="#pengumuman" class="btn-hero">
                Pengumuman & Informasi <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="hero-right fade-in">
            <img src="{{ asset('images/maskot.png') }}" alt="Ilustrasi Layanan" class="hero-img-custom">
        </div>
    </div>
</section>

<section class="news" id="pengumuman">
    <div class="container">

        <div class="section-header">
            <h2 class="section-title">Pengumuman & Informasi</h2>
            <div class="title-line"></div>
            <p class="section-desc">Pembaruan terkini terkait kegiatan dan agenda DP3A Kota Banjarmasin.</p>
        </div>

        <div class="news-grid">
            <div class="news-card reveal">
                <div class="card-img-wrapper">
                    <img src="{{ asset('images/pengumuman1.png') }}" alt="PUG">
                    <span class="card-badge red">Penting</span>
                </div>
                <div class="card-body">
                    <div class="card-date"><i class="fa-regular fa-calendar"></i> 10 Mei 2026</div>
                    <h4 class="card-title">Jadwal Penginputan Data PUG Tahun 2026</h4>
                    <p class="card-text">Penginputan data Pengarusutamaan Gender (PUG) dibuka untuk seluruh SKPD.</p>
                </div>
            </div>

            <div class="news-card reveal">
                <div class="card-img-wrapper">
                    <img src="{{ asset('images/pengumuman2.jpg') }}" alt="Sistem">
                    <span class="card-badge blue">Sistem</span>
                </div>
                <div class="card-body">
                    <div class="card-date"><i class="fa-regular fa-calendar"></i> 12 Mei 2026</div>
                    <h4 class="card-title">Pembaruan Sistem SIMPEL DP3A V.2.0</h4>
                    <p class="card-text">Peningkatan performa dashboard dan fitur keamanan login terbaru.</p>
                </div>
            </div>

            <div class="news-card reveal">
                <div class="card-img-wrapper">
                    <img src="{{ asset('images/home_DP3A (3).jpg') }}" alt="Kegiatan">
                    <span class="card-badge green">Kegiatan</span>
                </div>
                <div class="card-body">
                    <div class="card-date"><i class="fa-regular fa-calendar"></i> 15 Mei 2026</div>
                    <h4 class="card-title">Layanan Penjangkauan melalui Konseling Psikologis PraNikah</h4>
                    <p class="card-text">Dokumentasi kegiatan sosialisasi di KUA Banjarmasin Barat</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="manual-section" id="manual-book">
    <div class="container">
        <h2 class="section-title">Manual Book</h2>
        <div class="title-line"></div>
        <p class="section-desc">Panduan penggunaan aplikasi SIMPEL DP3A.</p>

        <div class="book-viewer-container reveal">

            <div class="swiper-btn-custom btn-prev">
                <i class="fa-solid fa-chevron-left"></i>
            </div>

            <div class="swiper bookSwiper book-wrapper">
                <div class="swiper-wrapper">
                    {{-- Slide 1 --}}
                    <div class="swiper-slide book-slide">
                        <img src="{{ asset('images/page1.jpg') }}" alt="Halaman 1">
                    </div>
                    {{-- Slide 2 --}}
                    <div class="swiper-slide book-slide">
                        <img src="{{ asset('images/page2.jpg') }}" alt="Halaman 2">
                    </div>
                    {{-- Slide 3 --}}
                    <div class="swiper-slide book-slide">
                        <img src="{{ asset('images/page3.jpg') }}" alt="Halaman 3">
                    </div>
                </div>
            </div>

            <div class="swiper-btn-custom btn-next">
                <i class="fa-solid fa-chevron-right"></i>
            </div>

        </div>

        <p style="margin-top: 15px; font-size: 13px; color: #888;">
            <i class="fa-solid fa-hand-pointer"></i> Klik panah atau geser gambar untuk melihat halaman selanjutnya
        </p>

        <a href="{{ asset('docs/manual_book.pdf') }}" class="btn-download" download>
            <i class="fa-solid fa-file-pdf"></i> Unduh Manual Book (PDF)
        </a>
    </div>
</section>

{{-- REVISI MAPS SECTION --}}
<section class="maps-section" id="lokasi-kantor">
    <div class="container">
        <h2 class="section-title">Lokasi Kantor</h2>
        <div class="title-line"></div>

        <div class="location-card reveal">
            <div class="loc-image-map">
                {{-- Menggunakan IFRAME sesuai permintaan (URL placeholder dari user) --}}
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.2158585496113!2d114.60134577449834!3d-3.2966582411273055!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de4239c50810335%3A0x31a8371931372e41!2sDinas%20Pemberdayaan%20Perempuan%20dan%20Perlindungan%20Anak%20Kota%20Banjarmasin!5e0!3m2!1sid!2sid!4v1770844501847!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            <div class="loc-info">
                <h3>Dinas Pemberdayaan Perempuan dan Perlindungan Anak</h3>

                <p class="address">
                    <i class="fa-solid fa-location-dot"></i>
                    Gedung Capil, Jl. Sultan Adam No.49, Surgi Mufti, Kec. Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70122
                </p>

                <p class="jam-ops">
                    <i class="fa-regular fa-clock"></i>
                    <span>
                        Senin - Kamis: 08.00 - 16.30 WITA<br>
                        Jumat : 07.30 - 11.00 WITA
                    </span>
                </p>

                <br>

                {{-- Tombol Rute --}}
                <a href="https://maps.app.goo.gl/JLNr8BGT1dsdVXQk7" target="_blank" class="btn-rute">
                    <i class="fa-solid fa-diamond-turn-right"></i> Petunjuk Rute (Google Maps)
                </a>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    // 1. REVISI: Refresh Halaman Kembali ke Atas
    if (history.scrollRestoration) {
        history.scrollRestoration = 'manual';
    } else {
        window.onbeforeunload = function() {
            window.scrollTo(0, 0);
        }
    }

    // Loading Screen
    window.addEventListener('load', function() {
        const loader = document.getElementById('loader');
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
                window.scrollTo(0, 0); // Force scroll top after load
            }, 500);
        }, 800);
    });

    // Reveal Animation
    window.addEventListener('scroll', function() {
        var reveals = document.querySelectorAll('.reveal');
        for (var i = 0; i < reveals.length; i++) {
            var windowHeight = window.innerHeight;
            var elementTop = reveals[i].getBoundingClientRect().top;
            if (elementTop < windowHeight - 100) {
                reveals[i].classList.add('active');
            }
        }
    });

    // Script Khusus Slider Manual Book
    var bookSwiper = new Swiper(".bookSwiper", {
        effect: "cards",
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: "auto",

        // Tambahan agar tidak bug saat di-load
        observer: true,
        observeParents: true,

        // Efek Cards yang lebih halus
        cardsEffect: {
            perSlideOffset: 8, // Jarak antar tumpukan kartu
            perSlideRotate: 2, // Rotasi tumpukan
            slideShadows: true, // Bayangan
        },

        autoplay: {
            delay: 4000,
            disableOnInteraction: true, // Berhenti auto jika user mengklik
        },

        navigation: {
            nextEl: ".btn-next",
            prevEl: ".btn-prev",
        },
    });
</script>
@endpush