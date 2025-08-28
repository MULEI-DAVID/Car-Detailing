<?php
session_start();
require_once 'database/config.php';
require_once 'includes/functions.php';
require_once 'includes/currency.php';

// Initialize database connection
$conn = getDatabaseConnection();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Detailing Pro - Professional Car Care Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car-wash me-2"></i>Car Detailing Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=contact">Contact</a>
                    </li>
                </ul>
                                       <ul class="navbar-nav me-3">
                           <li class="nav-item">
                               <?php echo displayCurrencySelector('me-2'); ?>
                           </li>
                       </ul>
                       <ul class="navbar-nav">
                           <?php if ($isLoggedIn): ?>
                               <?php if ($isAdmin): ?>
                                   <li class="nav-item">
                                       <a class="nav-link" href="index.php?page=admin">Admin Dashboard</a>
                                   </li>
                               <?php else: ?>
                                   <li class="nav-item dropdown">
                                       <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                           <i class="fas fa-user-circle me-1"></i>My Profile
                                       </a>
                                       <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                           <li><a class="dropdown-item" href="index.php?page=profile">
                                               <i class="fas fa-user me-2"></i>Profile Information
                                           </a></li>
                                           <li><a class="dropdown-item" href="index.php?page=profile#garage">
                                               <i class="fas fa-car me-2"></i>My Garage
                                           </a></li>
                                           <li><a class="dropdown-item" href="index.php?page=profile#appointments">
                                               <i class="fas fa-calendar me-2"></i>My Appointments
                                           </a></li>
                                           <li><hr class="dropdown-divider"></li>
                                           <li><a class="dropdown-item" href="index.php?page=booking">
                                               <i class="fas fa-plus me-2"></i>Book New Service
                                           </a></li>
                                       </ul>
                                   </li>
                               <?php endif; ?>
                               <li class="nav-item">
                                   <a class="nav-link" href="index.php?logout=1">Logout</a>
                               </li>
                           <?php else: ?>
                               <li class="nav-item">
                                   <a class="nav-link" href="index.php?page=login">Login</a>
                               </li>
                               <li class="nav-item">
                                   <a class="nav-link" href="index.php?page=register">Register</a>
                               </li>
                           <?php endif; ?>
                       </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-4">
        <?php
        // Route to appropriate page
        switch ($page) {
            case 'home':
                include 'pages/home.php';
                break;
            case 'login':
                include 'pages/login.php';
                break;
            case 'register':
                include 'pages/register.php';
                break;
            case 'profile':
                if (!$isLoggedIn) {
                    header('Location: index.php?page=login');
                    exit();
                }
                include 'pages/profile.php';
                break;
            case 'admin':
                if (!$isLoggedIn || !$isAdmin) {
                    header('Location: index.php?page=login');
                    exit();
                }
                include 'pages/admin.php';
                break;
            case 'admin_bookings':
                if (!$isLoggedIn || !$isAdmin) {
                    header('Location: index.php?page=login');
                    exit();
                }
                include 'pages/admin_bookings.php';
                break;
            case 'admin_users':
                if (!$isLoggedIn || !$isAdmin) {
                    header('Location: index.php?page=login');
                    exit();
                }
                include 'pages/admin_users.php';
                break;
            case 'admin_services':
                if (!$isLoggedIn || !$isAdmin) {
                    header('Location: index.php?page=login');
                    exit();
                }
                include 'pages/admin_services.php';
                break;
            case 'services':
                include 'pages/services.php';
                break;
            case 'booking':
                if (!$isLoggedIn) {
                    header('Location: index.php?page=login');
                    exit();
                }
                include 'pages/booking.php';
                break;
            case 'about':
                include 'pages/about.php';
                break;
            case 'contact':
                include 'pages/contact.php';
                break;
            default:
                include 'pages/home.php';
                break;
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Car Detailing Pro</h5>
                    <p>Professional car care services for your vehicle.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 Car Detailing Pro. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <script>
    // Enhanced profile dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Handle anchor links for smooth scrolling
        const anchorLinks = document.querySelectorAll('a[href*="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href.includes('#')) {
                    const targetId = href.split('#')[1];
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        e.preventDefault();
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        
                        // Add highlight effect
                        targetElement.style.transition = 'background-color 0.3s ease';
                        targetElement.style.backgroundColor = '#fff3cd';
                        setTimeout(() => {
                            targetElement.style.backgroundColor = '';
                        }, 2000);
                    }
                }
            });
        });
        
        // Profile dropdown enhancement
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileDropdown) {
            profileDropdown.addEventListener('click', function(e) {
                // Add visual feedback
                this.classList.add('active');
                setTimeout(() => {
                    this.classList.remove('active');
                }, 200);
            });
        }
        
        // Auto-close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target) && !e.target.closest('.dropdown-toggle')) {
                    dropdown.classList.remove('show');
                }
            });
        });
    });
    </script>
</body>
</html>
