/**
 * LFLshop Ethiopian E-commerce Configuration
 */

// Determine base URL dynamically
const getBaseUrl = () => {
    const protocol = window.location.protocol;
    const host = window.location.host;
    const pathname = window.location.pathname;
    
    // Extract base path (everything before /html/ or /javascript/ etc.)
    let basePath = pathname;
    if (pathname.includes('/html/')) {
        basePath = pathname.substring(0, pathname.indexOf('/html/'));
    } else if (pathname.includes('/javascript/')) {
        basePath = pathname.substring(0, pathname.indexOf('/javascript/'));
    } else if (pathname.includes('/admin/')) {
        basePath = pathname.substring(0, pathname.indexOf('/admin/'));
    } else {
        // If we're in root, use current directory
        basePath = pathname.endsWith('/') ? pathname.slice(0, -1) : pathname.substring(0, pathname.lastIndexOf('/'));
    }
    
    return `${protocol}//${host}${basePath}`;
};

// Application configuration
const LFLConfig = {
    // Base URLs
    BASE_URL: getBaseUrl(),
    API_BASE_URL: `${getBaseUrl()}/api`,
    
    // API Endpoints
    API: {
        AUTH: '/auth.php',
        PRODUCTS: '/products.php',
        CART: '/cart.php',
        ORDERS: '/orders.php',
        PAYMENT: '/payment.php',
        USERS: '/users.php'
    },
    
    // Application settings
    APP: {
        NAME: 'LFLshop',
        VERSION: '1.0.0',
        CURRENCY: 'ETB',
        CURRENCY_SYMBOL: 'ETB',
        DEFAULT_LANGUAGE: 'en'
    },
    
    // File upload settings
    UPLOAD: {
        MAX_FILE_SIZE: 5 * 1024 * 1024, // 5MB
        ALLOWED_TYPES: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        UPLOAD_PATH: '/uploads'
    },
    
    // Pagination settings
    PAGINATION: {
        PRODUCTS_PER_PAGE: 12,
        ORDERS_PER_PAGE: 10,
        REVIEWS_PER_PAGE: 5
    }
};

// Helper functions for API calls
const ApiHelper = {
    /**
     * Get full API URL
     */
    getApiUrl: (endpoint, params = {}) => {
        let url = LFLConfig.API_BASE_URL + endpoint;
        
        // Add query parameters
        const queryParams = new URLSearchParams(params);
        if (queryParams.toString()) {
            url += '?' + queryParams.toString();
        }
        
        return url;
    },
    
    /**
     * Make API request with proper error handling
     */
    request: async (endpoint, options = {}) => {
        const url = ApiHelper.getApiUrl(endpoint);
        
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        };
        
        const requestOptions = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, requestOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    },
    
    /**
     * GET request
     */
    get: (endpoint, params = {}) => {
        const url = ApiHelper.getApiUrl(endpoint, params);
        return ApiHelper.request(endpoint, { method: 'GET' });
    },
    
    /**
     * POST request
     */
    post: (endpoint, data = {}) => {
        return ApiHelper.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * PUT request
     */
    put: (endpoint, data = {}) => {
        return ApiHelper.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * DELETE request
     */
    delete: (endpoint) => {
        return ApiHelper.request(endpoint, {
            method: 'DELETE'
        });
    }
};

// Currency formatting helper for Ethiopian Birr
const CurrencyHelper = {
    format: (amount, showSymbol = true) => {
        const numAmount = parseFloat(amount) || 0;
        const formatted = numAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        return showSymbol ? `ETB ${formatted}` : formatted;
    },

    formatCompact: (amount) => {
        const numAmount = parseFloat(amount) || 0;
        if (numAmount >= 1000000) {
            return `ETB ${(numAmount / 1000000).toFixed(1)}M`;
        } else if (numAmount >= 1000) {
            return `ETB ${(numAmount / 1000).toFixed(1)}K`;
        }
        return CurrencyHelper.format(amount);
    },

    parse: (formattedAmount) => {
        if (typeof formattedAmount !== 'string') {
            return parseFloat(formattedAmount) || 0;
        }
        return parseFloat(formattedAmount.replace(/[^\d.-]/g, '')) || 0;
    },

    validate: (amount) => {
        const numAmount = CurrencyHelper.parse(amount);
        return !isNaN(numAmount) && numAmount >= 0;
    },

    // Ethiopian Birr specific formatting
    formatETB: (amount, options = {}) => {
        const {
            showDecimals = true,
            showSymbol = true,
            compact = false
        } = options;

        const numAmount = parseFloat(amount) || 0;

        if (compact) {
            return CurrencyHelper.formatCompact(amount);
        }

        const formatted = showDecimals
            ? numAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
            : numAmount.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

        return showSymbol ? `ETB ${formatted}` : formatted;
    },

    // Calculate price with discount
    calculateDiscount: (originalPrice, salePrice) => {
        const original = parseFloat(originalPrice) || 0;
        const sale = parseFloat(salePrice) || 0;

        if (original <= 0 || sale <= 0 || sale >= original) {
            return 0;
        }

        return Math.round(((original - sale) / original) * 100);
    },

    // Format price range
    formatRange: (minPrice, maxPrice) => {
        const min = parseFloat(minPrice) || 0;
        const max = parseFloat(maxPrice) || 0;

        if (min === max) {
            return CurrencyHelper.format(min);
        }

        return `${CurrencyHelper.format(min)} - ${CurrencyHelper.format(max)}`;
    }
};

// Make configuration globally available
window.LFLConfig = LFLConfig;
window.ApiHelper = ApiHelper;
window.CurrencyHelper = CurrencyHelper;
