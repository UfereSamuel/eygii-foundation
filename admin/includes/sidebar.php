<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine if we're in the admin root or pages subdirectory
$is_in_pages = ($current_dir === 'pages');
$base_path = $is_in_pages ? '../' : '';
$pages_path = $is_in_pages ? '' : 'pages/';
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Content Management</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'programs.php') ? 'active' : ''; ?>" href="<?php echo $pages_path; ?>programs.php">
                    <i class="fas fa-project-diagram"></i>
                    Programs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'news.php') ? 'active' : ''; ?>" href="<?php echo $pages_path; ?>news.php">
                    <i class="fas fa-newspaper"></i>
                    News & Blog
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Community</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'contacts.php') ? 'active' : ''; ?>" href="<?php echo $pages_path; ?>contacts.php">
                    <i class="fas fa-envelope"></i>
                    Contact Messages
                    <?php
                    try {
                        $db = Database::getInstance();
                        $unread_count = $db->fetch("SELECT COUNT(*) as count FROM contact_submissions WHERE status = 'new'")['count'];
                        if ($unread_count > 0) {
                            echo '<span class="badge bg-danger ms-2">' . $unread_count . '</span>';
                        }
                    } catch (Exception $e) {
                        // Ignore error
                    }
                    ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'volunteers.php') ? 'active' : ''; ?>" href="<?php echo $pages_path; ?>volunteers.php">
                    <i class="fas fa-users"></i>
                    Volunteers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'newsletter.php') ? 'active' : ''; ?>" href="<?php echo $pages_path; ?>newsletter.php">
                    <i class="fas fa-mail-bulk"></i>
                    Newsletter
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>System</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="<?php echo $pages_path; ?>settings.php">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>
        </ul>

        <hr class="my-3">
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-muted" href="<?php echo $base_path; ?>../index.php" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    View Website
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?php echo $base_path; ?>logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav> 