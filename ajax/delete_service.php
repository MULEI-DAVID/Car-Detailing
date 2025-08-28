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

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'Service ID is required.']);
    exit();
}

$service_id = (int)$input['service_id'];

// Check if service exists
$check_sql = "SELECT id, name FROM services WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $service_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Service not found.']);
    exit();
}

$service = $check_result->fetch_assoc();

// Check if service is being used in any bookings
$usage_sql = "SELECT COUNT(*) as count FROM booking_services WHERE service_id = ?";
$usage_stmt = $conn->prepare($usage_sql);
$usage_stmt->bind_param("i", $service_id);
$usage_stmt->execute();
$usage_result = $usage_stmt->get_result();
$usage_count = $usage_result->fetch_assoc()['count'];

if ($usage_count > 0) {
    echo json_encode([
        'success' => false, 
        'message' => "Cannot delete service '{$service['name']}' because it is being used in {$usage_count} booking(s). Please deactivate it instead."
    ]);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete service
    $delete_sql = "DELETE FROM services WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $service_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('Failed to delete service.');
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Service '{$service['name']}' deleted successfully!"
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting service: ' . $e->getMessage()]);
}

