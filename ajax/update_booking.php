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
if (!isset($_POST['booking_id']) || !isset($_POST['appointment_date']) || !isset($_POST['appointment_time'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit();
}

$booking_id = (int)$_POST['booking_id'];
$appointment_date = sanitize_input($_POST['appointment_date']);
$appointment_time = sanitize_input($_POST['appointment_time']);
$service_type = sanitize_input($_POST['service_type']);
$location = sanitize_input($_POST['location']);
$vehicle_id = (int)$_POST['vehicle_id'];
$admin_notes = sanitize_input($_POST['admin_notes']);
$services = isset($_POST['services']) ? $_POST['services'] : [];

// Validate booking exists
$check_sql = "SELECT id FROM bookings WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found.']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Update booking details
    $update_sql = "UPDATE bookings SET 
                   appointment_date = ?, 
                   appointment_time = ?, 
                   service_type = ?, 
                   location = ?, 
                   vehicle_id = ?, 
                   admin_notes = ?,
                   updated_at = NOW()
                   WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssisi", $appointment_date, $appointment_time, $service_type, $location, $vehicle_id, $admin_notes, $booking_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update booking details.');
    }
    
    // Remove existing services
    $delete_services_sql = "DELETE FROM booking_services WHERE booking_id = ?";
    $delete_services_stmt = $conn->prepare($delete_services_sql);
    $delete_services_stmt->bind_param("i", $booking_id);
    $delete_services_stmt->execute();
    
    // Add new services
    if (!empty($services)) {
        $insert_service_sql = "INSERT INTO booking_services (booking_id, service_id) VALUES (?, ?)";
        $insert_service_stmt = $conn->prepare($insert_service_sql);
        
        foreach ($services as $service_id) {
            $service_id = (int)$service_id;
            $insert_service_stmt->bind_param("ii", $booking_id, $service_id);
            $insert_service_stmt->execute();
        }
    }
    
    // Recalculate total amount
    $total_sql = "SELECT SUM(s.price) as subtotal 
                  FROM booking_services bs 
                  JOIN services s ON bs.service_id = s.id 
                  WHERE bs.booking_id = ?";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->bind_param("i", $booking_id);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    
    $subtotal = $total_row['subtotal'] ?? 0;
    $tax_amount = $subtotal * 0.16; // 16% VAT
    $total_amount = $subtotal + $tax_amount;
    
    // Update total amount
    $update_total_sql = "UPDATE bookings SET subtotal = ?, tax_amount = ?, total_amount = ? WHERE id = ?";
    $update_total_stmt = $conn->prepare($update_total_sql);
    $update_total_stmt->bind_param("dddi", $subtotal, $tax_amount, $total_amount, $booking_id);
    $update_total_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Booking updated successfully!',
        'total_amount' => $total_amount
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error updating booking: ' . $e->getMessage()]);
}

