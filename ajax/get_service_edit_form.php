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

// Get service information
$sql = "SELECT * FROM services WHERE id = ?";
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

<form id="editServiceForm">
    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
    
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary"><i class="fas fa-cogs me-2"></i>Service Details</h6>
            <div class="mb-3">
                <label for="name" class="form-label">Service Name</label>
                <input type="text" class="form-control" name="name" id="name" 
                       value="<?php echo htmlspecialchars($service['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" name="category" id="category" required>
                    <option value="package" <?php echo $service['category'] == 'package' ? 'selected' : ''; ?>>Package</option>
                    <option value="addon" <?php echo $service['category'] == 'addon' ? 'selected' : ''; ?>>Add-on</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price (KES)</label>
                <input type="number" class="form-control" name="price" id="price" 
                       value="<?php echo $service['price']; ?>" step="0.01" min="0" required>
            </div>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-primary"><i class="fas fa-clock me-2"></i>Service Settings</h6>
            <div class="mb-3">
                <label for="duration" class="form-label">Duration (minutes)</label>
                <input type="number" class="form-control" name="duration" id="duration" 
                       value="<?php echo $service['duration']; ?>" min="1" required>
            </div>
            <div class="mb-3">
                <label for="is_active" class="form-label">Status</label>
                <select class="form-select" name="is_active" id="is_active" required>
                    <option value="1" <?php echo $service['is_active'] ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo !$service['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-primary"><i class="fas fa-align-left me-2"></i>Description</h6>
            <div class="mb-3">
                <textarea class="form-control" name="description" id="description" rows="4" 
                          placeholder="Enter service description..."><?php echo htmlspecialchars($service['description']); ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Service Information</h6>
            <div class="alert alert-info">
                <p><strong>Service ID:</strong> #<?php echo $service['id']; ?></p>
                <p><strong>Created:</strong> <?php echo format_date($service['created_at']); ?></p>
                <?php if (isset($service['updated_at']) && $service['updated_at']): ?>
                    <p><strong>Last Updated:</strong> <?php echo format_date($service['updated_at']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>
