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

// Get user data
$sql = "SELECT * FROM users WHERE id = ? AND is_admin = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">User not found.</div>';
    exit();
}

$user = $result->fetch_assoc();
?>

<form id="editUserForm" method="POST" action="ajax/update_user.php">
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="full_name" name="full_name" 
                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number *</label>
                <input type="tel" class="form-control" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="new_password" name="new_password" 
                       minlength="6" placeholder="Enter new password">
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="email_verified" class="form-label">Email Verification Status</label>
                <select class="form-select" id="email_verified" name="email_verified">
                    <option value="1" <?php echo (isset($user['email_verified']) && $user['email_verified']) ? 'selected' : ''; ?>>Verified</option>
                    <option value="0" <?php echo (!isset($user['email_verified']) || !$user['email_verified']) ? 'selected' : ''; ?>>Pending</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="admin_status" class="form-label">Admin Status</label>
                <select class="form-select" id="admin_status" name="admin_status" disabled>
                    <option value="0" <?php echo (isset($user['is_admin']) && !$user['is_admin']) ? 'selected' : ''; ?>>Regular User</option>
                    <option value="1" <?php echo (isset($user['is_admin']) && $user['is_admin']) ? 'selected' : ''; ?>>Admin User</option>
                </select>
                <small class="text-muted">Admin status cannot be changed from this form</small>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Note:</strong> Changing the email verification status will affect the user's ability to access certain features.
        Setting a new password will immediately update the user's login credentials.
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update User
        </button>
    </div>
</form>

<script>
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('ajax/update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'User updated successfully!');
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            // Refresh the page to show updated data
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', data.message || 'Failed to update user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while updating the user');
    });
});
</script>
