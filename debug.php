<?php
// Step 1: Test apakah PHP bekerja
echo "Step 1: PHP Works! ✅<br>";

// Step 2: Test phpinfo
echo "Step 2: Checking PHP Info...<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Step 3: Test mysqli extension
if (extension_loaded('mysqli')) {
    echo "Step 3: mysqli extension LOADED ✅<br>";
} else {
    echo "Step 3: mysqli extension NOT LOADED ❌<br>";
    die("ERROR: mysqli extension required!");
}

// Step 4: Test database connection
echo "Step 4: Testing Database Connection...<br>";

$host = 'db.fr-pari1.bengt.wasmernet.com';
$port = 10272;
$user = '32c7cae474c38000e6591c4c7721';
$pass = '069232c7-cae4-7f0f-8000-77eb584fa46e';
$db   = 'dbAaHiLmjZwwrtJ9K7v63P9Z';

$conn = @mysqli_connect($host, $user, $pass, $db, $port);

if ($conn) {
    echo "Step 4: Database Connection SUCCESS ✅<br>";
    
    // Step 5: Test query
    echo "Step 5: Testing Query...<br>";
    $result = @mysqli_query($conn, "SELECT 1");
    if ($result) {
        echo "Step 5: Query SUCCESS ✅<br>";
    } else {
        echo "Step 5: Query FAILED ❌ Error: " . mysqli_error($conn) . "<br>";
    }
    
    mysqli_close($conn);
} else {
    echo "Step 4: Database Connection FAILED ❌<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
}

echo "<hr>";
echo "All tests completed!";
?>
