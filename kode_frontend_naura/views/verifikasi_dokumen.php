<?php
$dataVerifikasi = [
    [
        'nama' => 'Laporan Kegiatan 2025',
        'bidang' => 'Kualitas Hidup Perempuan',
        'tahun' => '2025',
        'status' => 'Menunggu',
        'penjelasan' => 'Penjelasan awal dari bidang terkait laporan kegiatan tahun 2025.'
    ],
    [
        'nama' => 'Data Anak Rentan',
        'bidang' => 'Pemenuhan Hak Anak',
        'tahun' => '2024',
        'status' => 'Diterima',
        'penjelasan' => 'Dokumen data anak rentan telah lengkap dan diverifikasi.'
    ],
    [
        'nama' => 'Dokumen Evaluasi',
        'bidang' => 'Perlindungan Anak',
        'tahun' => '2024',
        'status' => 'Ditolak',
        'penjelasan' => 'Dokumen evaluasi perlu dilengkapi kembali.'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verifikasi Dokumen | SIMPEL DP3A</title>
<link rel="stylesheet" href="../css/verifikasi_dokumen.css">
</head>

<body>

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

<div class="sidebar" id="sidebar">
    <ul>
        <li onclick="location.href='dashboardadmin.php'">Dashboard</li>
        <li onclick="location.href='permintaan_admin.php'">Permintaan Dokumen</li>
        <li class="active">Verifikasi Dokumen</li>
        <li onclick="location.href='dokumen_masuk.php'">Dokumen Masuk</li>
        <li onclick="location.href='pertanyaan_pug.php'">Pertanyaan Evaluasi PUG</li>
    </ul>
</div>

<div class="content">

<div class="section-header">
    <h2>Verifikasi Dokumen Masuk</h2>
</div>

<div class="table-box">
<table>
<thead>
<tr>
<th>No</th>
<th>Nama Dokumen</th>
<th>Bidang</th>
<th>Tahun</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php $no=1; foreach($dataVerifikasi as $row): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $row['nama'] ?></td>
<td><?= $row['bidang'] ?></td>
<td><?= $row['tahun'] ?></td>
<td class="<?= $row['status']=='Diterima'?'status-diterima':($row['status']=='Ditolak'?'status-ditolak':'status-menunggu') ?>">
<?= $row['status'] ?>
</td>
<td>
<div class="action-btn">
    <!-- 🔥 TAMBAHAN data-status -->
    <a class="btn-view"
       data-nama="<?= $row['nama'] ?>"
       data-bidang="<?= $row['bidang'] ?>"
       data-tahun="<?= $row['tahun'] ?>"
       data-status="<?= $row['status'] ?>"
       data-penjelasan="<?= htmlspecialchars($row['penjelasan']) ?>">
       <img src="../photos/eye.png">
    </a>
    <a class="btn-yes"><img src="../photos/yes.png"></a>
    <a class="btn-no"><img src="../photos/no.png"></a>
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

        <!-- INFO DOKUMEN -->
        <div class="doc-info">
            <div><strong>Nama Dokumen:</strong> <span id="dNama"></span></div>
            <div><strong>Bidang:</strong> <span id="dBidang"></span></div>
            <div><strong>Tahun:</strong> <span id="dTahun"></span></div>
        </div>

        <!-- TOOLBAR -->
        <div class="editor-toolbar">
            <button data-cmd="bold"><b>B</b></button>
            <button data-cmd="italic"><i>I</i></button>
            <button data-cmd="underline"><u>U</u></button>
            <button data-cmd="insertUnorderedList">• List</button>
            <button type="button" data-cmd="insertOrderedList">1. List</button>
        </div>

        <!-- EDITOR -->
        <div id="editorDetail" class="editor-area" contenteditable="true"></div>

        <!-- LAMPIRAN -->
        <div class="file-section">
            <h4>Lampiran Dokumen</h4>
            <ul>
                <li>SK_Penetapan.pdf <a href="#">Download</a></li>
                <li>Sertifikat.pdf <a href="#">Download</a></li>
            </ul>
        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
            <button type="button" class="btn-save">Simpan</button>
            <!-- 🔥 TOMBOL KIRIM DISIAPKAN, KONTROL VIA JS -->
            <button type="button" class="btn-send" style="display:none;">Kirim</button>
        </div>

    </div>
</div>

<script src="../js/verifikasi_dokumen.js"></script>
</body>
</html>
