<?php
/**
 * Simple File-based Cache System for LFLshop
 * Handles caching of products, categories, and other data
 */

class CacheManager {
    private $cacheDir;
    private $defaultTTL = 3600; // 1 hour default
    
    public function __construct($cacheDir = '../cache/') {
        $this->cacheDir = $cacheDir;
        $this->ensureCacheDirectory();
    }
    
    /**
     * Store data in cache with performance tracking
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTTL;
        $filename = $this->getCacheFilename($key);

        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time(),
            'key' => $key,
            'size' => strlen(serialize($data))
        ];

        $success = file_put_contents($filename, serialize($cacheData), LOCK_EX);

        // Log cache performance
        if ($success) {
            $this->logCacheOperation('set', $key, true);
        }

        return $success !== false;
    }
    
    /**
     * Retrieve data from cache
     */
    public function get($key) {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return null;
        }
        
        $cacheData = unserialize($content);
        if (!$cacheData || !isset($cacheData['expires'])) {
            $this->delete($key);
            return null;
        }
        
        // Check if expired
        if (time() > $cacheData['expires']) {
            $this->delete($key);
            return null;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * Check if cache key exists and is valid
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * Delete cache entry
     */
    public function delete($key) {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Clear expired cache entries
     */
    public function clearExpired() {
        $files = glob($this->cacheDir . '*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content !== false) {
                $cacheData = unserialize($content);
                if ($cacheData && isset($cacheData['expires']) && time() > $cacheData['expires']) {
                    if (unlink($file)) {
                        $cleared++;
                    }
                }
            }
        }
        
        return $cleared;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        $files = glob($this->cacheDir . '*.cache');
        $totalSize = 0;
        $validEntries = 0;
        $expiredEntries = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $content = file_get_contents($file);
            if ($content !== false) {
                $cacheData = unserialize($content);
                if ($cacheData && isset($cacheData['expires'])) {
                    if (time() > $cacheData['expires']) {
                        $expiredEntries++;
                    } else {
                        $validEntries++;
                    }
                }
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'valid_entries' => $validEntries,
            'expired_entries' => $expiredEntries,
            'cache_dir' => $this->cacheDir
        ];
    }
    
    /**
     * Cache with callback - get from cache or execute callback and cache result
     */
    public function remember($key, $callback, $ttl = null) {
        $data = $this->get($key);
        
        if ($data !== null) {
            return $data;
        }
        
        $data = $callback();
        $this->set($key, $data, $ttl);
        
        return $data;
    }
    
    /**
     * Generate cache filename
     */
    private function getCacheFilename($key) {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . $safeKey . '.cache';
    }
    
    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectory() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        // Create .htaccess to protect cache directory
        $htaccessFile = $this->cacheDir . '.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, "Deny from all\n");
        }
    }

    /**
     * Log cache operations for performance monitoring
     */
    private function logCacheOperation($operation, $key, $success) {
        $logFile = $this->cacheDir . 'cache.log';
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'operation' => $operation,
            'key' => $key,
            'success' => $success,
            'memory_usage' => memory_get_usage(true)
        ];

        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Product Cache Helper
 */
class ProductCache {
    private $cache;
    
    public function __construct() {
        $this->cache = new CacheManager();
    }
    
    public function getFeaturedProducts($limit = 8) {
        return $this->cache->remember("featured_products_{$limit}", function() use ($limit) {
            require_once '../database/config.php';
            $db = new Database();
            
            $db->query("
                SELECT p.*, u.name as seller_name, c.name as category_name 
                FROM products p 
                JOIN users u ON p.seller_id = u.id 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.is_featured = 1 
                ORDER BY p.created_at DESC 
                LIMIT :limit
            ");
            $db->bind(':limit', $limit);
            
            return $db->resultset();
        }, 1800); // 30 minutes
    }
    
    public function getProductsByCategory($categoryId, $limit = 20) {
        return $this->cache->remember("category_products_{$categoryId}_{$limit}", function() use ($categoryId, $limit) {
            require_once '../database/config.php';
            $db = new Database();
            
            $db->query("
                SELECT p.*, u.name as seller_name, c.name as category_name 
                FROM products p 
                JOIN users u ON p.seller_id = u.id 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = :category_id AND p.status = 'active' 
                ORDER BY p.created_at DESC 
                LIMIT :limit
            ");
            $db->bind(':category_id', $categoryId);
            $db->bind(':limit', $limit);
            
            return $db->resultset();
        }, 1800); // 30 minutes
    }
    
    public function getCategories() {
        return $this->cache->remember('categories', function() {
            require_once '../database/config.php';
            $db = new Database();
            
            $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name");
            return $db->resultset();
        }, 3600); // 1 hour
    }
    
    public function clearProductCache($productId = null) {
        if ($productId) {
            $this->cache->delete("product_{$productId}");
        }
        
        // Clear related caches
        $this->cache->delete('featured_products_8');
        $this->cache->delete('categories');
        
        // Clear category caches (this is a simple approach, could be optimized)
        for ($i = 1; $i <= 20; $i++) {
            $this->cache->delete("category_products_{$i}_20");
        }
    }
}

/**
 * Global cache instance
 */
$cache = new CacheManager();
$productCache = new ProductCache();
?>