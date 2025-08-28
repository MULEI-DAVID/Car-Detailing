<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Please log in.']);
    exit();
}

$conn = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate required fields
$required_fields = ['vehicle_id', 'nickname', 'make', 'model', 'year', 'type'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

$vehicle_id = (int)$_POST['vehicle_id'];
$user_id = $_SESSION['user_id'];
$nickname = trim($_POST['nickname']);
$make = trim($_POST['make']);
$model = trim($_POST['model']);
$year = (int)$_POST['year'];
$type = trim($_POST['type']);
$color = isset($_POST['color']) ? trim($_POST['color']) : '';
$license_plate = isset($_POST['license_plate']) ? trim($_POST['license_plate']) : '';
$vin = isset($_POST['vin']) ? trim($_POST['vin']) : '';
$is_default = isset($_POST['is_default']) ? 1 : 0;

// Validate year
if ($year < 1900 || $year > date('Y') + 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid year']);
    exit();
}

// Check if vehicle belongs to user
$check_sql = "SELECT id FROM vehicles WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $vehicle_id, $user_id);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Vehicle not found or access denied']);
    exit();
}

// If this is set as default, unset other defaults
if ($is_default) {
    $conn->query("UPDATE vehicles SET is_default = 0 WHERE user_id = $user_id");
}

// Update vehicle
$update_sql = "UPDATE vehicles SET 
               nickname = ?, 
               make = ?, 
               model = ?, 
               year = ?, 
               type = ?, 
               color = ?, 
               license_plate = ?, 
               vin = ?, 
               is_default = ?,
               updated_at = NOW()
               WHERE id = ? AND user_id = ?";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("sssissssiii", $nickname, $make, $model, $year, $type, $color, $license_plate, $vin, $is_default, $vehicle_id, $user_id);

if ($update_stmt->execute()) {
    // Get updated vehicle data for response
    $get_vehicle_sql = "SELECT * FROM vehicles WHERE id = ? AND user_id = ?";
    $get_vehicle_stmt = $conn->prepare($get_vehicle_sql);
    $get_vehicle_stmt->bind_param("ii", $vehicle_id, $user_id);
    $get_vehicle_stmt->execute();
    $updated_vehicle = $get_vehicle_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Vehicle updated successfully!',
        'vehicle' => [
            'id' => $updated_vehicle['id'],
            'nickname' => $updated_vehicle['nickname'],
            'make' => $updated_vehicle['make'],
            'model' => $updated_vehicle['model'],
            'year' => $updated_vehicle['year'],
            'type' => $updated_vehicle['type'],
            'color' => $updated_vehicle['color'],
            'license_plate' => $updated_vehicle['license_plate'],
            'vin' => $updated_vehicle['vin'],
            'is_default' => (bool)$updated_vehicle['is_default']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update vehicle: ' . $conn->error,
        'error_code' => $conn->errno
    ]);
}
?>
