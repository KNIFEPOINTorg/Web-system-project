<?php
session_start();

// Initialize secure API headers and error handling
require_once 'cors_config.php';
require_once 'error_handler.php';
initializeSecureAPI();

require_once '../database/config.php';

class CartAPI {
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
                return $this->getCart();
            case 'POST':
                switch ($action) {
                    case 'add':
                        return $this->addToCart();
                    case 'clear':
                        return $this->clearCart();
                    default:
                        return $this->addToCart();
                }
            case 'PUT':
                return $this->updateCartItem();
            case 'DELETE':
                return $this->removeFromCart();
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function getCart() {
        $this->db->query('SELECT ci.*, p.name, p.price, p.sale_price, p.image, p.stock_quantity, 
                                 u.name as seller_name, c.name as category_name
                          FROM cart_items ci
                          JOIN products p ON ci.product_id = p.id
                          JOIN users u ON p.seller_id = u.id
                          JOIN categories c ON p.category_id = c.id
                          WHERE ci.user_id = :user_id
                          ORDER BY ci.created_at DESC');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $items = $this->db->resultset();
        
        $total = 0;
        $itemCount = 0;
        
        foreach ($items as &$item) {
            $price = $item['sale_price'] ? (float)$item['sale_price'] : (float)$item['price'];
            $item['price'] = (float)$item['price'];
            $item['sale_price'] = $item['sale_price'] ? (float)$item['sale_price'] : null;
            $item['current_price'] = $price;
            $item['subtotal'] = $price * $item['quantity'];
            $total += $item['subtotal'];
            $itemCount += $item['quantity'];
        }
        
        return $this->success('Cart retrieved', [
            'items' => $items,
            'total' => $total,
            'item_count' => $itemCount
        ]);
    }
    
    private function addToCart() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['product_id'])) {
            return $this->error('Product ID required');
        }
        
        $productId = $input['product_id'];
        $quantity = $input['quantity'] ?? 1;
        $size = $input['size'] ?? null;
        
        $this->db->query('SELECT id, stock_quantity FROM products WHERE id = :id AND status = "active"');
        $this->db->bind(':id', $productId);
        $product = $this->db->single();
        
        if (!$product) {
            return $this->error('Product not found or inactive');
        }
        
        if ($product['stock_quantity'] < $quantity) {
            return $this->error('Insufficient stock');
        }
        
        $this->db->query('SELECT id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id AND size = :size');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':size', $size);
        $existingItem = $this->db->single();
        
        if ($existingItem) {
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            if ($product['stock_quantity'] < $newQuantity) {
                return $this->error('Insufficient stock for requested quantity');
            }
            
            $this->db->query('UPDATE cart_items SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP 
                              WHERE id = :id');
            $this->db->bind(':quantity', $newQuantity);
            $this->db->bind(':id', $existingItem['id']);
        } else {
            $this->db->query('INSERT INTO cart_items (user_id, product_id, quantity, size) 
                              VALUES (:user_id, :product_id, :quantity, :size)');
            $this->db->bind(':user_id', $_SESSION['user_id']);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':quantity', $quantity);
            $this->db->bind(':size', $size);
        }
        
        if ($this->db->execute()) {
            return $this->success('Item added to cart');
        }
        
        return $this->error('Failed to add item to cart');
    }
    
    private function updateCartItem() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['item_id']) || !isset($input['quantity'])) {
            return $this->error('Item ID and quantity required');
        }
        
        $itemId = $input['item_id'];
        $quantity = (int)$input['quantity'];
        
        if ($quantity <= 0) {
            return $this->removeFromCart($itemId);
        }
        
        $this->db->query('SELECT ci.product_id, p.stock_quantity 
                          FROM cart_items ci 
                          JOIN products p ON ci.product_id = p.id 
                          WHERE ci.id = :id AND ci.user_id = :user_id');
        $this->db->bind(':id', $itemId);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $item = $this->db->single();
        
        if (!$item) {
            return $this->error('Cart item not found');
        }
        
        if ($item['stock_quantity'] < $quantity) {
            return $this->error('Insufficient stock');
        }
        
        $this->db->query('UPDATE cart_items SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP 
                          WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':quantity', $quantity);
        $this->db->bind(':id', $itemId);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        
        if ($this->db->execute()) {
            return $this->success('Cart item updated');
        }
        
        return $this->error('Failed to update cart item');
    }
    
    private function removeFromCart($itemId = null) {
        if (!$itemId) {
            $itemId = $_GET['item_id'] ?? '';
        }
        
        if (!$itemId) {
            return $this->error('Item ID required');
        }
        
        $this->db->query('DELETE FROM cart_items WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':id', $itemId);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        
        if ($this->db->execute()) {
            return $this->success('Item removed from cart');
        }
        
        return $this->error('Failed to remove item from cart');
    }
    
    private function clearCart() {
        $this->db->query('DELETE FROM cart_items WHERE user_id = :user_id');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        
        if ($this->db->execute()) {
            return $this->success('Cart cleared');
        }
        
        return $this->error('Failed to clear cart');
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

$api = new CartAPI();
$api->handleRequest();
?>
