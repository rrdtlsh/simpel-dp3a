@extends('layouts.admin')
@section('title', 'Dashboard | SIMPEL DP3A')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/permintaan_admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/verifikasi_admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dokumen_masuk.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modal_password.css') }}?v={{ time() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
        .profile-dropdown a:hover, .profile-dropdown button:hover { background-color: #f4f6f8 !important; }
        .user-info-wrapper { display: flex; align-items: center; }
        .swal2-container { z-index: 99999 !important; }
    </style>
@endpush

@section('content')

{{-- 1. Panggil Header --}}
@include('admin.partials.header')

<div class="wrapper">
    {{-- 2. Panggil Sidebar --}}
    @include('admin.partials.sidebar')

    <div class="content">
        <div id="main-content">
            {{-- 3. Konten Default: Dashboard Widgets & Charts --}}
            <div style="margin-bottom:20px;">
                <h2 style="margin:0; font-weight:700; color:#2c2c2c;">
                    Halo, {{ Auth::user()->name ?? 'Admin' }}! 👋
                </h2>
                <p style="margin:4px 0 0; color:#6b7280; font-size:.875rem;">Ringkasan data sistem hari ini.</p>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin-bottom:24px;">
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #067fb2; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="margin:0 0 6px; font-size:.78rem; font-weight:600; color:#6b7280;">Total Permintaan</p>
                        <span style="font-size:1.8rem; font-weight:700; color:#111827;">{{ \App\Models\Pengajuan::count() }}</span>
                    </div>
                    <i class="fa-solid fa-file-lines" style="font-size:2rem; color:#e5e7eb;"></i>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #f59e0b; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="margin:0 0 6px; font-size:.78rem; font-weight:600; color:#6b7280;">Menunggu Review</p>
                        <span style="font-size:1.8rem; font-weight:700; color:#111827;">{{ \App\Models\PengajuanFile::where('status','pending')->count() }}</span>
                    </div>
                    <i class="fa-solid fa-hourglass-half" style="font-size:2rem; color:#e5e7eb;"></i>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #22c55e; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="margin:0 0 6px; font-size:.78rem; font-weight:600; color:#6b7280;">Total Arsip Masuk</p>
                        <span style="font-size:1.8rem; font-weight:700; color:#111827;">{{ \App\Models\PengajuanFile::where('status','approved')->count() }}</span>
                    </div>
                    <i class="fa-solid fa-box-archive" style="font-size:2rem; color:#e5e7eb;"></i>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #ef4444; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="margin:0 0 6px; font-size:.78rem; font-weight:600; color:#6b7280;">Ditolak / Revisi</p>
                        <span style="font-size:1.8rem; font-weight:700; color:#111827;">{{ \App\Models\PengajuanFile::where('status','rejected')->count() }}</span>
                    </div>
                    <i class="fa-solid fa-circle-xmark" style="font-size:2rem; color:#e5e7eb;"></i>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:24px;">
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,.06);">
                    <h4 style="margin:0 0 16px; font-size:.9rem; font-weight:700; color:#374151; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
                        <i class="fa-solid fa-chart-bar" style="color:#067fb2; margin-right:6px;"></i>Status Dokumen
                    </h4>
                    <div id="adminChartBar"></div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,.06);">
                    <h4 style="margin:0 0 16px; font-size:.9rem; font-weight:700; color:#374151; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
                        <i class="fa-solid fa-chart-pie" style="color:#22c55e; margin-right:6px;"></i>% Ketuntasan
                    </h4>
                    <div id="adminChartDonut"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. Panggil Modal --}}
@include('admin.partials.modal_password')

@endsection

@push('scripts')
    {{-- 5. Panggil Logika Javascript --}}
    @include('admin.partials.scripts')
@endpush