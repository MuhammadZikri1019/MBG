<?php
// import_db.php
require_once 'koneksi.php';

// Increase time limit for large imports
set_time_limit(300);

$sqlFile = 'db_mbg.sql';

echo "<h2>Database Import Tool</h2>";
echo "<p>Target Database: <strong>" . DB_NAME . "</strong></p>";

if (!file_exists($sqlFile)) {
    die("<p style='color: red'>Error: File $sqlFile not found!</p>");
}

// Read the SQL file
$sql = file_get_contents($sqlFile);

// Remove comments (optional, but good for cleaner execution)
$sql = preg_replace('/^--.*$/m', '', $sql);
$sql = preg_replace('/^\/\*.*\*\/$/m', '', $sql);

// Execute multi query
echo "<p>Importing data from $sqlFile...</p>";
echo "<div style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; height: 300px; overflow-y: scroll;'>";

if (mysqli_multi_query($conn, $sql)) {
    $count = 0;
    do {
        $count++;
        // Store first result set
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
        
        // Check for errors
        if (mysqli_errno($conn)) {
            echo "<p style='color: red'>Query $count Error: " . mysqli_error($conn) . "</p>";
        } else {
            // echo "<p style='color: green'>Query $count Success</p>";
        }
        
        // Prepare next result set
    } while (mysqli_next_result($conn));
    
    echo "<p style='color: green; font-weight: bold;'>Import Completed Successfully!</p>";
} else {
    echo "<p style='color: red'>Import Failed: " . mysqli_error($conn) . "</p>";
}

echo "</div>";
echo "<p><a href='index.php'>Go to Home</a></p>";
?>
