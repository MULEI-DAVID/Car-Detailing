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

// Get basic statistics
$user_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];
$booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'];

display_flash_message();
?>

<div class="admin-dashboard">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary">
                <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
            </h2>
            <p class="text-muted">Complete System Management & Control Center</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color));">
                <div class="card-body text-white">
                    <h3 class="mb-0 fw-bold"><?php echo $user_count; ?></h3>
                    <p class="mb-0">Registered Users</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--secondary-color), #FFA500);">
                <div class="card-body text-dark">
                    <h3 class="mb-0 fw-bold"><?php echo $booking_count; ?></h3>
                    <p class="mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--accent-color), var(--primary-color));">
                <div class="card-body text-white">
                    <h3 class="mb-0 fw-bold"><?php echo format_currency($revenue); ?></h3>
                    <p class="mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--warning-color), #FFD700);">
                <div class="card-body text-dark">
                    <h3 class="mb-0 fw-bold"><?php echo $pending_bookings; ?></h3>
                    <p class="mb-0">Pending Bookings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Recent Bookings</h5>
                    <a href="index.php?page=admin_bookings" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>Manage All
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    $recent_bookings = $conn->query("SELECT b.*, u.full_name, v.nickname as vehicle_nickname 
                                                   FROM bookings b 
                                                   JOIN users u ON b.user_id = u.id 
                                                   JOIN vehicles v ON b.vehicle_id = v.id 
                                                   ORDER BY b.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
                    ?>
                    
                    <?php if (empty($recent_bookings)): ?>
                        <p class="text-muted">No recent bookings</p>
                    <?php else: ?>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($booking['vehicle_nickname']); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?php echo get_status_badge_class($booking['status']); ?>">
                                        <?php echo get_status_display($booking['status']); ?>
                                    </span><br>
                                    <small class="text-muted"><?php echo format_currency($booking['total_amount'] ?? 0); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Users</h5>
                    <a href="index.php?page=admin_users" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>Manage All
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    $recent_users = $conn->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
                    ?>
                    
                    <?php if (empty($recent_users)): ?>
                        <p class="text-muted">No recent users</p>
                    <?php else: ?>
                        <?php foreach ($recent_users as $user): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted"><?php echo format_date($user['created_at']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Quick Management</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="index.php?page=admin_bookings" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-calendar me-2"></i>Booking Management
                                <br><small>View, edit, and update booking status</small>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="index.php?page=admin_users" class="btn btn-success w-100 mb-3">
                                <i class="fas fa-users me-2"></i>User Management
                                <br><small>Manage users, vehicles, and accounts</small>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="index.php?page=admin_services" class="btn btn-warning w-100 mb-3">
                                <i class="fas fa-cogs me-2"></i>Service Management
                                <br><small>Manage services, pricing, and availability</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary"><?php echo date('Y-m-d'); ?></h4>
                            <small class="text-muted">Today's Date</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success"><?php echo date('H:i'); ?></h4>
                            <small class="text-muted">Current Time</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info"><?php echo getCurrentCurrency(); ?></h4>
                            <small class="text-muted">Current Currency</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning">2.0.0</h4>
                            <small class="text-muted">System Version</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Dashboard loaded successfully - No AJAX required!');
});
</script>
