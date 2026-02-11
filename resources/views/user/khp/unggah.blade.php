@extends('layouts.admin')

@section('title', 'Unggah Dokumen | Bidang KHP')

@push('styles')
{{-- Menggunakan CSS khusus Unggah --}}
<link rel="stylesheet" href="{{ asset('css/user/unggah_khp.css') }}">
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
            <li><a href="{{ route('khp.permintaan') }}" style="text-decoration:none; color:inherit;">Permintaan Dokumen</a></li>
            <li class="active">Unggah Dokumen</li>
            <li>Pertanyaan Evaluasi PUG</li>
        </ul>
    </div>

    <div class="content">
        <div class="section-header">
            <h2>Dokumen Unggahan</h2>
            <button class="btn-add" id="btnTambahUnggah">+ Unggah Dokumen</button>
        </div>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Dokumen</th>
                        <th>Bidang</th>
                        <th>Tahun</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Laporan Tahunan 2025.pdf</td>
                        <td>KHP</td>
                        <td>2025</td>
                        <td>
                            <div class="action-btn">
                                <a class="btn-view" title="Lihat"><img src="{{ asset('images/eye.png') }}"></a>
                                <a class="btn-edit" title="Edit"><img src="{{ asset('images/edit.png') }}"></a>
                                <a class="btn-delete" title="Hapus"><img src="{{ asset('images/delete.png') }}"></a>
                            </div>
                        </td>
                    </tr>
                    @for ($i = 2; $i <= 10; $i++)
                    <tr>
                        <td>{{ $i }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <div class="action-btn">
                                <a class="btn-view"><img src="{{ asset('images/eye.png') }}"></a>
                                <a class="btn-edit"><img src="{{ asset('images/edit.png') }}"></a>
                                <a class="btn-delete"><img src="{{ asset('images/delete.png') }}"></a>
                            </div>
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal khusus unggah --}}
<div class="modal" id="modalUnggah">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Form Unggah Dokumen</h3>
            <span id="closeModalUnggah" style="cursor:pointer;">✖</span>
        </div>
        <form id="formUnggahDokumen">
            <input type="text" placeholder="Nama Dokumen" required>
            <input type="number" placeholder="Tahun" required>
            <input type="file" required>
            <button type="submit">Simpan Dokumen</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
{{-- Menggunakan JS khusus Unggah --}}
<script src="{{ asset('js/user/unggah_khp.js') }}"></script>
@endpush