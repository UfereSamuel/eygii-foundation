<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance();

// Get dashboard statistics
try {
    $stats = [
        'contacts' => $db->fetch("SELECT COUNT(*) as count FROM contact_submissions WHERE DATE(submitted_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'],
        'donations' => $db->fetch("SELECT COUNT(*) as count FROM donation_inquiries WHERE DATE(submitted_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'],
        'total_contacts' => $db->fetch("SELECT COUNT(*) as count FROM contact_submissions")['count'],
        'total_donations' => $db->fetch("SELECT COUNT(*) as count FROM donation_inquiries")['count'],
        'volunteers' => $db->fetch("SELECT COUNT(*) as count FROM volunteers WHERE status = 'active'")['count'],
        'programs' => $db->fetch("SELECT COUNT(*) as count FROM programs WHERE status = 'active'")['count']
    ];
    
    // Recent activities
    $recent_contacts = $db->fetchAll("SELECT name, email, subject, submitted_at FROM contact_submissions ORDER BY submitted_at DESC LIMIT 5");
    $recent_donations = $db->fetchAll("SELECT donor_name, donor_email, donation_amount, submitted_at FROM donation_inquiries ORDER BY submitted_at DESC LIMIT 5");
    
} catch (Exception $e) {
    $stats = ['contacts' => 0, 'donations' => 0, 'total_contacts' => 0, 'total_donations' => 0, 'volunteers' => 0, 'programs' => 0];
    $recent_contacts = [];
    $recent_donations = [];
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Quick Add
                    </button>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle fa-2x me-3 text-primary"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_username']); ?>!</h5>
                        <p class="mb-0">Here's what's happening with EYGII today.</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle p-3">
                                        <i class="fas fa-envelope fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">New Contacts (30 days)</div>
                                    <div class="h4 mb-0"><?php echo number_format($stats['contacts']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success text-white rounded-circle p-3">
                                        <i class="fas fa-heart fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Donation Inquiries (30 days)</div>
                                    <div class="h4 mb-0"><?php echo number_format($stats['donations']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning text-white rounded-circle p-3">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Active Volunteers</div>
                                    <div class="h4 mb-0"><?php echo number_format($stats['volunteers']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info text-white rounded-circle p-3">
                                        <i class="fas fa-project-diagram fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Active Programs</div>
                                    <div class="h4 mb-0"><?php echo number_format($stats['programs']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="pages/programs.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                        <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                        <span>Add Program</span>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="pages/news.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                        <i class="fas fa-newspaper fa-2x mb-2"></i>
                                        <span>Add News</span>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="pages/contacts.php" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                        <i class="fas fa-envelope fa-2x mb-2"></i>
                                        <span>View Messages</span>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="pages/settings.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                        <i class="fas fa-cog fa-2x mb-2"></i>
                                        <span>Settings</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-envelope me-2"></i>Recent Contact Messages
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_contacts)): ?>
                                <p class="text-muted text-center py-3">No recent contact messages</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_contacts as $contact): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($contact['name']); ?></h6>
                                                <small><?php echo date('M j, Y', strtotime($contact['submitted_at'])); ?></small>
                                            </div>
                                            <p class="mb-1 small"><?php echo htmlspecialchars($contact['subject']); ?></p>
                                            <small class="text-muted"><?php echo htmlspecialchars($contact['email']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="pages/contacts.php" class="btn btn-sm btn-outline-primary">View All Messages</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-heart me-2"></i>Recent Donation Inquiries
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_donations)): ?>
                                <p class="text-muted text-center py-3">No recent donation inquiries</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_donations as $donation): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($donation['donor_name']); ?></h6>
                                                <small><?php echo date('M j, Y', strtotime($donation['submitted_at'])); ?></small>
                                            </div>
                                            <p class="mb-1 small">
                                                <?php if ($donation['donation_amount']): ?>
                                                    Amount: ₦<?php echo number_format($donation['donation_amount']); ?>
                                                <?php else: ?>
                                                    General inquiry
                                                <?php endif; ?>
                                            </p>
                                            <small class="text-muted"><?php echo htmlspecialchars($donation['donor_email']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="pages/donations.php" class="btn btn-sm btn-outline-success">View All Inquiries</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-server me-2"></i>System Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-database text-success me-2"></i>
                                        <span>Database Connection</span>
                                        <span class="badge bg-success ms-auto">Online</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-envelope text-warning me-2"></i>
                                        <span>Email Service</span>
                                        <span class="badge bg-warning ms-auto">Configured</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-shield-alt text-info me-2"></i>
                                        <span>Security</span>
                                        <span class="badge bg-info ms-auto">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 