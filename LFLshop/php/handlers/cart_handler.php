<?php
/**
 * Cart Handler for LFLshop
 * Handles cart and wishlist operations
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';
requireAuth();

// Set JSON response header
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add_to_cart':
        handleAddToCart();
        break;
    case 'update_cart':
        handleUpdateCart();
        break;
    case 'remove_from_cart':
        handleRemoveFromCart();
        break;
    case 'clear_cart':
        handleClearCart();
        break;
    case 'get_cart':
        handleGetCart();
        break;
    case 'add_to_wishlist':
        handleAddToWishlist();
        break;
    case 'remove_from_wishlist':
        handleRemoveFromWishlist();
        break;
    case 'get_wishlist':
        handleGetWishlist();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Invalid action']]);
        break;
}

/**
 * Add item to cart
 */
function handleAddToCart() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        // Validate input
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid product ID']]);
            return;
        }
        
        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid quantity']]);
            return;
        }
        
        // Add to cart
        $order = new Order();
        $result = $order->addToCart($userId, $productId, $quantity);
        
        if ($result['success']) {
            // Get updated cart count
            $cartItems = $order->getCartItems($userId);
            $cartCount = count($cartItems);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item added to cart',
                'cart_count' => $cartCount
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to add item to cart']]);
    }
}

/**
 * Update cart item quantity
 */
function handleUpdateCart() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        
        // Validate input
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid product ID']]);
            return;
        }
        
        // Update cart
        $order = new Order();
        $result = $order->updateCartItem($userId, $productId, $quantity);
        
        if ($result['success']) {
            // Get updated cart items
            $cartItems = $order->getCartItems($userId);
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $price = $item['sale_price'] ?: $item['price'];
                $subtotal += $price * $item['quantity'];
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => count($cartItems),
                'subtotal' => $subtotal
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Update cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to update cart']]);
    }
}

/**
 * Remove item from cart
 */
function handleRemoveFromCart() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        $productId = (int)($_POST['product_id'] ?? 0);
        
        // Validate input
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid product ID']]);
            return;
        }
        
        // Remove from cart
        $order = new Order();
        $result = $order->removeFromCart($userId, $productId);
        
        if ($result['success']) {
            // Get updated cart count
            $cartItems = $order->getCartItems($userId);
            $cartCount = count($cartItems);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => $cartCount
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Remove from cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to remove item from cart']]);
    }
}

/**
 * Clear entire cart
 */
function handleClearCart() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        
        // Clear cart
        $order = new Order();
        $result = $order->clearCart($userId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared',
                'cart_count' => 0
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'errors' => ['Failed to clear cart']]);
        }
        
    } catch (Exception $e) {
        error_log("Clear cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to clear cart']]);
    }
}

/**
 * Get cart items
 */
function handleGetCart() {
    try {
        $userId = getCurrentUserId();
        
        $order = new Order();
        $cartItems = $order->getCartItems($userId);
        
        // Calculate totals
        $subtotal = 0;
        $totalItems = 0;
        
        foreach ($cartItems as &$item) {
            $price = $item['sale_price'] ?: $item['price'];
            $item['item_total'] = $price * $item['quantity'];
            $subtotal += $item['item_total'];
            $totalItems += $item['quantity'];
        }
        
        echo json_encode([
            'success' => true,
            'cart_items' => $cartItems,
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
            'cart_count' => count($cartItems)
        ]);
        
    } catch (Exception $e) {
        error_log("Get cart error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get cart items']]);
    }
}

/**
 * Add item to wishlist
 */
function handleAddToWishlist() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        $productId = (int)($_POST['product_id'] ?? 0);
        
        // Validate input
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid product ID']]);
            return;
        }
        
        // Add to wishlist
        $order = new Order();
        $result = $order->addToWishlist($userId, $productId);
        
        if ($result['success']) {
            // Get updated wishlist count
            $wishlistItems = $order->getWishlistItems($userId);
            $wishlistCount = count($wishlistItems);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item added to wishlist',
                'wishlist_count' => $wishlistCount
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Add to wishlist error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to add item to wishlist']]);
    }
}

/**
 * Remove item from wishlist
 */
function handleRemoveFromWishlist() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        $productId = (int)($_POST['product_id'] ?? 0);
        
        // Validate input
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid product ID']]);
            return;
        }
        
        // Remove from wishlist
        $order = new Order();
        $result = $order->removeFromWishlist($userId, $productId);
        
        if ($result['success']) {
            // Get updated wishlist count
            $wishlistItems = $order->getWishlistItems($userId);
            $wishlistCount = count($wishlistItems);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from wishlist',
                'wishlist_count' => $wishlistCount
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Remove from wishlist error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to remove item from wishlist']]);
    }
}

/**
 * Get wishlist items
 */
function handleGetWishlist() {
    try {
        $userId = getCurrentUserId();
        
        $order = new Order();
        $wishlistItems = $order->getWishlistItems($userId);
        
        echo json_encode([
            'success' => true,
            'wishlist_items' => $wishlistItems,
            'wishlist_count' => count($wishlistItems)
        ]);
        
    } catch (Exception $e) {
        error_log("Get wishlist error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get wishlist items']]);
    }
}
?>
