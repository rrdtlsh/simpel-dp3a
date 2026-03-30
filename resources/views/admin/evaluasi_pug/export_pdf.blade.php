<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Evaluasi Mandiri PUG {{ $tahun }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 4px 0 0 0; font-size: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #e5e7eb; font-weight: bold; text-align: center; }
        .komponen-row { background-color: #dbeafe; font-weight: bold; }
        .indikator-row { background-color: #f3f4f6; font-weight: bold; padding-left: 15px; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .status-hijau { background-color: #dcfce7; color: #16a34a; }
        .status-merah { background-color: #fee2e2; color: #dc2626; }
        .status-biru { background-color: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Evaluasi Mandiri Pengarusutamaan Gender (PUG)</h2>
        <p>Pemerintah Kota Banjarmasin - Tahun Evaluasi {{ $tahun }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%">Kode</th>
                <th width="35%">Pertanyaan</th>
                <th width="25%">Jawaban & Catatan</th>
                <th width="8%">Skor</th>
                <th width="12%">Status</th>
                <th width="12%">Lampiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($komponen as $komp)
                <tr class="komponen-row">
                    <td colspan="6">{{ $komp->kode }}. {{ $komp->nama }}</td>
                </tr>
                @foreach($komp->indikator as $indk)
                    <tr class="indikator-row">
                        <td colspan="6">{{ $indk->kode }}. {{ $indk->nama }}</td>
                    </tr>
                    @foreach($indk->pertanyaan as $pert)
                        @php
                            $jwb = $pert->jawaban->first();
                            $status = $jwb ? $jwb->status : 'belum';
                            $statusTeks = match($status) { 'disetujui' => 'Disetujui', 'diisi' => 'Diisi', 'ditolak' => 'Ditolak', default => 'Belum' };
                            $statusWarna = match($status) { 'disetujui' => 'status-hijau', 'diisi' => 'status-biru', default => 'status-merah' };
                        @endphp
                        <tr>
                            <td align="center">{{ $pert->kode }}</td>
                            <td>{{ $pert->pertanyaan }}</td>
                            <td>
                                <strong>Jawaban:</strong><br>
                                {{ $jwb?->jawaban_label ?? '-' }}<br><br>
                                <strong>Catatan:</strong><br>
                                {{ $jwb?->catatan ?? '-' }}
                            </td>
                            <td align="center">
                                <strong>{{ $jwb?->skor ?? 0 }}</strong> / {{ $pert->skor_maksimal }}
                            </td>
                            <td align="center">
                                <span class="status-badge {{ $statusWarna }}">{{ $statusTeks }}</span>
                            </td>
                            <td align="center">
                                {{ $jwb?->lampiran->count() ?? 0 }} File
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

</body>
</html>