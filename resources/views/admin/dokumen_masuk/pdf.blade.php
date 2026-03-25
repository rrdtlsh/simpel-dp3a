<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arsip Dokumen Masuk</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Arsip Dokumen Masuk</h2>
        <p>Dinas Pemberdayaan Perempuan dan Perlindungan Anak (DP3A) Kota Banjarmasin</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Dokumen</th>
                <th style="width: 25%;">Bidang Tujuan</th>
                <th style="width: 15%;">Tahun</th>
                <th style="width: 20%;">Tanggal Diterima</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengajuans as $index => $row)
                @php
                    $file = $row->files->first();
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->judul }}</td>
                    <td class="text-center">{{ $row->bidang->nama ?? '-' }}</td>
                    <td class="text-center">{{ $row->created_at->format('Y') }}</td>
                    <td class="text-center">{{ $file ? $file->updated_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data arsip.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>