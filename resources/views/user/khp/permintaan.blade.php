@extends('layouts.khp')

@section('title', 'Permintaan Dokumen | Bidang KHP')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/permintaan_khp.css') }}">
@endpush

@section('content')

<div class="section-header">
    <h2>Permintaan Masuk</h2>
</div>


<!-- ================= TABLE ================= -->
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
            @forelse($pengajuans as $index => $p)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $p->judul }}</td>
                <td>{{ $p->bidang->nama ?? '-' }}</td>
                <td>{{ $p->created_at->format('Y-m-d') }}</td>
                <td>
                    @if($p->status == 'open')
                    <span style="color: orange;">Menunggu</span>
                    @else
                    <span style="color: green;">Selesai</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('pengajuan.upload', $p->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" required>
                        <button type="submit">Upload</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>

    </table>
</div>

</div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/permintaan_khp.js') }}"></script>
@endpush