@extends('layouts.admin') {{-- pakai layout yg sama dengan admin --}}

@section('title', 'Permintaan Dokumen | Bidang KHP')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/user/permintaan_khp.css') }}">
@endpush

@section('content')

<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="{{ asset('images/DPPPA ver2.png') }}">
    </div>

    <div class="header-right">
        <div class="user-info">
            <div class="user-icon">
                <img src="{{ asset('images/accicon.png') }}">
            </div>
            <span>Bidang KHP</span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-logout">Logout</button>
        </form>
    </div>
</div>

<div class="wrapper">

<div class="sidebar" id="sidebar">
    <ul>
        <li class="active">Permintaan Dokumen</li>
        <li><a href="{{ route('khp.unggah') }}" style="text-decoration:none; color:inherit;">Unggah Dokumen</a></li>
        <li>Pertanyaan Evaluasi PUG</li>
    </ul>
</div>

<div class="content">

<div class="section-header">
    <h2>Permintaan Dokumen Masuk</h2>
</div>

<!-- ================= TABLE ================= -->
<div class="table-box">
<table>
<thead>
<tr>
<th>No</th>
<th>Nama Dokumen</th>
<th>Tanggal Permintaan</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
    <tr>
        <td>1</td>
        <td>Laporan Kegiatan KHP 2025</td>
        <td>20/01/2026</td>
        <td>
            <div class="action-btn">
                <a class="btn-delete" data-id="1" title="Hapus">
                    <img src="{{ asset('images/delete.png') }}">
                </a>
            </div>
        </td>
    </tr>
    <tr>
        <td>2</td>
        <td>Data Program Pemberdayaan</td>
        <td>19/01/2026</td>
        <td>
            <div class="action-btn">
                <a class="btn-delete" data-id="2" title="Hapus">
                    <img src="{{ asset('images/delete.png') }}">
                </a>
            </div>
        </td>
    </tr>
</tbody>
</table>
</div>

</div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/user/permintaan_khp.js') }}"></script>
@endpush
