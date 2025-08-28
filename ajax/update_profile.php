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
$required_fields = ['full_name', 'phone'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name']);
$phone = trim($_POST['phone']);

// Validate full name (at least 2 characters, only letters, spaces, and hyphens)
if (strlen($full_name) < 2) {
    echo json_encode(['success' => false, 'message' => 'Full name must be at least 2 characters long']);
    exit();
}

if (!preg_match('/^[a-zA-Z\s\-\.]+$/', $full_name)) {
    echo json_encode(['success' => false, 'message' => 'Full name can only contain letters, spaces, hyphens, and periods']);
    exit();
}

// Validate phone number (basic validation for various formats)
$phone_clean = preg_replace('/[^0-9+\-\(\)\s]/', '', $phone);
if (strlen($phone_clean) < 10) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number']);
    exit();
}

// Check if phone number is already used by another user
$check_sql = "SELECT id FROM users WHERE phone = ? AND id != ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $phone, $user_id);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This phone number is already registered by another user']);
    exit();
}

// Update profile
$update_sql = "UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ssi", $full_name, $phone, $user_id);

if ($update_stmt->execute()) {
    // Get updated user data for response
    $get_user_sql = "SELECT id, full_name, email, phone, created_at, updated_at FROM users WHERE id = ?";
    $get_user_stmt = $conn->prepare($get_user_sql);
    $get_user_stmt->bind_param("i", $user_id);
    $get_user_stmt->execute();
    $updated_user = $get_user_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully!',
        'user' => [
            'id' => $updated_user['id'],
            'full_name' => $updated_user['full_name'],
            'email' => $updated_user['email'],
            'phone' => $updated_user['phone'],
            'created_at' => $updated_user['created_at'],
            'updated_at' => $updated_user['updated_at']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update profile: ' . $conn->error,
        'error_code' => $conn->errno
    ]);
}
?>

