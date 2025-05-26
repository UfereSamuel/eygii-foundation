<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    exit('Unauthorized');
}

try {
    $db = Database::getInstance();
    $subscribers = $db->fetchAll("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC");
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID',
        'Email',
        'Name',
        'Status',
        'Subscribed At',
        'Updated At'
    ]);
    
    // Add data rows
    foreach ($subscribers as $subscriber) {
        fputcsv($output, [
            $subscriber['id'],
            $subscriber['email'],
            $subscriber['name'],
            $subscriber['status'],
            $subscriber['subscribed_at'],
            $subscriber['updated_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Export failed: ' . $e->getMessage();
}
?> 