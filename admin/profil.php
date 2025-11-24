<?php
// admin/profil.php
require_once '../koneksi.php';
checkRole(['super_admin']);

$id_admin = $_SESSION['user_id'];
$success = '';
$error = '';

// Ambil data user saat ini
$query = "SELECT * FROM tbl_super_admin WHERE id_super_admin = '$id_admin'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Proses Update Profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $password = escape($_POST['password']);
    
    // Validasi Email Unik (kecuali email sendiri)
    $check_email = mysqli_query($conn, "SELECT * FROM tbl_super_admin WHERE email = '$email' AND id_super_admin != '$id_admin'");
    if (mysqli_num_rows($check_email) > 0) {
        $error = "Email sudah digunakan oleh pengguna lain!";
    } else {
        // Update Foto Profil
        $new_foto = "";
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['foto_profil']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = "profil_admin_" . $id_admin . "_" . time() . "." . $ext;
                $upload_dir = "../assets/img/profil/";
                
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_dir . $new_filename)) {
                    // Hapus foto lama jika ada
                    if ($user['foto_profil'] && file_exists($upload_dir . $user['foto_profil'])) {
                        unlink($upload_dir . $user['foto_profil']);
                    }
                    $new_foto = $new_filename;
                    $_SESSION['foto_profil'] = $new_filename; // Update session
                } else {
                    $error = "Gagal mengupload foto!";
                }
            } else {
                $error = "Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.";
            }
        }
        
        if (!$error) {
            // Build the SET clause dynamically
            $updates = [];
            $updates[] = "nama_lengkap = '$nama'";
            $updates[] = "email = '$email'";
            
            if (!empty($password)) {
                $updates[] = "password = '$password'";
            }
            
            if ($new_foto) {
                $updates[] = "foto_profil = '$new_foto'";
            }
            
            $query_update = "UPDATE tbl_super_admin SET " . implode(', ', $updates) . " WHERE id_super_admin = '$id_admin'";
            
            if (mysqli_query($conn, $query_update)) {
                $success = "Profil berhasil diperbarui!";
                // Update session data
                $_SESSION['user_name'] = $nama;
                $_SESSION['user_email'] = $email;
                
                // Refresh data user
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = "Gagal memperbarui profil: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="bi bi-list"></i></button>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><img src="../assets/img/logo.png" alt="MBG Logo" class="logo-image"></div>
            <h4>MBG System</h4><small>Super Admin Panel</small>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
            <a href="pengelola.php"><i class="bi bi-people"></i><span>Kelola Pengelola</span></a>
            <a href="dapur.php"><i class="bi bi-house"></i><span>Kelola Dapur</span></a>
            <a href="karyawan.php"><i class="bi bi-person-badge"></i><span>Kelola Karyawan</span></a>
            <a href="laporan-sistem.php"><i class="bi bi-file-earmark-bar-graph"></i><span>Laporan Sistem</span></a>
            <a href="backup.php"><i class="bi bi-database"></i><span>Backup & Restore</span></a>
            <a href="settings.php"><i class="bi bi-gear"></i><span>Pengaturan Sistem</span></a>
            <a href="log-aktivitas.php"><i class="bi bi-clock-history"></i><span>Log Aktivitas</span></a>
            <a href="profil.php" class="active"><i class="bi bi-person-circle"></i><span>Profil</span></a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <div><h4 class="mb-0">Profil Saya</h4><small class="text-muted">Kelola informasi akun Anda</small></div>
            <div class="user-profile">
                <a href="profil.php" class="text-decoration-none text-dark d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="fw-bold"><?= $_SESSION['user_name'] ?></div>
                        <small class="text-muted">Super Administrator</small>
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

        <!-- Profile Card -->
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if ($user['foto_profil']): ?>
                                <img src="../assets/img/profil/<?= $user['foto_profil'] ?>" alt="Foto Profil" 
                                     class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 150px; height: 150px; font-size: 60px; font-weight: bold;">
                                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h4 class="mb-1"><?= $user['nama_lengkap'] ?></h4>
                        <p class="text-muted mb-3">Super Administrator</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profil</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" value="<?= $user['nama_lengkap'] ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                                <small class="text-muted">Isi hanya jika ingin mengubah password</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Foto Profil</label>
                                <input type="file" name="foto_profil" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Akun</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>ID Admin:</strong></p>
                                <p class="text-muted">#<?= $user['id_super_admin'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Terdaftar Sejak:</strong></p>
                                <p class="text-muted"><?= date('d F Y', strtotime($user['created_at'])) ?></p>
                            </div>
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
