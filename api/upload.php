<?php
/**
 * File Upload API for LFLshop
 * Handles secure image uploads for products and profiles
 */

session_start();
header('Content-Type: application/json');

require_once '../database/config.php';
require_once '../includes/security.php';

class UploadAPI {
    private $db;
    private $uploadDir = '../uploads/';
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5242880; // 5MB
    
    public function __construct() {
        $this->db = new Database();
        $this->ensureUploadDirectory();
    }
    
    public function handleRequest() {
        // Require authentication
        SecurityHelper::requireAuth();
        
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'product_image':
                return $this->uploadProductImage();
            case 'profile_image':
                return $this->uploadProfileImage();
            case 'delete_image':
                return $this->deleteImage();
            default:
                return $this->error('Invalid action');
        }
    }
    
    private function uploadProductImage() {
        if (!isset($_FILES['image'])) {
            return $this->error('No image file provided');
        }
        
        $file = $_FILES['image'];
        $productId = (int)($_POST['product_id'] ?? 0);
        $isPrimary = isset($_POST['is_primary']) && $_POST['is_primary'] === 'true';
        
        // Validate file
        $validation = SecurityHelper::validateFileUpload($file, $this->allowedTypes, $this->maxFileSize);
        if (!$validation['valid']) {
            return $this->error($validation['error']);
        }
        
        // Check if user owns the product (for sellers) or is admin
        if ($_SESSION['user_type'] === 'seller') {
            $this->db->query('SELECT id FROM products WHERE id = :product_id AND seller_id = :seller_id');
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':seller_id', $_SESSION['user_id']);
            
            if (!$this->db->single()) {
                return $this->error('Product not found or access denied', 403);
            }
        }
        
        try {
            // Generate secure filename
            $filename = SecurityHelper::generateSecureFilename($file['name']);
            $uploadPath = $this->uploadDir . 'products/' . $filename;
            
            // Ensure products directory exists
            $productDir = $this->uploadDir . 'products/';
            if (!is_dir($productDir)) {
                mkdir($productDir, 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return $this->error('Failed to upload file');
            }
            
            // Generate different sizes
            $this->generateImageSizes($uploadPath, $filename);
            
            // Save to database
            $imageUrl = 'uploads/products/' . $filename;
            
            if ($productId > 0) {
                // If setting as primary, unset other primary images
                if ($isPrimary) {
                    $this->db->query('UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id');
                    $this->db->bind(':product_id', $productId);
                    $this->db->execute();
                    
                    // Also update main product image
                    $this->db->query('UPDATE products SET image = :image WHERE id = :product_id');
                    $this->db->bind(':image', $imageUrl);
                    $this->db->bind(':product_id', $productId);
                    $this->db->execute();
                }
                
                // Insert into product_images table
                $this->db->query('INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (:product_id, :image_url, :is_primary, :sort_order)');
                $this->db->bind(':product_id', $productId);
                $this->db->bind(':image_url', $imageUrl);
                $this->db->bind(':is_primary', $isPrimary ? 1 : 0);
                $this->db->bind(':sort_order', 0);
                $this->db->execute();
                
                $imageId = $this->db->lastInsertId();
            } else {
                $imageId = null;
            }
            
            return $this->success('Image uploaded successfully', [
                'image_id' => $imageId,
                'image_url' => $imageUrl,
                'filename' => $filename,
                'sizes' => [
                    'original' => $imageUrl,
                    'large' => 'uploads/products/large_' . $filename,
                    'medium' => 'uploads/products/medium_' . $filename,
                    'thumb' => 'uploads/products/thumb_' . $filename
                ]
            ]);
            
        } catch (Exception $e) {
            return $this->error('Upload failed: ' . $e->getMessage());
        }
    }
    
    private function uploadProfileImage() {
        if (!isset($_FILES['image'])) {
            return $this->error('No image file provided');
        }
        
        $file = $_FILES['image'];
        
        // Validate file
        $validation = SecurityHelper::validateFileUpload($file, $this->allowedTypes, $this->maxFileSize);
        if (!$validation['valid']) {
            return $this->error($validation['error']);
        }
        
        try {
            // Generate secure filename
            $filename = SecurityHelper::generateSecureFilename($file['name']);
            $uploadPath = $this->uploadDir . 'profiles/' . $filename;
            
            // Ensure profiles directory exists
            $profileDir = $this->uploadDir . 'profiles/';
            if (!is_dir($profileDir)) {
                mkdir($profileDir, 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return $this->error('Failed to upload file');
            }
            
            // Generate profile image sizes
            $this->generateProfileImageSizes($uploadPath, $filename);
            
            // Update user profile
            $imageUrl = 'uploads/profiles/' . $filename;
            $this->db->query('UPDATE users SET profile_image = :image WHERE id = :user_id');
            $this->db->bind(':image', $imageUrl);
            $this->db->bind(':user_id', $_SESSION['user_id']);
            $this->db->execute();
            
            return $this->success('Profile image uploaded successfully', [
                'image_url' => $imageUrl,
                'filename' => $filename
            ]);
            
        } catch (Exception $e) {
            return $this->error('Upload failed: ' . $e->getMessage());
        }
    }
    
    private function deleteImage() {
        $imageId = (int)($_POST['image_id'] ?? 0);
        $imageType = $_POST['image_type'] ?? 'product';
        
        if (!$imageId) {
            return $this->error('Image ID required');
        }
        
        try {
            if ($imageType === 'product') {
                // Get image info
                $this->db->query('SELECT pi.*, p.seller_id FROM product_images pi JOIN products p ON pi.product_id = p.id WHERE pi.id = :image_id');
                $this->db->bind(':image_id', $imageId);
                $image = $this->db->single();
                
                if (!$image) {
                    return $this->error('Image not found', 404);
                }
                
                // Check permissions
                if ($_SESSION['user_type'] === 'seller' && $image['seller_id'] != $_SESSION['user_id']) {
                    return $this->error('Access denied', 403);
                }
                
                // Delete file
                $this->deleteImageFiles($image['image_url']);
                
                // Delete from database
                $this->db->query('DELETE FROM product_images WHERE id = :image_id');
                $this->db->bind(':image_id', $imageId);
                $this->db->execute();
                
                return $this->success('Image deleted successfully');
            }
            
        } catch (Exception $e) {
            return $this->error('Delete failed: ' . $e->getMessage());
        }
    }
    
    private function generateImageSizes($originalPath, $filename) {
        $sizes = [
            'large' => ['width' => 800, 'height' => 600],
            'medium' => ['width' => 400, 'height' => 300],
            'thumb' => ['width' => 150, 'height' => 150]
        ];
        
        foreach ($sizes as $sizeName => $dimensions) {
            $this->resizeImage($originalPath, $this->uploadDir . 'products/' . $sizeName . '_' . $filename, $dimensions['width'], $dimensions['height']);
        }
    }
    
    private function generateProfileImageSizes($originalPath, $filename) {
        $sizes = [
            'large' => ['width' => 200, 'height' => 200],
            'medium' => ['width' => 100, 'height' => 100],
            'thumb' => ['width' => 50, 'height' => 50]
        ];
        
        foreach ($sizes as $sizeName => $dimensions) {
            $this->resizeImage($originalPath, $this->uploadDir . 'profiles/' . $sizeName . '_' . $filename, $dimensions['width'], $dimensions['height']);
        }
    }
    
    private function resizeImage($sourcePath, $destPath, $newWidth, $newHeight) {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) return false;
        
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Create source image
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        // Calculate aspect ratio
        $aspectRatio = $sourceWidth / $sourceHeight;
        
        if ($newWidth / $newHeight > $aspectRatio) {
            $newWidth = $newHeight * $aspectRatio;
        } else {
            $newHeight = $newWidth / $aspectRatio;
        }
        
        // Create destination image
        $destImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
        
        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($destImage, $destPath, 85);
                break;
            case 'image/png':
                imagepng($destImage, $destPath);
                break;
            case 'image/gif':
                imagegif($destImage, $destPath);
                break;
        }
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        return true;
    }
    
    private function deleteImageFiles($imageUrl) {
        $filename = basename($imageUrl);
        $directory = dirname($imageUrl);
        
        // Delete original and all sizes
        $files = [
            $imageUrl,
            $directory . '/large_' . $filename,
            $directory . '/medium_' . $filename,
            $directory . '/thumb_' . $filename
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    private function ensureUploadDirectory() {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Create .htaccess for security
        $htaccessContent = "Options -Indexes\n";
        $htaccessContent .= "AddType text/plain .php .php3 .phtml .pht\n";
        $htaccessContent .= "<FilesMatch \"\.(php|php3|phtml|pht)$\">\n";
        $htaccessContent .= "    Deny from all\n";
        $htaccessContent .= "</FilesMatch>\n";
        
        file_put_contents($this->uploadDir . '.htaccess', $htaccessContent);
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

$api = new UploadAPI();
$api->handleRequest();
?>