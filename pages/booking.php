<?php
$vehicles = get_user_vehicles($_SESSION['user_id']);
$packages = get_services('package');
$addons = get_services('addon');

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = (int)$_POST['vehicle_id'];
    $service_type = sanitize_input($_POST['service_type']);
    $appointment_date = sanitize_input($_POST['appointment_date']);
    $appointment_time = sanitize_input($_POST['appointment_time']);
    $location = sanitize_input($_POST['location']);
    $selected_services = isset($_POST['services']) ? $_POST['services'] : [];
    
    $errors = [];
    
    // Validation
    if (empty($vehicle_id)) {
        $errors[] = "Please select a vehicle";
    }
    
    if (empty($service_type)) {
        $errors[] = "Please select service type";
    }
    
    if (empty($appointment_date)) {
        $errors[] = "Please select appointment date";
    }
    
    if (empty($appointment_time)) {
        $errors[] = "Please select appointment time";
    }
    
    if (empty($selected_services)) {
        $errors[] = "Please select at least one service";
    }
    
    if ($service_type == 'mobile' && empty($location)) {
        $errors[] = "Please provide location for mobile service";
    }
    
    // Check if vehicle belongs to user
    if (!empty($vehicle_id)) {
        $check_sql = "SELECT id FROM vehicles WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $vehicle_id, $_SESSION['user_id']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 0) {
            $errors[] = "Invalid vehicle selection";
        }
    }
    
    // If no errors, create booking
    if (empty($errors)) {
        $total_amount = calculate_booking_total($selected_services);
        
        // Create booking
        $booking_sql = "INSERT INTO bookings (user_id, vehicle_id, service_type, appointment_date, appointment_time, location, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("iissssd", $_SESSION['user_id'], $vehicle_id, $service_type, $appointment_date, $appointment_time, $location, $total_amount);
        
        if ($booking_stmt->execute()) {
            $booking_id = $conn->insert_id;
            
            // Add booking services
            foreach ($selected_services as $service_id) {
                $service_sql = "SELECT price FROM services WHERE id = ?";
                $service_stmt = $conn->prepare($service_sql);
                $service_stmt->bind_param("i", $service_id);
                $service_stmt->execute();
                $service = $service_stmt->get_result()->fetch_assoc();
                
                $booking_service_sql = "INSERT INTO booking_services (booking_id, service_id, price) VALUES (?, ?, ?)";
                $booking_service_stmt = $conn->prepare($booking_service_sql);
                $booking_service_stmt->bind_param("iid", $booking_id, $service_id, $service['price']);
                $booking_service_stmt->execute();
            }
            
            set_flash_message('success', 'Booking created successfully! We will confirm your appointment shortly.');
            header('Location: index.php?page=profile');
            exit();
        } else {
            $errors[] = "Error creating booking. Please try again.";
        }
    }
}

display_flash_message();
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Book Your Appointment</h4>
            </div>
            <div class="card-body">
                <?php if (empty($vehicles)): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>No Vehicles in Your Garage</h5>
                        <p>You need to add a vehicle to your garage before booking services.</p>
                        <a href="index.php?page=profile" class="btn btn-primary">Add Vehicle</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="bookingForm">
                        <!-- Step 1: Service Selection -->
                        <div class="booking-step" id="step1">
                            <h5 class="mb-3">Step 1: Select Services</h5>
                            
                            <div class="mb-4">
                                <h6>Service Packages</h6>
                                <div class="row">
                                    <?php foreach ($packages as $package): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="services[]" value="<?php echo $package['id']; ?>" id="service_<?php echo $package['id']; ?>">
                                                        <label class="form-check-label" for="service_<?php echo $package['id']; ?>">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($package['name']); ?></h6>
                                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($package['description']); ?></p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge bg-primary"><?php echo format_currency($package['price']); ?></span>
                                                                <small class="text-muted"><?php echo $package['duration']; ?> min</small>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6>Additional Services</h6>
                                <div class="row">
                                    <?php foreach ($addons as $addon): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="services[]" value="<?php echo $addon['id']; ?>" id="service_<?php echo $addon['id']; ?>">
                                                        <label class="form-check-label" for="service_<?php echo $addon['id']; ?>">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($addon['name']); ?></h6>
                                                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($addon['description']); ?></p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge bg-success"><?php echo format_currency($addon['price']); ?></span>
                                                                <small class="text-muted"><?php echo $addon['duration']; ?> min</small>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="nextStep()">Next: Select Vehicle</button>
                        </div>
                        
                        <!-- Step 2: Vehicle Selection -->
                        <div class="booking-step" id="step2" style="display: none;">
                            <h5 class="mb-3">Step 2: Select Vehicle</h5>
                            
                            <div class="mb-4">
                                <p class="text-muted">Choose a vehicle from your garage or add a new one:</p>
                                
                                <div class="row">
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="vehicle_id" value="<?php echo $vehicle['id']; ?>" id="vehicle_<?php echo $vehicle['id']; ?>" <?php echo $vehicle['is_default'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="vehicle_<?php echo $vehicle['id']; ?>">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($vehicle['nickname']); ?></h6>
                                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></p>
                                                            <p class="text-muted small mb-0">
                                                                Type: <?php echo htmlspecialchars($vehicle['type']); ?>
                                                                <?php if ($vehicle['color']): ?>
                                                                    | Color: <?php echo htmlspecialchars($vehicle['color']); ?>
                                                                <?php endif; ?>
                                                            </p>
                                                            <?php if ($vehicle['is_default']): ?>
                                                                <span class="badge bg-primary mt-1">Default</span>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                                        <i class="fas fa-plus me-1"></i>Add New Vehicle
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Next: Schedule</button>
                            </div>
                        </div>
                        
                        <!-- Step 3: Schedule -->
                        <div class="booking-step" id="step3" style="display: none;">
                            <h5 class="mb-3">Step 3: Schedule Appointment</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="service_type" class="form-label">Service Type *</label>
                                        <select class="form-select" id="service_type" name="service_type" required>
                                            <option value="">Select Service Type</option>
                                            <option value="facility">Facility Service</option>
                                            <option value="mobile">Mobile Service (We come to you)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="appointment_date" class="form-label">Appointment Date *</label>
                                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="appointment_time" class="form-label">Preferred Time *</label>
                                        <select class="form-select" id="appointment_time" name="appointment_time" required>
                                            <option value="">Select Time</option>
                                            <option value="09:00">9:00 AM</option>
                                            <option value="10:00">10:00 AM</option>
                                            <option value="11:00">11:00 AM</option>
                                            <option value="12:00">12:00 PM</option>
                                            <option value="13:00">1:00 PM</option>
                                            <option value="14:00">2:00 PM</option>
                                            <option value="15:00">3:00 PM</option>
                                            <option value="16:00">4:00 PM</option>
                                            <option value="17:00">5:00 PM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3" id="locationField" style="display: none;">
                                        <label for="location" class="form-label">Service Location *</label>
                                        <textarea class="form-control" id="location" name="location" rows="3" placeholder="Enter your address for mobile service"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Next: Review</button>
                            </div>
                        </div>
                        
                        <!-- Step 4: Review -->
                        <div class="booking-step" id="step4" style="display: none;">
                            <h5 class="mb-3">Step 4: Review & Confirm</h5>
                            
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div id="reviewContent">
                                        <!-- Review content will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                <button type="submit" class="btn btn-success">Confirm Booking</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Booking Summary -->
        <div class="card shadow sticky-top" style="top: 20px;">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
            </div>
            <div class="card-body">
                <div id="bookingSummary">
                    <p class="text-muted">Select services to see your booking summary.</p>
                </div>
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
            <form method="POST" action="index.php?page=profile" id="addVehicleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_nickname" class="form-label">Vehicle Nickname *</label>
                        <input type="text" class="form-control" id="modal_nickname" name="nickname" placeholder="e.g., My Daily Driver" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_make" class="form-label">Make *</label>
                                <input type="text" class="form-control" id="modal_make" name="make" placeholder="e.g., Honda" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_model" class="form-label">Model *</label>
                                <input type="text" class="form-control" id="modal_model" name="model" placeholder="e.g., CR-V" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_year" class="form-label">Year *</label>
                                <input type="number" class="form-control" id="modal_year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_type" class="form-label">Type *</label>
                                <select class="form-select" id="modal_type" name="type" required>
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
                                <label for="modal_color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="modal_color" name="color" placeholder="e.g., Black">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_license_plate" class="form-label">License Plate</label>
                                <input type="text" class="form-control" id="modal_license_plate" name="license_plate" placeholder="e.g., ABC123">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="modal_is_default" name="is_default" checked>
                        <label class="form-check-label" for="modal_is_default">
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

<script>
let currentStep = 1;
const totalSteps = 4;

function nextStep() {
    if (currentStep < totalSteps) {
        document.getElementById('step' + currentStep).style.display = 'none';
        currentStep++;
        document.getElementById('step' + currentStep).style.display = 'block';
        
        if (currentStep === 4) {
            updateReview();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        document.getElementById('step' + currentStep).style.display = 'none';
        currentStep--;
        document.getElementById('step' + currentStep).style.display = 'block';
    }
}

function updateBookingSummary() {
    const selectedServices = document.querySelectorAll('input[name="services[]"]:checked');
    const summaryDiv = document.getElementById('bookingSummary');
    
    if (selectedServices.length === 0) {
        summaryDiv.innerHTML = '<p class="text-muted">Select services to see your booking summary.</p>';
        return;
    }
    
    let total = 0;
    let servicesList = '';
    
    selectedServices.forEach(service => {
        const serviceCard = service.closest('.card');
        const serviceName = serviceCard.querySelector('h6').textContent;
        const servicePrice = parseFloat(serviceCard.querySelector('.badge').textContent.replace('$', ''));
        total += servicePrice;
        servicesList += `<div class="d-flex justify-content-between mb-1"><span>${serviceName}</span><span>$${servicePrice.toFixed(2)}</span></div>`;
    });
    
    summaryDiv.innerHTML = `
        <h6>Selected Services:</h6>
        ${servicesList}
        <hr>
        <div class="d-flex justify-content-between fw-bold">
            <span>Total:</span>
            <span>$${total.toFixed(2)}</span>
        </div>
    `;
}

function updateReview() {
    const selectedServices = document.querySelectorAll('input[name="services[]"]:checked');
    const selectedVehicle = document.querySelector('input[name="vehicle_id"]:checked');
    const serviceType = document.getElementById('service_type').value;
    const appointmentDate = document.getElementById('appointment_date').value;
    const appointmentTime = document.getElementById('appointment_time').value;
    const location = document.getElementById('location').value;
    
    let reviewContent = '<div class="row">';
    
    // Services
    reviewContent += '<div class="col-12 mb-3"><h6>Selected Services:</h6><ul>';
    selectedServices.forEach(service => {
        const serviceCard = service.closest('.card');
        const serviceName = serviceCard.querySelector('h6').textContent;
        const servicePrice = serviceCard.querySelector('.badge').textContent;
        reviewContent += `<li>${serviceName} - ${servicePrice}</li>`;
    });
    reviewContent += '</ul></div>';
    
    // Vehicle
    if (selectedVehicle) {
        const vehicleCard = selectedVehicle.closest('.card');
        const vehicleName = vehicleCard.querySelector('h6').textContent;
        const vehicleDetails = vehicleCard.querySelector('p').textContent;
        reviewContent += `<div class="col-12 mb-3"><h6>Selected Vehicle:</h6><p class="mb-0">${vehicleName}<br><small class="text-muted">${vehicleDetails}</small></p></div>`;
    }
    
    // Schedule
    reviewContent += '<div class="col-12 mb-3"><h6>Appointment Details:</h6>';
    reviewContent += `<p class="mb-1"><strong>Type:</strong> ${serviceType === 'mobile' ? 'Mobile Service' : 'Facility Service'}</p>`;
    reviewContent += `<p class="mb-1"><strong>Date:</strong> ${new Date(appointmentDate).toLocaleDateString()}</p>`;
    reviewContent += `<p class="mb-1"><strong>Time:</strong> ${new Date('2000-01-01T' + appointmentTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>`;
    if (serviceType === 'mobile' && location) {
        reviewContent += `<p class="mb-0"><strong>Location:</strong> ${location}</p>`;
    }
    reviewContent += '</div>';
    
    reviewContent += '</div>';
    
    document.getElementById('reviewContent').innerHTML = reviewContent;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update summary when services are selected
    document.querySelectorAll('input[name="services[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateBookingSummary);
    });
    
    // Show/hide location field based on service type
    document.getElementById('service_type').addEventListener('change', function() {
        const locationField = document.getElementById('locationField');
        if (this.value === 'mobile') {
            locationField.style.display = 'block';
            document.getElementById('location').required = true;
        } else {
            locationField.style.display = 'none';
            document.getElementById('location').required = false;
        }
    });
    
    // Handle vehicle form submission
    document.getElementById('addVehicleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        fetch('index.php?page=profile', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            location.reload();
        });
    });
});
</script>
