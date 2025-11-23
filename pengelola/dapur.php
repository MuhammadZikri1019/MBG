<?php
// pengelola/dapur.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];

// Proses Tambah Dapur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $nama_dapur = escape($_POST['nama_dapur']);
    $alamat = escape($_POST['alamat']);
    $kapasitas_produksi = escape($_POST['kapasitas_produksi']);
    $status = escape($_POST['status']);
    
    $query = "INSERT INTO tbl_dapur (id_pengelola, nama_dapur, alamat, kapasitas_produksi, status) 
              VALUES ('$id_pengelola', '$nama_dapur', '$alamat', '$kapasitas_produksi', '$status')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Dapur berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan dapur: " . mysqli_error($conn);
    }
}

// Proses Update Dapur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id_dapur = escape($_POST['id_dapur']);
    $nama_dapur = escape($_POST['nama_dapur']);
    $alamat = escape($_POST['alamat']);
    $kapasitas_produksi = escape($_POST['kapasitas_produksi']);
    $status = escape($_POST['status']);
    
    // Pastikan dapur milik pengelola yang login
    $check_query = "SELECT id_dapur FROM tbl_dapur WHERE id_dapur = '$id_dapur' AND id_pengelola = '$id_pengelola'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) > 0) {
        $query = "UPDATE tbl_dapur SET 
                  nama_dapur = '$nama_dapur',
                  alamat = '$alamat',
                  kapasitas_produksi = '$kapasitas_produksi',
                  status = '$status'
                  WHERE id_dapur = '$id_dapur' AND id_pengelola = '$id_pengelola'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Dapur berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate dapur: " . mysqli_error($conn);
        }
    } else {
        $error = "Anda tidak memiliki akses untuk mengedit dapur ini.";
    }
}

// Proses Hapus Dapur
if (isset($_GET['delete'])) {
    $id_dapur = escape($_GET['delete']);
    
    // Pastikan dapur milik pengelola yang login
    $check_query = "SELECT id_dapur FROM tbl_dapur WHERE id_dapur = '$id_dapur' AND id_pengelola = '$id_pengelola'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) > 0) {
        $query = "DELETE FROM tbl_dapur WHERE id_dapur = '$id_dapur' AND id_pengelola = '$id_pengelola'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Dapur berhasil dihapus!";
        } else {
            $error = "Gagal menghapus dapur: " . mysqli_error($conn);
        }
    } else {
        $error = "Anda tidak memiliki akses untuk menghapus dapur ini.";
    }
}

// Ambil data dapur milik pengelola
$query_dapur = "SELECT d.*, 
                (SELECT COUNT(*) FROM tbl_karyawan WHERE id_dapur = d.id_dapur AND status = 'aktif') as jumlah_karyawan
                FROM tbl_dapur d
                WHERE d.id_pengelola = '$id_pengelola'
                ORDER BY d.created_at DESC";
$result_dapur = mysqli_query($conn, $query_dapur);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dapur - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <style>
        /* Custom styles specific to this page if needed */
        .table-dapur th {
            font-weight: 600;
            color: var(--text-dark);
            background-color: #f8f9fa;
        }
        .dapur-name {
            font-weight: 600;
            color: var(--text-dark);
        }
        .alamat-text {
            font-size: 0.9rem;
            color: var(--text-light);
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        .btn-action {
            border: none;
            background: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .btn-edit { color: var(--warning); }
        .btn-edit:hover { background-color: #fff3cd; }
        .btn-delete { color: var(--danger); }
        .btn-delete:hover { background-color: #f8d7da; }
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
            <a href="dapur.php" class="active">
                <i class="bi bi-house"></i>
                <span>Kelola Dapur</span>
            </a>
            <a href="karyawan.php">
                <i class="bi bi-people"></i>
                <span>Karyawan</span>
            </a>
            <a href="absensi.php"><i class="bi bi-calendar-check"></i><span>Absensi Karyawan</span></a>

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
                <h4 class="mb-0">Kelola Dapur</h4>
                <small class="text-muted">Manajemen data dapur produksi</small>
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
                <i class="bi bi-plus-circle me-2"></i>Tambah Dapur
            </button>
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari dapur...">
            </div>
        </div>

        <!-- Dapur Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-dapur" id="dapurTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 25%;">Nama Dapur</th>
                            <th style="width: 30%;">Alamat</th>
                            <th style="width: 10%;" class="text-center">Karyawan</th>
                            <th style="width: 10%;" class="text-center">Kapasitas</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 10%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dapurTableBody">
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result_dapur) > 0):
                            while ($dapur = mysqli_fetch_assoc($result_dapur)): 
                        ?>
                        <tr class="dapur-row" data-name="<?= strtolower($dapur['nama_dapur']) ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <div class="dapur-name"><?= $dapur['nama_dapur'] ?></div>
                            </td>
                            <td>
                                <div class="alamat-text" title="<?= $dapur['alamat'] ?>"><?= $dapur['alamat'] ?></div>
                            </td>
                            <td class="text-center">
                                <strong><?= $dapur['jumlah_karyawan'] ?></strong> orang
                            </td>
                            <td class="text-center">
                                <?= $dapur['kapasitas_produksi'] ? $dapur['kapasitas_produksi'] . '/hari' : '-' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge-status <?= $dapur['status'] == 'aktif' ? 'bg-success' : ($dapur['status'] == 'maintenance' ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= ucfirst($dapur['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" onclick='editDapur(<?= json_encode($dapur) ?>)' title="Edit Dapur">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteDapur(<?= $dapur['id_dapur'] ?>, '<?= addslashes($dapur['nama_dapur']) ?>')" title="Hapus Dapur">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox" style="font-size: 48px;"></i>
                                    <p class="mt-2">Belum ada data dapur.</p>
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

    <!-- Modal Tambah Dapur -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Dapur Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Dapur <span class="text-danger">*</span></label>
                            <input type="text" name="nama_dapur" class="form-control" required 
                                   placeholder="Contoh: Dapur Cabang Selatan">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="3" required 
                                      placeholder="Masukkan alamat lengkap dapur"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kapasitas Produksi</label>
                                <input type="number" name="kapasitas_produksi" class="form-control" 
                                       placeholder="Per hari" min="0">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
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

    <!-- Modal Edit Dapur -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Dapur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_dapur" id="edit_id_dapur">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Dapur <span class="text-danger">*</span></label>
                            <input type="text" name="nama_dapur" id="edit_nama_dapur" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kapasitas Produksi</label>
                                <input type="number" name="kapasitas_produksi" id="edit_kapasitas_produksi" class="form-control" min="0">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
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
        // Search Functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.dapur-row');
            const emptyState = document.getElementById('emptyState');
            const tableCard = document.querySelector('.table-card');
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const dapurName = row.getAttribute('data-name');
                const rowText = row.textContent.toLowerCase();
                
                if (dapurName.includes(searchValue) || rowText.includes(searchValue)) {
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

        // Edit Dapur Function
        function editDapur(dapur) {
            document.getElementById('edit_id_dapur').value = dapur.id_dapur;
            document.getElementById('edit_nama_dapur').value = dapur.nama_dapur;
            document.getElementById('edit_alamat').value = dapur.alamat;
            document.getElementById('edit_kapasitas_produksi').value = dapur.kapasitas_produksi || '';
            document.getElementById('edit_status').value = dapur.status;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }

        // Delete Dapur Function
        function deleteDapur(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus dapur "${nama}"?\n\nData karyawan yang terkait mungkin akan terpengaruh!`)) {
                window.location.href = `dapur.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>
