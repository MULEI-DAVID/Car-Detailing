<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Please log in.']);
    exit();
}

$conn = getDatabaseConnection();
$user_id = $_SESSION['user_id'];

// Get user vehicles
$sql = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$vehicles = [];
while ($vehicle = $result->fetch_assoc()) {
    $vehicles[] = [
        'id' => $vehicle['id'],
        'nickname' => $vehicle['nickname'],
        'make' => $vehicle['make'],
        'model' => $vehicle['model'],
        'year' => $vehicle['year'],
        'type' => $vehicle['type'],
        'color' => $vehicle['color'],
        'license_plate' => $vehicle['license_plate'],
        'vin' => $vehicle['vin'],
        'is_default' => (bool)$vehicle['is_default']
    ];
}

header('Content-Type: application/json');
echo json_encode($vehicles);
?>
