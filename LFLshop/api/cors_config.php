<?php
/**
 * Secure CORS Configuration for LFLshop APIs
 * Replaces overly permissive CORS headers with secure configuration
 */

/**
 * Set secure CORS headers
 */
function setSecureCORSHeaders() {
    // Define allowed origins (customize for your domain)
    $allowedOrigins = [
        'http://localhost',
        'http://localhost:8080',
        'http://localhost:3000',
        'http://127.0.0.1',
        'https://yourdomain.com' // Replace with your actual domain
    ];
    
    // Get the origin of the request
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Check if the origin is allowed
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        // For same-origin requests or if no origin header
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        header("Access-Control-Allow-Origin: $protocol://$host");
    }
    
    // Set other secure CORS headers
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // 24 hours
    
    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content type
    header('Content-Type: application/json; charset=utf-8');
}

/**
 * Handle preflight OPTIONS requests
 */
function handlePreflightRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        setSecureCORSHeaders();
        http_response_code(200);
        exit();
    }
}

/**
 * Validate request origin for additional security
 */
function validateRequestOrigin() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // Allow requests without origin (direct API calls, mobile apps, etc.)
    if (empty($origin) && empty($referer)) {
        return true;
    }
    
    // Define allowed origins
    $allowedOrigins = [
        'http://localhost',
        'http://localhost:8080',
        'http://localhost:3000',
        'http://127.0.0.1',
        'https://yourdomain.com'
    ];
    
    // Check origin
    if (!empty($origin)) {
        foreach ($allowedOrigins as $allowed) {
            if (strpos($origin, $allowed) === 0) {
                return true;
            }
        }
    }
    
    // Check referer as fallback
    if (!empty($referer)) {
        foreach ($allowedOrigins as $allowed) {
            if (strpos($referer, $allowed) === 0) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Initialize secure API headers
 */
function initializeSecureAPI() {
    // Handle preflight requests
    handlePreflightRequest();
    
    // Set secure CORS headers
    setSecureCORSHeaders();
    
    // Validate origin (optional - enable for stricter security)
    // if (!validateRequestOrigin()) {
    //     http_response_code(403);
    //     echo json_encode(['success' => false, 'message' => 'Origin not allowed']);
    //     exit();
    // }
}
?>
