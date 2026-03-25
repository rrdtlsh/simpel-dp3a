@extends('layouts.khp')

@section('title', 'Data Pengajuan')

@push('styles')
<style>
    :root {
        --primary: #0284c7;
        --primary-dark: #0369a1;
        --success: #16a34a;
        --danger: #dc2626;
        --bg: #e9f7ff;
        --card-border: rgba(2, 132, 199, 0.25);
        --muted: #64748b;
    }

    .page-bg {
        background: var(--bg);
        border: 1px solid rgba(2, 132, 199, 0.18);
        border-radius: 14px;
        padding: 18px;
    }

    .card {
        background: #ffffff;
        border: 1px solid var(--card-border);
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(2, 132, 199, 0.08);
    }

    .card-header {
        padding: 16px 16px 10px 16px;
        border-bottom: 1px solid rgba(2, 132, 199, 0.12);
    }

    .card-body {
        padding: 16px;
    }

    .h1 {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 6px 0;
    }

    .subtitle {
        color: var(--muted);
        margin: 0;
        font-size: 13px;
    }

    .row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    .btn-primary {
        background: var(--primary);
        color: #ffffff;
        border-color: rgba(2, 132, 199, 0.5);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
    }

    .btn-success {
        background: var(--success);
        color: #ffffff;
        border-color: rgba(22, 163, 74, 0.55);
    }

    .btn-success:hover {
        filter: brightness(0.97);
    }

    .btn-danger {
        background: var(--danger);
        color: #ffffff;
        border-color: rgba(220, 38, 38, 0.55);
    }

    .btn-danger:hover {
        filter: brightness(0.97);
    }

    .btn-ghost {
        background: transparent;
        border-color: rgba(2, 132, 199, 0.35);
        color: var(--primary-dark);
    }

    .btn-ghost:hover {
        background: rgba(2, 132, 199, 0.06);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid transparent;
        text-transform: capitalize;
    }

    .badge-open {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .badge-closed {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .badge-approved {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .table-wrap {
        overflow-x: auto;
        border-radius: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 920px;
    }

    th {
        text-align: left;
        padding: 12px 12px;
        background: #eff6ff;
        color: #1d4ed8;
        border-bottom: 1px solid #dbeafe;
        font-size: 13px;
    }

    td {
        padding: 12px 12px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
        font-size: 14px;
        color: #0f172a;
    }

    .muted {
        color: var(--muted);
        font-size: 13px;
    }

    .truncate {
        max-width: 360px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .help-text {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-top: 8px;
    }

    @media (min-width: 768px) {
        .form-grid-2 {
            grid-template-columns: 1fr 1fr;
        }
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 14px;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    label.form-label {
        display: block;
        font-size: 13px;
        font-weight: 800;
        margin: 0 0 6px 0;
        color: #0f172a;
    }

    .mt-3 {
        margin-top: 12px;
    }

    .mt-6 {
        margin-top: 24px;
    }

    .mb-2 {
        margin-bottom: 8px;
    }

    details.details {
        margin: 0;
    }

    details.details > summary {
        list-style: none;
        cursor: pointer;
    }

    details.details > summary::-webkit-details-marker {
        display: none;
    }

    details.details[open] > summary {
        margin-bottom: 12px;
    }

    .details-card {
        background: #f0f9ff;
        border: 1px solid rgba(2, 132, 199, 0.2);
        border-radius: 12px;
        padding: 12px;
        margin-top: 10px;
    }

    .file-card {
        background: #ffffff;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        padding: 12px;
        margin-top: 12px;
    }

    .section-title {
        font-weight: 900;
        color: #0f172a;
        font-size: 15px;
        margin: 0 0 10px 0;
    }
</style>
@endpush

@section('content')
<div class="page-bg">
    @if (session('success'))
        <div class="details-card" style="background:#dcfce7;border-color:rgba(22,163,74,.25);">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="details-card" style="background:#fee2e2;border-color:rgba(220,38,38,.25);">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="details-card" style="background:#fee2e2;border-color:rgba(220,38,38,.25);">
            <div class="section-title">Terjadi kesalahan</div>
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row" style="justify-content: space-between;">
                <div>
                    <p class="h1">Data Pengajuan</p>
                    <p class="subtitle">Pengajuan ditampilkan sesuai role dan bidang user.</p>
                </div>

                <div class="row">
                    @if(auth()->user()->role == 'admin')
                        <details class="details">
                            <summary class="btn btn-primary">Tambah Pengajuan</summary>
                            <div class="details-card">
                                <div class="section-title">Buat Pengajuan Baru</div>
                                <form method="POST" action="{{ route('pengajuan.store') }}" class="mt-3">
                                    @csrf
                                    <div class="form-grid form-grid-2">
                                        <div>
                                            <label class="form-label" for="judul">Judul</label>
                                            <input id="judul" name="judul" type="text" class="form-control" value="{{ old('judul') }}" required>
                                        </div>
                                        <div>
                                            <label class="form-label" for="due_date">Due Date</label>
                                            <input id="due_date" name="due_date" type="date" class="form-control" value="{{ old('due_date') }}">
                                        </div>
                                    </div>
                                    <div class="form-grid mt-3">
                                        <div>
                                            <label class="form-label" for="bidang_id">Bidang</label>
                                            <select id="bidang_id" name="bidang_id" class="form-control" required>
                                                <option value="" disabled selected>Pilih Bidang</option>
                                                @forelse(($bidangs ?? []) as $b)
                                                    <option value="{{ $b->id }}" {{ old('bidang_id') == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                                                @empty
                                                    <option value="" disabled>Daftar bidang belum tersedia</option>
                                                @endforelse
                                            </select>
                                            <div class="muted mt-3">Pastikan controller menyiapkan `$bidangs` untuk dropdown.</div>
                                        </div>
                                        <div>
                                            <label class="form-label" for="deskripsi">Deskripsi</label>
                                            <textarea id="deskripsi" name="deskripsi" class="form-control" required>{{ old('deskripsi') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row mt-3" style="justify-content:flex-end;">
                                        <button type="submit" class="btn btn-primary">Simpan Pengajuan</button>
                                    </div>
                                </form>
                            </div>
                        </details>

                        <a class="btn btn-ghost" href="#review-panel">Review</a>
                    @elseif(auth()->user()->role == 'user')
                        @php $firstPengajuanId = optional($pengajuans->first())->id; @endphp
                        <a class="btn btn-primary" href="{{ $firstPengajuanId ? '#upload-' . $firstPengajuanId : '#' }}">Upload File</a>
                        <span class="badge badge-open" style="background:#e0f2fe;border-color:#bae6fd;color:#075985;">Mode User</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pengajuans as $p)
                            <tr>
                                <td style="font-weight:800;">{{ $p->judul }}</td>
                                <td>
                                    <div class="truncate">{{ $p->deskripsi }}</div>
                                </td>
                                <td>
                                    @if($p->due_date)
                                        {{ \Carbon\Carbon::parse($p->due_date)->format('d/m/Y') }}
                                    @else
                                        <span class="muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->status == 'open')
                                        <span class="badge badge-open">open</span>
                                    @elseif($p->status == 'closed')
                                        <span class="badge badge-closed">closed</span>
                                    @else
                                        <span class="badge">{{ $p->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(auth()->user()->role == 'admin')
                                        <details class="details">
                                            <summary class="btn btn-primary">Review</summary>
                                            <div class="details-card" style="margin-top:12px;">
                                                <div class="section-title">File yang Diunggah</div>
                                                @forelse($p->files as $file)
                                                    <div class="file-card">
                                                        <div class="row" style="justify-content: space-between;">
                                                            <div class="row">
                                                                @php
                                                                    $fileUrl = \Illuminate\Support\Facades\Storage::url($file->file_path);
                                                                @endphp
                                                                <a class="btn btn-ghost" href="{{ $fileUrl }}" target="_blank">Buka File</a>
                                                            </div>
                                                            <div>
                                                                @if($file->status == 'pending')
                                                                    <span class="badge badge-pending">pending</span>
                                                                @elseif($file->status == 'approved')
                                                                    <span class="badge badge-approved">approved</span>
                                                                @elseif($file->status == 'rejected')
                                                                    <span class="badge badge-rejected">rejected</span>
                                                                @else
                                                                    <span class="badge">{{ $file->status }}</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <form method="POST" action="{{ route('pengajuan.review', $file->id) }}" class="mt-3">
                                                            @csrf
                                                            <div class="form-grid">
                                                                <div>
                                                                    <label class="form-label" for="status_{{ $file->id }}">Keputusan</label>
                                                                    <select id="status_{{ $file->id }}" name="status" class="form-control" required>
                                                                        <option value="approved" {{ $file->status == 'approved' ? 'selected' : '' }}>Approve</option>
                                                                        <option value="rejected" {{ $file->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                                                                    </select>
                                                                </div>
                                                                <div>
                                                                    <label class="form-label" for="notes_{{ $file->id }}">Notes</label>
                                                                    <textarea id="notes_{{ $file->id }}" name="notes" class="form-control">{{ old("notes.$file->id", $file->admin_notes) }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3" style="justify-content: flex-end;">
                                                                <button type="submit" class="btn btn-primary">Simpan Review</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @empty
                                                    <div class="muted">Belum ada file yang diunggah.</div>
                                                @endforelse
                                            </div>
                                        </details>
                                    @elseif(auth()->user()->role == 'user')
                                        <details class="details">
                                            <summary class="btn btn-primary">Upload File</summary>
                                            <div class="details-card">
                                                <div id="upload-{{ $p->id }}"></div>
                                                <form method="POST" action="{{ route('pengajuan.upload', $p->id) }}" enctype="multipart/form-data" class="mt-3">
                                                    @csrf
                                                    <div>
                                                        <label class="form-label" for="file_{{ $p->id }}">Pilih File</label>
                                                        <input id="file_{{ $p->id }}" name="file" type="file" class="form-control" required>
                                                        <div class="help-text">Format file mengikuti konfigurasi server.</div>
                                                    </div>
                                                    <div class="row mt-3" style="justify-content: flex-end;">
                                                        <button type="submit" class="btn btn-success">Upload</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(auth()->user()->role == 'admin')
                <div id="review-panel" class="mt-6">
                    <div class="muted">Gunakan tombol `Review` pada tiap baris untuk approve atau reject.</div>
                </div>
            @endif

            <div class="pagination">
                {{ $pengajuans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection