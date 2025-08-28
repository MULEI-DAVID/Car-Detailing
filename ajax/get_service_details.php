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
    echo '<div class="alert alert-danger">Service ID is required.</div>';
    exit();
}

$service_id = (int)$_GET['id'];

// Get detailed service information
$sql = "SELECT s.*, COUNT(bs.id) as booking_count
        FROM services s
        LEFT JOIN booking_services bs ON s.id = bs.service_id
        WHERE s.id = ?
        GROUP BY s.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Service not found.</div>';
    exit();
}

$service = $result->fetch_assoc();
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-cogs me-2"></i>Service Information</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Service ID:</strong> #<?php echo $service['id']; ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($service['name']); ?></p>
                <p><strong>Category:</strong> 
                    <span class="badge bg-<?php echo $service['category'] == 'package' ? 'primary' : 'success'; ?>">
                        <?php echo ucfirst($service['category']); ?>
                    </span>
                </p>
                <p><strong>Status:</strong> 
                    <?php if ($service['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-money-bill me-2"></i>Pricing & Duration</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Price:</strong> <strong class="text-primary"><?php echo format_currency($service['price']); ?></strong></p>
                <p><strong>Duration:</strong> <?php echo $service['duration']; ?> minutes</p>
                <p><strong>Usage Count:</strong> <?php echo $service['booking_count']; ?> bookings</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h6 class="text-primary"><i class="fas fa-align-left me-2"></i>Description</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Service Details</h6>
        <div class="card">
            <div class="card-body">
                <p><strong>Created:</strong> <?php echo format_date($service['created_at']); ?></p>
                <?php if (isset($service['updated_at']) && $service['updated_at']): ?>
                    <p><strong>Last Updated:</strong> <?php echo format_date($service['updated_at']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
