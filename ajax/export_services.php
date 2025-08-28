<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !is_admin()) {
    http_response_code(403);
    echo 'Access denied. Admin privileges required.';
    exit();
}

$conn = getDatabaseConnection();

// Get all services with usage statistics
$sql = "SELECT s.*, COUNT(bs.id) as booking_count
        FROM services s
        LEFT JOIN booking_services bs ON s.id = bs.service_id
        GROUP BY s.id
        ORDER BY s.category, s.name";

$result = $conn->query($sql);

if (!$result) {
    echo 'Error fetching services data.';
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="services_export_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Service ID',
    'Name',
    'Description',
    'Category',
    'Price (KES)',
    'Duration (minutes)',
    'Status',
    'Usage Count',
    'Created Date',
    'Last Updated'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['description'],
        ucfirst($row['category']),
        number_format($row['price'], 2),
        $row['duration'],
        $row['is_active'] ? 'Active' : 'Inactive',
        $row['booking_count'],
        $row['created_at'],
        $row['updated_at'] ?: 'Never'
    ]);
}

fclose($output);
exit();
