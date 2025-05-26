<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$contact_id = $_GET['id'] ?? '';

if (empty($contact_id)) {
    echo json_encode(['success' => false, 'message' => 'Contact ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $contact = $db->fetch("SELECT * FROM contact_submissions WHERE id = ?", [$contact_id]);
    
    if ($contact) {
        echo json_encode([
            'success' => true,
            'contact' => $contact
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Contact not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 