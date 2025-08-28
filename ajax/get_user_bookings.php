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

// Get user bookings with vehicle and service details
$sql = "SELECT b.*, v.nickname as vehicle_nickname, v.make, v.model, v.year, v.license_plate,
               GROUP_CONCAT(s.name SEPARATOR ', ') as services
        FROM bookings b
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN booking_services bs ON b.id = bs.booking_id
        LEFT JOIN services s ON bs.service_id = s.id
        WHERE b.user_id = ?
        GROUP BY b.id
        ORDER BY b.appointment_date DESC, b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-info">No bookings found for this user.</div>';
    exit();
}
?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Date & Time</th>
                <th>Vehicle</th>
                <th>Services</th>
                <th>Total</th>
                <th>Status</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = $result->fetch_assoc()): ?>
            <tr>
                <td><strong>#<?php echo $booking['id']; ?></strong></td>
                <td>
                    <?php echo format_date($booking['appointment_date']); ?><br>
                    <small class="text-muted"><?php echo $booking['appointment_time']; ?></small>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($booking['vehicle_nickname']); ?></strong><br>
                    <small class="text-muted">
                        <?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['year'] . ')'); ?>
                    </small>
                </td>
                <td>
                    <small><?php echo htmlspecialchars($booking['services'] ?: 'No services'); ?></small>
                </td>
                                                <td><strong class="text-primary"><?php echo format_currency($booking['total_amount'] ?? 0); ?></strong></td>
                <td>
                    <?php
                    $status_class = '';
                    switch ($booking['status']) {
                        case 'pending': $status_class = 'bg-warning'; break;
                        case 'confirmed': $status_class = 'bg-info'; break;
                        case 'in_progress': $status_class = 'bg-primary'; break;
                        case 'completed': $status_class = 'bg-success'; break;
                        case 'cancelled': $status_class = 'bg-danger'; break;
                        default: $status_class = 'bg-secondary';
                    }
                    ?>
                    <span class="badge <?php echo $status_class; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                    </span>
                </td>
                <td>
                    <?php if ($booking['service_type'] === 'mobile'): ?>
                        <span class="badge bg-info">Mobile</span><br>
                        <small class="text-muted"><?php echo htmlspecialchars($booking['location']); ?></small>
                    <?php else: ?>
                        <span class="badge bg-secondary">Facility</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
