<?php
// karyawan/dokumentasi.php
require_once '../koneksi.php';
checkRole(['karyawan']);

$id_karyawan = $_SESSION['user_id'];
$success = null;
$error = null;

// Get user data for 'bagian'
$query_user = "SELECT * FROM tbl_karyawan WHERE id_karyawan = '$id_karyawan'";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);
$bagian_user = ucwords(str_replace('_', ' ', $user['bagian']));

// Handle Add Documentation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $tanggal_dokumentasi = escape($_POST['tanggal_dokumentasi']);
    $aktivitas = escape($_POST['aktivitas']);
    
    $foto_dokumentasi = null;
    if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] == 0) {
        $upload = uploadFoto($_FILES['foto_dokumentasi'], 'dokumentasi');
        if ($upload['status']) {
            $foto_dokumentasi = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    } else {
        $error = "Foto wajib diupload!";
    }
    
    if (!isset($error)) {
        // Format aktivitas: [Bagian] Deskripsi
        $aktivitas_formatted = "[$bagian_user] $aktivitas";
        
        $query = "INSERT INTO tbl_dokumentasi_karyawan (tanggal_dokumentasi, aktivitas, foto_dokumentasi) 
                  VALUES ('$tanggal_dokumentasi', '$aktivitas_formatted', '$foto_dokumentasi')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Dokumentasi berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan dokumentasi: " . mysqli_error($conn);
        }
    }
}

// Get dokumentasi data (only for this user's role/bagian or uploaded by them if we tracked user_id, but table structure only has aktivitas string. 
// So we filter by the formatted string part or just show all for context? 
// Let's show all for now as per "dokumentasi" nature, or maybe filter by date.
// Implementation plan said "Display list of documentation uploaded by the current employee".
// Since we don't store user_id in tbl_dokumentasi_karyawan, we'll filter by the [Bagian] tag we added.
$query_dok = "SELECT * FROM tbl_dokumentasi_karyawan 
              WHERE aktivitas LIKE '%[$bagian_user]%'
              ORDER BY tanggal_dokumentasi DESC, created_at DESC";
$result_dok = mysqli_query($conn, $query_dok);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi - MBG System</title>
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
            <h4>MBG System</h4><small>Karyawan Panel</small>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
            <a href="dokumentasi.php" class="active"><i class="bi bi-camera"></i><span>Dokumentasi</span></a>
            <a href="profil.php"><i class="bi bi-person-circle"></i><span>Profil</span></a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <div><h4 class="mb-0">Dokumentasi Pekerjaan</h4><small class="text-muted">Upload foto kegiatan harian Anda</small></div>
            <div class="user-profile">
                <a href="profil.php" class="text-decoration-none text-dark d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="fw-bold"><?= $_SESSION['user_name'] ?></div>
                        <small class="text-muted"><?= ucfirst($_SESSION['bagian']) ?></small>
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

        <!-- Add Button -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-1">Upload Dokumentasi Baru</h5>
                <p class="text-muted mb-3 small">Pastikan foto jelas dan sesuai dengan aktivitas pekerjaan.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-camera me-2"></i>Upload Foto
                </button>
            </div>
        </div>

        <!-- Documentation List -->
        <div class="row g-3">
            <?php if (mysqli_num_rows($result_dok) > 0): ?>
                <?php while ($dok = mysqli_fetch_assoc($result_dok)): 
                    $aktivitas_text = preg_replace('/\[.*?\]\s*/', '', $dok['aktivitas']);
                ?>
                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                        <div class="d-flex h-100">
                            <?php if ($dok['foto_dokumentasi']): ?>
                                <div style="width: 120px; min-width: 120px; background-image: url('../assets/img/dokumentasi/<?= $dok['foto_dokumentasi'] ?>'); background-size: cover; background-position: center;"></div>
                            <?php endif; ?>
                            <div class="p-3 flex-grow-1 d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($dok['tanggal_dokumentasi'])) ?>
                                    </span>
                                </div>
                                <p class="mb-0 fw-medium" style="font-size: 14px; line-height: 1.4;"><?= nl2br($aktivitas_text) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-images display-4 mb-3"></i>
                        <p>Belum ada dokumentasi yang diupload.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Dokumentasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_dokumentasi" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                            <textarea name="aktivitas" class="form-control" rows="3" placeholder="Jelaskan aktivitas yang difoto..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto <span class="text-danger">*</span></label>
                            <input type="file" name="foto_dokumentasi" class="form-control" accept="image/*" required>
                            <div class="form-text">Format: JPG, PNG, JPEG. Maks 2MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
</body>
</html>
