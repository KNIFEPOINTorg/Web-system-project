/**
 * Enhanced Search Functionality for LFLshop
 * Implements real-time search with backend connectivity and suggestions
 */

class EnhancedSearch {
    constructor() {
        this.searchInput = null;
        this.searchSuggestions = null;
        this.suggestionsList = null;
        this.searchClear = null;
        this.viewAllResults = null;
        this.searchTimeout = null;
        this.currentQuery = '';
        this.isSearching = false;
        this.cache = new Map();
        this.cacheTimeout = 300000; // 5 minutes
    }

    /**
     * Initialize the enhanced search functionality
     */
    init() {
        this.initializeElements();
        this.setupEventListeners();
        this.setupKeyboardNavigation();
    }

    /**
     * Initialize DOM elements
     */
    initializeElements() {
        this.searchInput = document.getElementById('globalSearchInput');
        this.searchSuggestions = document.getElementById('searchSuggestions');
        this.suggestionsList = document.getElementById('suggestionsList');
        this.searchClear = document.getElementById('searchClear');
        this.viewAllResults = document.getElementById('viewAllResults');

        if (!this.searchInput) {
            console.warn('Enhanced search: Search input not found');
            return;
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        if (!this.searchInput) return;

        // Search input events
        this.searchInput.addEventListener('input', (e) => {
            this.handleSearchInput(e.target.value);
        });

        this.searchInput.addEventListener('focus', () => {
            if (this.currentQuery.length >= 2) {
                this.showSuggestions();
            }
        });

        this.searchInput.addEventListener('blur', (e) => {
            // Delay hiding to allow clicking on suggestions
            setTimeout(() => {
                if (!this.searchSuggestions.contains(document.activeElement)) {
                    this.hideSuggestions();
                }
            }, 150);
        });

        // Clear button
        if (this.searchClear) {
            this.searchClear.addEventListener('click', () => {
                this.clearSearch();
            });
        }

        // View all results button
        if (this.viewAllResults) {
            this.viewAllResults.addEventListener('click', () => {
                this.performFullSearch();
            });
        }

        // Click outside to close suggestions
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                this.hideSuggestions();
            }
        });

        // Form submission
        const searchForm = this.searchInput.closest('form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.performFullSearch();
            });
        }
    }

    /**
     * Setup keyboard navigation
     */
    setupKeyboardNavigation() {
        if (!this.searchInput) return;

        this.searchInput.addEventListener('keydown', (e) => {
            const suggestions = this.suggestionsList.querySelectorAll('.suggestion-item');
            const activeSuggestion = this.suggestionsList.querySelector('.suggestion-item.active');
            let activeIndex = -1;

            if (activeSuggestion) {
                activeIndex = Array.from(suggestions).indexOf(activeSuggestion);
            }

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.navigateSuggestions(suggestions, activeIndex, 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.navigateSuggestions(suggestions, activeIndex, -1);
                    break;
                case 'Enter':
                    if (activeSuggestion) {
                        e.preventDefault();
                        activeSuggestion.click();
                    } else {
                        e.preventDefault();
                        this.performFullSearch();
                    }
                    break;
                case 'Escape':
                    this.hideSuggestions();
                    this.searchInput.blur();
                    break;
            }
        });
    }

    /**
     * Navigate through suggestions with keyboard
     */
    navigateSuggestions(suggestions, currentIndex, direction) {
        // Remove current active state
        suggestions.forEach(item => item.classList.remove('active'));

        // Calculate new index
        let newIndex = currentIndex + direction;
        if (newIndex < 0) newIndex = suggestions.length - 1;
        if (newIndex >= suggestions.length) newIndex = 0;

        // Set new active state
        if (suggestions[newIndex]) {
            suggestions[newIndex].classList.add('active');
            suggestions[newIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    /**
     * Handle search input changes
     */
    handleSearchInput(query) {
        this.currentQuery = query.trim();

        // Show/hide clear button
        if (this.searchClear) {
            this.searchClear.style.display = this.currentQuery ? 'block' : 'none';
        }

        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        if (this.currentQuery.length < 2) {
            this.hideSuggestions();
            return;
        }

        // Debounce search requests
        this.searchTimeout = setTimeout(() => {
            this.performSearch(this.currentQuery);
        }, 300);
    }

    /**
     * Perform search with suggestions
     */
    async performSearch(query) {
        if (this.isSearching) return;

        try {
            this.isSearching = true;
            this.showLoadingState();

            // Check cache first
            const cacheKey = `search_${query.toLowerCase()}`;
            const cached = this.cache.get(cacheKey);
            
            if (cached && (Date.now() - cached.timestamp) < this.cacheTimeout) {
                this.displaySuggestions(cached.data, query);
                return;
            }

            // Perform API search
            const response = await fetch(`../php/handlers/product_handler.php?action=search_products&q=${encodeURIComponent(query)}&limit=8`, {
                method: 'GET',
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    // Cache results
                    this.cache.set(cacheKey, {
                        data: data.products,
                        timestamp: Date.now()
                    });

                    this.displaySuggestions(data.products, query);
                } else {
                    this.showErrorState('Search failed');
                }
            } else {
                this.showErrorState('Search service unavailable');
            }

        } catch (error) {
            console.error('Search error:', error);
            this.showErrorState('Search error occurred');
        } finally {
            this.isSearching = false;
        }
    }

    /**
     * Display search suggestions
     */
    displaySuggestions(products, query) {
        if (!this.suggestionsList) return;

        if (products.length === 0) {
            this.showNoResultsState(query);
            return;
        }

        const highlightedQuery = query.toLowerCase();
        
        this.suggestionsList.innerHTML = products.map(product => {
            const name = this.highlightText(product.name, highlightedQuery);
            const price = product.sale_price || product.price;
            const formattedPrice = this.formatCurrency(price);
            
            return `
                <div class="suggestion-item" data-product-id="${product.id}" data-product-slug="${product.slug}">
                    <div class="suggestion-image">
                        <img src="${product.primary_image || '../images/placeholder-product.jpg'}" 
                             alt="${product.name}" 
                             onerror="this.src='../images/placeholder-product.jpg'">
                    </div>
                    <div class="suggestion-content">
                        <div class="suggestion-name">${name}</div>
                        <div class="suggestion-price">${formattedPrice}</div>
                    </div>
                    <div class="suggestion-action">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            `;
        }).join('');

        // Add click handlers to suggestions
        this.suggestionsList.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                const productSlug = item.dataset.productSlug;
                const productId = item.dataset.productId;
                this.selectProduct(productSlug, productId);
            });
        });

        this.showSuggestions();
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        if (!this.suggestionsList) return;

        this.suggestionsList.innerHTML = `
            <div class="suggestion-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Searching...</span>
            </div>
        `;
        this.showSuggestions();
    }

    /**
     * Show error state
     */
    showErrorState(message) {
        if (!this.suggestionsList) return;

        this.suggestionsList.innerHTML = `
            <div class="suggestion-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>${message}</span>
            </div>
        `;
        this.showSuggestions();
    }

    /**
     * Show no results state
     */
    showNoResultsState(query) {
        if (!this.suggestionsList) return;

        this.suggestionsList.innerHTML = `
            <div class="suggestion-no-results">
                <i class="fas fa-search"></i>
                <span>No products found for "${query}"</span>
                <small>Try different keywords or browse our categories</small>
            </div>
        `;
        this.showSuggestions();
    }

    /**
     * Show suggestions dropdown
     */
    showSuggestions() {
        if (this.searchSuggestions) {
            this.searchSuggestions.style.display = 'block';
        }
    }

    /**
     * Hide suggestions dropdown
     */
    hideSuggestions() {
        if (this.searchSuggestions) {
            this.searchSuggestions.style.display = 'none';
        }
    }

    /**
     * Clear search
     */
    clearSearch() {
        this.searchInput.value = '';
        this.currentQuery = '';
        this.hideSuggestions();
        
        if (this.searchClear) {
            this.searchClear.style.display = 'none';
        }
        
        this.searchInput.focus();
    }

    /**
     * Perform full search (navigate to search results page)
     */
    performFullSearch() {
        if (this.currentQuery.length < 2) return;

        const searchUrl = `../search-results.php?q=${encodeURIComponent(this.currentQuery)}`;
        window.location.href = searchUrl;
    }

    /**
     * Select a product from suggestions
     */
    selectProduct(slug, id) {
        if (slug) {
            window.location.href = `../product.php?slug=${slug}`;
        } else if (id) {
            window.location.href = `../product.php?id=${id}`;
        }
    }

    /**
     * Highlight search terms in text
     */
    highlightText(text, query) {
        if (!query || query.length < 2) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    /**
     * Get current search query
     */
    getCurrentQuery() {
        return this.currentQuery;
    }

    /**
     * Set search query programmatically
     */
    setQuery(query) {
        if (this.searchInput) {
            this.searchInput.value = query;
            this.handleSearchInput(query);
        }
    }
}

// Create global instance
window.enhancedSearch = new EnhancedSearch();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnhancedSearch;
}
