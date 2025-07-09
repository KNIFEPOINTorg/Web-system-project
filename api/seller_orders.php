<?php
/**
 * Seller Order Management API for LFLshop
 * Handles order management functionality for sellers
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database/config.php';
require_once '../includes/security.php';

class SellerOrdersAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        // Require seller authentication
        SecurityHelper::requireRole('seller');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        switch ($method) {
            case 'GET':
                return $this->handleGet($action);
            case 'PUT':
                return $this->handlePut($action);
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function handleGet($action) {
        switch ($action) {
            case 'list':
                return $this->getSellerOrders();
            case 'details':
                return $this->getOrderDetails();
            case 'stats':
                return $this->getSellerStats();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function handlePut($action) {
        switch ($action) {
            case 'update_status':
                return $this->updateOrderStatus();
            case 'add_tracking':
                return $this->addTrackingInfo();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function getSellerOrders() {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $offset = ($page - 1) * $limit;
        $sellerId = $_SESSION['user_id'];
        
        $whereConditions = ['oi.seller_id = :seller_id'];
        $params = [':seller_id' => $sellerId];
        
        if ($status) {
            $whereConditions[] = 'o.status = :status';
            $params[':status'] = $status;
        }
        
        if ($search) {
            $whereConditions[] = '(o.order_number LIKE :search OR u.name LIKE :search OR u.email LIKE :search)';
            $params[':search'] = "%$search%";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        try {
            // Get total count
            $this->db->query("
                SELECT COUNT(DISTINCT o.id) as count 
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                JOIN users u ON o.user_id = u.id 
                WHERE $whereClause
            ");
            
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }
            $totalCount = $this->db->single()['count'];
            
            // Get orders
            $this->db->query("
                SELECT DISTINCT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                       SUM(oi.total_price) as seller_total,
                       COUNT(oi.id) as item_count
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN users u ON o.user_id = u.id
                WHERE $whereClause
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }
            $this->db->bind(':limit', $limit);
            $this->db->bind(':offset', $offset);
            
            $orders = $this->db->resultset();
            
            // Get order items for each order
            foreach ($orders as &$order) {
                $this->db->query("
                    SELECT oi.*, p.name as product_name, p.image as product_image
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = :order_id AND oi.seller_id = :seller_id
                ");
                $this->db->bind(':order_id', $order['id']);
                $this->db->bind(':seller_id', $sellerId);
                $order['items'] = $this->db->resultset();
                
                // Parse JSON addresses
                $order['shipping_address'] = json_decode($order['shipping_address'], true);
                $order['billing_address'] = json_decode($order['billing_address'], true);
            }
            
            return $this->success('Orders retrieved successfully', [
                'orders' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalCount / $limit),
                    'total_count' => $totalCount,
                    'per_page' => $limit
                ]
            ]);
            
        } catch (Exception $e) {
            return $this->error('Failed to retrieve orders: ' . $e->getMessage());
        }
    }
    
    private function getOrderDetails() {
        $orderId = (int)($_GET['order_id'] ?? 0);
        
        if (!$orderId) {
            return $this->error('Order ID required');
        }
        
        try {
            // Get order details
            $this->db->query("
                SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = :order_id
            ");
            $this->db->bind(':order_id', $orderId);
            $order = $this->db->single();
            
            if (!$order) {
                return $this->error('Order not found', 404);
            }
            
            // Check if seller has items in this order
            $this->db->query("SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id AND seller_id = :seller_id");
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':seller_id', $_SESSION['user_id']);
            $hasItems = $this->db->single()['count'] > 0;
            
            if (!$hasItems) {
                return $this->error('Access denied', 403);
            }
            
            // Get seller's items in this order
            $this->db->query("
                SELECT oi.*, p.name as product_name, p.image as product_image, p.sku
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id AND oi.seller_id = :seller_id
            ");
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':seller_id', $_SESSION['user_id']);
            $order['items'] = $this->db->resultset();
            
            // Parse JSON addresses
            $order['shipping_address'] = json_decode($order['shipping_address'], true);
            $order['billing_address'] = json_decode($order['billing_address'], true);
            
            // Get payment transaction info
            $this->db->query("SELECT * FROM payment_transactions WHERE order_id = :order_id ORDER BY created_at DESC LIMIT 1");
            $this->db->bind(':order_id', $orderId);
            $order['payment_transaction'] = $this->db->single();
            
            return $this->success('Order details retrieved', $order);
            
        } catch (Exception $e) {
            return $this->error('Failed to retrieve order details: ' . $e->getMessage());
        }
    }
    
    private function updateOrderStatus() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['order_id']) || !isset($input['status'])) {
            return $this->error('Order ID and status required');
        }
        
        $orderId = (int)$input['order_id'];
        $status = SecurityHelper::sanitize($input['status']);
        $notes = SecurityHelper::sanitize($input['notes'] ?? '');
        
        $validStatuses = ['confirmed', 'processing', 'shipped', 'delivered'];
        if (!in_array($status, $validStatuses)) {
            return $this->error('Invalid status. Allowed: ' . implode(', ', $validStatuses));
        }
        
        try {
            // Verify seller has items in this order
            $this->db->query("SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id AND seller_id = :seller_id");
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':seller_id', $_SESSION['user_id']);
            $hasItems = $this->db->single()['count'] > 0;
            
            if (!$hasItems) {
                return $this->error('Access denied', 403);
            }
            
            $this->db->beginTransaction();
            
            // Update order status
            $this->db->query("UPDATE orders SET status = :status WHERE id = :order_id");
            $this->db->bind(':status', $status);
            $this->db->bind(':order_id', $orderId);
            $this->db->execute();
            
            // Add status update note
            if ($notes) {
                $currentNotes = '';
                $this->db->query("SELECT admin_notes FROM orders WHERE id = :order_id");
                $this->db->bind(':order_id', $orderId);
                $result = $this->db->single();
                if ($result && $result['admin_notes']) {
                    $currentNotes = $result['admin_notes'] . "\n\n";
                }
                
                $newNote = date('Y-m-d H:i:s') . " - Seller Update: $notes";
                $this->db->query("UPDATE orders SET admin_notes = :notes WHERE id = :order_id");
                $this->db->bind(':notes', $currentNotes . $newNote);
                $this->db->bind(':order_id', $orderId);
                $this->db->execute();
            }
            
            // Set delivered timestamp if status is delivered
            if ($status === 'delivered') {
                $this->db->query("UPDATE orders SET delivered_at = NOW() WHERE id = :order_id");
                $this->db->bind(':order_id', $orderId);
                $this->db->execute();
            }
            
            // Get customer info for notification
            $this->db->query("SELECT user_id, order_number FROM orders WHERE id = :order_id");
            $this->db->bind(':order_id', $orderId);
            $order = $this->db->single();
            
            // Send notification to customer
            $this->db->query("INSERT INTO notifications (user_id, type, title, message, action_url) VALUES (:user_id, :type, :title, :message, :action_url)");
            $this->db->bind(':user_id', $order['user_id']);
            $this->db->bind(':type', 'order_update');
            $this->db->bind(':title', 'Order Status Updated');
            $this->db->bind(':message', "Your order #{$order['order_number']} status has been updated to: $status");
            $this->db->bind(':action_url', '/html/customer-dashboard.html?tab=orders');
            $this->db->execute();
            
            $this->db->endTransaction();
            
            return $this->success('Order status updated successfully');
            
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return $this->error('Failed to update order status: ' . $e->getMessage());
        }
    }
    
    private function addTrackingInfo() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['order_id']) || !isset($input['tracking_number'])) {
            return $this->error('Order ID and tracking number required');
        }
        
        $orderId = (int)$input['order_id'];
        $trackingNumber = SecurityHelper::sanitize($input['tracking_number']);
        $carrier = SecurityHelper::sanitize($input['carrier'] ?? '');
        
        try {
            // Verify seller has items in this order
            $this->db->query("SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id AND seller_id = :seller_id");
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':seller_id', $_SESSION['user_id']);
            $hasItems = $this->db->single()['count'] > 0;
            
            if (!$hasItems) {
                return $this->error('Access denied', 403);
            }
            
            // Update tracking info
            $this->db->query("UPDATE orders SET tracking_number = :tracking_number, status = 'shipped' WHERE id = :order_id");
            $this->db->bind(':tracking_number', $trackingNumber);
            $this->db->bind(':order_id', $orderId);
            $this->db->execute();
            
            // Get customer info for notification
            $this->db->query("SELECT user_id, order_number FROM orders WHERE id = :order_id");
            $this->db->bind(':order_id', $orderId);
            $order = $this->db->single();
            
            // Send notification to customer
            $message = "Your order #{$order['order_number']} has been shipped. Tracking number: $trackingNumber";
            if ($carrier) {
                $message .= " (Carrier: $carrier)";
            }
            
            $this->db->query("INSERT INTO notifications (user_id, type, title, message, action_url) VALUES (:user_id, :type, :title, :message, :action_url)");
            $this->db->bind(':user_id', $order['user_id']);
            $this->db->bind(':type', 'order_shipped');
            $this->db->bind(':title', 'Order Shipped');
            $this->db->bind(':message', $message);
            $this->db->bind(':action_url', '/html/customer-dashboard.html?tab=orders');
            $this->db->execute();
            
            return $this->success('Tracking information added successfully');
            
        } catch (Exception $e) {
            return $this->error('Failed to add tracking info: ' . $e->getMessage());
        }
    }
    
    private function getSellerStats() {
        $sellerId = $_SESSION['user_id'];
        $period = $_GET['period'] ?? '30'; // days
        
        try {
            $stats = [];
            
            // Total orders
            $this->db->query("
                SELECT COUNT(DISTINCT o.id) as count 
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE oi.seller_id = :seller_id
            ");
            $this->db->bind(':seller_id', $sellerId);
            $stats['total_orders'] = $this->db->single()['count'];
            
            // Pending orders
            $this->db->query("
                SELECT COUNT(DISTINCT o.id) as count 
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE oi.seller_id = :seller_id AND o.status = 'pending'
            ");
            $this->db->bind(':seller_id', $sellerId);
            $stats['pending_orders'] = $this->db->single()['count'];
            
            // Total revenue
            $this->db->query("
                SELECT SUM(oi.total_price) as revenue 
                FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE oi.seller_id = :seller_id AND o.payment_status = 'paid'
            ");
            $this->db->bind(':seller_id', $sellerId);
            $result = $this->db->single();
            $stats['total_revenue'] = $result['revenue'] ?? 0;
            
            // Monthly revenue
            $this->db->query("
                SELECT SUM(oi.total_price) as revenue 
                FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE oi.seller_id = :seller_id 
                AND o.payment_status = 'paid' 
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
            ");
            $this->db->bind(':seller_id', $sellerId);
            $this->db->bind(':period', $period);
            $result = $this->db->single();
            $stats['period_revenue'] = $result['revenue'] ?? 0;
            
            // Top selling products
            $this->db->query("
                SELECT p.name, p.image, SUM(oi.quantity) as total_sold, SUM(oi.total_price) as revenue
                FROM products p
                JOIN order_items oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                WHERE p.seller_id = :seller_id AND o.payment_status = 'paid'
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT 5
            ");
            $this->db->bind(':seller_id', $sellerId);
            $stats['top_products'] = $this->db->resultset();
            
            return $this->success('Seller stats retrieved', $stats);
            
        } catch (Exception $e) {
            return $this->error('Failed to get seller stats: ' . $e->getMessage());
        }
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

$api = new SellerOrdersAPI();
$api->handleRequest();
?>