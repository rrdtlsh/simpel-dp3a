<?php
/* ===============================
   SIMULASI DATA (TANPA DATABASE)
================================ */

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

/* ===============================
   HITUNG PROGRESS
================================ */

$totalPertanyaan = count($pertanyaan);
$jumlahSelesai   = count(array_filter($jawaban, fn($j) => $j['status'] === 'selesai'));
$progressPersen  = $totalPertanyaan ? round(($jumlahSelesai / $totalPertanyaan) * 100) : 0;
$sisa            = $totalPertanyaan - $jumlahSelesai;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pertanyaan Evaluasi PUG | SIMPEL DP3A</title>
<link rel="stylesheet" href="../css/pertanyaan_pug.css">
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

<!-- ================= SIDEBAR ================= -->
<aside class="sidebar" id="sidebar">
    <ul>
        <li onclick="location.href='dashboardadmin.php'">Dashboard</li>
        <li onclick="location.href='permintaan_dokumen.php'">Permintaan Dokumen</li>
        <li onclick="location.href='verifikasi_dokumen.php'">Verifikasi Dokumen</li>
        <li onclick="location.href='dokumen_masuk.php'">Dokumen Masuk</li>
        <li class="active">Pertanyaan Evaluasi PUG</li>
    </ul>
</aside>

<!-- ================= CONTENT ================= -->
<main class="content">

    <div class="section-header">
        <h2>Pertanyaan Evaluasi PUG</h2>
        <button class="btn-add" id="btnTambah">+ Tambah Pertanyaan</button>
    </div>

    <!-- PROGRESS -->
    <div class="progress-box">
        <div class="progress-info">
            <span>Progress Pengisian</span>
            <strong><?= $progressPersen ?>%</strong>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $progressPersen ?>%"></div>
        </div>
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
            <?php foreach ($pertanyaan as $p): 
                $dataJawaban = $jawaban[$p['id']];
            ?>
                <tr>
                    <td><?= $p['kode'] ?></td>
                    <td><?= $p['teks'] ?></td>
                    <td class="<?= $dataJawaban['status'] === 'selesai' ? 'status-hijau' : 'status-biru' ?>">
                        <?= $dataJawaban['status'] === 'selesai' ? 'Selesai' : 'Belum Diisi' ?>
                    </td>
                    <td>
                        <button class="btn-view"
                            data-id="<?= $p['id'] ?>"
                            data-kode="<?= $p['kode'] ?>"
                            data-teks="<?= $p['teks'] ?>"
                            data-jawaban="<?= $dataJawaban['jawaban'] ?>"
                            data-file="<?= $dataJawaban['file'] ?>"
                            data-status="<?= $dataJawaban['status'] ?>">
                            <?= $dataJawaban['status'] === 'selesai' ? 'Lihat' : 'Ubah' ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal" id="modalTambah">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Pertanyaan PUG</h3>
            <span class="close" data-close>&times;</span>
        </div>

        <form>
            <input type="text" placeholder="Kode (contoh: 1.3)" required>
            <textarea placeholder="Isi pertanyaan" required></textarea>
            <button type="button">Simpan Pertanyaan</button>
        </form>
    </div>
</div>

<!-- ===== MODAL DETAIL PERTANYAAN ===== -->
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

    <!-- TOOLBAR -->
    <div class="editor-toolbar">
        <button data-cmd="bold"><b>B</b></button>
        <button data-cmd="italic"><i>I</i></button>
        <button data-cmd="underline"><u>U</u></button>
        <button data-cmd="insertUnorderedList">• List</button>
        <button data-cmd="insertOrderedList">1. List</button>
    </div>

    <!-- JAWABAN -->
    <div class="editor-area" contenteditable="true" id="editorJawaban">
        <p>Jawaban bidang akan ditampilkan di sini.</p>
    </div>

    <!-- LAMPIRAN -->
    <div class="lampiran">
        <h4>Lampiran Jawaban</h4>
        <ul id="lampiranList">
            <li>
                <span>SK_PUG.pdf</span>
                <a href="#">Download</a>
            </li>
        </ul>
    </div>

    <div class="modal-footer">
        <button class="btn-save">Simpan</button>
        <button class="btn-send">Kirim</button>
    </div>

</div>
</div>


<script src="../js/pertanyaan_pug.js"></script>
</body>
</html>
