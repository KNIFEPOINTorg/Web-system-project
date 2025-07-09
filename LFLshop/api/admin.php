<?php
/**
 * Admin API for LFLshop
 * Handles admin dashboard functionality, user management, and system operations
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database/config.php';
require_once '../includes/security.php';

class AdminAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        // Check admin authentication
        SecurityHelper::requireRole('admin');
        
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        switch ($method) {
            case 'GET':
                return $this->handleGet($action);
            case 'POST':
                return $this->handlePost($action);
            case 'PUT':
                return $this->handlePut($action);
            case 'DELETE':
                return $this->handleDelete($action);
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function handleGet($action) {
        switch ($action) {
            case 'dashboard':
                return $this->getDashboardStats();
            case 'users':
                return $this->getUsers();
            case 'orders':
                return $this->getOrders();
            case 'products':
                return $this->getProducts();
            case 'analytics':
                return $this->getAnalytics();
            case 'logs':
                return $this->getLogs();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function handlePost($action) {
        switch ($action) {
            case 'user':
                return $this->createUser();
            case 'category':
                return $this->createCategory();
            case 'send_notification':
                return $this->sendNotification();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function handlePut($action) {
        switch ($action) {
            case 'user':
                return $this->updateUser();
            case 'order_status':
                return $this->updateOrderStatus();
            case 'product_status':
                return $this->updateProductStatus();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function handleDelete($action) {
        switch ($action) {
            case 'user':
                return $this->deleteUser();
            case 'product':
                return $this->deleteProduct();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function getDashboardStats() {
        try {
            $stats = [];
            
            // Total users
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            $stats['total_users'] = $this->db->single()['count'];
            
            // Total customers
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer' AND is_active = 1");
            $stats['total_customers'] = $this->db->single()['count'];
            
            // Total sellers
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'seller' AND is_active = 1");
            $stats['total_sellers'] = $this->db->single()['count'];
            
            // Total products
            $this->db->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
            $stats['total_products'] = $this->db->single()['count'];
            
            // Total orders
            $this->db->query("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $this->db->single()['count'];
            
            // Pending orders
            $this->db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
            $stats['pending_orders'] = $this->db->single()['count'];
            
            // Total revenue
            $this->db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'");
            $result = $this->db->single();
            $stats['total_revenue'] = $result['revenue'] ?? 0;
            
            // Monthly revenue
            $this->db->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
            $result = $this->db->single();
            $stats['monthly_revenue'] = $result['revenue'] ?? 0;
            
            // Recent orders
            $this->db->query("
                SELECT o.*, u.name as customer_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT 10
            ");
            $stats['recent_orders'] = $this->db->resultset();
            
            // Top selling products
            $this->db->query("
                SELECT p.name, p.image, SUM(oi.quantity) as total_sold, SUM(oi.total_price) as revenue
                FROM products p
                JOIN order_items oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.payment_status = 'paid'
                GROUP BY p.id
                ORDER BY total_sold DESC
                LIMIT 5
            ");
            $stats['top_products'] = $this->db->resultset();
            
            return $this->success('Dashboard stats retrieved', $stats);
            
        } catch (Exception $e) {
            return $this->error('Failed to get dashboard stats: ' . $e->getMessage());
        }
    }
    
    private function getUsers() {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $search = $_GET['search'] ?? '';
        $userType = $_GET['user_type'] ?? '';
        
        $offset = ($page - 1) * $limit;
        
        $whereConditions = ['1=1'];
        $params = [];
        
        if ($search) {
            $whereConditions[] = "(name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if ($userType) {
            $whereConditions[] = "user_type = :user_type";
            $params[':user_type'] = $userType;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Get total count
        $this->db->query("SELECT COUNT(*) as count FROM users WHERE $whereClause");
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $totalCount = $this->db->single()['count'];
        
        // Get users
        $this->db->query("
            SELECT id, name, email, user_type, phone, city, is_active, email_verified, last_login, created_at
            FROM users 
            WHERE $whereClause 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        
        $users = $this->db->resultset();
        
        return $this->success('Users retrieved', [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalCount / $limit),
                'total_count' => $totalCount,
                'per_page' => $limit
            ]
        ]);
    }
    
    private function getOrders() {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 20);
        $status = $_GET['status'] ?? '';
        $paymentStatus = $_GET['payment_status'] ?? '';
        
        $offset = ($page - 1) * $limit;
        
        $whereConditions = ['1=1'];
        $params = [];
        
        if ($status) {
            $whereConditions[] = "o.status = :status";
            $params[':status'] = $status;
        }
        
        if ($paymentStatus) {
            $whereConditions[] = "o.payment_status = :payment_status";
            $params[':payment_status'] = $paymentStatus;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Get total count
        $this->db->query("SELECT COUNT(*) as count FROM orders o WHERE $whereClause");
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $totalCount = $this->db->single()['count'];
        
        // Get orders
        $this->db->query("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE $whereClause
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        
        $orders = $this->db->resultset();
        
        return $this->success('Orders retrieved', [
            'orders' => $orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalCount / $limit),
                'total_count' => $totalCount,
                'per_page' => $limit
            ]
        ]);
    }
    
    private function updateOrderStatus() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['order_id']) || !isset($input['status'])) {
            return $this->error('Order ID and status required');
        }
        
        $orderId = (int)$input['order_id'];
        $status = SecurityHelper::sanitize($input['status']);
        $notes = SecurityHelper::sanitize($input['notes'] ?? '');
        
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return $this->error('Invalid status');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Update order
            $this->db->query("UPDATE orders SET status = :status, admin_notes = :notes WHERE id = :order_id");
            $this->db->bind(':status', $status);
            $this->db->bind(':notes', $notes);
            $this->db->bind(':order_id', $orderId);
            $this->db->execute();
            
            // Log admin action
            $this->logAdminAction('order_status_update', 'orders', $orderId, ['status' => $status, 'notes' => $notes]);
            
            // Send notification to customer
            $this->db->query("SELECT user_id FROM orders WHERE id = :order_id");
            $this->db->bind(':order_id', $orderId);
            $order = $this->db->single();
            
            if ($order) {
                $this->db->query("INSERT INTO notifications (user_id, type, title, message) VALUES (:user_id, :type, :title, :message)");
                $this->db->bind(':user_id', $order['user_id']);
                $this->db->bind(':type', 'order_update');
                $this->db->bind(':title', 'Order Status Updated');
                $this->db->bind(':message', "Your order status has been updated to: $status");
                $this->db->execute();
            }
            
            $this->db->endTransaction();
            
            return $this->success('Order status updated successfully');
            
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return $this->error('Failed to update order status: ' . $e->getMessage());
        }
    }
    
    private function getAnalytics() {
        $period = $_GET['period'] ?? '30'; // days
        
        try {
            $analytics = [];
            
            // Sales over time
            $this->db->query("
                SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY) AND payment_status = 'paid'
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $this->db->bind(':period', $period);
            $analytics['sales_chart'] = $this->db->resultset();
            
            // Top categories
            $this->db->query("
                SELECT c.name, COUNT(oi.id) as orders, SUM(oi.total_price) as revenue
                FROM categories c
                JOIN products p ON c.id = p.category_id
                JOIN order_items oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.payment_status = 'paid' AND o.created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
                GROUP BY c.id
                ORDER BY revenue DESC
                LIMIT 10
            ");
            $this->db->bind(':period', $period);
            $analytics['top_categories'] = $this->db->resultset();
            
            // User registration over time
            $this->db->query("
                SELECT DATE(created_at) as date, COUNT(*) as registrations
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :period DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $this->db->bind(':period', $period);
            $analytics['user_registrations'] = $this->db->resultset();
            
            return $this->success('Analytics retrieved', $analytics);
            
        } catch (Exception $e) {
            return $this->error('Failed to get analytics: ' . $e->getMessage());
        }
    }
    
    private function logAdminAction($action, $tableName, $recordId, $data) {
        $this->db->query("INSERT INTO admin_logs (admin_id, action, table_name, record_id, new_values, ip_address, user_agent) VALUES (:admin_id, :action, :table_name, :record_id, :new_values, :ip_address, :user_agent)");
        $this->db->bind(':admin_id', $_SESSION['user_id']);
        $this->db->bind(':action', $action);
        $this->db->bind(':table_name', $tableName);
        $this->db->bind(':record_id', $recordId);
        $this->db->bind(':new_values', json_encode($data));
        $this->db->bind(':ip_address', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $this->db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        $this->db->execute();
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

$api = new AdminAPI();
$api->handleRequest();
?>