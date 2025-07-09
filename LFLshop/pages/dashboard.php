<?php
/**
 * User Dashboard - LFLshop
 * Main dashboard page for authenticated users
 */

require_once 'php/middleware/auth_middleware.php';
require_once 'php/helpers/currency_helper.php';
requireAuth();

$user = new User();
$order = new Order();
$product = new Product();

// Get user profile
$profile = $user->getUserById(getCurrentUserId());

// Get recent orders
$recentOrders = $order->getUserOrders(getCurrentUserId(), 1, 5);

// Get cart items count
$cartItems = $order->getCartItems(getCurrentUserId());
$cartCount = count($cartItems);

// Get wishlist items count
$wishlistItems = $order->getWishlistItems(getCurrentUserId());
$wishlistCount = count($wishlistItems);

// Get featured products
$featuredProducts = $product->getProducts(1, 4, ['featured' => true]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth-navigation.css">
    <link rel="stylesheet" href="css/search-enhanced.css">
    <link rel="stylesheet" href="css/enhanced-search.css">
    <link rel="stylesheet" href="dashboard/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="logo">
                    <img src="images/logo.png" alt="LFLshop" height="40">
                </a>
            </div>
            
            <div class="nav-right">
                <!-- Search Container -->
                <div class="search-container">
                    <input type="text" placeholder="Search Ethiopian products..." class="search-bar">
                    <i class="fas fa-search search-icon"></i>
                </div>
                
                <!-- Enhanced Search Container -->
                <div class="search-container enhanced-search">
                    <div class="search-input-wrapper">
                        <input type="text"
                               id="globalSearchInput"
                               placeholder="Search Ethiopian products..."
                               class="search-bar"
                               autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                        <button class="search-clear" id="searchClear" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Search Suggestions Dropdown -->
                    <div class="search-suggestions" id="searchSuggestions" style="display: none;">
                        <div class="suggestions-header">
                            <span>Search Suggestions</span>
                        </div>
                        <div class="suggestions-list" id="suggestionsList">
                            <!-- Dynamic suggestions will be inserted here -->
                        </div>
                        <div class="suggestions-footer">
                            <button class="view-all-results" id="viewAllResults">
                                <i class="fas fa-search"></i>
                                View all results
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation Icons (Always visible for authenticated users) -->
                <div class="nav-icons">
                    <a href="cart.php" class="nav-icon" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="wishlist.php" class="nav-icon" title="Wishlist">
                        <i class="fas fa-heart"></i>
                        <?php if ($wishlistCount > 0): ?>
                            <span class="notification-count"><?php echo $wishlistCount; ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="notifications.php" class="nav-icon" title="Notifications">
                        <i class="fas fa-bell"></i>
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="user-menu">
                    <button class="user-menu-toggle">
                        <span><?php echo h($profile['first_name'] . ' ' . $profile['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="user-dropdown">
                        <a href="dashboard.php" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="account-settings.php" class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            Account Settings
                        </a>
                        <a href="orders.php" class="dropdown-item">
                            <i class="fas fa-shopping-bag"></i>
                            My Orders
                        </a>
                        <?php if (isAdmin()): ?>
                            <div class="dropdown-divider"></div>
                            <a href="admin/dashboard.php" class="dropdown-item">
                                <i class="fas fa-cogs"></i>
                                Admin Panel
                            </a>
                        <?php endif; ?>
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
    <main class="dashboard-main">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Welcome back, <?php echo h($profile['first_name']); ?>!</h1>
                <p class="text-secondary">Here's what's happening with your account</p>
            </div>

            <!-- Dashboard Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $recentOrders['total']; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $cartCount; ?></h3>
                        <p>Cart Items</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $wishlistCount; ?></h3>
                        <p>Wishlist Items</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3>4.8</h3>
                        <p>Average Rating</p>
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
                        <?php if (!empty($recentOrders['orders'])): ?>
                            <div class="orders-list">
                                <?php foreach ($recentOrders['orders'] as $orderItem): ?>
                                    <div class="order-item">
                                        <div class="order-info">
                                            <h4>#<?php echo h($orderItem['order_number']); ?></h4>
                                            <p class="text-muted"><?php echo date('M j, Y', strtotime($orderItem['created_at'])); ?></p>
                                        </div>
                                        <div class="order-status">
                                            <span class="status-badge status-<?php echo $orderItem['status']; ?>">
                                                <?php echo ucfirst($orderItem['status']); ?>
                                            </span>
                                        </div>
                                        <div class="order-amount">
                                            <?php echo formatCurrency($orderItem['total_amount']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>No orders yet</h3>
                                <p>Start shopping to see your orders here</p>
                                <a href="collections.php" class="btn btn-primary">Browse Products</a>
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
                            <a href="collections.php" class="action-btn">
                                <i class="fas fa-shopping-bag"></i>
                                <span>Browse Products</span>
                            </a>
                            <a href="cart.php" class="action-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span>View Cart</span>
                            </a>
                            <a href="wishlist.php" class="action-btn">
                                <i class="fas fa-heart"></i>
                                <span>My Wishlist</span>
                            </a>
                            <a href="account-settings.php" class="action-btn">
                                <i class="fas fa-user-cog"></i>
                                <span>Account Settings</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Products -->
            <?php if (!empty($featuredProducts['products'])): ?>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Featured Products</h2>
                        <a href="collections.php?featured=1" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="products-grid">
                            <?php foreach ($featuredProducts['products'] as $productItem): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <?php if ($productItem['primary_image']): ?>
                                            <img src="<?php echo h($productItem['primary_image']); ?>" alt="<?php echo h($productItem['name']); ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3><?php echo h($productItem['name']); ?></h3>
                                        <p class="product-price">
                                            <?php if ($productItem['sale_price']): ?>
                                                <span class="sale-price"><?php echo formatCurrency($productItem['sale_price']); ?></span>
                                                <span class="original-price"><?php echo formatCurrency($productItem['price']); ?></span>
                                            <?php else: ?>
                                                <?php echo formatCurrency($productItem['price']); ?>
                                            <?php endif; ?>
                                        </p>
                                        <div class="product-actions">
                                            <a href="product.php?slug=<?php echo h($productItem['slug']); ?>" class="btn btn-primary btn-sm">View Details</a>
                                            <button class="btn btn-outline btn-sm" onclick="addToCart(<?php echo $productItem['id']; ?>)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script src="javascript/auth-state-manager.js"></script>
    <script src="javascript/enhanced-search.js"></script>
    <script src="javascript/auth-aware-navigation.js"></script>
    <script src="javascript/search-functionality.js"></script>
    <script>
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('php/handlers/auth_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || 'index.php';
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = 'index.php';
                });
            }
        }

        // Add to cart function
        function addToCart(productId) {
            fetch('php/handlers/cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_to_cart&product_id=${productId}&quantity=1&csrf_token=<?php echo generateCSRFToken(); ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    location.reload();
                } else {
                    alert(data.errors ? data.errors.join('\n') : 'Failed to add item to cart');
                }
            })
            .catch(error => {
                console.error('Add to cart error:', error);
                alert('Failed to add item to cart');
            });
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
    </script>
</body>
</html>
