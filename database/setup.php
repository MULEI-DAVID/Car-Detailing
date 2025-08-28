<?php
/**
 * Database Setup Script
 * 
 * This script sets up the complete database for the Car Detailing Booking System.
 * Run this script once to initialize your database.
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'car_detailing_db';

echo "=== Car Detailing Booking System - Database Setup ===\n\n";

try {
    // Create connection without database
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Connected to MySQL server\n";
    
    // Read and execute schema file
    $schemaFile = __DIR__ . '/schema.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    echo "✓ Found schema file\n";
    
    $sql = file_get_contents($schemaFile);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "✓ Executing database schema...\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue; // Skip comments and empty lines
        }
        
        if ($conn->query($statement) === TRUE) {
            $successCount++;
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        } else {
            $errorCount++;
            echo "✗ Error: " . $conn->error . "\n";
            echo "  Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    echo "\n=== Setup Summary ===\n";
    echo "Successful statements: $successCount\n";
    echo "Failed statements: $errorCount\n";
    
    if ($errorCount == 0) {
        echo "\n🎉 Database setup completed successfully!\n\n";
        echo "=== Default Login Credentials ===\n";
        echo "Admin Account:\n";
        echo "  Email: admin@cardetailing.com\n";
        echo "  Password: admin123\n\n";
        echo "Test User Account:\n";
        echo "  Email: john@example.com\n";
        echo "  Password: user123\n\n";
        echo "=== Next Steps ===\n";
        echo "1. Update database configuration in database/config.php if needed\n";
        echo "2. Access the application at your web server URL\n";
        echo "3. Login with the admin credentials to manage the system\n";
    } else {
        echo "\n⚠️  Database setup completed with errors. Please check the output above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration and try again.\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo "\n=== Setup Complete ===\n";
?>
