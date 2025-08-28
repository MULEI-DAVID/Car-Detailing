<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize database connection
$conn = getDatabaseConnection();

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['booking_id']) || !isset($input['vehicle_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$booking_id = (int)$input['booking_id'];
$vehicle_id = (int)$input['vehicle_id'];

// Verify booking belongs to user
$check_booking_sql = "SELECT id FROM bookings WHERE id = ? AND user_id = ?";
$check_booking_stmt = $conn->prepare($check_booking_sql);
$check_booking_stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$check_booking_stmt->execute();

if ($check_booking_stmt->get_result()->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking']);
    exit();
}

// Verify vehicle belongs to user
$check_vehicle_sql = "SELECT id FROM vehicles WHERE id = ? AND user_id = ?";
$check_vehicle_stmt = $conn->prepare($check_vehicle_sql);
$check_vehicle_stmt->bind_param("ii", $vehicle_id, $_SESSION['user_id']);
$check_vehicle_stmt->execute();

if ($check_vehicle_stmt->get_result()->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid vehicle']);
    exit();
}

// Update booking vehicle
$update_sql = "UPDATE bookings SET vehicle_id = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $vehicle_id, $booking_id);

if ($update_stmt->execute()) {
    // Get vehicle details for notification
    $vehicle_sql = "SELECT nickname, make, model, year FROM vehicles WHERE id = ?";
    $vehicle_stmt = $conn->prepare($vehicle_sql);
    $vehicle_stmt->bind_param("i", $vehicle_id);
    $vehicle_stmt->execute();
    $vehicle = $vehicle_stmt->get_result()->fetch_assoc();
    
    // Log the change (in a real system, you might send a notification to admin)
    error_log("User {$_SESSION['user_id']} changed vehicle for booking #$booking_id to {$vehicle['year']} {$vehicle['make']} {$vehicle['model']} ({$vehicle['nickname']})");
    
    echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating vehicle']);
}
?>
