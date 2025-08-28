// Car Detailing Pro - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation
    initializeFormValidation();

    // Booking form functionality
    initializeBookingForm();

    // Admin dashboard functionality
    initializeAdminDashboard();
});

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Booking form functionality
function initializeBookingForm() {
    const bookingForm = document.getElementById('bookingForm');
    if (!bookingForm) return;

    // Service selection validation
    const serviceCheckboxes = document.querySelectorAll('input[name="services[]"]');
    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            validateServiceSelection();
        });
    });

    // Vehicle selection validation
    const vehicleRadios = document.querySelectorAll('input[name="vehicle_id"]');
    vehicleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            validateVehicleSelection();
        });
    });

    // Date validation
    const dateInput = document.getElementById('appointment_date');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            validateDateSelection();
        });
    }
}

// Validate service selection
function validateServiceSelection() {
    const selectedServices = document.querySelectorAll('input[name="services[]"]:checked');
    const nextButton = document.querySelector('#step1 .btn-primary');
    
    if (selectedServices.length > 0) {
        nextButton.disabled = false;
        nextButton.classList.remove('btn-secondary');
        nextButton.classList.add('btn-primary');
    } else {
        nextButton.disabled = true;
        nextButton.classList.remove('btn-primary');
        nextButton.classList.add('btn-secondary');
    }
}

// Validate vehicle selection
function validateVehicleSelection() {
    const selectedVehicle = document.querySelector('input[name="vehicle_id"]:checked');
    const nextButton = document.querySelector('#step2 .btn-primary');
    
    if (selectedVehicle) {
        nextButton.disabled = false;
        nextButton.classList.remove('btn-secondary');
        nextButton.classList.add('btn-primary');
    } else {
        nextButton.disabled = true;
        nextButton.classList.remove('btn-primary');
        nextButton.classList.add('btn-secondary');
    }
}

// Validate date selection
function validateDateSelection() {
    const dateInput = document.getElementById('appointment_date');
    const selectedDate = new Date(dateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        dateInput.setCustomValidity('Please select a future date');
        dateInput.classList.add('is-invalid');
    } else {
        dateInput.setCustomValidity('');
        dateInput.classList.remove('is-invalid');
        dateInput.classList.add('is-valid');
    }
}

// Admin dashboard functionality
function initializeAdminDashboard() {
    // Initialize admin-specific features
    const adminTabs = document.querySelectorAll('#adminTabs .nav-link');
    adminTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Add loading state
            const targetId = this.getAttribute('data-bs-target').substring(1);
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';
            }
        });
    });
}

// Utility functions
function showLoading(element) {
    element.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>';
}

function hideLoading(element, content) {
    element.innerHTML = content;
}

function showAlert(message, type = 'info') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertContainer, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertContainer);
        bsAlert.close();
    }, 5000);
}

// Currency change function
function changeCurrency(currency) {
    // Show loading state
    const selector = document.getElementById('currency-selector');
    const originalValue = selector.value;
    selector.disabled = true;
    
    // Send AJAX request to change currency
    fetch('ajax/change_currency.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'currency=' + currency
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text().then(text => {
            console.log('Raw response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response text:', text);
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response: ' + e.message);
            }
        });
    })
    .then(data => {
        if (data.success) {
            // Reload page to update all prices
            location.reload();
        } else {
            // Revert selector on error
            selector.value = originalValue;
            showAlert('Error changing currency: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        // Revert selector on error
        selector.value = originalValue;
        showAlert('Error changing currency: ' + error.message, 'danger');
    })
    .finally(() => {
        selector.disabled = false;
    });
}

// Format currency for display
function formatCurrency(amount, currency = 'KES') {
    const symbols = {
        'KES': 'KSh',
        'EUR': '€',
        'USD': '$'
    };
    
    const symbol = symbols[currency] || currency;
    const formattedAmount = parseFloat(amount).toFixed(2);
    
    switch (currency) {
        case 'KES':
            return `KSh ${formattedAmount}`;
        case 'EUR':
            return `€${formattedAmount}`;
        case 'USD':
            return `$${formattedAmount}`;
        default:
            return `${symbol} ${formattedAmount}`;
    }
}

// Convert currency (client-side)
function convertCurrency(amount, fromCurrency, toCurrency) {
    const rates = {
        'KES': 1.0,
        'EUR': 0.0015,
        'USD': 0.0016
    };
    
    if (fromCurrency === toCurrency) {
        return amount;
    }
    
    // Convert to KES first
    if (fromCurrency !== 'KES') {
        amount = amount / rates[fromCurrency];
    }
    
    // Convert from KES to target
    if (toCurrency !== 'KES') {
        amount = amount * rates[toCurrency];
    }
    
    return Math.round(amount * 100) / 100;
}

// Update prices on currency change
function updatePrices(currency) {
    const priceElements = document.querySelectorAll('[data-price]');
    priceElements.forEach(element => {
        const originalPrice = parseFloat(element.getAttribute('data-price'));
        const convertedPrice = convertCurrency(originalPrice, 'KES', currency);
        element.textContent = formatCurrency(convertedPrice, currency);
    });
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    return new Date(`2000-01-01T${timeString}`).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// AJAX utility functions
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
        ...options
    };

    return fetch(url, defaultOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Request failed:', error);
            showAlert('An error occurred. Please try again.', 'danger');
            throw error;
        });
}

// Form submission with AJAX
function submitForm(formElement, successCallback = null) {
    const formData = new FormData(formElement);
    
    fetch(formElement.action, {
        method: formElement.method,
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (successCallback) {
            successCallback(data);
        } else {
            showAlert('Form submitted successfully!', 'success');
        }
    })
    .catch(error => {
        console.error('Form submission failed:', error);
        showAlert('Form submission failed. Please try again.', 'danger');
    });
}

// Modal utilities
function openModal(modalId) {
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

function closeModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

// Table utilities
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // Try to parse as numbers first
        const aNum = parseFloat(aValue.replace(/[^0-9.-]+/g, ''));
        const bNum = parseFloat(bValue.replace(/[^0-9.-]+/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return aNum - bNum;
        }
        
        // Fall back to string comparison
        return aValue.localeCompare(bValue);
    });
    
    // Clear and re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

function filterTable(table, searchTerm) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches ? '' : 'none';
    });
}

// Export functions
function exportTableToCSV(table, filename = 'export.csv') {
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => {
            // Remove HTML tags and escape quotes
            let text = col.textContent.replace(/"/g, '""');
            return `"${text}"`;
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    
    window.URL.revokeObjectURL(url);
}

// Print utilities
function printElement(element) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none !important; }
                    }
                </style>
            </head>
            <body>
                ${element.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Local storage utilities
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (error) {
        console.error('Failed to save to localStorage:', error);
    }
}

function loadFromLocalStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (error) {
        console.error('Failed to load from localStorage:', error);
        return null;
    }
}

function removeFromLocalStorage(key) {
    try {
        localStorage.removeItem(key);
    } catch (error) {
        console.error('Failed to remove from localStorage:', error);
    }
}

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle utility
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Global error handler
window.addEventListener('error', function(event) {
    console.error('Global error:', event.error);
    showAlert('An unexpected error occurred. Please refresh the page.', 'danger');
});

// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    showAlert('An unexpected error occurred. Please try again.', 'danger');
});
