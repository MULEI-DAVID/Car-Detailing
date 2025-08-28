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
?>

<form id="addServiceForm">
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary"><i class="fas fa-cogs me-2"></i>Service Details</h6>
            <div class="mb-3">
                <label for="name" class="form-label">Service Name</label>
                <input type="text" class="form-control" name="name" id="name" 
                       placeholder="Enter service name..." required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" name="category" id="category" required>
                    <option value="">Select category...</option>
                    <option value="package">Package</option>
                    <option value="addon">Add-on</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price (KES)</label>
                <input type="number" class="form-control" name="price" id="price" 
                       placeholder="0.00" step="0.01" min="0" required>
            </div>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-primary"><i class="fas fa-clock me-2"></i>Service Settings</h6>
            <div class="mb-3">
                <label for="duration" class="form-label">Duration (minutes)</label>
                <input type="number" class="form-control" name="duration" id="duration" 
                       placeholder="30" min="1" required>
            </div>
            <div class="mb-3">
                <label for="is_active" class="form-label">Status</label>
                <select class="form-select" name="is_active" id="is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-primary"><i class="fas fa-align-left me-2"></i>Description</h6>
            <div class="mb-3">
                <textarea class="form-control" name="description" id="description" rows="4" 
                          placeholder="Enter service description..."></textarea>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> All fields marked with * are required. The service will be immediately available for booking if set to Active.
            </div>
        </div>
    </div>
</form>
