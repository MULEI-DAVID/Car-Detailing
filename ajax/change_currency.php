<?php
/**
 * AJAX Currency Change Handler
 */

// Suppress warnings and notices for clean JSON output
error_reporting(E_ERROR | E_PARSE);

// Ensure no whitespace or BOM issues
if (ob_get_level()) {
    ob_end_clean();
}

// Start output buffering
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../includes/currency.php';

// Initialize database connection (needed for any potential database operations)
$conn = getDatabaseConnection();

// Clear any output that might have been generated
ob_clean();

// Set JSON header (suppress warnings)
@header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    @http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$currency = $_POST['currency'] ?? '';

if (empty($currency)) {
    echo json_encode(['success' => false, 'error' => 'Currency is required']);
    exit;
}

try {
    if (setCurrentCurrency($currency)) {
        echo json_encode([
            'success' => true, 
            'currency' => $currency,
            'symbol' => CURRENCY_SYMBOLS[$currency],
            'flag' => getCurrencyFlag($currency)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid currency']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

// Ensure clean output
if (ob_get_level()) {
    ob_end_flush();
}
?>
