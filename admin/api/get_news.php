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

$news_id = $_GET['id'] ?? '';

if (empty($news_id)) {
    echo json_encode(['success' => false, 'message' => 'News ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $news = $db->fetch("SELECT * FROM news WHERE id = ?", [$news_id]);
    
    if ($news) {
        echo json_encode([
            'success' => true,
            'news' => $news
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'News article not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 