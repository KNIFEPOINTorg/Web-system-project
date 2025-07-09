<?php
/**
 * Authentication API for LFLshop Ethiopian E-commerce Platform
 */

// IMPORTANT: Secure session configuration for production
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Initialize secure API headers and error handling
require_once 'cors_config.php';
require_once 'error_handler.php';
initializeSecureAPI();

require_once '../database/config.php';

class AuthAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        // Handle preflight requests
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        switch ($method) {
            case 'POST':
                switch ($action) {
                    case 'login':
                        return $this->login();
                    case 'register':
                        return $this->register();
                    case 'logout':
                        return $this->logout();
                    default:
                        return $this->error('Invalid action');
                }
            case 'GET':
                switch ($action) {
                    case 'check':
                        return $this->checkAuth();
                    default:
                        return $this->error('Invalid action');
                }
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function login() {
        try {
            // Check rate limiting first
            if (!$this->checkRateLimit()) {
                return $this->error('Too many login attempts. Please try again in 15 minutes.');
            }

            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            APIErrorHandler::validateRequired($input, ['email', 'password']);

            $email = APIErrorHandler::sanitizeInput($input['email']);
            $password = $input['password'];

            // Validate email format
            APIErrorHandler::validateEmail($email);
            
            // Get user from database
            $this->db->query('SELECT * FROM users WHERE email = :email AND is_active = 1');
            $this->db->bind(':email', $email);
            $user = $this->db->single();
            
            // Check if user exists and password is correct
            if (!$user) {
                $this->recordFailedAttempt($email);
                APIErrorHandler::sendError('Invalid email or password', APIErrorHandler::ERROR_UNAUTHORIZED);
            }

            if (!password_verify($password, $user['password'])) {
                $this->recordFailedAttempt($email);
                APIErrorHandler::sendError('Invalid email or password', APIErrorHandler::ERROR_UNAUTHORIZED);
            }

            // Clear failed attempts on successful login
            $this->clearFailedAttempts($email);
            
            // Update last login
            $this->db->query('UPDATE users SET last_login = NOW() WHERE id = :user_id');
            $this->db->bind(':user_id', $user['id']);
            $this->db->execute();
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            $_SESSION['login_time'] = time();
            
            // Remove password from user data
            unset($user['password']);
            
            // Determine redirect URL
            $redirectUrl = ($user['user_type'] === 'seller') ? 'seller-dashboard.html' : 'customer-dashboard.html';
            
            APIErrorHandler::sendSuccess('Login successful', [
                'user' => $user,
                'redirect' => $redirectUrl
            ]);
            
        } catch (Exception $e) {
            APIErrorHandler::sendError('Login failed: ' . $e->getMessage(), APIErrorHandler::ERROR_INTERNAL);
        }
    }
    
    private function register() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $required = ['firstName', 'lastName', 'email', 'password', 'confirmPassword'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty(trim($input[$field]))) {
                    return $this->error(ucfirst($field) . ' is required');
                }
            }
            
            // Validate passwords match
            if ($input['password'] !== $input['confirmPassword']) {
                return $this->error('Passwords do not match');
            }
            
            // Validate password length
            if (strlen($input['password']) < 6) {
                return $this->error('Password must be at least 6 characters long');
            }
            
            // Validate email
            $email = trim($input['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('Invalid email format');
            }
            
            // Check if email already exists
            $this->db->query('SELECT id FROM users WHERE email = :email');
            $this->db->bind(':email', $email);
            if ($this->db->single()) {
                return $this->error('Email address is already registered');
            }
            
            // Prepare user data
            $name = trim($input['firstName'] . ' ' . $input['lastName']);
            $userType = isset($input['userType']) ? $input['userType'] : 'customer';
            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
            
            // Insert new user
            $this->db->query('
                INSERT INTO users (name, email, password, user_type, is_active, email_verified, created_at) 
                VALUES (:name, :email, :password, :user_type, 1, 1, NOW())
            ');
            $this->db->bind(':name', $name);
            $this->db->bind(':email', $email);
            $this->db->bind(':password', $hashedPassword);
            $this->db->bind(':user_type', $userType);
            
            if ($this->db->execute()) {
                $userId = $this->db->lastInsertId();
                
                // Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_type'] = $userType;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['logged_in'] = true;
                
                $redirectUrl = ($userType === 'seller') ? 'seller-dashboard.html' : 'customer-dashboard.html';
                
                return $this->success('Registration successful', [
                    'user' => [
                        'id' => $userId,
                        'name' => $name,
                        'email' => $email,
                        'user_type' => $userType
                    ],
                    'redirect' => $redirectUrl
                ]);
            } else {
                return $this->error('Registration failed. Please try again.');
            }
            
        } catch (Exception $e) {
            return $this->error('Registration failed: ' . $e->getMessage());
        }
    }
    
    private function logout() {
        session_destroy();
        return $this->success('Logout successful');
    }
    
    private function checkAuth() {
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > 3600) { // 1 hour timeout
                session_destroy();
                return $this->error('Session expired', 401);
            }
        }
        $_SESSION['last_activity'] = time();

        if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            return $this->success('User is authenticated', [
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'user_type' => $_SESSION['user_type']
                ]
            ]);
        }

        return $this->error('User not authenticated', 401);
    }

    /**
     * Check if IP address has exceeded login rate limit
     */
    private function checkRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $maxAttempts = 5;
        $timeWindow = 900; // 15 minutes

        // Create login_attempts table if it doesn't exist
        $this->db->query("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                email VARCHAR(255),
                attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_time (ip_address, attempt_time)
            )
        ");
        $this->db->execute();

        // Count recent attempts from this IP
        $this->db->query("
            SELECT COUNT(*) as attempt_count
            FROM login_attempts
            WHERE ip_address = :ip
            AND attempt_time > DATE_SUB(NOW(), INTERVAL :time_window SECOND)
        ");
        $this->db->bind(':ip', $ip);
        $this->db->bind(':time_window', $timeWindow);
        $result = $this->db->single();

        return $result['attempt_count'] < $maxAttempts;
    }

    /**
     * Record a failed login attempt
     */
    private function recordFailedAttempt($email) {
        $ip = $_SERVER['REMOTE_ADDR'];

        $this->db->query("
            INSERT INTO login_attempts (ip_address, email, attempt_time)
            VALUES (:ip, :email, NOW())
        ");
        $this->db->bind(':ip', $ip);
        $this->db->bind(':email', $email);
        $this->db->execute();

        // Log security event
        $logEntry = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'login_failed',
            'ip' => $ip,
            'email' => $email
        ]);
        error_log($logEntry . "\n", 3, __DIR__ . '/../logs/security.log');
    }

    /**
     * Clear failed attempts for successful login
     */
    private function clearFailedAttempts($email) {
        $ip = $_SERVER['REMOTE_ADDR'];

        $this->db->query("
            DELETE FROM login_attempts
            WHERE ip_address = :ip AND email = :email
        ");
        $this->db->bind(':ip', $ip);
        $this->db->bind(':email', $email);
        $this->db->execute();
    }

    private function success($message, $data = null) {
        $response = ['success' => true, 'message' => $message];
        if ($data) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit;
    }
    
    private function error($message, $code = 400) {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

// Handle the request
try {
    $api = new AuthAPI();
    $api->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
