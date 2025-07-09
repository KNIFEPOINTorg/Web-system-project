<?php
/**
 * Standardized Error Handling for LFLshop APIs
 * Provides consistent error response format across all API endpoints
 */

class APIErrorHandler {
    
    // Error codes
    const ERROR_VALIDATION = 400;
    const ERROR_UNAUTHORIZED = 401;
    const ERROR_FORBIDDEN = 403;
    const ERROR_NOT_FOUND = 404;
    const ERROR_METHOD_NOT_ALLOWED = 405;
    const ERROR_CONFLICT = 409;
    const ERROR_RATE_LIMITED = 429;
    const ERROR_INTERNAL = 500;
    const ERROR_SERVICE_UNAVAILABLE = 503;
    
    /**
     * Send standardized error response
     */
    public static function sendError($message, $code = self::ERROR_INTERNAL, $details = null, $field = null) {
        http_response_code($code);
        
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
                'timestamp' => date('c')
            ]
        ];
        
        // Add additional details if provided
        if ($details !== null) {
            $response['error']['details'] = $details;
        }
        
        // Add field information for validation errors
        if ($field !== null) {
            $response['error']['field'] = $field;
        }
        
        // Log error for debugging (except for validation errors)
        if ($code >= 500) {
            self::logError($message, $code, $details);
        }
        
        echo json_encode($response);
        exit();
    }
    
    /**
     * Send standardized success response
     */
    public static function sendSuccess($message, $data = null, $meta = null) {
        http_response_code(200);
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($meta !== null) {
            $response['meta'] = $meta;
        }
        
        echo json_encode($response);
        exit();
    }
    
    /**
     * Handle validation errors
     */
    public static function sendValidationError($errors) {
        http_response_code(self::ERROR_VALIDATION);
        
        $response = [
            'success' => false,
            'error' => [
                'message' => 'Validation failed',
                'code' => self::ERROR_VALIDATION,
                'validation_errors' => $errors,
                'timestamp' => date('c')
            ]
        ];
        
        echo json_encode($response);
        exit();
    }
    
    /**
     * Handle database errors
     */
    public static function sendDatabaseError($operation = 'database operation') {
        self::logError("Database error during: $operation", self::ERROR_INTERNAL);
        
        self::sendError(
            'A database error occurred. Please try again later.',
            self::ERROR_INTERNAL,
            ['operation' => $operation]
        );
    }
    
    /**
     * Handle authentication errors
     */
    public static function sendAuthError($message = 'Authentication required') {
        self::sendError($message, self::ERROR_UNAUTHORIZED);
    }
    
    /**
     * Handle authorization errors
     */
    public static function sendAuthorizationError($message = 'Access denied') {
        self::sendError($message, self::ERROR_FORBIDDEN);
    }
    
    /**
     * Handle not found errors
     */
    public static function sendNotFoundError($resource = 'Resource') {
        self::sendError("$resource not found", self::ERROR_NOT_FOUND);
    }
    
    /**
     * Handle rate limiting errors
     */
    public static function sendRateLimitError($message = 'Rate limit exceeded') {
        self::sendError($message, self::ERROR_RATE_LIMITED);
    }
    
    /**
     * Log errors to file
     */
    private static function logError($message, $code, $details = null) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message,
            'code' => $code,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        
        if ($details) {
            $logEntry['details'] = $details;
        }
        
        $logLine = json_encode($logEntry) . "\n";
        
        // Ensure logs directory exists
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        error_log($logLine, 3, $logDir . '/api_errors.log');
    }
    
    /**
     * Set up global exception handler
     */
    public static function setupGlobalHandler() {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }
    
    /**
     * Global exception handler
     */
    public static function handleException($exception) {
        self::logError(
            'Uncaught exception: ' . $exception->getMessage(),
            self::ERROR_INTERNAL,
            [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]
        );
        
        self::sendError('An unexpected error occurred. Please try again later.');
    }
    
    /**
     * Global error handler
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        self::logError(
            "PHP Error: $message",
            self::ERROR_INTERNAL,
            [
                'severity' => $severity,
                'file' => $file,
                'line' => $line
            ]
        );
        
        // Don't send response for non-fatal errors
        if ($severity === E_ERROR || $severity === E_CORE_ERROR || $severity === E_COMPILE_ERROR) {
            self::sendError('A system error occurred. Please try again later.');
        }
        
        return true;
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $requiredFields) {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        if (!empty($errors)) {
            self::sendValidationError($errors);
        }
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email, $fieldName = 'email') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::sendValidationError([$fieldName => 'Invalid email format']);
        }
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// Set up global error handling
APIErrorHandler::setupGlobalHandler();
?>
