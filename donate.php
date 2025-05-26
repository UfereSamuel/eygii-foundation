<?php
$page_title = "Donate";
$page_description = "Support EYGII's mission to empower youth and revive integrity. Your donation makes a real difference in communities.";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center hero-content">
                <h1 class="display-4 fw-bold mb-4 fade-in">
                    <i class="fas fa-heart me-3"></i>Support Our Mission
                </h1>
                <p class="lead mb-4 fade-in">
                    Your generous donation helps us empower more young people and strengthen 
                    integrity in communities across Nigeria and beyond.
                </p>
                <p class="fade-in">
                    Every contribution, no matter the size, makes a meaningful impact in the lives 
                    of young people and their communities.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Donation Information -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Bank Details -->
                <div class="donation-info text-center">
                    <h3 class="mb-4">
                        <i class="fas fa-university me-2"></i>Bank Account Details
                    </h3>
                    
                    <div class="bank-details">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <h6 class="text-warning">Account Number</h6>
                                <p class="h5 mb-0">1024384710</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <h6 class="text-warning">Account Name</h6>
                                <p class="h6 mb-0">ELOQUENT YOUTH AND GLOBAL INTEGRITY</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <h6 class="text-warning">Bank</h6>
                                <p class="h6 mb-0">UNITED BANK FOR AFRICA</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p class="mb-2">
                            <i class="fas fa-info-circle me-2"></i>
                            Please use your name or organization as the transfer reference
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Send proof of payment to: 
                            <a href="mailto:eygii2017@gmail.com" class="text-white">eygii2017@gmail.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Donation Impact -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title fade-in">Your Impact</h2>
                <p class="lead fade-in">
                    See how your donation can make a difference in the lives of young people and communities.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center fade-in">
                    <div class="card-body">
                        <div class="card-icon bg-success">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h5 class="card-title">₦5,000</h5>
                        <p class="card-text">
                            Provides training materials for one youth in our leadership development program.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center fade-in">
                    <div class="card-body">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h5 class="card-title">₦15,000</h5>
                        <p class="card-text">
                            Sponsors a complete skills development workshop for 10 young people.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center fade-in">
                    <div class="card-body">
                        <div class="card-icon bg-info">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h5 class="card-title">₦50,000</h5>
                        <p class="card-text">
                            Funds a complete community outreach program reaching 100+ beneficiaries.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Donation Form -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="contact-form">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-handshake me-2"></i>Donation Inquiry Form
                    </h3>
                    <p class="text-center text-muted mb-4">
                        Fill out this form to let us know about your donation or to request more information.
                    </p>
                    
                    <form id="donationForm" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="donor_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="donor_name" name="donor_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="donor_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="donor_email" name="donor_email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="donor_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="donor_phone" name="donor_phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="donation_amount" class="form-label">Donation Amount (₦)</label>
                                <input type="number" class="form-control" id="donation_amount" name="donation_amount" min="1">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="donation_purpose" class="form-label">Donation Purpose</label>
                            <select class="form-control" id="donation_purpose" name="donation_purpose">
                                <option value="">Select a purpose (optional)</option>
                                <option value="general">General Support</option>
                                <option value="leadership">Leadership Training Programs</option>
                                <option value="community">Community Service Projects</option>
                                <option value="skills">Skills Development Workshops</option>
                                <option value="education">Educational Support</option>
                                <option value="other">Other (specify in message)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="donor_message" class="form-label">Message</label>
                            <textarea class="form-control" id="donor_message" name="donor_message" rows="4" 
                                placeholder="Tell us about your donation, ask questions, or share any special instructions..."></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="donor_updates" name="donor_updates" value="1">
                            <label class="form-check-label" for="donor_updates">
                                I would like to receive updates about EYGII's programs and impact
                            </label>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Submit Inquiry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Other Ways to Help -->
<section class="section-padding bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4 fade-in">Other Ways to Support Us</h2>
                <p class="lead fade-in">
                    Can't donate right now? There are many other ways you can support our mission.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-4 text-center mb-4">
                <div class="fade-in">
                    <i class="fas fa-share-alt fa-3x mb-3 text-warning"></i>
                    <h5>Spread the Word</h5>
                    <p>Share our mission with friends, family, and on social media to help us reach more people.</p>
                </div>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="fade-in">
                    <i class="fas fa-hands-helping fa-3x mb-3 text-warning"></i>
                    <h5>Volunteer</h5>
                    <p>Join our team of volunteers and contribute your time and skills to our programs.</p>
                    <a href="get-involved.php" class="btn btn-outline-light mt-2">Learn More</a>
                </div>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="fade-in">
                    <i class="fas fa-handshake fa-3x mb-3 text-warning"></i>
                    <h5>Partner with Us</h5>
                    <p>Explore partnership opportunities for organizations and businesses.</p>
                    <a href="contact.php" class="btn btn-outline-light mt-2">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 