<?php
// pengelola/dokumentasi.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];
$success = null;
$error = null;

// Handle Add Documentation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $tanggal_dokumentasi = escape($_POST['tanggal_dokumentasi']);
    $bagian = escape($_POST['bagian']);
    $aktivitas = escape($_POST['aktivitas']);
    
    $foto_dokumentasi = null;
    if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] == 0) {
        $upload = uploadFoto($_FILES['foto_dokumentasi'], 'dokumentasi');
        if ($upload['status']) {
            $foto_dokumentasi = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    }
    
    if (!isset($error)) {
        $query = "INSERT INTO tbl_dokumentasi_karyawan (tanggal_dokumentasi, aktivitas, foto_dokumentasi) 
                  VALUES ('$tanggal_dokumentasi', CONCAT('[$bagian] ', '$aktivitas'), " . ($foto_dokumentasi ? "'$foto_dokumentasi'" : "NULL") . ")";
        
        if (mysqli_query($conn, $query)) {
            $success = "Dokumentasi berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan dokumentasi: " . mysqli_error($conn);
        }
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id_dokumentasi = escape($_POST['id_dokumentasi']);
    $query = "DELETE FROM tbl_dokumentasi_karyawan WHERE id_dokumentasi = '$id_dokumentasi'";
    
    if (mysqli_query($conn, $query)) {
        $success = "Dokumentasi berhasil dihapus!";
    } else {
        $error = "Gagal menghapus dokumentasi: " . mysqli_error($conn);
    }
}

// Get filter
$filter_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_bagian = isset($_GET['bagian']) ? $_GET['bagian'] : '';

// Get dokumentasi data
$query_dok = "SELECT d.* 
              FROM tbl_dokumentasi_karyawan d 
              WHERE 1=1";

if ($filter_date) {
    $query_dok .= " AND d.tanggal_dokumentasi = '$filter_date'";
}
if ($filter_bagian) {
    $query_dok .= " AND d.aktivitas LIKE '%[$filter_bagian]%'";
}

$query_dok .= " ORDER BY d.tanggal_dokumentasi DESC, d.created_at DESC";
$result_dok = mysqli_query($conn, $query_dok);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi Harian - MBG System</title>
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
            <a href="dokumentasi.php" class="active"><i class="bi bi-journal-text"></i><span>Dokumentasi</span></a>
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
            <div><h4 class="mb-0">Dokumentasi Harian Karyawan</h4><small class="text-muted">Catat aktivitas karyawan setiap hari</small></div>
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

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filter & Add Button -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-end">
                    <form method="GET" class="row g-3 flex-grow-1 me-3">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= $filter_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bagian</label>
                            <select name="bagian" class="form-select">
                                <option value="">Semua Bagian</option>
                                <option value="Tukang Masak" <?= $filter_bagian == 'Tukang Masak' ? 'selected' : '' ?>>Tukang Masak</option>
                                <option value="Tukang Cuci Piring" <?= $filter_bagian == 'Tukang Cuci Piring' ? 'selected' : '' ?>>Tukang Cuci Piring</option>
                                <option value="Pengantar Makanan" <?= $filter_bagian == 'Pengantar Makanan' ? 'selected' : '' ?>>Pengantar Makanan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 mt-4">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </form>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Dokumentasi
                    </button>
                </div>
            </div>
        </div>

        <!-- Documentation List -->
        <div class="row">
            <?php while ($dok = mysqli_fetch_assoc($result_dok)): 
                // Extract bagian from aktivitas
                preg_match('/\[(.*?)\]/', $dok['aktivitas'], $matches);
                $bagian = $matches[1] ?? 'Umum';
                $aktivitas_text = preg_replace('/\[.*?\]\s*/', '', $dok['aktivitas']);
            ?>
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="row g-0">
                        <?php if ($dok['foto_dokumentasi']): ?>
                            <div class="col-md-4">
                                <img src="../assets/img/dokumentasi/<?= $dok['foto_dokumentasi'] ?>" class="img-fluid rounded-start" alt="Dokumentasi" style="height: 100%; min-height: 200px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                        <?php else: ?>
                            <div class="col-12">
                        <?php endif; ?>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-primary me-2"><?= ucfirst($bagian) ?></span>
                                            <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($dok['tanggal_dokumentasi'])) ?></small>
                                        </div>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_dokumentasi" value="<?= $dok['id_dokumentasi'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                    <p class="mb-0"><?= nl2br($aktivitas_text) ?></p>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Dokumentasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Bagian / Aktivitas <span class="text-danger">*</span></label>
                            <input type="text" name="bagian" class="form-control" placeholder="Contoh: Tukang Masak, Tukang Cuci Piring, Pengantar Makanan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_dokumentasi" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                            <textarea name="aktivitas" class="form-control" rows="4" placeholder="Contoh: Sedang memasak nasi untuk 100 porsi" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Dokumentasi <span class="text-danger">*</span></label>
                            <input type="file" name="foto_dokumentasi" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
</body>
</html>
