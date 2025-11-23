<?php
// pengelola/resep_menu.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: menu.php");
    exit;
}

$id_menu = escape($_GET['id']);

// Ambil data menu dan validasi kepemilikan
$query_menu = "SELECT * FROM tbl_menu WHERE id_menu = '$id_menu' AND id_pengelola = '$id_pengelola'";
$result_menu = mysqli_query($conn, $query_menu);

if (mysqli_num_rows($result_menu) == 0) {
    echo "<script>alert('Menu tidak ditemukan atau Anda tidak memiliki akses!'); window.location='menu.php';</script>";
    exit;
}

$menu = mysqli_fetch_assoc($result_menu);

// Proses Tambah Bahan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id_bahan = escape($_POST['id_bahan']);
    $jumlah_bahan = escape($_POST['jumlah_bahan']);
    $satuan = escape($_POST['satuan']);
    
    // Cek apakah bahan sudah ada di resep ini
    $check_exist = "SELECT * FROM tbl_resep_menu WHERE id_menu = '$id_menu' AND id_bahan = '$id_bahan'";
    if (mysqli_num_rows(mysqli_query($conn, $check_exist)) > 0) {
        $error = "Bahan baku ini sudah ada dalam resep!";
    } else {
        $query = "INSERT INTO tbl_resep_menu (id_menu, id_bahan, jumlah_bahan, satuan) 
                  VALUES ('$id_menu', '$id_bahan', '$jumlah_bahan', '$satuan')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Bahan baku berhasil ditambahkan ke resep!";
        } else {
            $error = "Gagal menambahkan bahan: " . mysqli_error($conn);
        }
    }
}

// Proses Update Bahan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id_resep = escape($_POST['id_resep']);
    $jumlah_bahan = escape($_POST['jumlah_bahan']);
    $satuan = escape($_POST['satuan']);
    
    $query = "UPDATE tbl_resep_menu SET jumlah_bahan = '$jumlah_bahan', satuan = '$satuan' WHERE id_resep = '$id_resep'";
    
    if (mysqli_query($conn, $query)) {
        $success = "Resep berhasil diupdate!";
    } else {
        $error = "Gagal update resep: " . mysqli_error($conn);
    }
}

// Proses Hapus Bahan
if (isset($_GET['delete'])) {
    $id_resep = escape($_GET['delete']);
    $query = "DELETE FROM tbl_resep_menu WHERE id_resep = '$id_resep'";
    
    if (mysqli_query($conn, $query)) {
        $success = "Bahan berhasil dihapus dari resep!";
    } else {
        $error = "Gagal menghapus bahan: " . mysqli_error($conn);
    }
}

// Ambil data resep
$query_resep = "SELECT r.*, b.nama_bahan 
                FROM tbl_resep_menu r 
                JOIN tbl_bahan_baku b ON r.id_bahan = b.id_bahan 
                WHERE r.id_menu = '$id_menu'";
$result_resep = mysqli_query($conn, $query_resep);

// Ambil daftar bahan baku untuk dropdown
$query_bahan = "SELECT * FROM tbl_bahan_baku ORDER BY nama_bahan ASC";
$result_bahan = mysqli_query($conn, $query_bahan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Resep - <?= $menu['nama_menu'] ?></title>
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

            <a href="menu.php" class="active">
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
                <h4 class="mb-0">Detail Resep</h4>
                <small class="text-muted">Kelola bahan baku untuk menu ini</small>
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

        <!-- Menu Info Card -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <?php if ($menu['foto_menu']): ?>
                        <img src="../assets/img/menu/<?= $menu['foto_menu'] ?>" alt="<?= $menu['nama_menu'] ?>" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded me-3 bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-image text-muted h3"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h4 class="mb-1"><?= $menu['nama_menu'] ?></h4>
                        <p class="text-muted mb-0"><?= $menu['deskripsi'] ?></p>
                    </div>
                    <div class="ms-auto">
                        <a href="menu.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Tambah Bahan
            </button>
        </div>

        <!-- Resep Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-dapur">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 40%;">Nama Bahan Baku</th>
                            <th style="width: 20%;">Jumlah</th>
                            <th style="width: 20%;">Satuan</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result_resep) > 0):
                            while ($resep = mysqli_fetch_assoc($result_resep)): 
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= $resep['nama_bahan'] ?></td>
                            <td><?= $resep['jumlah_bahan'] ?></td>
                            <td><?= $resep['satuan'] ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" onclick='editResep(<?= json_encode($resep) ?>)' title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteResep(<?= $resep['id_resep'] ?>, '<?= addslashes($resep['nama_bahan']) ?>')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-basket" style="font-size: 48px;"></i>
                                    <p class="mt-2">Belum ada bahan baku dalam resep ini.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Bahan -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Bahan Baku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Bahan Baku <span class="text-danger">*</span></label>
                            <select name="id_bahan" class="form-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                <?php 
                                mysqli_data_seek($result_bahan, 0);
                                while ($bahan = mysqli_fetch_assoc($result_bahan)): 
                                ?>
                                    <option value="<?= $bahan['id_bahan'] ?>"><?= $bahan['nama_bahan'] ?> (<?= $bahan['satuan'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_bahan" class="form-control" required min="0.1" step="0.1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input type="text" name="satuan" class="form-control" required placeholder="Contoh: kg, liter, pcs">
                            </div>
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

    <!-- Modal Edit Bahan -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Bahan Baku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_resep" id="edit_id_resep">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Bahan</label>
                            <input type="text" id="edit_nama_bahan" class="form-control" readonly disabled>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_bahan" id="edit_jumlah_bahan" class="form-control" required min="0.1" step="0.1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input type="text" name="satuan" id="edit_satuan" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script src="../assets/js/auth.js"></script>
    <script>
        function editResep(resep) {
            document.getElementById('edit_id_resep').value = resep.id_resep;
            document.getElementById('edit_nama_bahan').value = resep.nama_bahan;
            document.getElementById('edit_jumlah_bahan').value = resep.jumlah_bahan;
            document.getElementById('edit_satuan').value = resep.satuan;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }

        function deleteResep(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus "${nama}" dari resep ini?`)) {
                window.location.href = `resep_menu.php?id=<?= $id_menu ?>&delete=${id}`;
            }
        }
    </script>
</body>
</html>
