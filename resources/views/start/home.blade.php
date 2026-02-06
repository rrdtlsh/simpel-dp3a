@extends('start.layout')

@section('title', 'Beranda | SIMPEL DP3A')

@section('content')

<!-- ===== HERO ===== -->
<section class="hero" style="background-image:url('{{ asset('images/hero.jpg') }}')">
    <div class="hero-overlay"></div>

    <div class="hero-content fade-in">
        <h1>Sistem Monitoring dan Pengelolaan Dokumen Perencanaan DP3A</h1>
        <p>Informasi, pengumuman, dan penginputan data terintegrasi</p>
        <a href="#pengumuman" class="btn-primary">Lihat Pengumuman</a>
    </div>
</section>

<!-- ===== PENGUMUMAN ===== -->
<section class="news" id="pengumuman">
    <h2 class="section-title">Pengumuman Terkini</h2>

    <div class="news-grid">

        <div class="news-card reveal">
            <span class="badge">PUG</span>
            <h4>Jadwal Penginputan Data PUG</h4>
            <p>Penginputan dibuka mulai <strong>10–30 Mei 2026</strong>.</p>
        </div>

        <div class="news-card reveal">
            <span class="badge info">Update</span>
            <h4>Update Sistem</h4>
            <p>Perbaikan tampilan dan peningkatan performa sistem.</p>
        </div>

    </div>
</section>

@endsection
