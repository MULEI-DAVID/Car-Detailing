<?php
// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    $errors = [];
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                if ($user['email_verified'] == 1) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    
                    set_flash_message('success', 'Welcome back, ' . $user['full_name'] . '!');
                    
                    if ($user['is_admin']) {
                        header('Location: index.php?page=admin');
                    } else {
                        header('Location: index.php?page=profile');
                    }
                    exit();
                } else {
                    $errors[] = "Please verify your email address before logging in. Check your inbox for a verification link.";
                }
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}

// Handle forgot password
if (isset($_POST['forgot_password'])) {
    $email = sanitize_input($_POST['forgot_email']);
    
    if (empty($email)) {
        $errors[] = "Please enter your email address";
    } elseif (!is_valid_email($email)) {
        $errors[] = "Please enter a valid email address";
    } else {
        // Check if user exists
        $sql = "SELECT id, full_name FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $reset_token = generate_token();
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Update user with reset token
            $update_sql = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $reset_token, $reset_expires, $user['id']);
            
            if ($update_stmt->execute()) {
                // Send reset email
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $reset_token;
                $email_subject = "Reset Your Password - Car Detailing Pro";
                $email_message = "Hello {$user['full_name']},\n\nYou requested to reset your password. Click the following link to reset it:\n\n$reset_link\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nCar Detailing Pro Team";
                
                send_email($email, $email_subject, $email_message);
                
                set_flash_message('success', 'Password reset instructions have been sent to your email address.');
            } else {
                $errors[] = "Error processing request. Please try again.";
            }
        } else {
            // Don't reveal if email exists or not for security
            set_flash_message('success', 'If an account with that email exists, password reset instructions have been sent.');
        }
    }
}

display_flash_message();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="mb-0">Sign In</h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-0">Don't have an account? <a href="index.php?page=register" class="text-decoration-none">Create Account</a></p>
                </div>
                
                <hr class="my-4">
                
                <!-- Forgot Password Section -->
                <div class="text-center">
                    <button type="button" class="btn btn-link text-decoration-none" data-bs-toggle="collapse" data-bs-target="#forgotPassword">
                        Forgot your password?
                    </button>
                </div>
                
                <div class="collapse" id="forgotPassword">
                    <div class="card card-body bg-light mt-3">
                        <h6>Reset Password</h6>
                        <p class="text-muted small">Enter your email address and we'll send you instructions to reset your password.</p>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="forgot_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="forgot_email" name="forgot_email" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="forgot_password" class="btn btn-outline-primary">Send Reset Instructions</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

