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

// Get user vehicles
$sql = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-info">No vehicles found for this user.</div>';
    exit();
}
?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nickname</th>
                <th>Make & Model</th>
                <th>Year</th>
                <th>Type</th>
                <th>Color</th>
                <th>License Plate</th>
                <th>Default</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($vehicle = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($vehicle['nickname']); ?></strong>
                    <?php if ($vehicle['vin']): ?>
                        <br><small class="text-muted">VIN: <?php echo htmlspecialchars($vehicle['vin']); ?></small>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['year']); ?></td>
                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($vehicle['type']); ?></span></td>
                <td>
                    <span class="badge" style="background-color: <?php echo htmlspecialchars($vehicle['color']); ?>; color: white;">
                        <?php echo htmlspecialchars($vehicle['color']); ?>
                    </span>
                </td>
                <td><code><?php echo htmlspecialchars($vehicle['license_plate']); ?></code></td>
                <td>
                    <?php if ($vehicle['is_default']): ?>
                        <span class="badge bg-success">Default</span>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
