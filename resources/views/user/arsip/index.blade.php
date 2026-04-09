@extends('layouts.user')
@section('title', 'Arsip Dokumen Masuk')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="margin: 0; color: #2c2c2c;">Arsip Dokumen Masuk</h2>
        <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Riwayat dokumen yang telah disetujui (Selesai).</p>
    </div>
    
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        {{-- Search --}}
        <div style="position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888;"></i>
            <input type="text" id="searchArsipUser" placeholder="Cari nama dokumen..." 
                   style="padding: 10px 10px 10px 35px; border-radius: 8px; border: 1px solid #e0e0e0; outline: none; width: 220px; font-family:'Poppins', sans-serif;">
        </div>
        
        {{-- Filter Tahun --}}
        <select id="filterTahunUser" style="padding: 10px; border-radius: 8px; border: 1px solid #e0e0e0; outline: none; font-family:'Poppins', sans-serif; background: #fff;">
            <option value="">Semua Tahun</option>
            @foreach($tahuns as $t)
                <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
        
        {{-- Export PDF --}}
        <button id="btnExportPdfUser"
            style="padding: 10px 16px; background: #E74A3B; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
        </button>

        {{-- Export Excel --}}
        <button id="btnExportExcelUser"
            style="padding: 10px 16px; background: #22c55e; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-file-excel"></i> Export Excel
        </button>
    </div>
</div>

<div class="card" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px;">
    <table id="tabelArsipUser" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f9fafb; border-bottom: 2px solid #e0e0e0;">
                <th style="padding: 12px; text-align: left; font-size: 14px; width: 5%;">No</th>
                <th style="padding: 12px; text-align: left; font-size: 14px;">Nama Dokumen</th>
                <th style="padding: 12px; text-align: center; font-size: 14px;">Tahun</th>
                <th style="padding: 12px; text-align: center; font-size: 14px;">Tanggal Diterima</th>
                <th style="padding: 12px; text-align: center; font-size: 14px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $index => $row)
                @php
                    $fileUpload = $row->files->first();
                    $filesArray = collect($fileUpload?->files ?? [])->map(fn($f) => [
                        'name' => $f['original_name'] ?? basename($f['path'] ?? 'File'),
                        'path' => $f['path'] ?? '',
                    ])->values()->toArray();
                @endphp

                <script type="application/json" id="files-user-arsip-{{ $row->id }}">@json($filesArray)</script>

                <tr class="row-data" data-judul="{{ strtolower($row->judul) }}" data-tahun="{{ $row->tahun }}" style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-size: 14px;">{{ $index + 1 }}</td>
                    <td style="padding: 12px; font-size: 14px; font-weight: 500;">{{ $row->judul }}</td>
                    <td style="padding: 12px; text-align: center; font-size: 14px;">{{ $row->tahun ?? '-' }}</td>
                    <td style="padding: 12px; text-align: center; font-size: 14px; color: #388e3c; font-weight: bold;">
                        {{ $fileUpload ? $fileUpload->updated_at->format('d M Y H:i') : '-' }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button type="button" class="action-lihat-arsip-user" 
                            data-id="{{ $row->id }}" 
                            data-nama="{{ $row->judul }}" 
                            data-tgl="{{ $row->created_at?->format('d M Y') }}"
                            data-tahun="{{ $row->tahun ?? '-' }}"
                            data-status="approved" 
                            data-catatan="{{ $fileUpload->admin_notes ?? '' }}" 
                            data-user-notes="{{ $fileUpload->user_notes ?? '' }}"
                            style="background: #f3f4f6; color: #374151; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;">
                            <i class="fa-solid fa-eye"></i> Lihat
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #888;">Belum ada arsip dokumen yang diterima.</td>
                </tr>
            @endforelse
            <tr id="arsipSearchEmpty" style="display:none;">
                <td colspan="5" style="text-align: center; padding: 40px 20px; background: #fff;">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:3rem; color:#d1d5db; margin-bottom: 12px; display:block;"></i>
                    <h4 style="margin: 0 0 6px 0; color: #374151; font-size: 1.1rem;">Hasil Pencarian Tidak Ditemukan</h4>
                    <p style="margin: 0; color: #9ca3af; font-size: 0.85rem;">Coba gunakan kata kunci pencarian yang lain.</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    // Fitur Pencarian & Filter Tahun
    function filterArsipUser() {
        let textFilter = document.getElementById('searchArsipUser')?.value.toLowerCase() || '';
        let yearFilter = document.getElementById('filterTahunUser')?.value || '';
        let emptyRow = document.getElementById('arsipSearchEmpty');
        
        let rows = document.querySelectorAll('#tabelArsipUser tbody .row-data');
        let matchCount = 0;

        rows.forEach(row => {
            let judul = row.dataset.judul || '';
            let tahun = row.dataset.tahun || '';
            
            let matchText = judul.includes(textFilter);
            let matchYear = yearFilter === '' || tahun === yearFilter;
            
            if (matchText && matchYear) {
                row.style.display = '';
                matchCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyRow) {
            emptyRow.style.display = (matchCount === 0 && (textFilter !== '' || yearFilter !== '')) ? '' : 'none';
        }
    }

    document.getElementById('searchArsipUser')?.addEventListener('input', filterArsipUser);
    document.getElementById('filterTahunUser')?.addEventListener('change', filterArsipUser);

    // Fitur Lihat Modal (Mirip Permintaan)
    document.querySelectorAll('.action-lihat-arsip-user').forEach(btn => {
        btn.addEventListener('click', function() {
            let rowId = this.dataset.id;
            let files = [];
            
            try { 
                let jsonText = document.getElementById('files-user-arsip-' + rowId)?.textContent;
                files = JSON.parse(jsonText || '[]'); 
            } catch(e) {}
            
            let listHtml = files.map(f => {
                let ext = f.name.split('.').pop().toLowerCase();
                let icon = ext === 'pdf' ? '<i class="fa-solid fa-file-pdf" style="color:#E74A3B;"></i>' : '<i class="fa-solid fa-file-word" style="color:#2563eb;"></i>';
                return `<li style="margin-bottom: 8px; display: flex; justify-content: space-between; align-items:center; background: #f9fafb; padding: 10px; border-radius: 6px; border: 1px solid #eee;">
                    <span style="font-size: 13px; color:#374151;">${icon} ${f.name}</span>
                    <a href="/storage/${f.path}" target="_blank" download style="font-size: 12px; font-weight: bold; color: #067fb2; text-decoration: none;"><i class="fa-solid fa-download"></i> Unduh</a>
                </li>`;
            }).join('');

            let catatanHtml = '';
            let catatanAdmin = this.dataset.catatan.trim();
            if (catatanAdmin !== '') {
                catatanHtml = `
                    <div style="margin-top: 16px; background: #f0fdf4; border-left: 4px solid #16a34a; padding: 12px; border-radius: 4px; text-align: left;">
                        <span style="color: #166534; font-weight: bold; font-size: 13px;"><i class="fa-solid fa-comment-dots"></i> Catatan Admin (Diterima):</span>
                        <div style="margin: 6px 0 0 0; color: #14532d; font-size: 13.5px;">${catatanAdmin}</div>
                    </div>`;
            }

            Swal.fire({
                title: 'Detail Arsip Dokumen',
                html: `
                    <div style="text-align: left; font-family: 'Poppins', sans-serif;">
                        <p style="margin-top:0; font-size:14px; color:#374151;"><strong>Judul:</strong> ${this.dataset.nama}</p>
                        <p style="margin-top:0; font-size:14px; color:#374151;"><strong>Tahun:</strong> ${this.dataset.tahun}</p>
                        <h4 style="margin: 16px 0 10px; font-size: 13.5px; color:#111827;">Lampiran Dokumen Final:</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">${listHtml || '<li style="color:#888; font-size:13px; text-align:center;">Tidak ada file.</li>'}</ul>
                        ${catatanHtml}
                    </div>
                `,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#6b7280',
                width: '500px'
            });
        });
    });

    // === FITUR EXPORT DENGAN LOADING (SAMA SEPERTI ADMIN) ===
    document.getElementById('btnExportPdfUser')?.addEventListener('click', function(e) {
        e.preventDefault();
        let tahun = document.getElementById('filterTahunUser')?.value || '';

        Swal.fire({ title: 'Menyiapkan PDF...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        window.location.href = '{{ route("user.export.pdf") }}' + '?tahun=' + tahun;

        setTimeout(() => Swal.close(), 3000);
    });

    document.getElementById('btnExportExcelUser')?.addEventListener('click', function(e) {
        e.preventDefault();
        let tahun = document.getElementById('filterTahunUser')?.value || '';
        
        Swal.fire({ title: 'Menyiapkan Excel...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        
        window.location.href = '{{ route("user.export.excel") }}' + '?tahun=' + tahun;
        
        setTimeout(() => Swal.close(), 2000);
    });
</script>
@endpush