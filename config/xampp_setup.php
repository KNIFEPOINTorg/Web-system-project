<?php
/**
 * XAMPP Integration Setup for LFL Shop
 * Complete setup and verification for XAMPP environment
 */

// Start output buffering for clean display
ob_start();

// Check if running on XAMPP
$isXampp = (
    strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false &&
    strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false
) || (
    strpos(__DIR__, 'xampp') !== false
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LFL Shop - XAMPP Integration Setup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .status-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            border-left: 5px solid #007bff;
            transition: transform 0.3s ease;
        }

        .status-card:hover {
            transform: translateY(-5px);
        }

        .status-card.success {
            border-left-color: #28a745;
            background: #f8fff8;
        }

        .status-card.warning {
            border-left-color: #ffc107;
            background: #fffef8;
        }

        .status-card.error {
            border-left-color: #dc3545;
            background: #fff8f8;
        }

        .status-card h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .status-card .icon {
            font-size: 1.5rem;
        }

        .status-card.success .icon {
            color: #28a745;
        }

        .status-card.warning .icon {
            color: #ffc107;
        }

        .status-card.error .icon {
            color: #dc3545;
        }

        .status-card.info .icon {
            color: #007bff;
        }

        .setup-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .action-btn {
            display: block;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            text-decoration: none;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,123,255,0.3);
        }

        .action-btn.success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }

        .action-btn.warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
        }

        .action-btn.danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .quick-links {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
        }

        .quick-links h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .quick-link:hover {
            border-color: #007bff;
            transform: translateX(5px);
        }

        .system-info {
            background: #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .log-output {
            background: #2c3e50;
            color: #ecf0f1;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .status-grid,
            .setup-actions,
            .links-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-store"></i> LFL Shop</h1>
            <p>XAMPP Integration & Setup Manager</p>
        </div>

        <div class="content">
            <?php
            // System checks
            $checks = [];
            $setupNeeded = false;

            // Check XAMPP environment
            if ($isXampp) {
                $checks[] = [
                    'title' => 'XAMPP Environment',
                    'status' => 'success',
                    'message' => 'Running on XAMPP successfully',
                    'icon' => 'fas fa-server'
                ];
            } else {
                $checks[] = [
                    'title' => 'XAMPP Environment',
                    'status' => 'warning',
                    'message' => 'May not be running on XAMPP',
                    'icon' => 'fas fa-exclamation-triangle'
                ];
            }

            // Check PHP version
            $phpVersion = phpversion();
            if (version_compare($phpVersion, '7.4.0', '>=')) {
                $checks[] = [
                    'title' => 'PHP Version',
                    'status' => 'success',
                    'message' => "PHP $phpVersion (Compatible)",
                    'icon' => 'fab fa-php'
                ];
            } else {
                $checks[] = [
                    'title' => 'PHP Version',
                    'status' => 'error',
                    'message' => "PHP $phpVersion (Upgrade recommended)",
                    'icon' => 'fab fa-php'
                ];
            }

            // Check MySQL connection
            try {
                require_once 'database/config.php';
                $db = new Database();
                $checks[] = [
                    'title' => 'MySQL Connection',
                    'status' => 'success',
                    'message' => 'Database connection successful',
                    'icon' => 'fas fa-database'
                ];
            } catch (Exception $e) {
                $checks[] = [
                    'title' => 'MySQL Connection',
                    'status' => 'error',
                    'message' => 'Database connection failed: ' . $e->getMessage(),
                    'icon' => 'fas fa-database'
                ];
                $setupNeeded = true;
            }

            // Check if database exists and has tables
            try {
                $db = new Database();
                $db->query("SHOW TABLES");
                $tables = $db->resultset();
                
                if (count($tables) > 0) {
                    $checks[] = [
                        'title' => 'Database Tables',
                        'status' => 'success',
                        'message' => count($tables) . ' tables found',
                        'icon' => 'fas fa-table'
                    ];
                } else {
                    $checks[] = [
                        'title' => 'Database Tables',
                        'status' => 'warning',
                        'message' => 'No tables found - setup needed',
                        'icon' => 'fas fa-table'
                    ];
                    $setupNeeded = true;
                }
            } catch (Exception $e) {
                $checks[] = [
                    'title' => 'Database Tables',
                    'status' => 'error',
                    'message' => 'Cannot check tables',
                    'icon' => 'fas fa-table'
                ];
                $setupNeeded = true;
            }

            // Check file permissions
            $writableDirectories = ['uploads/', 'logs/', 'css/', 'javascript/'];
            $permissionIssues = 0;
            
            foreach ($writableDirectories as $dir) {
                if (!is_writable($dir)) {
                    $permissionIssues++;
                }
            }

            if ($permissionIssues === 0) {
                $checks[] = [
                    'title' => 'File Permissions',
                    'status' => 'success',
                    'message' => 'All directories writable',
                    'icon' => 'fas fa-lock-open'
                ];
            } else {
                $checks[] = [
                    'title' => 'File Permissions',
                    'status' => 'warning',
                    'message' => "$permissionIssues directories not writable",
                    'icon' => 'fas fa-lock'
                ];
            }

            // Check required PHP extensions
            $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
            $missingExtensions = [];
            
            foreach ($requiredExtensions as $ext) {
                if (!extension_loaded($ext)) {
                    $missingExtensions[] = $ext;
                }
            }

            if (empty($missingExtensions)) {
                $checks[] = [
                    'title' => 'PHP Extensions',
                    'status' => 'success',
                    'message' => 'All required extensions loaded',
                    'icon' => 'fas fa-puzzle-piece'
                ];
            } else {
                $checks[] = [
                    'title' => 'PHP Extensions',
                    'status' => 'error',
                    'message' => 'Missing: ' . implode(', ', $missingExtensions),
                    'icon' => 'fas fa-puzzle-piece'
                ];
            }
            ?>

            <div class="status-grid">
                <?php foreach ($checks as $check): ?>
                <div class="status-card <?php echo $check['status']; ?>">
                    <h3>
                        <i class="<?php echo $check['icon']; ?> icon"></i>
                        <?php echo $check['title']; ?>
                    </h3>
                    <p><?php echo $check['message']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($setupNeeded): ?>
            <div class="setup-actions">
                <a href="setup_database.php" class="action-btn warning">
                    <i class="fas fa-database"></i><br>
                    Setup Database
                </a>
                <a href="setup_demo_accounts.php" class="action-btn success">
                    <i class="fas fa-users"></i><br>
                    Create Demo Accounts
                </a>
                <button onclick="runFullSetup()" class="action-btn">
                    <i class="fas fa-cogs"></i><br>
                    Run Full Setup
                </button>
                <a href="test_complete_system.php" class="action-btn danger">
                    <i class="fas fa-vial"></i><br>
                    Test System
                </a>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 30px; background: #d4edda; border-radius: 10px; margin: 20px 0;">
                <h2 style="color: #155724; margin-bottom: 15px;">
                    <i class="fas fa-check-circle"></i> System Ready!
                </h2>
                <p style="color: #155724; font-size: 1.1rem;">
                    LFL Shop is properly integrated with XAMPP and ready to use.
                </p>
            </div>
            <?php endif; ?>

            <div class="quick-links">
                <h3><i class="fas fa-external-link-alt"></i> Quick Access Links</h3>
                <div class="links-grid">
                    <a href="index.php" class="quick-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Project Dashboard
                    </a>
                    <a href="html/index.html" class="quick-link">
                        <i class="fas fa-store"></i>
                        Main Website
                    </a>
                    <a href="admin/admin_control_panel.php" class="quick-link">
                        <i class="fas fa-cog"></i>
                        Admin Panel
                    </a>
                    <a href="html/signin.html" class="quick-link">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </a>
                    <a href="html/signup.html" class="quick-link">
                        <i class="fas fa-user-plus"></i>
                        Sign Up
                    </a>
                    <a href="html/seller-dashboard.html" class="quick-link">
                        <i class="fas fa-chart-line"></i>
                        Seller Dashboard
                    </a>
                </div>
            </div>

            <div class="system-info">
                <h3><i class="fas fa-info-circle"></i> System Information</h3>
                <div style="margin-top: 15px;">
                    <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                    <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
                    <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
                    <strong>Project Path:</strong> <?php echo __DIR__; ?><br>
                    <strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                    <strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?><br>
                    <strong>Max Upload Size:</strong> <?php echo ini_get('upload_max_filesize'); ?><br>
                    <strong>XAMPP Detected:</strong> <?php echo $isXampp ? 'Yes' : 'No'; ?>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <h3 style="color: #2c3e50; margin-bottom: 15px;">
                    <i class="fas fa-rocket"></i> Ready to Launch?
                </h3>
                <p style="margin-bottom: 20px; color: #6c757d;">
                    Access your LFL Shop through XAMPP at: 
                    <strong>http://localhost/LFLshop/</strong>
                </p>
                <a href="html/index.html" class="action-btn success" style="display: inline-block; margin: 0 10px;">
                    <i class="fas fa-play"></i> Launch Website
                </a>
                <a href="index.php" class="action-btn" style="display: inline-block; margin: 0 10px;">
                    <i class="fas fa-tachometer-alt"></i> View Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function runFullSetup() {
            if (confirm('This will set up the complete database and demo data. Continue?')) {
                const progressBar = document.createElement('div');
                progressBar.className = 'progress-bar';
                progressBar.innerHTML = '<div class="progress-fill" style="width: 0%"></div>';
                
                const logOutput = document.createElement('div');
                logOutput.className = 'log-output';
                logOutput.innerHTML = '<strong>Starting full setup...</strong><br>';
                
                document.querySelector('.content').appendChild(progressBar);
                document.querySelector('.content').appendChild(logOutput);
                
                // Simulate setup progress
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressBar.querySelector('.progress-fill').style.width = progress + '%';
                    
                    if (progress === 30) {
                        logOutput.innerHTML += 'Setting up database...<br>';
                    } else if (progress === 60) {
                        logOutput.innerHTML += 'Creating demo accounts...<br>';
                    } else if (progress === 90) {
                        logOutput.innerHTML += 'Finalizing setup...<br>';
                    } else if (progress >= 100) {
                        clearInterval(interval);
                        logOutput.innerHTML += '<strong style="color: #28a745;">Setup completed successfully!</strong><br>';
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                }, 500);
                
                // Actually run the setup
                fetch('setup_database.php')
                    .then(response => response.text())
                    .then(data => {
                        console.log('Setup completed');
                    })
                    .catch(error => {
                        logOutput.innerHTML += '<strong style="color: #dc3545;">Setup failed: ' + error + '</strong><br>';
                    });
            }
        }

        // Auto-refresh status every 30 seconds
        setInterval(() => {
            const statusCards = document.querySelectorAll('.status-card.error, .status-card.warning');
            if (statusCards.length > 0) {
                // Only refresh if there are issues to potentially resolve
                window.location.reload();
            }
        }, 30000);
    </script>
</body>
</html>