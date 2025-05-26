<?php
$page_title = "Our Programs";
$page_description = "Discover EYGII's comprehensive programs designed to empower youth and strengthen communities through leadership training, skills development, and community service.";
include 'includes/header.php';
require_once 'config/database.php';

// Get programs from database
try {
    $db = Database::getInstance();
    $programs = $db->fetchAll("SELECT * FROM programs WHERE status = 'active' ORDER BY created_at DESC");
} catch (Exception $e) {
    $programs = [];
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center hero-content">
                <h1 class="display-4 fw-bold mb-4 fade-in">
                    <i class="fas fa-project-diagram me-3"></i>Our Programs
                </h1>
                <p class="lead mb-4 fade-in">
                    Comprehensive initiatives designed to empower young people and strengthen 
                    communities through leadership development, skills training, and service.
                </p>
                <p class="fade-in">
                    Join our transformative programs and become part of a movement that's 
                    reviving integrity and moral values in communities across Nigeria.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Program Categories -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title fade-in">Program Categories</h2>
                <p class="lead fade-in">
                    Our programs are designed to address different aspects of youth development 
                    and community engagement.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-4 mb-4">
                <div class="card h-100 text-center fade-in">
                    <div class="card-body">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h5 class="card-title">Leadership Development</h5>
                        <p class="card-text">
                            Comprehensive training programs that equip young people with essential 
                            leadership skills, communication abilities, and ethical decision-making capabilities.
                        </p>
                        <a href="#leadership" class="btn btn-outline-primary">View Programs</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card h-100 text-center fade-in">
                    <div class="card-body">
                        <div class="card-icon bg-success">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h5 class="card-title">Community Service</h5>
                        <p class="card-text">
                            Organized outreach programs that address local community needs while 
                            fostering a spirit of service and social responsibility among participants.
                        </p>
                        <a href="#community" class="btn btn-outline-success">View Programs</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card h-100 text-center fade-in">
                    <div class="card-body">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h5 class="card-title">Skills Development</h5>
                        <p class="card-text">
                            Practical training workshops in entrepreneurship, digital literacy, 
                            vocational skills, and other areas that enhance employability and economic empowerment.
                        </p>
                        <a href="#skills" class="btn btn-outline-warning">View Programs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Active Programs -->
<section id="programs" class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title fade-in">Active Programs</h2>
                <p class="lead fade-in">
                    Join our current programs and start your journey of personal growth and community impact.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (empty($programs)): ?>
                <!-- Default programs if database is empty -->
                <div class="col-lg-6 mb-4">
                    <div class="card program-card h-100 fade-in">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary">Leadership</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <h4 class="card-title">Leadership Training Program</h4>
                            <p class="card-text">
                                A comprehensive 12-week program designed to develop essential leadership 
                                skills in young people aged 16-30. Participants learn communication, 
                                team building, project management, and ethical decision-making.
                            </p>
                            <div class="program-details mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>Duration: 12 weeks
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>Max: 30 participants
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <a href="contact.php?program=leadership" class="btn btn-primary">Apply Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card program-card h-100 fade-in">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-success">Community</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <h4 class="card-title">Community Service Initiative</h4>
                            <p class="card-text">
                                Monthly community outreach programs addressing local needs through 
                                organized service projects. Volunteers work together to create 
                                tangible positive impact in communities.
                            </p>
                            <div class="program-details mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>Monthly events
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>Various locations
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <a href="contact.php?program=community" class="btn btn-success">Join Us</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card program-card h-100 fade-in">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-warning">Skills</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <h4 class="card-title">Skills Development Workshops</h4>
                            <p class="card-text">
                                Practical training workshops in entrepreneurship, digital marketing, 
                                computer literacy, financial management, and various vocational skills 
                                to enhance employability.
                            </p>
                            <div class="program-details mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>Bi-weekly
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-certificate me-1"></i>Certificate provided
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <a href="contact.php?program=skills" class="btn btn-warning">Register</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card program-card h-100 fade-in">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-info">Mentorship</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <h4 class="card-title">Youth Mentorship Program</h4>
                            <p class="card-text">
                                One-on-one mentorship program connecting young people with experienced 
                                professionals and community leaders for guidance, support, and career development.
                            </p>
                            <div class="program-details mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>6 months
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-user-tie me-1"></i>1-on-1 mentoring
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <a href="contact.php?program=mentorship" class="btn btn-info">Apply</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Display programs from database -->
                <?php foreach ($programs as $program): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card program-card h-100 fade-in">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary"><?php echo ucfirst($program['slug']); ?></span>
                                    <span class="badge bg-<?php echo $program['status'] == 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($program['status']); ?>
                                    </span>
                                </div>
                                <h4 class="card-title"><?php echo htmlspecialchars($program['title']); ?></h4>
                                <p class="card-text"><?php echo htmlspecialchars($program['description']); ?></p>
                                
                                <?php if ($program['start_date'] || $program['location'] || $program['max_participants']): ?>
                                    <div class="program-details mb-3">
                                        <div class="row">
                                            <?php if ($program['start_date']): ?>
                                                <div class="col-6">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($program['max_participants']): ?>
                                                <div class="col-6">
                                                    <small class="text-muted">
                                                        <i class="fas fa-users me-1"></i>
                                                        Max: <?php echo $program['max_participants']; ?> participants
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($program['location']): ?>
                                                <div class="col-12 mt-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php echo htmlspecialchars($program['location']); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="contact.php?program=<?php echo urlencode($program['slug']); ?>" class="btn btn-primary">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Program Benefits -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title fade-in">Program Benefits</h2>
                <p class="lead fade-in">
                    Participants in our programs gain valuable skills, experiences, and connections 
                    that last a lifetime.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="text-center fade-in">
                    <div class="card-icon bg-primary mx-auto mb-3">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h5>Certification</h5>
                    <p class="text-muted">
                        Receive certificates of completion for all programs, 
                        recognized by employers and educational institutions.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center fade-in">
                    <div class="card-icon bg-success mx-auto mb-3">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <h5>Networking</h5>
                    <p class="text-muted">
                        Connect with like-minded peers, mentors, and professionals 
                        who share your commitment to positive change.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center fade-in">
                    <div class="card-icon bg-warning mx-auto mb-3">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h5>Career Growth</h5>
                    <p class="text-muted">
                        Develop skills and experiences that enhance your career 
                        prospects and leadership potential.
                    </p>
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
                <h2 class="mb-3 fade-in">Ready to Transform Your Future?</h2>
                <p class="lead mb-0 fade-in">
                    Join thousands of young people who have already benefited from our programs. 
                    Take the first step towards becoming a leader in your community.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="get-involved.php" class="btn btn-warning btn-lg me-3 fade-in">
                    <i class="fas fa-user-plus me-2"></i>Join a Program
                </a>
                <a href="contact.php" class="btn btn-outline-light btn-lg fade-in">
                    <i class="fas fa-question-circle me-2"></i>Ask Questions
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 