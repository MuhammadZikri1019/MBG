<?php
// admin/dashboard.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Ambil data dashboard dari view
$query_dashboard = "SELECT * FROM vw_dashboard_super_admin";
$result_dashboard = mysqli_query($conn, $query_dashboard);
$dashboard = mysqli_fetch_assoc($result_dashboard);

// Data untuk chart produksi 7 hari terakhir
$query_chart = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as bulan,
                    COUNT(CASE WHEN status = 'aktif' THEN 1 END) as total_aktif
                FROM tbl_pengelola_dapur
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY bulan ASC";
$result_chart = mysqli_query($conn, $query_chart);
$chart_data = [];
while($row = mysqli_fetch_assoc($result_chart)) {
    $chart_data[] = $row;
}

// Dapur status untuk sidebar
$query_dapur_stats = "SELECT COUNT(*) as total FROM tbl_dapur WHERE status = 'aktif'";
$dapur_stats = mysqli_fetch_assoc(mysqli_query($conn, $query_dapur_stats));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - MBG System</title>
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
            <small>Super Admin Panel</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="pengelola.php">
                <i class="bi bi-people"></i>
                <span>Kelola Pengelola</span>
            </a>
            <a href="dapur.php">
                <i class="bi bi-house"></i>
                <span>Kelola Dapur</span>
            </a>
            <a href="karyawan.php">
                <i class="bi bi-person-badge"></i>
                <span>Kelola Karyawan</span>
            </a>
            <a href="laporan-sistem.php">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan Sistem</span>
            </a>
            <a href="backup.php">
                <i class="bi bi-database"></i>
                <span>Backup & Restore</span>
            </a>
            <a href="settings.php">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Sistem</span>
            </a>
            <a href="log-aktivitas.php">
                <i class="bi bi-clock-history"></i>
                <span>Log Aktivitas</span>
            </a>
            <a href="profil.php"><i class="bi bi-person-circle"></i><span>Profil</span></a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="mb-0">Dashboard Overview</h4>
                <small class="text-muted">Selamat datang di sistem MBG</small>
            </div>
            <div class="user-profile">
                <a href="profil.php" class="text-decoration-none text-dark d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="fw-bold"><?= $_SESSION['user_name'] ?></div>
                        <small class="text-muted">Super Administrator</small>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                    </div>
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3><?= $dashboard['total_pengelola'] ?></h3>
                    <p>Pengelola</p>
                </div>
            </div>
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-house"></i>
                    </div>
                    <h3><?= $dashboard['total_dapur'] ?></h3>
                    <p>Dapur</p>
                </div>
            </div>
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3><?= $dashboard['total_karyawan'] ?></h3>
                    <p>Karyawan</p>
                </div>
            </div>
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card info">
                    <div class="icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>1</h3>
                    <p>Super Admin</p>
                </div>
            </div>
        </div>

        <!-- Charts & Tables -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 col-md-12">
                <div class="chart-card">
                    <h5><i class="bi bi-bar-chart-line"></i>Statistik Pengelola & Dapur per Bulan</h5>
                    <canvas id="systemChart"></canvas>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-12">
                <div class="table-card">
                    <h5><i class="bi bi-house-check"></i>Status Dapur</h5>
                    <div class="list-group list-group-flush">
                        <?php 
                        $query_dapur_status = "SELECT d.nama_dapur, d.status, COUNT(k.id_karyawan) as jumlah_karyawan
                                              FROM tbl_dapur d
                                              LEFT JOIN tbl_karyawan k ON d.id_dapur = k.id_dapur AND k.status = 'aktif'
                                              GROUP BY d.id_dapur
                                              ORDER BY d.created_at DESC
                                              LIMIT 5";
                        $result_dapur = mysqli_query($conn, $query_dapur_status);
                        
                        if(mysqli_num_rows($result_dapur) > 0): ?>
                            <?php while($dapur = mysqli_fetch_assoc($result_dapur)): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= $dapur['nama_dapur'] ?></strong>
                                        <small class="text-muted">
                                            <i class="bi bi-people"></i><?= $dapur['jumlah_karyawan'] ?> Karyawan
                                        </small>
                                    </div>
                                    <span class="badge-status <?= $dapur['status'] == 'aktif' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= ucfirst($dapur['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-house-slash" style="font-size: 48px;"></i>
                                <p class="mt-2">Belum ada dapur terdaftar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row g-4">
            <div class="col-lg-6 col-md-12">
                <div class="table-card">
                    <h5><i class="bi bi-person-plus"></i>Pengelola Terbaru</h5>
                    <div class="list-group list-group-flush">
                        <?php 
                        $query_pengelola = "SELECT nama, email, created_at 
                                          FROM tbl_pengelola_dapur 
                                          WHERE status = 'aktif'
                                          ORDER BY created_at DESC 
                                          LIMIT 5";
                        $result_pengelola_list = mysqli_query($conn, $query_pengelola);
                        
                        if(mysqli_num_rows($result_pengelola_list) > 0): ?>
                            <?php while($pengelola = mysqli_fetch_assoc($result_pengelola_list)): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= $pengelola['nama'] ?></strong>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i><?= $pengelola['email'] ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i><?= date('d M Y', strtotime($pengelola['created_at'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge-status bg-success">Aktif</span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-person-x" style="font-size: 48px;"></i>
                                <p class="mt-2">Belum ada pengelola terdaftar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="table-card">
                    <h5><i class="bi bi-person-badge"></i>Karyawan Terbaru</h5>
                    <div class="list-group list-group-flush">
                        <?php 
                        $query_karyawan_list = "SELECT k.nama, k.email, d.nama_dapur, k.created_at
                                              FROM tbl_karyawan k
                                              LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                                              WHERE k.status = 'aktif'
                                              ORDER BY k.created_at DESC 
                                              LIMIT 5";
                        $result_karyawan_list = mysqli_query($conn, $query_karyawan_list);
                        
                        if(mysqli_num_rows($result_karyawan_list) > 0): ?>
                            <?php while($karyawan = mysqli_fetch_assoc($result_karyawan_list)): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= $karyawan['nama'] ?></strong>
                                        <small class="text-muted">
                                            <i class="bi bi-house"></i><?= $karyawan['nama_dapur'] ?? 'Belum ditugaskan' ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i><?= date('d M Y', strtotime($karyawan['created_at'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge-status bg-success">Aktif</span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-person-x" style="font-size: 48px;"></i>
                                <p class="mt-2">Belum ada karyawan terdaftar</p>
                            </div>
                        <?php endif; ?>
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