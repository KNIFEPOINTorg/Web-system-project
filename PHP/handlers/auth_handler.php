<?php
/**
 * Authentication Handler for LFLshop
 * Handles login, registration, and logout requests
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../../api/cors_config.php';

// Initialize secure API headers
initializeSecureAPI();

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check_session':
        handleCheckSession();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Invalid action']]);
        break;
}

/**
 * Handle user login
 */
function handleLogin() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        // Sanitize input
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        // Validate input
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Email and password are required']]);
            return;
        }
        
        if (!Security::validateEmail($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid email format']]);
            return;
        }
        
        // Check for security threats
        if (Security::detectSQLInjection($email) || Security::detectXSS($email)) {
            Security::logSecurityEvent('login_security_threat', ['email' => $email]);
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid input detected']]);
            return;
        }
        
        // Attempt login
        $user = new User();
        $result = $user->login($email, $password, $rememberMe);
        
        if ($result['success']) {
            // Set remember me cookie if requested
            if ($rememberMe) {
                $token = Security::generateToken();
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days
                // Store token in database (implement if needed)
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $_POST['redirect'] ?? 'dashboard.php',
                'user' => [
                    'id' => $result['user']['id'],
                    'username' => $result['user']['username'],
                    'email' => $result['user']['email'],
                    'first_name' => $result['user']['first_name'],
                    'last_name' => $result['user']['last_name'],
                    'role' => $result['user']['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Login handler error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Login failed. Please try again.']]);
    }
}

/**
 * Handle user registration
 */
function handleRegister() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        // Sanitize input
        $userData = [
            'username' => Security::sanitizeInput($_POST['username'] ?? ''),
            'email' => Security::sanitizeInput($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'first_name' => Security::sanitizeInput($_POST['first_name'] ?? ''),
            'last_name' => Security::sanitizeInput($_POST['last_name'] ?? ''),
            'phone' => Security::sanitizeInput($_POST['phone'] ?? ''),
            'terms_accepted' => isset($_POST['terms_accepted'])
        ];
        
        // Validate required fields
        $required = ['username', 'email', 'password', 'confirm_password', 'first_name', 'last_name'];
        $errors = [];
        
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }
        
        // Check password confirmation
        if ($userData['password'] !== $userData['confirm_password']) {
            $errors[] = "Passwords do not match";
        }
        
        // Check terms acceptance
        if (!$userData['terms_accepted']) {
            $errors[] = "You must accept the terms and conditions";
        }
        
        // Check for security threats
        foreach (['username', 'email', 'first_name', 'last_name'] as $field) {
            if (Security::detectSQLInjection($userData[$field]) || Security::detectXSS($userData[$field])) {
                Security::logSecurityEvent('registration_security_threat', ['field' => $field, 'value' => $userData[$field]]);
                $errors[] = "Invalid input detected in $field";
            }
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }
        
        // Attempt registration
        $user = new User();
        $result = $user->register($userData);
        
        if ($result['success']) {
            // Create welcome notification for new user
            try {
                $notification = new Notification();
                $userName = $userData['first_name'] . ' ' . $userData['last_name'];
                $notification->createWelcomeNotification($result['user_id'], $userName);
            } catch (Exception $e) {
                error_log("Failed to create welcome notification: " . $e->getMessage());
                // Don't fail registration if notification creation fails
            }

            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! Please log in.',
                'redirect' => 'login.php'
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Registration handler error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Registration failed. Please try again.']]);
    }
}

/**
 * Handle user logout
 */
function handleLogout() {
    try {
        $user = new User();
        $result = $user->logout();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => 'index.php'
        ]);
        
    } catch (Exception $e) {
        error_log("Logout handler error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Logout failed']]);
    }
}

/**
 * Check session status
 */
function handleCheckSession() {
    try {
        if (isLoggedIn()) {
            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'email' => $_SESSION['email'],
                    'first_name' => $_SESSION['first_name'],
                    'last_name' => $_SESSION['last_name'],
                    'role' => $_SESSION['user_role']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'logged_in' => false
            ]);
        }
    } catch (Exception $e) {
        error_log("Check session error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Session check failed']]);
    }
}
?>
