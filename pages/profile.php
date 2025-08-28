<?php
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

$conn = getDatabaseConnection();
$user = get_current_user_data();
$vehicles = get_user_vehicles($_SESSION['user_id']);
$bookings = get_user_bookings($_SESSION['user_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize_input($_POST['full_name']);
    $phone = sanitize_input($_POST['phone']);
    
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($errors)) {
        $update_sql = "UPDATE users SET full_name = ?, phone = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $full_name, $phone, $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            set_flash_message('success', 'Profile updated successfully!');
            header('Location: index.php?page=profile');
            exit();
        } else {
            $errors[] = "Error updating profile. Please try again.";
        }
    }
}

// Handle vehicle operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_vehicle'])) {
        $nickname = sanitize_input($_POST['nickname']);
        $make = sanitize_input($_POST['make']);
        $model = sanitize_input($_POST['model']);
        $year = sanitize_input($_POST['year']);
        $type = sanitize_input($_POST['type']);
        $color = sanitize_input($_POST['color']);
        $license_plate = sanitize_input($_POST['license_plate']);
        $vin = sanitize_input($_POST['vin']);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        $errors = [];
        
        if (empty($nickname)) $errors[] = "Vehicle nickname is required";
        if (empty($make)) $errors[] = "Vehicle make is required";
        if (empty($model)) $errors[] = "Vehicle model is required";
        if (empty($year)) $errors[] = "Vehicle year is required";
        if (empty($type)) $errors[] = "Vehicle type is required";
        
        if (empty($errors)) {
            // If this is set as default, unset other defaults
            if ($is_default) {
                $conn->query("UPDATE vehicles SET is_default = 0 WHERE user_id = " . $_SESSION['user_id']);
            }
            
            $insert_sql = "INSERT INTO vehicles (user_id, nickname, make, model, year, type, color, license_plate, vin, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("issssssssi", $_SESSION['user_id'], $nickname, $make, $model, $year, $type, $color, $license_plate, $vin, $is_default);
            
            if ($insert_stmt->execute()) {
                set_flash_message('success', 'Vehicle added successfully!');
                header('Location: index.php?page=profile');
                exit();
            } else {
                $errors[] = "Error adding vehicle. Please try again.";
            }
        }
    }
    
    if (isset($_POST['delete_vehicle'])) {
        $vehicle_id = (int)$_POST['vehicle_id'];
        
        // Check if vehicle belongs to user
        $check_sql = "SELECT id FROM vehicles WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $vehicle_id, $_SESSION['user_id']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 1) {
            $delete_sql = "DELETE FROM vehicles WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $vehicle_id);
            
            if ($delete_stmt->execute()) {
                set_flash_message('success', 'Vehicle removed successfully!');
                header('Location: index.php?page=profile');
                exit();
            } else {
                $errors[] = "Error removing vehicle. Please try again.";
            }
        } else {
            $errors[] = "Invalid vehicle.";
        }
    }
    
    if (isset($_POST['set_default_vehicle'])) {
        $vehicle_id = (int)$_POST['vehicle_id'];
        
        // Check if vehicle belongs to user
        $check_sql = "SELECT id FROM vehicles WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $vehicle_id, $_SESSION['user_id']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 1) {
            // Unset all defaults first
            $conn->query("UPDATE vehicles SET is_default = 0 WHERE user_id = " . $_SESSION['user_id']);
            
            // Set new default
            $update_sql = "UPDATE vehicles SET is_default = 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $vehicle_id);
            
            if ($update_stmt->execute()) {
                set_flash_message('success', 'Default vehicle updated!');
                header('Location: index.php?page=profile');
                exit();
            } else {
                $errors[] = "Error updating default vehicle. Please try again.";
            }
        } else {
            $errors[] = "Invalid vehicle.";
        }
    }
}

display_flash_message();
?>

<style>
.profile-avatar {
    padding: 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: inline-block;
}

.profile-info h6 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.profile-info p {
    font-size: 1rem;
    font-weight: 500;
    color: #212529;
    margin-bottom: 1rem;
}

.card-header .btn {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.form-text {
    font-size: 0.75rem;
}

.alert {
    font-size: 0.875rem;
}

.modal-body .alert {
    margin-bottom: 1rem;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Enhanced profile card styling */
.card.border-primary {
    border-width: 2px !important;
}

.card.border-light {
    border-width: 1px !important;
}

/* Loading spinner for buttons */
.btn .fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Form validation styling */
.form-control:invalid {
    border-color: #dc3545;
}

.form-control:valid {
    border-color: #198754;
}

/* Toast container positioning */
.toast-container {
    z-index: 9999;
}
</style>

<div class="row">
    <div class="col-lg-4">
        <!-- Profile Information -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                <button type="button" class="btn btn-light btn-sm" onclick="editProfile()">
                    <i class="fas fa-edit me-1"></i>Edit Profile
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle fa-4x text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="profile-info">
                            <h6 class="text-muted mb-1">Full Name</h6>
                            <p class="mb-3" id="display-full-name"><?php echo htmlspecialchars($user['full_name']); ?></p>
                            
                            <h6 class="text-muted mb-1">Email</h6>
                            <p class="mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            
                            <h6 class="text-muted mb-1">Phone</h6>
                            <p class="mb-3" id="display-phone"><?php echo htmlspecialchars($user['phone']); ?></p>
                            
                            <h6 class="text-muted mb-1">Member Since</h6>
                            <p class="mb-3"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                            
                            <h6 class="text-muted mb-1">Last Updated</h6>
                            <p class="mb-0"><?php echo isset($user['updated_at']) ? date('F j, Y', strtotime($user['updated_at'])) : 'Never'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Settings -->
        <div class="card shadow mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-1">Change Password</h6>
                        <p class="text-muted mb-0 small">Update your account password for better security</p>
                    </div>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="changePassword()">
                        <i class="fas fa-key me-1"></i>Change Password
                    </button>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Account Status</h6>
                        <p class="text-muted mb-0 small">Your account is active and verified</p>
                    </div>
                    <span class="badge bg-success">Active</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary"><?php echo count($vehicles); ?></h4>
                        <p class="text-muted mb-0">Vehicles</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success"><?php echo count($bookings); ?></h4>
                        <p class="text-muted mb-0">Bookings</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- My Garage -->
        <div class="card shadow mb-4" id="garage">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-car me-2"></i>My Garage</h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                    <i class="fas fa-plus me-1"></i>Add Vehicle
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($vehicles)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-car fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No vehicles in your garage</h5>
                        <p class="text-muted">Add your first vehicle to get started with booking services.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                            Add Your First Vehicle
                        </button>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($vehicles as $vehicle): ?>
                                                         <div class="col-md-6 mb-3">
                                 <div class="card border-<?php echo $vehicle['is_default'] ? 'primary' : 'light'; ?> h-100" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0"><?php echo htmlspecialchars($vehicle['nickname']); ?></h6>
                                            <?php if ($vehicle['is_default']): ?>
                                                <span class="badge bg-primary">Default</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="card-text text-muted mb-2">
                                            <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>
                                        </p>
                                        <p class="card-text text-muted mb-2">
                                            Type: <?php echo htmlspecialchars($vehicle['type']); ?>
                                            <?php if ($vehicle['color']): ?>
                                                | Color: <?php echo htmlspecialchars($vehicle['color']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($vehicle['license_plate']): ?>
                                            <p class="card-text text-muted mb-2">
                                                Plate: <?php echo htmlspecialchars($vehicle['license_plate']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group btn-group-sm w-100">
                                            <button type="button" class="btn btn-outline-info" onclick="viewVehicleDetails(<?php echo $vehicle['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </button>
                                            <?php if (!$vehicle['is_default']): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                    <button type="submit" name="set_default_vehicle" class="btn btn-outline-primary">Set Default</button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this vehicle?')">
                                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                <button type="submit" name="delete_vehicle" class="btn btn-outline-danger">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- My Appointments -->
        <div class="card shadow" id="appointments">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>My Appointments</h5>
            </div>
            <div class="card-body">
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No appointments yet</h5>
                        <p class="text-muted">Book your first car detailing service to get started.</p>
                        <a href="index.php?page=booking" class="btn btn-primary">Book Now</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Vehicle</th>
                                    <th>Services</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo format_date($booking['appointment_date']); ?></strong><br>
                                            <small class="text-muted"><?php echo format_time($booking['appointment_time']); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($booking['vehicle_nickname']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['year'] . ' ' . $booking['make'] . ' ' . $booking['model']); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($booking['services']); ?></small>
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
                                                <button type="button" class="btn btn-outline-info" onclick="viewBookingDetails(<?php echo $booking['id']; ?>)">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </button>
                                                <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                                    <button type="button" class="btn btn-outline-primary" onclick="changeVehicle(<?php echo $booking['id']; ?>)">
                                                        Change Vehicle
                                                    </button>
                                                <?php endif; ?>
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

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nickname" class="form-label">Vehicle Nickname *</label>
                        <input type="text" class="form-control" id="nickname" name="nickname" placeholder="e.g., My Daily Driver, Sarah's SUV" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="make" class="form-label">Make *</label>
                                <input type="text" class="form-control" id="make" name="make" placeholder="e.g., Honda" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model" class="form-label">Model *</label>
                                <input type="text" class="form-control" id="model" name="model" placeholder="e.g., CR-V" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="year" class="form-label">Year *</label>
                                <input type="number" class="form-control" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="Sedan">Sedan</option>
                                    <option value="SUV">SUV</option>
                                    <option value="Truck">Truck</option>
                                    <option value="Van">Van</option>
                                    <option value="Coupe">Coupe</option>
                                    <option value="Wagon">Wagon</option>
                                    <option value="Hatchback">Hatchback</option>
                                    <option value="Convertible">Convertible</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color" placeholder="e.g., Black">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="license_plate" class="form-label">License Plate</label>
                                <input type="text" class="form-control" id="license_plate" name="license_plate" placeholder="e.g., ABC123">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vin" class="form-label">VIN (Optional)</label>
                        <input type="text" class="form-control" id="vin" name="vin" placeholder="17-character VIN">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">
                            Set as default vehicle
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Vehicle Details Modal -->
<div class="modal fade" id="vehicleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vehicle Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="vehicleDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading vehicle details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading booking details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Vehicle Modal -->
<div class="modal fade" id="changeVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Vehicle for Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select a different vehicle for this booking:</p>
                <div id="vehicleOptions">
                    <!-- Vehicle options will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmVehicleChange()">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-full-name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="edit-full-name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                <div class="form-text">Enter your full name (letters, spaces, hyphens, and periods only)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="edit-phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                <div class="form-text">Enter a valid phone number</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="edit-email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        <div class="form-text text-warning">Email address cannot be changed for security reasons</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Your profile information will be used for booking confirmations and communications.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProfile()">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label for="current-password" class="form-label">Current Password *</label>
                        <input type="password" class="form-control" id="current-password" name="current_password" required>
                        <div class="form-text">Enter your current password to verify your identity</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new-password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="new-password" name="new_password" required>
                        <div class="form-text">Password must be at least 8 characters with uppercase, lowercase, and number</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                        <div class="form-text">Re-enter your new password to confirm</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Security Tip:</strong> Choose a strong password that you don't use elsewhere.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="savePassword()">
                    <i class="fas fa-key me-2"></i>Change Password
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentBookingId = null;
let selectedVehicleId = null;

// Toast notification function
function showToast(type, message) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function viewVehicleDetails(vehicleId) {
    const modalContent = `
        <div class="modal fade" id="vehicleDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Vehicle Details #${vehicleId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="vehicleDetailsContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading vehicle details...</p>
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
    
    const existingModal = document.getElementById('vehicleDetailsModal');
    if (existingModal) { existingModal.remove(); }
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    const modal = new bootstrap.Modal(document.getElementById('vehicleDetailsModal'));
    modal.show();
    
    fetch(`ajax/get_customer_vehicle_details.php?id=${vehicleId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('vehicleDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('vehicleDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading vehicle details: ${error.message}
                </div>
            `;
        });
}

function viewBookingDetails(bookingId) {
    const modalContent = `
        <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Booking Details #${bookingId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="bookingDetailsContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading booking details...</p>
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
    
    const existingModal = document.getElementById('bookingDetailsModal');
    if (existingModal) { existingModal.remove(); }
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
    modal.show();
    
    fetch(`ajax/get_customer_booking_details.php?id=${bookingId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('bookingDetailsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('bookingDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading booking details: ${error.message}
                </div>
            `;
        });
}

function editVehicle(vehicleId) {
    const modalContent = `
        <div class="modal fade" id="editVehicleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Vehicle #${vehicleId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="editVehicleContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading vehicle form...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('editVehicleModal');
    if (existingModal) { existingModal.remove(); }
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    const modal = new bootstrap.Modal(document.getElementById('editVehicleModal'));
    modal.show();
    
    fetch(`ajax/get_edit_vehicle_form.php?id=${vehicleId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editVehicleContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('editVehicleContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading vehicle form: ${error.message}
                </div>
            `;
        });
}

function changeVehicle(bookingId) {
    currentBookingId = bookingId;
    
    // Load vehicle options
    fetch('ajax/get_customer_vehicles.php')
        .then(response => response.json())
        .then(vehicles => {
            const vehicleOptions = document.getElementById('vehicleOptions');
            vehicleOptions.innerHTML = '';
            
            vehicles.forEach(vehicle => {
                const div = document.createElement('div');
                div.className = 'form-check mb-2';
                div.innerHTML = `
                    <input class="form-check-input" type="radio" name="vehicle_select" id="vehicle_${vehicle.id}" value="${vehicle.id}">
                    <label class="form-check-label" for="vehicle_${vehicle.id}">
                        <strong>${vehicle.nickname}</strong> - ${vehicle.year} ${vehicle.make} ${vehicle.model} (${vehicle.type})
                    </label>
                `;
                vehicleOptions.appendChild(div);
            });
            
            // Add event listeners
            document.querySelectorAll('input[name="vehicle_select"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    selectedVehicleId = this.value;
                });
            });
            
            // Show modal
            new bootstrap.Modal(document.getElementById('changeVehicleModal')).show();
        });
}

function confirmVehicleChange() {
    if (!selectedVehicleId) {
        showToast('error', 'Please select a vehicle');
        return;
    }
    
    // Send AJAX request to update booking
    fetch('ajax/update_booking_vehicle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: currentBookingId,
            vehicle_id: selectedVehicleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Vehicle changed successfully!');
            bootstrap.Modal.getInstance(document.getElementById('changeVehicleModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', data.message || 'Error updating vehicle');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while updating the vehicle');
    });
}

// Profile Management Functions
function editProfile() {
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
}

function saveProfile() {
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    
    // Show loading state
    const saveBtn = document.querySelector('#editProfileModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    saveBtn.disabled = true;
    
    fetch('ajax/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            
            // Update the display on the page
            if (data.user) {
                document.getElementById('display-full-name').textContent = data.user.full_name;
                document.getElementById('display-phone').textContent = data.user.phone;
            }
            
            bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
        } else {
            showToast('error', data.message || 'Failed to update profile');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while updating the profile');
    })
    .finally(() => {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function changePassword() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
    
    // Clear form when modal opens
    document.getElementById('changePasswordForm').reset();
}

function savePassword() {
    const form = document.getElementById('changePasswordForm');
    const formData = new FormData(form);
    
    // Validate password match
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    if (newPassword !== confirmPassword) {
        showToast('error', 'New password and confirmation password do not match');
        return;
    }
    
    // Show loading state
    const saveBtn = document.querySelector('#changePasswordModal .btn-warning');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Changing...';
    saveBtn.disabled = true;
    
    fetch('ajax/change_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
            form.reset();
        } else {
            showToast('error', data.message || 'Failed to change password');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while changing the password');
    })
    .finally(() => {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Real-time validation for profile form
document.addEventListener('DOMContentLoaded', function() {
    const fullNameInput = document.getElementById('edit-full-name');
    const phoneInput = document.getElementById('edit-phone');
    
    if (fullNameInput) {
        fullNameInput.addEventListener('input', function() {
            const value = this.value;
            if (value.length > 0 && !/^[a-zA-Z\s\-\.]+$/.test(value)) {
                this.setCustomValidity('Name can only contain letters, spaces, hyphens, and periods');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const value = this.value;
            const cleanValue = value.replace(/[^0-9+\-\(\)\s]/g, '');
            if (value.length > 0 && cleanValue.length < 10) {
                this.setCustomValidity('Please enter a valid phone number');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script>
