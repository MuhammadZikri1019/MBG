<?php
// pengelola/menu.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];



// Proses Tambah Menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $nama_menu = escape($_POST['nama_menu']);
    $jumlah_porsi = escape($_POST['jumlah_porsi']);
    $deskripsi = escape($_POST['deskripsi']);
    $tanggal_menu = escape($_POST['tanggal_menu']);
    $status_pengantaran = escape($_POST['status_pengantaran']);
    
    $foto_menu = null;
    if (isset($_FILES['foto_menu']) && $_FILES['foto_menu']['error'] == 0) {
        $upload = uploadFoto($_FILES['foto_menu']);
        if ($upload['status']) {
            $foto_menu = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    }
    
    if (!isset($error)) {
        // Start Transaction
        mysqli_begin_transaction($conn);
        
        try {
            $query = "INSERT INTO tbl_menu (id_pengelola, nama_menu, jumlah_porsi, deskripsi, foto_menu, tanggal_menu, status_pengantaran) 
                      VALUES ('$id_pengelola', '$nama_menu', '$jumlah_porsi', '$deskripsi', '$foto_menu', '$tanggal_menu', '$status_pengantaran')";
            
            if (!mysqli_query($conn, $query)) {
                throw new Exception("Gagal menambahkan menu: " . mysqli_error($conn));
            }
            
            $id_menu_baru = mysqli_insert_id($conn);
            
            // Process Recipe
            if (isset($_POST['bahan_id']) && is_array($_POST['bahan_id'])) {
                $bahan_ids = $_POST['bahan_id'];
                $bahan_jumlahs = $_POST['bahan_jumlah'];
                $bahan_satuans = $_POST['bahan_satuan'];
                
                for ($i = 0; $i < count($bahan_ids); $i++) {
                    if (!empty($bahan_ids[$i])) {
                        $b_id = escape($bahan_ids[$i]);
                        $b_jml = escape($bahan_jumlahs[$i]);
                        $b_sat = escape($bahan_satuans[$i]);
                        
                        // Insert to tbl_resep_menu
                        $q_resep = "INSERT INTO tbl_resep_menu (id_menu, id_bahan, jumlah_bahan, satuan) 
                                    VALUES ('$id_menu_baru', '$b_id', '$b_jml', '$b_sat')";
                        if (!mysqli_query($conn, $q_resep)) {
                            throw new Exception("Gagal menambahkan resep: " . mysqli_error($conn));
                        }
                        
                        // Stock Reduction Logic (FIFO/LIFO not strictly applied here, just reducing total stock)
                        // Get last stock
                        $q_stok = "SELECT stok_sesudah FROM tbl_riwayat_stok WHERE id_bahan = '$b_id' ORDER BY id_riwayat DESC LIMIT 1";
                        $res_stok = mysqli_query($conn, $q_stok);
                        $stok_sebelum = 0;
                        if (mysqli_num_rows($res_stok) > 0) {
                            $row_stok = mysqli_fetch_assoc($res_stok);
                            $stok_sebelum = $row_stok['stok_sesudah'];
                        }
                        
                        $stok_sesudah = $stok_sebelum - $b_jml;
                        
                        // Insert to tbl_riwayat_stok
                        $q_riwayat = "INSERT INTO tbl_riwayat_stok (id_bahan, tipe_transaksi, jumlah_stok, stok_sebelum, stok_sesudah, satuan, tanggal_transaksi, keterangan) 
                                      VALUES ('$b_id', 'keluar', '$b_jml', '$stok_sebelum', '$stok_sesudah', '$b_sat', CURDATE(), 'Penggunaan untuk Menu: $nama_menu')";
                        if (!mysqli_query($conn, $q_riwayat)) {
                            throw new Exception("Gagal update stok: " . mysqli_error($conn));
                        }
                    }
                }
            }
            
            mysqli_commit($conn);
            $success = "Menu dan resep berhasil ditambahkan, stok telah dikurangi!";
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

// Proses Update Menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id_menu = escape($_POST['id_menu']);
    $nama_menu = escape($_POST['nama_menu']);
    $jumlah_porsi = escape($_POST['jumlah_porsi']);
    $deskripsi = escape($_POST['deskripsi']);
    $tanggal_menu = escape($_POST['tanggal_menu']);
    $status_pengantaran = escape($_POST['status_pengantaran']);
    
    // Cek kepemilikan menu
    $check_query = "SELECT * FROM tbl_menu WHERE id_menu = '$id_menu' AND id_pengelola = '$id_pengelola'";
    $result_check = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result_check) > 0) {
        $current_data = mysqli_fetch_assoc($result_check);
        $foto_menu = $current_data['foto_menu'];
        
        // Handle upload foto baru jika ada
        if (isset($_FILES['foto_menu']) && $_FILES['foto_menu']['error'] == 0) {
            $upload = uploadFoto($_FILES['foto_menu']);
            if ($upload['status']) {
                // Hapus foto lama jika ada
                if ($foto_menu && file_exists("../assets/img/menu/" . $foto_menu)) {
                    unlink("../assets/img/menu/" . $foto_menu);
                }
                $foto_menu = $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }
        
        if (!isset($error)) {
            $query = "UPDATE tbl_menu SET 
                      nama_menu = '$nama_menu',
                      jumlah_porsi = '$jumlah_porsi',
                      deskripsi = '$deskripsi',
                      foto_menu = '$foto_menu',
                      tanggal_menu = '$tanggal_menu',
                      status_pengantaran = '$status_pengantaran'
                      WHERE id_menu = '$id_menu' AND id_pengelola = '$id_pengelola'";
            
            if (mysqli_query($conn, $query)) {
                $success = "Menu berhasil diupdate!";
            } else {
                $error = "Gagal mengupdate menu: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "Anda tidak memiliki akses untuk mengedit menu ini.";
    }
}

// Proses Hapus Menu
if (isset($_GET['delete'])) {
    $id_menu = escape($_GET['delete']);
    
    // Cek kepemilikan
    $check_query = "SELECT foto_menu FROM tbl_menu WHERE id_menu = '$id_menu' AND id_pengelola = '$id_pengelola'";
    $result_check = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result_check) > 0) {
        $data = mysqli_fetch_assoc($result_check);
        
        // Hapus foto jika ada
        if ($data['foto_menu'] && file_exists("../assets/img/menu/" . $data['foto_menu'])) {
            unlink("../assets/img/menu/" . $data['foto_menu']);
        }
        
        $query = "DELETE FROM tbl_menu WHERE id_menu = '$id_menu' AND id_pengelola = '$id_pengelola'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Menu berhasil dihapus!";
        } else {
            $error = "Gagal menghapus menu: " . mysqli_error($conn);
        }
    } else {
        $error = "Anda tidak memiliki akses untuk menghapus menu ini.";
    }
}

// Ambil data menu milik pengelola
$query_menu = "SELECT * FROM tbl_menu WHERE id_pengelola = '$id_pengelola' ORDER BY nama_menu ASC";
$result_menu = mysqli_query($conn, $query_menu);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <style>
        .menu-img-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .menu-img-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
            display: none;
            border: 2px dashed #ddd;
        }
        .badge-difficulty {
            font-size: 11px;
            padding: 5px 10px;
            border-radius: 15px;
        }
        .diff-mudah { background-color: #d1e7dd; color: #0f5132; }
        .diff-sedang { background-color: #fff3cd; color: #664d03; }
        .diff-sulit { background-color: #f8d7da; color: #842029; }
    </style>
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
            <a href="karyawan.php"><i class="bi bi-people"></i><span>Karyawan</span></a>
            <a href="absensi.php"><i class="bi bi-calendar-check"></i><span>Absensi Karyawan</span></a>

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
                <h4 class="mb-0">Katalog Menu</h4>
                <small class="text-muted">Kelola daftar menu makanan dan minuman</small>
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

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Tambah Menu
            </button>
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari menu...">
            </div>
        </div>

        <!-- Menu Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-dapur" id="menuTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 10%;">Foto</th>
                            <th style="width: 15%;">Nama Menu</th>
                            <th style="width: 10%;" class="text-center">Porsi</th>
                            <th style="width: 25%;">Deskripsi</th>
                            <th style="width: 10%;">Tanggal</th>
                            <th style="width: 15%;" class="text-center">Status Pengantaran</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="menuTableBody">
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result_menu) > 0):
                            while ($menu = mysqli_fetch_assoc($result_menu)): 
                        ?>
                        <tr class="menu-row" data-name="<?= strtolower($menu['nama_menu']) ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <?php if ($menu['foto_menu']): ?>
                                    <img src="../assets/img/menu/<?= $menu['foto_menu'] ?>" alt="<?= $menu['nama_menu'] ?>" class="menu-img-thumbnail">
                                <?php else: ?>
                                    <div class="menu-img-thumbnail d-flex align-items-center justify-content-center bg-light text-muted">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?= $menu['nama_menu'] ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?= $menu['jumlah_porsi'] ?></span>
                            </td>
                            <td style="white-space: pre-wrap;"><?= $menu['deskripsi'] ?></td>
                            <td><?= date('d/m/Y', strtotime($menu['tanggal_menu'])) ?></td>
                            <td class="text-center">
                                <?php
                                $status_class = '';
                                $status_label = '';
                                switch($menu['status_pengantaran']) {
                                    case 'belum_diantar': $status_class = 'bg-secondary'; $status_label = 'Belum Diantar'; break;
                                    case 'proses': $status_class = 'bg-warning text-dark'; $status_label = 'Proses Pengantaran'; break;
                                    case 'selesai': $status_class = 'bg-success'; $status_label = 'Selesai'; break;
                                }
                                ?>
                                <span class="badge rounded-pill <?= $status_class ?>">
                                    <?= $status_label ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="resep_menu.php?id=<?= $menu['id_menu'] ?>" class="btn-action btn-info text-white me-1" title="Detail Resep">
                                        <i class="bi bi-list-check"></i>
                                    </a>
                                    <button class="btn-action btn-edit" onclick='editMenu(<?= json_encode($menu) ?>)' title="Edit Menu">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteMenu(<?= $menu['id_menu'] ?>, '<?= addslashes($menu['nama_menu']) ?>')" title="Hapus Menu">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-journal-x" style="font-size: 48px;"></i>
                                    <p class="mt-2">Belum ada data menu.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State for Search -->
        <div class="text-center py-5" id="emptyState" style="display: none;">
            <i class="bi bi-search text-muted" style="font-size: 48px;"></i>
            <h5 class="mt-3 text-muted">Tidak ada data ditemukan</h5>
            <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
        </div>
    </div>

    <!-- Modal Tambah Menu -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Menu Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_menu" class="form-control" required placeholder="Contoh: Nasi Goreng Spesial">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jumlah Porsi</label>
                                        <input type="number" name="jumlah_porsi" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Menu <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_menu" class="form-control" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status Pengantaran</label>
                                        <select name="status_pengantaran" class="form-select">
                                            <option value="belum_diantar">Belum Diantar</option>
                                            <option value="proses">Proses Pengantaran</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat menu..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Foto Menu</label>
                                    <img id="preview_foto_tambah" class="menu-img-preview bg-light d-block">
                                    <input type="file" name="foto_menu" class="form-control" accept="image/*" onchange="previewImage(this, 'preview_foto_tambah')">
                                    <small class="text-muted">Format: JPG, PNG, WEBP. Max: 2MB</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recipe Section -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Resep & Bahan Baku (Otomatis Kurangi Stok)</h6>
                                <div id="recipe-container">
                                    <div class="row recipe-row mb-2">
                                        <div class="col-md-5">
                                            <select name="bahan_id[]" class="form-select" required>
                                                <option value="">Pilih Bahan Baku</option>
                                                <?php 
                                                // Ambil data bahan baku
                                                $query_bahan = "SELECT * FROM tbl_bahan_baku ORDER BY nama_bahan ASC";
                                                $result_bahan = mysqli_query($conn, $query_bahan);
                                                while ($bahan = mysqli_fetch_assoc($result_bahan)): 
                                                ?>
                                                    <option value="<?= $bahan['id_bahan'] ?>"><?= $bahan['nama_bahan'] ?> (<?= $bahan['satuan'] ?>)</option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="bahan_jumlah[]" class="form-control" placeholder="Jumlah" step="0.01" min="0.01" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="bahan_satuan[]" class="form-control" placeholder="Satuan" required>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm mt-1 remove-recipe" style="display:none;"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-recipe-row">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Bahan
                                </button>
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

    <!-- Modal Edit Menu -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Menu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_menu" id="edit_id_menu">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Nama Menu <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_menu" id="edit_nama_menu" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Jumlah Porsi</label>
                                        <input type="number" name="jumlah_porsi" id="edit_jumlah_porsi" class="form-control" min="0">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Menu <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_menu" id="edit_tanggal_menu" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status Pengantaran</label>
                                        <select name="status_pengantaran" id="edit_status_pengantaran" class="form-select">
                                            <option value="belum_diantar">Belum Diantar</option>
                                            <option value="proses">Proses Pengantaran</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Foto Menu</label>
                                    <img id="preview_foto_edit" class="menu-img-preview bg-light d-block">
                                    <input type="file" name="foto_menu" class="form-control" accept="image/*" onchange="previewImage(this, 'preview_foto_edit')">
                                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto</small>
                                </div>

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
        // Preview Image Function
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Search Functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.menu-row');
            const emptyState = document.getElementById('emptyState');
            const tableCard = document.querySelector('.table-card');
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
                tableCard.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                tableCard.style.display = 'block';
                emptyState.style.display = 'none';
            }
        });

        // Edit Menu Function
        function editMenu(menu) {
            document.getElementById('edit_id_menu').value = menu.id_menu;
            document.getElementById('edit_nama_menu').value = menu.nama_menu;
            document.getElementById('edit_jumlah_porsi').value = menu.jumlah_porsi;
            document.getElementById('edit_deskripsi').value = menu.deskripsi;
            document.getElementById('edit_tanggal_menu').value = menu.tanggal_menu;
            document.getElementById('edit_status_pengantaran').value = menu.status_pengantaran;
            
            const preview = document.getElementById('preview_foto_edit');
            if (menu.foto_menu) {
                preview.src = '../assets/img/menu/' + menu.foto_menu;
                preview.style.display = 'block';
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }

        // Delete Menu Function
        function deleteMenu(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus menu "${nama}"?\n\nData yang dihapus tidak dapat dikembalikan!`)) {
                window.location.href = `menu.php?delete=${id}`;
            }
        }

        // Dynamic Recipe Rows
        document.getElementById('add-recipe-row').addEventListener('click', function() {
            const container = document.getElementById('recipe-container');
            const firstRow = container.querySelector('.recipe-row');
            const newRow = firstRow.cloneNode(true);
            
            // Reset values
            newRow.querySelector('select').value = '';
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            
            // Show delete button
            newRow.querySelector('.remove-recipe').style.display = 'block';
            
            container.appendChild(newRow);
        });

        document.getElementById('recipe-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-recipe')) {
                const row = e.target.closest('.recipe-row');
                if (document.querySelectorAll('.recipe-row').length > 1) {
                    row.remove();
                }
            }
        });
    </script>
</body>
</html>
