<?php
// pengelola/hari_libur.php
require_once '../koneksi.php';
checkRole(['pengelola']);

// Proses Tambah Hari Libur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $tanggal = escape($_POST['tanggal']);
        $keterangan = escape($_POST['keterangan']);
        
        // Cek apakah tanggal sudah ada
        $check = mysqli_query($conn, "SELECT * FROM tbl_hari_libur WHERE tanggal = '$tanggal'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Tanggal tersebut sudah ada dalam daftar libur!";
        } else {
            $query = "INSERT INTO tbl_hari_libur (tanggal, keterangan) VALUES ('$tanggal', '$keterangan')";
            if (mysqli_query($conn, $query)) {
                $success = "Hari libur berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan hari libur: " . mysqli_error($conn);
            }
        }
    } elseif ($_POST['action'] == 'delete') {
        $id_libur = escape($_POST['id_libur']);
        $query = "DELETE FROM tbl_hari_libur WHERE id_libur = $id_libur";
        if (mysqli_query($conn, $query)) {
            $success = "Hari libur berhasil dihapus!";
        } else {
            $error = "Gagal menghapus hari libur: " . mysqli_error($conn);
        }
    }
}

// Ambil data hari libur (urutkan dari yang terbaru)
$query = "SELECT * FROM tbl_hari_libur ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hari Libur - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
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
            <small>Pengelola Panel</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php">
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
            <a href="hari_libur.php" class="active">
                <i class="bi bi-calendar-event"></i>
                <span>Hari Libur</span>
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
                <h4 class="mb-0">Kelola Hari Libur</h4>
                <small class="text-muted">Atur jadwal libur karyawan</small>
            </div>
            <div class="user-profile">
                <div>
                    <div class="fw-bold text-end"><?= $_SESSION['user_name'] ?></div>
                    <small class="text-muted">Pengelola Dapur</small>
                </div>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Form Tambah -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Tambah Hari Libur</h5>
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Libur Nasional, Cuti Bersama" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-2"></i>Simpan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Daftar Hari Libur -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Daftar Hari Libur</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?= formatTanggal($row['tanggal']) ?></td>
                                                <td><?= $row['keterangan'] ?></td>
                                                <td class="text-end">
                                                    <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus hari libur ini?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id_libur" value="<?= $row['id_libur'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                                                Belum ada data hari libur
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
</body>
</html>
