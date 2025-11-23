<?php
// admin/backup.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Konfigurasi
$backup_dir = '../backups/';
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

// Proses Backup Database
if (isset($_POST['action']) && $_POST['action'] == 'backup') {
    // Disable script time limit
    set_time_limit(0);
    ignore_user_abort(true);
    
    $tables = array();
    // Use SHOW FULL TABLES to get table type (BASE TABLE vs VIEW)
    $result = mysqli_query($conn, 'SHOW FULL TABLES');
    
    if (!$result) {
        $error = "Gagal mengambil daftar tabel: " . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = ['name' => $row[0], 'type' => $row[1]];
        }
        
        $sql_script = "-- MBG System Database Backup\n";
        $sql_script .= "-- Backup Date: " . date('Y-m-d H:i:s') . "\n";
        $sql_script .= "-- Database: " . DB_NAME . "\n\n";
        $sql_script .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table_info) {
            $table = $table_info['name'];
            $type = $table_info['type'];
            
            // Get Create Table/View Statement
            // Use try-catch or suppress error for broken views
            try {
                $res_create = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
                if ($res_create) {
                    $row_create = mysqli_fetch_row($res_create);
                    $sql_script .= "-- Structure for $table ($type)\n";
                    
                    if ($type == 'VIEW') {
                        $sql_script .= "DROP VIEW IF EXISTS `$table`;\n";
                    } else {
                        $sql_script .= "DROP TABLE IF EXISTS `$table`;\n";
                    }
                    
                    $sql_script .= $row_create[1] . ";\n\n";
                }
            } catch (Exception $e) {
                $sql_script .= "-- Error dumping structure for $table: " . $e->getMessage() . "\n\n";
                continue; // Skip data dump if structure dump failed
            }
            
            // Dump Data ONLY for BASE TABLE
            if ($type == 'BASE TABLE') {
                $res_data = mysqli_query($conn, "SELECT * FROM `$table`");
                if ($res_data) {
                    $num_rows = mysqli_num_rows($res_data);
                    if ($num_rows > 0) {
                        $sql_script .= "-- Dumping data for table $table\n";
                        while ($row_data = mysqli_fetch_assoc($res_data)) {
                            $sql_script .= "INSERT INTO `$table` VALUES(";
                            $values = array();
                            foreach ($row_data as $value) {
                                if ($value === null) {
                                    $values[] = 'NULL';
                                } else {
                                    $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                                }
                            }
                            $sql_script .= implode(',', $values) . ");\n";
                        }
                        $sql_script .= "\n";
                    }
                }
            } else {
                $sql_script .= "-- Data dump skipped for VIEW $table\n\n";
            }
        }
        
        $sql_script .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        $filename = 'backup_' . DB_NAME . '_' . date('Y-m-d_His') . '.sql';
        $filepath = $backup_dir . $filename;
        
        if (file_put_contents($filepath, $sql_script) !== false) {
            $success = "Backup database berhasil! File: " . $filename;
        } else {
            $error = "Gagal menulis file backup ke folder: " . realpath($backup_dir);
            if (!is_writable($backup_dir)) {
                $error .= " (Folder tidak writable)";
            }
        }
    }
}

// Proses Restore Database
if (isset($_POST['action']) && $_POST['action'] == 'restore') {
    $filename = $_POST['backup_file'];
    $filepath = $backup_dir . $filename;
    
    if (file_exists($filepath)) {
        $sql_script = file_get_contents($filepath);
        
        mysqli_multi_query($conn, $sql_script);
        
        do {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        } while (mysqli_next_result($conn));
        
        $success = "Restore database berhasil dari file: " . $filename;
    } else {
        $error = "File backup tidak ditemukan!";
    }
}

// Proses Hapus Backup
if (isset($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = $backup_dir . $filename;
    
    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            $success = "File backup berhasil dihapus!";
        } else {
            $error = "Gagal menghapus file backup!";
        }
    }
}

// Proses Download Backup
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = $backup_dir . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }
}

// Ambil daftar file backup
$backup_files = array();
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $filepath = $backup_dir . $file;
            $backup_files[] = array(
                'name' => $file,
                'size' => filesize($filepath),
                'date' => filemtime($filepath)
            );
        }
    }
    
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Format file size
function formatSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - MBG System</title>
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
            <a href="backup.php" class="active">
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
                <h4 class="mb-0">Backup & Restore Database</h4>
                <small class="text-muted">Kelola backup dan restore database sistem</small>
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

        <!-- Info Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-database"></i>
                    </div>
                    <h3><?= DB_NAME ?></h3>
                    <p>Database</p>
                </div>
            </div>
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-hdd"></i>
                    </div>
                    <h3><?= count($backup_files) ?></h3>
                    <p>Total File</p>
                </div>
            </div>
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3><?= count($backup_files) > 0 ? date('d/m', $backup_files[0]['date']) : '-' ?></h3>
                    <p>Last Backup</p>
                </div>
            </div>
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card info">
                    <div class="icon">
                        <i class="bi bi-folder"></i>
                    </div>
                    <h3><?= formatSize(array_sum(array_column($backup_files, 'size'))) ?></h3>
                    <p>Total Size</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar">
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="backup">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Buat backup database sekarang?')">
                    <i class="bi bi-cloud-arrow-down"></i>
                    Buat Backup Sekarang
                </button>
            </form>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Cari file backup...">
            </div>
        </div>

        <!-- Backup Files Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="table-dapur">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 40%;">Nama File</th>
                            <th style="width: 12%;" class="text-center">Ukuran</th>
                            <th style="width: 18%;" class="text-center">Tanggal Backup</th>
                            <th style="width: 25%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($backup_files) > 0): ?>
                            <?php 
                            $no = 1;
                            foreach ($backup_files as $file): 
                            ?>
                            <tr class="dapur-row" data-name="<?= strtolower($file['name']) ?>">
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-zip" style="font-size: 20px; color: var(--baby-blue);"></i>
                                        <span class="dapur-name"><?= $file['name'] ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <strong><?= formatSize($file['size']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <?= date('d M Y, H:i', $file['date']) ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn-action restore" 
                                                onclick="restoreBackup('<?= $file['name'] ?>')" 
                                                data-bs-toggle="tooltip" title="Restore Database">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                        <a href="?download=<?= urlencode($file['name']) ?>" 
                                           class="btn-action download" 
                                           data-bs-toggle="tooltip" title="Download Backup">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button type="button" class="btn-action delete" 
                                                onclick="deleteBackup('<?= $file['name'] ?>')" 
                                                data-bs-toggle="tooltip" title="Hapus Backup">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 60px 20px;">
                                <div class="empty-state-inline">
                                    <i class="bi bi-inbox" style="font-size: 60px; color: var(--baby-blue-light);"></i>
                                    <h5 style="margin: 15px 0 5px; color: var(--text-dark);">Belum ada file backup</h5>
                                    <p style="color: var(--text-light); margin: 0;">Klik tombol "Buat Backup Sekarang" untuk membuat backup database</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State for Search -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="bi bi-search"></i>
            <h5>Tidak ada file ditemukan</h5>
            <p>Coba gunakan kata kunci yang berbeda</p>
        </div>

        <!-- Info Panel -->
        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <div class="chart-card">
                    <h5><i class="bi bi-info-circle me-2"></i>Informasi Backup</h5>
                    <div class="info-content">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Backup database dilakukan secara otomatis setiap hari
                            </li>
                            <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                File backup disimpan di folder <code>/backups/</code>
                            </li>
                            <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Format file: <code>backup_[database]_[tanggal].sql</code>
                            </li>
                            <li style="padding: 12px 0;">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Backup mencakup semua tabel dan data
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card">
                    <h5><i class="bi bi-exclamation-triangle me-2"></i>Peringatan</h5>
                    <div class="info-content">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                <i class="bi bi-x-circle text-danger me-2"></i>
                                Restore akan menimpa semua data yang ada
                            </li>
                            <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                <i class="bi bi-x-circle text-danger me-2"></i>
                                Pastikan backup sebelum melakukan restore
                            </li>
                            <li style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                <i class="bi bi-x-circle text-danger me-2"></i>
                                Download backup penting ke komputer lokal
                            </li>
                            <li style="padding: 12px 0;">
                                <i class="bi bi-x-circle text-danger me-2"></i>
                                Hapus backup lama untuk menghemat ruang disk
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Restore Confirmation -->
    <div class="modal fade" id="modalRestore" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Konfirmasi Restore Database
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="restore">
                    <input type="hidden" name="backup_file" id="restore_file">
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Peringatan!</strong> Proses restore akan mengganti semua data yang ada saat ini dengan data dari file backup.
                        </div>
                        <p class="mb-3">File backup yang akan di-restore:</p>
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-file-earmark-zip me-2"></i>
                            <strong id="restore_filename"></strong>
                        </div>
                        <p class="text-danger mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Pastikan Anda telah membuat backup terbaru sebelum melakukan restore!
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border: none;">
                            <i class="bi bi-arrow-clockwise"></i>
                            Ya, Restore Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
        // Search Functionality
        // ============================================
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.dapur-row');
            const emptyState = document.getElementById('emptyState');
            const tableContainer = document.querySelector('.table-container');
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                
                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (visibleCount === 0) {
                tableContainer.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                tableContainer.style.display = 'block';
                emptyState.style.display = 'none';
            }
        });

        // ============================================
        // Restore Backup Function
        // ============================================
        function restoreBackup(filename) {
            document.getElementById('restore_file').value = filename;
            document.getElementById('restore_filename').textContent = filename;
            
            const modal = new bootstrap.Modal(document.getElementById('modalRestore'));
            modal.show();
        }

        // ============================================
        // Delete Backup Function
        // ============================================
        function deleteBackup(filename) {
            if (confirm(`Apakah Anda yakin ingin menghapus file backup:\n\n${filename}\n\nFile yang dihapus tidak dapat dikembalikan!`)) {
                window.location.href = `backup.php?delete=${encodeURIComponent(filename)}`;
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
        // Auto dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        console.log('💾 Backup & Restore - Loaded Successfully!');
    </script>
</body>
</html>