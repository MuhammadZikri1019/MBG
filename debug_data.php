<?php
// debug_data.php
require_once 'koneksi.php';

echo "<h2>Database Debug Info</h2>";
echo "<p><strong>Connected to Database:</strong> " . DB_NAME . "</p>";
echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";

echo "<h3>Sample Data: tbl_super_admin</h3>";
$result = mysqli_query($conn, "SELECT * FROM tbl_super_admin LIMIT 5");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Email</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id_super_admin'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['nama_lengkap'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red'>Error: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Sample Data: tbl_pengelola_dapur</h3>";
$result = mysqli_query($conn, "SELECT * FROM tbl_pengelola_dapur ORDER BY created_at DESC LIMIT 5");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nama</th><th>Email</th><th>Status</th><th>Created At</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id_pengelola'] . "</td>";
        echo "<td>" . $row['nama'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red'>Error: " . mysqli_error($conn) . "</p>";
}
?>
