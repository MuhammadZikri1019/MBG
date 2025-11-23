<?php
// index.php
require_once 'koneksi.php';

// Redirect ke login jika belum login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect ke dashboard sesuai role
$redirectPath = match($_SESSION['user_type']) {
    'super_admin' => 'admin/dashboard.php',
    'pengelola' => 'pengelola/dashboard.php',
    'karyawan' => 'karyawan/dashboard.php',
    default => 'login.php'
};

header("Location: $redirectPath");
exit();
?>