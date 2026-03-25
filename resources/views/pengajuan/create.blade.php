@extends('layouts.khp')

@section('title', 'Tambah Pengajuan')

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
        font-weight: 900;
        color: #0f172a;
        margin: 0;
    }

    .subtitle {
        color: var(--muted);
        margin: 6px 0 0 0;
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
        font-weight: 800;
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

    .btn-ghost {
        background: transparent;
        border-color: rgba(2, 132, 199, 0.35);
        color: var(--primary-dark);
    }

    .btn-ghost:hover {
        background: rgba(2, 132, 199, 0.06);
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
        font-weight: 900;
        margin: 0 0 6px 0;
        color: #0f172a;
    }

    .muted {
        color: var(--muted);
        font-size: 13px;
    }

    .details-card {
        background: #f0f9ff;
        border: 1px solid rgba(2, 132, 199, 0.2);
        border-radius: 12px;
        padding: 12px;
        margin-top: 12px;
    }

    .mt-3 {
        margin-top: 12px;
    }

    .mt-6 {
        margin-top: 24px;
    }
</style>
@endpush

@section('content')
<div class="page-bg">
    <div class="card">
        <div class="card-header">
            <div class="row" style="justify-content: space-between;">
                <div>
                    <p class="h1">Tambah Pengajuan</p>
                    <p class="subtitle">Hanya admin yang dapat membuat pengajuan.</p>
                </div>
                <a class="btn btn-ghost" href="{{ route('pengajuan.index') }}">Kembali</a>
            </div>
        </div>

        <div class="card-body">
            @if(auth()->user()->role == 'admin')
                <form method="POST" action="{{ route('pengajuan.store') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="details-card" style="background:#fee2e2;border-color:rgba(220,38,38,.25);">
                            <div style="font-weight:900;color:#991b1b;margin-bottom:8px;">Terjadi kesalahan</div>
                            <ul style="margin:0;padding-left:18px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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

                    <div class="form-grid">
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
                            <div class="muted mt-3">Jika dropdown kosong, pastikan variable `$bidangs` tersedia dari controller.</div>
                        </div>

                        <div>
                            <label class="form-label" for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" required>{{ old('deskripsi') }}</textarea>
                        </div>
                    </div>

                    <div class="row mt-6" style="justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">Simpan Pengajuan</button>
                    </div>
                </form>
            @else
                <div class="details-card" style="background:#e0f2fe;border-color:#bae6fd;">
                    <div style="font-weight:900;color:#075985;">Akses ditolak</div>
                    <div class="muted">Anda tidak memiliki izin untuk membuat pengajuan.</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

