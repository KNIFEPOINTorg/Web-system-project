<?php
session_start();

// Initialize secure API headers and error handling
require_once 'cors_config.php';
require_once 'error_handler.php';
initializeSecureAPI();

require_once '../database/config.php';

class OrdersAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            return $this->error('Authentication required', 401);
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getOrders();
                    case 'single':
                        return $this->getOrder();
                    case 'seller':
                        return $this->getSellerOrders();
                    default:
                        return $this->getOrders();
                }
            case 'POST':
                return $this->createOrder();
            case 'PUT':
                return $this->updateOrderStatus();
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function createOrder() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $required = ['delivery_option', 'shipping_address', 'payment_method'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                return $this->error("$field is required");
            }
        }
        
        $this->db->query('SELECT ci.*, p.name, p.price, p.sale_price, p.stock_quantity, p.seller_id
                          FROM cart_items ci
                          JOIN products p ON ci.product_id = p.id
                          WHERE ci.user_id = :user_id');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $cartItems = $this->db->resultset();
        
        if (empty($cartItems)) {
            return $this->error('Cart is empty');
        }
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item['stock_quantity'] < $item['quantity']) {
                return $this->error("Insufficient stock for {$item['name']}");
            }
            $price = $item['sale_price'] ? (float)$item['sale_price'] : (float)$item['price'];
            $subtotal += $price * $item['quantity'];
        }
        
        $deliveryCost = $this->calculateDeliveryCost($input['delivery_option']);
        $totalAmount = $subtotal + $deliveryCost;
        $orderNumber = $this->generateOrderNumber();
        
        $this->db->beginTransaction();
        
        try {
            $this->db->query('INSERT INTO orders (order_number, user_id, total_amount, subtotal, delivery_cost, 
                                                 delivery_option, shipping_address, billing_address, payment_method, notes) 
                              VALUES (:order_number, :user_id, :total_amount, :subtotal, :delivery_cost, 
                                      :delivery_option, :shipping_address, :billing_address, :payment_method, :notes)');
            
            $this->db->bind(':order_number', $orderNumber);
            $this->db->bind(':user_id', $_SESSION['user_id']);
            $this->db->bind(':total_amount', $totalAmount);
            $this->db->bind(':subtotal', $subtotal);
            $this->db->bind(':delivery_cost', $deliveryCost);
            $this->db->bind(':delivery_option', $input['delivery_option']);
            $this->db->bind(':shipping_address', $input['shipping_address']);
            $this->db->bind(':billing_address', $input['billing_address'] ?? $input['shipping_address']);
            $this->db->bind(':payment_method', $input['payment_method']);
            $this->db->bind(':notes', $input['notes'] ?? null);
            
            $this->db->execute();
            $orderId = $this->db->lastInsertId();
            
            foreach ($cartItems as $item) {
                $price = $item['sale_price'] ? (float)$item['sale_price'] : (float)$item['price'];
                
                $this->db->query('INSERT INTO order_items (order_id, product_id, seller_id, quantity, price, size, product_name, product_image) 
                                  VALUES (:order_id, :product_id, :seller_id, :quantity, :price, :size, :product_name, :product_image)');
                
                $this->db->bind(':order_id', $orderId);
                $this->db->bind(':product_id', $item['product_id']);
                $this->db->bind(':seller_id', $item['seller_id']);
                $this->db->bind(':quantity', $item['quantity']);
                $this->db->bind(':price', $price);
                $this->db->bind(':size', $item['size']);
                $this->db->bind(':product_name', $item['name']);
                $this->db->bind(':product_image', $item['image'] ?? null);
                $this->db->execute();
                
                $this->db->query('UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id');
                $this->db->bind(':quantity', $item['quantity']);
                $this->db->bind(':product_id', $item['product_id']);
                $this->db->execute();
            }
            
            $this->db->query('DELETE FROM cart_items WHERE user_id = :user_id');
            $this->db->bind(':user_id', $_SESSION['user_id']);
            $this->db->execute();
            
            $this->db->endTransaction();
            
            return $this->success('Order created successfully', [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount
            ]);
            
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return $this->error('Failed to create order: ' . $e->getMessage());
        }
    }
    
    private function getOrders() {
        $this->db->query('SELECT o.*, COUNT(oi.id) as item_count
                          FROM orders o
                          LEFT JOIN order_items oi ON o.id = oi.order_id
                          WHERE o.user_id = :user_id
                          GROUP BY o.id
                          ORDER BY o.created_at DESC');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $orders = $this->db->resultset();
        
        foreach ($orders as &$order) {
            $order['total_amount'] = (float)$order['total_amount'];
            $order['subtotal'] = (float)$order['subtotal'];
            $order['delivery_cost'] = (float)$order['delivery_cost'];
            $order['item_count'] = (int)$order['item_count'];
        }
        
        return $this->success('Orders retrieved', $orders);
    }
    
    private function getOrder() {
        $id = $_GET['id'] ?? '';
        
        if (!$id) {
            return $this->error('Order ID required');
        }
        
        $this->db->query('SELECT * FROM orders WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $order = $this->db->single();
        
        if (!$order) {
            return $this->error('Order not found', 404);
        }
        
        $this->db->query('SELECT oi.*, u.name as seller_name 
                          FROM order_items oi
                          JOIN users u ON oi.seller_id = u.id
                          WHERE oi.order_id = :order_id');
        $this->db->bind(':order_id', $id);
        $items = $this->db->resultset();
        
        $order['total_amount'] = (float)$order['total_amount'];
        $order['subtotal'] = (float)$order['subtotal'];
        $order['delivery_cost'] = (float)$order['delivery_cost'];
        $order['items'] = $items;
        
        return $this->success('Order retrieved', $order);
    }
    
    private function getSellerOrders() {
        if ($_SESSION['user_type'] !== 'seller') {
            return $this->error('Seller access required', 403);
        }
        
        $this->db->query('SELECT o.*, oi.*, u.name as customer_name, u.email as customer_email
                          FROM orders o
                          JOIN order_items oi ON o.id = oi.order_id
                          JOIN users u ON o.user_id = u.id
                          WHERE oi.seller_id = :seller_id
                          ORDER BY o.created_at DESC');
        $this->db->bind(':seller_id', $_SESSION['user_id']);
        $orders = $this->db->resultset();
        
        foreach ($orders as &$order) {
            $order['total_amount'] = (float)$order['total_amount'];
            $order['subtotal'] = (float)$order['subtotal'];
            $order['delivery_cost'] = (float)$order['delivery_cost'];
            $order['price'] = (float)$order['price'];
        }
        
        return $this->success('Seller orders retrieved', $orders);
    }
    
    private function updateOrderStatus() {
        if ($_SESSION['user_type'] !== 'seller') {
            return $this->error('Seller access required', 403);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['order_id']) || !isset($input['status'])) {
            return $this->error('Order ID and status required');
        }
        
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($input['status'], $validStatuses)) {
            return $this->error('Invalid status');
        }
        
        $this->db->query('SELECT o.id FROM orders o
                          JOIN order_items oi ON o.id = oi.order_id
                          WHERE o.id = :order_id AND oi.seller_id = :seller_id');
        $this->db->bind(':order_id', $input['order_id']);
        $this->db->bind(':seller_id', $_SESSION['user_id']);
        $order = $this->db->single();
        
        if (!$order) {
            return $this->error('Order not found or access denied', 403);
        }
        
        $this->db->query('UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $this->db->bind(':status', $input['status']);
        $this->db->bind(':id', $input['order_id']);
        
        if ($this->db->execute()) {
            return $this->success('Order status updated');
        }
        
        return $this->error('Failed to update order status');
    }
    
    private function calculateDeliveryCost($deliveryOption) {
        switch ($deliveryOption) {
            case 'standard':
                return 25.00;
            case 'fast':
                return 50.00;
            case 'express':
                return 100.00;
            default:
                return 50.00;
        }
    }
    
    private function generateOrderNumber() {
        return 'LFL' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    private function success($message, $data = null) {
        $response = ['success' => true, 'message' => $message];
        if ($data) $response['data'] = $data;
        echo json_encode($response);
        exit;
    }
    
    private function error($message, $code = 400) {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

$api = new OrdersAPI();
$api->handleRequest();
?>
