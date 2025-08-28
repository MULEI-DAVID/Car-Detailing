<?php
/**
 * Database Configuration
 * 
 * Update these settings according to your MySQL server configuration.
 */

// Database connection settings
define('DB_HOST', 'localhost');        // MySQL server hostname
define('DB_USER', 'root');             // MySQL username
define('DB_PASS', '');                 // MySQL password
define('DB_NAME', 'car_detailing_db'); // Database name

// Database options
define('DB_CHARSET', 'utf8mb4');       // Character set
define('DB_COLLATION', 'utf8mb4_unicode_ci'); // Collation

// Connection timeout (in seconds)
define('DB_TIMEOUT', 30);

// Debug mode (set to false in production)
define('DB_DEBUG', true);

// Backup settings
define('BACKUP_DIR', __DIR__ . '/backups/'); // Backup directory
define('MAX_BACKUPS', 10); // Maximum number of backup files to keep

// Email settings (for notifications)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'noreply@cardetailing.com');
define('SMTP_FROM_NAME', 'Car Detailing Pro');

// Application settings
define('APP_NAME', 'Car Detailing Pro');
define('APP_URL', 'http://localhost'); // Update this to your domain
define('ADMIN_EMAIL', 'admin@cardetailing.com');

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('TOKEN_EXPIRY', 3600); // 1 hour for reset tokens

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination settings
define('ITEMS_PER_PAGE', 10);

// Timezone
date_default_timezone_set('UTC'); // Change to your timezone

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Logging
define('LOG_DIR', __DIR__ . '/../logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Cache settings
define('CACHE_ENABLED', false);
define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_TIME', 3600); // 1 hour

// API settings (if needed for future features)
define('API_ENABLED', false);
define('API_KEY_LENGTH', 32);
define('API_RATE_LIMIT', 100); // requests per hour

// Maintenance mode
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'System is under maintenance. Please try again later.');

// Development settings
define('DEV_MODE', true); // Set to false in production
define('SHOW_QUERIES', false); // Set to true to log all SQL queries

// Backup schedule (cron job settings)
define('AUTO_BACKUP', false);
define('BACKUP_SCHEDULE', 'daily'); // daily, weekly, monthly

// Notification settings
define('EMAIL_NOTIFICATIONS', true);
define('SMS_NOTIFICATIONS', false); // Requires SMS service integration

// Payment settings (for future payment integration)
define('PAYMENT_GATEWAY', 'stripe'); // stripe, paypal, etc.
define('DEFAULT_CURRENCY', 'KES'); // Kenyan Shillings
define('TAX_RATE', 0.16); // 16% VAT for Kenya

// Currency settings
define('SUPPORTED_CURRENCIES', ['KES', 'EUR', 'USD']);
define('CURRENCY_RATES', [
    'KES' => 1.0,      // Base currency
    'EUR' => 0.0015,   // 1 KES = 0.0015 EUR (approximate)
    'USD' => 0.0016    // 1 KES = 0.0016 USD (approximate)
]);

define('CURRENCY_SYMBOLS', [
    'KES' => 'KSh',
    'EUR' => '€',
    'USD' => '$'
]);

define('CURRENCY_NAMES', [
    'KES' => 'Kenyan Shilling',
    'EUR' => 'Euro',
    'USD' => 'US Dollar'
]);

// Service settings
define('MAX_BOOKINGS_PER_DAY', 50);
define('MIN_BOOKING_NOTICE', 24); // hours
define('MAX_BOOKING_ADVANCE', 30); // days

// Vehicle settings
define('MAX_VEHICLES_PER_USER', 10);
define('REQUIRE_VIN', false); // Set to true if VIN is mandatory

// Admin settings
define('ADMIN_NOTIFICATION_EMAIL', true);
define('ADMIN_NOTIFICATION_SMS', false);
define('BOOKING_CONFIRMATION_REQUIRED', true);

// Customer settings
define('EMAIL_VERIFICATION_REQUIRED', true);
define('PHONE_VERIFICATION_REQUIRED', false);
define('AUTO_LOGIN_AFTER_REGISTRATION', false);

// System limits
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hour
define('EMAIL_VERIFICATION_EXPIRY', 86400); // 24 hours

// File paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads/');
define('TEMP_PATH', ROOT_PATH . '/temp/');

// Create necessary directories if they don't exist
$directories = [
    LOG_DIR,
    CACHE_DIR,
    BACKUP_DIR,
    UPLOADS_PATH,
    TEMP_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Database connection function
function getDatabaseConnection() {
    global $conn;
    
    if (!isset($conn)) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            if (DB_DEBUG) {
                die("Connection failed: " . $conn->connect_error);
            } else {
                error_log("Database connection failed: " . $conn->connect_error);
                die("Database connection error. Please try again later.");
            }
        }
        
        // Set character set
        $conn->set_charset(DB_CHARSET);
        
        // Set timezone
        $conn->query("SET time_zone = '+00:00'");
    }
    
    return $conn;
}

// Close database connection
function closeDatabaseConnection() {
    global $conn;
    if (isset($conn)) {
        $conn->close();
        unset($conn);
    }
}

// Log function
function logMessage($level, $message, $context = []) {
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0755, true);
    }
    
    $logFile = LOG_DIR . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (DB_DEBUG) {
        echo "<b>Error:</b> [$errno] $errstr<br>";
        echo "<b>File:</b> $errfile<br>";
        echo "<b>Line:</b> $errline<br>";
    } else {
        logMessage('ERROR', "$errstr in $errfile on line $errline");
    }
    
    return true;
}

// Set error handler
set_error_handler('customErrorHandler');

// Shutdown function to close database connection
register_shutdown_function('closeDatabaseConnection');
?>
