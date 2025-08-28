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
    echo '<div class="alert alert-danger">Booking ID is required.</div>';
    exit();
}

$booking_id = (int)$_GET['id'];

// Get detailed booking information
$sql = "SELECT b.*, u.full_name, u.email, u.phone, v.nickname as vehicle_nickname, v.make, v.model, v.year, v.color, v.license_plate, v.vin,
               GROUP_CONCAT(s.name SEPARATOR ', ') as services,
               GROUP_CONCAT(s.price SEPARATOR ', ') as service_prices
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN booking_services bs ON b.id = bs.booking_id
        LEFT JOIN services s ON bs.service_id = s.id
        WHERE b.id = ?
        GROUP BY b.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Booking not found.</div>';
    exit();
}

$booking = $result->fetch_assoc();
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-user me-2"></i>Customer Information</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-car me-2"></i>Vehicle Information</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Nickname:</strong> <?php echo htmlspecialchars($booking['vehicle_nickname']); ?></p>
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['year'] . ' ' . $booking['make'] . ' ' . $booking['model']); ?></p>
                <p><strong>Color:</strong> <?php echo htmlspecialchars($booking['color']); ?></p>
                <?php if ($booking['license_plate']): ?>
                    <p><strong>License Plate:</strong> <?php echo htmlspecialchars($booking['license_plate']); ?></p>
                <?php endif; ?>
                <?php if ($booking['vin']): ?>
                    <p><strong>VIN:</strong> <?php echo htmlspecialchars($booking['vin']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-calendar me-2"></i>Booking Details</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                <p><strong>Date:</strong> <?php echo format_date($booking['appointment_date']); ?></p>
                <p><strong>Time:</strong> <?php echo format_time($booking['appointment_time']); ?></p>
                <p><strong>Type:</strong> 
                    <span class="badge bg-<?php echo $booking['service_type'] == 'mobile' ? 'info' : 'secondary'; ?>">
                        <?php echo ucfirst($booking['service_type']); ?>
                    </span>
                </p>
                <p><strong>Status:</strong> 
                    <span class="badge <?php echo get_status_badge_class($booking['status']); ?>">
                        <?php echo get_status_display($booking['status']); ?>
                    </span>
                </p>
                <?php if ($booking['location']): ?>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['location']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary"><i class="fas fa-cogs me-2"></i>Services & Pricing</h6>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Services:</strong> <?php echo htmlspecialchars($booking['services'] ?: 'No services selected'); ?></p>
                <p><strong>Subtotal:</strong> <?php echo format_currency($booking['subtotal'] ?? 0); ?></p>
                <p><strong>Tax:</strong> <?php echo format_currency($booking['tax_amount'] ?? 0); ?></p>
                <p><strong>Total:</strong> <strong class="text-primary"><?php echo format_currency($booking['total_amount'] ?? 0); ?></strong></p>
            </div>
        </div>
    </div>
</div>

<?php if ($booking['admin_notes']): ?>
<div class="row">
    <div class="col-12">
        <h6 class="text-primary"><i class="fas fa-sticky-note me-2"></i>Admin Notes</h6>
        <div class="card">
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($booking['admin_notes'])); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p><strong>Created:</strong> <?php echo format_date($booking['created_at']); ?></p>
                <?php if (isset($booking['updated_at']) && $booking['updated_at']): ?>
                    <p><strong>Last Updated:</strong> <?php echo format_date($booking['updated_at']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
