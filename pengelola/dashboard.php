<?php
// pengelola/dashboard.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];

// Data dapur yang dikelola
$query_dapur = "SELECT * FROM tbl_dapur WHERE id_pengelola = $id_pengelola";
$result_dapur = mysqli_query($conn, $query_dapur);
$total_dapur = mysqli_num_rows($result_dapur);

// Total karyawan
$query_karyawan = "SELECT COUNT(*) as total FROM tbl_karyawan WHERE id_pengelola = $id_pengelola AND status = 'aktif'";
$total_karyawan = mysqli_fetch_assoc(mysqli_query($conn, $query_karyawan))['total'];

// Total menu
$query_menu = "SELECT COUNT(*) as total FROM tbl_menu WHERE id_pengelola = $id_pengelola AND status = 'aktif'";
$total_menu = mysqli_fetch_assoc(mysqli_query($conn, $query_menu))['total'];



// Pembelanjaan bulan ini
$query_pembelanjaan = "SELECT COALESCE(SUM(total_pembelian), 0) as total 
                       FROM tbl_pembelanjaan 
                       WHERE id_pengelola = $id_pengelola 
                       AND MONTH(tanggal_pembelian) = MONTH(CURDATE())
                       AND YEAR(tanggal_pembelian) = YEAR(CURDATE())";
$total_pembelanjaan = mysqli_fetch_assoc(mysqli_query($conn, $query_pembelanjaan))['total'];

// Absensi hari ini
$query_absensi = "SELECT 
                    COUNT(*) as total_attendance,
                    SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status_kehadiran = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status_kehadiran = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status_kehadiran = 'alpha' THEN 1 ELSE 0 END) as alpha
                  FROM tbl_absensi a
                  INNER JOIN tbl_karyawan k ON a.id_karyawan = k.id_karyawan
                  WHERE k.id_pengelola = $id_pengelola
                  AND a.tanggal = CURDATE()";
$absensi_data = mysqli_fetch_assoc(mysqli_query($conn, $query_absensi));



?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengelola - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Mobile Menu Toggle (Always Visible on All Devices) -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="../assets/img/logo.png" alt="MBG Logo" class="logo-image">
            </div>
            <h4>MBG System</h4>
            <small>Pengelola Panel</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="dapur.php">
                <i class="bi bi-house"></i>
                <span>Kelola Dapur</span>
            </a>
            <a href="karyawan.php">
                <i class="bi bi-people"></i>
                <span>Karyawan</span>
            </a>
            <a href="absensi.php">
                <i class="bi bi-calendar-check"></i>
                <span>Absensi Karyawan</span>
            </a>

            <a href="menu.php">
                <i class="bi bi-card-list"></i>
                <span>Menu</span>
            </a>

            <a href="pembelanjaan.php">
                <i class="bi bi-cash-stack"></i>
                <span>Pembelanjaan</span>
            </a>
            <a href="stok.php">
                <i class="bi bi-box-seam"></i>
                <span>Stok Bahan</span>
            </a>
            <a href="dokumentasi.php">
                <i class="bi bi-journal-text"></i>
                <span>Dokumentasi</span>
            </a>
            <a href="laporan.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Laporan</span>
            </a>
            <a href="profil.php">
                <i class="bi bi-person-circle"></i>
                <span>Profil</span>
            </a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="mb-0">Dashboard Pengelola</h4>
                <small class="text-muted">Monitoring operasional dapur</small>
            </div>
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

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4 col-lg-6 col-md-6">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-house"></i>
                    </div>
                    <h3><?= $total_dapur ?></h3>
                    <p>Dapur</p>
                </div>
            </div>
            <div class="col-6 col-xl-4 col-lg-6 col-md-6">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3><?= $total_karyawan ?></h3>
                    <p>Karyawan</p>
                </div>
            </div>
            <div class="col-12 col-xl-4 col-lg-6 col-md-6">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-card-list"></i>
                    </div>
                    <h3><?= $total_menu ?></h3>
                    <p>Total Menu</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6 col-md-12">
                <div class="stat-card warning h-100">
                    <div class="icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h3>Rp <?= number_format($total_pembelanjaan, 0, ',', '.') ?></h3>
                    <p>Pembelanjaan Bulan Ini</p>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="stat-card info h-100">
                    <div class="icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3><?= $absensi_data['total_attendance'] ?? 0 ?></h3>
                    <p>Absensi Hari Ini</p>
                    <div class="mt-2">
                        <span class="badge bg-success me-1">Hadir: <?= $absensi_data['hadir'] ?? 0 ?></span>
                        <span class="badge bg-warning me-1">Izin: <?= $absensi_data['izin'] ?? 0 ?></span>
                        <span class="badge bg-info me-1">Sakit: <?= $absensi_data['sakit'] ?? 0 ?></span>
                        <span class="badge bg-danger">Alpha: <?= $absensi_data['alpha'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-lg-6 col-md-12">
                <div class="table-card h-100">
                    <h5><i class="bi bi-house-door"></i>Daftar Dapur</h5>
                    <div class="list-group list-group-flush">
                        <?php mysqli_data_seek($result_dapur, 0); ?>
                        <?php while($dapur = mysqli_fetch_assoc($result_dapur)): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= $dapur['nama_dapur'] ?></strong>
                                    <small class="text-muted">
                                        <i class="bi bi-people me-1"></i><?= $dapur['jumlah_karyawan'] ?> Karyawan
                                    </small>
                                </div>
                                <span class="badge-status <?= $dapur['status'] == 'aktif' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($dapur['status']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script src="../assets/js/auth.js"></script>

</body>
</html>