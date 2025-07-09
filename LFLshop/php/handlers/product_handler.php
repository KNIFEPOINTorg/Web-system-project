<?php
/**
 * Product Handler for LFLshop
 * Handles product management operations
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';
requireAuth();

// Set JSON response header
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_product':
        requireAdmin();
        handleCreateProduct();
        break;
    case 'update_product':
        requireAdmin();
        handleUpdateProduct();
        break;
    case 'delete_product':
        requireAdmin();
        handleDeleteProduct();
        break;
    case 'get_products':
        handleGetProducts();
        break;
    case 'get_product':
        handleGetProduct();
        break;
    case 'search_products':
        handleSearchProducts();
        break;
    case 'get_categories':
        handleGetCategories();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Invalid action']]);
        break;
}

/**
 * Create new product
 */
function handleCreateProduct() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        // Sanitize input
        $productData = [
            'name' => Security::sanitizeInput($_POST['name'] ?? ''),
            'description' => Security::sanitizeInput($_POST['description'] ?? ''),
            'short_description' => Security::sanitizeInput($_POST['short_description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'sku' => Security::sanitizeInput($_POST['sku'] ?? ''),
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
            'dimensions' => Security::sanitizeInput($_POST['dimensions'] ?? ''),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'status' => Security::sanitizeInput($_POST['status'] ?? 'active'),
            'seller_id' => getCurrentUserId()
        ];
        
        // Validate required fields
        $errors = [];
        if (empty($productData['name'])) {
            $errors[] = "Product name is required";
        }
        if (empty($productData['description'])) {
            $errors[] = "Product description is required";
        }
        if ($productData['price'] <= 0) {
            $errors[] = "Product price must be greater than 0";
        }
        if ($productData['category_id'] <= 0) {
            $errors[] = "Please select a category";
        }
        
        // Check for security threats
        foreach (['name', 'description', 'short_description', 'sku', 'dimensions'] as $field) {
            if (Security::detectSQLInjection($productData[$field]) || Security::detectXSS($productData[$field])) {
                Security::logSecurityEvent('product_create_security_threat', ['field' => $field]);
                $errors[] = "Invalid input detected in $field";
            }
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }
        
        // Handle image uploads
        $images = [];
        if (!empty($_FILES['images'])) {
            $uploadResult = handleImageUploads($_FILES['images']);
            if (!$uploadResult['success']) {
                http_response_code(400);
                echo json_encode($uploadResult);
                return;
            }
            $images = $uploadResult['images'];
        }
        
        $productData['images'] = $images;
        
        // Create product
        $product = new Product();
        $result = $product->createProduct($productData);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $result['product_id']
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Create product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to create product']]);
    }
}

/**
 * Update product
 */
function handleUpdateProduct() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid product ID']]);
            return;
        }
        
        // Sanitize input
        $productData = [
            'name' => Security::sanitizeInput($_POST['name'] ?? ''),
            'description' => Security::sanitizeInput($_POST['description'] ?? ''),
            'short_description' => Security::sanitizeInput($_POST['short_description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'sku' => Security::sanitizeInput($_POST['sku'] ?? ''),
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
            'dimensions' => Security::sanitizeInput($_POST['dimensions'] ?? ''),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'status' => Security::sanitizeInput($_POST['status'] ?? 'active')
        ];
        
        // Remove empty values
        $productData = array_filter($productData, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // Update product
        $product = new Product();
        $result = $product->updateProduct($productId, $productData);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Product updated successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Update product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to update product']]);
    }
}

/**
 * Delete product
 */
function handleDeleteProduct() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Invalid product ID']]);
            return;
        }
        
        // Delete product
        $product = new Product();
        $result = $product->deleteProduct($productId);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Delete product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to delete product']]);
    }
}

/**
 * Get products with pagination and filters
 */
function handleGetProducts() {
    try {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 12);
        $filters = [
            'category_id' => !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null,
            'search' => Security::sanitizeInput($_GET['search'] ?? ''),
            'min_price' => !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null,
            'max_price' => !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null,
            'featured' => isset($_GET['featured']) ? true : null
        ];
        
        // Remove null filters
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        $product = new Product();
        $result = $product->getProducts($page, $limit, $filters);
        
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
        
    } catch (Exception $e) {
        error_log("Get products error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get products']]);
    }
}

/**
 * Get single product
 */
function handleGetProduct() {
    try {
        $productId = (int)($_GET['id'] ?? 0);
        $slug = Security::sanitizeInput($_GET['slug'] ?? '');
        
        $product = new Product();
        
        if ($productId > 0) {
            $result = $product->getProductById($productId);
        } elseif (!empty($slug)) {
            $result = $product->getProductBySlug($slug);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Product ID or slug required']]);
            return;
        }
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'product' => $result
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'errors' => ['Product not found']]);
        }
        
    } catch (Exception $e) {
        error_log("Get product error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get product']]);
    }
}

/**
 * Search products
 */
function handleSearchProducts() {
    try {
        $query = Security::sanitizeInput($_GET['q'] ?? '');
        $limit = (int)($_GET['limit'] ?? 10);
        
        if (empty($query)) {
            echo json_encode([
                'success' => true,
                'products' => []
            ]);
            return;
        }
        
        $product = new Product();
        $results = $product->searchProducts($query, $limit);
        
        echo json_encode([
            'success' => true,
            'products' => $results
        ]);
        
    } catch (Exception $e) {
        error_log("Search products error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Search failed']]);
    }
}

/**
 * Get categories
 */
function handleGetCategories() {
    try {
        $product = new Product();
        $categories = $product->getCategories();
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
        
    } catch (Exception $e) {
        error_log("Get categories error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get categories']]);
    }
}

/**
 * Handle image uploads
 */
function handleImageUploads($files) {
    try {
        $images = [];
        $uploadDir = UPLOAD_PATH . 'products/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Handle multiple files
        if (is_array($files['tmp_name'])) {
            for ($i = 0; $i < count($files['tmp_name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $files['name'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'size' => $files['size'][$i],
                        'error' => $files['error'][$i]
                    ];
                    
                    $errors = Security::validateFileUpload($file);
                    if (!empty($errors)) {
                        return ['success' => false, 'errors' => $errors];
                    }
                    
                    // Generate unique filename
                    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = 'product_' . time() . '_' . $i . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $filePath)) {
                        $images[] = [
                            'url' => 'uploads/products/' . $fileName,
                            'alt_text' => ''
                        ];
                    }
                }
            }
        } else {
            // Single file
            if ($files['error'] === UPLOAD_ERR_OK) {
                $errors = Security::validateFileUpload($files);
                if (!empty($errors)) {
                    return ['success' => false, 'errors' => $errors];
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($files['name'], PATHINFO_EXTENSION);
                $fileName = 'product_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($files['tmp_name'], $filePath)) {
                    $images[] = [
                        'url' => 'uploads/products/' . $fileName,
                        'alt_text' => ''
                    ];
                }
            }
        }
        
        return ['success' => true, 'images' => $images];
        
    } catch (Exception $e) {
        error_log("Image upload error: " . $e->getMessage());
        return ['success' => false, 'errors' => ['Image upload failed']];
    }
}
?>
