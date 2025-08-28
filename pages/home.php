<?php
display_flash_message();
?>

<!-- Hero Section -->
<div class="hero-section text-white py-5 mb-5">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Professional Car Detailing Services</h1>
                <p class="lead mb-4">Give your vehicle the care it deserves with our premium detailing services. From basic washes to complete restoration, we've got you covered.</p>
                <?php if (!$isLoggedIn): ?>
                    <div class="d-flex gap-3">
                        <a href="index.php?page=register" class="btn btn-secondary btn-lg">Get Started</a>
                        <a href="index.php?page=services" class="btn btn-outline-light btn-lg">View Services</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-3">
                        <a href="index.php?page=booking" class="btn btn-secondary btn-lg">Book Now</a>
                        <a href="index.php?page=services" class="btn btn-outline-light btn-lg">View Services</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-car-wash fa-8x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services Overview -->
<div class="container mb-5">
    <div class="row text-center mb-4">
        <div class="col-12">
            <h2 class="display-5 fw-bold">Our Services</h2>
            <p class="lead text-muted">Professional car care services tailored to your needs</p>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-car-wash fa-2x text-primary"></i>
                    </div>
                    <h4 class="card-title">Basic Wash</h4>
                    <p class="card-text text-muted">Exterior wash, interior vacuum, and basic cleaning to keep your car looking fresh.</p>
                    <div class="mt-3">
                        <span class="badge bg-primary fs-6">Starting at <?php echo formatCurrency(2500); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-sparkles fa-2x text-primary"></i>
                    </div>
                    <h4 class="card-title">Premium Detail</h4>
                    <p class="card-text text-muted">Complete interior and exterior detailing with wax for that showroom finish.</p>
                    <div class="mt-3">
                        <span class="badge bg-primary fs-6">Starting at <?php echo formatCurrency(7500); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-crown fa-2x text-primary"></i>
                    </div>
                    <h4 class="card-title">Ultimate Detail</h4>
                    <p class="card-text text-muted">Full service including paint correction and ceramic coating for maximum protection.</p>
                    <div class="mt-3">
                        <span class="badge bg-primary fs-6">Starting at <?php echo formatCurrency(15000); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-light py-5 mb-5">
    <div class="container">
        <div class="row text-center mb-4">
            <div class="col-12">
                <h2 class="display-5 fw-bold">Why Choose Us?</h2>
                <p class="lead text-muted">Experience the difference with our professional approach</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-clock fa-lg text-primary"></i>
                    </div>
                    <h5>Convenient Scheduling</h5>
                    <p class="text-muted">Book appointments online at your convenience</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-home fa-lg text-primary"></i>
                    </div>
                    <h5>Mobile Service</h5>
                    <p class="text-muted">We come to you or visit our facility</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-shield-alt fa-lg text-primary"></i>
                    </div>
                    <h5>Quality Guarantee</h5>
                    <p class="text-muted">Satisfaction guaranteed on all services</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-users fa-lg text-primary"></i>
                    </div>
                    <h5>Expert Team</h5>
                    <p class="text-muted">Trained professionals with years of experience</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="container mb-5">
    <div class="row">
        <div class="col-12">
            <div class="bg-primary text-white rounded p-5 text-center" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%) !important;">
                <h2 class="mb-3">Ready to Give Your Car the Care It Deserves?</h2>
                <p class="lead mb-4">Join thousands of satisfied customers who trust us with their vehicles</p>
                <?php if (!$isLoggedIn): ?>
                    <a href="index.php?page=register" class="btn btn-light btn-lg">Create Account & Book Now</a>
                <?php else: ?>
                    <a href="index.php?page=booking" class="btn btn-light btn-lg">Book Your Appointment</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
