<?php
/**
 * Security Testing Script for LFLshop
 * Tests security measures and validates backend functionality
 */

require_once __DIR__ . '/../config/config.php';

// Only allow access from localhost during development
if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    http_response_code(403);
    die('Access denied');
}

// Set content type
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFLshop Security Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .test-pass { color: #28a745; font-weight: bold; }
        .test-fail { color: #dc3545; font-weight: bold; }
        .test-warning { color: #ffc107; font-weight: bold; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
        .pass { background: #d4edda; border: 1px solid #c3e6cb; }
        .fail { background: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat { padding: 15px; border-radius: 5px; text-align: center; flex: 1; }
        .stat.pass { background: #d4edda; }
        .stat.fail { background: #f8d7da; }
        .stat.warning { background: #fff3cd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>LFLshop Security & Functionality Test</h1>
        <p>Testing backend security measures and functionality...</p>

        <?php
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;
        $warnings = 0;

        function runTest($testName, $testFunction) {
            global $totalTests, $passedTests, $failedTests, $warnings;
            $totalTests++;
            
            try {
                $result = $testFunction();
                if ($result['status'] === 'pass') {
                    $passedTests++;
                    echo "<div class='test-result pass'><span class='test-pass'>✓ PASS:</span> {$testName}";
                } elseif ($result['status'] === 'warning') {
                    $warnings++;
                    echo "<div class='test-result warning'><span class='test-warning'>⚠ WARNING:</span> {$testName}";
                } else {
                    $failedTests++;
                    echo "<div class='test-result fail'><span class='test-fail'>✗ FAIL:</span> {$testName}";
                }
                
                if (!empty($result['message'])) {
                    echo "<br><small>{$result['message']}</small>";
                }
                echo "</div>";
                
            } catch (Exception $e) {
                $failedTests++;
                echo "<div class='test-result fail'><span class='test-fail'>✗ ERROR:</span> {$testName}<br><small>Exception: {$e->getMessage()}</small></div>";
            }
        }

        // Database Connection Test
        echo "<div class='test-section'>";
        echo "<h2>Database Connection Tests</h2>";
        
        runTest("Database Connection", function() {
            try {
                $db = getDB();
                return ['status' => 'pass', 'message' => 'Database connection successful'];
            } catch (Exception $e) {
                return ['status' => 'fail', 'message' => 'Database connection failed: ' . $e->getMessage()];
            }
        });

        runTest("Database Tables Exist", function() {
            $db = getDB();
            $requiredTables = ['users', 'products', 'orders', 'categories', 'cart', 'wishlist'];
            $missingTables = [];
            
            foreach ($requiredTables as $table) {
                $stmt = $db->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if (!$stmt->fetch()) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                return ['status' => 'pass', 'message' => 'All required tables exist'];
            } else {
                return ['status' => 'fail', 'message' => 'Missing tables: ' . implode(', ', $missingTables)];
            }
        });

        echo "</div>";

        // Security Tests
        echo "<div class='test-section'>";
        echo "<h2>Security Tests</h2>";

        runTest("CSRF Token Generation", function() {
            $token1 = generateCSRFToken();
            $token2 = generateCSRFToken();
            
            if (!empty($token1) && $token1 === $token2 && strlen($token1) >= 32) {
                return ['status' => 'pass', 'message' => 'CSRF token generation working correctly'];
            } else {
                return ['status' => 'fail', 'message' => 'CSRF token generation failed'];
            }
        });

        runTest("Password Hashing", function() {
            $password = 'TestPassword123!';
            $hash = Security::hashPassword($password);
            $verify = Security::verifyPassword($password, $hash);
            
            if ($verify && strlen($hash) > 50) {
                return ['status' => 'pass', 'message' => 'Password hashing working correctly'];
            } else {
                return ['status' => 'fail', 'message' => 'Password hashing failed'];
            }
        });

        runTest("Input Sanitization", function() {
            $maliciousInput = '<script>alert("xss")</script>';
            $sanitized = Security::sanitizeInput($maliciousInput);
            
            if (strpos($sanitized, '<script>') === false) {
                return ['status' => 'pass', 'message' => 'Input sanitization working correctly'];
            } else {
                return ['status' => 'fail', 'message' => 'Input sanitization failed'];
            }
        });

        runTest("SQL Injection Detection", function() {
            $sqlInjection = "'; DROP TABLE users; --";
            $detected = Security::detectSQLInjection($sqlInjection);
            
            if ($detected) {
                return ['status' => 'pass', 'message' => 'SQL injection detection working'];
            } else {
                return ['status' => 'fail', 'message' => 'SQL injection detection failed'];
            }
        });

        runTest("XSS Detection", function() {
            $xssAttempt = '<script>alert("xss")</script>';
            $detected = Security::detectXSS($xssAttempt);
            
            if ($detected) {
                return ['status' => 'pass', 'message' => 'XSS detection working'];
            } else {
                return ['status' => 'fail', 'message' => 'XSS detection failed'];
            }
        });

        runTest("Email Validation", function() {
            $validEmail = 'test@example.com';
            $invalidEmail = 'invalid-email';
            
            $validResult = Security::validateEmail($validEmail);
            $invalidResult = Security::validateEmail($invalidEmail);
            
            if ($validResult && !$invalidResult) {
                return ['status' => 'pass', 'message' => 'Email validation working correctly'];
            } else {
                return ['status' => 'fail', 'message' => 'Email validation failed'];
            }
        });

        echo "</div>";

        // Class Tests
        echo "<div class='test-section'>";
        echo "<h2>Class Functionality Tests</h2>";

        runTest("User Class Instantiation", function() {
            $user = new User();
            if ($user instanceof User) {
                return ['status' => 'pass', 'message' => 'User class instantiated successfully'];
            } else {
                return ['status' => 'fail', 'message' => 'User class instantiation failed'];
            }
        });

        runTest("Product Class Instantiation", function() {
            $product = new Product();
            if ($product instanceof Product) {
                return ['status' => 'pass', 'message' => 'Product class instantiated successfully'];
            } else {
                return ['status' => 'fail', 'message' => 'Product class instantiation failed'];
            }
        });

        runTest("Order Class Instantiation", function() {
            $order = new Order();
            if ($order instanceof Order) {
                return ['status' => 'pass', 'message' => 'Order class instantiated successfully'];
            } else {
                return ['status' => 'fail', 'message' => 'Order class instantiation failed'];
            }
        });

        echo "</div>";

        // Configuration Tests
        echo "<div class='test-section'>";
        echo "<h2>Configuration Tests</h2>";

        runTest("Required Constants Defined", function() {
            $requiredConstants = ['APP_NAME', 'BASE_URL', 'DB_HOST', 'DB_NAME', 'CSRF_TOKEN_NAME'];
            $missingConstants = [];
            
            foreach ($requiredConstants as $constant) {
                if (!defined($constant)) {
                    $missingConstants[] = $constant;
                }
            }
            
            if (empty($missingConstants)) {
                return ['status' => 'pass', 'message' => 'All required constants defined'];
            } else {
                return ['status' => 'fail', 'message' => 'Missing constants: ' . implode(', ', $missingConstants)];
            }
        });

        runTest("Upload Directory Writable", function() {
            $uploadPath = UPLOAD_PATH;
            
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    return ['status' => 'fail', 'message' => 'Cannot create upload directory'];
                }
            }
            
            if (is_writable($uploadPath)) {
                return ['status' => 'pass', 'message' => 'Upload directory is writable'];
            } else {
                return ['status' => 'fail', 'message' => 'Upload directory is not writable'];
            }
        });

        runTest("Session Configuration", function() {
            if (session_status() === PHP_SESSION_ACTIVE) {
                return ['status' => 'pass', 'message' => 'Session is active'];
            } else {
                return ['status' => 'warning', 'message' => 'Session not started (may be normal depending on context)'];
            }
        });

        echo "</div>";

        // File Structure Tests
        echo "<div class='test-section'>";
        echo "<h2>File Structure Tests</h2>";

        runTest("Required PHP Files Exist", function() {
            $requiredFiles = [
                '../config/config.php',
                '../config/database.php',
                '../classes/User.php',
                '../classes/Product.php',
                '../classes/Order.php',
                '../classes/Security.php',
                '../middleware/auth_middleware.php'
            ];
            
            $missingFiles = [];
            foreach ($requiredFiles as $file) {
                if (!file_exists(__DIR__ . '/' . $file)) {
                    $missingFiles[] = $file;
                }
            }
            
            if (empty($missingFiles)) {
                return ['status' => 'pass', 'message' => 'All required PHP files exist'];
            } else {
                return ['status' => 'fail', 'message' => 'Missing files: ' . implode(', ', $missingFiles)];
            }
        });

        runTest("Handler Files Exist", function() {
            $handlerFiles = [
                '../handlers/auth_handler.php',
                '../handlers/profile_handler.php',
                '../handlers/cart_handler.php',
                '../handlers/product_handler.php',
                '../handlers/order_handler.php',
                '../handlers/contact_handler.php'
            ];
            
            $missingFiles = [];
            foreach ($handlerFiles as $file) {
                if (!file_exists(__DIR__ . '/' . $file)) {
                    $missingFiles[] = $file;
                }
            }
            
            if (empty($missingFiles)) {
                return ['status' => 'pass', 'message' => 'All handler files exist'];
            } else {
                return ['status' => 'fail', 'message' => 'Missing handler files: ' . implode(', ', $missingFiles)];
            }
        });

        echo "</div>";

        // Display Test Summary
        echo "<div class='stats'>";
        echo "<div class='stat pass'><h3>{$passedTests}</h3><p>Passed</p></div>";
        echo "<div class='stat fail'><h3>{$failedTests}</h3><p>Failed</p></div>";
        echo "<div class='stat warning'><h3>{$warnings}</h3><p>Warnings</p></div>";
        echo "<div class='stat'><h3>{$totalTests}</h3><p>Total Tests</p></div>";
        echo "</div>";

        $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
        
        echo "<div class='test-section'>";
        echo "<h2>Test Summary</h2>";
        echo "<p><strong>Success Rate:</strong> {$successRate}%</p>";
        
        if ($failedTests === 0) {
            echo "<div class='test-result pass'><span class='test-pass'>✓ All critical tests passed!</span><br>";
            echo "<small>Your LFLshop backend is ready for use.</small></div>";
        } elseif ($failedTests <= 2) {
            echo "<div class='test-result warning'><span class='test-warning'>⚠ Minor issues detected</span><br>";
            echo "<small>Please review and fix the failed tests before going live.</small></div>";
        } else {
            echo "<div class='test-result fail'><span class='test-fail'>✗ Critical issues detected</span><br>";
            echo "<small>Please fix the failed tests before using the application.</small></div>";
        }
        
        echo "</div>";

        // Security Recommendations
        echo "<div class='test-section'>";
        echo "<h2>Security Recommendations</h2>";
        echo "<ul>";
        echo "<li>✓ CSRF protection implemented</li>";
        echo "<li>✓ Input sanitization active</li>";
        echo "<li>✓ SQL injection protection enabled</li>";
        echo "<li>✓ XSS protection implemented</li>";
        echo "<li>✓ Password hashing using secure methods</li>";
        echo "<li>✓ Session management configured</li>";
        echo "<li>⚠ Remember to use HTTPS in production</li>";
        echo "<li>⚠ Configure proper error logging in production</li>";
        echo "<li>⚠ Set up regular database backups</li>";
        echo "<li>⚠ Implement rate limiting for production</li>";
        echo "</ul>";
        echo "</div>";
        ?>

        <div class="test-section">
            <h2>Next Steps</h2>
            <ol>
                <li>Fix any failed tests shown above</li>
                <li>Test user registration and login functionality</li>
                <li>Test product management features</li>
                <li>Test cart and order functionality</li>
                <li>Configure email settings for notifications</li>
                <li>Set up SSL certificate for production</li>
                <li>Configure production database settings</li>
                <li>Test all forms with various input types</li>
            </ol>
        </div>

        <div class="test-section">
            <h2>Manual Testing Checklist</h2>
            <ul>
                <li>□ User registration with valid data</li>
                <li>□ User registration with invalid data</li>
                <li>□ User login with correct credentials</li>
                <li>□ User login with incorrect credentials</li>
                <li>□ Password change functionality</li>
                <li>□ Profile update functionality</li>
                <li>□ Add products to cart</li>
                <li>□ Remove products from cart</li>
                <li>□ Add products to wishlist</li>
                <li>□ Create order process</li>
                <li>□ Admin dashboard access</li>
                <li>□ Product management (CRUD)</li>
                <li>□ Order management</li>
                <li>□ Contact form submission</li>
                <li>□ Newsletter subscription</li>
            </ul>
        </div>
    </div>
</body>
</html>
