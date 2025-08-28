<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !is_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

$conn = getDatabaseConnection();

// Get all users with statistics
$sql = "SELECT u.*,
               COUNT(DISTINCT v.id) as vehicle_count,
               COUNT(DISTINCT b.id) as booking_count,
               SUM(b.total_amount) as total_spent
        FROM users u
        LEFT JOIN vehicles v ON u.id = v.user_id
        LEFT JOIN bookings b ON u.id = b.user_id AND b.status = 'completed'
        WHERE u.is_admin = 0
        GROUP BY u.id
        ORDER BY u.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch users']);
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV headers
$headers = [
    'User ID',
    'Full Name',
    'Email',
    'Phone',
    'Email Verified',
    'Account Status',
    'Vehicles Count',
    'Bookings Count',
    'Total Spent',
    'Registration Date',
    'Last Updated'
];

fputcsv($output, $headers);

// Add data rows
while ($user = $result->fetch_assoc()) {
    $row = [
        $user['id'],
        $user['full_name'],
        $user['email'],
        $user['phone'],
        $user['email_verified'] ? 'Yes' : 'No',
        $user['is_active'] ? 'Active' : 'Inactive',
        $user['vehicle_count'],
        $user['booking_count'],
        number_format($user['total_spent'] ?? 0, 2),
        $user['created_at'],
        $user['updated_at'] ?: 'Never'
    ];
    
    fputcsv($output, $row);
}

fclose($output);
exit();
?>

