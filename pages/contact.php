<?php
// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!is_valid_email($email)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, send message (in a real system, you'd send an email)
    if (empty($errors)) {
        // Log the contact message (in production, send email)
        error_log("Contact form submission from: $name ($email) - Subject: $subject - Message: $message");
        
        set_flash_message('success', 'Thank you for your message! We will get back to you within 24 hours.');
        header('Location: index.php?page=contact');
        exit();
    }
}

display_flash_message();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Us</h2>
        <p class="text-muted">Get in touch with us for any questions or to schedule your appointment</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send us a Message</h4>
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="Service Question" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Service Question') ? 'selected' : ''; ?>>Service Question</option>
                                    <option value="Booking Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Booking Inquiry') ? 'selected' : ''; ?>>Booking Inquiry</option>
                                    <option value="Pricing Question" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Pricing Question') ? 'selected' : ''; ?>>Pricing Question</option>
                                    <option value="Complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Complaint') ? 'selected' : ''; ?>>Complaint</option>
                                    <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                    <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Please provide details about your inquiry..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Contact Information -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6><i class="fas fa-map-marker-alt text-danger me-2"></i>Address</h6>
                    <p class="text-muted mb-0">123 Car Care Drive<br>Automotive District<br>City, State 12345</p>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-phone text-success me-2"></i>Phone</h6>
                    <p class="text-muted mb-0">
                        <a href="tel:+1234567890" class="text-decoration-none">(123) 456-7890</a><br>
                        <small>Mon-Fri: 8AM-6PM</small>
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-envelope text-primary me-2"></i>Email</h6>
                    <p class="text-muted mb-0">
                        <a href="mailto:info@cardetailingpro.com" class="text-decoration-none">info@cardetailingpro.com</a><br>
                        <small>We respond within 24 hours</small>
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6><i class="fas fa-clock text-warning me-2"></i>Business Hours</h6>
                    <p class="text-muted mb-0">
                        Monday - Friday: 8:00 AM - 6:00 PM<br>
                        Saturday: 9:00 AM - 5:00 PM<br>
                        Sunday: 10:00 AM - 4:00 PM
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Quick Contact -->
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Contact</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="tel:+1234567890" class="btn btn-outline-success">
                        <i class="fas fa-phone me-2"></i>Call Now
                    </a>
                    <a href="mailto:info@cardetailingpro.com" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Email Us
                    </a>
                    <?php if (is_logged_in()): ?>
                        <a href="index.php?page=booking" class="btn btn-outline-warning">
                            <i class="fas fa-calendar me-2"></i>Book Online
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=register" class="btn btn-outline-warning">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="row mt-5">
    <div class="col-12">
        <h3 class="mb-4">Frequently Asked Questions</h3>
    </div>
    
    <div class="col-lg-6">
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                        How long does a typical detailing service take?
                    </button>
                </h2>
                <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Service duration varies by package. Basic wash takes about 1 hour, Premium detail takes 2-3 hours, and Ultimate detail can take 4-6 hours depending on the vehicle size and condition.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                        Do you offer mobile detailing services?
                    </button>
                </h2>
                <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes! We offer both mobile and facility services. Our mobile service brings professional detailing right to your doorstep with all the same quality and equipment.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                        What payment methods do you accept?
                    </button>
                </h2>
                <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        We accept cash, credit cards, debit cards, and digital payments. Payment is due upon completion of service.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="accordion" id="faqAccordion2">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                        Do you provide a warranty on your services?
                    </button>
                </h2>
                <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                    <div class="accordion-body">
                        Yes, we offer a satisfaction guarantee on all our services. If you're not completely satisfied, we'll make it right at no additional cost.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq5">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                        Can I cancel or reschedule my appointment?
                    </button>
                </h2>
                <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                    <div class="accordion-body">
                        Yes, you can cancel or reschedule your appointment up to 24 hours before the scheduled time without any fees. Late cancellations may incur a small fee.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq6">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6">
                        What should I do to prepare my car for detailing?
                    </button>
                </h2>
                <div id="collapse6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                    <div class="accordion-body">
                        Remove personal items from your vehicle and ensure it's accessible. For mobile service, please provide a water source if possible. No other preparation is needed!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Section -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-map me-2"></i>Find Us</h5>
            </div>
            <div class="card-body p-0">
                <div class="bg-light p-4 text-center">
                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                    <h5>Our Location</h5>
                    <p class="text-muted">123 Car Care Drive, Automotive District<br>City, State 12345</p>
                    <a href="https://maps.google.com" target="_blank" class="btn btn-primary">
                        <i class="fas fa-directions me-2"></i>Get Directions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
