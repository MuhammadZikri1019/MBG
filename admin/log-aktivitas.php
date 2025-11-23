<?php
// admin/log-aktivitas.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter
$user_type_filter = isset($_GET['user_type']) ? escape($_GET['user_type']) : '';
$activity_filter = isset($_GET['activity']) ? escape($_GET['activity']) : '';
$date_from = isset($_GET['date_from']) ? escape($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? escape($_GET['date_to']) : '';

// Build query
$where = array();
if ($user_type_filter) {
    $where[] = "user_type = '$user_type_filter'";
}
if ($activity_filter) {
    $where[] = "activity LIKE '%$activity_filter%'";
}
if ($date_from) {
    $where[] = "DATE(created_at) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(created_at) <= '$date_to'";
}

$where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total records
$count_query = "SELECT COUNT(*) as total FROM tbl_log_aktivitas $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Get log data
$query = "SELECT * FROM tbl_log_aktivitas 
          $where_clause
          ORDER BY created_at DESC 
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_logs,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_logs,
    COUNT(CASE WHEN user_type = 'super_admin' THEN 1 END) as admin_logs,
    COUNT(CASE WHEN user_type = 'pengelola' THEN 1 END) as pengelola_logs,
    COUNT(CASE WHEN user_type = 'karyawan' THEN 1 END) as karyawan_logs
    FROM tbl_log_aktivitas";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Proses Clear Old Logs
if (isset($_GET['clear']) && $_GET['clear'] == 'old') {
    $days = 30; // Clear logs older than 30 days
    $delete_query = "DELETE FROM tbl_log_aktivitas WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)";
    if (mysqli_query($conn, $delete_query)) {
        $success = "Log aktivitas lama (>30 hari) berhasil dihapus!";
    } else {
        $error = "Gagal menghapus log aktivitas!";
    }
}

// Proses Clear All Logs
if (isset($_GET['clear']) && $_GET['clear'] == 'all') {
    $delete_query = "TRUNCATE TABLE tbl_log_aktivitas";
    if (mysqli_query($conn, $delete_query)) {
        $success = "Semua log aktivitas berhasil dihapus!";
    } else {
        $error = "Gagal menghapus log aktivitas!";
    }
}

// Function to get icon based on activity
function getActivityIcon($activity) {
    $activity_lower = strtolower($activity);
    if (strpos($activity_lower, 'login') !== false) return 'bi-box-arrow-in-right text-success';
    if (strpos($activity_lower, 'logout') !== false) return 'bi-box-arrow-right text-danger';
    if (strpos($activity_lower, 'tambah') !== false || strpos($activity_lower, 'create') !== false) return 'bi-plus-circle text-primary';
    if (strpos($activity_lower, 'edit') !== false || strpos($activity_lower, 'update') !== false) return 'bi-pencil-square text-warning';
    if (strpos($activity_lower, 'hapus') !== false || strpos($activity_lower, 'delete') !== false) return 'bi-trash text-danger';
    if (strpos($activity_lower, 'backup') !== false) return 'bi-database text-info';
    if (strpos($activity_lower, 'restore') !== false) return 'bi-arrow-clockwise text-warning';
    return 'bi-info-circle text-secondary';
}

// Function to get badge color based on user type
function getUserTypeBadge($user_type) {
    switch($user_type) {
        case 'super_admin': return 'badge-aktif';
        case 'pengelola': return 'badge-maintenance';
        case 'karyawan': return 'badge-nonaktif';
        default: return 'badge-aktif';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/dapur.css">
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Mobile Menu Toggle -->
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
            <a href="dashboard.php">
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
            <a href="log-aktivitas.php" class="active">
                <i class="bi bi-clock-history"></i>
                <span>Log Aktivitas</span>
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
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="mb-0">Log Aktivitas Sistem</h4>
                <small class="text-muted">Monitor semua aktivitas pengguna</small>
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

        <!-- Alert Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show animate-alert" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show animate-alert" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h3><?= number_format($stats['total_logs']) ?></h3>
                    <p>Total Log Aktivitas</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3><?= number_format($stats['today_logs']) ?></h3>
                    <p>Log Hari Ini</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3><?= number_format($stats['pengelola_logs']) ?></h3>
                    <p>Log Pengelola</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card info">
                    <div class="icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3><?= number_format($stats['karyawan_logs']) ?></h3>
                    <p>Log Karyawan</p>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="chart-card mb-4">
            <h5 class="mb-3"><i class="bi bi-funnel me-2"></i>Filter Log Aktivitas</h5>
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tipe User</label>
                        <select name="user_type" class="form-select">
                            <option value="">Semua User</option>
                            <option value="super_admin" <?= $user_type_filter == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            <option value="pengelola" <?= $user_type_filter == 'pengelola' ? 'selected' : '' ?>>Pengelola</option>
                            <option value="karyawan" <?= $user_type_filter == 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aktivitas</label>
                        <input type="text" name="activity" class="form-control" placeholder="Cari aktivitas..." value="<?= $activity_filter ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="log-aktivitas.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar mb-4">
            <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="exportLog()">
                    <i class="bi bi-download"></i>
                    Export Log
                </button>
                <button class="btn" style="background: #ffc107; color: #664d03; border: none;" 
                        onclick="clearOldLogs()">
                    <i class="bi bi-calendar-x"></i>
                    Hapus Log Lama
                </button>
                <button class="btn btn-danger" onclick="clearAllLogs()">
                    <i class="bi bi-trash"></i>
                    Hapus Semua Log
                </button>
            </div>
            <div class="text-muted">
                Menampilkan <?= min($offset + 1, $total_records) ?> - <?= min($offset + $limit, $total_records) ?> dari <?= $total_records ?> log
            </div>
        </div>

        <!-- Log Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="table-dapur">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No</th>
                            <th style="width: 8%;" class="text-center">User Type</th>
                            <th style="width: 15%;">User</th>
                            <th style="width: 10%;">IP Address</th>
                            <th style="width: 33%;">Aktivitas</th>
                            <th style="width: 15%;">Waktu</th>
                            <th style="width: 15%;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php 
                            $no = $offset + 1;
                            while ($log = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr class="dapur-row">
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center">
                                    <span class="badge <?= getUserTypeBadge($log['user_type']) ?>">
                                        <?= ucfirst($log['user_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="pengelola-info">
                                        <span class="pengelola-name"><?= $log['user_name'] ?? '-' ?></span>
                                        <small class="pengelola-email"><?= $log['user_email'] ?? '-' ?></small>
                                    </div>
                                </td>
                                <td>
                                    <small><?= $log['ip_address'] ?? '-' ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi <?= getActivityIcon($log['activity']) ?>" style="font-size: 18px;"></i>
                                        <span><?= $log['activity'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d M Y', strtotime($log['created_at'])) ?><br>
                                        <span class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= $log['description'] ? (strlen($log['description']) > 50 ? substr($log['description'], 0, 50) . '...' : $log['description']) : '-' ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 60px 20px;">
                                <div class="empty-state-inline">
                                    <i class="bi bi-inbox" style="font-size: 60px; color: var(--baby-blue-light);"></i>
                                    <h5 style="margin: 15px 0 5px; color: var(--text-dark);">Tidak ada log aktivitas</h5>
                                    <p style="color: var(--text-light); margin: 0;">Belum ada aktivitas yang terekam atau coba ubah filter</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $user_type_filter ? '&user_type='.$user_type_filter : '' ?><?= $activity_filter ? '&activity='.$activity_filter : '' ?><?= $date_from ? '&date_from='.$date_from : '' ?><?= $date_to ? '&date_to='.$date_to : '' ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $user_type_filter ? '&user_type='.$user_type_filter : '' ?><?= $activity_filter ? '&activity='.$activity_filter : '' ?><?= $date_from ? '&date_from='.$date_from : '' ?><?= $date_to ? '&date_to='.$date_to : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $user_type_filter ? '&user_type='.$user_type_filter : '' ?><?= $activity_filter ? '&activity='.$activity_filter : '' ?><?= $date_from ? '&date_from='.$date_from : '' ?><?= $date_to ? '&date_to='.$date_to : '' ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script>
        // ============================================
        // Toggle Sidebar Function
        // ============================================
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content');
            
            sidebar.classList.toggle('collapsed');
            overlay.classList.toggle('active');
            mainContent.classList.toggle('expanded');
            
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // ============================================
        // Load Sidebar State
        // ============================================
        window.addEventListener('DOMContentLoaded', function() {
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
        });

        // ============================================
        // Export Log Function
        // ============================================
        function exportLog() {
            const params = new URLSearchParams(window.location.search);
            const url = 'export-log.php?' + params.toString();
            window.open(url, '_blank');
        }

        // ============================================
        // Clear Old Logs Function
        // ============================================
        function clearOldLogs() {
            if (confirm('Hapus semua log aktivitas yang lebih dari 30 hari?\n\nData yang dihapus tidak dapat dikembalikan!')) {
                window.location.href = 'log-aktivitas.php?clear=old';
            }
        }

        // ============================================
        // Clear All Logs Function
        // ============================================
        function clearAllLogs() {
            if (confirm('Hapus SEMUA log aktivitas?\n\nPeringatan: Data yang dihapus tidak dapat dikembalikan!\n\nKetik "HAPUS" untuk konfirmasi.')) {
                const confirmation = prompt('Ketik "HAPUS" untuk konfirmasi:');
                if (confirmation === 'HAPUS') {
                    window.location.href = 'log-aktivitas.php?clear=all';
                }
            }
        }

        // ============================================
        // Auto Dismiss Alerts
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // ============================================
        // Smooth Scroll on Alert
        // ============================================
        window.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        console.log('📋 Log Aktivitas - Loaded Successfully!');
    </script>
    <style>
        /* Pagination Styles */
        .pagination {
            gap: 5px;
        }
        
        .page-link {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            color: var(--baby-blue);
            padding: 8px 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            background: var(--baby-blue-lighter);
            border-color: var(--baby-blue);
            color: var(--baby-blue-dark);
        }
        
        .page-item.active .page-link {
            background: var(--gradient);
            border-color: var(--baby-blue);
            color: white;
        }
        
        .page-item.disabled .page-link {
            background: #f8f9fa;
            border-color: #e0e0e0;
            color: #6c757d;
        }
    </style>
</body>
</html>