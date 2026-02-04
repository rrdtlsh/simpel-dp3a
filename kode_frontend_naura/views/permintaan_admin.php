<?php
$notif = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_dokumen = $_POST['nama_dokumen'] ?? '';
    $bidang = $_POST['bidang'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';

    if ($nama_dokumen && $bidang && $tanggal) {
        $notif = "Permintaan berhasil dikirim (simulasi)";
    }
}

$dataPermintaan = [
    [
        'nama' => 'Laporan Kegiatan 2025',
        'bidang' => 'Kualitas Hidup Perempuan',
        'tanggal' => '20/01/2026',
        'status' => 'Menunggu'
    ],
    [
        'nama' => 'Data Anak Rentan',
        'bidang' => 'Pemenuhan Hak Anak',
        'tanggal' => '19/01/2026',
        'status' => 'Masuk'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Permintaan Dokumen | SIMPEL DP3A</title>

<link rel="stylesheet" href="../css/permintaan_admin.css">
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
        <li class="active">Permintaan Dokumen</li>
        <li onclick="location.href='verifikasi_dokumen.php'">Verifikasi Dokumen</li>
        <li onclick="location.href='dokumen_masuk.php'">Dokumen Masuk</li>
        <li onclick="location.href='pertanyaan_pug.php'">Pertanyaan Evaluasi PUG</li>
    </ul>
</div>

<div class="content">

<div class="section-header">
    <h2>Status Permintaan Terkini</h2>
    <button class="btn-add" id="btnTambah">+ Tambah Permintaan</button>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal" id="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Form Permintaan Dokumen</h3>
            <span id="closeModal">✖</span>
        </div>

        <form method="post">
            <input type="text" name="nama_dokumen" placeholder="Nama Dokumen" required>

            <select name="bidang" required>
                <option value="">Pilih Bidang</option>
                <option>Kualitas Hidup Perempuan</option>
                <option>Perlindungan Perempuan</option>
                <option>Pemenuhan Hak Anak</option>
                <option>Perlindungan Khusus Anak</option>
            </select>

            <input type="date" name="tanggal" required>

            <button type="submit">Kirim Permintaan</button>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT ================= -->
<div class="modal" id="modalEdit">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Permintaan Dokumen</h3>
            <span id="closeEdit">✖</span>
        </div>

        <form>
            <input type="text" id="editNama" placeholder="Nama Dokumen" required>

            <select id="editBidang" required>
                <option value="">Pilih Bidang</option>
                <option>Kualitas Hidup Perempuan</option>
                <option>Perlindungan Perempuan</option>
                <option>Pemenuhan Hak Anak</option>
                <option>Perlindungan Khusus Anak</option>
            </select>

            <input type="date" id="editTanggal" required>

            <button type="button">Simpan Perubahan</button>
        </form>
    </div>
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
<?php $no=1; foreach($dataPermintaan as $row): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $row['nama'] ?></td>
<td><?= $row['bidang'] ?></td>
<td><?= $row['tanggal'] ?></td>
<td class="<?= $row['status']=='Masuk'?'status-masuk':'status-menunggu' ?>">
    <?= $row['status'] ?>
</td>
<td>
<div class="action-btn">
        <a href="#" class="btn-edit"
        data-nama="<?= $row['nama'] ?>"
        data-bidang="<?= $row['bidang'] ?>"
        data-tanggal="<?= date('Y-m-d', strtotime($row['tanggal'])) ?>">
            <img src="../photos/edit.png">
        </a>

    <a class="btn-delete">
        <img src="../photos/delete.png">
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

<script src="../js/permintaan_admin.js"></script>

</body>
</html>
