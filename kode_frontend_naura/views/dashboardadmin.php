<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | SIMPEL DP3A</title>

<!-- CSS -->
<link rel="stylesheet" href="../css/dashboardadmin.css">
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <div class="hamburger" id="hamburger">☰</div>
        <img src="../photos/DPPPA ver2.png" alt="Logo DP3A">
    </div>

    <div class="header-right">
        <div class="user-info">
            <div class="user-icon">
                <img src="../photos/accicon.png" alt="Akun">
            </div>
            <span>Perencanaan</span>
        </div>
        <button class="btn-logout">Logout</button>
    </div>
</div>

<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <ul id="menu">
        <li class="active" onclick="location.href='dashboardadmin.php'">Dashboard</li>
        <li onclick="location.href='permintaan_admin.php'">Permintaan Dokumen</li>
        <li onclick="location.href='verifikasi_dokumen.php'">Verifikasi Dokumen</li>
        <li onclick="location.href='dokumen_masuk.php'">Dokumen Masuk</li>
        <li onclick="location.href='pertanyaan_pug.php'">Pertanyaan Evaluasi PUG</li>
    </ul>
</div>

<!-- CONTENT -->
<div class="content">
    <h2>Halo, Bidang Perencanaan!</h2>

    <div class="card-container">
        <div class="card">
            <h4>Permintaan Anda</h4>
            <span>12</span>
        </div>
        <div class="card">
            <h4>Total Arsip</h4>
            <span>58</span>
        </div>
        <div class="card">
            <h4>Dokumen Masuk</h4>
            <span>45</span>
        </div>
        <div class="card">
            <h4>Dokumen Belum Masuk</h4>
            <span>13</span>
        </div>
    </div>

    <div class="table-box">
        <h3>Dokumen Terbaru</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dokumen</th>
                    <th>Bidang</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Laporan Kegiatan 2025</td>
                    <td>Kualitas Hidup Perempuan</td>
                    <td>20/01/2026</td>
                    <td class="status-ok">Masuk</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Data Anak Rentan</td>
                    <td>Pemenuhan Hak Anak</td>
                    <td>19/01/2026</td>
                    <td class="status-wait">Menunggu</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</div>

<!-- JAVASCRIPT -->
<script src="../js/dashboardadmin.js"></script>

</body>
</html>
