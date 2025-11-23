<?php
require_once 'koneksi.php';

echo "<h3>tbl_pengelola_dapur</h3>";
$res = mysqli_query($conn, "DESCRIBE tbl_pengelola_dapur");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

echo "<h3>tbl_dapur</h3>";
$res = mysqli_query($conn, "DESCRIBE tbl_dapur");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}
?>
