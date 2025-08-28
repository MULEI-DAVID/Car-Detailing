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
    echo '<div class="alert alert-danger">Booking ID is required.</div>';
    exit();
}

$booking_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get booking data (ensure it belongs to the logged-in user)
$sql = "SELECT b.*, v.nickname as vehicle_nickname, v.make, v.model, v.year, v.type, v.color, v.license_plate,
               GROUP_CONCAT(s.name SEPARATOR ', ') as services,
               GROUP_CONCAT(s.price SEPARATOR ', ') as service_prices
        FROM bookings b
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN booking_services bs ON b.id = bs.booking_id
        LEFT JOIN services s ON bs.service_id = s.id
        WHERE b.id = ? AND b.user_id = ?
        GROUP BY b.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Booking not found or access denied.</div>';
    exit();
}

$booking = $result->fetch_assoc();

// Get individual services for this booking
$services_sql = "SELECT s.name, s.description, s.duration, bs.price
                 FROM booking_services bs
                 LEFT JOIN services s ON bs.service_id = s.id
                 WHERE bs.booking_id = ?
                 ORDER BY s.category DESC, s.name ASC";
$services_stmt = $conn->prepare($services_sql);
$services_stmt->bind_param("i", $booking_id);
$services_stmt->execute();
$services_result = $services_stmt->get_result();
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Booking Information</h6>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Booking ID:</label>
            <p class="mb-1"><code>#<?php echo $booking['id']; ?></code></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Date & Time:</label>
            <p class="mb-1">
                <strong><?php echo format_date($booking['appointment_date']); ?></strong><br>
                <small class="text-muted"><?php echo format_time($booking['appointment_time']); ?></small>
            </p>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Service Type:</label>
            <p class="mb-1">
                <span class="badge bg-<?php echo $booking['service_type'] == 'mobile' ? 'success' : 'info'; ?>">
                    <?php echo ucfirst($booking['service_type']); ?> Service
                </span>
            </p>
        </div>
        
        <?php if ($booking['location']): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">Location:</label>
            <p class="mb-1"><?php echo htmlspecialchars($booking['location']); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Status:</label>
            <p class="mb-1">
                <span class="badge <?php echo get_status_badge_class($booking['status']); ?>">
                    <?php echo get_status_display($booking['status']); ?>
                </span>
            </p>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Total Amount:</label>
            <p class="mb-1">
                <strong class="text-success fs-5"><?php echo format_currency($booking['total_amount'] ?? 0); ?></strong>
            </p>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">Booked On:</label>
            <p class="mb-1"><?php echo format_date($booking['created_at']); ?></p>
        </div>
        
        <?php if (isset($booking['updated_at']) && $booking['updated_at']): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">Last Updated:</label>
            <p class="mb-1"><?php echo format_date($booking['updated_at']); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Vehicle Details</h6>
        
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="card-title"><?php echo htmlspecialchars($booking['vehicle_nickname']); ?></h6>
                <p class="card-text text-muted mb-2">
                    <?php echo htmlspecialchars($booking['year'] . ' ' . $booking['make'] . ' ' . $booking['model']); ?>
                </p>
                <p class="card-text text-muted mb-2">
                    Type: <?php echo htmlspecialchars($booking['type']); ?>
                    <?php if ($booking['color']): ?>
                        | Color: <?php echo htmlspecialchars($booking['color']); ?>
                    <?php endif; ?>
                </p>
                <?php if ($booking['license_plate']): ?>
                    <p class="card-text text-muted mb-0">
                        Plate: <code><?php echo htmlspecialchars($booking['license_plate']); ?></code>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($booking['admin_notes']): ?>
        <div class="mt-3">
            <h6 class="text-primary mb-2">Admin Notes</h6>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo nl2br(htmlspecialchars($booking['admin_notes'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-primary mb-3">Services Booked</h6>
        
        <?php if ($services_result->num_rows === 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No services found for this booking.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th class="text-end">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($service = $services_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($service['name']); ?></strong></td>
                                <td>
                                    <?php if ($service['description']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($service['description']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($service['duration']): ?>
                                        <span class="badge bg-secondary"><?php echo $service['duration']; ?> min</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <strong><?php echo format_currency($service['price'] ?? 0); ?></strong>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="3">Total</th>
                            <th class="text-end">
                                <strong><?php echo format_currency($booking['total_amount'] ?? 0); ?></strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                <button type="button" class="btn btn-outline-primary" onclick="changeVehicle(<?php echo $booking['id']; ?>)">
                    <i class="fas fa-car me-2"></i>Change Vehicle
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-outline-secondary" disabled>
                    <i class="fas fa-car me-2"></i>Change Vehicle
                </button>
            <?php endif; ?>
            
            <a href="index.php?page=booking" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Book Another Service
            </a>
        </div>
    </div>
</div>
