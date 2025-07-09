<?php
/**
 * Search Results Page - LFLshop
 * Displays search results for products
 */

require_once 'php/config/config.php';

$product = new Product();

// Get search parameters
$query = Security::sanitizeInput($_GET['q'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$limit = 12;
$category = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$minPrice = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$sortBy = Security::sanitizeInput($_GET['sort'] ?? 'relevance');

// Build filters
$filters = [];
if (!empty($query)) {
    $filters['search'] = $query;
}
if ($category) {
    $filters['category_id'] = $category;
}
if ($minPrice) {
    $filters['min_price'] = $minPrice;
}
if ($maxPrice) {
    $filters['max_price'] = $maxPrice;
}

// Get search results
$results = $product->getProducts($page, $limit, $filters);
$categories = $product->getCategories();

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$cartCount = 0;
$wishlistCount = 0;

if ($isLoggedIn) {
    $order = new Order();
    $cartItems = $order->getCartItems(getCurrentUserId());
    $cartCount = count($cartItems);
    
    $wishlistItems = $order->getWishlistItems(getCurrentUserId());
    $wishlistCount = count($wishlistItems);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($query) ? "Search: " . h($query) : "Search Products"; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth-navigation.css">
    <link rel="stylesheet" href="css/enhanced-search.css">
    <link rel="stylesheet" href="css/search-results.css">
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
                <!-- Enhanced Search Container -->
                <div class="search-container enhanced-search">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="globalSearchInput" 
                               placeholder="Search Ethiopian products..." 
                               class="search-bar"
                               value="<?php echo h($query); ?>"
                               autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                        <button class="search-clear" id="searchClear" style="<?php echo !empty($query) ? 'display: block;' : 'display: none;'; ?>">
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
                
                <!-- Navigation Icons -->
                <div class="nav-icons" style="<?php echo $isLoggedIn ? 'display: flex;' : 'display: none;'; ?>">
                    <a href="wishlist.php" class="nav-icon" title="Wishlist">
                        <i class="fas fa-heart"></i>
                        <?php if ($wishlistCount > 0): ?>
                            <span class="notification-count"><?php echo $wishlistCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="cart.php" class="nav-icon" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="notifications.php" class="nav-icon" title="Notifications">
                        <i class="fas fa-bell"></i>
                    </a>
                </div>
                
                <!-- User Menu or Auth Links -->
                <?php if ($isLoggedIn): ?>
                    <div class="user-menu">
                        <button class="user-menu-toggle">
                            <span><?php echo h($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
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
                <?php else: ?>
                    <div class="auth-links">
                        <a href="login.php">Sign In</a>
                        <span class="auth-divider">|</span>
                        <a href="register.php">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="search-results-main">
        <div class="container">
            <!-- Search Header -->
            <div class="search-header">
                <?php if (!empty($query)): ?>
                    <h1>Search Results for "<?php echo h($query); ?>"</h1>
                    <p class="search-meta">
                        <?php echo number_format($results['total']); ?> products found
                        <?php if ($results['total'] > 0): ?>
                            (Page <?php echo $page; ?> of <?php echo $results['total_pages']; ?>)
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <h1>Search Products</h1>
                    <p class="search-meta">Browse our collection of authentic Ethiopian products</p>
                <?php endif; ?>
            </div>

            <div class="search-content">
                <!-- Search Filters Sidebar -->
                <aside class="search-filters">
                    <div class="filter-section">
                        <h3>Filters</h3>
                        
                        <form id="searchFilters" method="GET">
                            <input type="hidden" name="q" value="<?php echo h($query); ?>">
                            
                            <!-- Categories -->
                            <div class="filter-group">
                                <h4>Categories</h4>
                                <div class="filter-options">
                                    <label class="filter-option">
                                        <input type="radio" name="category" value="" <?php echo !$category ? 'checked' : ''; ?>>
                                        <span>All Categories</span>
                                    </label>
                                    <?php foreach ($categories as $cat): ?>
                                        <label class="filter-option">
                                            <input type="radio" name="category" value="<?php echo $cat['id']; ?>" 
                                                   <?php echo $category == $cat['id'] ? 'checked' : ''; ?>>
                                            <span><?php echo h($cat['name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="filter-group">
                                <h4>Price Range</h4>
                                <div class="price-inputs">
                                    <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice; ?>" min="0" step="0.01">
                                    <span>to</span>
                                    <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice; ?>" min="0" step="0.01">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-full">Apply Filters</button>
                        </form>
                    </div>
                </aside>

                <!-- Search Results -->
                <div class="search-results">
                    <!-- Sort Options -->
                    <div class="results-header">
                        <div class="results-count">
                            <?php echo number_format($results['total']); ?> products
                        </div>
                        <div class="sort-options">
                            <label for="sortBy">Sort by:</label>
                            <select id="sortBy" name="sort">
                                <option value="relevance" <?php echo $sortBy === 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                                <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                            </select>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <?php if (!empty($results['products'])): ?>
                        <div class="products-grid">
                            <?php foreach ($results['products'] as $productItem): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <a href="product.php?slug=<?php echo h($productItem['slug']); ?>">
                                            <?php if ($productItem['primary_image']): ?>
                                                <img src="<?php echo h($productItem['primary_image']); ?>" alt="<?php echo h($productItem['name']); ?>">
                                            <?php else: ?>
                                                <div class="placeholder-image">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        
                                        <?php if ($isLoggedIn): ?>
                                            <div class="product-actions">
                                                <button class="action-btn wishlist-btn" onclick="toggleWishlist(<?php echo $productItem['id']; ?>)" title="Add to Wishlist">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                                <button class="action-btn cart-btn" onclick="addToCart(<?php echo $productItem['id']; ?>)" title="Add to Cart">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-info">
                                        <h3 class="product-name">
                                            <a href="product.php?slug=<?php echo h($productItem['slug']); ?>">
                                                <?php echo h($productItem['name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <div class="product-category">
                                            <?php echo h($productItem['category_name']); ?>
                                        </div>
                                        
                                        <div class="product-price">
                                            <?php if ($productItem['sale_price']): ?>
                                                <span class="sale-price"><?php echo formatCurrency($productItem['sale_price']); ?></span>
                                                <span class="original-price"><?php echo formatCurrency($productItem['price']); ?></span>
                                            <?php else: ?>
                                                <span class="current-price"><?php echo formatCurrency($productItem['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="product-seller">
                                            by <?php echo h($productItem['seller_first_name'] . ' ' . $productItem['seller_last_name']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($results['total_pages'] > 1): ?>
                            <div class="pagination">
                                <?php
                                $baseUrl = "search-results.php?q=" . urlencode($query);
                                if ($category) $baseUrl .= "&category=" . $category;
                                if ($minPrice) $baseUrl .= "&min_price=" . $minPrice;
                                if ($maxPrice) $baseUrl .= "&max_price=" . $maxPrice;
                                if ($sortBy !== 'relevance') $baseUrl .= "&sort=" . $sortBy;
                                ?>
                                
                                <?php if ($page > 1): ?>
                                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $page - 1; ?>" class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($results['total_pages'], $page + 2); $i++): ?>
                                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $i; ?>" 
                                       class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $results['total_pages']): ?>
                                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $page + 1; ?>" class="pagination-btn">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- No Results -->
                        <div class="no-results">
                            <div class="no-results-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3>No products found</h3>
                            <?php if (!empty($query)): ?>
                                <p>We couldn't find any products matching "<?php echo h($query); ?>"</p>
                                <div class="no-results-suggestions">
                                    <h4>Try:</h4>
                                    <ul>
                                        <li>Checking your spelling</li>
                                        <li>Using different keywords</li>
                                        <li>Browsing our categories</li>
                                        <li>Removing some filters</li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <p>Start searching to discover amazing Ethiopian products</p>
                            <?php endif; ?>
                            <a href="collections.php" class="btn btn-primary">Browse All Products</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="javascript/auth-state-manager.js"></script>
    <script src="javascript/enhanced-search.js"></script>
    <script>
        // Initialize search functionality
        document.addEventListener('DOMContentLoaded', function() {
            if (window.enhancedSearch) {
                window.enhancedSearch.init();
            }
            
            // Initialize user menu dropdown
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
                    const cartCounts = document.querySelectorAll('.cart-count');
                    cartCounts.forEach(count => {
                        count.textContent = data.cart_count;
                        count.style.display = data.cart_count > 0 ? 'block' : 'none';
                    });
                    
                    // Show success message
                    showNotification('Product added to cart!', 'success');
                } else {
                    showNotification(data.errors ? data.errors.join('\n') : 'Failed to add item to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Add to cart error:', error);
                showNotification('Failed to add item to cart', 'error');
            });
        }

        // Toggle wishlist function
        function toggleWishlist(productId) {
            fetch('php/handlers/cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_to_wishlist&product_id=${productId}&csrf_token=<?php echo generateCSRFToken(); ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update wishlist count
                    const wishlistCounts = document.querySelectorAll('.notification-count');
                    wishlistCounts.forEach(count => {
                        count.textContent = data.wishlist_count;
                        count.style.display = data.wishlist_count > 0 ? 'block' : 'none';
                    });
                    
                    showNotification('Product added to wishlist!', 'success');
                } else {
                    showNotification(data.errors ? data.errors.join('\n') : 'Failed to add item to wishlist', 'error');
                }
            })
            .catch(error => {
                console.error('Add to wishlist error:', error);
                showNotification('Failed to add item to wishlist', 'error');
            });
        }

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

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentNode.remove()">×</button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
