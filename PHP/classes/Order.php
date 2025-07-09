<?php
/**
 * Order Class for LFLshop
 * Handles order management, cart operations
 */

class Order {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create new order
     */
    public function createOrder($userId, $orderData) {
        try {
            $this->db->beginTransaction();
            
            // Generate order number
            $orderNumber = $this->generateOrderNumber();
            
            // Calculate totals
            $subtotal = $orderData['subtotal'];
            $taxAmount = $orderData['tax_amount'] ?? 0;
            $shippingAmount = $orderData['shipping_amount'] ?? 0;
            $discountAmount = $orderData['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount + $shippingAmount - $discountAmount;
            
            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    user_id, order_number, status, total_amount, subtotal, 
                    tax_amount, shipping_amount, discount_amount, payment_method,
                    shipping_address, billing_address, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $userId,
                $orderNumber,
                'pending',
                $totalAmount,
                $subtotal,
                $taxAmount,
                $shippingAmount,
                $discountAmount,
                $orderData['payment_method'] ?? null,
                json_encode($orderData['shipping_address'] ?? []),
                json_encode($orderData['billing_address'] ?? []),
                $orderData['notes'] ?? null
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create order");
            }
            
            $orderId = $this->db->lastInsertId();
            
            // Add order items
            if (!empty($orderData['items'])) {
                foreach ($orderData['items'] as $item) {
                    $this->addOrderItem($orderId, $item);
                }
            }
            
            // Clear user's cart
            $this->clearCart($userId);
            
            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Create order error: " . $e->getMessage());
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }
    
    /**
     * Get order by ID
     */
    public function getOrderById($orderId, $userId = null) {
        try {
            $sql = "
                SELECT o.*, u.first_name, u.last_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
            ";
            $params = [$orderId];
            
            if ($userId) {
                $sql .= " AND o.user_id = ?";
                $params[] = $userId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $order = $stmt->fetch();
            
            if ($order) {
                // Get order items
                $stmt = $this->db->prepare("
                    SELECT oi.*, p.name as product_name, p.slug as product_slug
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$orderId]);
                $order['items'] = $stmt->fetchAll();
                
                // Decode JSON fields
                $order['shipping_address'] = json_decode($order['shipping_address'], true);
                $order['billing_address'] = json_decode($order['billing_address'], true);
            }
            
            return $order;
            
        } catch (Exception $e) {
            error_log("Get order error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $stmt->execute([$userId]);
            $totalOrders = $stmt->fetchColumn();
            
            // Get orders
            $stmt = $this->db->prepare("
                SELECT o.*, 
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            $orders = $stmt->fetchAll();
            
            return [
                'orders' => $orders,
                'total' => $totalOrders,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalOrders / $limit)
            ];
            
        } catch (Exception $e) {
            error_log("Get user orders error: " . $e->getMessage());
            return ['orders' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'total_pages' => 0];
        }
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $status) {
        try {
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid order status");
            }
            
            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $result = $stmt->execute([$status, $orderId]);
            
            if ($result) {
                return ['success' => true];
            }
            
            throw new Exception("Failed to update order status");
            
        } catch (Exception $e) {
            error_log("Update order status error: " . $e->getMessage());
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }
    
    /**
     * Add item to cart
     */
    public function addToCart($userId, $productId, $quantity = 1) {
        try {
            // Check if product exists and is available
            $stmt = $this->db->prepare("
                SELECT id, stock_quantity, stock_status 
                FROM products 
                WHERE id = ? AND status = 'published'
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return ['success' => false, 'errors' => ['Product not found']];
            }
            
            if ($product['stock_status'] === 'out_of_stock') {
                return ['success' => false, 'errors' => ['Product is out of stock']];
            }
            
            // Check if item already in cart
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $cartItem = $stmt->fetch();
            
            if ($cartItem) {
                // Update quantity
                $newQuantity = $cartItem['quantity'] + $quantity;
                $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $cartItem['id']]);
            } else {
                // Add new item
                $stmt = $this->db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $productId, $quantity]);
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Add to cart error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to add item to cart']];
        }
    }
    
    /**
     * Get cart items
     */
    public function getCartItems($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, p.name, p.price, p.sale_price, p.slug, p.stock_quantity, p.stock_status,
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ? AND p.status = 'published'
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get cart items error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update cart item quantity
     */
    public function updateCartItem($userId, $productId, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($userId, $productId);
            }
            
            $stmt = $this->db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$quantity, $userId, $productId]);
            
            return ['success' => $result];
            
        } catch (Exception $e) {
            error_log("Update cart item error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to update cart item']];
        }
    }
    
    /**
     * Remove item from cart
     */
    public function removeFromCart($userId, $productId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$userId, $productId]);
            
            return ['success' => $result];
            
        } catch (Exception $e) {
            error_log("Remove from cart error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to remove item from cart']];
        }
    }
    
    /**
     * Clear cart
     */
    public function clearCart($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (Exception $e) {
            error_log("Clear cart error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add to wishlist
     */
    public function addToWishlist($userId, $productId) {
        try {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO wishlist (user_id, product_id) 
                VALUES (?, ?)
            ");
            $result = $stmt->execute([$userId, $productId]);
            
            return ['success' => $result];
            
        } catch (Exception $e) {
            error_log("Add to wishlist error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to add to wishlist']];
        }
    }
    
    /**
     * Get wishlist items
     */
    public function getWishlistItems($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT w.*, p.name, p.price, p.sale_price, p.slug,
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
                FROM wishlist w
                LEFT JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ? AND p.status = 'published'
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get wishlist items error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Remove from wishlist
     */
    public function removeFromWishlist($userId, $productId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $result = $stmt->execute([$userId, $productId]);
            
            return ['success' => $result];
            
        } catch (Exception $e) {
            error_log("Remove from wishlist error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Failed to remove from wishlist']];
        }
    }
    
    /**
     * Generate unique order number
     */
    private function generateOrderNumber() {
        $prefix = 'LFL';
        $timestamp = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $timestamp . $random;
    }
    
    /**
     * Add order item
     */
    private function addOrderItem($orderId, $item) {
        $stmt = $this->db->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, total)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $total = $item['quantity'] * $item['price'];
        
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $total
        ]);
    }
}
?>
