<?php
/**
 * Simple Password Update Script - Direct PDO approach
 * This bypasses the Database class to ensure it works
 */

session_start();

$message = '';
$error = '';
$success = false;

// Database connection
$dsn = "mysql:host=localhost;dbname=eygii;charset=utf8mb4";
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get admin users
$stmt = $pdo->query("SELECT username, full_name, email, status, last_login FROM admin_users ORDER BY username");
$admin_users = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_username = trim($_POST['username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($selected_username)) {
        $error = 'Please select a username.';
    } elseif (empty($new_password)) {
        $error = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, username FROM admin_users WHERE username = ?");
            $stmt->execute([$selected_username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'User not found.';
            } else {
                // Hash the new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update the password
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, updated_at = ? WHERE username = ?");
                $updated = $stmt->execute([$password_hash, date('Y-m-d H:i:s'), $selected_username]);
                
                if ($updated && $stmt->rowCount() > 0) {
                    $success = true;
                    $message = "Password updated successfully for user: {$selected_username}";
                    
                    // Log the password change
                    $log_message = "[" . date('Y-m-d H:i:s') . "] Password updated for admin user: {$selected_username} from IP: " . $_SERVER['REMOTE_ADDR'] . " (Simple Update Script)" . PHP_EOL;
                    $log_dir = __DIR__ . '/../logs';
                    if (!is_dir($log_dir)) {
                        mkdir($log_dir, 0755, true);
                    }
                    file_put_contents($log_dir . '/admin_password_changes.log', $log_message, FILE_APPEND | LOCK_EX);
                } else {
                    $error = 'Failed to update password. No rows were affected.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Password Update - EYGII</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --accent-gold: #f59e0b;
        }
        
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 50px 0;
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
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            Simple Password Update
                        </h3>
                        <p class="mb-0 mt-2">Direct Database Approach</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                            <div class="text-center">
                                <a href="../admin/index.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Test Login
                                </a>
                                <a href="update_admin_password.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Main Tool
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-1"></i>Select Admin User
                                    </label>
                                    <select class="form-control" id="username" name="username" required>
                                        <option value="">Choose user...</option>
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
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>New Password
                                    </label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" required minlength="8"
                                           placeholder="Enter new password (min 8 characters)">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Confirm Password
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required
                                           placeholder="Confirm new password">
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Update Password
                                    </button>
                                </div>
                            </form>
                            
                            <div class="mt-4">
                                <h6>Available Users:</h6>
                                <ul class="list-group">
                                    <?php foreach ($admin_users as $user): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                <?php if ($user['full_name']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($user['full_name']); ?></small>
                                                <?php endif; ?>
                                            </span>
                                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-white">
                        <i class="fas fa-info-circle me-1"></i>
                        This tool uses direct PDO for maximum compatibility
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 