<?php
// admin/laporan.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Get filter parameters
$tipe_laporan = isset($_GET['tipe']) ? $_GET['tipe'] : 'semua';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');
$id_dapur = isset($_GET['id_dapur']) ? $_GET['id_dapur'] : 'semua';

// Build query
$query = "SELECT l.*, 
          p.nama as nama_pengelola,
          d.nama_dapur,
          CASE 
            WHEN l.dibuat_oleh_tipe = 'super_admin' THEN sa.nama_lengkap
            WHEN l.dibuat_oleh_tipe = 'pengelola' THEN p.nama
          END as pembuat
          FROM tbl_laporan l
          LEFT JOIN tbl_pengelola_dapur p ON l.id_pengelola = p.id_pengelola
          LEFT JOIN tbl_dapur d ON l.id_dapur = d.id_dapur
          LEFT JOIN tbl_super_admin sa ON l.dibuat_oleh = sa.id_super_admin AND l.dibuat_oleh_tipe = 'super_admin'
          WHERE l.tanggal_mulai >= '$tanggal_mulai' 
          AND l.tanggal_akhir <= '$tanggal_akhir'";

if ($tipe_laporan != 'semua') {
    $query .= " AND l.tipe_laporan = '$tipe_laporan'";
}

if ($kategori != 'semua') {
    $query .= " AND l.kategori_laporan = '$kategori'";
}

if ($id_dapur != 'semua') {
    $query .= " AND l.id_dapur = $id_dapur";
}

$query .= " ORDER BY l.created_at DESC LIMIT 50";

$result_laporan = mysqli_query($conn, $query);

// Get statistics
$stats_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_laporan"))['total'];
$stats_bulan_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_laporan WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())"))['total'];
$stats_draft = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_laporan WHERE status_laporan = 'draft'"))['total'];
$stats_final = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_laporan WHERE status_laporan = 'final'"))['total'];

// Get dapur list for filter
$dapur_list = mysqli_query($conn, "SELECT id_dapur, nama_dapur FROM tbl_dapur ORDER BY nama_dapur ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem - MBG System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    
    <style>
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 3px 20px rgba(137, 207, 240, 0.12);
            margin-bottom: 25px;
            border: none !important;
        }
        
        .filter-card h5 {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-card h5 i {
            color: var(--baby-blue);
            font-size: 20px;
        }
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 3px 20px rgba(137, 207, 240, 0.12);
            border: none !important;
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
            padding: 15px 12px;
            vertical-align: middle;
            white-space: nowrap;
            font-size: 14px;
        }
        
        .table tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            font-size: 14px;
        }
        
        .badge-tipe {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 11px;
            white-space: nowrap;
        }
        
        .badge-kategori {
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
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .action-btn i {
            font-size: 14px;
        }
        
        .btn-baby-blue {
            background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-baby-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(137, 207, 240, 0.4);
            color: white;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #89cff0;
            box-shadow: 0 0 0 4px rgba(137, 207, 240, 0.1);
            outline: none;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: var(--text-light);
            font-size: 14px;
        }
        
        @media (max-width: 991px) {
            .filter-card {
                padding: 20px;
            }
            
            .content-card {
                padding: 20px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 12px 10px;
                font-size: 13px;
            }
        }
        
        @media (max-width: 767px) {
            .filter-card {
                padding: 15px;
            }
            
            .content-card {
                padding: 15px;
            }
            
            .table {
                font-size: 12px;
                min-width: 900px;
            }
            
            .badge-tipe,
            .badge-kategori {
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
            <a href="laporan-sistem.php" class="active">
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4 class="mb-0">Laporan Sistem</h4>
                <small class="text-muted">Monitor dan kelola laporan sistem</small>
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
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h3><?= $stats_total ?></h3>
                    <p>Total Laporan</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3><?= $stats_bulan_ini ?></h3>
                    <p>Laporan Bulan Ini</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-file-earmark-minus"></i>
                    </div>
                    <h3><?= $stats_draft ?></h3>
                    <p>Draft Laporan</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card info">
                    <div class="icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <h3><?= $stats_final ?></h3>
                    <p>Laporan Final</p>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <h5>
                <i class="bi bi-funnel"></i>
                Filter Laporan
            </h5>
            <form method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="<?= $tanggal_mulai ?>">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="form-control" value="<?= $tanggal_akhir ?>">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Tipe Laporan</label>
                        <select name="tipe" class="form-select">
                            <option value="semua">Semua Tipe</option>
                            <option value="harian" <?= $tipe_laporan == 'harian' ? 'selected' : '' ?>>Harian</option>
                            <option value="mingguan" <?= $tipe_laporan == 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
                            <option value="bulanan" <?= $tipe_laporan == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select">
                            <option value="semua">Semua Kategori</option>
                            <option value="produksi" <?= $kategori == 'produksi' ? 'selected' : '' ?>>Produksi</option>
                            <option value="keuangan" <?= $kategori == 'keuangan' ? 'selected' : '' ?>>Keuangan</option>
                            <option value="stok" <?= $kategori == 'stok' ? 'selected' : '' ?>>Stok</option>
                            <option value="karyawan" <?= $kategori == 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="keseluruhan" <?= $kategori == 'keseluruhan' ? 'selected' : '' ?>>Keseluruhan</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Dapur</label>
                        <select name="id_dapur" class="form-select">
                            <option value="semua">Semua Dapur</option>
                            <?php while($dapur = mysqli_fetch_assoc($dapur_list)): ?>
                            <option value="<?= $dapur['id_dapur'] ?>" <?= $id_dapur == $dapur['id_dapur'] ? 'selected' : '' ?>>
                                <?= $dapur['nama_dapur'] ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-baby-blue">
                                <i class="bi bi-search me-2"></i>Terapkan Filter
                            </button>
                            <a href="laporan-sistem.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Reset
                            </a>
                            <button type="button" class="btn btn-outline-primary ms-auto" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Cetak
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Content Card -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-bar-graph text-baby-blue me-2"></i>
                    Daftar Laporan
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-baby-blue btn-sm" data-bs-toggle="modal" data-bs-target="#modalGenerate">
                        <i class="bi bi-plus-lg me-2"></i>Generate Laporan
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive-custom">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Kode Laporan</th>
                            <th width="20%">Judul</th>
                            <th width="10%">Tipe</th>
                            <th width="10%">Kategori</th>
                            <th width="12%">Periode</th>
                            <th width="12%">Dapur</th>
                            <th width="8%">Status</th>
                            <th width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result_laporan) > 0):
                            $no = 1;
                            while ($laporan = mysqli_fetch_assoc($result_laporan)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <code style="font-size: 12px; background: #f8f9fa; padding: 4px 8px; border-radius: 4px;">
                                    <?= $laporan['kode_laporan'] ?>
                                </code>
                            </td>
                            <td>
                                <strong style="font-size: 13px;"><?= $laporan['judul_laporan'] ?></strong>
                            </td>
                            <td>
                                <span class="badge-tipe bg-info">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= ucfirst($laporan['tipe_laporan']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $kategori_icons = [
                                    'produksi' => 'bi-box-seam',
                                    'keuangan' => 'bi-cash-coin',
                                    'stok' => 'bi-stack',
                                    'karyawan' => 'bi-people',
                                    'keseluruhan' => 'bi-grid'
                                ];
                                $kategori_colors = [
                                    'produksi' => 'primary',
                                    'keuangan' => 'success',
                                    'stok' => 'warning',
                                    'karyawan' => 'info',
                                    'keseluruhan' => 'dark'
                                ];
                                $icon = $kategori_icons[$laporan['kategori_laporan']] ?? 'bi-file';
                                $color = $kategori_colors[$laporan['kategori_laporan']] ?? 'secondary';
                                ?>
                                <span class="badge-kategori bg-<?= $color ?>">
                                    <i class="<?= $icon ?> me-1"></i>
                                    <?= ucfirst($laporan['kategori_laporan']) ?>
                                </span>
                            </td>
                            <td>
                                <small style="font-size: 12px;">
                                    <?= date('d/m/Y', strtotime($laporan['tanggal_mulai'])) ?>
                                    <br>s/d<br>
                                    <?= date('d/m/Y', strtotime($laporan['tanggal_akhir'])) ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($laporan['nama_dapur']): ?>
                                    <small><?= $laporan['nama_dapur'] ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_badges = [
                                    'draft' => ['bg' => 'secondary', 'icon' => 'bi-pencil-square'],
                                    'final' => ['bg' => 'success', 'icon' => 'bi-check-circle'],
                                    'approved' => ['bg' => 'primary', 'icon' => 'bi-shield-check']
                                ];
                                $badge = $status_badges[$laporan['status_laporan']] ?? ['bg' => 'secondary', 'icon' => 'bi-question'];
                                ?>
                                <span class="badge-tipe bg-<?= $badge['bg'] ?>">
                                    <i class="<?= $badge['icon'] ?> me-1"></i>
                                    <?= ucfirst($laporan['status_laporan']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-action view" 
                                            onclick="viewLaporan(<?= $laporan['id_laporan'] ?>)"
                                            data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if ($laporan['file_pdf']): ?>
                                    <a href="../uploads/laporan/<?= $laporan['file_pdf'] ?>" 
                                       class="btn-action download" 
                                       target="_blank"
                                       data-bs-toggle="tooltip" title="Download PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn-action delete" 
                                            onclick="deleteLaporan(<?= $laporan['id_laporan'] ?>, '<?= addslashes($laporan['kode_laporan']) ?>')"
                                            data-bs-toggle="tooltip" title="Hapus">
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
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>Tidak ada laporan</h5>
                                    <p>Belum ada laporan yang sesuai dengan filter. Silakan generate laporan baru.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Generate Laporan -->
    <div class="modal fade" id="modalGenerate" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header" style="background: linear-gradient(135deg, #89cff0 0%, #5fb3d4 100%); color: white; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Generate Laporan Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        Pilih jenis laporan yang ingin di-generate
                    </p>
                    <div class="d-grid gap-2">
                        <a href="generate-laporan.php?jenis=produksi" class="btn btn-outline-primary">
                            <i class="bi bi-box-seam me-2"></i>Laporan Produksi
                        </a>
                        <a href="generate-laporan.php?jenis=keuangan" class="btn btn-outline-success">
                            <i class="bi bi-cash-coin me-2"></i>Laporan Keuangan
                        </a>
                        <a href="generate-laporan.php?jenis=stok" class="btn btn-outline-warning">
                            <i class="bi bi-stack me-2"></i>Laporan Stok
                        </a>
                        <a href="generate-laporan.php?jenis=karyawan" class="btn btn-outline-info">
                            <i class="bi bi-people me-2"></i>Laporan Karyawan
                        </a>
                        <a href="generate-laporan.php?jenis=keseluruhan" class="btn btn-outline-dark">
                            <i class="bi bi-grid me-2"></i>Laporan Keseluruhan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script src="../assets/js/auth.js"></script>
    
    <script>
        function viewLaporan(id) {
            window.location.href = 'detail-laporan.php?id=' + id;
        }
        
        function deleteLaporan(id, kode) {
            if (confirm('Apakah Anda yakin ingin menghapus laporan "' + kode + '"?')) {
                window.location.href = 'laporan-sistem.php?delete=' + id;
            }
        }
        
        console.log('✅ Laporan Sistem Page Loaded!');
    </script>
</body>
</html>