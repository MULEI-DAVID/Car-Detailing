<?php
/**
 * Database Backup Script
 * 
 * This script creates a backup of the car detailing database.
 * Can be run manually or via cron job for automated backups.
 */

require_once 'config.php';

echo "=== Car Detailing Database Backup ===\n\n";

try {
    // Create backup directory if it doesn't exist
    if (!is_dir(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 0755, true);
        echo "✓ Created backup directory\n";
    }
    
    // Generate backup filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = BACKUP_DIR . "car_detailing_backup_{$timestamp}.sql";
    
    // Build mysqldump command
    $command = sprintf(
        'mysqldump -h %s -u %s %s %s > %s',
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_USER),
        DB_PASS ? '-p' . escapeshellarg(DB_PASS) : '',
        escapeshellarg(DB_NAME),
        escapeshellarg($backupFile)
    );
    
    echo "✓ Creating backup...\n";
    
    // Execute backup command
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Backup created successfully: " . basename($backupFile) . "\n";
        
        // Get file size
        $fileSize = filesize($backupFile);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);
        echo "✓ Backup size: {$fileSizeMB} MB\n";
        
        // Clean up old backups
        cleanOldBackups();
        
        // Log the backup
        logMessage('INFO', "Database backup created: " . basename($backupFile), [
            'size' => $fileSizeMB . ' MB',
            'timestamp' => $timestamp
        ]);
        
        echo "\n🎉 Backup completed successfully!\n";
        
    } else {
        throw new Exception("Backup failed with return code: $returnCode\nOutput: " . implode("\n", $output));
    }
    
} catch (Exception $e) {
    echo "❌ Backup failed: " . $e->getMessage() . "\n";
    logMessage('ERROR', "Database backup failed: " . $e->getMessage());
    exit(1);
}

/**
 * Clean up old backup files
 */
function cleanOldBackups() {
    $backupFiles = glob(BACKUP_DIR . 'car_detailing_backup_*.sql');
    
    if (count($backupFiles) <= MAX_BACKUPS) {
        return;
    }
    
    // Sort files by modification time (oldest first)
    usort($backupFiles, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // Remove oldest files
    $filesToRemove = array_slice($backupFiles, 0, count($backupFiles) - MAX_BACKUPS);
    
    foreach ($filesToRemove as $file) {
        if (unlink($file)) {
            echo "✓ Removed old backup: " . basename($file) . "\n";
            logMessage('INFO', "Removed old backup: " . basename($file));
        }
    }
}

/**
 * Test database connection
 */
function testConnection() {
    try {
        $conn = getDatabaseConnection();
        echo "✓ Database connection test successful\n";
        return true;
    } catch (Exception $e) {
        echo "❌ Database connection test failed: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Get database statistics
 */
function getDatabaseStats() {
    try {
        $conn = getDatabaseConnection();
        
        $stats = [];
        
        // Get table counts
        $tables = ['users', 'vehicles', 'services', 'bookings', 'booking_services'];
        
        foreach ($tables as $table) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            $row = $result->fetch_assoc();
            $stats[$table] = $row['count'];
        }
        
        echo "\n=== Database Statistics ===\n";
        echo "Users: " . $stats['users'] . "\n";
        echo "Vehicles: " . $stats['vehicles'] . "\n";
        echo "Services: " . $stats['services'] . "\n";
        echo "Bookings: " . $stats['bookings'] . "\n";
        echo "Booking Services: " . $stats['booking_services'] . "\n";
        
        return $stats;
        
    } catch (Exception $e) {
        echo "❌ Failed to get database statistics: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Verify backup integrity
 */
function verifyBackup($backupFile) {
    echo "✓ Verifying backup integrity...\n";
    
    // Check if file exists and has content
    if (!file_exists($backupFile)) {
        throw new Exception("Backup file not found");
    }
    
    $fileSize = filesize($backupFile);
    if ($fileSize === 0) {
        throw new Exception("Backup file is empty");
    }
    
    // Check for SQL content
    $content = file_get_contents($backupFile);
    if (strpos($content, '-- MySQL dump') === false) {
        throw new Exception("Backup file does not appear to be a valid MySQL dump");
    }
    
    echo "✓ Backup verification passed\n";
    return true;
}

// If run from command line, show additional information
if (php_sapi_name() === 'cli') {
    echo "\n=== Additional Information ===\n";
    
    // Test connection
    testConnection();
    
    // Get database stats
    getDatabaseStats();
    
    // Verify backup if it was created
    if (isset($backupFile) && file_exists($backupFile)) {
        verifyBackup($backupFile);
    }
    
    echo "\n=== Backup Location ===\n";
    echo "Backup directory: " . BACKUP_DIR . "\n";
    echo "Current backup: " . (isset($backupFile) ? basename($backupFile) : 'None') . "\n";
    
    echo "\n=== Usage ===\n";
    echo "Manual backup: php backup.php\n";
    echo "Automated backup (cron): 0 2 * * * /usr/bin/php " . __FILE__ . "\n";
    echo "Restore backup: mysql -u username -p database_name < backup_file.sql\n";
}

echo "\n=== Backup Complete ===\n";
?>

