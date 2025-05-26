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

$volunteer_id = $_GET['id'] ?? '';

if (empty($volunteer_id)) {
    echo json_encode(['success' => false, 'message' => 'Volunteer ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $volunteer = $db->fetch("SELECT * FROM volunteers WHERE id = ?", [$volunteer_id]);
    
    if ($volunteer) {
        echo json_encode([
            'success' => true,
            'volunteer' => $volunteer
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Volunteer not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 