<?php
// admin/karyawan.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Proses Tambah Karyawan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id_dapur = escape($_POST['id_dapur']);
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $password = escape($_POST['password']);
    $no_telepon = escape($_POST['no_telepon']);
    $alamat = escape($_POST['alamat']);
    $bagian = escape($_POST['bagian']);
    $status = escape($_POST['status']);
    
    // Check email duplicate
    $check_email = "SELECT * FROM tbl_karyawan WHERE email = '$email'";
    if (mysqli_num_rows(mysqli_query($conn, $check_email)) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $query = "INSERT INTO tbl_karyawan (id_dapur, nama, email, password, no_telepon, alamat, bagian, status) 
                  VALUES ('$id_dapur', '$nama', '$email', '$password', '$no_telepon', '$alamat', '$bagian', '$status')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Karyawan berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan karyawan: " . mysqli_error($conn);
        }
    }
}

// Proses Update Karyawan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id_karyawan = escape($_POST['id_karyawan']);
    $id_dapur = escape($_POST['id_dapur']);
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $password = escape($_POST['password']);
    $no_telepon = escape($_POST['no_telepon']);
    $alamat = escape($_POST['alamat']);
    $bagian = escape($_POST['bagian']);
    $status = escape($_POST['status']);
    
    // Check email duplicate (exclude current user)
    $check_email = "SELECT * FROM tbl_karyawan WHERE email = '$email' AND id_karyawan != '$id_karyawan'";
    if (mysqli_num_rows(mysqli_query($conn, $check_email)) > 0) {
        $error = "Email sudah digunakan karyawan lain!";
    } else {
        // Update with or without password change
        if (!empty($password)) {
            $query = "UPDATE tbl_karyawan SET 
                      id_dapur = '$id_dapur',
                      nama = '$nama',
                      email = '$email',
                      password = '$password',
                      no_telepon = '$no_telepon',
                      alamat = '$alamat',
                      bagian = '$bagian',
                      status = '$status'
                      WHERE id_karyawan = '$id_karyawan'";
        } else {
            $query = "UPDATE tbl_karyawan SET 
                      id_dapur = '$id_dapur',
                      nama = '$nama',
                      email = '$email',
                      no_telepon = '$no_telepon',
                      alamat = '$alamat',
                      bagian = '$bagian',
                      status = '$status'
                      WHERE id_karyawan = '$id_karyawan'";
        }
        
        if (mysqli_query($conn, $query)) {
            $success = "Data karyawan berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate karyawan: " . mysqli_error($conn);
        }
    }
}

// Proses Hapus Karyawan
if (isset($_GET['delete'])) {
    $id_karyawan = escape($_GET['delete']);
    $query = "DELETE FROM tbl_karyawan WHERE id_karyawan = '$id_karyawan'";
    
    if (mysqli_query($conn, $query)) {
        $success = "Karyawan berhasil dihapus!";
    } else {
        $error = "Gagal menghapus karyawan: " . mysqli_error($conn);
    }
}

// Ambil data karyawan dengan join dapur
$query_karyawan = "SELECT k.*, d.nama_dapur, d.alamat as alamat_dapur
                   FROM tbl_karyawan k
                   LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                   ORDER BY k.created_at DESC";
$result_karyawan = mysqli_query($conn, $query_karyawan);

// Ambil data dapur untuk dropdown
$query_dapur = "SELECT * FROM tbl_dapur WHERE status = 'aktif' ORDER BY nama_dapur ASC";
$result_dapur = mysqli_query($conn, $query_dapur);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan - MBG System</title>
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
            <a href="karyawan.php" class="active">
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
                <h4 class="mb-0">Kelola Karyawan</h4>
                <small class="text-muted">Manajemen data karyawan semua dapur</small>
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
                Tambah Karyawan
            </button>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Cari karyawan...">
            </div>
        </div>

        <!-- Karyawan Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="table-dapur" id="karyawanTable">
                    <thead>
                        <tr>
                            <th style="width: 4%;">No</th>
                            <th style="width: 15%;">Nama Lengkap</th>
                            <th style="width: 14%;">Email</th>
                            <th style="width: 10%;">No Telepon</th>
                            <th style="width: 16%;">Alamat</th>
                            <th style="width: 13%;">Dapur</th>
                            <th style="width: 10%;">Bagian</th>
                            <th style="width: 8%;" class="text-center">Status</th>
                            <th style="width: 10%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="karyawanTableBody">
                        <?php 
                        $no = 1;
                        mysqli_data_seek($result_karyawan, 0);
                        while ($karyawan = mysqli_fetch_assoc($result_karyawan)): 
                        ?>
                        <tr class="dapur-row" data-name="<?= strtolower($karyawan['nama']) ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <div class="dapur-name"><?= $karyawan['nama'] ?></div>
                            </td>
                            <td>
                                <small><?= $karyawan['email'] ?></small>
                            </td>
                            <td>
                                <small><?= $karyawan['no_telepon'] ?? '-' ?></small>
                            </td>
                            <td>
                                <div class="alamat-text" style="max-width: 200px;">
                                    <?= strlen($karyawan['alamat']) > 50 ? substr($karyawan['alamat'], 0, 50) . '...' : $karyawan['alamat'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="pengelola-info">
                                    <span class="pengelola-name"><?= $karyawan['nama_dapur'] ?? '-' ?></span>
                                    <?php if ($karyawan['alamat_dapur']): ?>
                                    <small class="pengelola-email" style="font-size: 11px;">
                                        <?= strlen($karyawan['alamat_dapur']) > 30 ? substr($karyawan['alamat_dapur'], 0, 30) . '...' : $karyawan['alamat_dapur'] ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: #e3f5fc; color: #0c5460; padding: 5px 10px; border-radius: 15px; font-size: 11px;">
                                    <?= ucfirst($karyawan['bagian']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $karyawan['status'] ?>">
                                    <?= ucfirst($karyawan['status']) ?>
                                </span>
                            </td>
                            <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn-action edit" 
                                                onclick='editKaryawan(<?= json_encode($karyawan) ?>)'
                                                data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn-action delete" 
                                                onclick="deleteKaryawan(<?= $karyawan['id_karyawan'] ?>, '<?= $karyawan['nama'] ?>')"
                                                data-bs-toggle="tooltip" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($result_karyawan) == 0): ?>
                        <tr>
                            <td colspan="9" class="text-center" style="padding: 60px 20px;">
                                <div class="empty-state-inline">
                                    <i class="bi bi-person-x" style="font-size: 60px; color: var(--baby-blue-light);"></i>
                                    <h5 style="margin: 15px 0 5px; color: var(--text-dark);">Belum ada data karyawan</h5>
                                    <p style="color: var(--text-light); margin: 0;">Klik tombol "Tambah Karyawan" untuk menambahkan data baru</p>
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

    <!-- Modal Tambah Karyawan -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i>
                        Tambah Karyawan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required 
                                       placeholder="Masukkan nama lengkap">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required 
                                       placeholder="contoh@email.com">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required 
                                       placeholder="Minimal 6 karakter" minlength="6">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No Telepon</label>
                                <input type="text" name="no_telepon" class="form-control" 
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" required 
                                      placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Dapur <span class="text-danger">*</span></label>
                                <select name="id_dapur" class="form-select" required>
                                    <option value="">Pilih Dapur</option>
                                    <?php 
                                    mysqli_data_seek($result_dapur, 0);
                                    while ($dapur = mysqli_fetch_assoc($result_dapur)): 
                                    ?>
                                        <option value="<?= $dapur['id_dapur'] ?>">
                                            <?= $dapur['nama_dapur'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bagian <span class="text-danger">*</span></label>
                                <select name="bagian" class="form-select" required>
                                    <option value="">Pilih Bagian</option>
                                    <option value="produksi">Produksi</option>
                                    <option value="packing">Packing</option>
                                    <option value="quality_control">Quality Control</option>
                                    <option value="gudang">Gudang</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                    <option value="cuti">Cuti</option>
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

    <!-- Modal Edit Karyawan -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit Data Karyawan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_karyawan" id="edit_id_karyawan">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="edit_nama" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                <input type="password" name="password" id="edit_password" class="form-control" 
                                       placeholder="Minimal 6 karakter" minlength="6">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No Telepon</label>
                                <input type="text" name="no_telepon" id="edit_no_telepon" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Dapur <span class="text-danger">*</span></label>
                                <select name="id_dapur" id="edit_id_dapur" class="form-select" required>
                                    <option value="">Pilih Dapur</option>
                                    <?php 
                                    mysqli_data_seek($result_dapur, 0);
                                    while ($dapur = mysqli_fetch_assoc($result_dapur)): 
                                    ?>
                                        <option value="<?= $dapur['id_dapur'] ?>">
                                            <?= $dapur['nama_dapur'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bagian <span class="text-danger">*</span></label>
                                <select name="bagian" id="edit_bagian" class="form-select" required>
                                    <option value="">Pilih Bagian</option>
                                    <option value="produksi">Produksi</option>
                                    <option value="packing">Packing</option>
                                    <option value="quality_control">Quality Control</option>
                                    <option value="gudang">Gudang</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                    <option value="cuti">Cuti</option>
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
        // Search Functionality for Table
        // ============================================
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.dapur-row');
            const emptyState = document.getElementById('emptyState');
            const tableContainer = document.querySelector('.table-container');
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
                tableContainer.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                tableContainer.style.display = 'block';
                emptyState.style.display = 'none';
            }
        });

        // ============================================
        // Edit Karyawan Function
        // ============================================
        function editKaryawan(karyawan) {
            document.getElementById('edit_id_karyawan').value = karyawan.id_karyawan;
            document.getElementById('edit_nama').value = karyawan.nama;
            document.getElementById('edit_email').value = karyawan.email;
            document.getElementById('edit_no_telepon').value = karyawan.no_telepon || '';
            document.getElementById('edit_alamat').value = karyawan.alamat;
            document.getElementById('edit_id_dapur').value = karyawan.id_dapur || '';
            document.getElementById('edit_bagian').value = karyawan.bagian;
            document.getElementById('edit_status').value = karyawan.status;
            document.getElementById('edit_password').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }

        // ============================================
        // Delete Karyawan Function
        // ============================================
        function deleteKaryawan(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus karyawan "${nama}"?\n\nData yang dihapus tidak dapat dikembalikan!`)) {
                window.location.href = `karyawan.php?delete=${id}`;
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
            if (confirm('Apakah Anda yakin ingin menghapus karyawan "' + nama + '"?')) {
                window.location.href = 'karyawan.php?delete=' + id;
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

        console.log('👥 Kelola Karyawan - Loaded Successfully!');
    </script>
</body>
</html>