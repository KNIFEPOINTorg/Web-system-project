/**
 * Enhanced Search Functionality
 * Provides live search with suggestions and product filtering
 */

class SearchManager {
    constructor() {
        this.searchData = [
            // Ethiopian Categories
            { text: 'Traditional Textiles', type: 'category', icon: 'tshirt', url: 'collections.html?category=textiles' },
            { text: 'Ethiopian Coffee', type: 'category', icon: 'coffee', url: 'collections.html?category=coffee' },
            { text: 'Berbere Spice', type: 'category', icon: 'pepper-hot', url: 'collections.html?category=spices' },
            { text: 'Ethiopian Jewelry', type: 'category', icon: 'gem', url: 'collections.html?category=jewelry' },
            
            // Ethiopian Products
            { text: 'Habesha Dress', type: 'product', icon: 'tshirt', url: 'collections.html?search=habesha+dress' },
            { text: 'Yirgacheffe Coffee', type: 'product', icon: 'coffee', url: 'collections.html?search=yirgacheffe' },
            { text: 'Ethiopian Cross', type: 'product', icon: 'cross', url: 'collections.html?search=cross' },
            { text: 'Traditional Jebena', type: 'product', icon: 'coffee', url: 'collections.html?search=jebena' },
            { text: 'Handwoven Scarves', type: 'product', icon: 'tshirt', url: 'collections.html?search=scarf' },
            { text: 'Ethiopian Art', type: 'category', icon: 'palette', url: 'collections.html?category=art' },
            { text: 'Traditional Baskets', type: 'product', icon: 'shopping-basket', url: 'collections.html?search=basket' },
            { text: 'Pottery & Ceramics', type: 'product', icon: 'vase', url: 'collections.html?search=pottery' }
        ];
        
        this.init();
    }

    init() {
        this.setupSearchListeners();
    }

    setupSearchListeners() {
        // Wait for the search bar to be created by auth-aware-navigation
        const checkForSearchBar = () => {
            const searchBar = document.querySelector('.search-bar');
            const searchIcon = document.querySelector('.search-icon');
            
            if (searchBar && searchIcon) {
                this.attachSearchEvents(searchBar, searchIcon);
            } else {
                setTimeout(checkForSearchBar, 100);
            }
        };
        
        checkForSearchBar();
    }

    attachSearchEvents(searchBar, searchIcon) {
        // Search on Enter key
        searchBar.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.performSearch(searchBar.value);
            }
        });

        // Live search suggestions
        searchBar.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            if (query.length >= 2) {
                this.showSearchSuggestions(query);
            } else {
                this.hideSearchSuggestions();
            }
        });

        // Search icon click
        searchIcon.addEventListener('click', (e) => {
            e.preventDefault();
            this.performSearch(searchBar.value);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                this.hideSearchSuggestions();
            }
        });

        // Hide suggestions on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideSearchSuggestions();
            }
        });
    }

    showSearchSuggestions(query) {
        const suggestions = this.getSearchSuggestions(query);
        
        if (suggestions.length === 0) {
            this.hideSearchSuggestions();
            return;
        }

        let suggestionsContainer = document.querySelector('.search-suggestions');
        if (!suggestionsContainer) {
            suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'search-suggestions';
            
            const searchContainer = document.querySelector('.search-container');
            if (searchContainer) {
                searchContainer.appendChild(suggestionsContainer);
            }
        }

        suggestionsContainer.innerHTML = suggestions.map(suggestion => `
            <div class="search-suggestion" onclick="searchManager.selectSuggestion('${suggestion.text}', '${suggestion.url}')">
                <i class="fas fa-${suggestion.icon}"></i>
                <span class="suggestion-text">${suggestion.text}</span>
                <span class="suggestion-category">${suggestion.type}</span>
            </div>
        `).join('');
    }

    getSearchSuggestions(query) {
        const lowerQuery = query.toLowerCase();
        
        // Filter suggestions based on query
        const filtered = this.searchData.filter(item => 
            item.text.toLowerCase().includes(lowerQuery)
        );

        // Sort by relevance (exact matches first, then partial matches)
        const exactMatches = filtered.filter(item => 
            item.text.toLowerCase().startsWith(lowerQuery)
        );

        const partialMatches = filtered.filter(item => 
            !item.text.toLowerCase().startsWith(lowerQuery)
        );

        // Combine and limit results
        return [...exactMatches, ...partialMatches].slice(0, 6);
    }

    selectSuggestion(text, url) {
        const searchBar = document.querySelector('.search-bar');
        if (searchBar) {
            searchBar.value = text;
        }
        
        this.hideSearchSuggestions();
        
        // Navigate to the suggestion URL
        window.location.href = url;
    }

    hideSearchSuggestions() {
        const suggestionsContainer = document.querySelector('.search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.remove();
        }
    }

    performSearch(query) {
        if (!query || !query.trim()) {
            return;
        }

        const trimmedQuery = query.trim();
        
        // Check if it's a direct match to our search data
        const directMatch = this.searchData.find(item => 
            item.text.toLowerCase() === trimmedQuery.toLowerCase()
        );

        if (directMatch) {
            window.location.href = directMatch.url;
        } else {
            // General search
            const searchParams = new URLSearchParams();
            searchParams.set('search', trimmedQuery);
            window.location.href = `collections.html?${searchParams.toString()}`;
        }

        this.hideSearchSuggestions();
    }

    // Public method to add search data dynamically
    addSearchData(items) {
        this.searchData.push(...items);
    }

    // Public method to search products from API
    async searchProducts(query) {
        try {
            const response = await fetch(`../api/products.php?action=search&q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data || [];
            }
        } catch (error) {
            console.error('Product search error:', error);
        }
        
        return [];
    }
}

// Initialize search manager
const searchManager = new SearchManager();

// Export for global access
window.searchManager = searchManager;