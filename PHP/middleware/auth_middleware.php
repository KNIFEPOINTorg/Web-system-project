<?php
/**
 * Authentication Middleware for LFLshop
 * Handles authentication checks and redirects
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        // Store the current URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // If it's an AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => ['Authentication required'], 'redirect' => $redirectTo]);
            exit;
        }
        
        // Regular redirect
        redirect($redirectTo);
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        redirect($redirectTo);
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    // Clean old sessions periodically
    if (rand(1, 100) <= 5) { // 5% chance
        Security::cleanOldSessions();
    }
}

/**
 * Require admin role
 */
function requireAdmin($redirectTo = 'index.php') {
    requireAuth();
    
    if (!isAdmin()) {
        // If it's an AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => ['Admin access required'], 'redirect' => $redirectTo]);
            exit;
        }
        
        // Regular redirect
        redirect($redirectTo);
    }
}

/**
 * Require guest (not authenticated) - redirect if already logged in
 */
function requireGuest($redirectTo = 'dashboard.php') {
    if (isLoggedIn()) {
        redirect($redirectTo);
    }
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Check if user owns resource
 */
function ownsResource($resourceUserId) {
    return isLoggedIn() && getCurrentUserId() == $resourceUserId;
}

/**
 * Validate session token
 */
function validateSessionToken() {
    if (!isLoggedIn()) {
        return false;
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT user_id, last_activity 
            FROM user_sessions 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([session_id(), getCurrentUserId()]);
        $session = $stmt->fetch();
        
        if (!$session) {
            // Session not found in database
            session_destroy();
            return false;
        }
        
        // Check if session is expired
        if (strtotime($session['last_activity']) < (time() - SESSION_TIMEOUT)) {
            // Remove expired session
            $stmt = $db->prepare("DELETE FROM user_sessions WHERE id = ?");
            $stmt->execute([session_id()]);
            session_destroy();
            return false;
        }
        
        // Update last activity
        $stmt = $db->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([session_id()]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate and validate CSRF token for forms
 */
function csrfTokenField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . h($token) . '">';
}

/**
 * Validate CSRF token from request
 */
function validateCSRFToken() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    return verifyCSRFToken($token);
}

/**
 * Rate limiting middleware
 */
function checkRateLimit($action, $maxAttempts = 10, $timeWindow = 3600) {
    $identifier = $_SERVER['REMOTE_ADDR'] . '_' . $action;
    
    if (!Security::checkRateLimit($identifier, $maxAttempts, $timeWindow)) {
        http_response_code(429);
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => ['Rate limit exceeded. Please try again later.']]);
        } else {
            echo "Rate limit exceeded. Please try again later.";
        }
        exit;
    }
}

/**
 * Log security event
 */
function logSecurityEvent($event, $details = []) {
    Security::logSecurityEvent($event, array_merge($details, [
        'user_id' => getCurrentUserId(),
        'session_id' => session_id(),
        'url' => $_SERVER['REQUEST_URI'] ?? '',
        'method' => $_SERVER['REQUEST_METHOD'] ?? ''
    ]));
}

/**
 * Validate request method
 */
function requireMethod($method) {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        http_response_code(405);
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => ['Method not allowed']]);
        } else {
            echo "Method not allowed";
        }
        exit;
    }
}

/**
 * Validate AJAX request
 */
function requireAjax() {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'errors' => ['AJAX request required']]);
        exit;
    }
}

/**
 * Sanitize all input data
 */
function sanitizeInput() {
    $_GET = Security::sanitizeInput($_GET);
    $_POST = Security::sanitizeInput($_POST);
    $_REQUEST = Security::sanitizeInput($_REQUEST);
}

/**
 * Initialize security middleware
 */
function initSecurity() {
    // Sanitize input
    sanitizeInput();
    
    // Set security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HTTPS redirect in production
    if (!isset($_SERVER['HTTPS']) && $_SERVER['SERVER_NAME'] !== 'localhost') {
        redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }
}

// Initialize security on every request
initSecurity();
?>
