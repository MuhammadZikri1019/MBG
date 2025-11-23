<?php
require_once 'koneksi.php';

$sql = "ALTER TABLE tbl_laporan ADD COLUMN konten_laporan LONGTEXT DEFAULT NULL";

if (mysqli_query($conn, $sql)) {
    echo "<h1>Berhasil!</h1>";
    echo "<p>Kolom <code>konten_laporan</code> berhasil ditambahkan ke tabel <code>tbl_laporan</code>.</p>";
    echo "<p>Silakan kembali ke <a href='admin/dashboard.php'>Dashboard</a>.</p>";
} else {
    $error = mysqli_error($conn);
    if (strpos($error, "Duplicate column") !== false) {
        echo "<h1>Sudah Terupdate</h1>";
        echo "<p>Kolom <code>konten_laporan</code> sudah ada.</p>";
        echo "<p>Silakan kembali ke <a href='admin/dashboard.php'>Dashboard</a>.</p>";
    } else {
        echo "<h1>Gagal</h1>";
        echo "<p>Terjadi error: " . $error . "</p>";
    }
}
?>
