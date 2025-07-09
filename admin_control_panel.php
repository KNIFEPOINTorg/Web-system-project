<?php
/**
 * LFL Shop Admin Control Panel
 * Consolidated database and system management interface
 */

session_start();
require_once 'database/config.php';

// Secure admin authentication with hashed password
// Default admin password hash for 'admin123' - CHANGE THIS IN PRODUCTION
$admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT); // admin123
$is_authenticated = isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;

if (isset($_POST['admin_login'])) {
    $entered_password = $_POST['password'] ?? '';
    if (password_verify($entered_password, $admin_password_hash)) {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_login_time'] = time();
        $is_authenticated = true;

        // Log successful admin login
        error_log("Admin login successful from IP: " . $_SERVER['REMOTE_ADDR']);
    } else {
        $login_error = "Invalid password";
        // Log failed admin login attempt
        error_log("Admin login failed from IP: " . $_SERVER['REMOTE_ADDR']);
    }
}

// Check session timeout (30 minutes)
if ($is_authenticated && isset($_SESSION['admin_login_time'])) {
    if (time() - $_SESSION['admin_login_time'] > 1800) { // 30 minutes
        session_destroy();
        $is_authenticated = false;
        $login_error = "Session expired. Please login again.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_control_panel.php');
    exit;
}

if (!$is_authenticated) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - LFL Shop</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body { 
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0; 
                padding: 20px; 
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }
            
            .login-container { 
                max-width: 420px; 
                width: 100%;
                background: rgba(255, 255, 255, 0.95); 
                backdrop-filter: blur(20px);
                padding: 50px; 
                border-radius: 20px; 
                box-shadow: 0 25px 50px rgba(0,0,0,0.15);
                border: 1px solid rgba(255, 255, 255, 0.2);
                position: relative;
                overflow: hidden;
            }
            
            .login-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
                border-radius: 20px;
                pointer-events: none;
            }
            .form-group { 
                margin: 25px 0; 
                position: relative;
                z-index: 1;
            }
            label { 
                display: block; 
                margin-bottom: 10px; 
                font-weight: 600; 
                color: #2d3748;
                font-size: 15px;
                letter-spacing: 0.3px;
            }
            input[type="password"] { 
                width: 100%; 
                padding: 16px 20px; 
                border: 2px solid rgba(255, 255, 255, 0.3); 
                border-radius: 12px; 
                font-size: 16px;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            input[type="password"]:focus {
                outline: none;
                border-color: rgba(255, 255, 255, 0.8);
                background: rgba(255, 255, 255, 0.95);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                transform: translateY(-2px);
            }
            .btn { 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white; 
                padding: 16px 24px; 
                border: none; 
                border-radius: 12px; 
                cursor: pointer; 
                font-size: 16px; 
                font-weight: 600;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                transition: all 0.3s ease;
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
                position: relative;
                z-index: 1;
                overflow: hidden;
            }
            
            .btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: left 0.5s ease;
            }
            
            .btn:hover::before {
                left: 100%;
            }
            
            .btn:hover { 
                transform: translateY(-3px);
                box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
            }
            .error { 
                color: #dc2626; 
                margin: 20px 0; 
                padding: 16px 20px;
                background: rgba(254, 242, 242, 0.9);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                border: 1px solid rgba(254, 202, 202, 0.5);
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 10px;
                position: relative;
                z-index: 1;
                box-shadow: 0 4px 15px rgba(220, 38, 38, 0.1);
            }
            .header { 
                text-align: center; 
                margin-bottom: 40px;
                position: relative;
                z-index: 1;
            }
            .logo-container {
                margin-bottom: 25px;
                display: flex;
                justify-content: center;
            }
            .logo {
                width: 90px;
                height: 90px;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 15px 35px rgba(0,0,0,0.15);
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 12px;
                border: 2px solid rgba(255, 255, 255, 0.3);
                transition: all 0.3s ease;
            }
            
            .logo:hover {
                transform: translateY(-5px) scale(1.05);
                box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            }
            
            .logo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
            
            .header h1 {
                color: #1a202c;
                font-size: 28px;
                font-weight: 700;
                margin: 0 0 10px 0;
                letter-spacing: -0.5px;
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .header p {
                color: #4a5568;
                font-size: 16px;
                margin: 0;
                font-weight: 500;
                opacity: 0.8;
            }
            .default-password {
                margin-top: 25px; 
                text-align: center; 
                color: #4a5568; 
                font-size: 14px;
                padding: 16px 20px;
                background: rgba(249, 250, 251, 0.8);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                border: 1px solid rgba(229, 231, 235, 0.5);
                position: relative;
                z-index: 1;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            }
            
            .back-button {
                position: absolute;
                top: 30px;
                left: 30px;
                background: rgba(255, 255, 255, 0.25);
                backdrop-filter: blur(20px);
                color: white;
                padding: 16px 24px;
                border: none;
                border-radius: 20px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 12px;
                transition: all 0.3s ease;
                border: 2px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                z-index: 10;
                text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }
            
            .back-button::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%);
                border-radius: 20px;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .back-button:hover::before {
                opacity: 1;
            }
            
            .back-button:hover {
                background: rgba(255, 255, 255, 0.35);
                transform: translateY(-5px);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
                border-color: rgba(255, 255, 255, 0.5);
            }
            
            .back-button svg {
                transition: transform 0.3s ease;
            }
            
            .back-button:hover svg {
                transform: translateX(-4px);
            }
            
            /* Add responsive design */
            @media (max-width: 768px) {
                .login-container {
                    margin: 20px;
                    padding: 30px 25px;
                }
                
                .back-button {
                    top: 20px;
                    left: 20px;
                    padding: 12px 18px;
                    font-size: 13px;
                }
                
                .header h1 {
                    font-size: 24px;
                }
                
                .logo {
                    width: 70px;
                    height: 70px;
                }
            }
        </style>
    </head>
    <body>
        <!-- Back Button -->
        <a href="login.php" class="back-button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back to Sign In
        </a>

        <div class="login-container">
            <div class="header">
                <div class="logo-container">
                    <div class="logo">
                        <img src="Logo/LOCALS.png" alt="LFLshop Logo">
                    </div>
                </div>
                <h1>Admin Access</h1>
                <p>LFL Shop Control Panel</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">Admin Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Enter admin password">
                </div>
                <button type="submit" name="admin_login" class="btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Access Admin Panel
                </button>
            </form>
            
            <div class="default-password">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px; vertical-align: middle;">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 16V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 8H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <strong>Default Password:</strong> admin123
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle AJAX requests for admin functions
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        $db = new Database();
        
        switch ($_GET['action']) {
            case 'get_users':
                $db->query('SELECT id, name, email, user_type, is_active, created_at FROM users ORDER BY id');
                $users = $db->resultset();
                echo json_encode(['success' => true, 'data' => $users]);
                break;
                
            case 'test_auth':
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                $db->query('SELECT * FROM users WHERE email = :email AND is_active = 1');
                $db->bind(':email', $email);
                $user = $db->single();
                
                if ($user && password_verify($password, $user['password'])) {
                    unset($user['password']);
                    echo json_encode(['success' => true, 'message' => 'Authentication successful', 'user' => $user]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Authentication failed']);
                }
                break;
                
            case 'create_demo_accounts':
                // Drop and recreate users table
                $db->query("DROP TABLE IF EXISTS users");
                $db->execute();
                
                $db->query("
                    CREATE TABLE users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        user_type ENUM('customer', 'seller', 'admin') DEFAULT 'customer',
                        phone VARCHAR(20),
                        address TEXT,
                        city VARCHAR(100),
                        country VARCHAR(100) DEFAULT 'Ethiopia',
                        is_active BOOLEAN DEFAULT TRUE,
                        email_verified BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_email (email),
                        INDEX idx_user_type (user_type),
                        INDEX idx_active (is_active)
                    )
                ");
                $db->execute();
                
                // Create demo accounts
                $accounts = [
                    ['Demo Customer', 'customer@demo.com', 'password', 'customer'],
                    ['Demo Seller', 'seller@demo.com', 'password', 'seller'],
                    ['Admin User', 'admin@demo.com', 'admin123', 'admin']
                ];
                
                foreach ($accounts as $account) {
                    $hashedPassword = password_hash($account[2], PASSWORD_DEFAULT);
                    $db->query("
                        INSERT INTO users (name, email, password, user_type, is_active, email_verified) 
                        VALUES (:name, :email, :password, :user_type, 1, 1)
                    ");
                    $db->bind(':name', $account[0]);
                    $db->bind(':email', $account[1]);
                    $db->bind(':password', $hashedPassword);
                    $db->bind(':user_type', $account[3]);
                    $db->execute();
                }
                
                echo json_encode(['success' => true, 'message' => 'Demo accounts created successfully']);
                break;
                
            case 'system_status':
                $status = [];
                
                // Check database connection
                try {
                    $db->query("SELECT 1");
                    $status['database'] = 'Connected';
                } catch (Exception $e) {
                    $status['database'] = 'Failed: ' . $e->getMessage();
                }
                
                // Check tables
                $tables = ['users', 'products', 'categories', 'orders', 'cart_items'];
                $status['tables'] = [];
                foreach ($tables as $table) {
                    try {
                        $db->query("SHOW TABLES LIKE '$table'");
                        $result = $db->single();
                        $status['tables'][$table] = $result ? 'Exists' : 'Missing';
                    } catch (Exception $e) {
                        $status['tables'][$table] = 'Error';
                    }
                }
                
                echo json_encode(['success' => true, 'data' => $status]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel - LFL Shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .header { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(10px);
            color: white; 
            padding: 20px 0; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .header-content { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 0 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .header h1 { 
            font-size: 28px; 
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .logout-btn { 
            background: rgba(255,255,255,0.2); 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 30px 20px; 
        }
        
        .dashboard-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
            gap: 25px; 
            margin-bottom: 40px; 
        }
        
        .card { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px; 
            padding: 25px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        .card h3 { 
            margin-bottom: 20px; 
            color: #2d3748; 
            border-bottom: 2px solid #e2e8f0; 
            padding-bottom: 12px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .btn { 
            background: #4299e1; 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            margin: 5px; 
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn:hover { 
            background: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 153, 225, 0.3);
        }
        .btn-success { background: #48bb78; }
        .btn-success:hover { background: #38a169; box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3); }
        .btn-warning { background: #ed8936; color: white; }
        .btn-warning:hover { background: #dd6b20; box-shadow: 0 4px 12px rgba(237, 137, 54, 0.3); }
        .btn-danger { background: #f56565; }
        .btn-danger:hover { background: #e53e3e; box-shadow: 0 4px 12px rgba(245, 101, 101, 0.3); }
        
        .status-indicator { 
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 8px;
        }
        .status-online { 
            background: #c6f6d5; 
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .status-offline { 
            background: #fed7d7; 
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        .status-warning { 
            background: #fefcbf; 
            color: #744210;
            border: 1px solid #f6e05e;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }
        
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .table th, .table td { 
            padding: 16px; 
            text-align: left; 
            border-bottom: 1px solid #e2e8f0; 
        }
        .table th { 
            background: #f7fafc; 
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table tr:hover { 
            background: #f7fafc; 
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-details h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .user-details p {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: #718096;
        }
        
        .user-type-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-customer { background: #bee3f8; color: #2b6cb0; }
        .badge-seller { background: #c6f6d5; color: #22543d; }
        .badge-admin { background: #fed7d7; color: #c53030; }
        
        .form-group { margin: 20px 0; }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #4a5568;
        }
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid #e2e8f0; 
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
        
        .alert { 
            padding: 16px 20px; 
            margin: 20px 0; 
            border-radius: 12px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success { 
            background: #f0fff4; 
            color: #22543d; 
            border-left-color: #48bb78;
        }
        .alert-danger { 
            background: #fff5f5; 
            color: #742a2a; 
            border-left-color: #f56565;
        }
        .alert-info { 
            background: #ebf8ff; 
            color: #2c5282; 
            border-left-color: #4299e1;
        }
        
        .loading { 
            display: none; 
            text-align: center; 
            padding: 40px; 
        }
        .spinner { 
            border: 4px solid #e2e8f0; 
            border-top: 4px solid #4299e1; 
            border-radius: 50%; 
            width: 40px; 
            height: 40px; 
            animation: spin 1s linear infinite; 
            margin: 0 auto 16px; 
        }
        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }
        
        .quick-actions { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 12px; 
            margin: 20px 0; 
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #4299e1;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }
        
        .users-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            .container {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>
                <div class="header-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                LFL Shop Admin Control Panel
            </h1>
            <a href="?logout=1" class="logout-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Logout
            </a>
        </div>
    </div>

    <div class="container">
        <!-- User Statistics -->
        <div class="stats-grid" id="user-stats">
            <div class="stat-card">
                <div class="stat-number" id="total-users">0</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-customers">0</div>
                <div class="stat-label">Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-sellers">0</div>
                <div class="stat-label">Sellers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-admins">0</div>
                <div class="stat-label">Admins</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- System Status -->
            <div class="card">
                <h3>
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 12H18L15 21L9 3L6 12H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    System Status
                </h3>
                <div id="system-status">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Checking system status...</p>
                    </div>
                </div>
                <button class="btn" onclick="checkSystemStatus()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 4V10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Refresh Status
                </button>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h3>
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    Quick Actions
                </h3>
                <div class="quick-actions">
                    <button class="btn btn-success" onclick="createDemoAccounts()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 8V14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 11H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Create Demo Accounts
                    </button>
                    <button class="btn btn-warning" onclick="loadUsers()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 21V19C23 18.1645 22.7155 17.3541 22.2094 16.6977C21.7033 16.0414 20.9999 15.5766 20.2 15.3727" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 3.13A4 4 0 0 1 16 11.87" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        View Users
                    </button>
                </div>
                <div id="action-result"></div>
            </div>

            <!-- Authentication Tester -->
            <div class="card">
                <h3>
                    <div class="card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="16" r="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    Authentication Tester
                </h3>
                <form id="auth-test-form">
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" id="test-email" value="customer@demo.com">
                    </div>
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" id="test-password" value="password">
                    </div>
                    <button type="submit" class="btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Test Authentication
                    </button>
                </form>
                <div id="auth-result"></div>
            </div>
        </div>

        <!-- Users Management -->
        <div class="users-section">
            <h3>
                <div class="card-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 4H18C18.5304 4 19.0391 4.21071 19.4142 4.58579C19.7893 4.96086 20 5.46957 20 6V20C20 20.5304 19.7893 21.0391 19.4142 21.4142C19.0391 21.7893 18.5304 22 18 22H6C5.46957 22 4.96086 21.7893 4.58579 21.4142C4.21071 21.0391 4 20.5304 4 20V6C4 5.46957 4.21071 4.96086 4.58579 4.58579C4.96086 4.21071 5.46957 4 6 4H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                Users Management
            </h3>
            <button class="btn" onclick="loadUsers()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 4V10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 20V14H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14L18.36 18.36A9 9 0 0 1 3.51 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Load Users
            </button>
            <div id="users-table"></div>
        </div>
    </div>

    <script>
        // System Status Check with enhanced display
        function checkSystemStatus() {
            const statusDiv = document.getElementById('system-status');
            statusDiv.innerHTML = '<div class="loading"><div class="spinner"></div><p>Checking system status...</p></div>';
            
            fetch('?action=system_status')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div style="margin: 20px 0;">';
                        
                        // Database status
                        const dbStatus = data.data.database === 'Connected' ? 'online' : 'offline';
                        html += `<div style="margin-bottom: 15px;">
                            <span class="status-indicator status-${dbStatus}">
                                <div class="status-dot"></div>
                                Database: ${data.data.database}
                            </span>
                        </div>`;
                        
                        // Tables status
                        html += '<div style="margin-top: 20px;"><strong style="color: #4a5568; font-size: 14px;">Database Tables:</strong></div>';
                        html += '<div style="margin-top: 10px; display: grid; gap: 8px;">';
                        for (const [table, status] of Object.entries(data.data.tables)) {
                            const tableStatus = status === 'Exists' ? 'online' : 'offline';
                            html += `<div style="display: flex; align-items: center; padding: 8px 12px; background: #f7fafc; border-radius: 6px;">
                                <span class="status-indicator status-${tableStatus}" style="margin-right: 12px;">
                                    <div class="status-dot"></div>
                                    ${table}
                                </span>
                                <span style="margin-left: auto; font-size: 12px; color: #718096;">${status}</span>
                            </div>`;
                        }
                        html += '</div>';
                        
                        html += '</div>';
                        statusDiv.innerHTML = html;
                    } else {
                        statusDiv.innerHTML = `<div class="alert alert-danger">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Error: ${data.message}
                        </div>`;
                    }
                })
                .catch(error => {
                    statusDiv.innerHTML = `<div class="alert alert-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Error: ${error.message}
                    </div>`;
                });
        }

        // Create Demo Accounts
        function createDemoAccounts() {
            if (!confirm('This will recreate the users table and demo accounts. Continue?')) return;
            
            const resultDiv = document.getElementById('action-result');
            resultDiv.innerHTML = '<div class="loading"><div class="spinner"></div><p>Creating accounts...</p></div>';
            
            fetch('?action=create_demo_accounts', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        loadUsers();
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                });
        }

        // Load Users with enhanced display
        function loadUsers() {
            const tableDiv = document.getElementById('users-table');
            tableDiv.innerHTML = '<div class="loading"><div class="spinner"></div><p>Loading users...</p></div>';
            
            fetch('?action=get_users')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateUserStats(data.data);
                        
                        let html = '<table class="table"><thead><tr><th>User</th><th>Contact</th><th>Type</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead><tbody>';
                        
                        data.data.forEach(user => {
                            const initials = user.name.split(' ').map(n => n[0]).join('').toUpperCase();
                            const joinDate = new Date(user.created_at).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                            
                            html += `<tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">${initials}</div>
                                        <div class="user-details">
                                            <h4>${user.name}</h4>
                                            <p>ID: ${user.id}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>${user.email}</strong><br>
                                        <small style="color: #718096;">${user.phone || 'No phone'}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="user-type-badge badge-${user.user_type}">${user.user_type}</span>
                                </td>
                                <td>
                                    <span class="status-indicator status-${user.is_active ? 'online' : 'offline'}">
                                        <div class="status-dot"></div>
                                        ${user.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td>${joinDate}</td>
                                <td>
                                    <button class="btn btn-sm" onclick="viewUserDetails(${user.id})" style="padding: 6px 12px; font-size: 12px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        View
                                    </button>
                                </td>
                            </tr>`;
                        });
                        
                        html += '</tbody></table>';
                        tableDiv.innerHTML = html;
                    } else {
                        tableDiv.innerHTML = `<div class="alert alert-danger">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Error: ${data.message}
                        </div>`;
                    }
                })
                .catch(error => {
                    tableDiv.innerHTML = `<div class="alert alert-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Error: ${error.message}
                    </div>`;
                });
        }

        // Update user statistics
        function updateUserStats(users) {
            const totalUsers = users.length;
            const customers = users.filter(u => u.user_type === 'customer').length;
            const sellers = users.filter(u => u.user_type === 'seller').length;
            const admins = users.filter(u => u.user_type === 'admin').length;

            document.getElementById('total-users').textContent = totalUsers;
            document.getElementById('total-customers').textContent = customers;
            document.getElementById('total-sellers').textContent = sellers;
            document.getElementById('total-admins').textContent = admins;
        }

        // View user details
        function viewUserDetails(userId) {
            alert(`User details for ID: ${userId}\n\nThis feature can be expanded to show detailed user information, edit capabilities, and user management actions.`);
        }

        // Test Authentication
        document.getElementById('auth-test-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('test-email').value;
            const password = document.getElementById('test-password').value;
            const resultDiv = document.getElementById('auth-result');
            
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            
            fetch('?action=test_auth', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>✅ Authentication Successful</strong><br>
                                User: ${data.user.name} (${data.user.user_type})<br>
                                Email: ${data.user.email}
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkSystemStatus();
            loadUsers();
        });
    </script>
</body>
</html>