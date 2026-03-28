@extends('layouts.user')
@section('title', 'Dashboard Bidang')

@push('styles')
    {{-- Memanggil ApexCharts & File CSS Eksternal --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="{{ asset('css/dashboarduser.css') }}">
@endpush

@section('content')

{{-- ── Halo Header ── --}}
<div class="dash-header">
    <h2>Halo, {{ Auth::user()->bidang ? Auth::user()->bidang->nama : Auth::user()->name }}! 👋</h2>
    <p>Selamat datang di portal SIMPEL DP3A Kota Banjarmasin.</p>
</div>

{{-- ── Statistik Card Grid ── --}}
<div class="dash-stat-grid">
    <div class="dash-stat-card" style="--accent: #067fb2">
        <div>
            <p class="stat-label">Total Permintaan</p>
            <span class="stat-value" id="statTotalPermintaan">{{ $stats['total'] ?? 0 }}</span>
        </div>
        <i class="fa-solid fa-file-circle-plus stat-icon"></i>
    </div>

    <div class="dash-stat-card" style="--accent: #f59e0b">
        <div>
            <p class="stat-label">Menunggu Upload</p>
            <span class="stat-value" id="statMenunggu">{{ $stats['open'] ?? 0 }}</span>
        </div>
        <i class="fa-solid fa-clock stat-icon"></i>
    </div>

    <div class="dash-stat-card" style="--accent: #ef4444">
        <div>
            <p class="stat-label">Perlu Revisi</p>
            <span class="stat-value" id="statDitolak">{{ $stats['rejected'] ?? 0 }}</span>
        </div>
        <i class="fa-solid fa-circle-xmark stat-icon"></i>
    </div>

    <div class="dash-stat-card" style="--accent: #22c55e">
        <div>
            <p class="stat-label">Dokumen Selesai</p>
            <span class="stat-value" id="statApproved">{{ $stats['approved'] ?? 0 }}</span>
        </div>
        <i class="fa-solid fa-check-circle stat-icon"></i>
    </div>
</div>

{{-- ── Chart Grid ── --}}
<div class="dash-chart-grid">
    {{-- Chart 1: Progress Bar Ketuntasan --}}
    <div class="dash-chart-card">
        <h4><i class="fa-solid fa-chart-bar" style="color:#067fb2;"></i> Status Permintaan Dokumen</h4>
        <div id="chartPermintaan"></div>
    </div>

    {{-- Chart 2: Donut % Ketuntasan --}}
    <div class="dash-chart-card">
        <h4><i class="fa-solid fa-chart-pie" style="color:#22c55e;"></i> % Ketuntasan</h4>
        <div id="chartDonut"></div>
    </div>
</div>

{{-- ── Info Panduan ── --}}
<div class="info-card">
    <h3><i class="fa-solid fa-circle-info" style="color:#067fb2;"></i> Panduan Cepat</h3>
    <ul class="info-list">
        <li>Gunakan menu <b>Daftar Permintaan</b> untuk melihat dan mengunggah dokumen yang diminta Admin.</li>
        <li>Gunakan menu <b>Arsip Dokumen</b> untuk melihat riwayat dokumen yang telah diverifikasi.</li>
        <li>Perhatikan selalu <b>Tenggat Waktu (Deadline)</b> pada setiap permintaan.</li>
        <li>Dokumen yang <b>Ditolak</b> dapat diunggah ulang setelah membaca catatan Admin.</li>
    </ul>
</div>

@endsection

@push('scripts')
<script>
(function() {
    // ── Data dari Laravel (menggunakan json_encode agar aman) ─────────────────
    var stats = {!! json_encode($stats ?? ['total'=>0, 'open'=>0, 'pending'=>0, 'rejected'=>0, 'approved'=>0]) !!};

    // ── Chart 1: Bar Chart Status Permintaan ─────────────────────────────────
    var barOptions = {
        chart  : { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'Poppins' },
        series : [{
            name: 'Jumlah',
            data: [
                stats.open     || 0,
                stats.pending  || 0,
                stats.rejected || 0,
                stats.approved || 0,
            ]
        }],
        colors     : ['#f59e0b', '#3b82f6', '#ef4444', '#22c55e'],
        plotOptions: { bar: { distributed: true, borderRadius: 6, columnWidth: '55%' } },
        dataLabels : { enabled: true, style: { fontFamily: 'Poppins', fontSize: '12px' } },
        xaxis: {
            categories: ['Belum Upload', 'Menunggu Review', 'Ditolak', 'Selesai'],
            labels    : { style: { fontFamily: 'Poppins', fontSize: '11px' } },
        },
        yaxis  : { labels: { style: { fontFamily: 'Poppins' } } },
        legend : { show: false },
        grid   : { borderColor: '#f3f4f6' },
    };
    new ApexCharts(document.getElementById('chartPermintaan'), barOptions).render();

    // ── Chart 2: Donut % Ketuntasan ──────────────────────────────────────────
    var total    = stats.total || 1; 
    var approved = stats.approved || 0;
    var pct      = Math.round((approved / total) * 100);

    var donutOptions = {
        chart  : { type: 'donut', height: 220, fontFamily: 'Poppins' },
        series : [approved, total - approved],
        labels : ['Selesai', 'Belum Selesai'],
        colors : ['#22c55e', '#f3f4f6'],
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show : true,
                        total: {
                            show      : true,
                            label     : 'Ketuntasan',
                            fontSize  : '12px',
                            fontFamily: 'Poppins',
                            color     : '#6b7280',
                            formatter : function() { return pct + '%'; }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontFamily: 'Poppins', fontSize: '12px' },
    };
    new ApexCharts(document.getElementById('chartDonut'), donutOptions).render();
})();
</script>
@endpush