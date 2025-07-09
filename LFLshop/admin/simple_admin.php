<?php
session_start();

// Simple admin login for testing
$correct_password = 'admin123';
$is_authenticated = isset($_SESSION['simple_admin_auth']) && $_SESSION['simple_admin_auth'] === true;

if (isset($_POST['login'])) {
    $entered_password = $_POST['password'] ?? '';
    
    if ($entered_password === $correct_password) {
        $_SESSION['simple_admin_auth'] = true;
        $is_authenticated = true;
        $success_message = "Login successful!";
    } else {
        $error_message = "Invalid password. Expected: " . $correct_password;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: simple_admin.php');
    exit;
}

if (!$is_authenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Simple Admin Login</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 400px; margin: 100px auto; padding: 20px; }
            .form-group { margin: 15px 0; }
            input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; }
            button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; cursor: pointer; }
            .error { color: red; margin: 10px 0; }
            .success { color: green; margin: 10px 0; }
        </style>
    </head>
    <body>
        <h2>Simple Admin Login</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success_message)): ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        
        <p><small>Password: admin123</small></p>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 15px; margin-bottom: 20px; }
        .card { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 5px; }
        .btn { padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Simple Admin Panel - Working!</h1>
        <a href="?logout=1" style="color: white;">Logout</a>
    </div>
    
    <div class="card">
        <h3>✅ Authentication Successful</h3>
        <p>You are now logged in as admin using password: <strong>admin123</strong></p>
        <p>This confirms the password is working correctly.</p>
    </div>
    
    <div class="card">
        <h3>🔗 Links</h3>
        <p><a href="admin_control_panel.php" class="btn">Try Main Admin Panel</a></p>
        <p><a href="../admin_login_test.php" class="btn">Password Test Page</a></p>
    </div>
    
    <div class="card">
        <h3>📋 Session Info</h3>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Auth Status:</strong> <?php echo $_SESSION['simple_admin_auth'] ? 'Authenticated' : 'Not Authenticated'; ?></p>
        <p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>