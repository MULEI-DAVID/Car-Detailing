<?php
@session_start();
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Access denied. Please log in.</div>';
    exit();
}

$conn = getDatabaseConnection();

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">Vehicle ID is required.</div>';
    exit();
}

$vehicle_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get vehicle data (ensure it belongs to the logged-in user)
$sql = "SELECT * FROM vehicles WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $vehicle_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Vehicle not found or access denied.</div>';
    exit();
}

$vehicle = $result->fetch_assoc();
?>

<form id="editVehicleForm" method="POST" action="ajax/update_vehicle.php">
    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
    
    <div class="mb-3">
        <label for="nickname" class="form-label">Vehicle Nickname *</label>
        <input type="text" class="form-control" id="nickname" name="nickname" 
               value="<?php echo htmlspecialchars($vehicle['nickname']); ?>" required>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="make" class="form-label">Make *</label>
                <input type="text" class="form-control" id="make" name="make" 
                       value="<?php echo htmlspecialchars($vehicle['make']); ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="model" class="form-label">Model *</label>
                <input type="text" class="form-control" id="model" name="model" 
                       value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="year" class="form-label">Year *</label>
                <input type="number" class="form-control" id="year" name="year" 
                       value="<?php echo htmlspecialchars($vehicle['year']); ?>" 
                       min="1900" max="<?php echo date('Y') + 1; ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="type" class="form-label">Type *</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">Select Type</option>
                    <option value="Sedan" <?php echo $vehicle['type'] == 'Sedan' ? 'selected' : ''; ?>>Sedan</option>
                    <option value="SUV" <?php echo $vehicle['type'] == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                    <option value="Truck" <?php echo $vehicle['type'] == 'Truck' ? 'selected' : ''; ?>>Truck</option>
                    <option value="Van" <?php echo $vehicle['type'] == 'Van' ? 'selected' : ''; ?>>Van</option>
                    <option value="Coupe" <?php echo $vehicle['type'] == 'Coupe' ? 'selected' : ''; ?>>Coupe</option>
                    <option value="Wagon" <?php echo $vehicle['type'] == 'Wagon' ? 'selected' : ''; ?>>Wagon</option>
                    <option value="Hatchback" <?php echo $vehicle['type'] == 'Hatchback' ? 'selected' : ''; ?>>Hatchback</option>
                    <option value="Convertible" <?php echo $vehicle['type'] == 'Convertible' ? 'selected' : ''; ?>>Convertible</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="color" class="form-label">Color</label>
                <input type="text" class="form-control" id="color" name="color" 
                       value="<?php echo htmlspecialchars($vehicle['color']); ?>" placeholder="e.g., Black">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="license_plate" class="form-label">License Plate</label>
                <input type="text" class="form-control" id="license_plate" name="license_plate" 
                       value="<?php echo htmlspecialchars($vehicle['license_plate']); ?>" placeholder="e.g., ABC123">
            </div>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="vin" class="form-label">VIN (Optional)</label>
        <input type="text" class="form-control" id="vin" name="vin" 
               value="<?php echo htmlspecialchars($vehicle['vin']); ?>" placeholder="17-character VIN">
    </div>
    
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" 
               <?php echo $vehicle['is_default'] ? 'checked' : ''; ?>>
        <label class="form-check-label" for="is_default">
            Set as default vehicle
        </label>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Update Vehicle
        </button>
    </div>
</form>

<script>
document.getElementById('editVehicleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('ajax/update_vehicle.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            
            // Update the vehicle card on the page without full reload
            if (data.vehicle) {
                updateVehicleCard(data.vehicle);
            }
            
            bootstrap.Modal.getInstance(document.getElementById('editVehicleModal')).hide();
            
            // Show a brief success message before any redirect
            setTimeout(() => {
                // Optionally refresh the page or update specific elements
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Failed to update vehicle');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while updating the vehicle');
    });
});

// Function to update vehicle card on the page
function updateVehicleCard(vehicle) {
    // Find the vehicle card by ID and update its content
    const vehicleCard = document.querySelector(`[data-vehicle-id="${vehicle.id}"]`);
    if (vehicleCard) {
        // Update vehicle nickname
        const nicknameElement = vehicleCard.querySelector('.card-title');
        if (nicknameElement) {
            nicknameElement.textContent = vehicle.nickname;
        }
        
        // Update vehicle details
        const detailsElement = vehicleCard.querySelector('.card-text');
        if (detailsElement) {
            detailsElement.textContent = `${vehicle.year} ${vehicle.make} ${vehicle.model}`;
        }
        
        // Update type and color
        const typeColorElement = vehicleCard.querySelector('.card-text:nth-child(3)');
        if (typeColorElement) {
            let typeColorText = `Type: ${vehicle.type}`;
            if (vehicle.color) {
                typeColorText += ` | Color: ${vehicle.color}`;
            }
            typeColorElement.textContent = typeColorText;
        }
        
        // Update license plate if exists
        if (vehicle.license_plate) {
            const plateElement = vehicleCard.querySelector('.card-text:nth-child(4)');
            if (plateElement) {
                plateElement.textContent = `Plate: ${vehicle.license_plate}`;
            }
        }
        
        // Update default badge
        const defaultBadge = vehicleCard.querySelector('.badge');
        if (defaultBadge) {
            if (vehicle.is_default) {
                defaultBadge.textContent = 'Default';
                defaultBadge.className = 'badge bg-primary';
            } else {
                defaultBadge.textContent = '';
                defaultBadge.className = 'badge';
            }
        }
    }
}
</script>
