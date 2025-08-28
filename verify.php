<?php
session_start();
require_once 'database/config.php';
$conn = getDatabaseConnection();
require_once 'includes/functions.php';

$message = '';
$message_type = '';

if (isset($_GET['token'])) {
    $token = sanitize_input($_GET['token']);
    
    // Find user with this verification token
    $sql = "SELECT id, full_name, email_verified FROM users WHERE verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['email_verified'] == 0) {
            // Verify the email
            $update_sql = "UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            
            if ($update_stmt->execute()) {
                $message = "Email verified successfully! You can now log in to your account.";
                $message_type = 'success';
            } else {
                $message = "Error verifying email. Please try again.";
                $message_type = 'error';
            }
        } else {
            $message = "Email is already verified. You can log in to your account.";
            $message_type = 'info';
        }
    } else {
        $message = "Invalid verification token. Please check your email or contact support.";
        $message_type = 'error';
    }
} else {
    $message = "No verification token provided.";
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Car Detailing Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-envelope me-2"></i>Email Verification
                        </h3>
                    </div>
                    <div class="card-body p-4 text-center">
                        <?php if ($message_type == 'success'): ?>
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                        <?php elseif ($message_type == 'error'): ?>
                            <div class="text-danger mb-3">
                                <i class="fas fa-exclamation-circle fa-3x"></i>
                            </div>
                        <?php else: ?>
                            <div class="text-info mb-3">
                                <i class="fas fa-info-circle fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4 class="mb-3"><?php echo $message; ?></h4>
                        
                        <div class="mt-4">
                            <a href="index.php?page=login" class="btn btn-primary btn-lg">Go to Login</a>
                        </div>
                        
                        <div class="mt-3">
                            <a href="index.php" class="text-decoration-none">Return to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
