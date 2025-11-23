<?php
// admin/detail-laporan.php
require_once '../koneksi.php';
checkRole(['super_admin']);

$id_laporan = isset($_GET['id']) ? escape($_GET['id']) : 0;

$query = "SELECT * FROM tbl_laporan WHERE id_laporan = '$id_laporan'";
$result = mysqli_query($conn, $query);
$laporan = mysqli_fetch_assoc($result);

if (!$laporan) {
    echo "Laporan tidak ditemukan.";
    exit;
}

$data = json_decode($laporan['konten_laporan'], true);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f5f7fa; }
        .report-container { background: white; max-width: 210mm; margin: 30px auto; padding: 40px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
        .report-header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .report-logo { width: 80px; height: auto; }
        @media print {
            body { background: white; }
            .report-container { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container-fluid no-print py-3 bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="laporan-sistem.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-2"></i>Cetak Laporan</button>
        </div>
    </div>

    <div class="report-container">
        <div class="report-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="../assets/img/logo.png" alt="Logo" class="report-logo me-3">
                <div>
                    <h4 class="mb-0 fw-bold">MBG System</h4>
                    <small class="text-muted">Laporan Sistem Manajemen</small>
                </div>
            </div>
            <div class="text-end">
                <h5 class="mb-1"><?= $laporan['judul_laporan'] ?></h5>
                <small class="text-muted">Kode: <?= $laporan['kode_laporan'] ?></small><br>
                <small class="text-muted">Tanggal Cetak: <?= date('d M Y') ?></small>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <table class="table table-sm table-borderless">
                    <tr><td width="120">Kategori</td><td>: <?= ucfirst($laporan['kategori_laporan']) ?></td></tr>
                    <tr><td>Periode</td><td>: <?= date('d M Y', strtotime($laporan['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($laporan['tanggal_akhir'])) ?></td></tr>
                    <tr><td>Status</td><td>: <?= ucfirst($laporan['status_laporan']) ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Content Based on Category -->
        <?php if ($laporan['kategori_laporan'] == 'keuangan'): ?>
            <div class="alert alert-light border mb-4">
                <h4 class="mb-0 text-center">Total Pengeluaran: Rp <?= number_format($data['summary']['total_pengeluaran'], 0, ',', '.') ?></h4>
            </div>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Dapur</th>
                        <th>Supplier</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['details'] as $item): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($item['tanggal_pembelian'])) ?></td>
                        <td><?= $item['kode_pembelanjaan'] ?></td>
                        <td><?= $item['nama_dapur'] ?></td>
                        <td><?= $item['supplier'] ?></td>
                        <td class="text-end">Rp <?= number_format($item['total_pembelian'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($laporan['kategori_laporan'] == 'stok'): ?>
            <div class="alert alert-light border mb-4">
                <h5 class="mb-0">Total Item Bahan Baku: <?= $data['summary']['total_item'] ?></h5>
            </div>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Bahan</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['details'] as $item): ?>
                    <tr>
                        <td><?= $item['nama_bahan'] ?></td>
                        <td class="text-center"><?= $item['stok_saat_ini'] ?></td>
                        <td class="text-center"><?= $item['satuan'] ?></td>
                        <td class="text-center">
                            <?php if ($item['stok_saat_ini'] <= $item['stok_minimum']): ?>
                                <span class="badge bg-danger text-white">Menipis</span>
                            <?php else: ?>
                                <span class="badge bg-success text-white">Aman</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($laporan['kategori_laporan'] == 'karyawan'): ?>
            <div class="alert alert-light border mb-4">
                <h5 class="mb-0">Total Karyawan Aktif: <?= $data['summary']['total_karyawan'] ?></h5>
            </div>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Bagian</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Alpha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['details'] as $item): ?>
                    <tr>
                        <td><?= $item['nama'] ?></td>
                        <td><?= ucfirst($item['bagian']) ?></td>
                        <td class="text-center"><?= $item['hadir'] ?></td>
                        <td class="text-center"><?= $item['izin'] ?></td>
                        <td class="text-center"><?= $item['sakit'] ?></td>
                        <td class="text-center"><?= $item['alpha'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($laporan['kategori_laporan'] == 'produksi'): ?>
            <div class="alert alert-light border mb-4">
                <h5 class="mb-0">Total Menu Terdaftar: <?= $data['summary']['total_menu'] ?></h5>
            </div>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Menu</th>
                        <th>Dapur</th>
                        <th class="text-center">Porsi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['details'] as $item): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($item['tanggal_menu'])) ?></td>
                        <td><?= $item['nama_menu'] ?></td>
                        <td><?= $item['nama_dapur'] ?></td>
                        <td class="text-center"><?= $item['jumlah_porsi'] ?></td>
                        <td><?= ucfirst($item['status_pengantaran']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="mt-5 pt-5 text-end">
            <p class="mb-5">Mengetahui,</p>
            <p class="fw-bold text-decoration-underline mt-5"><?= $_SESSION['user_name'] ?></p>
            <p>Super Administrator</p>
        </div>
    </div>
</body>
</html>
