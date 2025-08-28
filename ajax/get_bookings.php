<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize database connection
$conn = getDatabaseConnection();
if (!$conn) {
    http_response_code(500);
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Database connection failed.</div>';
    exit();
}

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Access denied. Admin privileges required.</div>';
    exit();
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on status filter
$sql = "SELECT b.*, u.full_name, u.email, u.phone, v.nickname as vehicle_nickname, v.make, v.model, v.year, v.color, v.license_plate,
               GROUP_CONCAT(s.name SEPARATOR ', ') as services
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN booking_services bs ON b.id = bs.booking_id
        LEFT JOIN services s ON bs.service_id = s.id";

if ($status_filter !== 'all') {
    $sql .= " WHERE b.status = ?";
}

$sql .= " GROUP BY b.id ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Database query preparation failed.</div>';
    exit();
}

if ($status_filter !== 'all') {
    $stmt->bind_param("s", $status_filter);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Database query execution failed.</div>';
    exit();
}

$result = $stmt->get_result();
if (!$result) {
    http_response_code(500);
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to get query result.</div>';
    exit();
}

$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php if (empty($bookings)): ?>
    <div class="text-center py-4">
        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No bookings found</h5>
        <p class="text-muted">There are no bookings matching your criteria.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Services</th>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $booking['id']; ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small><br>
                            <small class="text-muted"><?php echo htmlspecialchars($booking['phone']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($booking['vehicle_nickname']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($booking['year'] . ' ' . $booking['make'] . ' ' . $booking['model']); ?></small><br>
                            <small class="text-muted"><?php echo htmlspecialchars($booking['color']); ?></small>
                            <?php if ($booking['license_plate']): ?>
                                <br><small class="text-muted">Plate: <?php echo htmlspecialchars($booking['license_plate']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($booking['services']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo format_date($booking['appointment_date']); ?></strong><br>
                            <small class="text-muted"><?php echo format_time($booking['appointment_time']); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $booking['service_type'] == 'mobile' ? 'info' : 'secondary'; ?>">
                                <?php echo ucfirst($booking['service_type']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo get_status_badge_class($booking['status']); ?>">
                                <?php echo get_status_display($booking['status']); ?>
                            </span>
                        </td>
                        <td>
                                                            <strong><?php echo format_currency($booking['total_amount'] ?? 0); ?></strong>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="editBooking(<?php echo $booking['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
