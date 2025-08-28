<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !is_admin()) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Access denied. Admin privileges required.</div>';
    exit();
}

$conn = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">User ID is required.</div>';
    exit();
}

$user_id = (int)$_GET['id'];

// Get detailed user information
$sql = "SELECT u.*,
               COUNT(DISTINCT v.id) as vehicle_count,
               COUNT(DISTINCT b.id) as booking_count,
               SUM(b.total_amount) as total_spent
        FROM users u
        LEFT JOIN vehicles v ON u.id = v.user_id
        LEFT JOIN bookings b ON u.id = b.user_id AND b.status = 'completed'
        WHERE u.id = ? AND u.is_admin = 0
        GROUP BY u.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">User not found.</div>';
    exit();
}

$user = $result->fetch_assoc();
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-user me-2"></i>Personal Information</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>User ID:</strong> #<?php echo $user['id']; ?></p>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Status:</strong> 
                    <?php if ($user['email_verified']): ?>
                        <span class="badge bg-success">Verified</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Pending Verification</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-chart-bar me-2"></i>Account Statistics</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Vehicles:</strong> <?php echo $user['vehicle_count']; ?> registered</p>
                <p><strong>Bookings:</strong> <?php echo $user['booking_count']; ?> total</p>
                <p><strong>Total Spent:</strong> <strong class="text-primary"><?php echo format_currency($user['total_spent'] ?? 0); ?></strong></p>
                <p><strong>Member Since:</strong> <?php echo format_date($user['created_at']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Account Information</h6>
        <div class="card">
            <div class="card-body">
                <p><strong>Registration Date:</strong> <?php echo format_date($user['created_at']); ?></p>
                <?php if (isset($user['updated_at']) && $user['updated_at']): ?>
                    <p><strong>Last Updated:</strong> <?php echo format_date($user['updated_at']); ?></p>
                <?php endif; ?>
                <p><strong>Email Verification:</strong> 
                    <?php if ($user['email_verified']): ?>
                        <span class="text-success">✓ Verified on <?php echo format_date($user['email_verified_at']); ?></span>
                    <?php else: ?>
                        <span class="text-warning">⚠ Pending verification</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
