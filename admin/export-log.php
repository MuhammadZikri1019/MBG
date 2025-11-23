<?php
// admin/export-log.php
require_once '../koneksi.php';
checkRole(['super_admin']);

// Filter (Sama seperti di log-aktivitas.php)
$user_type_filter = isset($_GET['user_type']) ? escape($_GET['user_type']) : '';
$activity_filter = isset($_GET['activity']) ? escape($_GET['activity']) : '';
$date_from = isset($_GET['date_from']) ? escape($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? escape($_GET['date_to']) : '';

// Build query
$where = array();
if ($user_type_filter) {
    $where[] = "user_type = '$user_type_filter'";
}
if ($activity_filter) {
    $where[] = "activity LIKE '%$activity_filter%'";
}
if ($date_from) {
    $where[] = "DATE(created_at) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(created_at) <= '$date_to'";
}

$where_clause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Query Data
$query = "SELECT * FROM tbl_log_aktivitas $where_clause ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Set Header untuk Download CSV
$filename = "log_aktivitas_" . date('Y-m-d_His') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis Header CSV
fputcsv($output, array('No', 'User Type', 'User Name', 'User Email', 'IP Address', 'Activity', 'Description', 'Date', 'Time'));

// Tulis Data
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $no++,
        $row['user_type'],
        $row['user_name'],
        $row['user_email'],
        $row['ip_address'],
        $row['activity'],
        $row['description'],
        date('Y-m-d', strtotime($row['created_at'])),
        date('H:i:s', strtotime($row['created_at']))
    ));
}

fclose($output);
exit();
?>
