<?php
/**
 * Contact Handler for LFLshop
 * Handles contact form submissions and inquiries
 */

require_once __DIR__ . '/../config/config.php';

// Set JSON response header
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'submit_contact':
        handleContactSubmission();
        break;
    case 'subscribe_newsletter':
        handleNewsletterSubscription();
        break;
    case 'get_contact_messages':
        requireAdmin();
        handleGetContactMessages();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Invalid action']]);
        break;
}

/**
 * Handle contact form submission
 */
function handleContactSubmission() {
    try {
        // Rate limiting
        checkRateLimit('contact_form', 5, 3600); // 5 submissions per hour
        
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        // Sanitize input
        $contactData = [
            'name' => Security::sanitizeInput($_POST['name'] ?? ''),
            'email' => Security::sanitizeInput($_POST['email'] ?? ''),
            'phone' => Security::sanitizeInput($_POST['phone'] ?? ''),
            'subject' => Security::sanitizeInput($_POST['subject'] ?? ''),
            'message' => Security::sanitizeInput($_POST['message'] ?? ''),
            'category' => Security::sanitizeInput($_POST['category'] ?? 'general')
        ];
        
        // Validate required fields
        $errors = [];
        
        if (empty($contactData['name'])) {
            $errors[] = "Name is required";
        }
        
        if (empty($contactData['email'])) {
            $errors[] = "Email is required";
        } elseif (!Security::validateEmail($contactData['email'])) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($contactData['subject'])) {
            $errors[] = "Subject is required";
        }
        
        if (empty($contactData['message'])) {
            $errors[] = "Message is required";
        }
        
        // Check for security threats
        foreach ($contactData as $field => $value) {
            if (Security::detectSQLInjection($value) || Security::detectXSS($value)) {
                Security::logSecurityEvent('contact_form_security_threat', ['field' => $field]);
                $errors[] = "Invalid input detected";
                break;
            }
        }
        
        // Validate message length
        if (strlen($contactData['message']) < 10) {
            $errors[] = "Message must be at least 10 characters long";
        }
        
        if (strlen($contactData['message']) > 2000) {
            $errors[] = "Message must be less than 2000 characters";
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }
        
        // Store contact message in database
        $result = storeContactMessage($contactData);
        
        if ($result['success']) {
            // Send notification email to admin (if configured)
            sendContactNotification($contactData);
            
            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.',
                'contact_id' => $result['contact_id']
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'errors' => ['Failed to submit contact form']]);
        }
        
    } catch (Exception $e) {
        error_log("Contact form error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Contact form submission failed']]);
    }
}

/**
 * Handle newsletter subscription
 */
function handleNewsletterSubscription() {
    try {
        // Rate limiting
        checkRateLimit('newsletter_subscription', 3, 3600); // 3 subscriptions per hour
        
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        
        // Validate email
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Email is required']]);
            return;
        }
        
        if (!Security::validateEmail($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid email format']]);
            return;
        }
        
        // Check for security threats
        if (Security::detectSQLInjection($email) || Security::detectXSS($email)) {
            Security::logSecurityEvent('newsletter_security_threat', ['email' => $email]);
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid input detected']]);
            return;
        }
        
        // Store newsletter subscription
        $result = storeNewsletterSubscription($email);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Successfully subscribed to newsletter!'
            ]);
        } else {
            if ($result['duplicate']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'You are already subscribed to our newsletter!'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'errors' => ['Newsletter subscription failed']]);
            }
        }
        
    } catch (Exception $e) {
        error_log("Newsletter subscription error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Newsletter subscription failed']]);
    }
}

/**
 * Get contact messages (Admin only)
 */
function handleGetContactMessages() {
    try {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $status = Security::sanitizeInput($_GET['status'] ?? '');
        $category = Security::sanitizeInput($_GET['category'] ?? '');
        
        $db = getDB();
        $offset = ($page - 1) * $limit;
        
        // Build query conditions
        $whereConditions = [];
        $params = [];
        
        if (!empty($status)) {
            $whereConditions[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($category)) {
            $whereConditions[] = "category = ?";
            $params[] = $category;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM contact_messages $whereClause";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $totalMessages = $stmt->fetchColumn();
        
        // Get messages
        $sql = "
            SELECT * FROM contact_messages 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'messages' => $messages,
                'total' => $totalMessages,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalMessages / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Get contact messages error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get contact messages']]);
    }
}

/**
 * Store contact message in database
 */
function storeContactMessage($contactData) {
    try {
        $db = getDB();
        
        // Create contact_messages table if it doesn't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                subject VARCHAR(200) NOT NULL,
                message TEXT NOT NULL,
                category VARCHAR(50) DEFAULT 'general',
                status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        $stmt = $db->prepare("
            INSERT INTO contact_messages (name, email, phone, subject, message, category, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $contactData['name'],
            $contactData['email'],
            $contactData['phone'],
            $contactData['subject'],
            $contactData['message'],
            $contactData['category'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        if ($result) {
            return ['success' => true, 'contact_id' => $db->lastInsertId()];
        }
        
        return ['success' => false];
        
    } catch (Exception $e) {
        error_log("Store contact message error: " . $e->getMessage());
        return ['success' => false];
    }
}

/**
 * Store newsletter subscription
 */
function storeNewsletterSubscription($email) {
    try {
        $db = getDB();
        
        // Create newsletter_subscriptions table if it doesn't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) UNIQUE NOT NULL,
                status ENUM('active', 'unsubscribed') DEFAULT 'active',
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        $stmt = $db->prepare("
            INSERT INTO newsletter_subscriptions (email, ip_address, user_agent)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            status = 'active',
            updated_at = CURRENT_TIMESTAMP
        ");
        
        $result = $stmt->execute([
            $email,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        if ($result) {
            // Check if it was an update (duplicate)
            $duplicate = $stmt->rowCount() === 0;
            return ['success' => true, 'duplicate' => $duplicate];
        }
        
        return ['success' => false];
        
    } catch (Exception $e) {
        error_log("Store newsletter subscription error: " . $e->getMessage());
        return ['success' => false];
    }
}

/**
 * Send contact notification email to admin
 */
function sendContactNotification($contactData) {
    try {
        // This is a placeholder for email functionality
        // In a real application, you would integrate with an email service
        // like PHPMailer, SendGrid, or similar
        
        $adminEmail = 'admin@lflshop.com';
        $subject = 'New Contact Form Submission - ' . $contactData['subject'];
        
        $message = "
            New contact form submission received:
            
            Name: {$contactData['name']}
            Email: {$contactData['email']}
            Phone: {$contactData['phone']}
            Subject: {$contactData['subject']}
            Category: {$contactData['category']}
            
            Message:
            {$contactData['message']}
            
            Submitted at: " . date('Y-m-d H:i:s') . "
            IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
        ";
        
        // Log the notification (replace with actual email sending)
        error_log("Contact notification: " . $subject);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Send contact notification error: " . $e->getMessage());
        return false;
    }
}
?>
