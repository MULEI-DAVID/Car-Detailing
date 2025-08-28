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

// Handle service status updates
if (isset($_POST['toggle_status']) && isset($_POST['service_id'])) {
    $service_id = (int)$_POST['service_id'];
    $new_status = (int)$_POST['new_status'];
    
    $update_sql = "UPDATE services SET is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $new_status, $service_id);
    
    if ($stmt->execute()) {
        set_flash_message('Service status updated successfully!', 'success');
    } else {
        set_flash_message('Error updating service status.', 'danger');
    }
    
    // Redirect to prevent form resubmission
    header('Location: index.php?page=admin_services');
    exit();
}

// Get all services with usage statistics
$all_services_sql = "SELECT s.*, COUNT(bs.id) as booking_count
                     FROM services s
                     LEFT JOIN booking_services bs ON s.id = bs.service_id
                     GROUP BY s.id
                     ORDER BY s.category, s.price";
$all_services_result = $conn->query($all_services_sql);
$all_services = $all_services_result->fetch_all(MYSQLI_ASSOC);

display_flash_message();
?>

<div class="admin-dashboard">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">
                        <i class="fas fa-cogs me-2"></i>Service Management
                    </h2>
                    <p class="text-muted">Manage all services and their pricing</p>
                </div>
                <div>
                    <a href="index.php?page=admin" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color));">
                <div class="card-body text-white">
                    <h3 class="mb-0 fw-bold"><?php echo count($all_services); ?></h3>
                    <p class="mb-0">Total Services</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--secondary-color), #FFA500);">
                <div class="card-body text-dark">
                    <h3 class="mb-0 fw-bold"><?php echo count(array_filter($all_services, function($s) { return $s['is_active']; })); ?></h3>
                    <p class="mb-0">Active Services</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                <div class="card-body text-white">
                    <h3 class="mb-0 fw-bold"><?php echo array_sum(array_column($all_services, 'booking_count')); ?></h3>
                    <p class="mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--warning-color), #FFD700);">
                <div class="card-body text-dark">
                    <h3 class="mb-0 fw-bold"><?php echo count(array_unique(array_column($all_services, 'category'))); ?></h3>
                    <p class="mb-0">Categories</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Services</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addService()">
                            <i class="fas fa-plus me-1"></i>Add Service
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportServices()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($all_services)): ?>
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
                                    <?php foreach ($all_services as $service): ?>
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
                                                    <button type="button" class="btn btn-outline-primary" onclick="viewService(<?php echo $service['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success" onclick="editService(<?php echo $service['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-<?php echo $service['is_active'] ? 'warning' : 'success'; ?>"
                                                            onclick="toggleServiceStatus(<?php echo $service['id']; ?>, <?php echo $service['is_active'] ? 0 : 1; ?>)">
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Status Toggle Form -->
<form method="POST" id="toggleForm">
    <input type="hidden" name="service_id" id="service_id">
    <input type="hidden" name="new_status" id="new_status">
    <input type="hidden" name="toggle_status" value="1">
</form>

<script>
function viewService(serviceId) {
    // Create modal content for viewing service details
    const modalContent = `
        <div class="modal fade" id="viewServiceModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Service Details #${serviceId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="serviceDetailsContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading service details...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="editService(${serviceId})">Edit Service</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('viewServiceModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewServiceModal'));
    modal.show();
    
    // Load service details via AJAX
    fetch(`ajax/get_service_details.php?id=${serviceId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('serviceDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('serviceDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading service details: ${error.message}
                </div>
            `;
        });
}

function editService(serviceId) {
    // Create modal content for editing service
    const modalContent = `
        <div class="modal fade" id="editServiceModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Service #${serviceId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="editServiceContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading service form...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveServiceChanges()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('editServiceModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editServiceModal'));
    modal.show();
    
    // Load service edit form via AJAX
    fetch(`ajax/get_service_edit_form.php?id=${serviceId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editServiceContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('editServiceContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading service form: ${error.message}
                </div>
            `;
        });
}

function saveServiceChanges() {
    const form = document.getElementById('editServiceForm');
    if (!form) {
        alert('Edit form not found!');
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('ajax/update_service.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('editServiceModal')).hide();
            location.reload();
        } else {
            alert('Error updating service: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error updating service: ' + error.message);
    });
}

function toggleServiceStatus(serviceId, newStatus) {
    if (confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this service?')) {
        document.getElementById('service_id').value = serviceId;
        document.getElementById('new_status').value = newStatus;
        document.getElementById('toggleForm').submit();
    }
}

function deleteService(serviceId) {
    if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
        fetch('ajax/delete_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                service_id: serviceId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting service: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error deleting service: ' + error.message);
        });
    }
}

function addService() {
    // Create modal content for adding service
    const modalContent = `
        <div class="modal fade" id="addServiceModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="addServiceContent">
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
                        <button type="button" class="btn btn-primary" onclick="saveNewService()">Add Service</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('addServiceModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
    modal.show();
    
    // Load add service form via AJAX
    fetch('ajax/get_add_service_form.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('addServiceContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('addServiceContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading form: ${error.message}
                </div>
            `;
        });
}

function saveNewService() {
    const form = document.getElementById('addServiceForm');
    if (!form) {
        alert('Add form not found!');
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('ajax/add_service.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('addServiceModal')).hide();
            location.reload();
        } else {
            alert('Error adding service: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error adding service: ' + error.message);
    });
}

function exportServices() {
    // Create a temporary form to submit the export request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'ajax/export_services.php';
    
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
