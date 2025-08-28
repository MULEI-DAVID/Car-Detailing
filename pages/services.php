<?php
$packages = get_services('package');
$addons = get_services('addon');
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="fas fa-cogs me-2"></i>Our Services</h2>
        <p class="text-muted">Professional car detailing services tailored to your needs</p>
    </div>
</div>

<!-- Service Packages -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="mb-4">Service Packages</h3>
        <p class="text-muted mb-4">Choose from our comprehensive service packages designed to meet different levels of detailing needs.</p>
    </div>
    
    <?php foreach ($packages as $package): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card service-card h-100 shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <?php if (strpos(strtolower($package['name']), 'basic') !== false): ?>
                                <i class="fas fa-car-wash fa-2x text-primary"></i>
                            <?php elseif (strpos(strtolower($package['name']), 'premium') !== false): ?>
                                <i class="fas fa-sparkles fa-2x text-primary"></i>
                            <?php elseif (strpos(strtolower($package['name']), 'ultimate') !== false): ?>
                                <i class="fas fa-crown fa-2x text-primary"></i>
                            <?php else: ?>
                                <i class="fas fa-car fa-2x text-primary"></i>
                            <?php endif; ?>
                        </div>
                        <h4 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h4>
                    </div>
                    
                    <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($package['description']); ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h4 text-primary mb-0"><?php echo format_currency($package['price']); ?></span>
                        <span class="badge bg-info"><?php echo $package['duration']; ?> min</span>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">What's Included:</h6>
                        <ul class="list-unstyled">
                            <?php if (strpos(strtolower($package['name']), 'basic') !== false): ?>
                                <li><i class="fas fa-check text-success me-2"></i>Exterior wash</li>
                                <li><i class="fas fa-check text-success me-2"></i>Interior vacuum</li>
                                <li><i class="fas fa-check text-success me-2"></i>Window cleaning</li>
                                <li><i class="fas fa-check text-success me-2"></i>Tire dressing</li>
                            <?php elseif (strpos(strtolower($package['name']), 'premium') !== false): ?>
                                <li><i class="fas fa-check text-success me-2"></i>Everything in Basic</li>
                                <li><i class="fas fa-check text-success me-2"></i>Clay bar treatment</li>
                                <li><i class="fas fa-check text-success me-2"></i>Wax application</li>
                                <li><i class="fas fa-check text-success me-2"></i>Interior deep cleaning</li>
                                <li><i class="fas fa-check text-success me-2"></i>Leather conditioning</li>
                            <?php elseif (strpos(strtolower($package['name']), 'ultimate') !== false): ?>
                                <li><i class="fas fa-check text-success me-2"></i>Everything in Premium</li>
                                <li><i class="fas fa-check text-success me-2"></i>Paint correction</li>
                                <li><i class="fas fa-check text-success me-2"></i>Ceramic coating</li>
                                <li><i class="fas fa-check text-success me-2"></i>Engine bay cleaning</li>
                                <li><i class="fas fa-check text-success me-2"></i>Headlight restoration</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="index.php?page=booking" class="btn btn-primary w-100">Book This Service</a>
                    <?php else: ?>
                        <a href="index.php?page=register" class="btn btn-primary w-100">Sign Up to Book</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Additional Services -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="mb-4">Additional Services</h3>
        <p class="text-muted mb-4">Enhance your detailing experience with these specialized add-on services.</p>
    </div>
    
    <?php foreach ($addons as $addon): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card service-card h-100 shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <?php if (strpos(strtolower($addon['name']), 'interior') !== false): ?>
                                <i class="fas fa-couch fa-lg text-success"></i>
                            <?php elseif (strpos(strtolower($addon['name']), 'paint') !== false): ?>
                                <i class="fas fa-shield-alt fa-lg text-success"></i>
                            <?php elseif (strpos(strtolower($addon['name']), 'engine') !== false): ?>
                                <i class="fas fa-cog fa-lg text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-plus fa-lg text-success"></i>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars($addon['name']); ?></h5>
                    </div>
                    
                    <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($addon['description']); ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h5 text-success mb-0"><?php echo format_currency($addon['price']); ?></span>
                        <span class="badge bg-success"><?php echo $addon['duration']; ?> min</span>
                    </div>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="index.php?page=booking" class="btn btn-outline-success w-100">Add to Booking</a>
                    <?php else: ?>
                        <a href="index.php?page=register" class="btn btn-outline-success w-100">Sign Up to Book</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Service Information -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Service Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Mobile Service</h5>
                        <p class="text-muted">We come to you! Our mobile detailing service brings professional car care right to your doorstep.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Convenient location</li>
                            <li><i class="fas fa-check text-success me-2"></i>Same professional quality</li>
                            <li><i class="fas fa-check text-success me-2"></i>Water and power provided</li>
                            <li><i class="fas fa-check text-success me-2"></i>Flexible scheduling</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Facility Service</h5>
                        <p class="text-muted">Visit our professional facility for the ultimate detailing experience with specialized equipment.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Professional equipment</li>
                            <li><i class="fas fa-check text-success me-2"></i>Climate controlled environment</li>
                            <li><i class="fas fa-check text-success me-2"></i>Multiple service bays</li>
                            <li><i class="fas fa-check text-success me-2"></i>Waiting area available</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Why Choose Us?</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6><i class="fas fa-medal text-warning me-2"></i>Professional Quality</h6>
                    <p class="text-muted small">Trained technicians with years of experience</p>
                </div>
                <div class="mb-3">
                    <h6><i class="fas fa-clock text-info me-2"></i>Convenient Scheduling</h6>
                    <p class="text-muted small">Book online at your convenience</p>
                </div>
                <div class="mb-3">
                    <h6><i class="fas fa-shield-alt text-success me-2"></i>Quality Guarantee</h6>
                    <p class="text-muted small">Satisfaction guaranteed on all services</p>
                </div>
                <div class="mb-3">
                    <h6><i class="fas fa-tools text-primary me-2"></i>Premium Products</h6>
                    <p class="text-muted small">Using only the best detailing products</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card bg-primary text-white text-center">
            <div class="card-body py-5">
                <h3 class="mb-3">Ready to Give Your Car the Care It Deserves?</h3>
                <p class="lead mb-4">Book your appointment today and experience the difference professional detailing makes.</p>
                <?php if (is_logged_in()): ?>
                    <a href="index.php?page=booking" class="btn btn-light btn-lg">Book Your Appointment</a>
                <?php else: ?>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="index.php?page=register" class="btn btn-light btn-lg">Create Account</a>
                        <a href="index.php?page=login" class="btn btn-outline-light btn-lg">Sign In</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

