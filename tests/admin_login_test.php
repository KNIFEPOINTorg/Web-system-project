<?php
session_start();

// Test admin login functionality
if (isset($_POST['test_login'])) {
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Testing Admin Login</h2>";
    echo "<p><strong>Entered Password:</strong> " . htmlspecialchars($password) . "</p>";
    
    // Test with direct password comparison
    if ($password === 'admin123') {
        echo "<p style='color: green;'>✅ Direct password match: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>❌ Direct password match: FAILED</p>";
    }
    
    // Test with password_hash and password_verify
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "<p><strong>Generated Hash:</strong> " . $hash . "</p>";
    
    if (password_verify($password, $hash)) {
        echo "<p style='color: green;'>✅ Password hash verification: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>❌ Password hash verification: FAILED</p>";
    }
    
    // Test with the old hash from admin panel
    $old_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    echo "<p><strong>Old Hash:</strong> " . $old_hash . "</p>";
    
    if (password_verify($password, $old_hash)) {
        echo "<p style='color: green;'>✅ Old hash verification: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>❌ Old hash verification: FAILED</p>";
    }
    
    echo "<hr>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        input[type="password"] { width: 200px; padding: 8px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Admin Login Test</h1>
    
    <form method="POST">
        <div class="form-group">
            <label>Test Password:</label>
            <input type="password" name="password" value="admin123">
        </div>
        <button type="submit" name="test_login">Test Login</button>
    </form>
    
    <hr>
    
    <h3>Quick Tests:</h3>
    <p><strong>Expected Password:</strong> admin123</p>
    
    <?php
    // Show some basic info
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Password Hash Algorithm:</strong> " . PASSWORD_DEFAULT . "</p>";
    
    // Test the hash generation
    $test_hash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "<p><strong>Fresh Hash for 'admin123':</strong> " . $test_hash . "</p>";
    echo "<p><strong>Verification Test:</strong> " . (password_verify('admin123', $test_hash) ? 'PASS' : 'FAIL') . "</p>";
    ?>
    
    <hr>
    <p><a href="admin/admin_control_panel.php">Go to Admin Control Panel</a></p>
</body>
</html>