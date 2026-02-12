@extends('layouts.khp')

@section('title', 'Permintaan Dokumen | Bidang KHP')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/permintaan_khp.css') }}">
@endpush

@section('content')

<div class="section-header">
    <h2>Permintaan Masuk</h2>
</div>


<!-- ================= TABLE ================= -->
<div class="table-box">
<table>
<thead>
<tr>
<th>No</th>
<th>Nama Dokumen</th>
<th>Bidang</th>
<th>Tanggal</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>

<tbody>

@php
    $dataPermintaan = [
        [
            'nama' => 'Dokumen RPJMD 2026',
            'bidang' => 'Kualitas Hidup Perempuan',
            'tanggal' => '2026-02-12',
            'status' => 'Masuk'
        ],
        [
            'nama' => 'Laporan Evaluasi PUG',
            'bidang' => 'Perlindungan Perempuan',
            'tanggal' => '2026-02-11',
            'status' => 'Menunggu'
        ],
        [
            'nama' => 'Data Gender Kabupaten',
            'bidang' => 'Pemenuhan Hak Anak',
            'tanggal' => '2026-02-10',
            'status' => 'Masuk'
        ]
    ];
@endphp

<!-- ===== CARD STATISTIK ===== -->
<div class="card-container">
    <div class="card">
        <h4>Permintaan Masuk</h4>
    </div>

    <div class="card">
        <h4>Total Permintaan</h4>
    </div>
</div>

@forelse($dataPermintaan as $no => $row)
<tr>
<td>{{ $no + 1 }}</td>
<td>{{ $row['nama'] }}</td>
<td>{{ $row['bidang'] }}</td>
<td>{{ $row['tanggal'] }}</td>
<td class="{{ $row['status'] == 'Masuk' ? 'status-masuk' : 'status-menunggu' }}">
    {{ $row['status'] }}
</td>
<td>
<div class="action-btn">
    <button class="btn-see">
        <img src="{{ asset('images/eye.png') }}" alt="See">
    </button>

    <button class="btn-delete">
        <img src="{{ asset('images/delete.png') }}" alt="Delete">
    </button>
</div>
</td>
</tr>

@empty
<tr>
<td colspan="6" style="text-align:center;">Belum ada data permintaan</td>
</tr>
@endforelse

</tbody>

</table>
</div>

</div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/permintaan_khp.js') }}"></script>
@endpush