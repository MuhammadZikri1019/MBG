<?php
// login.php
require_once 'koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    $redirectPath = match($_SESSION['user_type']) {
        'super_admin' => 'admin/dashboard.php',
        'pengelola' => 'pengelola/dashboard.php',
        'karyawan' => 'karyawan/dashboard.php',
        default => 'login.php'
    };
    header("Location: $redirectPath");
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // Proses Login
    // Proses Login
    if ($_POST['action'] == 'login') {
        $identifier = escape($_POST['identifier']);
        $password = escape($_POST['password']);
        
        // 1. Cek Super Admin (Username / Email / Nama Lengkap)
        $query = "SELECT * FROM tbl_super_admin 
                  WHERE (username = '$identifier' OR email = '$identifier' OR nama_lengkap = '$identifier') 
                  AND password = '$password' 
                  AND status = 'aktif'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['id_super_admin'];
            $_SESSION['user_type'] = 'super_admin';
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['foto_profil'] = $user['foto_profil'];
            
            mysqli_query($conn, "UPDATE tbl_super_admin SET last_login = NOW() WHERE id_super_admin = {$user['id_super_admin']}");
            
            header("Location: admin/dashboard.php");
            exit();
        }
        
        // 2. Cek Pengelola Dapur (Email / Nama)
        $query = "SELECT * FROM tbl_pengelola_dapur 
                  WHERE (email = '$identifier' OR nama = '$identifier') 
                  AND password = '$password' 
                  AND status = 'aktif'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if ($user['is_verified'] == 0) {
                $error = "Akun belum diverifikasi! Silakan cek email Anda.";
                $show_verification = true;
                $verified_email = $user['email'];
            } else {
                $_SESSION['user_id'] = $user['id_pengelola'];
                $_SESSION['user_type'] = 'pengelola';
                $_SESSION['user_name'] = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['foto_profil'] = $user['foto_profil'];
                
                header("Location: pengelola/dashboard.php");
                exit();
            }
        }
        
        // 3. Cek Karyawan (Email / Nama)
        $query = "SELECT k.*, d.nama_dapur 
                  FROM tbl_karyawan k
                  LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                  WHERE (k.email = '$identifier' OR k.nama = '$identifier') 
                  AND k.password = '$password' 
                  AND k.status = 'aktif'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['id_karyawan'];
            $_SESSION['user_type'] = 'karyawan';
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['id_dapur'] = $user['id_dapur'];
            $_SESSION['nama_dapur'] = $user['nama_dapur'];
            $_SESSION['bagian'] = $user['bagian'];
            $_SESSION['foto_profil'] = $user['foto_profil'];
            
            header("Location: karyawan/dashboard.php");
            exit();
        }
        
        $error = "Nama/Email/Username atau Password salah!";
    }
    
    // Proses Register Pengelola
    elseif ($_POST['action'] == 'register') {
        $nama = escape($_POST['name']);
        $email = escape($_POST['email']);
        $password = escape($_POST['password']);
        $confirm_password = escape($_POST['confirm_password']);
        
        // Validasi
        if ($password !== $confirm_password) {
            $register_error = "Password dan Konfirmasi Password tidak sama!";
        } else {
            // Cek email sudah terdaftar
            $check_query = "SELECT * FROM tbl_pengelola_dapur WHERE email = '$email'";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                $register_error = "Email sudah terdaftar!";
            } else {
                // Generate verification code
                $verification_code = sprintf("%06d", mt_rand(1, 999999));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));
                
                // Insert pengelola baru
                $insert_query = "INSERT INTO tbl_pengelola_dapur (nama, email, password, status, verification_code, verification_expires_at, is_verified, created_at) 
                                VALUES ('$nama', '$email', '$password', 'aktif', '$verification_code', '$expires_at', 0, NOW())";
                
                if (mysqli_query($conn, $insert_query)) {
                    // Kirim Email menggunakan EmailService
                    require_once __DIR__ . '/includes/EmailService.php';
                    
                    $emailResult = EmailService::sendOTPEmail($email, $nama, $verification_code);
                    
                    if ($emailResult['success']) {
                        $success = "Registrasi berhasil! Kode verifikasi telah dikirim ke email Anda. Silakan cek inbox atau folder spam.";
                    } else {
                        // Email gagal, tapi tetap tampilkan kode untuk testing
                        $success = "Registrasi berhasil! Email gagal dikirim. <strong>Kode verifikasi Anda: $verification_code</strong><br><small class='text-muted'>({$emailResult['message']})</small>";
                    }
                    
                    $show_verification = true;
                    $verified_email = $email;
                } else {
                    $register_error = "Terjadi kesalahan. Silakan coba lagi.";
                }
            }
        }
    }
    
    // Proses Verifikasi
    elseif ($_POST['action'] == 'verify') {
        $email = escape($_POST['email']);
        $code = escape($_POST['code']);
        
        $query = "SELECT * FROM tbl_pengelola_dapur 
                  WHERE email = '$email' 
                  AND verification_code = '$code' 
                  AND is_verified = 0";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Cek expired
            if (strtotime($user['verification_expires_at']) > time()) {
                // Update verified
                mysqli_query($conn, "UPDATE tbl_pengelola_dapur SET is_verified = 1, verification_code = NULL WHERE id_pengelola = {$user['id_pengelola']}");
                $success = "Verifikasi berhasil! Silakan login.";
            } else {
                $error = "Kode verifikasi telah kadaluarsa. Silakan daftar ulang.";
                // Opsional: Hapus user yang expired agar bisa daftar ulang
                mysqli_query($conn, "DELETE FROM tbl_pengelola_dapur WHERE id_pengelola = {$user['id_pengelola']}");
            }
        } else {
            $error = "Kode verifikasi salah atau email tidak ditemukan!";
            $show_verification = true;
            $verified_email = $email;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container" id="authContainer">
        <!-- Overlay Panel -->
        <div class="overlay-container" style="display: <?= isset($show_verification) ? 'none' : '' ?>;">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <div class="overlay-content">
                        <img src="assets/img/logo.png" alt="MBG Logo" class="logo">
                        <h1 class="overlay-title">Welcome Back!</h1>
                        <p class="overlay-text">Sudah punya akun? Login untuk mengakses dashboard</p>
                        <button class="btn-ghost" id="signIn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </div>
                <div class="overlay-panel overlay-right">
                    <div class="overlay-content">
                        <img src="assets/img/logo.png" alt="MBG Logo" class="logo">
                        <h1 class="overlay-title">Hello, Friend!</h1>
                        <p class="overlay-text">Daftar sebagai Pengelola Dapur untuk memulai</p>
                        <button class="btn-ghost" id="signUp">
                            <i class="bi bi-person-plus me-2"></i>Sign Up
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sign In Form -->
        <div class="form-container sign-in-container" style="display: <?= isset($show_verification) ? 'none' : '' ?>;">
            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="action" value="login">
                
                <div class="form-header">
                    <h1>Sign In</h1>
                    <p class="text-muted">Masuk ke akun Anda</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'unauthorized'): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-shield-exclamation me-2"></i>
                        Anda tidak memiliki akses ke halaman tersebut!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="input-group-custom mt-4">
                    <i class="bi bi-person"></i>
                    <input type="text" name="identifier" placeholder="Email, Username, atau Nama" required>
                </div>

                <div class="input-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('loginPassword', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <a href="#" class="forgot-password">Lupa password?</a>

                <button type="submit" class="btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>

                <div class="mobile-toggle">
                    <p>Belum punya akun? <a href="#" id="mobileSignUp">Daftar sebagai Pengelola</a></p>
                </div>
            </form>
        </div>

        <!-- Verification Form -->
        <div class="form-container verification-container" style="display: <?= isset($show_verification) ? 'block' : 'none' ?>;">
            <form method="POST" action="" class="auth-form" id="otpForm">
                <input type="hidden" name="action" value="verify">
                <input type="hidden" name="email" value="<?= isset($verified_email) ? $verified_email : '' ?>">
                <input type="hidden" name="code" id="hiddenOtpCode">
                
                <div class="form-header text-center">
                    <div class="mb-3">
                        <i class="bi bi-envelope-check" style="font-size: 48px; color: var(--primary);"></i>
                    </div>
                    <h1>Verifikasi Akun</h1>
                    <p class="text-muted">Masukkan kode 6 digit yang telah dikirim ke email Anda</p>
                    <?php if (isset($verified_email)): ?>
                        <p class="text-muted fw-bold"><?= $verified_email ?></p>
                    <?php endif; ?>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- OTP Input Boxes -->
                <div class="otp-input-container mb-4">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="0">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="1">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="2">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="3">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="4">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="5">
                </div>

                <button type="submit" class="btn-primary" id="verifyBtn">
                    <i class="bi bi-check-circle me-2"></i>Verifikasi
                </button>

                <div class="text-center mt-3">
                    <p class="text-muted small">Tidak menerima kode?</p>
                    <a href="login.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Register Form (Hanya untuk Pengelola) -->
        <div class="form-container sign-up-container" style="display: <?= isset($show_verification) ? 'none' : '' ?>;">
            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="action" value="register">
                
                <div class="form-header">
                    <h1>Daftar Pengelola</h1>
                    <p class="text-muted">Buat akun Pengelola Dapur baru</p>
                </div>

                <?php if (isset($register_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= $register_error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="input-group-custom mt-4">
                    <i class="bi bi-person"></i>
                    <input type="text" name="name" placeholder="Nama Lengkap" required>
                </div>

                <div class="input-group-custom">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="registerPassword" placeholder="Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('registerPassword', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <div class="input-group-custom">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Konfirmasi Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                </button>

                <div class="mobile-toggle">
                    <p>Sudah punya akun? <a href="#" id="mobileSignIn">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>
