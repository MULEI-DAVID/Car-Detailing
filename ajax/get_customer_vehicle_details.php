<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Access denied. Please log in.</div>';
    exit();
}

$conn = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">Vehicle ID is required.</div>';
    exit();
}

$vehicle_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get vehicle data (ensure it belongs to the logged-in user)
$sql = "SELECT * FROM vehicles WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $vehicle_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Vehicle not found or access denied.</div>';
    exit();
}

$vehicle = $result->fetch_assoc();

// Get booking history for this vehicle
$booking_sql = "SELECT b.*, GROUP_CONCAT(s.name SEPARATOR ', ') as services
                FROM bookings b
                LEFT JOIN booking_services bs ON b.id = bs.booking_id
                LEFT JOIN services s ON bs.service_id = s.id
                WHERE b.vehicle_id = ?
                GROUP BY b.id
                ORDER BY b.appointment_date DESC, b.created_at DESC
                LIMIT 5";
$booking_stmt = $conn->prepare($booking_sql);
$booking_stmt->bind_param("i", $vehicle_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Vehicle Information</h6>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Nickname:</label>
            <p class="mb-1"><?php echo htmlspecialchars($vehicle['nickname']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Make & Model:</label>
            <p class="mb-1"><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Type:</label>
            <p class="mb-1"><?php echo htmlspecialchars($vehicle['type']); ?></p>
        </div>
        
        <?php if ($vehicle['color']): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">Color:</label>
            <p class="mb-1">
                <span class="badge" style="background-color: <?php echo htmlspecialchars($vehicle['color']); ?>; color: white;">
                    <?php echo htmlspecialchars($vehicle['color']); ?>
                </span>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($vehicle['license_plate']): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">License Plate:</label>
            <p class="mb-1"><code><?php echo htmlspecialchars($vehicle['license_plate']); ?></code></p>
        </div>
        <?php endif; ?>
        
        <?php if ($vehicle['vin']): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">VIN:</label>
            <p class="mb-1"><code><?php echo htmlspecialchars($vehicle['vin']); ?></code></p>
        </div>
        <?php endif; ?>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Status:</label>
            <p class="mb-1">
                <?php if ($vehicle['is_default']): ?>
                    <span class="badge bg-primary">Default Vehicle</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Regular Vehicle</span>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Added:</label>
            <p class="mb-1"><?php echo format_date($vehicle['created_at']); ?></p>
        </div>
        
        <?php if (isset($vehicle['updated_at']) && $vehicle['updated_at']): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">Last Updated:</label>
            <p class="mb-1"><?php echo format_date($vehicle['updated_at']); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Recent Bookings</h6>
        
        <?php if ($booking_result->num_rows === 0): ?>
            <div class="text-center py-4">
                <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                <p class="text-muted">No bookings yet for this vehicle</p>
                <a href="index.php?page=booking" class="btn btn-primary btn-sm">Book Service</a>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php while ($booking = $booking_result->fetch_assoc()): ?>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1"><?php echo format_date($booking['appointment_date']); ?></h6>
                                <p class="mb-1 text-muted"><?php echo format_time($booking['appointment_time']); ?></p>
                                <small class="text-muted"><?php echo htmlspecialchars($booking['services']); ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge <?php echo get_status_badge_class($booking['status']); ?>">
                                    <?php echo get_status_display($booking['status']); ?>
                                </span>
                                <br>
                                <small class="text-muted"><?php echo format_currency($booking['total_amount'] ?? 0); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php if ($booking_result->num_rows >= 5): ?>
                <div class="text-center mt-3">
                    <a href="index.php?page=profile" class="btn btn-outline-primary btn-sm">View All Bookings</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-outline-primary" onclick="editVehicle(<?php echo $vehicle['id']; ?>)">
                <i class="fas fa-edit me-2"></i>Edit Vehicle
            </button>
            <a href="index.php?page=booking" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Book Service
            </a>
        </div>
    </div>
</div>
