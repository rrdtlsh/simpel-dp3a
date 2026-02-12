@extends('layouts.khp')

@section('title', 'Pertanyaan Evaluasi PUG | SIMPEL DP3A')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pertanyaan_pug.css') }}">
@endpush

@section('content')

@php
$pertanyaan = [
    [
        'id'   => 1,
        'kode' => '1.1',
        'teks' => 'Apakah Pemda Kabupaten/Kota memiliki regulasi/kebijakan sebagai landasan hukum Penyelenggaraan PUG secara komprehensif?'
    ],
    [
        'id'   => 2,
        'kode' => '1.2',
        'teks' => 'Apakah regulasi/kebijakan Penyelenggaraan PUG sudah mengintegrasikan gender ke seluruh proses pembangunan?'
    ]
];

$jawaban = [
    1 => [
        'status'   => 'selesai',
        'jawaban'  => 'Sudah ada Perbup No. 3 Tahun 2024',
        'file'     => 'SK_PUG.pdf'
    ],
    2 => [
        'status'   => 'belum',
        'jawaban'  => '',
        'file'     => ''
    ]
];

$totalPertanyaan = count($pertanyaan);
$jumlahSelesai   = collect($jawaban)->where('status', 'selesai')->count();
$progressPersen  = $totalPertanyaan ? round(($jumlahSelesai / $totalPertanyaan) * 100) : 0;
@endphp


<div class="section-header">
    <h2>Pertanyaan Evaluasi PUG</h2>
</div>

<!-- TABLE -->
<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pertanyaan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

        @foreach ($pertanyaan as $p)
            @php
                $dataJawaban = $jawaban[$p['id']];
            @endphp
            <tr>
                <td>{{ $p['kode'] }}</td>
                <td>{{ $p['teks'] }}</td>
                <td class="{{ $dataJawaban['status'] === 'selesai' ? 'status-hijau' : 'status-biru' }}">
                    {{ $dataJawaban['status'] === 'selesai' ? 'Selesai' : 'Belum Diisi' }}
                </td>
                <td>
<div class="action-btn">

    @if($dataJawaban['status'] === 'selesai')
        <button class="btn-see"
            data-id="{{ $p['id'] }}">
            <img src="{{ asset('images/eye.png') }}">
        </button>
    @else
        <button class="btn-edit"
            data-id="{{ $p['id'] }}">
            <img src="{{ asset('images/edit.png') }}">
        </button>
    @endif

</div>

                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
</div>

<!-- MODAL DETAIL -->
<div class="modal" id="modalDetail">
    <div class="modal-content modal-large">

        <div class="modal-header">
            <h3>Detail Pertanyaan PUG</h3>
            <span class="close-modal" data-close>&times;</span>
        </div>

        <div class="doc-info">
            <p><strong>Kode:</strong> <span id="detailKode"></span></p>
            <p><strong>Pertanyaan:</strong></p>
            <p id="detailPertanyaan"></p>
        </div>

        <div class="editor-area" contenteditable="true" id="editorJawaban">
            <p>Jawaban bidang akan ditampilkan di sini.</p>
        </div>

        <div class="lampiran">
            <h4>Lampiran Jawaban</h4>
            <ul id="lampiranList"></ul>
        </div>

        <div class="modal-footer">
            <button class="btn-save">Simpan</button>
            <button class="btn-send">Kirim</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/pertanyaan_pug.js') }}"></script>
@endpush
