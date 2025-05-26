<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Settings";
$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_settings') {
        $settings = [
            'site_name' => trim($_POST['site_name'] ?? ''),
            'site_description' => trim($_POST['site_description'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
            'contact_whatsapp' => trim($_POST['contact_whatsapp'] ?? ''),
            'contact_address' => trim($_POST['contact_address'] ?? ''),
            'bank_name' => trim($_POST['bank_name'] ?? ''),
            'bank_account_name' => trim($_POST['bank_account_name'] ?? ''),
            'bank_account_number' => trim($_POST['bank_account_number'] ?? ''),
            'social_facebook' => trim($_POST['social_facebook'] ?? ''),
            'social_twitter' => trim($_POST['social_twitter'] ?? ''),
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_linkedin' => trim($_POST['social_linkedin'] ?? ''),
            'social_youtube' => trim($_POST['social_youtube'] ?? ''),
            'footer_text' => trim($_POST['footer_text'] ?? ''),
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
            'allow_registrations' => isset($_POST['allow_registrations']) ? 1 : 0,
            'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0
        ];
        
        try {
            foreach ($settings as $key => $value) {
                // Check if setting exists
                $existing = $db->fetch("SELECT id FROM site_settings WHERE setting_key = ?", [$key]);
                
                if ($existing) {
                    // Update existing setting
                    $stmt = $db->getConnection()->prepare("UPDATE site_settings SET setting_value = ?, updated_at = ? WHERE setting_key = ?");
                    $stmt->execute([$value, date('Y-m-d H:i:s'), $key]);
                } else {
                    // Insert new setting
                    $stmt = $db->getConnection()->prepare("INSERT INTO site_settings (setting_key, setting_value, created_at) VALUES (?, ?, ?)");
                    $stmt->execute([$key, $value, date('Y-m-d H:i:s')]);
                }
            }
            
            $success_message = "Settings updated successfully.";
        } catch (Exception $e) {
            $error_message = "Failed to update settings: " . $e->getMessage();
        }
    }
    
    if ($action === 'update_logo') {
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../assets/images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = 'logo.' . $file_extension;
                $target_path = $upload_dir . $filename;
                
                // Delete old logo files
                $logo_files = glob($upload_dir . 'logo.*');
                foreach ($logo_files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                    // Update logo setting
                    try {
                        $logo_path = 'assets/images/' . $filename;
                        $existing = $db->fetch("SELECT id FROM site_settings WHERE setting_key = 'site_logo'");
                        
                        if ($existing) {
                            $stmt = $db->getConnection()->prepare("UPDATE site_settings SET setting_value = ?, updated_at = ? WHERE setting_key = 'site_logo'");
                            $stmt->execute([$logo_path, date('Y-m-d H:i:s')]);
                        } else {
                            $stmt = $db->getConnection()->prepare("INSERT INTO site_settings (setting_key, setting_value, created_at) VALUES ('site_logo', ?, ?)");
                            $stmt->execute([$logo_path, date('Y-m-d H:i:s')]);
                        }
                        
                        $success_message = "Logo updated successfully.";
                    } catch (Exception $e) {
                        $error_message = "Failed to save logo setting: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Failed to upload logo file.";
                }
            } else {
                $error_message = "Invalid file format. Please upload JPG, PNG, GIF, or SVG files only.";
            }
        } else {
            $error_message = "Please select a logo file to upload.";
        }
    }
}

// Get current settings
try {
    $settings_data = $db->fetchAll("SELECT setting_key, setting_value FROM site_settings");
    $settings = [];
    foreach ($settings_data as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (Exception $e) {
    $settings = [];
    $error_message = "Failed to load settings: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-cog me-2"></i>Settings
                </h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Settings Navigation -->
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                        <button class="nav-link active" id="v-pills-general-tab" data-bs-toggle="pill" data-bs-target="#v-pills-general" type="button">
                            <i class="fas fa-globe me-2"></i>General
                        </button>
                        <button class="nav-link" id="v-pills-contact-tab" data-bs-toggle="pill" data-bs-target="#v-pills-contact" type="button">
                            <i class="fas fa-address-book me-2"></i>Contact Info
                        </button>
                        <button class="nav-link" id="v-pills-social-tab" data-bs-toggle="pill" data-bs-target="#v-pills-social" type="button">
                            <i class="fas fa-share-alt me-2"></i>Social Media
                        </button>
                        <button class="nav-link" id="v-pills-banking-tab" data-bs-toggle="pill" data-bs-target="#v-pills-banking" type="button">
                            <i class="fas fa-university me-2"></i>Banking
                        </button>
                        <button class="nav-link" id="v-pills-appearance-tab" data-bs-toggle="pill" data-bs-target="#v-pills-appearance" type="button">
                            <i class="fas fa-palette me-2"></i>Appearance
                        </button>
                        <button class="nav-link" id="v-pills-system-tab" data-bs-toggle="pill" data-bs-target="#v-pills-system" type="button">
                            <i class="fas fa-server me-2"></i>System
                        </button>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="col-md-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="v-pills-general">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">General Settings</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_settings">
                                        
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Site Name</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                                   value="<?php echo htmlspecialchars($settings['site_name'] ?? 'EYGII'); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="site_description" class="form-label">Site Description</label>
                                            <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? 'Eloquent Youth & Global Integrity - Reviving world integrity and moral values'); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="footer_text" class="form-label">Footer Text</label>
                                            <textarea class="form-control" id="footer_text" name="footer_text" rows="2"><?php echo htmlspecialchars($settings['footer_text'] ?? '© 2024 EYGII. All rights reserved.'); ?></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="tab-pane fade" id="v-pills-contact">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Contact Information</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_settings">
                                        
                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                                   value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'eygii2017@gmail.com'); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="contact_phone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                                   value="<?php echo htmlspecialchars($settings['contact_phone'] ?? '+234 803 123 4567'); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="contact_whatsapp" class="form-label">WhatsApp Number</label>
                                            <input type="text" class="form-control" id="contact_whatsapp" name="contact_whatsapp" 
                                                   value="<?php echo htmlspecialchars($settings['contact_whatsapp'] ?? '+234 803 123 4567'); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="contact_address" class="form-label">Address</label>
                                            <textarea class="form-control" id="contact_address" name="contact_address" rows="3"><?php echo htmlspecialchars($settings['contact_address'] ?? 'K19, Joke Plaza, Bodija, Ibadan, Oyo State, Nigeria'); ?></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="tab-pane fade" id="v-pills-social">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Social Media Links</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_settings">
                                        
                                        <div class="mb-3">
                                            <label for="social_facebook" class="form-label">
                                                <i class="fab fa-facebook me-2"></i>Facebook URL
                                            </label>
                                            <input type="url" class="form-control" id="social_facebook" name="social_facebook" 
                                                   value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>" 
                                                   placeholder="https://facebook.com/eygii">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="social_twitter" class="form-label">
                                                <i class="fab fa-twitter me-2"></i>Twitter URL
                                            </label>
                                            <input type="url" class="form-control" id="social_twitter" name="social_twitter" 
                                                   value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>" 
                                                   placeholder="https://twitter.com/eygii">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="social_instagram" class="form-label">
                                                <i class="fab fa-instagram me-2"></i>Instagram URL
                                            </label>
                                            <input type="url" class="form-control" id="social_instagram" name="social_instagram" 
                                                   value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>" 
                                                   placeholder="https://instagram.com/eygii">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="social_linkedin" class="form-label">
                                                <i class="fab fa-linkedin me-2"></i>LinkedIn URL
                                            </label>
                                            <input type="url" class="form-control" id="social_linkedin" name="social_linkedin" 
                                                   value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>" 
                                                   placeholder="https://linkedin.com/company/eygii">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="social_youtube" class="form-label">
                                                <i class="fab fa-youtube me-2"></i>YouTube URL
                                            </label>
                                            <input type="url" class="form-control" id="social_youtube" name="social_youtube" 
                                                   value="<?php echo htmlspecialchars($settings['social_youtube'] ?? ''); ?>" 
                                                   placeholder="https://youtube.com/c/eygii">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Banking Information -->
                        <div class="tab-pane fade" id="v-pills-banking">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Banking Information</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_settings">
                                        
                                        <div class="mb-3">
                                            <label for="bank_name" class="form-label">Bank Name</label>
                                            <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                                   value="<?php echo htmlspecialchars($settings['bank_name'] ?? 'United Bank for Africa'); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_account_name" class="form-label">Account Name</label>
                                            <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" 
                                                   value="<?php echo htmlspecialchars($settings['bank_account_name'] ?? 'Eloquent Youth & Global Integrity'); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bank_account_number" class="form-label">Account Number</label>
                                            <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" 
                                                   value="<?php echo htmlspecialchars($settings['bank_account_number'] ?? '1024384710'); ?>">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Appearance -->
                        <div class="tab-pane fade" id="v-pills-appearance">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Appearance Settings</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Logo Upload -->
                                    <div class="mb-4">
                                        <h6>Site Logo</h6>
                                        <?php if (isset($settings['site_logo']) && $settings['site_logo']): ?>
                                            <div class="mb-3">
                                                <img src="../../<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                                     alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="action" value="update_logo">
                                            <div class="mb-3">
                                                <input type="file" class="form-control" name="logo" accept="image/*" required>
                                                <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, SVG. Recommended size: 200x60px</small>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-1"></i>Upload Logo
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="tab-pane fade" id="v-pills-system">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">System Settings</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_settings">
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                                       <?php echo isset($settings['maintenance_mode']) && $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="maintenance_mode">
                                                    Maintenance Mode
                                                </label>
                                                <small class="form-text text-muted d-block">When enabled, the website will show a maintenance page to visitors.</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="allow_registrations" name="allow_registrations" 
                                                       <?php echo isset($settings['allow_registrations']) && $settings['allow_registrations'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="allow_registrations">
                                                    Allow User Registrations
                                                </label>
                                                <small class="form-text text-muted d-block">Allow new users to register on the website.</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                                       <?php echo isset($settings['email_notifications']) && $settings['email_notifications'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="email_notifications">
                                                    Email Notifications
                                                </label>
                                                <small class="form-text text-muted d-block">Send email notifications for new contacts, donations, etc.</small>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Save Changes
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- System Information -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">System Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                                            <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                                            <p><strong>Database:</strong> MySQL</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Upload Max Size:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
                                            <p><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></p>
                                            <p><strong>Max Execution Time:</strong> <?php echo ini_get('max_execution_time'); ?>s</p>
                                        </div>
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

<?php include '../includes/footer.php'; ?> 