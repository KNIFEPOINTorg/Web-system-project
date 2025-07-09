<?php
/**
 * Public Products Handler
 * Serves product data for public pages (collections, sales)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Product.php';

$action = $_GET['action'] ?? '';
$product = new Product();

switch ($action) {
    case 'get_collections':
        handleGetCollections($product);
        break;
    case 'get_sales':
        handleGetSales($product);
        break;
    case 'get_categories':
        handleGetCategories($product);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

/**
 * Get products for collections page
 */
function handleGetCollections($product) {
    try {
        error_log("🔄 Collections API called");
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $category = $_GET['category'] ?? null;

        error_log("📊 Fetching products with limit: $limit, offset: $offset, category: $category");
        $products = $product->getPublicProducts($limit, $offset, $category, false);
        error_log("📊 Retrieved " . count($products) . " products from database");
        
        // Format products for frontend with ETB currency
        $formattedProducts = array_map(function($p) {
            return [
                'id' => (int)$p['id'],
                'title' => $p['name'],
                'description' => $p['description'] ?: 'Authentic Ethiopian product',
                'price' => (float)$p['price'],
                'sale_price' => $p['sale_price'] ? (float)$p['sale_price'] : null,
                'price_formatted' => 'ETB ' . number_format($p['price'], 2),
                'sale_price_formatted' => $p['sale_price'] ? 'ETB ' . number_format($p['sale_price'], 2) : null,
                'currency' => 'ETB',
                'currency_symbol' => 'ETB',
                'image' => $p['image'] ?: 'https://via.placeholder.com/400x300?text=No+Image',
                'category' => $p['category_name'] ?: 'General',
                'rating' => 5, // Default rating - can be enhanced later
                'seller' => trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?: 'Ethiopian Artisan',
                'location' => $p['location'] ?: 'Ethiopia',
                'stock' => (int)($p['stock_quantity'] ?? 0),
                'tags' => $p['tags'] ?: '',
                'created_at' => $p['created_at'],
                'region' => getRegionFromLocation($p['location'] ?? 'Ethiopia')
            ];
        }, $products);
        
        echo json_encode([
            'success' => true,
            'products' => $formattedProducts,
            'count' => count($formattedProducts)
        ]);
        
    } catch (Exception $e) {
        error_log("Get collections error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to fetch products']);
    }
}

/**
 * Get products for sales page (only products with sale prices)
 */
function handleGetSales($product) {
    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $category = $_GET['category'] ?? null;
        
        $products = $product->getPublicProducts($limit, $offset, $category, true);
        
        // Format products for frontend with sale information and ETB currency
        $formattedProducts = array_map(function($p) {
            $originalPrice = (float)$p['price'];
            $salePrice = (float)$p['sale_price'];
            $discount = $originalPrice > 0 ? round((($originalPrice - $salePrice) / $originalPrice) * 100) : 0;

            return [
                'id' => (int)$p['id'],
                'title' => $p['name'],
                'description' => $p['description'] ?: 'Authentic Ethiopian product on sale',
                'currentPrice' => $salePrice,
                'originalPrice' => $originalPrice,
                'currentPrice_formatted' => 'ETB ' . number_format($salePrice, 2),
                'originalPrice_formatted' => 'ETB ' . number_format($originalPrice, 2),
                'discount' => $discount,
                'currency' => 'ETB',
                'currency_symbol' => 'ETB',
                'image' => $p['image'] ?: 'https://via.placeholder.com/400x300?text=No+Image',
                'category' => $p['category_name'] ?: 'General',
                'rating' => 5, // Default rating - can be enhanced later
                'seller' => trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?: 'Ethiopian Artisan',
                'location' => $p['location'] ?: 'Ethiopia',
                'stock' => (int)($p['stock_quantity'] ?? 0),
                'tags' => $p['tags'] ?: '',
                'created_at' => $p['created_at'],
                'onSale' => true,
                'salePrice' => $salePrice
            ];
        }, $products);
        
        echo json_encode([
            'success' => true,
            'products' => $formattedProducts,
            'count' => count($formattedProducts)
        ]);
        
    } catch (Exception $e) {
        error_log("Get sales error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to fetch sale products']);
    }
}

/**
 * Get available categories
 */
function handleGetCategories($product) {
    try {
        // Get categories from database
        $db = getDB();
        $stmt = $db->prepare("SELECT DISTINCT name FROM categories WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
        
    } catch (Exception $e) {
        error_log("Get categories error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to fetch categories']);
    }
}

/**
 * Map location to region for filtering
 */
function getRegionFromLocation($location) {
    $location = strtolower($location);
    
    $regionMap = [
        'addis ababa' => 'addis-ababa',
        'dire dawa' => 'dire-dawa',
        'mekelle' => 'mekelle',
        'gondar' => 'gondar',
        'awassa' => 'awassa',
        'bahir dar' => 'bahir-dar',
        'jimma' => 'jimma',
        'harar' => 'harar',
        'yirgacheffe' => 'yirgacheffe',
        'lalibela' => 'lalibela',
        'axum' => 'axum'
    ];
    
    foreach ($regionMap as $city => $region) {
        if (strpos($location, $city) !== false) {
            return $region;
        }
    }
    
    return 'addis-ababa'; // Default region
}


?>
