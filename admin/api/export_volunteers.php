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
    $volunteers = $db->fetchAll("SELECT * FROM volunteers ORDER BY applied_at DESC");
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="volunteers_export_' . date('Y-m-d') . '.csv"');
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
        'Age',
        'Occupation',
        'Skills',
        'Interests',
        'Experience',
        'Motivation',
        'Availability',
        'Emergency Contact Name',
        'Emergency Contact Phone',
        'Status',
        'Notes',
        'Applied At',
        'Updated At'
    ]);
    
    // Add data rows
    foreach ($volunteers as $volunteer) {
        fputcsv($output, [
            $volunteer['id'],
            $volunteer['name'],
            $volunteer['email'],
            $volunteer['phone'],
            $volunteer['age'],
            $volunteer['occupation'],
            $volunteer['skills'],
            $volunteer['interests'],
            $volunteer['experience'],
            $volunteer['motivation'],
            $volunteer['availability'],
            $volunteer['emergency_contact_name'],
            $volunteer['emergency_contact_phone'],
            $volunteer['status'],
            $volunteer['notes'],
            $volunteer['applied_at'],
            $volunteer['updated_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Export failed: ' . $e->getMessage();
}
?> 