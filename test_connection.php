<?php
// Test connection file - untuk debug koneksi database
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

// Set timezone
date_default_timezone_set('Asia/Jakarta');
echo "<p>✓ Timezone set to: " . date_default_timezone_get() . "</p>";
echo "<p>✓ Current time: " . date('Y-m-d H:i:s') . "</p>";

// Database config
define('DB_HOST', 'db.fr-pari1.bengt.wasmernet.com');
define('DB_PORT', '10272');
define('DB_USER', '32c7cae474c38000e6591c4c7721');
define('DB_PASS', '069232c7-cae4-7f0f-8000-77eb584fa46e');
define('DB_NAME', 'dbAaHiLmjZwwrtJ9K7v63P9Z');

echo "<p>Trying to connect to: " . DB_HOST . ":" . DB_PORT . "</p>";

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if (!$conn) {
    echo "<p style='color: red;'>✗ Connection FAILED: " . mysqli_connect_error() . "</p>";
    echo "<p>Error Code: " . mysqli_connect_errno() . "</p>";
    die();
} else {
    echo "<p style='color: green;'>✓ Connection SUCCESSFUL!</p>";
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
echo "<p>✓ Charset set to: utf8mb4</p>";

// Set MySQL timezone
mysqli_query($conn, "SET time_zone = '+07:00'");
echo "<p>✓ MySQL timezone set to: +07:00</p>";

// Test query
$result = mysqli_query($conn, "SELECT NOW() as current_time");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p style='color: green;'>✓ Test query SUCCESS!</p>";
    echo "<p>Database current time: " . $row['current_time'] . "</p>";
} else {
    echo "<p style='color: red;'>✗ Test query FAILED: " . mysqli_error($conn) . "</p>";
}

// Test table access
$test_table = mysqli_query($conn, "SHOW TABLES");
if ($test_table) {
    echo "<p style='color: green;'>✓ Can access tables!</p>";
    echo "<p>Total tables: " . mysqli_num_rows($test_table) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Cannot access tables: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
echo "<hr>";
echo "<p><strong>All tests completed!</strong></p>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>
