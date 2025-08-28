<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize database connection
$conn = getDatabaseConnection();

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo 'Access denied';
    exit();
}

// Get all users (excluding admins)
$sql = "SELECT u.*, 
               COUNT(DISTINCT v.id) as vehicle_count,
               COUNT(DISTINCT b.id) as booking_count,
               SUM(b.total_amount) as total_spent
        FROM users u
        LEFT JOIN vehicles v ON u.id = v.user_id
        LEFT JOIN bookings b ON u.id = b.user_id AND b.status = 'completed'
        WHERE u.is_admin = 0
        GROUP BY u.id
        ORDER BY u.created_at DESC";

$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php if (empty($users)): ?>
    <div class="text-center py-4">
        <i class="fas fa-users fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No users found</h5>
        <p class="text-muted">There are no registered users yet.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Vehicles</th>
                    <th>Bookings</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $user['id']; ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                        </td>
                        <td>
                            <div><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($user['email']); ?></div>
                            <div><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($user['phone']); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $user['vehicle_count']; ?> vehicles</span>
                        </td>
                        <td>
                            <span class="badge bg-success"><?php echo $user['booking_count']; ?> bookings</span>
                        </td>
                        <td>
                            <strong><?php echo format_currency($user['total_spent'] ?? 0); ?></strong>
                        </td>
                        <td>
                            <small><?php echo format_date($user['created_at']); ?></small>
                        </td>
                        <td>
                            <?php if ($user['email_verified']): ?>
                                <span class="badge bg-success">Verified</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="viewUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="viewUserVehicles(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-car"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="viewUserBookings(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-calendar"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
