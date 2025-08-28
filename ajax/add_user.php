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
$required_fields = ['full_name', 'email', 'phone', 'password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];
$email_verified = isset($_POST['email_verified']) ? (int)$_POST['email_verified'] : 0;
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit();
}

// Check if email already exists
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email address already exists']);
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$sql = "INSERT INTO users (full_name, email, phone, password, email_verified, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $full_name, $email, $phone, $hashed_password, $email_verified, $is_active);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    
    // Set email verification timestamp if verified
    if ($email_verified) {
        $sql = "UPDATE users SET email_verified_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'User added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add user: ' . $conn->error]);
}
?>

