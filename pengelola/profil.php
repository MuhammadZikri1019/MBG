<?php
// pengelola/profil.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];
$success = '';
$error = '';

// Ambil data user saat ini
$query = "SELECT * FROM tbl_pengelola_dapur WHERE id_pengelola = '$id_pengelola'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Proses Update Profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $password = escape($_POST['password']);
    
    // Validasi Email Unik (kecuali email sendiri)
    $check_email = mysqli_query($conn, "SELECT * FROM tbl_pengelola_dapur WHERE email = '$email' AND id_pengelola != '$id_pengelola'");
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
                $new_filename = "profil_" . $id_pengelola . "_" . time() . "." . $ext;
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
            $updates[] = "nama = '$nama'";
            $updates[] = "email = '$email'";
            
            if (!empty($password)) {
                $updates[] = "password = '$password'";
            }
            
            if ($new_foto) {
                $updates[] = "foto_profil = '$new_foto'";
            }
            
            $query_update = "UPDATE tbl_pengelola_dapur SET " . implode(', ', $updates) . " WHERE id_pengelola = '$id_pengelola'";
            
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
            <a href="profil.php" class="active">
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
                <h4 class="mb-0">Profil Saya</h4>
                <small class="text-muted">Kelola informasi akun Anda</small>
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
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show animate-alert" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show animate-alert" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <?php if ($user['foto_profil']): ?>
                                        <img src="../assets/img/profil/<?= $user['foto_profil'] ?>" alt="Foto Profil" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px; font-size: 64px; color: #aaa;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <label for="foto_profil" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                        <i class="bi bi-camera-fill"></i>
                                    </label>
                                    <input type="file" name="foto_profil" id="foto_profil" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </div>
                                <div class="mt-2 text-muted small">Klik ikon kamera untuk mengganti foto</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="<?= $user['nama'] ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Cari elemen img sebelumnya atau div placeholder
                    var container = input.parentElement;
                    var img = container.querySelector('img');
                    var placeholder = container.querySelector('div.rounded-circle');
                    
                    if (img) {
                        img.src = e.target.result;
                    } else if (placeholder) {
                        // Ganti placeholder dengan img baru
                        var newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = "Foto Profil";
                        newImg.className = "rounded-circle img-thumbnail";
                        newImg.style.width = "150px";
                        newImg.style.height = "150px";
                        newImg.style.objectFit = "cover";
                        
                        placeholder.parentNode.replaceChild(newImg, placeholder);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
