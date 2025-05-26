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
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }
    
    // Validate message length
    if (strlen($message) < 10) {
        echo json_encode(['success' => false, 'message' => 'Message must be at least 10 characters long.']);
        exit;
    }
    
    // Connect to database
    $db = Database::getInstance();
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Insert contact submission
        $contact_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'organization' => $organization,
            'message' => $message,
            'newsletter_signup' => $newsletter,
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        
        $contact_id = $db->insert('contact_submissions', $contact_data);
        
        // Add to newsletter if requested
        if ($newsletter) {
            $newsletter_data = [
                'email' => $email,
                'name' => $name,
                'source' => 'contact_form',
                'subscribed_at' => date('Y-m-d H:i:s')
            ];
            
            // Check if email already exists in newsletter
            $existing = $db->fetch("SELECT id FROM newsletter_subscribers WHERE email = ?", [$email]);
            if (!$existing) {
                $db->insert('newsletter_subscribers', $newsletter_data);
            }
        }
        
        // Commit transaction
        $db->commit();
        
        // Send email notifications
        $emailService = new EmailService();
        $email_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'organization' => $organization,
            'message' => $message,
            'contact_id' => $contact_id
        ];
        
        $emailSent = $emailService->sendContactNotification($email_data);
        
        // Log email status
        if ($emailSent) {
            $db->update('contact_submissions', 
                ['status' => 'new'], 
                'id = ?', 
                [$contact_id]
            );
        } else {
            // Log email failure but don't fail the whole process
            error_log("Failed to send contact notification email for submission ID: " . $contact_id);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for your message! We will get back to you soon.',
            'contact_id' => $contact_id
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?> 