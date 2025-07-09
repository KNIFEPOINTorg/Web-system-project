<?php
session_start();

// IMPORTANT: Initialize secure API headers and error handling
require_once 'cors_config.php';
require_once 'error_handler.php';
initializeSecureAPI();

require_once '../database/config.php';

class ProductsAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getProducts();
                    case 'single':
                        return $this->getProduct();
                    case 'seller':
                        return $this->getSellerProducts();
                    case 'featured':
                        return $this->getFeaturedProducts();
                    case 'categories':
                        return $this->getCategories();
                    default:
                        return $this->getProducts();
                }
            case 'POST':
                return $this->createProduct();
            case 'PUT':
                return $this->updateProduct();
            case 'DELETE':
                return $this->deleteProduct();
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function getProducts() {
        $category = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'active';
        $limit = (int)($_GET['limit'] ?? 20);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $sql = 'SELECT p.*, c.name as category_name, u.name as seller_name, 
                       (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.seller_id = u.id 
                WHERE p.status = :status';
        
        $params = [':status' => $status];
        
        if ($category) {
            $sql .= ' AND c.slug = :category';
            $params[':category'] = $category;
        }
        
        if ($search) {
            $sql .= ' AND (p.name LIKE :search OR p.description LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= ' ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        $products = $this->db->resultset();
        
        foreach ($products as &$product) {
            $product['price'] = (float)$product['price'];
            $product['sale_price'] = $product['sale_price'] ? (float)$product['sale_price'] : null;
            $product['avg_rating'] = $product['avg_rating'] ? round((float)$product['avg_rating'], 1) : null;
            $product['review_count'] = (int)$product['review_count'];
        }
        
        return $this->success('Products retrieved', $products);
    }
    
    private function getProduct() {
        $id = $_GET['id'] ?? '';
        
        if (!$id) {
            return $this->error('Product ID required');
        }
        
        $this->db->query('SELECT p.*, c.name as category_name, u.name as seller_name, u.email as seller_email,
                                 (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
                                 (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN users u ON p.seller_id = u.id 
                          WHERE p.id = :id');
        $this->db->bind(':id', $id);
        $product = $this->db->single();
        
        if (!$product) {
            return $this->error('Product not found', 404);
        }
        
        $product['price'] = (float)$product['price'];
        $product['sale_price'] = $product['sale_price'] ? (float)$product['sale_price'] : null;
        $product['avg_rating'] = $product['avg_rating'] ? round((float)$product['avg_rating'], 1) : null;
        $product['review_count'] = (int)$product['review_count'];
        
        $this->db->query('UPDATE products SET views_count = views_count + 1 WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        return $this->success('Product retrieved', $product);
    }
    
    private function getSellerProducts() {
        if (!isset($_SESSION['user_id'])) {
            return $this->error('Authentication required', 401);
        }
        
        $this->db->query('SELECT p.*, c.name as category_name,
                                 (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
                                 (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.seller_id = :seller_id 
                          ORDER BY p.created_at DESC');
        $this->db->bind(':seller_id', $_SESSION['user_id']);
        $products = $this->db->resultset();
        
        foreach ($products as &$product) {
            $product['price'] = (float)$product['price'];
            $product['sale_price'] = $product['sale_price'] ? (float)$product['sale_price'] : null;
            $product['avg_rating'] = $product['avg_rating'] ? round((float)$product['avg_rating'], 1) : null;
            $product['review_count'] = (int)$product['review_count'];
        }
        
        return $this->success('Seller products retrieved', $products);
    }
    
    private function getFeaturedProducts() {
        $limit = (int)($_GET['limit'] ?? 4);
        
        $this->db->query('SELECT p.*, c.name as category_name, u.name as seller_name,
                                 (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating,
                                 (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN users u ON p.seller_id = u.id 
                          WHERE p.status = "active" AND p.is_featured = 1 
                          ORDER BY p.created_at DESC 
                          LIMIT :limit');
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $products = $this->db->resultset();
        
        foreach ($products as &$product) {
            $product['price'] = (float)$product['price'];
            $product['sale_price'] = $product['sale_price'] ? (float)$product['sale_price'] : null;
            $product['avg_rating'] = $product['avg_rating'] ? round((float)$product['avg_rating'], 1) : null;
            $product['review_count'] = (int)$product['review_count'];
        }
        
        return $this->success('Featured products retrieved', $products);
    }
    
    private function getCategories() {
        $this->db->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name');
        $categories = $this->db->resultset();
        
        return $this->success('Categories retrieved', $categories);
    }
    
    private function createProduct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
            return $this->error('Seller authentication required', 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $required = ['name', 'description', 'price', 'category_id', 'stock_quantity'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                return $this->error("$field is required");
            }
        }
        
        $sku = $input['sku'] ?? $this->generateSKU();
        
        $this->db->query('INSERT INTO products (seller_id, category_id, name, description, price, sale_price, 
                                               sku, stock_quantity, size, color, location, image, status, is_featured) 
                          VALUES (:seller_id, :category_id, :name, :description, :price, :sale_price, 
                                  :sku, :stock_quantity, :size, :color, :location, :image, :status, :is_featured)');
        
        $this->db->bind(':seller_id', $_SESSION['user_id']);
        $this->db->bind(':category_id', $input['category_id']);
        $this->db->bind(':name', $input['name']);
        $this->db->bind(':description', $input['description']);
        $this->db->bind(':price', $input['price']);
        $this->db->bind(':sale_price', $input['sale_price'] ?? null);
        $this->db->bind(':sku', $sku);
        $this->db->bind(':stock_quantity', $input['stock_quantity']);
        $this->db->bind(':size', $input['size'] ?? null);
        $this->db->bind(':color', $input['color'] ?? null);
        $this->db->bind(':location', $input['location'] ?? null);
        $this->db->bind(':image', $input['image'] ?? null);
        $this->db->bind(':status', $input['status'] ?? 'draft');
        $this->db->bind(':is_featured', $input['is_featured'] ?? 0);
        
        if ($this->db->execute()) {
            $productId = $this->db->lastInsertId();
            return $this->success('Product created successfully', ['id' => $productId]);
        }
        
        return $this->error('Failed to create product');
    }
    
    private function updateProduct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
            return $this->error('Seller authentication required', 401);
        }
        
        $id = $_GET['id'] ?? '';
        if (!$id) {
            return $this->error('Product ID required');
        }
        
        $this->db->query('SELECT seller_id FROM products WHERE id = :id');
        $this->db->bind(':id', $id);
        $product = $this->db->single();
        
        if (!$product || $product['seller_id'] != $_SESSION['user_id']) {
            return $this->error('Product not found or access denied', 403);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $this->db->query('UPDATE products SET 
                          name = :name, description = :description, price = :price, sale_price = :sale_price,
                          category_id = :category_id, stock_quantity = :stock_quantity, size = :size, 
                          color = :color, location = :location, image = :image, status = :status, 
                          is_featured = :is_featured, updated_at = CURRENT_TIMESTAMP
                          WHERE id = :id');
        
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $input['name']);
        $this->db->bind(':description', $input['description']);
        $this->db->bind(':price', $input['price']);
        $this->db->bind(':sale_price', $input['sale_price'] ?? null);
        $this->db->bind(':category_id', $input['category_id']);
        $this->db->bind(':stock_quantity', $input['stock_quantity']);
        $this->db->bind(':size', $input['size'] ?? null);
        $this->db->bind(':color', $input['color'] ?? null);
        $this->db->bind(':location', $input['location'] ?? null);
        $this->db->bind(':image', $input['image'] ?? null);
        $this->db->bind(':status', $input['status'] ?? 'draft');
        $this->db->bind(':is_featured', $input['is_featured'] ?? 0);
        
        if ($this->db->execute()) {
            return $this->success('Product updated successfully');
        }
        
        return $this->error('Failed to update product');
    }
    
    private function deleteProduct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
            return $this->error('Seller authentication required', 401);
        }
        
        $id = $_GET['id'] ?? '';
        if (!$id) {
            return $this->error('Product ID required');
        }
        
        $this->db->query('SELECT seller_id FROM products WHERE id = :id');
        $this->db->bind(':id', $id);
        $product = $this->db->single();
        
        if (!$product || $product['seller_id'] != $_SESSION['user_id']) {
            return $this->error('Product not found or access denied', 403);
        }
        
        $this->db->query('DELETE FROM products WHERE id = :id');
        $this->db->bind(':id', $id);
        
        if ($this->db->execute()) {
            return $this->success('Product deleted successfully');
        }
        
        return $this->error('Failed to delete product');
    }
    
    private function generateSKU() {
        return 'LFL-' . strtoupper(substr(uniqid(), -8));
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

$api = new ProductsAPI();
$api->handleRequest();
?>
