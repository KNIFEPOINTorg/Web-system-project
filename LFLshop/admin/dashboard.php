<?php
/**
 * Admin Dashboard - LFLshop
 * Main admin interface for managing the application
 */

require_once '../php/middleware/auth_middleware.php';
requireAdmin();

$user = new User();
$order = new Order();
$product = new Product();

// Get dashboard statistics
$db = getDB();

// Total users
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE status = 'active'");
$stmt->execute();
$totalUsers = $stmt->fetchColumn();

// Total products
$stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE status = 'published'");
$stmt->execute();
$totalProducts = $stmt->fetchColumn();

// Total orders
$stmt = $db->prepare("SELECT COUNT(*) FROM orders");
$stmt->execute();
$totalOrders = $stmt->fetchColumn();

// Total revenue
$stmt = $db->prepare("SELECT SUM(total_amount) FROM orders WHERE status IN ('processing', 'shipped', 'delivered')");
$stmt->execute();
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Recent orders
$stmt = $db->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recentOrders = $stmt->fetchAll();

// Recent users
$stmt = $db->prepare("
    SELECT id, username, email, first_name, last_name, created_at, status 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recentUsers = $stmt->fetchAll();

// Low stock products
$stmt = $db->prepare("
    SELECT id, name, stock_quantity, sku 
    FROM products 
    WHERE stock_quantity <= 10 AND status = 'published' 
    ORDER BY stock_quantity ASC 
    LIMIT 10
");
$stmt->execute();
$lowStockProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/auth-navigation.css">
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="../dashboard/styles.css">
</head>
<body class="admin-dashboard">
    <!-- Admin Navigation -->
    <nav class="navbar admin-navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index.php" class="logo">
                    <img src="../images/logo.png" alt="LFLshop" height="40">
                    <span class="admin-badge">Admin</span>
                </a>
            </div>
            
            <div class="nav-center">
                <div class="admin-nav-links">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                    <a href="products.php" class="nav-link">
                        <i class="fas fa-box"></i>
                        Products
                    </a>
                    <a href="orders.php" class="nav-link">
                        <i class="fas fa-shopping-bag"></i>
                        Orders
                    </a>
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Users
                    </a>
                    <a href="categories.php" class="nav-link">
                        <i class="fas fa-tags"></i>
                        Categories
                    </a>
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                </div>
            </div>
            
            <div class="nav-right">
                <!-- User Menu -->
                <div class="user-menu">
                    <button class="user-menu-toggle">
                        <span><?php echo h($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="user-dropdown">
                        <a href="../dashboard.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            User Dashboard
                        </a>
                        <a href="../account-settings.php" class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            Account Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item logout-btn" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Admin Dashboard</h1>
                <p class="text-secondary">Manage your LFLshop application</p>
            </div>

            <!-- Dashboard Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($totalUsers); ?></h3>
                        <p>Total Users</p>
                        <span class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            +12% this month
                        </span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($totalProducts); ?></h3>
                        <p>Total Products</p>
                        <span class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            +8% this month
                        </span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($totalOrders); ?></h3>
                        <p>Total Orders</p>
                        <span class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            +15% this month
                        </span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo formatCurrency($totalRevenue); ?></h3>
                        <p>Total Revenue</p>
                        <span class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            +22% this month
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Orders -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Recent Orders</h2>
                        <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentOrders)): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $orderItem): ?>
                                            <tr>
                                                <td>
                                                    <a href="order-details.php?id=<?php echo $orderItem['id']; ?>" class="order-link">
                                                        #<?php echo h($orderItem['order_number']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="customer-info">
                                                        <strong><?php echo h($orderItem['first_name'] . ' ' . $orderItem['last_name']); ?></strong>
                                                        <small><?php echo h($orderItem['email']); ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo formatCurrency($orderItem['total_amount']); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $orderItem['status']; ?>">
                                                        <?php echo ucfirst($orderItem['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($orderItem['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>No orders yet</h3>
                                <p>Orders will appear here once customers start purchasing</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Recent Users</h2>
                        <a href="users.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentUsers)): ?>
                            <div class="users-list">
                                <?php foreach ($recentUsers as $userItem): ?>
                                    <div class="user-item">
                                        <div class="user-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="user-info">
                                            <h4><?php echo h($userItem['first_name'] . ' ' . $userItem['last_name']); ?></h4>
                                            <p class="text-muted">@<?php echo h($userItem['username']); ?></p>
                                            <small><?php echo h($userItem['email']); ?></small>
                                        </div>
                                        <div class="user-meta">
                                            <span class="status-badge status-<?php echo $userItem['status']; ?>">
                                                <?php echo ucfirst($userItem['status']); ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($userItem['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h3>No users yet</h3>
                                <p>User registrations will appear here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Low Stock Alert</h2>
                        <a href="products.php?filter=low_stock" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($lowStockProducts)): ?>
                            <div class="stock-alerts">
                                <?php foreach ($lowStockProducts as $productItem): ?>
                                    <div class="stock-item">
                                        <div class="stock-info">
                                            <h4><?php echo h($productItem['name']); ?></h4>
                                            <?php if ($productItem['sku']): ?>
                                                <p class="text-muted">SKU: <?php echo h($productItem['sku']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="stock-quantity">
                                            <span class="quantity-badge <?php echo $productItem['stock_quantity'] <= 5 ? 'critical' : 'warning'; ?>">
                                                <?php echo $productItem['stock_quantity']; ?> left
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle text-success"></i>
                                <h3>All products in stock</h3>
                                <p>No low stock alerts at this time</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="actions-grid">
                            <a href="product-add.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Add Product</span>
                            </a>
                            <a href="orders.php?status=pending" class="action-btn">
                                <i class="fas fa-clock"></i>
                                <span>Pending Orders</span>
                            </a>
                            <a href="users.php?filter=new" class="action-btn">
                                <i class="fas fa-user-plus"></i>
                                <span>New Users</span>
                            </a>
                            <a href="reports.php" class="action-btn">
                                <i class="fas fa-chart-line"></i>
                                <span>View Reports</span>
                            </a>
                            <a href="categories.php" class="action-btn">
                                <i class="fas fa-tags"></i>
                                <span>Manage Categories</span>
                            </a>
                            <a href="settings.php" class="action-btn">
                                <i class="fas fa-cogs"></i>
                                <span>Settings</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('../php/handlers/auth_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || '../index.php';
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = '../index.php';
                });
            }
        }

        // Initialize user menu dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuToggle = document.querySelector('.user-menu-toggle');
            const userDropdown = document.querySelector('.user-dropdown');

            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    userDropdown.style.opacity = userDropdown.style.opacity === '1' ? '0' : '1';
                    userDropdown.style.visibility = userDropdown.style.visibility === 'visible' ? 'hidden' : 'visible';
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.style.opacity = '0';
                        userDropdown.style.visibility = 'hidden';
                    }
                });
            }
        });

        // Auto-refresh dashboard data every 30 seconds
        setInterval(function() {
            // Refresh stats without full page reload
            fetch('dashboard-stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stats here if needed
                    }
                })
                .catch(error => {
                    console.error('Stats refresh error:', error);
                });
        }, 30000);
    </script>
</body>
</html>
