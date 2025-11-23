<?php
// admin/settings.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// File konfigurasi
$config_file = '../config/settings.json';
$config_dir = '../config/';

// Buat folder config jika belum ada
if (!file_exists($config_dir)) {
    mkdir($config_dir, 0777, true);
}

// Load settings
$default_settings = array(
    'app_name' => 'MBG System',
    'app_description' => 'Sistem Manajemen Bakery & Gudang',
    'app_email' => 'admin@mbgsystem.com',
    'app_phone' => '0812-3456-7890',
    'app_address' => 'Jakarta, Indonesia',
    'timezone' => 'Asia/Jakarta',
    'date_format' => 'd/m/Y',
    'time_format' => 'H:i',
    'currency' => 'IDR',
    'language' => 'id',
    'pagination' => 10,
    'session_timeout' => 3600,
    'maintenance_mode' => false,
    'email_notifications' => true,
    'backup_auto' => true,
    'backup_schedule' => 'daily',
    'max_upload_size' => 5,
    'allowed_extensions' => 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx'
);

// Load existing settings
if (file_exists($config_file)) {
    $settings = json_decode(file_get_contents($config_file), true);
    $settings = array_merge($default_settings, $settings);
} else {
    $settings = $default_settings;
}

// Proses Update Settings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'general') {
        $settings['app_name'] = escape($_POST['app_name']);
        $settings['app_description'] = escape($_POST['app_description']);
        $settings['app_email'] = escape($_POST['app_email']);
        $settings['app_phone'] = escape($_POST['app_phone']);
        $settings['app_address'] = escape($_POST['app_address']);
        
        if (file_put_contents($config_file, json_encode($settings, JSON_PRETTY_PRINT))) {
            $success = "Pengaturan umum berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan pengaturan umum!";
        }
    }
    
    if ($_POST['action'] == 'system') {
        $settings['timezone'] = escape($_POST['timezone']);
        $settings['date_format'] = escape($_POST['date_format']);
        $settings['time_format'] = escape($_POST['time_format']);
        $settings['currency'] = escape($_POST['currency']);
        $settings['language'] = escape($_POST['language']);
        $settings['pagination'] = (int)$_POST['pagination'];
        $settings['session_timeout'] = (int)$_POST['session_timeout'];
        
        if (file_put_contents($config_file, json_encode($settings, JSON_PRETTY_PRINT))) {
            $success = "Pengaturan sistem berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan pengaturan sistem!";
        }
    }
    
    if ($_POST['action'] == 'features') {
        $settings['maintenance_mode'] = isset($_POST['maintenance_mode']);
        $settings['email_notifications'] = isset($_POST['email_notifications']);
        $settings['backup_auto'] = isset($_POST['backup_auto']);
        $settings['backup_schedule'] = escape($_POST['backup_schedule']);
        
        if (file_put_contents($config_file, json_encode($settings, JSON_PRETTY_PRINT))) {
            $success = "Pengaturan fitur berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan pengaturan fitur!";
        }
    }
    
    if ($_POST['action'] == 'upload') {
        $settings['max_upload_size'] = (int)$_POST['max_upload_size'];
        $settings['allowed_extensions'] = escape($_POST['allowed_extensions']);
        
        if (file_put_contents($config_file, json_encode($settings, JSON_PRETTY_PRINT))) {
            $success = "Pengaturan upload berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan pengaturan upload!";
        }
    }
    
    if ($_POST['action'] == 'password') {
        $current_password = escape($_POST['current_password']);
        $new_password = escape($_POST['new_password']);
        $confirm_password = escape($_POST['confirm_password']);
        
        // Verify current password
        $query = "SELECT * FROM tbl_super_admin WHERE id_super_admin = '{$_SESSION['user_id']}' AND password = '$current_password'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            if ($new_password == $confirm_password) {
                $update = "UPDATE tbl_super_admin SET password = '$new_password' WHERE id_super_admin = '{$_SESSION['user_id']}'";
                if (mysqli_query($conn, $update)) {
                    $success = "Password berhasil diubah!";
                } else {
                    $error = "Gagal mengubah password!";
                }
            } else {
                $error = "Password baru dan konfirmasi tidak sama!";
            }
        } else {
            $error = "Password saat ini salah!";
        }
    }
    
    // Reload settings after update
    if (file_exists($config_file)) {
        $settings = json_decode(file_get_contents($config_file), true);
        $settings = array_merge($default_settings, $settings);
    }
}

// Get admin info
$query_admin = "SELECT * FROM tbl_super_admin WHERE id_super_admin = '{$_SESSION['user_id']}'";
$admin_info = mysqli_fetch_assoc(mysqli_query($conn, $query_admin));

// Get database info
$db_size_query = "SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
    FROM information_schema.TABLES 
    WHERE table_schema = '" . DB_NAME . "'";
$db_size = mysqli_fetch_assoc(mysqli_query($conn, $db_size_query))['size_mb'];

$table_count_query = "SELECT COUNT(*) as total FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'";
$table_count = mysqli_fetch_assoc(mysqli_query($conn, $table_count_query))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - MBG System</title>
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
            <a href="settings.php" class="active">
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
                <h4 class="mb-0">Pengaturan Sistem</h4>
                <small class="text-muted">Konfigurasi dan pengaturan aplikasi</small>
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

        <!-- System Info Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-server"></i>
                    </div>
                    <h3>PHP <?= phpversion() ?></h3>
                    <p>PHP Version</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-database"></i>
                    </div>
                    <h3><?= $db_size ?> MB</h3>
                    <p>Database Size</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-table"></i>
                    </div>
                    <h3><?= $table_count ?></h3>
                    <p>Total Tables</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card info">
                    <div class="icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h3><?= $settings['timezone'] ?></h3>
                    <p>Timezone</p>
                </div>
            </div>
        </div>

        <!-- Settings Tabs -->
        <div class="chart-card mb-4">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                        <i class="bi bi-info-circle me-2"></i>Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button">
                        <i class="bi bi-gear me-2"></i>Sistem
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button">
                        <i class="bi bi-toggles me-2"></i>Fitur
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button">
                        <i class="bi bi-cloud-upload me-2"></i>Upload
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
                        <i class="bi bi-shield-lock me-2"></i>Keamanan
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4" id="settingsTabsContent">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="general">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Aplikasi <span class="text-danger">*</span></label>
                            <input type="text" name="app_name" class="form-control" value="<?= $settings['app_name'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Aplikasi</label>
                            <textarea name="app_description" class="form-control" rows="2"><?= $settings['app_description'] ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email Kontak <span class="text-danger">*</span></label>
                                <input type="email" name="app_email" class="form-control" value="<?= $settings['app_email'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nomor Telepon</label>
                                <input type="text" name="app_phone" class="form-control" value="<?= $settings['app_phone'] ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea name="app_address" class="form-control" rows="2"><?= $settings['app_address'] ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Pengaturan Umum
                        </button>
                    </form>
                </div>

                <!-- System Settings -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="system">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Timezone <span class="text-danger">*</span></label>
                                <select name="timezone" class="form-select" required>
                                    <option value="Asia/Jakarta" <?= $settings['timezone'] == 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta (WIB)</option>
                                    <option value="Asia/Makassar" <?= $settings['timezone'] == 'Asia/Makassar' ? 'selected' : '' ?>>Asia/Makassar (WITA)</option>
                                    <option value="Asia/Jayapura" <?= $settings['timezone'] == 'Asia/Jayapura' ? 'selected' : '' ?>>Asia/Jayapura (WIT)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bahasa <span class="text-danger">*</span></label>
                                <select name="language" class="form-select" required>
                                    <option value="id" <?= $settings['language'] == 'id' ? 'selected' : '' ?>>Indonesia</option>
                                    <option value="en" <?= $settings['language'] == 'en' ? 'selected' : '' ?>>English</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Format Tanggal</label>
                                <select name="date_format" class="form-select">
                                    <option value="d/m/Y" <?= $settings['date_format'] == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                    <option value="m/d/Y" <?= $settings['date_format'] == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                    <option value="Y-m-d" <?= $settings['date_format'] == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Format Waktu</label>
                                <select name="time_format" class="form-select">
                                    <option value="H:i" <?= $settings['time_format'] == 'H:i' ? 'selected' : '' ?>>24 Jam (HH:mm)</option>
                                    <option value="h:i A" <?= $settings['time_format'] == 'h:i A' ? 'selected' : '' ?>>12 Jam (hh:mm AM/PM)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Mata Uang</label>
                                <select name="currency" class="form-select">
                                    <option value="IDR" <?= $settings['currency'] == 'IDR' ? 'selected' : '' ?>>IDR (Rupiah)</option>
                                    <option value="USD" <?= $settings['currency'] == 'USD' ? 'selected' : '' ?>>USD (Dollar)</option>
                                    <option value="EUR" <?= $settings['currency'] == 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Items Per Page</label>
                                <input type="number" name="pagination" class="form-control" value="<?= $settings['pagination'] ?>" min="5" max="100">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Session Timeout (detik)</label>
                                <input type="number" name="session_timeout" class="form-control" value="<?= $settings['session_timeout'] ?>" min="300" max="86400">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Pengaturan Sistem
                        </button>
                    </form>
                </div>

                <!-- Features Settings -->
                <div class="tab-pane fade" id="features" role="tabpanel">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="features">
                        
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" 
                                       <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="maintenance_mode">
                                    Mode Maintenance
                                    <br><small class="text-muted">Nonaktifkan akses untuk user biasa (kecuali admin)</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" 
                                       <?= $settings['email_notifications'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="email_notifications">
                                    Notifikasi Email
                                    <br><small class="text-muted">Kirim notifikasi via email untuk aktivitas penting</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="backup_auto" id="backup_auto" 
                                       <?= $settings['backup_auto'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="backup_auto">
                                    Backup Otomatis
                                    <br><small class="text-muted">Backup database secara otomatis sesuai jadwal</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Jadwal Backup</label>
                            <select name="backup_schedule" class="form-select">
                                <option value="daily" <?= $settings['backup_schedule'] == 'daily' ? 'selected' : '' ?>>Harian</option>
                                <option value="weekly" <?= $settings['backup_schedule'] == 'weekly' ? 'selected' : '' ?>>Mingguan</option>
                                <option value="monthly" <?= $settings['backup_schedule'] == 'monthly' ? 'selected' : '' ?>>Bulanan</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Pengaturan Fitur
                        </button>
                    </form>
                </div>

                <!-- Upload Settings -->
                <div class="tab-pane fade" id="upload" role="tabpanel">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="upload">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Maximum Upload Size (MB)</label>
                            <input type="number" name="max_upload_size" class="form-control" value="<?= $settings['max_upload_size'] ?>" min="1" max="50">
                            <small class="text-muted">Ukuran maksimal file yang dapat diupload (1-50 MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Allowed File Extensions</label>
                            <input type="text" name="allowed_extensions" class="form-control" value="<?= $settings['allowed_extensions'] ?>">
                            <small class="text-muted">Ekstensi file yang diizinkan, pisahkan dengan koma (contoh: jpg,png,pdf)</small>
                        </div>
                        
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Current PHP Upload Limits:</strong><br>
                            upload_max_filesize: <?= ini_get('upload_max_filesize') ?> | 
                            post_max_size: <?= ini_get('post_max_size') ?> | 
                            max_execution_time: <?= ini_get('max_execution_time') ?>s
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Pengaturan Upload
                        </button>
                    </form>
                </div>

                <!-- Security Settings -->
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <h6 class="mb-3 fw-bold">Ubah Password Admin</h6>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="password">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Masukkan password saat ini">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Minimal 6 karakter" minlength="6">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required placeholder="Ulangi password baru" minlength="6">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>Ubah Password
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3 fw-bold">Informasi Admin</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <td width="200"><strong>Nama Lengkap</strong></td>
                                <td><?= $admin_info['nama_lengkap'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Username</strong></td>
                                <td><?= $admin_info['username'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td><?= $admin_info['email'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Login</strong></td>
                                <td><?= $admin_info['last_login'] ? date('d M Y, H:i', strtotime($admin_info['last_login'])) : '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td><span class="badge badge-aktif"><?= ucfirst($admin_info['status']) ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
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
        // Form Validation
        // ============================================
        (function() {
            'use strict';
            const forms = document.querySelectorAll('form');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // ============================================
        // Loading Animation on Submit
        // ============================================
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && this.checkValidity()) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
                }
            });
        });

        // ============================================
        // Password Confirmation Validation
        // ============================================
        const passwordForm = document.querySelector('form[action*="password"]');
        if (passwordForm) {
            const newPassword = passwordForm.querySelector('input[name="new_password"]');
            const confirmPassword = passwordForm.querySelector('input[name="confirm_password"]');
            
            confirmPassword.addEventListener('input', function() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Password tidak sama!');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
            
            newPassword.addEventListener('input', function() {
                if (confirmPassword.value && newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Password tidak sama!');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
        }

        // ============================================
        // Smooth Scroll on Alert
        // ============================================
        window.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert');
        // Tab Navigation State
        // ============================================
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(event) {
                const tabId = event.target.getAttribute('data-bs-target');
                localStorage.setItem('activeSettingsTab', tabId);
            });
        });

        // Load last active tab
        window.addEventListener('DOMContentLoaded', function() {
            const lastTab = localStorage.getItem('activeSettingsTab');
            if (lastTab) {
                const tabButton = document.querySelector(`[data-bs-target="${lastTab}"]`);
                if (tabButton) {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }
            }
        });

        console.log('⚙️ Pengaturan Sistem - Loaded Successfully!');
    </script>
    <style>
        /* Custom Tab Styles */
        .nav-tabs {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 0;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: var(--text-light);
            padding: 15px 20px;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--baby-blue);
            border-bottom-color: var(--baby-blue-light);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--baby-blue);
            background: none;
            border-bottom-color: var(--baby-blue);
        }
        
        /* Form Styles */
        .form-label.fw-bold {
            color: var(--text-dark);
            font-size: 14px;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--baby-blue);
            box-shadow: 0 0 0 0.25rem rgba(137, 207, 240, 0.25);
        }
        
        /* Switch Styles */
        .form-check-input:checked {
            background-color: var(--baby-blue);
            border-color: var(--baby-blue);
        }
        
        .form-check-input:focus {
            border-color: var(--baby-blue);
            box-shadow: 0 0 0 0.25rem rgba(137, 207, 240, 0.25);
        }
        
        /* Table Styles */
        .table {
            margin: 0;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .table tr:last-child td {
            border-bottom: none;
        }
        
        /* Responsive Tabs */
        @media (max-width: 768px) {
            .nav-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .nav-tabs .nav-link {
                padding: 12px 15px;
                font-size: 13px;
                white-space: nowrap;
            }
            
            .tab-content {
                padding: 20px 15px !important;
            }
        }
        
        @media (max-width: 576px) {
            .nav-tabs .nav-link {
                padding: 10px 12px;
                font-size: 12px;
            }
            
            .nav-tabs .nav-link i {
                display: none;
            }
        }
    </style>
</body>
</html>