<?php
/**
 * EYGII Admin Password Update Script (Command Line Version)
 * Use this script to update admin passwords from the command line
 * 
 * Usage:
 * php setup/update_admin_password_cli.php [username] [new_password]
 * 
 * Examples:
 * php setup/update_admin_password_cli.php admin newpassword123
 * php setup/update_admin_password_cli.php  (interactive mode)
 */

// Ensure this is run from command line
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

echo "EYGII Admin Password Update Tool\n";
echo "================================\n\n";

// Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eygii');
define('DB_CHARSET', 'utf8mb4');

// Simple database connection for CLI
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please ensure XAMPP MySQL is running and the database 'eygii' exists.\n";
    echo "You can also try running this via the web interface: http://localhost/eygii/setup/update_admin_password.php\n";
    exit(1);
}

// Get command line arguments
$username = $argv[1] ?? null;
$new_password = $argv[2] ?? null;

// Interactive mode if no arguments provided
if (!$username || !$new_password) {
    echo "Interactive Mode\n";
    echo "---------------\n\n";
    
    // Show available users
    try {
        $stmt = $pdo->query("SELECT username, full_name, email, status, last_login FROM admin_users ORDER BY username");
        $users = $stmt->fetchAll();
        
        if (empty($users)) {
            echo "❌ No admin users found in database.\n";
            exit(1);
        }
        
        echo "Available admin users:\n";
        foreach ($users as $user) {
            $status_icon = $user['status'] === 'active' ? '✅' : '⚠️';
            $last_login = $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never';
            echo "  {$status_icon} {$user['username']}";
            if ($user['full_name']) {
                echo " ({$user['full_name']})";
            }
            echo " - Last login: {$last_login}\n";
        }
        echo "\n";
        
    } catch (PDOException $e) {
        echo "❌ Error fetching users: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Get username
    if (!$username) {
        echo "Enter username: ";
        $username = trim(fgets(STDIN));
        
        if (empty($username)) {
            echo "❌ Username cannot be empty.\n";
            exit(1);
        }
    }
    
    // Get password
    if (!$new_password) {
        echo "Enter new password (minimum 8 characters): ";
        
        // Hide password input on Unix systems
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            system('stty -echo');
            $new_password = trim(fgets(STDIN));
            system('stty echo');
            echo "\n";
        } else {
            $new_password = trim(fgets(STDIN));
        }
        
        if (empty($new_password)) {
            echo "❌ Password cannot be empty.\n";
            exit(1);
        }
        
        if (strlen($new_password) < 8) {
            echo "❌ Password must be at least 8 characters long.\n";
            exit(1);
        }
        
        // Confirm password
        echo "Confirm new password: ";
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            system('stty -echo');
            $confirm_password = trim(fgets(STDIN));
            system('stty echo');
            echo "\n";
        } else {
            $confirm_password = trim(fgets(STDIN));
        }
        
        if ($new_password !== $confirm_password) {
            echo "❌ Passwords do not match.\n";
            exit(1);
        }
    }
}

// Validate inputs
if (strlen($new_password) < 8) {
    echo "❌ Password must be at least 8 characters long.\n";
    exit(1);
}

// Check if user exists
try {
    $stmt = $pdo->prepare("SELECT id, username, full_name FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "❌ User '{$username}' not found.\n";
        exit(1);
    }
    
    echo "Found user: {$user['username']}";
    if ($user['full_name']) {
        echo " ({$user['full_name']})";
    }
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Error checking user: " . $e->getMessage() . "\n";
    exit(1);
}

// Confirm the action
echo "\nAre you sure you want to update the password for '{$username}'? (y/N): ";
$confirm = trim(fgets(STDIN));

if (strtolower($confirm) !== 'y' && strtolower($confirm) !== 'yes') {
    echo "Operation cancelled.\n";
    exit(0);
}

// Update the password
try {
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, updated_at = ? WHERE username = ?");
    $updated = $stmt->execute([$password_hash, date('Y-m-d H:i:s'), $username]);
    
    if ($updated && $stmt->rowCount() > 0) {
        echo "\n✅ Password updated successfully for user: {$username}\n";
        
        // Log the password change
        $log_message = "[" . date('Y-m-d H:i:s') . "] Password updated for admin user: {$username} via CLI from " . gethostname() . PHP_EOL;
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        file_put_contents($log_dir . '/admin_password_changes.log', $log_message, FILE_APPEND | LOCK_EX);
        
        echo "📝 Password change logged to: logs/admin_password_changes.log\n";
        echo "\n🔐 Security recommendations:\n";
        echo "   • Use a strong, unique password\n";
        echo "   • Enable two-factor authentication if available\n";
        echo "   • Log out from all devices after password change\n";
        echo "   • Monitor admin access logs regularly\n";
        
    } else {
        echo "❌ Failed to update password. Please try again.\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "❌ Error updating password: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Password update completed successfully!\n";
echo "You can now login with the new password at: http://localhost/eygii/admin/\n";
?> 