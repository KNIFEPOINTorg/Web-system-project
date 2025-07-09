<?php
/**
 * Order Handler for LFLshop
 * Handles order processing and management
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';
requireAuth();

// Set JSON response header
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_order':
        handleCreateOrder();
        break;
    case 'get_orders':
        handleGetOrders();
        break;
    case 'get_order':
        handleGetOrder();
        break;
    case 'update_order_status':
        requireAdmin();
        handleUpdateOrderStatus();
        break;
    case 'cancel_order':
        handleCancelOrder();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Invalid action']]);
        break;
}

/**
 * Create new order
 */
function handleCreateOrder() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        
        // Get cart items
        $orderClass = new Order();
        $cartItems = $orderClass->getCartItems($userId);
        
        if (empty($cartItems)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Cart is empty']]);
            return;
        }
        
        // Calculate totals
        $subtotal = 0;
        $orderItems = [];
        
        foreach ($cartItems as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $itemTotal = $price * $item['quantity'];
            $subtotal += $itemTotal;
            
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $price
            ];
        }
        
        // Sanitize address data
        $shippingAddress = [
            'first_name' => Security::sanitizeInput($_POST['shipping_first_name'] ?? ''),
            'last_name' => Security::sanitizeInput($_POST['shipping_last_name'] ?? ''),
            'address_line1' => Security::sanitizeInput($_POST['shipping_address_line1'] ?? ''),
            'address_line2' => Security::sanitizeInput($_POST['shipping_address_line2'] ?? ''),
            'city' => Security::sanitizeInput($_POST['shipping_city'] ?? ''),
            'state' => Security::sanitizeInput($_POST['shipping_state'] ?? ''),
            'postal_code' => Security::sanitizeInput($_POST['shipping_postal_code'] ?? ''),
            'country' => Security::sanitizeInput($_POST['shipping_country'] ?? 'Ethiopia'),
            'phone' => Security::sanitizeInput($_POST['shipping_phone'] ?? '')
        ];
        
        $billingAddress = [
            'first_name' => Security::sanitizeInput($_POST['billing_first_name'] ?? ''),
            'last_name' => Security::sanitizeInput($_POST['billing_last_name'] ?? ''),
            'address_line1' => Security::sanitizeInput($_POST['billing_address_line1'] ?? ''),
            'address_line2' => Security::sanitizeInput($_POST['billing_address_line2'] ?? ''),
            'city' => Security::sanitizeInput($_POST['billing_city'] ?? ''),
            'state' => Security::sanitizeInput($_POST['billing_state'] ?? ''),
            'postal_code' => Security::sanitizeInput($_POST['billing_postal_code'] ?? ''),
            'country' => Security::sanitizeInput($_POST['billing_country'] ?? 'Ethiopia'),
            'phone' => Security::sanitizeInput($_POST['billing_phone'] ?? '')
        ];
        
        // Use shipping address for billing if same_as_shipping is checked
        if (isset($_POST['same_as_shipping']) && $_POST['same_as_shipping']) {
            $billingAddress = $shippingAddress;
        }
        
        // Validate required fields
        $errors = [];
        $requiredShippingFields = ['first_name', 'last_name', 'address_line1', 'city', 'state', 'postal_code'];
        
        foreach ($requiredShippingFields as $field) {
            if (empty($shippingAddress[$field])) {
                $errors[] = "Shipping " . str_replace('_', ' ', $field) . " is required";
            }
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }
        
        // Calculate additional charges
        $taxAmount = $subtotal * 0.15; // 15% tax
        $shippingAmount = $subtotal > 100 ? 0 : 10; // Free shipping over $100
        $discountAmount = 0; // Apply discounts if any
        
        // Prepare order data
        $orderData = [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'payment_method' => Security::sanitizeInput($_POST['payment_method'] ?? 'cash_on_delivery'),
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'notes' => Security::sanitizeInput($_POST['notes'] ?? ''),
            'items' => $orderItems
        ];
        
        // Create order
        $result = $orderClass->createOrder($userId, $orderData);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $result['order_id'],
                'order_number' => $result['order_number']
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Create order error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to create order']]);
    }
}

/**
 * Get user orders
 */
function handleGetOrders() {
    try {
        $userId = getCurrentUserId();
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        
        // Admin can view all orders
        if (isAdmin() && isset($_GET['all'])) {
            $db = getDB();
            $offset = ($page - 1) * $limit;
            
            // Get total count
            $stmt = $db->prepare("SELECT COUNT(*) FROM orders");
            $stmt->execute();
            $totalOrders = $stmt->fetchColumn();
            
            // Get orders
            $stmt = $db->prepare("
                SELECT o.*, u.first_name, u.last_name, u.email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $orders = $stmt->fetchAll();
            
            $result = [
                'orders' => $orders,
                'total' => $totalOrders,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalOrders / $limit)
            ];
        } else {
            // Regular user orders
            $order = new Order();
            $result = $order->getUserOrders($userId, $page, $limit);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
        
    } catch (Exception $e) {
        error_log("Get orders error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get orders']]);
    }
}

/**
 * Get single order
 */
function handleGetOrder() {
    try {
        $orderId = (int)($_GET['id'] ?? 0);
        
        if ($orderId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid order ID']]);
            return;
        }
        
        $userId = isAdmin() ? null : getCurrentUserId();
        
        $order = new Order();
        $result = $order->getOrderById($orderId, $userId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'order' => $result
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'errors' => ['Order not found']]);
        }
        
    } catch (Exception $e) {
        error_log("Get order error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get order']]);
    }
}

/**
 * Update order status (Admin only)
 */
function handleUpdateOrderStatus() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = Security::sanitizeInput($_POST['status'] ?? '');
        
        if ($orderId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid order ID']]);
            return;
        }
        
        if (empty($status)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Status is required']]);
            return;
        }
        
        $order = new Order();
        $result = $order->updateOrderStatus($orderId, $status);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Update order status error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to update order status']]);
    }
}

/**
 * Cancel order
 */
function handleCancelOrder() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $userId = getCurrentUserId();
        
        if ($orderId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid order ID']]);
            return;
        }
        
        // Get order to verify ownership and status
        $order = new Order();
        $orderData = $order->getOrderById($orderId, $userId);
        
        if (!$orderData) {
            http_response_code(404);
            echo json_encode(['success' => false, 'errors' => ['Order not found']]);
            return;
        }
        
        // Check if order can be cancelled
        if (!in_array($orderData['status'], ['pending', 'processing'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Order cannot be cancelled at this stage']]);
            return;
        }
        
        // Cancel order
        $result = $order->updateOrderStatus($orderId, 'cancelled');
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Cancel order error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to cancel order']]);
    }
}
?>
