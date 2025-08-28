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
    echo '<div class="alert alert-danger">Booking ID is required.</div>';
    exit();
}

$booking_id = (int)$_GET['id'];

// Get booking information
$sql = "SELECT b.*, u.full_name, u.email, u.phone, v.nickname as vehicle_nickname, v.make, v.model, v.year, v.color, v.license_plate, v.vin
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Booking not found.</div>';
    exit();
}

$booking = $result->fetch_assoc();

// Get all services for selection
$services_sql = "SELECT * FROM services WHERE is_active = 1 ORDER BY category, name";
$services_result = $conn->query($services_sql);
$all_services = $services_result->fetch_all(MYSQLI_ASSOC);

// Get currently selected services for this booking
$selected_services_sql = "SELECT service_id FROM booking_services WHERE booking_id = ?";
$selected_stmt = $conn->prepare($selected_services_sql);
$selected_stmt->bind_param("i", $booking_id);
$selected_stmt->execute();
$selected_result = $selected_stmt->get_result();
$selected_services = array_column($selected_result->fetch_all(MYSQLI_ASSOC), 'service_id');

// Get user's vehicles
$vehicles_sql = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY is_default DESC, nickname";
$vehicles_stmt = $conn->prepare($vehicles_sql);
$vehicles_stmt->bind_param("i", $booking['user_id']);
$vehicles_stmt->execute();
$vehicles_result = $vehicles_stmt->get_result();
$user_vehicles = $vehicles_result->fetch_all(MYSQLI_ASSOC);
?>

<form id="editBookingForm">
    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
    
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary"><i class="fas fa-calendar me-2"></i>Appointment Details</h6>
            <div class="mb-3">
                <label for="appointment_date" class="form-label">Date</label>
                <input type="date" class="form-control" name="appointment_date" id="appointment_date" 
                       value="<?php echo $booking['appointment_date']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="appointment_time" class="form-label">Time</label>
                <input type="time" class="form-control" name="appointment_time" id="appointment_time" 
                       value="<?php echo $booking['appointment_time']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="service_type" class="form-label">Service Type</label>
                <select class="form-select" name="service_type" id="service_type" required>
                    <option value="mobile" <?php echo $booking['service_type'] == 'mobile' ? 'selected' : ''; ?>>Mobile Service</option>
                    <option value="facility" <?php echo $booking['service_type'] == 'facility' ? 'selected' : ''; ?>>Facility Service</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <textarea class="form-control" name="location" id="location" rows="2" 
                          placeholder="Enter service location or address..."><?php echo htmlspecialchars($booking['location']); ?></textarea>
            </div>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-primary"><i class="fas fa-car me-2"></i>Vehicle Selection</h6>
            <div class="mb-3">
                <label for="vehicle_id" class="form-label">Vehicle</label>
                <select class="form-select" name="vehicle_id" id="vehicle_id" required>
                    <?php foreach ($user_vehicles as $vehicle): ?>
                        <option value="<?php echo $vehicle['id']; ?>" <?php echo $vehicle['id'] == $booking['vehicle_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($vehicle['nickname'] . ' (' . $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <h6 class="text-primary mt-4"><i class="fas fa-cogs me-2"></i>Services</h6>
            <div class="mb-3">
                <label class="form-label">Select Services</label>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                    <?php foreach ($all_services as $service): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="services[]" 
                                   value="<?php echo $service['id']; ?>" id="service_<?php echo $service['id']; ?>"
                                   <?php echo in_array($service['id'], $selected_services) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="service_<?php echo $service['id']; ?>">
                                <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                <span class="text-muted"> - <?php echo format_currency($service['price']); ?></span>
                                <br><small class="text-muted"><?php echo htmlspecialchars($service['description']); ?></small>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-primary"><i class="fas fa-sticky-note me-2"></i>Admin Notes</h6>
            <div class="mb-3">
                <textarea class="form-control" name="admin_notes" id="admin_notes" rows="3" 
                          placeholder="Add any admin notes about this booking..."><?php echo htmlspecialchars($booking['admin_notes']); ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Booking Information</h6>
            <div class="alert alert-info">
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['full_name']); ?> (<?php echo htmlspecialchars($booking['email']); ?>)</p>
                <p><strong>Current Status:</strong> 
                    <span class="badge <?php echo get_status_badge_class($booking['status']); ?>">
                        <?php echo get_status_display($booking['status']); ?>
                    </span>
                </p>
                <p><strong>Current Total:</strong> <?php echo format_currency($booking['total_amount']); ?></p>
            </div>
        </div>
    </div>
</form>

<script>
// Add event listeners for dynamic calculations
document.addEventListener('DOMContentLoaded', function() {
    const serviceCheckboxes = document.querySelectorAll('input[name="services[]"]');
    const totalDisplay = document.createElement('div');
    totalDisplay.className = 'alert alert-success mt-3';
    totalDisplay.innerHTML = '<strong>Selected Services Total: <span id="selectedTotal">0</span></strong>';
    
    // Insert total display after services section
    const servicesSection = document.querySelector('.border.rounded.p-3');
    servicesSection.parentNode.insertBefore(totalDisplay, servicesSection.nextSibling);
    
    function updateTotal() {
        let total = 0;
        serviceCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                // Extract price from label text
                const label = checkbox.nextElementSibling;
                const priceText = label.querySelector('.text-muted').textContent;
                const price = parseFloat(priceText.replace(/[^\d.]/g, ''));
                total += price;
            }
        });
        
        // Add tax (assuming 16% VAT)
        const tax = total * 0.16;
        const grandTotal = total + tax;
        
        document.getElementById('selectedTotal').textContent = formatCurrency(grandTotal);
    }
    
    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotal);
    });
    
    // Calculate initial total
    updateTotal();
});

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount);
}
</script>

