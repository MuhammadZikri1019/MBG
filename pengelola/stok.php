<?php
// pengelola/stok.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];

// Get Stock Data
$query_stok = "SELECT * FROM tbl_bahan_baku ORDER BY nama_bahan ASC";
$result_stok = mysqli_query($conn, $query_stok);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Bahan Baku - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
    <!-- Sidebar & Navbar -->
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
            <a href="stok.php" class="active"><i class="bi bi-box-seam"></i><span>Stok Bahan</span></a>
            <a href="dokumentasi.php"><i class="bi bi-journal-text"></i><span>Dokumentasi</span></a>
            <a href="laporan.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Laporan</span>
            </a>
            <a href="profil.php">
                <i class="bi bi-person-circle"></i>
                <span>Profil</span>
            </a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <div><h4 class="mb-0">Stok Bahan Baku</h4><small class="text-muted">Monitoring ketersediaan bahan</small></div>
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

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama Bahan</th>
                            <th class="text-center">Stok Saat Ini</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($stok = mysqli_fetch_assoc($result_stok)): ?>
                        <tr>
                            <td class="fw-bold"><?= $stok['nama_bahan'] ?></td>
                            <td class="text-center">
                                <span class="badge fs-6 <?= $stok['stok_saat_ini'] <= $stok['stok_minimum'] ? 'bg-danger' : 'bg-success' ?>">
                                    <?= number_format($stok['stok_saat_ini'], 2) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $stok['satuan'] ?></td>
                            <td class="text-center">
                                <?php if ($stok['stok_saat_ini'] <= 0): ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php elseif ($stok['stok_saat_ini'] <= $stok['stok_minimum']): ?>
                                    <span class="badge bg-warning text-dark">Menipis</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Aman</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
</body>
</html>
