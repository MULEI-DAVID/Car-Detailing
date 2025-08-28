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

// Validate required fields
if (!isset($_POST['service_id']) || !isset($_POST['name']) || !isset($_POST['price'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit();
}

$service_id = (int)$_POST['service_id'];
$name = sanitize_input($_POST['name']);
$description = sanitize_input($_POST['description']);
$category = sanitize_input($_POST['category']);
$price = (float)$_POST['price'];
$duration = (int)$_POST['duration'];
$is_active = (int)$_POST['is_active'];

// Validate service exists
$check_sql = "SELECT id FROM services WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $service_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Service not found.']);
    exit();
}

// Update service
$update_sql = "UPDATE services SET 
               name = ?, 
               description = ?, 
               category = ?, 
               price = ?, 
               duration = ?, 
               is_active = ?
               WHERE id = ?";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("sssdiii", $name, $description, $category, $price, $duration, $is_active, $service_id);

if ($update_stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Service updated successfully!'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating service: ' . $conn->error]);
}
