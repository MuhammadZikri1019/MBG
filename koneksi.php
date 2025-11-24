<?php
// koneksi.php
session_start();

// Konfigurasi Database - Wasmer
define('DB_HOST', 'db.fr-pari1.bengt.wasmernet.com');
define('DB_PORT', '10272');
define('DB_USER', '3da41cfd763580009b220c65f8d7');
define('DB_PASS', '06923da4-1cfe-711b-8000-58e53d9c235e');
define('DB_NAME', 'dbZ9s7AJt4CAfv6FE5o67Q67');

// Membuat koneksi dengan port
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Set timezone to Asia/Jakarta (WIB - UTC+7)
date_default_timezone_set('Asia/Jakarta');
// Try to set MySQL timezone, suppress error if not supported
@mysqli_query($conn, "SET time_zone = '+07:00'");

// Check Maintenance Mode
$config_file = __DIR__ . '/config/settings.json';
if (@file_exists($config_file)) {
    $settings = @json_decode(@file_get_contents($config_file), true);
    
    if (is_array($settings) && isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === true) {
        // Allow access to login.php, logout.php, and admin pages (if logged in as super_admin)
        $current_script = basename($_SERVER['PHP_SELF']);
        
        // Skip check for login/logout/maintenance pages
        if ($current_script != 'login.php' && $current_script != 'logout.php' && $current_script != 'maintenance.php') {
            
            // Check if user is logged in as super_admin
            $is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'super_admin';
            
            if (!$is_admin) {
                // Determine path to maintenance.php based on current location
                // If we are in a subdirectory (like admin/), we need to go up one level
                $path_to_root = (dirname($_SERVER['PHP_SELF']) == '/' || dirname($_SERVER['PHP_SELF']) == '\\') ? '' : '../';
                
                // Redirect to maintenance page
                header("Location: " . $path_to_root . "maintenance.php");
                exit();
            }
        }
    }
}

// Fungsi untuk mencegah SQL Injection
function escape($data) {
    global $conn;
    return mysqli_real_escape_string($conn, $data);
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Fungsi untuk cek role
function checkRole($allowedRoles) {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
    
    if (!in_array($_SESSION['user_type'], $allowedRoles)) {
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Helper function untuk upload foto
function uploadFoto($file, $type = 'menu') {
    $target_dir = "../assets/img/" . $type . "/";
    
    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Validasi tipe file
    $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($file_extension, $allowed_types)) {
        return ['status' => false, 'message' => 'Hanya file JPG, JPEG, PNG, dan WEBP yang diperbolehkan.'];
    }
    
    // Validasi ukuran (max 2MB)
    if ($file["size"] > 2000000) {
        return ['status' => false, 'message' => 'Ukuran file terlalu besar (Maksimal 2MB).'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['status' => true, 'filename' => $new_filename];
    } else {
        return ['status' => false, 'message' => 'Gagal mengupload file.'];
    }
}
?>
