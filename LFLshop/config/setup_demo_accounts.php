<?php
/**
 * Setup Demo Accounts for LFLshop
 * Creates demo customer, seller, and admin accounts
 */

require_once 'database/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Demo Accounts - LFLshop</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #c3e6cb; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #f5c6cb; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #bee5eb; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #ffeaa7; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .demo-credentials { background: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3498db; }
        .demo-credentials h3 { margin-top: 0; color: #2980b9; }
        .demo-credentials code { background: #fff; padding: 4px 8px; border-radius: 4px; font-family: monospace; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🇪🇹 LFLshop Demo Accounts Setup</h1>";

try {
    $db = new Database();
    
    echo "<div class='info'>🔄 Setting up demo accounts for LFLshop Ethiopian marketplace...</div>";
    
    // Check if users table exists
    $db->query("SHOW TABLES LIKE 'users'");
    $tableExists = $db->single();
    
    if (!$tableExists) {
        echo "<div class='error'>❌ Users table does not exist. Please run database setup first.</div>";
        echo "<a href='setup_database.php' class='btn'>Setup Database</a>";
        exit;
    }
    
    // Check existing demo accounts
    $db->query("SELECT email, user_type FROM users WHERE email IN ('customer@demo.com', 'seller@demo.com', 'admin@demo.com')");
    $existingAccounts = $db->resultSet();
    
    if (count($existingAccounts) > 0) {
        echo "<div class='warning'>⚠️ Some demo accounts already exist:</div>";
        foreach ($existingAccounts as $account) {
            echo "<div class='info'>📧 {$account['email']} ({$account['user_type']})</div>";
        }
        echo "<div class='info'>🔄 Updating existing accounts with fresh passwords...</div>";
        
        // Update existing accounts
        $demoAccounts = [
            ['customer@demo.com', 'Demo Customer', 'password', 'customer'],
            ['seller@demo.com', 'Demo Seller', 'password', 'seller'],
            ['admin@demo.com', 'Demo Admin', 'admin123', 'admin']
        ];
        
        foreach ($demoAccounts as $account) {
            $hashedPassword = password_hash($account[2], PASSWORD_DEFAULT);
            
            $db->query("
                UPDATE users 
                SET name = :name, password = :password, is_active = 1, email_verified = 1, updated_at = NOW()
                WHERE email = :email
            ");
            $db->bind(':email', $account[0]);
            $db->bind(':name', $account[1]);
            $db->bind(':password', $hashedPassword);
            
            if ($db->execute()) {
                echo "<div class='success'>✅ Updated {$account[3]} account: {$account[0]}</div>";
            } else {
                echo "<div class='error'>❌ Failed to update {$account[3]} account: {$account[0]}</div>";
            }
        }
    } else {
        echo "<div class='info'>🆕 Creating new demo accounts...</div>";
        
        // Create new demo accounts
        $demoAccounts = [
            ['customer@demo.com', 'Demo Customer', 'password', 'customer', '+251911234567', 'Bole District, Addis Ababa', 'Addis Ababa'],
            ['seller@demo.com', 'Demo Seller', 'password', 'seller', '+251922345678', 'Piassa, Addis Ababa', 'Addis Ababa'],
            ['admin@demo.com', 'Demo Admin', 'admin123', 'admin', '+251933456789', 'Kirkos, Addis Ababa', 'Addis Ababa']
        ];
        
        foreach ($demoAccounts as $account) {
            $hashedPassword = password_hash($account[2], PASSWORD_DEFAULT);
            
            $db->query("
                INSERT INTO users (email, name, password, user_type, phone, address, city, is_active, email_verified, created_at, updated_at)
                VALUES (:email, :name, :password, :user_type, :phone, :address, :city, 1, 1, NOW(), NOW())
            ");
            $db->bind(':email', $account[0]);
            $db->bind(':name', $account[1]);
            $db->bind(':password', $hashedPassword);
            $db->bind(':user_type', $account[3]);
            $db->bind(':phone', $account[4]);
            $db->bind(':address', $account[5]);
            $db->bind(':city', $account[6]);
            
            if ($db->execute()) {
                echo "<div class='success'>✅ Created {$account[3]} account: {$account[0]}</div>";
            } else {
                echo "<div class='error'>❌ Failed to create {$account[3]} account: {$account[0]}</div>";
            }
        }
    }
    
    // Verify all accounts exist and are active
    echo "<h2>🔍 Account Verification</h2>";
    $db->query("SELECT email, name, user_type, is_active, email_verified FROM users WHERE email IN ('customer@demo.com', 'seller@demo.com', 'admin@demo.com') ORDER BY user_type");
    $verifiedAccounts = $db->resultSet();
    
    if (count($verifiedAccounts) === 3) {
        echo "<div class='success'>✅ All 3 demo accounts verified successfully!</div>";
        
        foreach ($verifiedAccounts as $account) {
            $status = ($account['is_active'] && $account['email_verified']) ? '✅ Active' : '❌ Inactive';
            echo "<div class='info'>👤 {$account['name']} ({$account['user_type']}) - {$account['email']} - {$status}</div>";
        }
    } else {
        echo "<div class='error'>❌ Only " . count($verifiedAccounts) . " accounts found. Expected 3.</div>";
    }
    
    // Test authentication for each account
    echo "<h2>🔐 Authentication Test</h2>";
    $testCredentials = [
        ['customer@demo.com', 'password'],
        ['seller@demo.com', 'password'],
        ['admin@demo.com', 'admin123']
    ];
    
    foreach ($testCredentials as $cred) {
        $db->query('SELECT id, name, email, user_type, password FROM users WHERE email = :email AND is_active = 1');
        $db->bind(':email', $cred[0]);
        $user = $db->single();
        
        if ($user && password_verify($cred[1], $user['password'])) {
            echo "<div class='success'>✅ Authentication test passed for {$cred[0]}</div>";
        } else {
            echo "<div class='error'>❌ Authentication test failed for {$cred[0]}</div>";
        }
    }
    
    echo "<div class='demo-credentials'>
        <h3>🔑 Demo Account Credentials</h3>
        <p><strong>Customer Account:</strong><br>
        Email: <code>customer@demo.com</code><br>
        Password: <code>password</code></p>
        
        <p><strong>Seller Account:</strong><br>
        Email: <code>seller@demo.com</code><br>
        Password: <code>password</code></p>
        
        <p><strong>Admin Account:</strong><br>
        Email: <code>admin@demo.com</code><br>
        Password: <code>admin123</code></p>
    </div>";
    
    echo "<div class='success'>
        <h2>🎉 Demo Accounts Setup Complete!</h2>
        <p>All demo accounts are ready for testing. You can now:</p>
        <ul>
            <li>Test customer login and shopping features</li>
            <li>Test seller login and product management</li>
            <li>Test admin login and system administration</li>
        </ul>
    </div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>
        <a href='html/index.html' class='btn'>🏠 Go to Homepage</a>
        <a href='html/signin.html' class='btn'>🔐 Test Sign In</a>
        <a href='test_demo_auth.php' class='btn'>🧪 Run Auth Tests</a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <h2>❌ Setup Failed!</h2>
        <p>Error: " . $e->getMessage() . "</p>
        <p>Please check your database configuration and try again.</p>
    </div>";
    
    echo "<div style='text-align: center; margin-top: 20px;'>
        <a href='setup_database.php' class='btn'>🔧 Setup Database</a>
    </div>";
}

echo "</div></body></html>";
?>