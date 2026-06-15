@extends('layouts.user')
@section('title', 'Daftar Permintaan Dokumen')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; color: #2c2c2c;">Daftar Permintaan Dokumen</h2>
        <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Daftar dokumen yang harus diunggah oleh bidang Anda.</p>
    </div>
    
    <div style="display: flex; gap: 15px; align-items: center;">
        <select id="filterStatusUser" style="padding: 10px; border-radius: 8px; border: 1px solid #e0e0e0; outline: none; font-family:'Poppins', sans-serif; cursor: pointer; color: #555;">
            <option value="all">Semua Status</option>
            <option value="belum_upload">Belum Diunggah</option>
            <option value="pending">Menunggu Review</option>
            <option value="approved">Diterima</option>
            <option value="rejected">Ditolak</option>
        </select>

        <div style="position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;"></i>
            <input type="text" id="searchPermintaanUser" placeholder="Cari nama dokumen..." 
                   style="padding: 10px 10px 10px 35px; border-radius: 8px; border: 1px solid #e0e0e0; outline: none; width: 250px; font-family:'Poppins', sans-serif;">
        </div>
    </div>
</div>

<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px;">
    <table id="tabelPermintaanUser" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f9fafb; border-bottom: 2px solid #e0e0e0;">
                <th style="padding: 12px; text-align: left; font-size: 14px;">No</th>
                <th style="padding: 12px; text-align: left; font-size: 14px;">Nama Dokumen</th>
                <th style="padding: 12px; text-align: left; font-size: 14px;">Tanggal Dibuat</th>
                <th style="padding: 12px; text-align: left; font-size: 14px;">Deadline</th>
                <th style="padding: 12px; text-align: center; font-size: 14px;">Status Upload</th>
                <th style="padding: 12px; text-align: center; font-size: 14px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $index => $row)
                @php
                    $fileUpload = $row->files->first();
                    $status = $fileUpload ? strtolower(trim($fileUpload->status)) : 'belum_upload';
                    $isTerlambat = \Carbon\Carbon::now()->greaterThan($row->due_date) && in_array($status, ['belum_upload', 'rejected']);

                    // Siapkan array data file
                    $filesArray = collect($fileUpload?->files ?? [])->map(fn($f) => [
                        'name' => $f['original_name'] ?? basename($f['path'] ?? 'File'),
                        'path' => $f['path'] ?? '',
                    ])->values()->toArray();
                @endphp

                <script type="application/json" id="files-user-{{ $row->id }}">@json($filesArray)</script>

                <tr class="row-data" data-judul="{{ strtolower($row->judul) }}" data-status="{{ $status }}" style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-size: 14px;">{{ $index + 1 }}</td>
                    
                    <td style="padding: 12px; font-size: 14px; font-weight: 500;">
                        {{ $row->judul }}
                        @if($isTerlambat)
                            <br><span style="display: inline-block; margin-top: 4px; background: #dc2626; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;"><i class="fa-solid fa-triangle-exclamation"></i> Terlambat</span>
                        @endif
                    </td>

                    <td style="padding: 12px; font-size: 14px; color: #555;">
                        {{ $row->created_at ? $row->created_at->format('d M Y H:i') : '-' }}
                    </td>
                    
                    {{-- ✅ INI BAGIAN YANG DIUBAH: Warna merah tetap (#E74A3B) dan tebal (600) --}}
                    <td style="padding: 12px; font-size: 14px; color: #E74A3B; font-weight: 600;">
                        {{ \Carbon\Carbon::parse($row->due_date)->format('d M Y H:i') }}
                    </td>

                    <td style="padding: 12px; text-align: center; font-size: 14px;">
                        @if($status === 'belum_upload')
                            <span style="background: #f1f3f5; color: #555; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Belum Diunggah</span>
                        @elseif($status === 'pending')
                            <span style="background: #fff8e1; color: #fbc02d; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Sedang Direview</span>
                        @elseif($status === 'rejected')
                            <span style="background: #ffebee; color: #d32f2f; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Revisi (Ditolak)</span>
                        @else
                            <span style="background: #e8f5e9; color: #388e3c; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px;">Selesai (Arsip)</span>
                        @endif
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            
                            {{-- Tombol Lihat --}}
                            <button type="button" class="action-lihat-user" 
                                data-id="{{ $row->id }}" 
                                data-nama="{{ $row->judul }}" 
                                data-tgl="{{ $row->created_at?->format('d M Y') }}"
                                data-tahun="{{ $row->tahun ?? '-' }}"
                                data-status="{{ $status }}" 
                                data-deskripsi="{{ $row->deskripsi ?? 'Tidak ada instruksi.' }}"
                                data-catatan="{{ $fileUpload->admin_notes ?? '' }}" 
                                data-user-notes="{{ $fileUpload->user_notes ?? '' }}"
                                style="background: #f3f4f6; color: #374151; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;" 
                                title="Lihat Detail & Dokumen">
                                <i class="fa-solid fa-eye"></i> Lihat
                            </button>
                            
                            {{-- Tombol Upload / Revisi --}}
                            @if($status !== 'approved')
                                <button type="button" 
                                onclick="openUploadModal({{ $row->id }}, `{{ addslashes($row->deskripsi ?? 'Tidak ada instruksi khusus dari Admin.') }}`, 'files-user-{{ $row->id }}', `{{ addslashes($fileUpload ? ($fileUpload->user_notes ?? '') : '') }}`)"
                                style="background: {{ $status === 'rejected' ? '#d32f2f' : '#067fb2' }}; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;"
                                title="{{ $status === 'rejected' ? 'Re-upload Dokumen' : 'Upload Dokumen' }}">
                                <i class="fa-solid {{ $status === 'rejected' ? 'fa-rotate-right' : 'fa-upload' }}"></i> 
                                {{ $status === 'rejected' ? 'Revisi' : 'Upload' }}
                            </button>
                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #888;">Belum ada permintaan dokumen untuk bidang Anda.</td>
                </tr>
            @endforelse
            <tr id="permintaanSearchEmpty" style="display:none;">
                <td colspan="6" style="text-align: center; padding: 40px 20px; background: #fff;">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:3rem; color:#d1d5db; margin-bottom: 12px; display:block;"></i>
                    <h4 style="margin: 0 0 6px 0; color: #374151; font-size: 1.1rem;">Hasil Pencarian Tidak Ditemukan</h4>
                    <p style="margin: 0; color: #9ca3af; font-size: 0.85rem;">Coba gunakan kata kunci pencarian yang lain.</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection