<?php
// Helper functions for the car detailing system
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/currency.php';

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Generate random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Send email (placeholder function - in production, use a proper email service)
 */
function send_email($to, $subject, $message) {
    // In a real application, use PHPMailer or similar
    // For now, we'll just log the email
    error_log("Email to: $to, Subject: $subject, Message: $message");
    return true;
}

/**
 * Validate email format
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Get current user data
 */
function get_current_user_data() {
    global $conn;
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get user vehicles
 */
function get_user_vehicles($user_id) {
    global $conn;
    $sql = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY is_default DESC, created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get user bookings
 */
function get_user_bookings($user_id) {
    global $conn;
    $sql = "SELECT b.*, v.nickname as vehicle_nickname, v.make, v.model, v.year, v.color,
                   GROUP_CONCAT(s.name SEPARATOR ', ') as services
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.id
            LEFT JOIN booking_services bs ON b.id = bs.booking_id
            LEFT JOIN services s ON bs.service_id = s.id
            WHERE b.user_id = ?
            GROUP BY b.id
            ORDER BY b.appointment_date DESC, b.appointment_time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all services
 */
function get_services($category = null) {
    global $conn;
    $sql = "SELECT * FROM services WHERE is_active = 1";
    if ($category) {
        $sql .= " AND category = ?";
    }
    $sql .= " ORDER BY category, price";
    
    $stmt = $conn->prepare($sql);
    if ($category) {
        $stmt->bind_param("s", $category);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get booking details
 */
function get_booking_details($booking_id) {
    global $conn;
    $sql = "SELECT b.*, u.full_name, u.email, u.phone,
                   v.nickname as vehicle_nickname, v.make, v.model, v.year, v.color, v.license_plate,
                   GROUP_CONCAT(s.name SEPARATOR ', ') as services
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN vehicles v ON b.vehicle_id = v.id
            LEFT JOIN booking_services bs ON b.id = bs.booking_id
            LEFT JOIN services s ON bs.service_id = s.id
            WHERE b.id = ?
            GROUP BY b.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Format date for display
 */
function format_date($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Format time for display
 */
function format_time($time) {
    return date('g:i A', strtotime($time));
}

/**
 * Format currency
 */
function format_currency($amount, $currency = null) {
    // Handle null or empty amount
    if ($amount === null || $amount === '') {
        $amount = 0;
    }
    
    // Ensure amount is numeric
    $amount = floatval($amount);
    
    if ($currency === null) {
        $currency = getCurrentCurrency();
    }
    
    $symbol = CURRENCY_SYMBOLS[$currency];
    $formattedAmount = number_format($amount, 2);
    
    // Different formatting for different currencies
    switch ($currency) {
        case 'KES':
            return "KSh " . $formattedAmount;
        case 'EUR':
            return "€" . $formattedAmount;
        case 'USD':
            return "$" . $formattedAmount;
        default:
            return $symbol . " " . $formattedAmount;
    }
}

/**
 * Get status badge class
 */
function get_status_badge_class($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning';
        case 'confirmed':
            return 'bg-info';
        case 'in_progress':
            return 'bg-primary';
        case 'completed':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

/**
 * Get status display text
 */
function get_status_display($status) {
    switch ($status) {
        case 'pending':
            return 'Pending';
        case 'confirmed':
            return 'Confirmed';
        case 'in_progress':
            return 'In Progress';
        case 'completed':
            return 'Completed';
        case 'cancelled':
            return 'Cancelled';
        default:
            return ucfirst($status);
    }
}

/**
 * Check if date is available for booking
 */
function is_date_available($date, $time, $service_type = 'facility') {
    global $conn;
    
    // For now, we'll allow multiple bookings on the same day
    // In a real system, you'd check against available time slots
    return true;
}

/**
 * Calculate total booking amount
 */
function calculate_booking_total($service_ids) {
    global $conn;
    $total = 0;
    
    foreach ($service_ids as $service_id) {
        $sql = "SELECT price FROM services WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service = $result->fetch_assoc();
        
        if ($service) {
            $total += $service['price'];
        }
    }
    
    return $total;
}

/**
 * Set flash message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message
 */
function display_flash_message() {
    $flash = get_flash_message();
    if ($flash) {
        $alert_class = $flash['type'] === 'error' ? 'alert-danger' : 'alert-success';
        echo "<div class='alert $alert_class alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}
?>
