<?php
// test_connection.php - Upload file ini ke hosting Wasmer Anda
// Akses: https://mbg00.wasmer.app/test_connection.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Koneksi Database Wasmer</h2>";

// Kredensial
$host = 'db.fr-pari1.bengt.wasmernet.com';
$port = '10272';
$user = '32c7cae474c38000e6591c4c7721';
$pass = '069232c7-cae4-7f0f-8000-77eb584fa46e';
$db   = 'dbAaHiLmjZwwrtJ9K7v63P9Z';

echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>Port:</strong> $port</p>";
echo "<p><strong>Database:</strong> $db</p>";

// Test koneksi
echo "<h3>Mencoba koneksi...</h3>";

try {
    $conn = mysqli_connect($host, $user, $pass, $db, $port);
    
    if (!$conn) {
        echo "<p style='color: red;'>❌ <strong>GAGAL:</strong> " . mysqli_connect_error() . "</p>";
        die();
    }
    
    echo "<p style='color: green;'>✅ <strong>Koneksi BERHASIL!</strong></p>";
    
    // Test query sederhana
    echo "<h3>Test Query...</h3>";
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_role");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p style='color: green;'>✅ Query berhasil! Total role: " . $row['total'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Query gagal: " . mysqli_error($conn) . "</p>";
    }
    
    // Cek apakah view ada
    echo "<h3>Cek View Dashboard...</h3>";
    $result = mysqli_query($conn, "SELECT * FROM vw_dashboard_super_admin LIMIT 1");
    
    if ($result) {
        echo "<p style='color: green;'>✅ View vw_dashboard_super_admin ADA dan berfungsi!</p>";
    } else {
        echo "<p style='color: red;'>❌ View vw_dashboard_super_admin TIDAK ADA: " . mysqli_error($conn) . "</p>";
        echo "<p><strong>Solusi:</strong> Jalankan script fix_view_dashboard.sql di phpMyAdmin!</p>";
    }
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p>Jika semua ✅, berarti koneksi OK. Jika ada ❌, ikuti instruksi di atas.</p>";
?>
