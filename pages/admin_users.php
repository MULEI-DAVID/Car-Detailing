<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    echo '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                            <h3>Access Denied</h3>
                            <p class="text-muted">You must be logged in to access the admin dashboard.</p>
                            <a href="index.php?page=login" class="btn btn-primary">Login</a>
                        </div>
                    </div>
                </div>
            </div>
          </div>';
    return;
}

if (!is_admin()) {
    echo '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-user-shield fa-3x text-danger mb-3"></i>
                            <h3>Admin Access Required</h3>
                            <p class="text-muted">You do not have admin privileges to access this dashboard.</p>
                            <a href="index.php" class="btn btn-primary">Go to Home</a>
                        </div>
                    </div>
                </div>
            </div>
          </div>';
    return;
}

// Get all users with their statistics
$all_users_sql = "SELECT u.*,
                         COUNT(DISTINCT v.id) as vehicle_count,
                         COUNT(DISTINCT b.id) as booking_count,
                         SUM(b.total_amount) as total_spent
                  FROM users u
                  LEFT JOIN vehicles v ON u.id = v.user_id
                  LEFT JOIN bookings b ON u.id = b.user_id AND b.status = 'completed'
                  WHERE u.is_admin = 0
                  GROUP BY u.id
                  ORDER BY u.created_at DESC";
$all_users_result = $conn->query($all_users_sql);
$all_users = $all_users_result->fetch_all(MYSQLI_ASSOC);

display_flash_message();
?>

<div class="admin-dashboard">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">
                        <i class="fas fa-users me-2"></i>User Management
                    </h2>
                    <p class="text-muted">Manage all registered users and their information</p>
                </div>
                <div>
                    <a href="index.php?page=admin" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color));">
                <div class="card-body text-white">
                    <h3 class="mb-0 fw-bold"><?php echo count($all_users); ?></h3>
                    <p class="mb-0">Total Users</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--secondary-color), #FFA500);">
                <div class="card-body text-dark">
                    <h3 class="mb-0 fw-bold"><?php echo array_sum(array_column($all_users, 'vehicle_count')); ?></h3>
                    <p class="mb-0">Total Vehicles</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                <div class="card-body text-white">
                    <h3 class="mb-0 fw-bold"><?php echo array_sum(array_column($all_users, 'booking_count')); ?></h3>
                    <p class="mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--warning-color), #FFD700);">
                <div class="card-body text-dark">
                    <h3 class="mb-0 fw-bold"><?php echo format_currency(array_sum(array_column($all_users, 'total_spent'))); ?></h3>
                    <p class="mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Users</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportUsers()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addUser()">
                            <i class="fas fa-plus me-1"></i>Add User
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($all_users)): ?>
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
                                    <?php foreach ($all_users as $user): ?>
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
                                                    <button type="button" class="btn btn-outline-warning" onclick="editUser(<?php echo $user['id']; ?>)">
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewUser(userId) {
    // Create modal content for viewing user details
    const modalContent = `
        <div class="modal fade" id="viewUserModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">User Details #${userId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="userDetailsContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading user details...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="editUser(${userId})">Edit User</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('viewUserModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    modal.show();
    
    // Load user details via AJAX
    fetch(`ajax/get_user_details.php?id=${userId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('userDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('userDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading user details: ${error.message}
                </div>
            `;
        });
}

function viewUserVehicles(userId) {
    // Create modal content for viewing user vehicles
    const modalContent = `
        <div class="modal fade" id="viewUserVehiclesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">User Vehicles #${userId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="userVehiclesContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading vehicles...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('viewUserVehiclesModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewUserVehiclesModal'));
    modal.show();
    
    // Load user vehicles via AJAX
    fetch(`ajax/get_user_vehicles.php?id=${userId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('userVehiclesContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('userVehiclesContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading vehicles: ${error.message}
                </div>
            `;
        });
}

function viewUserBookings(userId) {
    // Create modal content for viewing user bookings
    const modalContent = `
        <div class="modal fade" id="viewUserBookingsModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">User Bookings #${userId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="userBookingsContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading bookings...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('viewUserBookingsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewUserBookingsModal'));
    modal.show();
    
    // Load user bookings via AJAX
    fetch(`ajax/get_user_bookings.php?id=${userId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('userBookingsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('userBookingsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading bookings: ${error.message}
                </div>
            `;
        });
}

function editUser(userId) {
    // Create modal content for editing user
    const modalContent = `
        <div class="modal fade" id="editUserModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User #${userId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="editUserContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading user form...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveUserChanges()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('editUserModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
    
    // Load user edit form via AJAX
    fetch(`ajax/get_user_edit_form.php?id=${userId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editUserContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('editUserContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading user form: ${error.message}
                </div>
            `;
        });
}

function saveUserChanges() {
    const form = document.getElementById('editUserForm');
    if (!form) {
        alert('Edit form not found!');
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('ajax/update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            location.reload();
        } else {
            alert('Error updating user: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error updating user: ' + error.message);
    });
}

function addUser() {
    // Create modal content for adding user
    const modalContent = `
        <div class="modal fade" id="addUserModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="addUserContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading form...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveNewUser()">Add User</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('addUserModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    modal.show();
    
    // Load add user form via AJAX
    fetch('ajax/get_add_user_form.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('addUserContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('addUserContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading form: ${error.message}
                </div>
            `;
        });
}

function saveNewUser() {
    const form = document.getElementById('addUserForm');
    if (!form) {
        alert('Add form not found!');
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('ajax/add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            location.reload();
        } else {
            alert('Error adding user: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error adding user: ' + error.message);
    });
}

function exportUsers() {
    // Create a temporary form to submit the export request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'ajax/export_users.php';
    
    // Add CSRF token if needed
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
