<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database/config.php';

class ReviewsAPI {
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
                    case 'product':
                        return $this->getProductReviews();
                    default:
                        return $this->getProductReviews();
                }
            case 'POST':
                return $this->createReview();
            case 'PUT':
                return $this->updateReview();
            case 'DELETE':
                return $this->deleteReview();
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function getProductReviews() {
        $productId = $_GET['product_id'] ?? '';
        
        if (!$productId) {
            return $this->error('Product ID required');
        }
        
        $this->db->query('SELECT r.*, u.name as user_name 
                          FROM reviews r
                          JOIN users u ON r.user_id = u.id
                          WHERE r.product_id = :product_id AND r.is_approved = 1
                          ORDER BY r.created_at DESC');
        $this->db->bind(':product_id', $productId);
        $reviews = $this->db->resultset();
        
        foreach ($reviews as &$review) {
            $review['rating'] = (int)$review['rating'];
        }
        
        return $this->success('Reviews retrieved', $reviews);
    }
    
    private function createReview() {
        if (!isset($_SESSION['user_id'])) {
            return $this->error('Authentication required', 401);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $required = ['product_id', 'rating'];
        foreach ($required as $field) {
            if (!isset($input[$field])) {
                return $this->error("$field is required");
            }
        }
        
        $rating = (int)$input['rating'];
        if ($rating < 1 || $rating > 5) {
            return $this->error('Rating must be between 1 and 5');
        }
        
        $this->db->query('SELECT id FROM reviews WHERE user_id = :user_id AND product_id = :product_id');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $this->db->bind(':product_id', $input['product_id']);
        if ($this->db->single()) {
            return $this->error('You have already reviewed this product');
        }
        
        $this->db->query('INSERT INTO reviews (product_id, user_id, rating, title, comment, is_verified) 
                          VALUES (:product_id, :user_id, :rating, :title, :comment, :is_verified)');
        
        $this->db->bind(':product_id', $input['product_id']);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $this->db->bind(':rating', $rating);
        $this->db->bind(':title', $input['title'] ?? null);
        $this->db->bind(':comment', $input['comment'] ?? null);
        $this->db->bind(':is_verified', 0);
        
        if ($this->db->execute()) {
            return $this->success('Review submitted successfully');
        }
        
        return $this->error('Failed to submit review');
    }
    
    private function updateReview() {
        if (!isset($_SESSION['user_id'])) {
            return $this->error('Authentication required', 401);
        }
        
        $id = $_GET['id'] ?? '';
        if (!$id) {
            return $this->error('Review ID required');
        }
        
        $this->db->query('SELECT user_id FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        $review = $this->db->single();
        
        if (!$review || $review['user_id'] != $_SESSION['user_id']) {
            return $this->error('Review not found or access denied', 403);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $rating = (int)$input['rating'];
        if ($rating < 1 || $rating > 5) {
            return $this->error('Rating must be between 1 and 5');
        }
        
        $this->db->query('UPDATE reviews SET rating = :rating, title = :title, comment = :comment, 
                          updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        
        $this->db->bind(':id', $id);
        $this->db->bind(':rating', $rating);
        $this->db->bind(':title', $input['title'] ?? null);
        $this->db->bind(':comment', $input['comment'] ?? null);
        
        if ($this->db->execute()) {
            return $this->success('Review updated successfully');
        }
        
        return $this->error('Failed to update review');
    }
    
    private function deleteReview() {
        if (!isset($_SESSION['user_id'])) {
            return $this->error('Authentication required', 401);
        }
        
        $id = $_GET['id'] ?? '';
        if (!$id) {
            return $this->error('Review ID required');
        }
        
        $this->db->query('SELECT user_id FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        $review = $this->db->single();
        
        if (!$review || $review['user_id'] != $_SESSION['user_id']) {
            return $this->error('Review not found or access denied', 403);
        }
        
        $this->db->query('DELETE FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        
        if ($this->db->execute()) {
            return $this->success('Review deleted successfully');
        }
        
        return $this->error('Failed to delete review');
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

$api = new ReviewsAPI();
$api->handleRequest();
?>
