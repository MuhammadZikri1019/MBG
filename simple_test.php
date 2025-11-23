<?php
// simple_test.php - Test paling sederhana
// Akses: https://mbg00.wasmer.app/simple_test.php

echo "PHP bekerja dengan baik!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Test koneksi database langsung
$host = 'db.fr-pari1.bengt.wasmernet.com';
$port = '10272';
$user = '32c7cae474c38000e6591c4c7721';
$pass = '069232c7-cae4-7f0f-8000-77eb584fa46e';
$db   = 'dbAaHiLmjZwwrtJ9K7v63P9Z';

$conn = @mysqli_connect($host, $user, $pass, $db, $port);

if ($conn) {
    echo "<h2 style='color:green'>✅ Database Connected!</h2>";
    mysqli_close($conn);
} else {
    echo "<h2 style='color:red'>❌ Database Error: " . mysqli_connect_error() . "</h2>";
}
?>
