@extends('layouts.khp')

@section('title', 'Unggah Dokumen | Bidang KHP')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/unggah_khp.css') }}">
@endpush

@section('content')

<div class="section-header">
    <h2>Unggah Dokumen</h2>
</div>

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

@php
    $dataPermintaan = [
        [
            'nama' => 'Dokumen Rencana Aksi PUG',
            'bidang' => 'Kualitas Hidup Perempuan',
            'tanggal' => '2026-02-12',
            'status' => 'Masuk'
        ],
        [
            'nama' => 'Laporan Monitoring Gender',
            'bidang' => 'Perlindungan Perempuan',
            'tanggal' => '2026-02-11',
            'status' => 'Menunggu'
        ],
        [
            'nama' => 'Data Indeks Gender',
            'bidang' => 'Pemenuhan Hak Anak',
            'tanggal' => '2026-02-10',
            'status' => 'Masuk'
        ]
    ];
@endphp

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

    <button class="btn-edit">
        <img src="{{ asset('images/edit.png') }}" alt="Edit">
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

@endsection

@push('scripts')
<script src="{{ asset('js/unggah_khp.js') }}"></script>
@endpush
