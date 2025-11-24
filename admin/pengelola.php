<?php
// admin/pengelola.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Handle CRUD Operations
$message = '';
$message_type = '';

// CREATE - Tambah Pengelola
if (isset($_POST['tambah_pengelola'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Cek email sudah ada atau belum
    $check_email = mysqli_query($conn, "SELECT * FROM tbl_pengelola_dapur WHERE email = '$email'");
    
    if (mysqli_num_rows($check_email) > 0) {
        $message = "Email sudah terdaftar!";
        $message_type = "danger";
    } else {
        $query = "INSERT INTO tbl_pengelola_dapur (nama, email, no_telepon, password, id_role, status) 
                  VALUES ('$nama', '$email', '$no_telepon', '$password', 2, 'aktif')";
        
        if (mysqli_query($conn, $query)) {
            $message = "Pengelola berhasil ditambahkan!";
            $message_type = "success";
            
            // Log aktivitas
            $id_admin = $_SESSION['user_id'];
            $id_pengelola = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO tbl_admin_log (id_super_admin, aktivitas, tabel_target, id_target, deskripsi) 
                                VALUES ($id_admin, 'CREATE', 'tbl_pengelola_dapur', $id_pengelola, 'Menambah pengelola: $nama')");
        } else {
            $message = "Gagal menambahkan pengelola: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}

// UPDATE - Edit Pengelola
if (isset($_POST['edit_pengelola'])) {
    $id_pengelola = $_POST['id_pengelola'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE tbl_pengelola_dapur SET 
              nama = '$nama', 
              email = '$email', 
              no_telepon = '$no_telepon',
              status = '$status'
              WHERE id_pengelola = $id_pengelola";
    
    if (mysqli_query($conn, $query)) {
        $message = "Data pengelola berhasil diupdate!";
        $message_type = "success";
        
        // Log aktivitas
        $id_admin = $_SESSION['user_id'];
        mysqli_query($conn, "INSERT INTO tbl_admin_log (id_super_admin, aktivitas, tabel_target, id_target, deskripsi) 
                            VALUES ($id_admin, 'UPDATE', 'tbl_pengelola_dapur', $id_pengelola, 'Mengupdate pengelola: $nama')");
    } else {
        $message = "Gagal mengupdate pengelola: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// UPDATE PASSWORD
if (isset($_POST['reset_password'])) {
    $id_pengelola = $_POST['id_pengelola'];
    $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    
    $query = "UPDATE tbl_pengelola_dapur SET password = '$password_baru' WHERE id_pengelola = $id_pengelola";
    
    if (mysqli_query($conn, $query)) {
        $message = "Password berhasil direset!";
        $message_type = "success";
        
        // Log aktivitas
        $id_admin = $_SESSION['user_id'];
        mysqli_query($conn, "INSERT INTO tbl_admin_log (id_super_admin, aktivitas, tabel_target, id_target, deskripsi) 
                            VALUES ($id_admin, 'UPDATE', 'tbl_pengelola_dapur', $id_pengelola, 'Reset password pengelola')");
    } else {
        $message = "Gagal reset password: " . mysqli_error($conn);
        $message_type = "danger";
    }
}

// DELETE - Hapus Pengelola
if (isset($_GET['hapus'])) {
    $id_pengelola = $_GET['hapus'];
    
    // Cek apakah pengelola punya dapur
    $check_dapur = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_dapur WHERE id_pengelola = $id_pengelola");
    $row_dapur = mysqli_fetch_assoc($check_dapur);
    
    if ($row_dapur['total'] > 0) {
        $message = "Tidak dapat menghapus! Pengelola masih memiliki dapur yang terdaftar.";
        $message_type = "warning";
    } else {
        $query = "DELETE FROM tbl_pengelola_dapur WHERE id_pengelola = $id_pengelola";
        
        if (mysqli_query($conn, $query)) {
            $message = "Pengelola berhasil dihapus!";
            $message_type = "success";
            
            // Log aktivitas
            $id_admin = $_SESSION['user_id'];
            mysqli_query($conn, "INSERT INTO tbl_admin_log (id_super_admin, aktivitas, tabel_target, id_target, deskripsi) 
                                VALUES ($id_admin, 'DELETE', 'tbl_pengelola_dapur', $id_pengelola, 'Menghapus pengelola')");
        } else {
            $message = "Gagal menghapus pengelola: " . mysqli_error($conn);
            $message_type = "danger";
        }
    }
}

// Ambil data pengelola
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query_pengelola = "SELECT p.*, 
                    (SELECT COUNT(*) FROM tbl_dapur WHERE id_pengelola = p.id_pengelola) as jumlah_dapur,
                    (SELECT COUNT(*) FROM tbl_karyawan k 
                     INNER JOIN tbl_dapur d ON k.id_dapur = d.id_dapur 
                     WHERE d.id_pengelola = p.id_pengelola AND k.status = 'aktif') as total_karyawan,
                    (SELECT GROUP_CONCAT(d.nama_dapur SEPARATOR '|||')
                     FROM tbl_dapur d 
                     WHERE d.id_pengelola = p.id_pengelola) as daftar_dapur,
                    (SELECT GROUP_CONCAT(
                        (SELECT COUNT(*) FROM tbl_karyawan WHERE id_dapur = d.id_dapur AND status = 'aktif') 
                        SEPARATOR '|||')
                     FROM tbl_dapur d 
                     WHERE d.id_pengelola = p.id_pengelola) as jumlah_karyawan_perdapur
                    FROM tbl_pengelola_dapur p 
                    WHERE 1=1";

if ($search != '') {
    $query_pengelola .= " AND (p.nama LIKE '%$search%' OR p.email LIKE '%$search%' OR p.no_telepon LIKE '%$search%')";
}

if ($status_filter != '') {
    $query_pengelola .= " AND p.status = '$status_filter'";
}

$query_pengelola .= " ORDER BY p.created_at DESC";
$result_pengelola = mysqli_query($conn, $query_pengelola);

// Statistik
$stats_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_pengelola_dapur WHERE status = 'aktif'"))['total'];
$stats_nonaktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_pengelola_dapur WHERE status = 'nonaktif'"))['total'];
$stats_total = $stats_aktif + $stats_nonaktif;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengelola - MBG System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    
    <style>
        .content-header {
            background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(137, 207, 240, 0.3);
        }
        
        .stats-card {
            border-radius: 15px;
            border: none !important;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(137, 207, 240, 0.3);
        }
        
        .stats-card .card-body {
            padding: 25px;
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .icon-blue {
            background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%);
            color: white;
        }
        
        .icon-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .icon-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .content-card {
            border-radius: 15px;
            border: none !important;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .table-responsive-custom {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%);
            color: white;
        }
        
        .table thead th {
            border: none;
            font-weight: 600;
            padding: 15px 10px;
            vertical-align: middle;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
        }
        
        .btn-baby-blue {
            background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-baby-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(137, 207, 240, 0.4);
            color: white;
        }
        
        .badge-custom {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 11px;
            white-space: nowrap;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s ease;
            margin: 2px;
            padding: 0;
            cursor: pointer;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .action-btn i {
            font-size: 14px;
        }
        
        .action-btn.edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .action-btn.password {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .action-btn.delete {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }
        
        .search-box {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            border-color: #89cff0;
            box-shadow: 0 0 0 4px rgba(137, 207, 240, 0.1);
            outline: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #89cff0;
            box-shadow: 0 0 0 4px rgba(137, 207, 240, 0.1);
            outline: none;
        }

        .user-avatar-table {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
        }

        .dapur-item {
            font-size: 12px;
            padding: 4px 0;
            display: flex;
            align-items: start;
            gap: 5px;
        }

        .dapur-item i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* Desktop - Lebar tabel normal */
        @media (min-width: 1200px) {
            .table {
                min-width: 100%;
            }
        }

        /* Tablet & Mobile - Enable horizontal scroll */
        @media (max-width: 1199px) {
            .table {
                min-width: 1100px;
            }
            
            .top-navbar {
                padding: 15px 20px;
            }
            
            .user-profile {
                font-size: 14px;
            }
            
            .stats-card .card-body {
                padding: 20px;
            }
            
            .stats-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }

        @media (max-width: 991px) {
            .content-card .card-body {
                padding: 15px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            .user-avatar-table {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .dapur-item {
                font-size: 11px;
            }
        }

        @media (max-width: 767px) {
            .top-navbar h4 {
                font-size: 18px;
            }
            
            .top-navbar small {
                font-size: 12px;
            }
            
            .user-profile > div:first-child {
                display: none;
            }
            
            .stats-card .card-body {
                padding: 15px;
                text-align: center;
            }
            
            .stats-icon {
                margin: 0 auto 10px;
            }
            
            .content-card .card-body {
                padding: 15px;
            }
            
            .toolbar-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-baby-blue {
                width: 100%;
            }
            
            .search-form {
                width: 100%;
            }
            
            .table {
                font-size: 12px;
            }
            
            .badge-custom {
                padding: 4px 8px;
                font-size: 10px;
            }
            
            .action-btn {
                width: 28px;
                height: 28px;
                margin: 1px;
            }

            .action-btn i {
                font-size: 12px;
            }
        }

        @media (max-width: 575px) {
            .stats-card .card-body h2 {
                font-size: 24px;
            }
            
            .stats-card .card-body h6 {
                font-size: 11px;
            }
            
            .form-control,
            .form-select {
                padding: 8px 12px;
                font-size: 14px;
            }
            
            .modal-body {
                padding: 15px;
            }

            .table thead th,
            .table tbody td {
                font-size: 11px;
                padding: 8px 5px;
            }
        }
    </style>
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
            <a href="pengelola.php" class="active">
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
            <a href="log-aktivitas.php"><i class="bi bi-clock-history"></i><span>Log Aktivitas</span></a>
            <a href="profil.php"><i class="bi bi-person-circle"></i><span>Profil</span></a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="mb-0">Kelola Pengelola Dapur</h4>
                <small class="text-muted">Manajemen Data Pengelola</small>
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
        <?php if ($message != ''): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'danger' ? 'exclamation-circle' : 'exclamation-triangle'); ?>-fill me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="card-body">
                        <div class="stats-icon icon-blue">
                            <i class="bi bi-people"></i>
                        </div>
                        <h6 class="text-muted mb-2">Total Pengelola</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $stats_total; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="card-body">
                        <div class="stats-icon icon-success">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <h6 class="text-muted mb-2">Pengelola Aktif</h6>
                        <h2 class="mb-0 fw-bold text-success"><?php echo $stats_aktif; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="card-body">
                        <div class="stats-icon icon-warning">
                            <i class="bi bi-person-x"></i>
                        </div>
                        <h6 class="text-muted mb-2">Pengelola Nonaktif</h6>
                        <h2 class="mb-0 fw-bold text-danger"><?php echo $stats_nonaktif; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="card-body">
                        <div class="stats-icon icon-blue">
                            <i class="bi bi-house"></i>
                        </div>
                        <h6 class="text-muted mb-2">Total Dapur</h6>
                        <h2 class="mb-0 fw-bold"><?php 
                            $total_dapur = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_dapur"))['total'];
                            echo $total_dapur; 
                        ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card content-card">
            <div class="card-body">
                <!-- Toolbar -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mb-4 gap-3 toolbar-actions">
                    <div>
                        <button class="btn btn-baby-blue" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Pengelola
                        </button>
                    </div>
                    <div class="flex-grow-1">
                        <form method="GET" class="row g-2 search-form">
                            <div class="col-md-7 col-12">
                                <input type="text" name="search" class="form-control search-box" 
                                       placeholder="Cari nama, email, atau telepon..." 
                                       value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-3 col-6">
                                <select name="status" class="form-select search-box">
                                    <option value="">Semua Status</option>
                                    <option value="aktif" <?php echo $status_filter == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo $status_filter == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <button type="submit" class="btn btn-baby-blue w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive-custom">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="18%">Nama Pengelola</th>
                                <th width="15%">Email</th>
                                <th width="12%">No. Telepon</th>
                                <th width="17%">Nama Dapur</th>
                                <th width="10%">Jumlah Karyawan</th>
                                <th width="9%">Status</th>
                                <th width="10%">Terdaftar</th>
                                <th width="8%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result_pengelola) > 0):
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result_pengelola)): 
                                    $dapur_array = $row['daftar_dapur'] ? explode('|||', $row['daftar_dapur']) : [];
                                    $karyawan_array = $row['jumlah_karyawan_perdapur'] ? explode('|||', $row['jumlah_karyawan_perdapur']) : [];
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-table me-2">
                                            <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="font-size: 13px;"><?php echo $row['nama']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small style="font-size: 12px;"><?php echo $row['email']; ?></small>
                                </td>
                                <td><small><?php echo $row['no_telepon']; ?></small></td>
                                <td>
                                    <?php if ($row['jumlah_dapur'] > 0): ?>
                                        <div style="max-height: 120px; overflow-y: auto;">
                                            <?php 
                                            $max_display = 3;
                                            for ($i = 0; $i < min(count($dapur_array), $max_display); $i++): 
                                            ?>
                                            <div class="dapur-item">
                                                <i class="bi bi-house-door text-primary"></i>
                                                <span><?php echo $dapur_array[$i]; ?></span>
                                            </div>
                                            <?php endfor; ?>
                                            
                                            <?php if (count($dapur_array) > $max_display): ?>
                                            <div class="dapur-item text-muted">
                                                <i class="bi bi-three-dots"></i>
                                                <small><em>+<?php echo count($dapur_array) - $max_display; ?> dapur lainnya</em></small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Belum ada dapur
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['jumlah_dapur'] > 0): ?>
                                        <div style="max-height: 120px; overflow-y: auto;">
                                            <?php 
                                            $max_display = 3;
                                            for ($i = 0; $i < min(count($karyawan_array), $max_display); $i++): 
                                            ?>
                                            <div class="dapur-item">
                                                <i class="bi bi-people text-success"></i>
                                                <span><?php echo $karyawan_array[$i]; ?> orang</span>
                                            </div>
                                            <?php endfor; ?>
                                            
                                            <?php if (count($karyawan_array) > $max_display): ?>
                                            <div class="dapur-item text-muted">
                                                <i class="bi bi-three-dots"></i>
                                                <small><em>...</em></small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-1">
                                            <small class="badge bg-info">
                                                Total: <?php echo $row['total_karyawan']; ?>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <span class="badge badge-custom bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-custom bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted" style="font-size: 11px;">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn-action edit" 
                                                onclick='editPengelola(<?= json_encode($row) ?>)'
                                                data-bs-toggle="tooltip" title="Edit Data">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <button type="button" class="btn-action password" 
                                                onclick="resetPassword(<?= $row['id_pengelola'] ?>, '<?= $row['nama'] ?>')"
                                                data-bs-toggle="tooltip" title="Reset Password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        
                                        <button type="button" class="btn-action delete" 
                                                onclick="hapusPengelola(<?= $row['id_pengelola'] ?>, '<?= $row['nama'] ?>')"
                                                data-bs-toggle="tooltip" title="Hapus Pengelola">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <h5 class="text-muted mt-3">Belum ada data pengelola</h5>
                                    <p class="text-muted">Klik tombol "Tambah Pengelola" untuk menambah data</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>Tambah Pengelola Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telepon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_pengelola" class="btn btn-baby-blue">
                            <i class="bi bi-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Pengelola
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_pengelola" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telepon" id="edit_telepon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_pengelola" class="btn btn-baby-blue">
                            <i class="bi bi-save me-2"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div class="modal fade" id="modalResetPassword" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-key me-2"></i>Reset Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_pengelola" id="reset_id">
                        <p class="text-muted">Reset password untuk: <strong id="reset_nama"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="reset_password" class="btn btn-baby-blue">
                            <i class="bi bi-check-lg me-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script src="../assets/js/auth.js"></script>
    
    <script>
        // CRITICAL: Force enable scroll on load
        function forceEnableScroll() {
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.classList.remove('mobile-sidebar-active');
            document.documentElement.style.overflow = '';
        }

        // Toggle Sidebar dengan ROBUST scroll fix
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isMobile = window.innerWidth < 1200;
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            if (isMobile) {
                // Mobile: Toggle scroll lock
                if (sidebar.classList.contains('active')) {
                    document.body.classList.add('mobile-sidebar-active');
                } else {
                    forceEnableScroll();
                }
            } else {
                // Desktop: ALWAYS enable scroll
                forceEnableScroll();
            }
        }

        // Close sidebar and FORCE enable scroll
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            
            // FORCE enable scroll
            forceEnableScroll();
        }

        // ESC key handler
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (sidebar.classList.contains('active')) {
                    closeSidebar();
                }
            }
        });

        // Overlay click handler
        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            closeSidebar();
        });

        // Menu link click handler
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth < 1200) {
                    setTimeout(closeSidebar, 150);
                }
            });
        });

        // Window resize handler
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 1200) {
                    // Desktop: Close sidebar dan force enable scroll
                    closeSidebar();
                }
            }, 250);
        });

        // Page load handler - CRITICAL
        window.addEventListener('load', forceEnableScroll);
        
        // DOMContentLoaded handler - BACKUP
        document.addEventListener('DOMContentLoaded', forceEnableScroll);

        // Visibility change handler - when tab becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && window.innerWidth >= 1200) {
                forceEnableScroll();
            }
        });
        
        // Edit Pengelola Function
        function editPengelola(data) {
            document.getElementById('edit_id').value = data.id_pengelola;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_telepon').value = data.no_telepon;
            document.getElementById('edit_status').value = data.status;
            
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }
        
        // Reset Password Function
        function resetPassword(id, nama) {
            document.getElementById('reset_id').value = id;
            document.getElementById('reset_nama').textContent = nama;
            
            new bootstrap.Modal(document.getElementById('modalResetPassword')).show();
        }
        
        // Hapus Pengelola Function
        function hapusPengelola(id, nama) {
            if (confirm('Apakah Anda yakin ingin menghapus pengelola "' + nama + '"?\n\nPerhatian: Pengelola tidak dapat dihapus jika masih memiliki dapur terdaftar.')) {
                window.location.href = 'pengelola.php?hapus=' + id;
            }
        }
        
        // Auto dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
```