<?php
/**
 * Product Class for LFLshop
 * Handles product management, CRUD operations
 */

class Product {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get all products with pagination
     */
    public function getProducts($page = 1, $limit = 12, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            $whereConditions = ["p.status = 'active'"];
            $params = [];
            
            // Apply filters
            if (!empty($filters['category_id'])) {
                $whereConditions[] = "p.category_id = ?";
                $params[] = $filters['category_id'];
            }
            
            if (!empty($filters['search'])) {
                $whereConditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['min_price'])) {
                $whereConditions[] = "p.price >= ?";
                $params[] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $whereConditions[] = "p.price <= ?";
                $params[] = $filters['max_price'];
            }
            
            if (!empty($filters['featured'])) {
                $whereConditions[] = "p.featured = 1";
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE $whereClause
            ";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $totalProducts = $countStmt->fetchColumn();
            
            // Get products
            $sql = "
                SELECT p.*, c.name as category_name,
                       u.first_name as seller_first_name, u.last_name as seller_last_name,
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE $whereClause
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
            
            return [
                'products' => $products,
                'total' => $totalProducts,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalProducts / $limit)
            ];
            
        } catch (Exception $e) {
            error_log("Get products error: " . $e->getMessage());
            return ['products' => [], 'total' => 0, 'page' => 1, 'limit' => $limit, 'total_pages' => 0];
        }
    }

    /**
     * Get products for public display (collections and sales pages)
     */
    public function getPublicProducts($limit = null, $offset = 0, $category = null, $onSale = false) {
        try {
            $sql = "SELECT p.*, c.name as category_name, u.first_name, u.last_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.seller_id = u.id
                    WHERE p.status = 'active'";

            $params = [];

            if ($category) {
                $sql .= " AND c.name = ?";
                $params[] = $category;
            }

            if ($onSale) {
                $sql .= " AND p.sale_price IS NOT NULL AND p.sale_price > 0 AND p.sale_price < p.price";
            }

            $sql .= " ORDER BY p.created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get public products error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get product by ID
     */
    public function getProductById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name,
                       u.first_name as seller_first_name, u.last_name as seller_last_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Get product images
                $stmt = $this->db->prepare("
                    SELECT * FROM product_images 
                    WHERE product_id = ? 
                    ORDER BY is_primary DESC, sort_order ASC
                ");
                $stmt->execute([$id]);
                $product['images'] = $stmt->fetchAll();
            }
            
            return $product;
            
        } catch (Exception $e) {
            error_log("Get product error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get product by slug
     */
    public function getProductBySlug($slug) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name as category_name,
                       u.first_name as seller_first_name, u.last_name as seller_last_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.seller_id = u.id
                WHERE p.slug = ? AND p.status = 'published'
            ");
            $stmt->execute([$slug]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Get product images
                $stmt = $this->db->prepare("
                    SELECT * FROM product_images 
                    WHERE product_id = ? 
                    ORDER BY is_primary DESC, sort_order ASC
                ");
                $stmt->execute([$product['id']]);
                $product['images'] = $stmt->fetchAll();
            }
            
            return $product;
            
        } catch (Exception $e) {
            error_log("Get product by slug error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new product
     */
    public function createProduct($productData) {
        try {
            $this->db->beginTransaction();
            
            // Validate required fields
            $required = ['name', 'description', 'price', 'category_id', 'seller_id'];
            foreach ($required as $field) {
                if (empty($productData[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            // Generate slug
            $slug = $this->generateSlug($productData['name']);
            
            // Insert product
            $stmt = $this->db->prepare("
                INSERT INTO products (
                    seller_id, category_id, name, slug, description, short_description,
                    price, sale_price, sku, stock_quantity, weight, dimensions, featured, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $productData['seller_id'],
                $productData['category_id'],
                $productData['name'],
                $slug,
                $productData['description'],
                $productData['short_description'] ?? null,
                $productData['price'],
                $productData['sale_price'] ?? null,
                $productData['sku'] ?? null,
                $productData['stock_quantity'] ?? 0,
                $productData['weight'] ?? null,
                $productData['dimensions'] ?? null,
                $productData['featured'] ?? 0,
                $productData['status'] ?? 'active'
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create product");
            }
            
            $productId = $this->db->lastInsertId();
            
            // Handle image uploads if provided
            if (!empty($productData['images'])) {
                $this->addProductImages($productId, $productData['images']);
            }
            
            $this->db->commit();
            return ['success' => true, 'product_id' => $productId];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Create product error: " . $e->getMessage());
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }
    
    /**
     * Update product
     */
    public function updateProduct($productId, $productData) {
        try {
            $this->db->beginTransaction();
            
            // Build update query
            $updateFields = [];
            $params = [];
            
            $allowedFields = [
                'category_id', 'name', 'description', 'short_description',
                'price', 'sale_price', 'sku', 'stock_quantity', 'weight',
                'dimensions', 'featured', 'status'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($productData[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $productData[$field];
                }
            }
            
            // Update slug if name changed
            if (isset($productData['name'])) {
                $updateFields[] = "slug = ?";
                $params[] = $this->generateSlug($productData['name']);
            }
            
            if (!empty($updateFields)) {
                $params[] = $productId;
                
                $stmt = $this->db->prepare("
                    UPDATE products 
                    SET " . implode(', ', $updateFields) . " 
                    WHERE id = ?
                ");
                $stmt->execute($params);
            }
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update product error: " . $e->getMessage());
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($productId) {
        try {
            $this->db->beginTransaction();
            
            // Delete product images
            $stmt = $this->db->prepare("DELETE FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
            
            // Delete product
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $result = $stmt->execute([$productId]);
            
            if (!$result) {
                throw new Exception("Failed to delete product");
            }
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Delete product error: " . $e->getMessage());
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }
    
    /**
     * Get categories
     */
    public function getCategories() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM categories 
                WHERE status = 'active' 
                ORDER BY sort_order ASC, name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search products
     */
    public function searchProducts($query, $limit = 10) {
        try {
            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare("
                SELECT p.id, p.name, p.price, p.slug,
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                WHERE (p.name LIKE ? OR p.description LIKE ?) 
                AND p.status = 'published'
                ORDER BY p.name ASC
                LIMIT ?
            ");
            $stmt->execute([$searchTerm, $searchTerm, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Search products error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate unique slug
     */
    private function generateSlug($name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Add product images
     */
    private function addProductImages($productId, $images) {
        foreach ($images as $index => $image) {
            $stmt = $this->db->prepare("
                INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_primary)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $productId,
                $image['url'],
                $image['alt_text'] ?? '',
                $index,
                $index === 0 ? 1 : 0 // First image is primary
            ]);
        }
    }
}
?>
