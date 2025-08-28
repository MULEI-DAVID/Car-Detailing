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

// Handle booking status updates
if (isset($_POST['update_status']) && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = sanitize_input($_POST['status']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        set_flash_message('Invalid status selected.', 'danger');
        header('Location: index.php?page=admin_bookings');
        exit();
    }
    
    // Get current booking info for logging
    $current_sql = "SELECT status, user_id, vehicle_id FROM bookings WHERE id = ?";
    $current_stmt = $conn->prepare($current_sql);
    $current_stmt->bind_param("i", $booking_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    
    if ($current_result->num_rows === 0) {
        set_flash_message('Booking not found.', 'danger');
        header('Location: index.php?page=admin_bookings');
        exit();
    }
    
    $current_booking = $current_result->fetch_assoc();
    $old_status = $current_booking['status'];
    
    // Update booking status
    $update_sql = "UPDATE bookings SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $status, $notes, $booking_id);
    
    if ($stmt->execute()) {
        // Log the status change (optional - you can create a status_logs table)
        $log_message = "Status changed from " . ucfirst($old_status) . " to " . ucfirst($status);
        if (!empty($notes)) {
            $log_message .= " - Notes: " . $notes;
        }
        
        set_flash_message("Booking #{$booking_id} status updated successfully from " . ucfirst($old_status) . " to " . ucfirst($status) . "!", 'success');
    } else {
        set_flash_message('Error updating booking status: ' . $conn->error, 'danger');
    }
    
    // Redirect to prevent form resubmission
    header('Location: index.php?page=admin_bookings');
    exit();
}

// Get all bookings with user and vehicle information
$all_bookings_sql = "SELECT b.*, u.full_name, u.email, u.phone, v.nickname as vehicle_nickname, v.make, v.model, v.year, v.color, v.license_plate,
                            GROUP_CONCAT(s.name SEPARATOR ', ') as services
                     FROM bookings b
                     JOIN users u ON b.user_id = u.id
                     JOIN vehicles v ON b.vehicle_id = v.id
                     LEFT JOIN booking_services bs ON b.id = bs.booking_id
                     LEFT JOIN services s ON bs.service_id = s.id
                     GROUP BY b.id
                     ORDER BY b.created_at DESC";
$all_bookings_result = $conn->query($all_bookings_sql);
$all_bookings = $all_bookings_result->fetch_all(MYSQLI_ASSOC);

display_flash_message();
?>

<div class="admin-dashboard">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">
                        <i class="fas fa-calendar me-2"></i>Booking Management
                    </h2>
                    <p class="text-muted">Manage all bookings and update their status</p>
                </div>
                <div>
                    <a href="index.php?page=admin" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="filterBookings('all')">All Bookings</button>
                        <button type="button" class="btn btn-outline-warning" onclick="filterBookings('pending')">Pending</button>
                        <button type="button" class="btn btn-outline-info" onclick="filterBookings('confirmed')">Confirmed</button>
                        <button type="button" class="btn btn-outline-success" onclick="filterBookings('completed')">Completed</button>
                        <button type="button" class="btn btn-outline-danger" onclick="filterBookings('cancelled')">Cancelled</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($all_bookings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No bookings found</h5>
                            <p class="text-muted">There are no bookings in the system yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="bookingsTable">
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
                                    <?php foreach ($all_bookings as $booking): ?>
                                                                                 <tr data-status="<?php echo $booking['status']; ?>" data-booking-id="<?php echo $booking['id']; ?>">
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
                                                  <!-- Main Action Buttons Row -->
                                                  <div class="action-buttons-container">
                                                      <!-- Primary Action Buttons -->
                                                      <div class="btn-group btn-group-sm mb-2" role="group">
                                                          <!-- View Details Button -->
                                                          <button type="button" class="btn btn-outline-primary action-btn" onclick="viewBooking(<?php echo $booking['id']; ?>)" title="View Details">
                                                              <i class="fas fa-eye"></i>
                                                          </button>
                                                          
                                                          <!-- Edit Booking Button -->
                                                          <button type="button" class="btn btn-outline-success action-btn" onclick="editBooking(<?php echo $booking['id']; ?>)" title="Edit Booking">
                                                              <i class="fas fa-edit"></i>
                                                          </button>
                                                          
                                                          <!-- Status Update Button -->
                                                          <button type="button" class="btn btn-outline-warning action-btn" onclick="updateStatus(<?php echo $booking['id']; ?>)" title="Update Status">
                                                              <i class="fas fa-cog"></i>
                                                          </button>
                                                      </div>
                                                      
                                                                                                             <!-- Quick Status Dropdown -->
                                                       <div class="btn-group btn-group-sm mb-2" role="group">
                                                           <button type="button" class="btn btn-outline-info dropdown-toggle action-btn" title="Quick Status Actions">
                                                               <i class="fas fa-bolt"></i>
                                                           </button>
                                                           <ul class="dropdown-menu dropdown-menu-end quick-status-dropdown" style="min-width: 250px; max-height: 400px; overflow-y: auto; display: none;">
                                                               <li><h6 class="dropdown-header">Quick Status Change</h6></li>
                                                               <li><hr class="dropdown-divider"></li>
                                                               
                                                               <!-- Pending Status -->
                                                               <li>
                                                                   <a class="dropdown-item <?php echo $booking['status'] == 'pending' ? 'active' : ''; ?>" 
                                                                      href="javascript:void(0)" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'pending')">
                                                                        <i class="fas fa-clock me-2"></i>🟡 Set Pending
                                                                        <?php if ($booking['status'] == 'pending'): ?>
                                                                            <i class="fas fa-check ms-2"></i>
                                                                        <?php endif; ?>
                                                                    </a>
                                                                </li>
                                                                
                                                                <!-- Confirmed Status -->
                                                                <li>
                                                                    <a class="dropdown-item <?php echo $booking['status'] == 'confirmed' ? 'active' : ''; ?>" 
                                                                       href="javascript:void(0)" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'confirmed')">
                                                                        <i class="fas fa-check-circle me-2"></i>🔵 Set Confirmed
                                                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                                                            <i class="fas fa-check ms-2"></i>
                                                                        <?php endif; ?>
                                                                    </a>
                                                                </li>
                                                                
                                                                <!-- In Progress Status -->
                                                                <li>
                                                                    <a class="dropdown-item <?php echo $booking['status'] == 'in_progress' ? 'active' : ''; ?>" 
                                                                       href="javascript:void(0)" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'in_progress')">
                                                                        <i class="fas fa-spinner me-2"></i>🔄 Set In Progress
                                                                        <?php if ($booking['status'] == 'in_progress'): ?>
                                                                            <i class="fas fa-check ms-2"></i>
                                                                        <?php endif; ?>
                                                                    </a>
                                                                </li>
                                                                
                                                                <!-- Completed Status -->
                                                                <li>
                                                                    <a class="dropdown-item <?php echo $booking['status'] == 'completed' ? 'active' : ''; ?>" 
                                                                       href="javascript:void(0)" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'completed')">
                                                                        <i class="fas fa-check-double me-2"></i>✅ Set Completed
                                                                        <?php if ($booking['status'] == 'completed'): ?>
                                                                            <i class="fas fa-check ms-2"></i>
                                                                        <?php endif; ?>
                                                                    </a>
                                                                </li>
                                                                
                                                                <!-- Cancelled Status -->
                                                                <li>
                                                                    <a class="dropdown-item <?php echo $booking['status'] == 'cancelled' ? 'active' : ''; ?>" 
                                                                       href="javascript:void(0)" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'cancelled')">
                                                                        <i class="fas fa-times-circle me-2"></i>❌ Set Cancelled
                                                                        <?php if ($booking['status'] == 'cancelled'): ?>
                                                                            <i class="fas fa-check ms-2"></i>
                                                                        <?php endif; ?>
                                                                    </a>
                                                                </li>
                                                                
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item text-muted" href="javascript:void(0)" onclick="updateStatus(<?php echo $booking['id']; ?>)">
                                                                        <i class="fas fa-cog me-2"></i>Advanced Status Update
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                      </div>
                                                      
                                                      <!-- Status-specific Quick Action Buttons -->
                                                      <div class="status-quick-actions">
                                                          <?php if ($booking['status'] == 'pending'): ?>
                                                              <button type="button" class="btn btn-sm btn-success action-btn" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'confirmed')" title="Confirm Booking">
                                                                  <i class="fas fa-check me-1"></i>Confirm
                                                              </button>
                                                          <?php elseif ($booking['status'] == 'confirmed'): ?>
                                                              <button type="button" class="btn btn-sm btn-primary action-btn" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'in_progress')" title="Start Service">
                                                                  <i class="fas fa-play me-1"></i>Start
                                                              </button>
                                                          <?php elseif ($booking['status'] == 'in_progress'): ?>
                                                              <button type="button" class="btn btn-sm btn-success action-btn" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'completed')" title="Complete Service">
                                                                  <i class="fas fa-check-double me-1"></i>Complete
                                                              </button>
                                                          <?php elseif ($booking['status'] == 'completed'): ?>
                                                              <span class="badge bg-success">✅ Service Completed</span>
                                                          <?php elseif ($booking['status'] == 'cancelled'): ?>
                                                              <span class="badge bg-danger">❌ Booking Cancelled</span>
                                                          <?php endif; ?>
                                                          
                                                          <?php if (in_array($booking['status'], ['pending', 'confirmed', 'in_progress'])): ?>
                                                              <button type="button" class="btn btn-sm btn-danger action-btn" onclick="quickStatusChange(<?php echo $booking['id']; ?>, 'cancelled')" title="Cancel Booking">
                                                                  <i class="fas fa-times me-1"></i>Cancel
                                                              </button>
                                                          <?php endif; ?>
                                                      </div>
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

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Update Booking Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="statusUpdateForm">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="booking_id">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Booking #<span id="bookingNumber"></span></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Status *</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="">Select Status</option>
                            <option value="pending">🟡 Pending</option>
                            <option value="confirmed">🔵 Confirmed</option>
                            <option value="in_progress">🔄 In Progress</option>
                            <option value="completed">✅ Completed</option>
                            <option value="cancelled">❌ Cancelled</option>
                        </select>
                        <div class="form-text">Choose the current status of this booking</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Admin Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="4" 
                                  placeholder="Add any notes about this booking (optional)..."></textarea>
                        <div class="form-text">These notes will be visible to the customer</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> Status changes will be logged and may trigger notifications to the customer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" name="update_status" class="btn btn-primary" id="updateStatusBtn">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Enhanced action buttons styling */
.action-buttons-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 200px;
}

.btn-group-sm .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    margin: 0;
    border-radius: 0.25rem;
}

.action-btn {
    transition: all 0.2s ease;
    position: relative;
    z-index: 1;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
}

.status-quick-actions .btn {
    margin: 0;
    white-space: nowrap;
}

/* Prevent button overlapping */
.btn-group {
    display: inline-flex;
    vertical-align: middle;
}

.btn-group > .btn {
    position: relative;
    flex: 1 1 auto;
}

.btn-group > .btn:not(:first-child) {
    margin-left: -1px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.btn-group > .btn:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.dropdown-item.active {
    background-color: var(--bs-primary);
    color: white;
}

.dropdown-item:hover {
    background-color: var(--bs-light);
}

.dropdown-header {
    font-weight: 600;
    color: var(--bs-primary);
}

/* Status-specific button colors */
.btn-status-pending {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-status-confirmed {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.btn-status-in-progress {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: #fff;
}

.btn-status-completed {
    background-color: #198754;
    border-color: #198754;
    color: #fff;
}

.btn-status-cancelled {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

/* Loading animation */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: button-loading-spinner 1s ease infinite;
}

@keyframes button-loading-spinner {
    from {
        transform: rotate(0turn);
    }
    to {
        transform: rotate(1turn);
    }
}

/* Quick Status Dropdown Improvements */
.quick-status-dropdown {
    max-height: 400px !important;
    min-width: 250px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    scrollbar-width: thin;
    scrollbar-color: var(--bs-primary) #f8f9fa;
    z-index: 99999 !important;
    position: fixed !important;
    margin-top: 2px !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.25) !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.375rem !important;
    background: white !important;
    backdrop-filter: blur(10px) !important;
}

.quick-status-dropdown::-webkit-scrollbar {
    width: 6px;
}

.quick-status-dropdown::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.quick-status-dropdown::-webkit-scrollbar-thumb {
    background: var(--bs-primary);
    border-radius: 3px;
}

.quick-status-dropdown::-webkit-scrollbar-thumb:hover {
    background: var(--bs-primary-dark);
}

.quick-status-dropdown .dropdown-item {
    padding: 0.75rem 1rem !important;
    font-size: 0.9rem !important;
    white-space: nowrap !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    display: block !important;
    width: 100% !important;
    text-decoration: none !important;
    border: none !important;
    background: transparent !important;
    color: inherit !important;
    line-height: 1.5 !important;
    min-height: 44px !important;
    display: flex !important;
    align-items: center !important;
}

.quick-status-dropdown .dropdown-item:hover {
    background-color: var(--bs-primary);
    color: white;
    transform: translateX(2px);
}

.quick-status-dropdown .dropdown-item.active {
    background-color: var(--bs-success);
    color: white;
    font-weight: 600;
}

.quick-status-dropdown .dropdown-header {
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 0.5rem;
}

/* Enhanced dropdown positioning */
.dropdown-menu-end {
    right: 0;
    left: auto;
    min-width: 200px;
}

/* Ensure button group has proper positioning for dropdown */
.btn-group {
    position: relative;
}

.btn-group .dropdown-toggle {
    position: relative;
}

/* Prevent table row overflow */
.table-responsive {
    overflow: visible;
}

.table td {
    position: relative;
}

/* Responsive table improvements */
@media (max-width: 768px) {
    .action-buttons-container {
        min-width: 150px;
        gap: 0.25rem;
    }
    
    .btn-group-sm .btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    .dropdown-menu {
        font-size: 0.8rem;
    }
    
    .quick-status-dropdown {
        max-height: 250px;
        min-width: 180px;
    }
    
    .quick-status-dropdown .dropdown-item {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
    
    .status-quick-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .status-quick-actions .btn {
        width: 100%;
        margin-bottom: 0.25rem;
    }
}

@media (max-width: 576px) {
    .action-buttons-container {
        min-width: 120px;
    }
    
    .btn-group-sm .btn {
        font-size: 0.65rem;
        padding: 0.15rem 0.3rem;
    }
    
    .action-btn {
        min-width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
</style>

<script>
function filterBookings(status) {
    const rows = document.querySelectorAll('#bookingsTable tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewBooking(bookingId) {
    // Create modal content for viewing booking details
    const modalContent = `
        <div class="modal fade" id="viewBookingModal" tabindex="-1">
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
                        <button type="button" class="btn btn-primary" onclick="editBooking(${bookingId})">Edit Booking</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('viewBookingModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
    modal.show();
    
    // Load booking details via AJAX
    fetch(`ajax/get_booking_details.php?id=${bookingId}`)
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

function editBooking(bookingId) {
    // Create modal content for editing booking
    const modalContent = `
        <div class="modal fade" id="editBookingModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Booking #${bookingId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="editBookingContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading booking form...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveBookingChanges()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('editBookingModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
    modal.show();
    
    // Load booking edit form via AJAX
    fetch(`ajax/get_booking_edit_form.php?id=${bookingId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editBookingContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('editBookingContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading booking form: ${error.message}
                </div>
            `;
        });
}

function saveBookingChanges() {
    const form = document.getElementById('editBookingForm');
    if (!form) {
        alert('Edit form not found!');
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('ajax/update_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('editBookingModal')).hide();
            location.reload();
        } else {
            alert('Error updating booking: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error updating booking: ' + error.message);
    });
}

function updateStatus(bookingId) {
    // Get current booking status to pre-select it
    const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
    const currentStatus = row ? row.getAttribute('data-status') : '';
    
    // Set the booking ID and display booking number
    document.getElementById('booking_id').value = bookingId;
    document.getElementById('bookingNumber').textContent = bookingId;
    
    // Pre-select current status
    const statusSelect = document.getElementById('status');
    if (statusSelect && currentStatus) {
        statusSelect.value = currentStatus;
    } else {
        statusSelect.value = '';
    }
    
    // Clear previous notes
    document.getElementById('notes').value = '';
    
    // Show modal
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

// Add form validation and submission handling
document.addEventListener('DOMContentLoaded', function() {
    const statusUpdateForm = document.getElementById('statusUpdateForm');
    if (statusUpdateForm) {
        statusUpdateForm.addEventListener('submit', function(e) {
            const statusSelect = document.getElementById('status');
            const updateBtn = document.getElementById('updateStatusBtn');
            
            // Validate status selection
            if (!statusSelect.value) {
                e.preventDefault();
                alert('Please select a status');
                statusSelect.focus();
                return;
            }
            
            // Show loading state
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            updateBtn.disabled = true;
            
            // Form will submit normally
        });
    }
});

// Quick status change function with enhanced feedback
function quickStatusChange(bookingId, newStatus) {
    const statusLabels = {
        'pending': '🟡 Pending',
        'confirmed': '🔵 Confirmed', 
        'in_progress': '🔄 In Progress',
        'completed': '✅ Completed',
        'cancelled': '❌ Cancelled'
    };
    
    const statusLabel = statusLabels[newStatus] || newStatus.toUpperCase();
    
    if (confirm(`Are you sure you want to change the status of Booking #${bookingId} to ${statusLabel}?`)) {
        // Show loading state on the button that was clicked
        const clickedButton = event.target.closest('button, a');
        if (clickedButton) {
            const originalContent = clickedButton.innerHTML;
            clickedButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
            clickedButton.disabled = true;
            
            // Re-enable after 3 seconds if something goes wrong
            setTimeout(() => {
                clickedButton.innerHTML = originalContent;
                clickedButton.disabled = false;
            }, 3000);
        }
        
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const bookingIdInput = document.createElement('input');
        bookingIdInput.type = 'hidden';
        bookingIdInput.name = 'booking_id';
        bookingIdInput.value = bookingId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        
        // Add appropriate notes based on status
        let notes = '';
        switch(newStatus) {
            case 'pending':
                notes = 'Status set to pending - awaiting confirmation';
                break;
            case 'confirmed':
                notes = 'Booking confirmed by admin';
                break;
            case 'in_progress':
                notes = 'Service started - work in progress';
                break;
            case 'completed':
                notes = 'Service completed successfully';
                break;
            case 'cancelled':
                notes = 'Booking cancelled by admin';
                break;
            default:
                notes = 'Status changed via quick action';
        }
        
        const notesInput = document.createElement('input');
        notesInput.type = 'hidden';
        notesInput.name = 'notes';
        notesInput.value = notes;
        
        const updateInput = document.createElement('input');
        updateInput.type = 'hidden';
        updateInput.name = 'update_status';
        updateInput.value = '1';
        
        form.appendChild(bookingIdInput);
        form.appendChild(statusInput);
        form.appendChild(notesInput);
        form.appendChild(updateInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Enhanced status update function
function updateStatus(bookingId) {
    // Get current booking status to pre-select it
    const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
    const currentStatus = row ? row.getAttribute('data-status') : '';
    
    // Set the booking ID and display booking number
    document.getElementById('booking_id').value = bookingId;
    document.getElementById('bookingNumber').textContent = bookingId;
    
    // Pre-select current status
    const statusSelect = document.getElementById('status');
    if (statusSelect && currentStatus) {
        statusSelect.value = currentStatus;
    } else {
        statusSelect.value = '';
    }
    
    // Clear previous notes
    document.getElementById('notes').value = '';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
    
    // Focus on status select
    setTimeout(() => {
        statusSelect.focus();
    }, 500);
}

// Enhanced dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all action buttons
    initializeActionButtons();
    
        // Initialize quick status dropdowns
    const quickStatusButtons = document.querySelectorAll('.dropdown-toggle');
    quickStatusButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find the dropdown menu within the same button group
            const buttonGroup = this.closest('.btn-group');
            const dropdownMenu = buttonGroup.querySelector('.quick-status-dropdown');
            
            if (dropdownMenu) {
                // Close all other dropdowns first
                document.querySelectorAll('.quick-status-dropdown').forEach(d => {
                    if (d !== dropdownMenu) {
                        d.style.display = 'none';
                    }
                });
                
                // Toggle current dropdown
                const isVisible = dropdownMenu.style.display === 'block';
                
                if (!isVisible) {
                    // Position the dropdown using fixed positioning
                    const buttonRect = this.getBoundingClientRect();
                    const dropdownHeight = 400; // max-height
                    
                    // Calculate position
                    let top = buttonRect.bottom + 5;
                    let left = buttonRect.right - 250; // min-width of dropdown
                    
                    // Check if dropdown would go below viewport
                    if (top + dropdownHeight > window.innerHeight) {
                        top = buttonRect.top - dropdownHeight - 5;
                    }
                    
                    // Check if dropdown would go off the right edge
                    if (left + 250 > window.innerWidth) {
                        left = window.innerWidth - 260;
                    }
                    
                    // Check if dropdown would go off the left edge
                    if (left < 10) {
                        left = 10;
                    }
                    
                    // Apply positioning
                    dropdownMenu.style.position = 'fixed';
                    dropdownMenu.style.top = top + 'px';
                    dropdownMenu.style.left = left + 'px';
                    dropdownMenu.style.right = 'auto';
                    dropdownMenu.style.bottom = 'auto';
                    dropdownMenu.style.display = 'block';
                    dropdownMenu.style.zIndex = '99999';
                    dropdownMenu.style.visibility = 'visible';
                    dropdownMenu.style.opacity = '1';
                    
                    // Debug: Log the positioning
                    console.log('Dropdown positioned at:', { top, left, display: dropdownMenu.style.display });
                } else {
                    dropdownMenu.style.display = 'none';
                }
            }
        });
    });
    
    // Add hover effects for better UX
    const dropdownItems = document.querySelectorAll('.quick-status-dropdown .dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(3px)';
            this.style.backgroundColor = 'var(--bs-primary)';
            this.style.color = 'white';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
            if (!this.classList.contains('active')) {
                this.style.backgroundColor = '';
                this.style.color = '';
            }
        });
        
        // Ensure click events work properly
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close the dropdown after selection
            const dropdown = this.closest('.quick-status-dropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
            
            // Get the onclick attribute and execute it
            const onclickAttr = this.getAttribute('onclick');
            if (onclickAttr) {
                eval(onclickAttr);
            }
        });
    });
    
    // Add smooth scrolling behavior to dropdowns
    const quickStatusDropdowns = document.querySelectorAll('.quick-status-dropdown');
    quickStatusDropdowns.forEach(dropdown => {
        dropdown.addEventListener('scroll', function(e) {
            // Prevent horizontal scrolling
            if (e.deltaX !== 0) {
                e.preventDefault();
            }
        });
        
        // Add keyboard navigation
        dropdown.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.dropdown-item');
            const currentIndex = Array.from(items).findIndex(item => item === document.activeElement);
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (currentIndex < items.length - 1) {
                        items[currentIndex + 1].focus();
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (currentIndex > 0) {
                        items[currentIndex - 1].focus();
                    }
                    break;
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    if (document.activeElement.classList.contains('dropdown-item')) {
                        document.activeElement.click();
                    }
                    break;
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.btn-group') && !e.target.closest('.quick-status-dropdown')) {
            document.querySelectorAll('.quick-status-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    });
    
    // Close dropdowns when scrolling
    document.addEventListener('scroll', function() {
        document.querySelectorAll('.quick-status-dropdown').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    });
});

// Initialize action buttons functionality
function initializeActionButtons() {
    // Add click event listeners to all action buttons
    const actionButtons = document.querySelectorAll('.action-btn');
    
    actionButtons.forEach(button => {
        // Remove any existing event listeners to prevent duplicates
        button.removeEventListener('click', handleActionButtonClick);
        
        // Add new event listener
        button.addEventListener('click', handleActionButtonClick);
        
        // Add visual feedback
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
}

// Handle action button clicks
function handleActionButtonClick(e) {
    // Prevent event bubbling
    e.stopPropagation();
    
    // Add loading state
    const button = e.currentTarget;
    const originalContent = button.innerHTML;
    const originalDisabled = button.disabled;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    // Re-enable button after a short delay if no response
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = originalDisabled;
    }, 3000);
    
    // Let the onclick attribute handle the actual functionality
    const onclickAttr = button.getAttribute('onclick');
    if (onclickAttr) {
        // Execute the onclick function
        eval(onclickAttr);
    }
}


</script>
