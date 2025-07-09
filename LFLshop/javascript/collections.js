// Collections Page JavaScript
// Handles filtering, sorting, and product display

// Product data - starts empty, populated by seller dashboard
let collectionsProducts = [];

// Load products from localStorage (seller dashboard products)
function loadProductsFromStorage() {
    const storedProducts = localStorage.getItem('lflshop_products');
    if (storedProducts) {
        collectionsProducts = JSON.parse(storedProducts);
        // Products loaded from seller dashboard
    } else {
        collectionsProducts = [];
        // No products found - starting with empty collection
    }
}

let filteredProducts = [...collectionsProducts];
let currentPage = 1;
const productsPerPage = 6;

// Initialize collections page
document.addEventListener('DOMContentLoaded', async function() {
    await loadProductsFromAPI();
    initializeCollections();
    setupEventListeners();
    setupFilterSidebar();
    setupMobileFilters();
    handleCategoryFromURL();
    displayProducts();
    updateProductCount();
    updateCartIcon();
    initializeCategoryFilters();

    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

async function loadProductsFromAPI() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');
        const search = urlParams.get('search');

        // Use centralized API configuration
        const params = { action: 'list' };
        if (category) params.category = category;
        if (search) params.search = search;

        const apiUrl = ApiHelper.getApiUrl(LFLConfig.API.PRODUCTS, params);
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.success) {
            collectionsProducts = data.data || [];
            filteredProducts = [...collectionsProducts];
        } else {
            console.error('Failed to load products:', data.message);
            collectionsProducts = [];
            filteredProducts = [];
        }
    } catch (error) {
        console.error('Error loading products:', error);
        collectionsProducts = [];
        filteredProducts = [];
    }
}

// Save products to localStorage
function saveProductsToStorage() {
    localStorage.setItem('lflshop_products', JSON.stringify(collectionsProducts));
    // Products saved to storage
}

// Initialize collections functionality
function initializeCollections() {
    // Setup price range sliders
    setupPriceRange();

    // Load URL parameters for filters
    loadFiltersFromURL();

    // Apply initial filters
    applyFilters();
}

// Setup filter sidebar functionality
function setupFilterSidebar() {
    // Setup section toggles
    const sectionToggles = document.querySelectorAll('.section-toggle');
    sectionToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const section = this.dataset.section;
            const content = document.getElementById(`${section}-content`);
            const icon = this.querySelector('i');

            if (content) {
                content.classList.toggle('collapsed');
                this.classList.toggle('collapsed');

                // Update icon rotation
                if (content.classList.contains('collapsed')) {
                    icon.style.transform = 'rotate(-90deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        });
    });

    // Update filter count on any filter change
    updateActiveFilterCount();

    // Setup filter change listeners for count updates
    const filterInputs = document.querySelectorAll('.filter-option input');
    filterInputs.forEach(input => {
        input.addEventListener('change', updateActiveFilterCount);
    });

    const priceInputs = document.querySelectorAll('#min-price, #max-price');
    priceInputs.forEach(input => {
        input.addEventListener('input', updateActiveFilterCount);
    });
}

// Setup mobile filter functionality
function setupMobileFilters() {
    const mobileToggle = document.getElementById('mobile-filter-toggle');
    const filtersSidebar = document.getElementById('filters-sidebar');
    const filterClose = document.getElementById('filter-close');

    if (mobileToggle && filtersSidebar) {
        mobileToggle.addEventListener('click', function() {
            filtersSidebar.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (filterClose && filtersSidebar) {
        filterClose.addEventListener('click', function() {
            filtersSidebar.classList.remove('open');
            document.body.style.overflow = '';
        });
    }

    // Close on backdrop click
    if (filtersSidebar) {
        filtersSidebar.addEventListener('click', function(e) {
            if (e.target === filtersSidebar) {
                filtersSidebar.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && filtersSidebar && filtersSidebar.classList.contains('open')) {
            filtersSidebar.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
}

// Setup collapsible filters
function setupCollapsibleFilters() {
    const filterToggle = document.getElementById('filter-toggle');
    const filtersSidebar = document.getElementById('filters-sidebar');

    if (filterToggle && filtersSidebar) {
        filterToggle.addEventListener('click', function() {
            const isExpanded = filtersSidebar.classList.contains('expanded');

            if (isExpanded) {
                filtersSidebar.classList.remove('expanded');
                filterToggle.classList.remove('active');
            } else {
                filtersSidebar.classList.add('expanded');
                filterToggle.classList.add('active');
            }
        });
    }
}

// Update active filter count and tags
function updateActiveFilterCount() {
    const categoryCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]:checked');
    const regionCheckboxes = document.querySelectorAll('input[value="Addis Ababa"], input[value="Oromia"], input[value="Amhara"], input[value="Tigray"], input[value="SNNP"]');
    const selectedRegions = Array.from(regionCheckboxes).filter(cb => cb.checked);
    const selectedRating = document.querySelector('.rating-group input[type="radio"]:checked');
    const minPrice = document.getElementById('min-price')?.value;
    const maxPrice = document.getElementById('max-price')?.value;

    let activeCount = 0;

    // Count active filters
    activeCount += categoryCheckboxes.length;
    activeCount += selectedRegions.length;
    if (selectedRating && selectedRating.value !== '0') activeCount += 1;
    if (minPrice && minPrice !== '') activeCount += 1;
    if (maxPrice && maxPrice !== '') activeCount += 1;

    // Update mobile filter count
    const mobileFilterCount = document.getElementById('mobile-filter-count');
    if (mobileFilterCount) {
        mobileFilterCount.textContent = activeCount;
        mobileFilterCount.style.display = activeCount > 0 ? 'block' : 'none';
    }

    // Update filter count display (legacy)
    const filterCountElement = document.getElementById('active-filters-count');
    if (filterCountElement) {
        filterCountElement.textContent = activeCount === 0 ? '0 active' : `${activeCount} active`;
    }

    // Update active filter tags
    updateActiveFilterTags();
}

// Update active filter tags
function updateActiveFilterTags() {
    const activeFiltersContainer = document.getElementById('active-filters');
    const mobileActiveFiltersContainer = document.getElementById('active-filters-mobile');

    let filterTags = [];

    // Add category filters
    const categoryCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]:checked');
    Array.from(categoryCheckboxes).forEach(checkbox => {
        filterTags.push({
            type: 'category',
            value: checkbox.value,
            label: checkbox.value
        });
    });

    // Add region filters
    const regionCheckboxes = document.querySelectorAll('input[value="Addis Ababa"], input[value="Oromia"], input[value="Amhara"], input[value="Tigray"], input[value="SNNP"]');
    const selectedRegions = Array.from(regionCheckboxes).filter(cb => cb.checked);
    selectedRegions.forEach(checkbox => {
        filterTags.push({
            type: 'region',
            value: checkbox.value,
            label: checkbox.value
        });
    });

    // Add price range filter
    const minPrice = document.getElementById('min-price')?.value;
    const maxPrice = document.getElementById('max-price')?.value;
    if (minPrice || maxPrice) {
        const minVal = minPrice || 0;
        const maxVal = maxPrice || 5000;
        filterTags.push({
            type: 'price',
            value: `${minVal}-${maxVal}`,
            label: CurrencyHelper.formatRange(minVal, maxVal)
        });
    }

    // Add rating filter
    const selectedRating = document.querySelector('.rating-group input[type="radio"]:checked');
    if (selectedRating && selectedRating.value !== '0') {
        const ratingText = selectedRating.value === '5' ? '5 stars only' : `${selectedRating.value}+ stars`;
        filterTags.push({
            type: 'rating',
            value: selectedRating.value,
            label: ratingText
        });
    }

    // Render filter tags for desktop
    if (activeFiltersContainer) {
        if (filterTags.length === 0) {
            activeFiltersContainer.innerHTML = '<span class="no-filters" style="color: var(--text-secondary); font-size: 0.9rem;">No filters applied</span>';
        } else {
            activeFiltersContainer.innerHTML = filterTags.map(tag => `
                <div class="filter-tag">
                    <span>${tag.label}</span>
                    <button class="remove-filter" onclick="removeFilter('${tag.type}', '${tag.value}')" title="Remove filter">
                        <i data-feather="x"></i>
                    </button>
                </div>
            `).join('');

            // Re-initialize Feather icons for new elements
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    }

    // Render filter tags for mobile
    if (mobileActiveFiltersContainer) {
        if (filterTags.length === 0) {
            mobileActiveFiltersContainer.innerHTML = '';
        } else {
            mobileActiveFiltersContainer.innerHTML = filterTags.map(tag => `
                <div class="filter-tag">
                    <span>${tag.label}</span>
                    <button class="remove-filter" onclick="removeFilter('${tag.type}', '${tag.value}')" title="Remove filter">
                        <i data-feather="x"></i>
                    </button>
                </div>
            `).join('');

            // Re-initialize Feather icons for new elements
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    }
}

// Remove individual filter
function removeFilter(type, value) {
    switch(type) {
        case 'category':
            const categoryCheckbox = document.querySelector(`input[value="${value}"]`);
            if (categoryCheckbox) categoryCheckbox.checked = false;
            break;
        case 'region':
            const regionCheckbox = document.querySelector(`input[value="${value}"]`);
            if (regionCheckbox) regionCheckbox.checked = false;
            break;
        case 'price':
            document.getElementById('min-price').value = '';
            document.getElementById('max-price').value = '';
            break;
        case 'rating':
            const ratingRadios = document.querySelectorAll('.rating-group input[type="radio"]');
            ratingRadios.forEach(radio => radio.checked = false);
            break;
    }

    applyFilters();
    updateActiveFilterCount();
}

// Setup event listeners
function setupEventListeners() {
    // Category checkboxes
    const categoryCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });

    // Region checkboxes
    const regionCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
    regionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });

    // Rating radio buttons
    const ratingRadios = document.querySelectorAll('.rating-group input[type="radio"]');
    ratingRadios.forEach(radio => {
        radio.addEventListener('change', applyFilters);
    });

    // Price inputs
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');

    if (minPriceInput) minPriceInput.addEventListener('input', applyFilters);
    if (maxPriceInput) maxPriceInput.addEventListener('input', applyFilters);

    // Sort dropdown
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', handleSort);
    }
    
    // View toggle buttons
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            toggleView(this.dataset.view);
        });
    });
    
    // Filter action buttons
    const applyBtn = document.getElementById('apply-filters');
    const clearBtn = document.getElementById('clear-filters');
    const clearAllBtn = document.getElementById('clear-all-filters');

    if (applyBtn) {
        applyBtn.addEventListener('click', applyFilters);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', clearAllFilters);
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', clearAllFilters);
    }
    
    // Load more button
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreProducts);
    }
    
    // Category cards
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.dataset.category;
            filterByCategory(category);
        });
    });
}

// Setup price range functionality
function setupPriceRange() {
    const minSlider = document.getElementById('price-min');
    const maxSlider = document.getElementById('price-max');
    const minDisplay = document.getElementById('price-min-display');
    const maxDisplay = document.getElementById('price-max-display');
    
    if (minSlider && maxSlider) {
        minSlider.addEventListener('input', function() {
            const minValue = parseInt(this.value);
            const maxValue = parseInt(maxSlider.value);
            
            if (minValue >= maxValue) {
                this.value = maxValue - 100;
            }
            
            minDisplay.textContent = parseInt(this.value).toLocaleString();
            applyFilters();
        });
        
        maxSlider.addEventListener('input', function() {
            const minValue = parseInt(minSlider.value);
            const maxValue = parseInt(this.value);
            
            if (maxValue <= minValue) {
                this.value = minValue + 100;
            }
            
            maxDisplay.textContent = parseInt(this.value).toLocaleString();
            applyFilters();
        });
    }
}

// Load filters from URL parameters
function loadFiltersFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');
    const region = urlParams.get('region');
    
    if (category) {
        const categoryCheckbox = document.querySelector(`input[name="category"][value="${category}"]`);
        if (categoryCheckbox) {
            categoryCheckbox.checked = true;
        }
    }
    
    if (region) {
        const regionCheckbox = document.querySelector(`input[name="region"][value="${region}"]`);
        if (regionCheckbox) {
            regionCheckbox.checked = true;
        }
    }
}

// Apply filters to products
function applyFilters() {
    // Get selected categories
    const categoryCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]:checked');
    const selectedCategories = Array.from(categoryCheckboxes).map(cb => cb.value);

    // Get selected regions (if any region checkboxes exist)
    const regionCheckboxes = document.querySelectorAll('input[value="Addis Ababa"], input[value="Oromia"], input[value="Amhara"], input[value="Tigray"], input[value="SNNP"]');
    const selectedRegions = Array.from(regionCheckboxes).filter(cb => cb.checked).map(cb => cb.value);

    // Get price range
    const minPrice = parseInt(document.getElementById('min-price')?.value || 0);
    const maxPrice = parseInt(document.getElementById('max-price')?.value || 5000);

    // Get rating
    const selectedRating = document.querySelector('.rating-group input[type="radio"]:checked');
    const ratingFilter = selectedRating ? parseFloat(selectedRating.value) : 0;

    filteredProducts = collectionsProducts.filter(product => {
        // Category filter
        const categoryMatch = selectedCategories.length === 0 || selectedCategories.includes(product.category);

        // Region filter (if regions are available in product data)
        const regionMatch = selectedRegions.length === 0 || (product.region && selectedRegions.includes(product.region));

        // Price filter
        const priceMatch = product.price >= minPrice && product.price <= maxPrice;

        // Rating filter
        let ratingMatch = true;
        if (ratingFilter > 0) {
            if (ratingFilter === 5) {
                // 5 stars only - exact match
                ratingMatch = product.rating === 5;
            } else {
                // All other ratings - greater than or equal
                ratingMatch = product.rating >= ratingFilter;
            }
        }

        return categoryMatch && regionMatch && priceMatch && ratingMatch;
    });

    currentPage = 1;
    displayProducts();
    updateProductCount();
    updateActiveFilterCount();

    showSuccessToast(`Found ${filteredProducts.length} products matching your filters`);
}

// Get selected checkbox values
function getSelectedValues(name) {
    const checkboxes = document.querySelectorAll(`input[name="${name}"]:checked`);
    return Array.from(checkboxes).map(cb => cb.value);
}

// Handle sorting
function handleSort() {
    const sortValue = document.getElementById('sort-select').value;
    
    switch(sortValue) {
        case 'price-low':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'newest':
            filteredProducts.sort((a, b) => b.id - a.id);
            break;
        case 'popular':
            filteredProducts.sort((a, b) => b.rating - a.rating);
            break;
        default:
            // Featured - keep original order
            break;
    }
    
    displayProducts();
    showInfoToast(`Products sorted by ${sortValue.replace('-', ' ')}`);
}

// Display products in grid
function displayProducts() {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return;
    
    const startIndex = (currentPage - 1) * productsPerPage;
    const endIndex = startIndex + productsPerPage;
    const productsToShow = filteredProducts.slice(0, endIndex);
    
    if (productsToShow.length === 0) {
        productsGrid.innerHTML = '';
        const emptyState = document.getElementById('empty-state');
        if (emptyState) {
            emptyState.style.display = 'flex';
        }
        return;
    } else {
        const emptyState = document.getElementById('empty-state');
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    }
    
    productsGrid.innerHTML = productsToShow.map(product => `
        <div class="product-card monochrome-product-card" data-product-id="${product.id}">
            <div class="product-image monochrome-product-image">
                <img src="${product.image}" alt="${product.name}" loading="lazy">
                <div class="product-overlay">
                    <button class="quick-view-btn" onclick="quickViewProduct(${product.id})">
                        <i class="fas fa-eye"></i>
                        Quick View
                    </button>
                </div>
            </div>
            <div class="product-info monochrome-product-info">
                <div class="product-category">${getCategoryDisplayName(product.category)}</div>
                <h3 class="product-title monochrome-product-title">${product.name}</h3>
                <div class="product-seller">
                    <i class="fas fa-store"></i>
                    <span>by ${product.seller}</span>
                    <span class="seller-badge">Verified</span>
                </div>
                <div class="product-rating">
                    <div class="stars rating-stars">
                        ${generateStarRating(product.rating)}
                    </div>
                    <span class="rating-count">(${Math.floor(Math.random() * 50) + 10} reviews)</span>
                </div>
                <div class="product-price-section">
                    <span class="current-price monochrome-product-price">${CurrencyHelper.format(product.price)}</span>
                    <span class="product-region">${product.region || 'Ethiopia'}</span>
                </div>
                <div class="product-actions">
                    <button class="btn add-to-cart-btn" onclick="addToCart(${product.id})">
                        <span><i class="fas fa-shopping-cart"></i> Add to Cart</span>
                    </button>
                    <button class="wishlist-btn" onclick="toggleWishlist(${product.id})" title="Add to Wishlist">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    // Update pagination
    updatePagination();
}

// Generate star rating HTML
function generateStarRating(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    let starsHTML = '';

    // Full stars
    for (let i = 0; i < fullStars; i++) {
        starsHTML += '<i class="fas fa-star"></i>';
    }

    // Half star
    if (hasHalfStar) {
        starsHTML += '<i class="fas fa-star-half-alt"></i>';
    }

    // Empty stars
    for (let i = 0; i < emptyStars; i++) {
        starsHTML += '<i class="far fa-star"></i>';
    }

    return starsHTML;
}

// Get category display name
function getCategoryDisplayName(category) {
    const categoryMap = {
        'Textiles': 'Traditional Textiles',
        'Clothing': 'Clothing & Fashion',
        'Jewelry': 'Jewelry & Accessories',
        'Coffee': 'Ethiopian Coffee',
        'Pottery': 'Pottery & Ceramics',
        'Spices': 'Spices & Herbs',
        'Home Decor': 'Home & Decor',
        'Art': 'Traditional Art',
        'Footwear': 'Footwear',
        'Food': 'Food & Beverages',
        'Merchandise': 'Gifts & Merchandise'
    };

    return categoryMap[category] || category;
}

// Update pagination
function updatePagination() {
    const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
    const paginationInfo = document.getElementById('pagination-info');
    const paginationControls = document.getElementById('pagination-controls');

    if (paginationInfo) {
        const startItem = (currentPage - 1) * productsPerPage + 1;
        const endItem = Math.min(currentPage * productsPerPage, filteredProducts.length);
        paginationInfo.textContent = `Showing ${startItem}-${endItem} of ${filteredProducts.length} products`;
    }

    if (paginationControls && totalPages > 1) {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const pageNumbers = document.getElementById('page-numbers');

        // Update prev/next buttons
        if (prevBtn) {
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayProducts();
                }
            };
        }

        if (nextBtn) {
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayProducts();
                }
            };
        }

        // Update page numbers
        if (pageNumbers) {
            let pageNumbersHTML = '';
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                pageNumbersHTML += `
                    <button class="page-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">
                        ${i}
                    </button>
                `;
            }

            pageNumbers.innerHTML = pageNumbersHTML;
        }
    }
}

// Go to specific page
function goToPage(page) {
    currentPage = page;
    displayProducts();
}

// Quick view product
function quickViewProduct(productId) {
    const product = collectionsProducts.find(p => p.id === productId);
    if (product) {
        showToast(`Quick view for ${product.name}`, 'info');
        // In a real app, this would open a modal with product details
    }
}

// Add to cart
function addToCart(productId) {
    const product = collectionsProducts.find(p => p.id === productId);
    if (product) {
        showToast(`${product.name} added to cart!`, 'success');
        // In a real app, this would add to cart functionality
    }
}

// Toggle wishlist
function toggleWishlist(productId) {
    const product = collectionsProducts.find(p => p.id === productId);
    if (product) {
        showToast(`${product.name} added to wishlist!`, 'success');
        // In a real app, this would toggle wishlist functionality
    }
}

// Load more products
function loadMoreProducts() {
    currentPage++;
    displayProducts();
    showInfoToast('More products loaded');
}

// Toggle view between grid and list
function toggleView(viewType) {
    const productsGrid = document.getElementById('products-grid');
    if (viewType === 'list') {
        productsGrid.classList.add('list-view');
    } else {
        productsGrid.classList.remove('list-view');
    }
}

// Clear all filters
function clearAllFilters() {
    // Uncheck all checkboxes
    const checkboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);

    // Uncheck all radio buttons
    const radioButtons = document.querySelectorAll('.rating-group input[type="radio"]');
    radioButtons.forEach(radio => radio.checked = false);

    // Reset price inputs
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    if (minPriceInput) {
        minPriceInput.value = '';
        minPriceInput.placeholder = '0';
    }
    if (maxPriceInput) {
        maxPriceInput.value = '';
        maxPriceInput.placeholder = '5000';
    }

    // Reset sort
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) sortSelect.value = 'featured';

    // Update filter count
    updateActiveFilterCount();

    // Apply filters
    applyFilters();

    showSuccessToast('All filters cleared');
}

// Filter by specific category
function filterByCategory(category) {
    clearAllFilters();
    
    const categoryCheckbox = document.querySelector(`input[name="category"][value="${category}"]`);
    if (categoryCheckbox) {
        categoryCheckbox.checked = true;
        applyFilters();
    }
}

// View individual product
function viewProduct(productId) {
    window.location.href = `product-detail.html?id=${productId}`;
}

// Update product count display
function updateProductCount() {
    const totalProductsElement = document.getElementById('total-products');
    if (totalProductsElement) {
        totalProductsElement.textContent = filteredProducts.length;
    }

    // Update results count in header and toolbar
    const resultsCountElements = document.querySelectorAll('#results-count, #products-results-count');
    resultsCountElements.forEach(element => {
        if (element) {
            const count = filteredProducts.length;
            const itemText = count === 1 ? 'item' : 'items';
            element.textContent = `${count} ${itemText} found`;
        }
    });
}

// Initialize category filters using the new category system
function initializeCategoryFilters() {
    if (typeof CategoryManager === 'undefined') return;

    // Update category filter checkboxes with new category system
    const categoryFiltersContainer = document.querySelector('.filter-group[data-filter="category"] .filter-options');
    if (categoryFiltersContainer) {
        const categories = CategoryManager.getAllCategories();
        const featuredCategories = CategoryManager.getFeaturedCategories();

        let categoryHTML = '';

        // Add featured Ethiopian categories first
        if (featuredCategories.length > 0) {
            categoryHTML += '<div class="filter-section"><h4>🇪🇹 Ethiopian Traditional</h4>';
            featuredCategories.forEach(category => {
                categoryHTML += `
                    <label class="filter-option featured">
                        <input type="checkbox" name="category" value="${category.id}">
                        <span class="checkmark"></span>
                        <span class="option-text">${category.name}</span>
                        <span class="cultural-badge">🇪🇹</span>
                    </label>
                `;
            });
            categoryHTML += '</div>';
        }

        // Add other categories
        const otherCategories = Object.values(categories).filter(cat => !cat.featured);
        if (otherCategories.length > 0) {
            categoryHTML += '<div class="filter-section"><h4>🛍️ General Categories</h4>';
            otherCategories.forEach(category => {
                categoryHTML += `
                    <label class="filter-option">
                        <input type="checkbox" name="category" value="${category.id}">
                        <span class="checkmark"></span>
                        <span class="option-text">${category.name}</span>
                    </label>
                `;
            });
            categoryHTML += '</div>';
        }

        categoryFiltersContainer.innerHTML = categoryHTML;

        // Re-setup event listeners for new checkboxes
        const newCheckboxes = categoryFiltersContainer.querySelectorAll('input[type="checkbox"]');
        newCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', applyFilters);
        });
    }
}

// Map old category values to new category system
function mapLegacyCategoryToNew(oldCategory) {
    const categoryMapping = {
        'textiles': 'traditional-textiles',
        'jewelry': 'jewelry-accessories',
        'coffee': 'coffee-beverages',
        'pottery': 'pottery-ceramics',
        'spices': 'spices-food'
    };

    return categoryMapping[oldCategory] || oldCategory;
}

// Enhanced filter function with category system integration
function applyFiltersWithCategorySystem() {
    const selectedCategories = getSelectedValues('category');
    const selectedRegions = getSelectedValues('region');
    const minPrice = parseInt(document.getElementById('price-min')?.value || 0);
    const maxPrice = parseInt(document.getElementById('price-max')?.value || 50000);

    filteredProducts = collectionsProducts.filter(product => {
        // Map legacy category to new system for comparison
        const productCategory = mapLegacyCategoryToNew(product.category);
        const categoryMatch = selectedCategories.length === 0 ||
                             selectedCategories.includes(product.category) ||
                             selectedCategories.includes(productCategory);

        const regionMatch = selectedRegions.length === 0 || selectedRegions.includes(product.region);
        const priceMatch = product.price >= minPrice && product.price <= maxPrice;

        return categoryMatch && regionMatch && priceMatch;
    });

    currentPage = 1;
    displayProducts();
    updateProductCount();

    showSuccessToast(`Found ${filteredProducts.length} products matching your filters`);
}

// Handle category filtering from URL parameters (from home page navigation)
function handleCategoryFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');

    if (categoryParam) {
        // Clear existing filters first
        clearAllFilters();

        // Set the category filter
        const categoryCheckboxes = document.querySelectorAll('input[name="category"]');
        categoryCheckboxes.forEach(checkbox => {
            if (checkbox.value === categoryParam ||
                checkbox.value === categoryParam.replace('-', '') ||
                mapLegacyCategoryToNew(checkbox.value) === categoryParam) {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });

        // Apply the filter
        applyFilters();

        // Update page title and header
        updatePageHeaderForCategory(categoryParam);

        // Show success message
        showSuccessToast(`Showing products in ${categoryParam.replace('-', ' ')} category`);
    }
}

// Update page header when filtering by category
function updatePageHeaderForCategory(categoryId) {
    const headerContent = document.querySelector('.header-content h1');
    const headerSubtitle = document.querySelector('.header-content p');

    const categoryNames = {
        'traditional-textiles': 'Traditional Ethiopian Textiles',
        'jewelry-accessories': 'Jewelry & Accessories',
        'pottery-ceramics': 'Pottery & Ceramics',
        'coffee-beverages': 'Coffee & Beverages',
        'spices-food': 'Traditional Spices & Herbs',
        'handwoven-baskets': 'Handwoven Baskets & Storage',
        'musical-instruments': 'Traditional Musical Instruments',
        'ethiopian-art': 'Ethiopian Art & Paintings'
    };

    const categoryDescriptions = {
        'traditional-textiles': 'Authentic Ethiopian clothing, habesha dresses, and handwoven fabrics',
        'jewelry-accessories': 'Traditional Ethiopian jewelry, silver crosses, and handcrafted accessories',
        'pottery-ceramics': 'Traditional jebenas, clay vessels, and decorative ceramic art',
        'coffee-beverages': 'Premium Ethiopian coffee beans, brewing equipment, and accessories',
        'spices-food': 'Authentic berbere, mitmita, and traditional Ethiopian spice blends',
        'handwoven-baskets': 'Traditional mesob baskets, storage containers, and woven home decor',
        'musical-instruments': 'Krar, masinko, kebero drums, and authentic Ethiopian instruments',
        'ethiopian-art': 'Traditional paintings, religious art, and contemporary Ethiopian artwork'
    };

    if (headerContent && categoryNames[categoryId]) {
        headerContent.textContent = categoryNames[categoryId];
    }

    if (headerSubtitle && categoryDescriptions[categoryId]) {
        headerSubtitle.textContent = categoryDescriptions[categoryId];
    }
}

// Enhanced product filtering with demo data integration
function loadDemoProductsForCategory(categoryId) {
    // Load demo data if available
    if (typeof demoCategoryProducts !== 'undefined' && demoCategoryProducts[categoryId]) {
        const categoryProducts = demoCategoryProducts[categoryId];

        // Add demo products to the main products array
        categoryProducts.forEach(product => {
            // Check if product already exists to avoid duplicates
            const existingProduct = collectionsProducts.find(p => p.id === product.id);
            if (!existingProduct) {
                collectionsProducts.push(product);
            }
        });

        return categoryProducts.length;
    }
    return 0;
}

// Export functions for global use
window.viewProduct = viewProduct;
window.filterByCategory = filterByCategory;
window.clearAllFilters = clearAllFilters;
window.initializeCategoryFilters = initializeCategoryFilters;
window.handleCategoryFromURL = handleCategoryFromURL;


