<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/email.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get form data
    $donor_name = trim($_POST['donor_name'] ?? '');
    $donor_email = trim($_POST['donor_email'] ?? '');
    $donor_phone = trim($_POST['donor_phone'] ?? '');
    $donation_amount = trim($_POST['donation_amount'] ?? '');
    $donation_purpose = trim($_POST['donation_purpose'] ?? '');
    $donor_message = trim($_POST['donor_message'] ?? '');
    $donor_updates = isset($_POST['donor_updates']) ? 1 : 0;
    
    // Validate required fields
    if (empty($donor_name) || empty($donor_email)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
    
    // Validate email
    if (!filter_var($donor_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }
    
    // Validate donation amount if provided
    if (!empty($donation_amount) && (!is_numeric($donation_amount) || $donation_amount <= 0)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid donation amount.']);
        exit;
    }
    
    // Validate phone number if provided
    if (!empty($donor_phone) && !preg_match('/^[\+]?[1-9][\d]{0,15}$/', str_replace([' ', '-', '(', ')'], '', $donor_phone))) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']);
        exit;
    }
    
    // Connect to database
    $db = Database::getInstance();
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Insert donation inquiry
        $donation_data = [
            'donor_name' => $donor_name,
            'donor_email' => $donor_email,
            'donor_phone' => $donor_phone,
            'donation_amount' => !empty($donation_amount) ? floatval($donation_amount) : null,
            'donation_purpose' => $donation_purpose,
            'donor_message' => $donor_message,
            'updates_requested' => $donor_updates,
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        
        $donation_id = $db->insert('donation_inquiries', $donation_data);
        
        // Add to newsletter if requested
        if ($donor_updates) {
            $newsletter_data = [
                'email' => $donor_email,
                'name' => $donor_name,
                'source' => 'donation_form',
                'subscribed_at' => date('Y-m-d H:i:s')
            ];
            
            // Check if email already exists in newsletter
            $existing = $db->fetch("SELECT id FROM newsletter_subscribers WHERE email = ?", [$donor_email]);
            if (!$existing) {
                $db->insert('newsletter_subscribers', $newsletter_data);
            }
        }
        
        // Commit transaction
        $db->commit();
        
        // Send email notifications
        $emailService = new EmailService();
        $email_data = [
            'donor_name' => $donor_name,
            'donor_email' => $donor_email,
            'donor_phone' => $donor_phone,
            'donation_amount' => $donation_amount,
            'donation_purpose' => $donation_purpose,
            'donor_message' => $donor_message,
            'donation_id' => $donation_id
        ];
        
        $emailSent = $emailService->sendDonationNotification($email_data);
        
        // Log email status
        if ($emailSent) {
            $db->update('donation_inquiries', 
                ['status' => 'inquiry'], 
                'id = ?', 
                [$donation_id]
            );
        } else {
            // Log email failure but don't fail the whole process
            error_log("Failed to send donation notification email for inquiry ID: " . $donation_id);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for your donation inquiry! We will contact you soon with further details.',
            'donation_id' => $donation_id
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Donation form error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?> 