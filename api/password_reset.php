<?php
/**
 * Password Reset API
 * Handles password reset requests and token validation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../database/config.php';

try {
    $db = new Database();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'request_reset':
            requestPasswordReset($db);
            break;
        case 'verify_token':
            verifyResetToken($db);
            break;
        case 'reset_password':
            resetPassword($db);
            break;
        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function requestPasswordReset($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $resetId = trim($input['resetId'] ?? '');

    if (empty($resetId)) {
        throw new Exception('Email or phone number is required');
    }

    // Determine if it's email or phone
    $isEmail = filter_var($resetId, FILTER_VALIDATE_EMAIL);
    $field = $isEmail ? 'email' : 'phone';

    // Check if user exists
    $db->query("SELECT id, name, email, phone FROM users WHERE $field = :resetId AND is_active = 1");
    $db->bind(':resetId', $resetId);
    $user = $db->single();

    if (!$user) {
        // Don't reveal if user exists or not for security
        echo json_encode([
            'success' => true,
            'message' => 'If an account with that email/phone exists, you will receive a reset link.'
        ]);
        return;
    }

    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store reset token
    $db->query("UPDATE users SET password_reset_token = :token, password_reset_expires = :expires WHERE id = :id");
    $db->bind(':token', $token);
    $db->bind(':expires', $expires);
    $db->bind(':id', $user['id']);
    $db->execute();

    // In a real application, you would send email/SMS here
    // For demo purposes, we'll just return success
    if ($isEmail) {
        // Send email (simulated)
        $resetLink = "http://localhost/LFLshop/html/reset-password.html?token=$token";
        // sendResetEmail($user['email'], $resetLink);
    } else {
        // Send SMS (simulated)
        // sendResetSMS($user['phone'], $token);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Password reset instructions have been sent.',
        'debug_token' => $token // Remove in production
    ]);
}

function verifyResetToken($db) {
    $token = $_GET['token'] ?? '';

    if (empty($token)) {
        throw new Exception('Reset token is required');
    }

    $db->query("SELECT id, name, email FROM users WHERE password_reset_token = :token AND password_reset_expires > NOW() AND is_active = 1");
    $db->bind(':token', $token);
    $user = $db->single();

    if (!$user) {
        throw new Exception('Invalid or expired reset token');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Token is valid',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);
}

function resetPassword($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? '';
    $newPassword = $input['password'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';

    if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
        throw new Exception('All fields are required');
    }

    if ($newPassword !== $confirmPassword) {
        throw new Exception('Passwords do not match');
    }

    if (strlen($newPassword) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    // Verify token
    $db->query("SELECT id FROM users WHERE password_reset_token = :token AND password_reset_expires > NOW() AND is_active = 1");
    $db->bind(':token', $token);
    $user = $db->single();

    if (!$user) {
        throw new Exception('Invalid or expired reset token');
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password and clear reset token
    $db->query("UPDATE users SET password = :password, password_reset_token = NULL, password_reset_expires = NULL WHERE id = :id");
    $db->bind(':password', $hashedPassword);
    $db->bind(':id', $user['id']);
    $db->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Password has been reset successfully'
    ]);
}

// Helper functions (would be implemented in a real application)
function sendResetEmail($email, $resetLink) {
    // Email sending logic would go here
    // Using services like PHPMailer, SendGrid, etc.
}

function sendResetSMS($phone, $token) {
    // SMS sending logic would go here
    // Using services like Twilio, Africa's Talking, etc.
}
?>