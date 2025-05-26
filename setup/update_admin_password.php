<?php
/**
 * EYGII Admin Password Update Script
 * Use this script to update admin user passwords securely
 * 
 * Usage:
 * 1. Run this script in your browser: http://localhost/eygii/setup/update_admin_password.php
 * 2. Enter the username and new password
 * 3. The password will be securely hashed and updated
 */

session_start();
require_once '../config/database.php';

$message = '';
$error = '';
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $error = 'Username is required.';
    } elseif (empty($new_password)) {
        $error = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $db = Database::getInstance();
            
            // Check if user exists
            $user = $db->fetch("SELECT id, username FROM admin_users WHERE username = ?", [$username]);
            
            if (!$user) {
                $error = 'User not found.';
            } else {
                // Hash the new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update the password using direct SQL to avoid parameter conflicts
                try {
                    $stmt = $db->getConnection()->prepare("UPDATE admin_users SET password_hash = ?, updated_at = ? WHERE username = ?");
                    $updated = $stmt->execute([$password_hash, date('Y-m-d H:i:s'), $username]);
                    
                    if ($updated && $stmt->rowCount() > 0) {
                        $success = true;
                        $message = "Password updated successfully for user: {$username}";
                        
                        // Log the password change
                        $log_message = "[" . date('Y-m-d H:i:s') . "] Password updated for admin user: {$username} from IP: " . $_SERVER['REMOTE_ADDR'] . PHP_EOL;
                        $log_dir = __DIR__ . '/../logs';
                        if (!is_dir($log_dir)) {
                            mkdir($log_dir, 0755, true);
                        }
                        file_put_contents($log_dir . '/admin_password_changes.log', $log_message, FILE_APPEND | LOCK_EX);
                    } else {
                        $error = 'Failed to update password. User may not exist or no changes were made.';
                    }
                } catch (PDOException $e) {
                    $error = 'Database update error: ' . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get list of admin users for reference
$admin_users = [];
try {
    $db = Database::getInstance();
    $admin_users = $db->fetchAll("SELECT username, full_name, email, status, last_login FROM admin_users ORDER BY username");
} catch (Exception $e) {
    // Ignore error for display purposes
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Admin Password - EYGII</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --accent-gold: #f59e0b;
            --gradient-primary: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        
        body {
            background: var(--gradient-primary);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            padding-top: 50px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: var(--accent-gold);
            color: #1f2937;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-secondary {
            background: #6b7280;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: #dc2626; }
        .strength-medium { background: #f59e0b; }
        .strength-strong { background: #059669; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            Update Admin Password
                        </h3>
                        <p class="mb-0 mt-2">EYGII Admin Password Management</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                            <div class="text-center">
                                <a href="../admin/index.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Go to Admin Login
                                </a>
                                <a href="../index.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-home me-2"></i>Go to Website
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="passwordForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">
                                                <i class="fas fa-user me-1"></i>Username
                                            </label>
                                            <select class="form-control" id="username" name="username" required>
                                                <option value="">Select admin user...</option>
                                                <?php foreach ($admin_users as $user): ?>
                                                    <option value="<?php echo htmlspecialchars($user['username']); ?>" 
                                                            <?php echo (isset($_POST['username']) && $_POST['username'] === $user['username']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($user['username']); ?>
                                                        <?php if ($user['full_name']): ?>
                                                            (<?php echo htmlspecialchars($user['full_name']); ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">User Status</label>
                                            <div id="userInfo" class="form-control bg-light" style="min-height: 48px; display: flex; align-items: center;">
                                                <span class="text-muted">Select a user to see details</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">
                                                <i class="fas fa-lock me-1"></i>New Password
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="new_password" 
                                                       name="new_password" required minlength="8"
                                                       placeholder="Enter new password">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-strength" id="strengthBar"></div>
                                            <small class="text-muted">Minimum 8 characters</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">
                                                <i class="fas fa-lock me-1"></i>Confirm Password
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirm_password" 
                                                       name="confirm_password" required
                                                       placeholder="Confirm new password">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword2">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div id="passwordMatch" class="mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Update Password
                                    </button>
                                    <a href="../admin/index.php" class="btn btn-secondary btn-lg ms-2">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($admin_users) && !$success): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Current Admin Users
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Last Login</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($admin_users as $user): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($user['full_name'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <small class="text-white">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secure password update for EYGII admin users
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User data for display
        const userData = <?php echo json_encode($admin_users); ?>;
        
        // Toggle password visibility
        document.getElementById('togglePassword1').addEventListener('click', function() {
            togglePasswordVisibility('new_password', this);
        });
        
        document.getElementById('togglePassword2').addEventListener('click', function() {
            togglePasswordVisibility('confirm_password', this);
        });
        
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Username selection handler
        document.getElementById('username').addEventListener('change', function() {
            const username = this.value;
            const userInfo = document.getElementById('userInfo');
            
            if (username) {
                const user = userData.find(u => u.username === username);
                if (user) {
                    userInfo.innerHTML = `
                        <div>
                            <span class="badge bg-${user.status === 'active' ? 'success' : 'warning'} me-2">
                                ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                            </span>
                            <small class="text-muted">
                                Last login: ${user.last_login ? new Date(user.last_login).toLocaleDateString() : 'Never'}
                            </small>
                        </div>
                    `;
                }
            } else {
                userInfo.innerHTML = '<span class="text-muted">Select a user to see details</span>';
            }
        });
        
        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength < 3) {
                strengthBar.classList.add('strength-weak');
            } else if (strength < 5) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // Password match checker
        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirm.length > 0) {
                if (password === confirm) {
                    matchDiv.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>Passwords match</small>';
                } else {
                    matchDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>Passwords do not match</small>';
                }
            } else {
                matchDiv.innerHTML = '';
            }
        }
        
        document.getElementById('new_password').addEventListener('input', checkPasswordMatch);
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        
        // Form submission handler
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;
            
            // Re-enable if form submission fails
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });
    </script>
</body>
</html> 