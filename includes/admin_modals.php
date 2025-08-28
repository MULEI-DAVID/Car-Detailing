<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetailsContent">
                <!-- Booking details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addServiceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="service_name" class="form-label">Service Name *</label>
                        <input type="text" class="form-control" id="service_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="service_description" class="form-label">Description</label>
                        <textarea class="form-control" id="service_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="service_price" class="form-label">Price (<?php echo getCurrentCurrency(); ?>) *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo CURRENCY_SYMBOLS[getCurrentCurrency()]; ?></span>
                                    <input type="number" class="form-control" id="service_price" name="price" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="service_duration" class="form-label">Duration (minutes) *</label>
                                <input type="number" class="form-control" id="service_duration" name="duration" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="service_category" class="form-label">Category *</label>
                        <select class="form-select" id="service_category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="package">Package</option>
                            <option value="addon">Add-on</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="user_full_name" name="full_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="user_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_phone" class="form-label">Phone *</label>
                                <input type="tel" class="form-control" id="user_phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="user_password" name="password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="user_is_admin" name="is_admin">
                            <label class="form-check-label" for="user_is_admin">
                                Admin Privileges
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBookingForm">
                <div class="modal-body" id="editBookingContent">
                    <!-- Edit booking form will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item? This action cannot be undone.</p>
                <div id="deleteItemDetails">
                    <!-- Item details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- System Settings Modal -->
<div class="modal fade" id="systemSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">System Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="systemSettingsForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>General Settings</h6>
                            <div class="mb-3">
                                <label for="modal_site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="modal_site_name" name="site_name">
                            </div>
                            <div class="mb-3">
                                <label for="modal_default_currency" class="form-label">Default Currency</label>
                                <select class="form-select" id="modal_default_currency" name="default_currency">
                                    <option value="KES">Kenyan Shilling (KES)</option>
                                    <option value="USD">US Dollar (USD)</option>
                                    <option value="EUR">Euro (EUR)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Security Settings</h6>
                            <div class="mb-3">
                                <label for="modal_session_timeout" class="form-label">Session Timeout (minutes)</label>
                                <input type="number" class="form-control" id="modal_session_timeout" name="session_timeout">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modal_email_verification" name="email_verification">
                                    <label class="form-check-label" for="modal_email_verification">
                                        Require Email Verification
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="systemSettingsForm" class="btn btn-primary">Save Settings</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Data Modal -->
<div class="modal fade" id="exportDataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportDataForm">
                    <div class="mb-3">
                        <label for="export_type" class="form-label">Export Type</label>
                        <select class="form-select" id="export_type" name="export_type" required>
                            <option value="">Select Export Type</option>
                            <option value="users">Users</option>
                            <option value="bookings">Bookings</option>
                            <option value="services">Services</option>
                            <option value="reports">Reports</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Format</label>
                        <select class="form-select" id="export_format" name="export_format" required>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="export_date_range" class="form-label">Date Range</label>
                        <select class="form-select" id="export_date_range" name="export_date_range">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="row" id="customDateRange" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="export_start_date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="export_end_date" name="end_date">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="exportDataForm" class="btn btn-primary">Export</button>
            </div>
        </div>
    </div>
</div>

