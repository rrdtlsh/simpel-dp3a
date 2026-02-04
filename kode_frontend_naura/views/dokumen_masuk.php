<?php
$dataDokumen = [
    [
        'nama' => 'Laporan Kegiatan 2025',
        'bidang' => 'Kualitas Hidup Perempuan',
        'tahun' => '2025'
    ],
    [
        'nama' => 'Data Anak Rentan',
        'bidang' => 'Pemenuhan Hak Anak',
        'tahun' => '2024'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dokumen Masuk | SIMPEL DP3A</title>
<link rel="stylesheet" href="../css/dokumen_masuk.css">
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="../photos/DPPPA ver2.png">
    </div>

    <div class="header-right">
        <div class="user-info">
            <div class="user-icon">
                <img src="../photos/accicon.png">
            </div>
            <span>Perencanaan</span>
        </div>
        <button class="btn-logout">Logout</button>
    </div>
</div>

<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <ul>
        <li onclick="location.href='dashboardadmin.php'">Dashboard</li>
        <li onclick="location.href='permintaan_admin.php'">Permintaan Dokumen</li>
        <li onclick="location.href='verifikasi_dokumen.php'">Verifikasi Dokumen</li>
        <li class="active">Dokumen Masuk</li>
        <li onclick="location.href='pertanyaan_pug.php'">Pertanyaan Evaluasi PUG</li>
    </ul>
</div>

<!-- CONTENT -->
<div class="content">

<div class="section-header">
    <h2>Dokumen Masuk</h2>

    <div class="header-actions">
        <!-- FILTER -->
        <select id="filterBidang">
            <option value="">Semua Bidang</option>
            <option>Kualitas Hidup Perempuan</option>
            <option>Pemenuhan Hak Anak</option>
        </select>

        <select id="filterTahun">
            <option value="">Semua Tahun</option>
            <option>2025</option>
            <option>2024</option>
        </select>

        <!-- EXPORT -->
        <button class="btn-export btn-pdf">
            <img src="../photos/pdf.png"> Export PDF
        </button>
        <button class="btn-export btn-excel">
            <img src="../photos/excel.png"> Export Excel
        </button>
    </div>
</div>

<div class="table-box">
<table id="dokumenTable">
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
<?php $no=1; foreach($dataDokumen as $row): ?>
<tr data-bidang="<?= $row['bidang'] ?>" data-tahun="<?= $row['tahun'] ?>">
<td><?= $no++ ?></td>
<td><?= $row['nama'] ?></td>
<td><?= $row['bidang'] ?></td>
<td><?= $row['tahun'] ?></td>
<td>
<div class="action-btn">
    <a href="javascript:void(0)"
        class="btn-view"
        data-nama="<?= $row['nama'] ?>"
        data-bidang="<?= $row['bidang'] ?>"
        data-tahun="<?= $row['tahun'] ?>">
        <img src="../photos/eye.png">
    </a>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>
</div>

<!-- ===== MODAL DETAIL ===== -->
<div class="modal" id="detailModal">
<div class="modal-content modal-large">

<div class="modal-header">
    <h3>Detail Dokumen</h3>
    <span class="close-modal" id="closeDetail">&times;</span>
</div>

<div class="doc-info">
    <p><b>Nama Dokumen:</b> <span id="detailNama"></span></p>
    <p><b>Bidang:</b> <span id="detailBidang"></span></p>
    <p><b>Tahun:</b> <span id="detailTahun"></span></p>
</div>

<div class="editor-toolbar">
    <button data-cmd="bold"><b>B</b></button>
    <button data-cmd="italic"><i>I</i></button>
    <button data-cmd="underline"><u>U</u></button>
    <button data-cmd="insertUnorderedList">• List</button>
    <button data-cmd="insertOrderedList">1. List</button>
</div>

<div class="editor-area" contenteditable="true" id="editorDetail">
    <p>Penjelasan dokumen akan ditampilkan di sini.</p>
</div>

<div class="lampiran">
    <h4>Lampiran Dokumen</h4>
    <ul>
        <li>
            <span>SK_Penetapan.pdf</span>
            <a href="#">Download</a>
        </li>
        <li>
            <span>Sertifikat.pdf</span>
            <a href="#">Download</a>
        </li>
    </ul>
</div>


<div class="modal-footer">
    <button class="btn-save">Simpan</button>
</div>

</div>
</div>

<script src="../js/dokumen_masuk.js"></script>
</body>
</html>
