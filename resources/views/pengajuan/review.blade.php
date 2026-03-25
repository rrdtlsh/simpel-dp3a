@extends('layouts.khp')

@section('title', 'Review Pengajuan')

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

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid transparent;
        text-transform: capitalize;
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

    .details-card {
        background: #f0f9ff;
        border: 1px solid rgba(2, 132, 199, 0.2);
        border-radius: 12px;
        padding: 12px;
        margin-top: 12px;
    }

    .file-card {
        background: #ffffff;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        padding: 12px;
        margin-top: 12px;
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

    .mt-3 {
        margin-top: 12px;
    }

    .mt-6 {
        margin-top: 24px;
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
@php
    $files = collect();
    if (isset($pengajuanFiles)) {
        $files = $pengajuanFiles;
    } elseif (isset($pengajuan) && $pengajuan) {
        $files = $pengajuan->files ?? collect();
    }
@endphp

<div class="page-bg">
    <div class="card">
        <div class="card-header">
            <div class="row" style="justify-content: space-between;">
                <div>
                    <p class="h1">Review Pengajuan</p>
                    <p class="subtitle">Detail pengajuan dan keputusan admin untuk setiap file.</p>
                </div>
                <a class="btn btn-ghost" href="{{ route('pengajuan.index') }}">Kembali</a>
            </div>
        </div>

        <div class="card-body">
            <div class="details-card">
                <div class="section-title">Pengajuan Detail</div>
                <div class="form-grid">
                    <div>
                        <div class="muted">Judul</div>
                        <div style="font-weight:900;color:#0f172a;">{{ isset($pengajuan) && $pengajuan ? $pengajuan->judul : '-' }}</div>
                    </div>
                    <div>
                        <div class="muted">Due Date</div>
                        <div style="font-weight:900;color:#0f172a;">
                            @if(isset($pengajuan) && $pengajuan->due_date)
                                {{ \Carbon\Carbon::parse($pengajuan->due_date)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="muted">Status Pengajuan</div>
                        <div style="font-weight:900;color:#0f172a;">{{ isset($pengajuan) && $pengajuan ? $pengajuan->status : '-' }}</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="muted">Deskripsi</div>
                    <div style="margin-top:6px;">{{ isset($pengajuan) && $pengajuan ? $pengajuan->deskripsi : '-' }}</div>
                </div>
            </div>

            <div class="mt-6">
                <div class="section-title">Daftar File yang Diunggah</div>

                @forelse($files as $file)
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

                        @if(auth()->user()->role == 'admin')
                            <form method="POST" action="{{ route('pengajuan.review', $file->id) }}" class="mt-3">
                                @csrf
                                <div class="form-grid form-grid-2">
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
                                <div class="row mt-3" style="justify-content:flex-end;">
                                    <button type="submit" class="btn btn-primary">Kirim Review</button>
                                </div>
                            </form>
                        @else
                            <div class="muted mt-3">
                                Anda bukan admin, sehingga form review tidak tersedia.
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="muted">Belum ada file yang diunggah.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

