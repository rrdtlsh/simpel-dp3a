@extends('start.layout')

@section('title', 'Beranda | SIMPEL DP3A')

@push('styles')
{{-- 1. CSS Library SwiperJS (Wajib untuk slider) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

{{-- 2. CSS Khusus Halaman Home (Yang baru kita buat) --}}
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

<section class="hero">

    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide" style="background-image: url('{{ asset('images/home_DP3A (1).jpeg') }}');"></div>
            <div class="swiper-slide" style="background-image: url('{{ asset('images/home_DP3A (2).jpeg') }}');"></div>
            <div class="swiper-slide" style="background-image: url('{{ asset('images/home_DP3A (3).jpg') }}');"></div>
        </div>
        <div class="swiper-pagination"></div>
    </div>

    <div class="hero-overlay"></div>

    <div class="hero-content fade-in">
        <h1>Sistem Monitoring dan Pengelolaan Dokumen Perencanaan DP3A</h1>
        <p>Informasi, pengumuman, dan penginputan data terintegrasi</p>
        <br>
        <a href="#pengumuman" class="btn-primary">Lihat Pengumuman</a>
    </div>
</section>

<section class="news" id="pengumuman">
    <div class="container">
        <h2 class="section-title">Pengumuman Terkini</h2>

        <div class="news-grid">

            <div class="news-card reveal">
                <img src="{{ asset('images/pengumuman1.png') }}" class="news-img" alt="Ilustrasi PUG">
                <div class="news-body">
                    <span class="news-badge" style="background: #e74c3c;">PUG</span>
                    <h4 class="news-title">Jadwal Penginputan Data PUG</h4>
                    <p>Penginputan dibuka mulai <strong>10–30 Mei 2026</strong>. Mohon siapkan dokumen terkait.</p>
                </div>
            </div>

            <div class="news-card reveal">
                <img src="{{ asset('images/pengumuman2.jpg') }}" class="news-img" alt="Ilustrasi Update">
                <div class="news-body">
                    <span class="news-badge" style="background: #3498db;">Update</span>
                    <h4 class="news-title">Update Sistem Versi 2.0</h4>
                    <p>Perbaikan tampilan dashboard dan peningkatan keamanan sistem login.</p>
                </div>
            </div>

            <div class="news-card reveal">
                <img src="{{ asset('images/home_DP3A (3).jpg') }}" class="news-img" alt="Kegiatan">
                <div class="news-body">
                    <span class="news-badge" style="background: #27ae60;">Kegiatan</span>
                    <h4 class="news-title">Sosialisasi Pencegahan Kekerasan</h4>
                    <p>Dokumentasi kegiatan sosialisasi di kelurahan Sungai Miai.</p>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
{{-- JS SwiperJS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

{{-- Script Inisialisasi Slider --}}
<script>
    var swiper = new Swiper(".mySwiper", {
        spaceBetween: 0,
        effect: "fade",
        centeredSlides: true,
        speed: 1500,
        allowTouchMove: false,

        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },

        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },

        pagination: {
            el: ".swiper-pagination",
            clickable: false,
        },
    });
</script>
@endpush