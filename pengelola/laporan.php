<?php
// pengelola/laporan.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];

// Get filter parameters
$report_type = isset($_GET['type']) ? $_GET['type'] : 'pembelanjaan';
$periode = isset($_GET['periode']) ? $_GET['periode'] : 'bulan_ini';
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');

// Set date range based on periode
if ($periode == 'hari_ini') {
    $tanggal_mulai = $tanggal_akhir = date('Y-m-d');
} elseif ($periode == 'bulan_ini') {
    $tanggal_mulai = date('Y-m-01');
    $tanggal_akhir = date('Y-m-d');
}

// Handle Export
if (isset($_GET['export'])) {
    $export_type = $_GET['export']; // 'excel' or 'word'
    
    if ($report_type == 'pembelanjaan') {
        // Export Shopping Report
        $query = "SELECT p.*, d.nama_dapur 
                  FROM tbl_pembelanjaan p 
                  JOIN tbl_dapur d ON p.id_dapur = d.id_dapur 
                  WHERE p.id_pengelola = '$id_pengelola' 
                  AND p.tanggal_pembelian BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
                  AND p.status = 'selesai'
                  ORDER BY p.tanggal_pembelian DESC";
        $result = mysqli_query($conn, $query);
        
        if ($export_type == 'excel') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="Laporan_Pembelanjaan_' . date('Y-m-d') . '.xls"');
            
            echo "<table border='1'>";
            echo "<tr><th colspan='8' style='text-align:center; font-weight:bold;'>LAPORAN PEMBELANJAAN</th></tr>";
            echo "<tr><th colspan='8'>Periode: " . date('d/m/Y', strtotime($tanggal_mulai)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . "</th></tr>";
            echo "<tr><th colspan='8' style='background-color:#ffffcc;'>Catatan: Untuk melihat bukti pembayaran, gunakan Export Word</th></tr>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Kode</th><th>No Nota</th><th>Supplier</th><th>Total</th><th>Dapur</th><th>Bukti Bayar</th></tr>";
            
            $no = 1;
            $grand_total = 0;
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($row['tanggal_pembelian'])) . "</td>";
                echo "<td>" . $row['kode_pembelanjaan'] . "</td>";
                echo "<td>" . ($row['no_nota_fisik'] ?? '-') . "</td>";
                echo "<td>" . $row['supplier'] . "</td>";
                echo "<td style='text-align:right;'>" . number_format($row['total_pembelian'], 0, ',', '.') . "</td>";
                echo "<td>" . $row['nama_dapur'] . "</td>";
                echo "<td>" . ($row['bukti_pembelian'] ? '✓ Ada Bukti' : '✗ Tidak Ada') . "</td>";
                echo "</tr>";
                $grand_total += $row['total_pembelian'];
            }
            echo "<tr><th colspan='5'>TOTAL</th><th style='text-align:right;'>" . number_format($grand_total, 0, ',', '.') . "</th><th colspan='2'></th></tr>";
            echo "</table>";
            exit;
        } elseif ($export_type == 'word') {
            header('Content-Type: application/vnd.ms-word');
            header('Content-Disposition: attachment; filename="Laporan_Pembelanjaan_' . date('Y-m-d') . '.doc"');
            
            echo "<html><body>";
            echo "<h2 style='text-align:center;'>LAPORAN PEMBELANJAAN</h2>";
            echo "<p>Periode: " . date('d/m/Y', strtotime($tanggal_mulai)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . "</p>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse; width:100%;'>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Kode</th><th>No Nota</th><th>Supplier</th><th>Total</th><th>Bukti Pembayaran</th></tr>";
            
            $no = 1;
            $grand_total = 0;
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($row['tanggal_pembelian'])) . "</td>";
                echo "<td>" . $row['kode_pembelanjaan'] . "</td>";
                echo "<td>" . ($row['no_nota_fisik'] ?? '-') . "</td>";
                echo "<td>" . $row['supplier'] . "</td>";
                echo "<td style='text-align:right;'>Rp " . number_format($row['total_pembelian'], 0, ',', '.') . "</td>";
                echo "<td>";
                if ($row['bukti_pembelian']) {
                    $img_path = "../assets/img/bukti/" . $row['bukti_pembelian'];
                    if (file_exists($img_path)) {
                        $imageData = base64_encode(file_get_contents($img_path));
                        $src = 'data:image/jpeg;base64,' . $imageData;
                        echo "<img src='" . $src . "' width='150' />";
                    } else {
                        echo "File tidak ditemukan";
                    }
                } else {
                    echo "-";
                }
                echo "</td>";
                echo "</tr>";
                $grand_total += $row['total_pembelian'];
            }
            echo "<tr><th colspan='5'>TOTAL</th><th style='text-align:right;'>Rp " . number_format($grand_total, 0, ',', '.') . "</th><th></th></tr>";
            echo "</table>";
            echo "</body></html>";
            exit;
        }
    } elseif ($report_type == 'karyawan') {
        // Export Documentation Activity Report
        $query = "SELECT *
                  FROM tbl_dokumentasi_karyawan
                  WHERE tanggal_dokumentasi BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
                  ORDER BY tanggal_dokumentasi DESC, created_at DESC";
        $result = mysqli_query($conn, $query);
        
        if ($export_type == 'excel') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="Laporan_Dokumentasi_' . date('Y-m-d') . '.xls"');
            
            echo "<table border='1'>";
            echo "<tr><th colspan='5' style='text-align:center; font-weight:bold;'>LAPORAN DOKUMENTASI AKTIVITAS</th></tr>";
            echo "<tr><th colspan='5'>Periode: " . date('d/m/Y', strtotime($tanggal_mulai)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . "</th></tr>";
            echo "<tr><th colspan='5' style='background-color:#ffffcc;'>Catatan: Untuk melihat foto, gunakan Export Word</th></tr>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Bagian</th><th>Aktivitas</th><th>Foto</th></tr>";
            
            $no = 1;
            while($row = mysqli_fetch_assoc($result)) {
                preg_match('/\[(.*?)\]/', $row['aktivitas'], $matches);
                $bagian = $matches[1] ?? 'Umum';
                $aktivitas_text = preg_replace('/\[.*?\]\s*/', '', $row['aktivitas']);
                
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($row['tanggal_dokumentasi'])) . "</td>";
                echo "<td>" . ucfirst($bagian) . "</td>";
                echo "<td>" . $aktivitas_text . "</td>";
                echo "<td>" . ($row['foto_dokumentasi'] ? '✓ Ada Foto' : '✗ Tidak Ada') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            exit;
        } elseif ($export_type == 'word') {
            header('Content-Type: application/vnd.ms-word');
            header('Content-Disposition: attachment; filename="Laporan_Dokumentasi_' . date('Y-m-d') . '.doc"');
            
            echo "<html><body>";
            echo "<h2 style='text-align:center;'>LAPORAN DOKUMENTASI AKTIVITAS</h2>";
            echo "<p>Periode: " . date('d/m/Y', strtotime($tanggal_mulai)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . "</p>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse; width:100%;'>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Bagian</th><th>Aktivitas</th><th>Foto</th></tr>";
            
            $no = 1;
            while($row = mysqli_fetch_assoc($result)) {
                preg_match('/\\[(.*?)\\]/', $row['aktivitas'], $matches);
                $bagian = $matches[1] ?? 'Umum';
                $aktivitas_text = preg_replace('/\\[.*?\\]\\s*/', '', $row['aktivitas']);
                
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($row['tanggal_dokumentasi'])) . "</td>";
                echo "<td>" . ucfirst($bagian) . "</td>";
                echo "<td>" . nl2br($aktivitas_text) . "</td>";
                echo "<td>";
                if ($row['foto_dokumentasi']) {
                    $img_path = "../assets/img/dokumentasi/" . $row['foto_dokumentasi'];
                    if (file_exists($img_path)) {
                        $imageData = base64_encode(file_get_contents($img_path));
                        $src = 'data:image/jpeg;base64,' . $imageData;
                        echo "<img src='" . $src . "' width='150' />";
                    } else {
                        echo "File tidak ditemukan";
                    }
                } else {
                    echo "-";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</body></html>";
            exit;
        }
    } elseif ($report_type == 'absensi') {
        // Export Attendance Report
        $query = "SELECT a.tanggal, k.nama, k.bagian, d.nama_dapur, 
                         a.jam_masuk, a.jam_keluar, a.total_jam_kerja, 
                         a.status_kehadiran, a.keterangan
                  FROM tbl_absensi a
                  INNER JOIN tbl_karyawan k ON a.id_karyawan = k.id_karyawan
                  LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                  WHERE a.tanggal BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
                  AND k.id_pengelola = '$id_pengelola'
                  ORDER BY a.tanggal DESC, k.nama ASC";
        $result = mysqli_query($conn, $query);
        
        
        if ($export_type == 'excel') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="Laporan_Absensi_' . date('Y-m-d') . '.xls"');
            
            echo "<table border='1'>";
            echo "<tr><th colspan='9' style='text-align:center; font-weight:bold;'>LAPORAN ABSENSI KARYAWAN</th></tr>";
            echo "<tr><th colspan='9'>Periode: " . date('d/m/Y', strtotime($tanggal_mulai)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . "</th></tr>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Nama</th><th>Bagian</th><th>Dapur</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Total Jam</th><th>Status</th></tr>";
            
            $no = 1;
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                echo "<td>" . $row['nama'] . "</td>";
                echo "<td>" . ($row['bagian'] ?? '-') . "</td>";
                echo "<td>" . ($row['nama_dapur'] ?? '-') . "</td>";
                echo "<td>" . ($row['jam_masuk'] ?? '-') . "</td>";
                echo "<td>" . ($row['jam_keluar'] ?? '-') . "</td>";
                echo "<td>" . ($row['total_jam_kerja'] ?? '-') . "</td>";
                echo "<td>" . ucfirst($row['status_kehadiran']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            exit;
        } elseif ($export_type == 'word') {
            header('Content-Type: application/vnd.ms-word');
            header('Content-Disposition: attachment; filename="Laporan_Absensi_' . date('Y-m-d') . '.doc"');
            
            echo "<html><body>";
            echo "<h2 style='text-align:center;'>LAPORAN ABSENSI KARYAWAN</h2>";
            echo "<p>Periode: " . date('d/m/Y', strtotime($tanggal_mulai)) . " - " . date('d/m/Y', strtotime($tanggal_akhir)) . "</p>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse; width:100%;'>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Nama</th><th>Bagian</th><th>Dapur</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Total Jam</th><th>Status</th><th>Keterangan</th></tr>";
            
            $no = 1;
            while($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                echo "<td>" . $row['nama'] . "</td>";
                echo "<td>" . ($row['bagian'] ?? '-') . "</td>";
                echo "<td>" . ($row['nama_dapur'] ?? '-') . "</td>";
                echo "<td>" . ($row['jam_masuk'] ?? '-') . "</td>";
                echo "<td>" . ($row['jam_keluar'] ?? '-') . "</td>";
                echo "<td>" . ($row['total_jam_kerja'] ?? '-') . "</td>";
                echo "<td>" . ucfirst($row['status_kehadiran']) . "</td>";
                echo "<td>" . nl2br($row['keterangan'] ?? '') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</body></html>";
            exit;
        }
    }
}

// Fetch data for display
if ($report_type == 'pembelanjaan') {
    $query = "SELECT p.*, d.nama_dapur 
              FROM tbl_pembelanjaan p 
              JOIN tbl_dapur d ON p.id_dapur = d.id_dapur 
              WHERE p.id_pengelola = '$id_pengelola' 
              AND p.tanggal_pembelian BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
              AND p.status = 'selesai'
              ORDER BY p.tanggal_pembelian DESC";
} elseif ($report_type == 'karyawan') {
    $query = "SELECT *
              FROM tbl_dokumentasi_karyawan
              WHERE tanggal_dokumentasi BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
              ORDER BY tanggal_dokumentasi DESC, created_at DESC";
} else {
    // Absensi Report
    $query = "SELECT a.*, k.nama, k.bagian, d.nama_dapur
              FROM tbl_absensi a
              INNER JOIN tbl_karyawan k ON a.id_karyawan = k.id_karyawan
              LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
              WHERE a.tanggal BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
              AND k.id_pengelola = '$id_pengelola'
              ORDER BY a.tanggal DESC, k.nama ASC";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="bi bi-list"></i></button>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><img src="../assets/img/logo.png" alt="MBG Logo" class="logo-image"></div>
            <h4>MBG System</h4><small>Pengelola Panel</small>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
            <a href="dapur.php"><i class="bi bi-house"></i><span>Kelola Dapur</span></a>
            <a href="karyawan.php"><i class="bi bi-people"></i><span>Karyawan</span></a>
            <a href="absensi.php"><i class="bi bi-calendar-check"></i><span>Absensi Karyawan</span></a>
            <a href="menu.php"><i class="bi bi-card-list"></i><span>Menu</span></a>
            <a href="pembelanjaan.php"><i class="bi bi-cash-stack"></i><span>Pembelanjaan</span></a>
            <a href="stok.php"><i class="bi bi-box-seam"></i><span>Stok Bahan</span></a>
            <a href="dokumentasi.php"><i class="bi bi-journal-text"></i><span>Dokumentasi</span></a>
            <a href="laporan.php" class="active"><i class="bi bi-file-earmark-text"></i><span>Laporan</span></a>
            <a href="profil.php"><i class="bi bi-person-circle"></i><span>Profil</span></a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <div><h4 class="mb-0">Laporan</h4><small class="text-muted">Laporan dan Dokumentasi</small></div>
            <div class="user-profile">
                <a href="profil.php" class="text-decoration-none text-dark d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="fw-bold"><?= $_SESSION['user_name'] ?></div>
                        <small class="text-muted">Pengelola Dapur</small>
                    </div>
                    <div class="user-avatar">
                        <?php if (isset($_SESSION['foto_profil']) && $_SESSION['foto_profil']): ?>
                            <img src="../assets/img/profil/<?= $_SESSION['foto_profil'] ?>" alt="Profil" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Jenis Laporan</label>
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="pembelanjaan" <?= $report_type == 'pembelanjaan' ? 'selected' : '' ?>>Riwayat Pembelanjaan</option>
                                <option value="karyawan" <?= $report_type == 'karyawan' ? 'selected' : '' ?>>Dokumentasi Aktivitas</option>
                                <option value="absensi" <?= $report_type == 'absensi' ? 'selected' : '' ?>>Absensi Karyawan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Periode</label>
                            <select name="periode" class="form-select" onchange="this.form.submit()">
                                <option value="hari_ini" <?= $periode == 'hari_ini' ? 'selected' : '' ?>>Hari Ini</option>
                                <option value="bulan_ini" <?= $periode == 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                                <option value="custom" <?= $periode == 'custom' ? 'selected' : '' ?>>Custom</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dari</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= $tanggal_mulai ?>" <?= $periode != 'custom' ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sampai</label>
                            <input type="date" name="tanggal_akhir" class="form-control" value="<?= $tanggal_akhir ?>" <?= $periode != 'custom' ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Buttons -->
        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
            <a href="?type=<?= $report_type ?>&periode=<?= $periode ?>&tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_akhir=<?= $tanggal_akhir ?>&export=excel" 
               style="background-color: #198754; color: white; padding: 12px 20px; text-align: center; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 500; border: none; cursor: pointer; display: block;">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="?type=<?= $report_type ?>&periode=<?= $periode ?>&tanggal_mulai=<?= $tanggal_mulai ?>&tanggal_akhir=<?= $tanggal_akhir ?>&export=word" 
               style="background-color: #0d6efd; color: white; padding: 12px 20px; text-align: center; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 500; border: none; cursor: pointer; display: block;">
                <i class="bi bi-file-earmark-word"></i> Export Word
            </a>
        </div>

        <!-- Report Table -->
        <div class="table-card">
            <?php if ($report_type == 'pembelanjaan'): ?>
                <h5 class="mb-3"><i class="bi bi-receipt"></i> Laporan Pembelanjaan</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode</th>
                                <th>No Nota</th>
                                <th>Supplier</th>
                                <th>Total</th>
                                <th>Bukti Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $grand_total = 0;
                            while($row = mysqli_fetch_assoc($result)): 
                                $grand_total += $row['total_pembelian'];
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_pembelian'])) ?></td>
                                <td><?= $row['kode_pembelanjaan'] ?></td>
                                <td><?= $row['no_nota_fisik'] ?? '-' ?></td>
                                <td><?= $row['supplier'] ?></td>
                                <td class="text-end">Rp <?= number_format($row['total_pembelian'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if ($row['bukti_pembelian']): ?>
                                        <a href="../assets/img/bukti/<?= $row['bukti_pembelian'] ?>" target="_blank">
                                            <img src="../assets/img/bukti/<?= $row['bukti_pembelian'] ?>" alt="Bukti" style="max-width: 60px; cursor: pointer;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">TOTAL</th>
                                <th class="text-end">Rp <?= number_format($grand_total, 0, ',', '.') ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php elseif ($report_type == 'karyawan'): ?>
                <h5 class="mb-3"><i class="bi bi-journal-text"></i> Laporan Dokumentasi Aktivitas</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Bagian</th>
                                <th>Aktivitas</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($result)): 
                                preg_match('/\[(.*?)\]/', $row['aktivitas'], $matches);
                                $bagian = $matches[1] ?? 'Umum';
                                $aktivitas_text = preg_replace('/\[.*?\]\s*/', '', $row['aktivitas']);
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_dokumentasi'])) ?></td>
                                <td><span class="badge bg-primary"><?= $bagian ?></span></td>
                                <td><?= nl2br($aktivitas_text) ?></td>
                                <td class="text-center">
                                    <?php if ($row['foto_dokumentasi']): ?>
                                        <a href="../assets/img/dokumentasi/<?= $row['foto_dokumentasi'] ?>" target="_blank">
                                            <img src="../assets/img/dokumentasi/<?= $row['foto_dokumentasi'] ?>" alt="Dokumentasi" style="max-width: 60px; cursor: pointer;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <h5 class="mb-3"><i class="bi bi-calendar-check"></i> Laporan Absensi Karyawan</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Bagian</th>
                                <th>Dapur</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Total Jam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($result)): 
                                $status_class = $row['status_kehadiran'] == 'hadir' ? 'bg-success' : 
                                               ($row['status_kehadiran'] == 'izin' ? 'bg-warning' : 
                                               ($row['status_kehadiran'] == 'sakit' ? 'bg-info' : 'bg-danger'));
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['bagian'] ?? '-' ?></td>
                                <td><?= $row['nama_dapur'] ?? '-' ?></td>
                                <td><?= $row['jam_masuk'] ?? '-' ?></td>
                                <td><?= $row['jam_keluar'] ?? '-' ?></td>
                                <td><?= $row['total_jam_kerja'] ?? '-' ?> jam</td>
                                <td><span class="badge <?= $status_class ?>"><?= ucfirst($row['status_kehadiran']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
</body>
</html>
