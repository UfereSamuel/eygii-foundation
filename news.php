<?php
$page_title = "News & Updates";
$page_description = "Stay updated with the latest news, events, and stories from EYGII. Read about our impact, upcoming programs, and community initiatives.";
include 'includes/header.php';
require_once 'config/database.php';

// Get news from database
try {
    $db = Database::getInstance();
    $news = $db->fetchAll("SELECT * FROM news WHERE status = 'published' ORDER BY published_at DESC LIMIT 12");
} catch (Exception $e) {
    $news = [];
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center hero-content">
                <h1 class="display-4 fw-bold mb-4 fade-in">
                    <i class="fas fa-newspaper me-3"></i>News & Updates
                </h1>
                <p class="lead mb-4 fade-in">
                    Stay informed about our latest initiatives, success stories, and upcoming 
                    events as we work together to revive integrity and moral values.
                </p>
                <p class="fade-in">
                    Discover how EYGII is making a difference in communities across Nigeria 
                    and how you can be part of our mission.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured News -->
<?php if (!empty($news)): ?>
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title fade-in">Featured Stories</h2>
                <p class="lead fade-in">
                    Highlighting our most impactful stories and recent developments.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <!-- Featured Article -->
            <div class="col-lg-8 mb-4">
                <div class="card news-card h-100 fade-in">
                    <img src="assets/images/banners/news-featured.jpg" class="card-img-top" alt="Featured News" 
                         style="height: 300px; object-fit: cover;" 
                         onerror="this.src='https://via.placeholder.com/800x300/1e3a8a/ffffff?text=EYGII+Featured+News'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary">Featured</span>
                            <small class="text-muted"><?php echo date('M j, Y', strtotime($news[0]['published_at'] ?? 'now')); ?></small>
                        </div>
                        <h3 class="card-title"><?php echo htmlspecialchars($news[0]['title'] ?? 'EYGII Launches New Youth Leadership Initiative'); ?></h3>
                        <p class="card-text">
                            <?php echo htmlspecialchars(substr($news[0]['content'] ?? 'EYGII is proud to announce the launch of our comprehensive youth leadership development program, designed to empower young people with the skills and knowledge needed to become effective leaders in their communities. This initiative represents our continued commitment to fostering integrity and moral values among the next generation.', 0, 200)); ?>...
                        </p>
                        <a href="#" class="btn btn-primary">Read More</a>
                    </div>
                </div>
            </div>
            
            <!-- Recent News Sidebar -->
            <div class="col-lg-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Updates</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php for ($i = 1; $i < min(4, count($news)); $i++): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars(substr($news[$i]['title'], 0, 50)); ?>...</h6>
                                        <small><?php echo date('M j', strtotime($news[$i]['published_at'])); ?></small>
                                    </div>
                                    <p class="mb-1 small"><?php echo htmlspecialchars(substr($news[$i]['content'], 0, 80)); ?>...</p>
                                </div>
                            <?php endfor; ?>
                            
                            <!-- Default items if not enough news -->
                            <?php if (count($news) < 4): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Community Outreach Success</h6>
                                        <small>Dec 15</small>
                                    </div>
                                    <p class="mb-1 small">Our recent community service initiative reached over 500 families...</p>
                                </div>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Skills Training Workshop</h6>
                                        <small>Dec 10</small>
                                    </div>
                                    <p class="mb-1 small">50 young people completed our digital literacy program...</p>
                                </div>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Partnership Announcement</h6>
                                        <small>Dec 5</small>
                                    </div>
                                    <p class="mb-1 small">EYGII partners with local organizations to expand reach...</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- News Grid -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title fade-in">Latest News</h2>
                <p class="lead fade-in">
                    Explore all our recent news, updates, and success stories.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (empty($news)): ?>
                <!-- Default news items if database is empty -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card news-card h-100 fade-in">
                        <img src="https://via.placeholder.com/400x250/1e3a8a/ffffff?text=Leadership+Training" 
                             class="card-img-top" alt="Leadership Training" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">Programs</span>
                                <small class="text-muted">Dec 20, 2024</small>
                            </div>
                            <h5 class="card-title">Leadership Training Program Graduates 30 Young Leaders</h5>
                            <p class="card-text">
                                Our 12-week leadership development program successfully graduated 30 young 
                                people who are now equipped with essential leadership skills and ready to 
                                make positive changes in their communities.
                            </p>
                            <a href="#" class="btn btn-outline-primary btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card news-card h-100 fade-in">
                        <img src="https://via.placeholder.com/400x250/059669/ffffff?text=Community+Service" 
                             class="card-img-top" alt="Community Service" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-success">Community</span>
                                <small class="text-muted">Dec 18, 2024</small>
                            </div>
                            <h5 class="card-title">Monthly Community Outreach Reaches 500 Families</h5>
                            <p class="card-text">
                                Our volunteers distributed food packages, educational materials, and 
                                provided health awareness sessions to over 500 families in underserved 
                                communities across Ibadan.
                            </p>
                            <a href="#" class="btn btn-outline-success btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card news-card h-100 fade-in">
                        <img src="https://via.placeholder.com/400x250/f59e0b/ffffff?text=Skills+Workshop" 
                             class="card-img-top" alt="Skills Workshop" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-warning">Training</span>
                                <small class="text-muted">Dec 15, 2024</small>
                            </div>
                            <h5 class="card-title">Digital Literacy Workshop Empowers 50 Youth</h5>
                            <p class="card-text">
                                Young people gained valuable digital skills including computer basics, 
                                internet safety, and online entrepreneurship during our intensive 
                                two-week digital literacy program.
                            </p>
                            <a href="#" class="btn btn-outline-warning btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card news-card h-100 fade-in">
                        <img src="https://via.placeholder.com/400x250/8b5a2b/ffffff?text=Partnership" 
                             class="card-img-top" alt="Partnership" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-info">Partnership</span>
                                <small class="text-muted">Dec 12, 2024</small>
                            </div>
                            <h5 class="card-title">New Partnership with Local Universities</h5>
                            <p class="card-text">
                                EYGII announces strategic partnerships with three local universities 
                                to expand our mentorship programs and provide more opportunities for 
                                youth development and career guidance.
                            </p>
                            <a href="#" class="btn btn-outline-info btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card news-card h-100 fade-in">
                        <img src="https://via.placeholder.com/400x250/dc2626/ffffff?text=Award" 
                             class="card-img-top" alt="Award" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-danger">Achievement</span>
                                <small class="text-muted">Dec 10, 2024</small>
                            </div>
                            <h5 class="card-title">EYGII Receives Youth Development Excellence Award</h5>
                            <p class="card-text">
                                Our organization was honored with the Youth Development Excellence Award 
                                by the Oyo State Government for our outstanding contributions to youth 
                                empowerment and community development.
                            </p>
                            <a href="#" class="btn btn-outline-danger btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card news-card h-100 fade-in">
                        <img src="https://via.placeholder.com/400x250/6366f1/ffffff?text=Fundraiser" 
                             class="card-img-top" alt="Fundraiser" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary">Fundraising</span>
                                <small class="text-muted">Dec 8, 2024</small>
                            </div>
                            <h5 class="card-title">Annual Fundraising Gala Raises ₦2.5 Million</h5>
                            <p class="card-text">
                                Our annual fundraising gala was a tremendous success, raising ₦2.5 million 
                                to support our programs for the coming year. Thank you to all our donors 
                                and supporters who made this possible.
                            </p>
                            <a href="#" class="btn btn-outline-secondary btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Display news from database -->
                <?php foreach (array_slice($news, 1) as $article): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card news-card h-100 fade-in">
                            <img src="<?php echo $article['featured_image'] ?: 'https://via.placeholder.com/400x250/1e3a8a/ffffff?text=EYGII+News'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary"><?php echo ucfirst($article['category'] ?? 'News'); ?></span>
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($article['published_at'])); ?></small>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($article['content'], 0, 120)); ?>...
                                </p>
                                <a href="#" class="btn btn-outline-primary btn-sm">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Load More Button -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button class="btn btn-primary btn-lg fade-in" id="loadMoreNews">
                    <i class="fas fa-plus me-2"></i>Load More News
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Signup -->
<section class="section-padding bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="mb-3 fade-in">Stay Updated</h2>
                <p class="lead mb-4 fade-in">
                    Subscribe to our newsletter and never miss important updates about our programs, 
                    events, and community impact stories.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 fade-in">
                    <div class="card-body">
                        <form id="newsletterForm" class="row g-3">
                            <div class="col-md-8">
                                <input type="email" class="form-control" placeholder="Enter your email address" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-paper-plane me-1"></i>Subscribe
                                </button>
                            </div>
                        </form>
                        <small class="text-muted">
                            We respect your privacy. Unsubscribe at any time.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Feed -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title fade-in">Follow Us on Social Media</h2>
                <p class="lead fade-in">
                    Connect with us on social media for daily updates, behind-the-scenes content, 
                    and community highlights.
                </p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="text-center fade-in">
                    <div class="card-icon bg-primary mx-auto mb-3">
                        <i class="fab fa-facebook-f"></i>
                    </div>
                    <h5>Facebook</h5>
                    <p class="text-muted">Follow our page for event updates and community stories.</p>
                    <a href="#" class="btn btn-outline-primary">Follow Us</a>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center fade-in">
                    <div class="card-icon bg-info mx-auto mb-3">
                        <i class="fab fa-twitter"></i>
                    </div>
                    <h5>Twitter</h5>
                    <p class="text-muted">Get real-time updates and join the conversation.</p>
                    <a href="#" class="btn btn-outline-info">Follow Us</a>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center fade-in">
                    <div class="card-icon bg-danger mx-auto mb-3">
                        <i class="fab fa-instagram"></i>
                    </div>
                    <h5>Instagram</h5>
                    <p class="text-muted">See photos and videos from our programs and events.</p>
                    <a href="#" class="btn btn-outline-danger">Follow Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Newsletter form submission
    document.getElementById('newsletterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        // Here you would typically send the email to your backend
        alert('Thank you for subscribing! You will receive our latest updates.');
        this.reset();
    });
    
    // Load more news functionality
    document.getElementById('loadMoreNews').addEventListener('click', function() {
        // Here you would typically load more news via AJAX
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-check me-2"></i>All news loaded';
            this.disabled = true;
        }, 2000);
    });
});
</script>

<?php include 'includes/footer.php'; ?> 