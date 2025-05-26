<?php
$page_title = "Contact Us";
$page_description = "Get in touch with EYGII. Contact us for partnerships, volunteering opportunities, or general inquiries.";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center hero-content">
                <h1 class="display-4 fw-bold mb-4 fade-in">
                    <i class="fas fa-envelope me-3"></i>Contact Us
                </h1>
                <p class="lead mb-4 fade-in">
                    We'd love to hear from you! Get in touch for partnerships, volunteering 
                    opportunities, or any questions about our programs.
                </p>
                <p class="fade-in">
                    Your message is important to us, and we'll get back to you as soon as possible.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-5">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5 class="card-title">Our Location</h5>
                        <p class="card-text">
                            K19, Joke Plaza<br>
                            Bodija, Ibadan<br>
                            Oyo State, Nigeria
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-5">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="card-icon bg-success">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5 class="card-title">Email Us</h5>
                        <p class="card-text">
                            <a href="mailto:eygii2017@gmail.com" class="text-decoration-none">
                                eygii2017@gmail.com
                            </a>
                        </p>
                        <p class="text-muted small">
                            We typically respond within 24 hours
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-5">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="card-icon bg-warning">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h5 class="card-title">WhatsApp</h5>
                        <p class="card-text">
                            <a href="https://wa.me/2348136613616" class="text-decoration-none d-block">
                                +234 813 661 3616
                            </a>
                            <a href="https://wa.me/2348054824514" class="text-decoration-none d-block">
                                +234 805 482 4514
                            </a>
                        </p>
                        <p class="text-muted small">
                            Available during business hours
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="contact-form">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-paper-plane me-2"></i>Send Us a Message
                    </h3>
                    <p class="text-center text-muted mb-4">
                        Fill out the form below and we'll get back to you as soon as possible.
                    </p>
                    
                    <form id="contactForm" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <select class="form-control" id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="volunteer">Volunteering Opportunity</option>
                                    <option value="partnership">Partnership/Collaboration</option>
                                    <option value="donation">Donation Inquiry</option>
                                    <option value="program">Program Information</option>
                                    <option value="media">Media/Press Inquiry</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="organization" class="form-label">Organization (if applicable)</label>
                            <input type="text" class="form-control" id="organization" name="organization" 
                                placeholder="Your organization or company name">
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required
                                placeholder="Please provide details about your inquiry..."></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter" value="1">
                            <label class="form-check-label" for="newsletter">
                                I would like to receive updates about EYGII's programs and activities
                            </label>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office Hours & Additional Info -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-clock me-2 text-primary"></i>Office Hours
                        </h5>
                        <div class="office-hours">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Monday - Friday:</span>
                                <span>9:00 AM - 5:00 PM</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Saturday:</span>
                                <span>10:00 AM - 2:00 PM</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Sunday:</span>
                                <span>Closed</span>
                            </div>
                        </div>
                        <hr>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            For urgent matters outside office hours, please use WhatsApp or email.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-handshake me-2 text-primary"></i>How We Can Help
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Program information and registration
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Volunteering and partnership opportunities
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Donation guidance and support
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Community outreach collaboration
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Media and press inquiries
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                General questions about our mission
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="section-padding bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="mb-3 fade-in">Ready to Get Involved?</h2>
                <p class="lead mb-0 fade-in">
                    Whether you're interested in volunteering, partnerships, or supporting our cause, 
                    we're here to help you find the perfect way to make a difference.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="get-involved.php" class="btn btn-warning btn-lg me-3 fade-in">
                    <i class="fas fa-hands-helping me-2"></i>Get Involved
                </a>
                <a href="donate.php" class="btn btn-outline-light btn-lg fade-in">
                    <i class="fas fa-heart me-2"></i>Donate
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 