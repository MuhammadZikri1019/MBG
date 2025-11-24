<?php
// admin/dapur.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Proses Tambah Dapur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id_pengelola = escape($_POST['id_pengelola']);
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
    $id_pengelola = escape($_POST['id_pengelola']);
    $nama_dapur = escape($_POST['nama_dapur']);
    $alamat = escape($_POST['alamat']);
    $kapasitas_produksi = escape($_POST['kapasitas_produksi']);
    $status = escape($_POST['status']);
    
    $query = "UPDATE tbl_dapur SET 
              id_pengelola = '$id_pengelola',
              nama_dapur = '$nama_dapur',
              alamat = '$alamat',
              kapasitas_produksi = '$kapasitas_produksi',
              status = '$status'
              WHERE id_dapur = '$id_dapur'";
    
    if (mysqli_query($conn, $query)) {
        $success = "Dapur berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate dapur: " . mysqli_error($conn);
    }
}

// Proses Hapus Dapur
if (isset($_GET['delete'])) {
    $id_dapur = escape($_GET['delete']);
    $query = "DELETE FROM tbl_dapur WHERE id_dapur = '$id_dapur'";
    
    if (mysqli_query($conn, $query)) {
        $success = "Dapur berhasil dihapus!";
    } else {
        $error = "Gagal menghapus dapur: " . mysqli_error($conn);
    }
}

// Ambil data dapur dengan join pengelola dan hitung jumlah karyawan
$query_dapur = "SELECT d.*, p.nama as nama_pengelola, p.email as email_pengelola,
                (SELECT COUNT(*) FROM tbl_karyawan WHERE id_dapur = d.id_dapur AND status = 'aktif') as jumlah_karyawan
                FROM tbl_dapur d
                LEFT JOIN tbl_pengelola_dapur p ON d.id_pengelola = p.id_pengelola
                ORDER BY d.created_at DESC";
$result_dapur = mysqli_query($conn, $query_dapur);

// Ambil data pengelola untuk dropdown
$query_pengelola = "SELECT * FROM tbl_pengelola_dapur WHERE status = 'aktif' ORDER BY nama ASC";
$result_pengelola = mysqli_query($conn, $query_pengelola);
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
            <a href="dapur.php" class="active">
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
            <a href="log-aktivitas.php">
                <i class="bi bi-clock-history"></i>
                <span>Log Aktivitas</span>
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
    <div class="main-content">
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

        <!-- Action Buttons -->
        <div class="action-bar">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle"></i>
                Tambah Dapur
            </button>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Cari dapur...">
            </div>
        </div>

        <!-- Dapur Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="table-dapur" id="dapurTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">Nama Dapur</th>
                            <th style="width: 18%;">Pengelola</th>
                            <th style="width: 25%;">Alamat</th>
                            <th style="width: 10%;" class="text-center">Karyawan</th>
                            <th style="width: 10%;" class="text-center">Kapasitas</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dapurTableBody">
                        <?php 
                        $no = 1;
                        mysqli_data_seek($result_dapur, 0);
                        while ($dapur = mysqli_fetch_assoc($result_dapur)): 
                        ?>
                        <tr class="dapur-row" data-name="<?= strtolower($dapur['nama_dapur']) ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <div class="dapur-name"><?= $dapur['nama_dapur'] ?></div>
                            </td>
                            <td>
                                <div class="pengelola-info">
                                    <span class="pengelola-name"><?= $dapur['nama_pengelola'] ?? '-' ?></span>
                                    <span class="pengelola-email"><?= $dapur['email_pengelola'] ?? '-' ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="alamat-text"><?= $dapur['alamat'] ?></div>
                            </td>
                            <td class="text-center">
                                <strong><?= $dapur['jumlah_karyawan'] ?></strong> orang
                            </td>
                            <td class="text-center">
                                <?= $dapur['kapasitas_produksi'] ? $dapur['kapasitas_produksi'] . '/hari' : '-' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $dapur['status'] ?>">
                                    <?= ucfirst($dapur['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn-action edit" 
                                            onclick='editDapur(<?= json_encode($dapur) ?>)'
                                            data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn-action delete" 
                                            onclick="deleteDapur(<?= $dapur['id_dapur'] ?>, '<?= $dapur['nama_dapur'] ?>')"
                                            data-bs-toggle="tooltip" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($result_dapur) == 0): ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 60px 20px;">
                                <div class="empty-state-inline">
                                    <i class="bi bi-inbox" style="font-size: 60px; color: var(--baby-blue-light);"></i>
                                    <h5 style="margin: 15px 0 5px; color: var(--text-dark);">Belum ada data dapur</h5>
                                    <p style="color: var(--text-light); margin: 0;">Klik tombol "Tambah Dapur" untuk menambahkan data baru</p>
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
            <h5>Tidak ada data ditemukan</h5>
            <p>Coba gunakan kata kunci yang berbeda</p>
        </div>
    </div>

    <!-- Modal Tambah Dapur -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i>
                        Tambah Dapur Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pengelola Dapur <span class="text-danger">*</span></label>
                                <select name="id_pengelola" class="form-select" required>
                                    <option value="">Pilih Pengelola</option>
                                    <?php 
                                    mysqli_data_seek($result_pengelola, 0);
                                    while ($pengelola = mysqli_fetch_assoc($result_pengelola)): 
                                    ?>
                                        <option value="<?= $pengelola['id_pengelola'] ?>">
                                            <?= $pengelola['nama'] ?> (<?= $pengelola['email'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Dapur <span class="text-danger">*</span></label>
                                <input type="text" name="nama_dapur" class="form-control" required 
                                       placeholder="Contoh: Dapur Pusat Jakarta">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="3" required 
                                      placeholder="Masukkan alamat lengkap dapur"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kapasitas Produksi (per hari)</label>
                                <input type="number" name="kapasitas_produksi" class="form-control" 
                                       placeholder="Contoh: 500" min="0">
                                <small class="text-muted">Kosongkan jika belum diatur</small>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Dapur -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit Dapur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_dapur" id="edit_id_dapur">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pengelola Dapur <span class="text-danger">*</span></label>
                                <select name="id_pengelola" id="edit_id_pengelola" class="form-select" required>
                                    <option value="">Pilih Pengelola</option>
                                    <?php 
                                    mysqli_data_seek($result_pengelola, 0);
                                    while ($pengelola = mysqli_fetch_assoc($result_pengelola)): 
                                    ?>
                                        <option value="<?= $pengelola['id_pengelola'] ?>">
                                            <?= $pengelola['nama'] ?> (<?= $pengelola['email'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Dapur <span class="text-danger">*</span></label>
                                <input type="text" name="nama_dapur" id="edit_nama_dapur" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kapasitas Produksi (per hari)</label>
                                <input type="number" name="kapasitas_produksi" id="edit_kapasitas_produksi" class="form-control" min="0">
                                <small class="text-muted">Kosongkan jika belum diatur</small>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Update
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
        // Search Functionality for Table
        // ============================================

        // ============================================
        // Search Functionality for Table
        // ============================================
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.dapur-row');
            const emptyState = document.getElementById('emptyState');
            const tableContainer = document.querySelector('.table-container');
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
            
            // Show/hide empty state
            if (visibleCount === 0) {
                tableContainer.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                tableContainer.style.display = 'block';
                emptyState.style.display = 'none';
            }
        });

        // ============================================
        // Edit Dapur Function
        // ============================================
        function editDapur(dapur) {
            // Set values to form
            document.getElementById('edit_id_dapur').value = dapur.id_dapur;
            document.getElementById('edit_id_pengelola').value = dapur.id_pengelola;
            document.getElementById('edit_nama_dapur').value = dapur.nama_dapur;
            document.getElementById('edit_alamat').value = dapur.alamat;
            document.getElementById('edit_kapasitas_produksi').value = dapur.kapasitas_produksi || '';
            document.getElementById('edit_status').value = dapur.status;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }

        // ============================================
        // Delete Dapur Function
        // ============================================
        function deleteDapur(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus dapur "${nama}"?\n\nPeringatan: Semua data karyawan yang terkait dengan dapur ini akan terpengaruh!`)) {
                window.location.href = `dapur.php?delete=${id}`;
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

        // Reset Form When Modal Hidden
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', function() {
                const forms = this.querySelectorAll('form');
                forms.forEach(form => {
                    form.reset();
                    form.classList.remove('was-validated');
                });
            });
        });

        // Loading Animation on Submit
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && this.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                }
            });
        });

        console.log('🏠 Kelola Dapur - Loaded Successfully!');
    </script>
</body>
</html>