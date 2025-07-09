<?php
/**
 * Wishlist Page for LFLshop
 * Basic wishlist functionality to resolve navigation link
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: html/signin.html');
    exit();
}

// Include necessary files
require_once 'php/config/config.php';
require_once 'database/config.php';

$db = new Database();
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - LFLshop</title>
    <meta name="description" content="Your saved products wishlist on LFLshop Ethiopian marketplace">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth-navigation.css">
    <link rel="stylesheet" href="css/mobile-optimization.css">
    <link rel="stylesheet" href="css/accessibility.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="Logo/LOCALS.png">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="html/index.html">
                    <img src="Logo/LOCALS.png" alt="LFLshop Logo" class="logo">
                    <span class="brand-text">LFLshop</span>
                </a>
            </div>
            
            <div class="nav-menu">
                <a href="html/index.html" class="nav-link">Home</a>
                <a href="html/collections.html" class="nav-link">Collections</a>
                <a href="html/cart.html" class="nav-link">Cart</a>
                <a href="wishlist.php" class="nav-link active">Wishlist</a>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
            </div>
            
            <div class="nav-icons">
                <a href="html/cart.html" class="nav-icon" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="notification-count" id="cart-count">0</span>
                </a>
                <a href="wishlist.php" class="nav-icon active" title="Wishlist">
                    <i class="fas fa-heart"></i>
                    <span class="notification-count" id="wishlist-count">0</span>
                </a>
                <div class="user-menu">
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                    <a href="api/auth.php?action=logout" class="nav-link">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-heart"></i> My Wishlist</h1>
                <p>Save your favorite Ethiopian products for later</p>
            </div>

            <!-- Wishlist Content -->
            <div class="wishlist-container">
                <div class="wishlist-empty" id="wishlist-empty">
                    <div class="empty-state">
                        <i class="fas fa-heart-broken"></i>
                        <h2>Your wishlist is empty</h2>
                        <p>Start adding products you love to your wishlist!</p>
                        <a href="html/collections.html" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Browse Products
                        </a>
                    </div>
                </div>

                <div class="wishlist-items" id="wishlist-items" style="display: none;">
                    <!-- Wishlist items will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>LFLshop</h3>
                    <p>Supporting Ethiopian creators and preserving cultural heritage through e-commerce.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="html/index.html">Home</a></li>
                        <li><a href="html/collections.html">Collections</a></li>
                        <li><a href="html/customer-contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="orders.php">My Orders</a></li>
                        <li><a href="account-settings.php">Settings</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 LFLshop. Supporting Ethiopian creators. 🇪🇹</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="javascript/config.js"></script>
    <script src="javascript/auth.js"></script>
    <script>
        // Basic wishlist functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize wishlist
            loadWishlist();
            
            // Update cart count
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            }
        });

        async function loadWishlist() {
            try {
                // For now, show empty state
                // In future implementation, this would load from database
                document.getElementById('wishlist-empty').style.display = 'block';
                document.getElementById('wishlist-items').style.display = 'none';
                document.getElementById('wishlist-count').textContent = '0';
            } catch (error) {
                console.error('Error loading wishlist:', error);
            }
        }

        // Placeholder function for adding to wishlist
        function addToWishlist(productId) {
            // Future implementation would save to database
            console.log('Add to wishlist:', productId);
            showNotification('Product added to wishlist!', 'success');
        }

        // Placeholder function for removing from wishlist
        function removeFromWishlist(productId) {
            // Future implementation would remove from database
            console.log('Remove from wishlist:', productId);
            showNotification('Product removed from wishlist!', 'info');
        }

        // Simple notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 4px;
                color: white;
                z-index: 1000;
                background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>

    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .page-header i {
            color: #e74c3c;
            margin-right: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 2px dashed #dee2e6;
        }

        .empty-state i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #495057;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 30px;
        }

        .nav-link.active {
            color: #e74c3c;
            font-weight: 600;
        }

        .nav-icon.active {
            color: #e74c3c;
        }

        @media (max-width: 768px) {
            .wishlist-container {
                padding: 10px;
            }
            
            .empty-state {
                padding: 40px 15px;
            }
            
            .empty-state i {
                font-size: 3rem;
            }
        }
    </style>
</body>
</html>
