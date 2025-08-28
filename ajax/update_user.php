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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate required fields
$required_fields = ['user_id', 'full_name', 'email', 'phone'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

$user_id = (int)$_POST['user_id'];
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
$email_verified = isset($_POST['email_verified']) ? (int)$_POST['email_verified'] : 0;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Check if email already exists for another user
$sql = "SELECT id FROM users WHERE email = ? AND id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email address already exists']);
    exit();
}

// Start building the update query
$update_fields = [];
$params = [];
$types = '';

// Add basic fields
$update_fields[] = "full_name = ?";
$update_fields[] = "email = ?";
$update_fields[] = "phone = ?";
$update_fields[] = "email_verified = ?";
$update_fields[] = "updated_at = NOW()";

$params[] = $full_name;
$params[] = $email;
$params[] = $phone;
$params[] = $email_verified;
$types .= 'sssi';

// Handle password update if provided
if (!empty($new_password)) {
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit();
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_fields[] = "password = ?";
    $params[] = $hashed_password;
    $types .= 's';
}

// Note: email_verified_at column doesn't exist in the users table
// The email_verified field is a boolean flag only

$sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ? AND is_admin = 0";
$params[] = $user_id;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update user: ' . $conn->error]);
}
?>
