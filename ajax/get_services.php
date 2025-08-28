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

// Get all services
$sql = "SELECT s.*, COUNT(bs.id) as booking_count
        FROM services s
        LEFT JOIN booking_services bs ON s.id = bs.service_id
        GROUP BY s.id
        ORDER BY s.category, s.price";

$result = $conn->query($sql);
$services = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php if (empty($services)): ?>
    <div class="text-center py-4">
        <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No services found</h5>
        <p class="text-muted">There are no services configured yet.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Service ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo $service['id']; ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($service['description']); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $service['category'] == 'package' ? 'primary' : 'success'; ?>">
                                <?php echo ucfirst($service['category']); ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo format_currency($service['price']); ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $service['duration']; ?> min</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $service['booking_count']; ?> bookings</span>
                        </td>
                        <td>
                            <?php if ($service['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="editService(<?php echo $service['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-<?php echo $service['is_active'] ? 'warning' : 'success'; ?>" 
                                        onclick="toggleServiceStatus(<?php echo $service['id']; ?>, <?php echo $service['is_active'] ? 'false' : 'true'; ?>)">
                                    <i class="fas fa-<?php echo $service['is_active'] ? 'pause' : 'play'; ?>"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteService(<?php echo $service['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
