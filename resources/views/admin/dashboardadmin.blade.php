@extends('layouts.admin')
@section('title', 'Dashboard | SIMPEL DP3A')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/permintaan_admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/verifikasi_admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/dokumen_masuk.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/modal_password.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/evaluasi_pug.css') }}">
@endpush

@section('content')

@include('admin.partials.header')

<div class="wrapper">
    @include('admin.partials.sidebar')

    <div class="content">
        <div id="main-content">

            <div class="dash-header">
                <h2>Halo, {{ Auth::user()->name ?? 'Admin' }}! 👋</h2>
                <p>Ringkasan data sistem hari ini.</p>
            </div>

            <div class="dash-stat-grid">
                <div class="dash-stat-card" style="border-left-color: #067fb2;">
                    <div class="dash-stat-info">
                        <p>Total Permintaan</p>
                        <span>{{ \App\Models\Pengajuan::count() }}</span>
                    </div>
                    <i class="fa-solid fa-file-lines dash-stat-icon"></i>
                </div>
                
                <div class="dash-stat-card" style="border-left-color: #f59e0b;">
                    <div class="dash-stat-info">
                        <p>Menunggu Review</p>
                        <span>{{ \App\Models\PengajuanFile::where('status','pending')->count() }}</span>
                    </div>
                    <i class="fa-solid fa-hourglass-half dash-stat-icon"></i>
                </div>
                
                <div class="dash-stat-card" style="border-left-color: #22c55e;">
                    <div class="dash-stat-info">
                        <p>Total Arsip Masuk</p>
                        <span>{{ \App\Models\PengajuanFile::where('status','approved')->count() }}</span>
                    </div>
                    <i class="fa-solid fa-box-archive dash-stat-icon"></i>
                </div>
                
                <div class="dash-stat-card" style="border-left-color: #ef4444;">
                    <div class="dash-stat-info">
                        <p>Ditolak / Revisi</p>
                        <span>{{ \App\Models\PengajuanFile::where('status','rejected')->count() }}</span>
                    </div>
                    <i class="fa-solid fa-circle-xmark dash-stat-icon"></i>
                </div>
            </div>

            <div class="dash-chart-grid">
                <div class="dash-chart-card status-chart-card">
                    <h4 class="dash-chart-header">
                        <i class="fa-solid fa-chart-bar" style="color:#067fb2;"></i> Status Dokumen
                    </h4>
                    <div id="adminChartBar"></div>
                </div>
                
                <div class="dash-chart-card ketuntasan-chart-card">
                    <h4 class="dash-chart-header">
                        <i class="fa-solid fa-chart-pie" style="color:#22c55e;"></i> % Ketuntasan
                    </h4>
                    <div id="adminChartDonut"></div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin.partials.modal_password')

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @include('admin.partials.scripts')
    <script src="{{ asset('js/evaluasi_pug.js') }}"></script>
@endpush