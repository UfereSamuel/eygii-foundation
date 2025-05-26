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
    $contacts = $db->fetchAll("SELECT * FROM contact_submissions ORDER BY submitted_at DESC");
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="contacts_export_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID',
        'Name',
        'Email',
        'Phone',
        'Organization',
        'Subject',
        'Message',
        'Status',
        'Submitted At',
        'Updated At'
    ]);
    
    // Add data rows
    foreach ($contacts as $contact) {
        fputcsv($output, [
            $contact['id'],
            $contact['name'],
            $contact['email'],
            $contact['phone'],
            $contact['organization'],
            $contact['subject'],
            $contact['message'],
            $contact['status'],
            $contact['submitted_at'],
            $contact['updated_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Export failed: ' . $e->getMessage();
}
?> 