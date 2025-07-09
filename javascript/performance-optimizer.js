/**
 * Performance Optimization Module for LFLshop
 * Implements caching, lazy loading, and performance monitoring
 */

class PerformanceOptimizer {
    constructor() {
        this.cache = new Map();
        this.imageObserver = null;
        this.performanceMetrics = {
            apiCalls: 0,
            cacheHits: 0,
            cacheMisses: 0,
            loadTimes: []
        };
        
        this.init();
    }

    init() {
        this.setupImageLazyLoading();
        this.setupAPICache();
        this.setupPerformanceMonitoring();
        this.optimizeAssets();
    }

    /**
     * Setup lazy loading for images
     */
    setupImageLazyLoading() {
        if ('IntersectionObserver' in window) {
            this.imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        this.imageObserver.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            // Observe all images with data-src
            this.observeImages();
        } else {
            // Fallback for older browsers
            this.loadAllImages();
        }
    }

    /**
     * Observe images for lazy loading
     */
    observeImages() {
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            this.imageObserver.observe(img);
        });
    }

    /**
     * Load a single image
     */
    loadImage(img) {
        const src = img.dataset.src;
        if (src) {
            img.src = src;
            img.classList.add('loaded');
            img.removeAttribute('data-src');
        }
    }

    /**
     * Load all images (fallback)
     */
    loadAllImages() {
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => this.loadImage(img));
    }

    /**
     * Setup API response caching
     */
    setupAPICache() {
        // Override ApiHelper methods to add caching
        if (window.ApiHelper) {
            const originalGet = ApiHelper.get;
            const originalPost = ApiHelper.post;

            ApiHelper.get = async (endpoint, params = {}) => {
                const cacheKey = this.generateCacheKey(endpoint, params);
                
                // Check cache first
                if (this.cache.has(cacheKey)) {
                    this.performanceMetrics.cacheHits++;
                    return this.cache.get(cacheKey);
                }

                // Make API call
                this.performanceMetrics.cacheMisses++;
                this.performanceMetrics.apiCalls++;
                
                const startTime = performance.now();
                const result = await originalGet.call(ApiHelper, endpoint, params);
                const endTime = performance.now();
                
                this.performanceMetrics.loadTimes.push(endTime - startTime);

                // Cache the result (with TTL)
                this.setCacheWithTTL(cacheKey, result, 5 * 60 * 1000); // 5 minutes

                return result;
            };

            // Don't cache POST requests, but monitor performance
            ApiHelper.post = async (endpoint, data = {}) => {
                this.performanceMetrics.apiCalls++;
                
                const startTime = performance.now();
                const result = await originalPost.call(ApiHelper, endpoint, data);
                const endTime = performance.now();
                
                this.performanceMetrics.loadTimes.push(endTime - startTime);

                return result;
            };
        }
    }

    /**
     * Generate cache key from endpoint and parameters
     */
    generateCacheKey(endpoint, params) {
        return `${endpoint}:${JSON.stringify(params)}`;
    }

    /**
     * Set cache with TTL (Time To Live)
     */
    setCacheWithTTL(key, value, ttl) {
        const expiry = Date.now() + ttl;
        this.cache.set(key, {
            value,
            expiry
        });

        // Clean up expired entries
        setTimeout(() => {
            const cached = this.cache.get(key);
            if (cached && Date.now() > cached.expiry) {
                this.cache.delete(key);
            }
        }, ttl);
    }

    /**
     * Get cached value if not expired
     */
    getCachedValue(key) {
        const cached = this.cache.get(key);
        if (cached) {
            if (Date.now() < cached.expiry) {
                return cached.value;
            } else {
                this.cache.delete(key);
            }
        }
        return null;
    }

    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        // Monitor page load performance
        window.addEventListener('load', () => {
            if ('performance' in window) {
                const navigation = performance.getEntriesByType('navigation')[0];
                const loadTime = navigation.loadEventEnd - navigation.loadEventStart;
                
                console.log(`Page load time: ${loadTime.toFixed(2)}ms`);
                
                // Log performance metrics
                this.logPerformanceMetrics();
            }
        });

        // Monitor long tasks
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.duration > 50) {
                        console.warn(`Long task detected: ${entry.duration.toFixed(2)}ms`);
                    }
                });
            });
            
            observer.observe({ entryTypes: ['longtask'] });
        }
    }

    /**
     * Optimize assets loading
     */
    optimizeAssets() {
        // Preload critical resources
        this.preloadCriticalResources();
        
        // Defer non-critical scripts
        this.deferNonCriticalScripts();
        
        // Optimize font loading
        this.optimizeFontLoading();
    }

    /**
     * Preload critical resources
     */
    preloadCriticalResources() {
        const criticalResources = [
            '../css/design-system.css',
            '../css/styles.css',
            '../javascript/config.js'
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource;
            link.as = resource.endsWith('.css') ? 'style' : 'script';
            document.head.appendChild(link);
        });
    }

    /**
     * Defer non-critical scripts
     */
    deferNonCriticalScripts() {
        const nonCriticalScripts = document.querySelectorAll('script[data-defer]');
        
        nonCriticalScripts.forEach(script => {
            script.defer = true;
        });
    }

    /**
     * Optimize font loading
     */
    optimizeFontLoading() {
        // Use font-display: swap for better performance
        const style = document.createElement('style');
        style.textContent = `
            @font-face {
                font-family: 'Segoe UI';
                font-display: swap;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
        console.log('Cache cleared');
    }

    /**
     * Get performance metrics
     */
    getPerformanceMetrics() {
        const avgLoadTime = this.performanceMetrics.loadTimes.length > 0
            ? this.performanceMetrics.loadTimes.reduce((a, b) => a + b, 0) / this.performanceMetrics.loadTimes.length
            : 0;

        return {
            ...this.performanceMetrics,
            averageLoadTime: avgLoadTime.toFixed(2),
            cacheHitRate: this.performanceMetrics.cacheHits / (this.performanceMetrics.cacheHits + this.performanceMetrics.cacheMisses) * 100
        };
    }

    /**
     * Log performance metrics
     */
    logPerformanceMetrics() {
        const metrics = this.getPerformanceMetrics();
        console.group('Performance Metrics');
        console.log('API Calls:', metrics.apiCalls);
        console.log('Cache Hits:', metrics.cacheHits);
        console.log('Cache Misses:', metrics.cacheMisses);
        console.log('Cache Hit Rate:', `${metrics.cacheHitRate.toFixed(1)}%`);
        console.log('Average Load Time:', `${metrics.averageLoadTime}ms`);
        console.groupEnd();
    }

    /**
     * Optimize images for better performance
     */
    optimizeImages() {
        const images = document.querySelectorAll('img');
        
        images.forEach(img => {
            // Add loading="lazy" for native lazy loading
            if (!img.hasAttribute('loading')) {
                img.loading = 'lazy';
            }
            
            // Add proper alt text if missing
            if (!img.alt) {
                img.alt = 'LFLshop product image';
            }
            
            // Optimize image dimensions
            if (img.naturalWidth > 800) {
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
            }
        });
    }

    /**
     * Debounce function for performance
     */
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Throttle function for performance
     */
    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Initialize performance optimizer
const performanceOptimizer = new PerformanceOptimizer();

// Make it globally available
window.PerformanceOptimizer = PerformanceOptimizer;
window.performanceOptimizer = performanceOptimizer;

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceOptimizer;
}
