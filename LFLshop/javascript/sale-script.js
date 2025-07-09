// Sale Page JavaScript

// Verified sale products with legitimate original prices
const saleProducts = [
    {
        id: 1,
        image: "https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80",
        title: "Traditional Habesha Dress",
        category: "Textiles",
        rating: 5,
        ratingCount: 24,
        originalPrice: 120.00,
        salePrice: 89.99,
        discountPercent: 25,
        verificationDate: "2024-01-15",
        retailHistory: "Verified retail price $120 from Jan 2024"
    },
    {
        id: 2,
        image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=688&q=80",
        title: "Silver Filigree Necklace",
        category: "Jewelry",
        rating: 4,
        ratingCount: 18,
        originalPrice: 200.00,
        salePrice: 156.00,
        discountPercent: 22,
        verificationDate: "2024-01-10",
        retailHistory: "Verified retail price $200 from Dec 2023"
    },
    {
        id: 3,
        image: "https://images.unsplash.com/photo-1559525839-d9d1e38b0a35?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Premium Yirgacheffe Coffee",
        category: "Coffee",
        rating: 5,
        ratingCount: 42,
        originalPrice: 35.00,
        salePrice: 28.50,
        discountPercent: 19,
        verificationDate: "2024-01-20",
        retailHistory: "Verified retail price $35 from Jan 2024"
    },
    {
        id: 4,
        image: "https://images.unsplash.com/photo-1519219788971-8d9797e0928e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1169&q=80",
        title: "Handwoven Storage Basket",
        category: "Home Decor",
        rating: 4,
        ratingCount: 15,
        originalPrice: 60.00,
        salePrice: 45.00,
        discountPercent: 25,
        verificationDate: "2024-01-12",
        retailHistory: "Verified retail price $60 from Dec 2023"
    },
    {
        id: 5,
        image: "https://images.unsplash.com/photo-1530968033775-2c92736b131e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Traditional Jebena Coffee Pot",
        category: "Pottery",
        rating: 4,
        ratingCount: 31,
        originalPrice: 75.00,
        salePrice: 52.00,
        discountPercent: 31,
        verificationDate: "2024-01-08",
        retailHistory: "Verified retail price $75 from Nov 2023"
    },
    {
        id: 6,
        image: "https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Authentic Berbere Spice Mix",
        category: "Spices",
        rating: 5,
        ratingCount: 67,
        originalPrice: 25.00,
        salePrice: 18.99,
        discountPercent: 24,
        verificationDate: "2024-01-18",
        retailHistory: "Verified retail price $25 from Jan 2024"
    },
    {
        id: 7,
        image: "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Colorful Woven Scarf",
        category: "Textiles",
        rating: 5,
        ratingCount: 33,
        originalPrice: 55.00,
        salePrice: 42.00,
        discountPercent: 24,
        verificationDate: "2024-01-14",
        retailHistory: "Verified retail price $55 from Dec 2023"
    },
    {
        id: 8,
        image: "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1160&q=80",
        title: "Handcrafted Leather Sandals",
        category: "Footwear",
        rating: 4,
        ratingCount: 12,
        originalPrice: 85.00,
        salePrice: 65.00,
        discountPercent: 24,
        verificationDate: "2024-01-16",
        retailHistory: "Verified retail price $85 from Jan 2024"
    },
    {
        id: 9,
        image: "https://images.unsplash.com/photo-1587049352851-8d4e89133924?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Pure Ethiopian Honey",
        category: "Food",
        rating: 4,
        ratingCount: 25,
        originalPrice: 38.00,
        salePrice: 24.99,
        discountPercent: 34,
        verificationDate: "2024-01-11",
        retailHistory: "Verified retail price $38 from Dec 2023"
    },
    {
        id: 10,
        image: "https://images.unsplash.com/photo-1629196914168-3a2652305f9f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Hand-Carved Wooden Sculpture",
        category: "Art",
        rating: 5,
        ratingCount: 11,
        originalPrice: 180.00,
        salePrice: 125.00,
        discountPercent: 31,
        verificationDate: "2024-01-09",
        retailHistory: "Verified retail price $180 from Nov 2023"
    },
    {
        id: 11,
        image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=688&q=80",
        title: "Ethiopian Cross Pendant",
        category: "Jewelry",
        rating: 5,
        ratingCount: 23,
        originalPrice: 125.00,
        salePrice: 92.50,
        discountPercent: 26,
        verificationDate: "2024-01-13",
        retailHistory: "Verified retail price $125 from Dec 2023"
    },
    {
        id: 12,
        image: "https://images.unsplash.com/photo-1590047698081-c5f87b5d28e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Traditional Ethiopian Shawl",
        category: "Textiles",
        rating: 4,
        ratingCount: 16,
        originalPrice: 95.00,
        salePrice: 78.50,
        discountPercent: 17,
        verificationDate: "2024-01-17",
        retailHistory: "Verified retail price $95 from Jan 2024"
    },
    {
        id: 13,
        image: "https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Korarima Spice Pods",
        category: "Spices",
        rating: 4,
        ratingCount: 19,
        originalPrice: 32.00,
        salePrice: 22.75,
        discountPercent: 29,
        verificationDate: "2024-01-19",
        retailHistory: "Verified retail price $32 from Jan 2024"
    },
    {
        id: 14,
        image: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80",
        title: "Complete Coffee Ceremony Set",
        category: "Coffee",
        rating: 5,
        ratingCount: 19,
        originalPrice: 125.00,
        salePrice: 89.00,
        discountPercent: 29,
        verificationDate: "2024-01-07",
        retailHistory: "Verified retail price $125 from Nov 2023"
    },
    {
        id: 15,
        image: "https://images.unsplash.com/photo-1602166242292-93a00e63e8e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Traditional Incense Burner",
        category: "Home Decor",
        rating: 5,
        ratingCount: 8,
        originalPrice: 45.00,
        salePrice: 32.50,
        discountPercent: 28,
        verificationDate: "2024-01-21",
        retailHistory: "Verified retail price $45 from Jan 2024"
    },
    {
        id: 16,
        image: "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1160&q=80",
        title: "Traditional Leather Shoes",
        category: "Footwear",
        rating: 4,
        ratingCount: 18,
        originalPrice: 135.00,
        salePrice: 95.00,
        discountPercent: 30,
        verificationDate: "2024-01-06",
        retailHistory: "Verified retail price $135 from Oct 2023"
    },
    {
        id: 17,
        image: "https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80",
        title: "Traditional Ethiopian Kemis",
        category: "Clothing",
        rating: 4.5,
        ratingCount: 32,
        originalPrice: 180.00,
        salePrice: 135.00,
        discountPercent: 25,
        verificationDate: "2024-01-22",
        retailHistory: "Verified retail price $180 from Jan 2024"
    },
    {
        id: 18,
        image: "https://images.unsplash.com/photo-1590047698081-c5f87b5d28e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Modern Ethiopian Fusion Dress",
        category: "Clothing",
        rating: 4.8,
        ratingCount: 24,
        originalPrice: 220.00,
        salePrice: 165.00,
        discountPercent: 25,
        verificationDate: "2024-01-20",
        retailHistory: "Verified retail price $220 from Jan 2024"
    },
    {
        id: 19,
        image: "https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "LFLshop Branded Tote Bag",
        category: "Merchandise",
        rating: 4.2,
        ratingCount: 45,
        originalPrice: 35.00,
        salePrice: 25.00,
        discountPercent: 29,
        verificationDate: "2024-01-18",
        retailHistory: "Verified retail price $35 from Jan 2024"
    },
    {
        id: 20,
        image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Ethiopian Flag Souvenir Mug",
        category: "Merchandise",
        rating: 3.8,
        ratingCount: 28,
        originalPrice: 25.00,
        salePrice: 18.00,
        discountPercent: 28,
        verificationDate: "2024-01-15",
        retailHistory: "Verified retail price $25 from Dec 2023"
    },
    {
        id: 21,
        image: "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        title: "Handwoven Ethiopian Scarf Set",
        category: "Clothing",
        rating: 4.7,
        ratingCount: 19,
        originalPrice: 85.00,
        salePrice: 65.00,
        discountPercent: 24,
        verificationDate: "2024-01-19",
        retailHistory: "Verified retail price $85 from Jan 2024"
    }
];

// Global variables for filtering
let currentFilters = {
    categories: [],
    minPrice: 0,
    maxPrice: 5000,
    rating: 0,
    sortBy: 'discount'
};

let filteredSaleProducts = [...saleProducts];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initializeSalePage();

    // Setup filter event listeners
    setupSaleFilterListeners();

    // Setup collapsible filters
    setupCollapsibleFilters();

    // Setup CTA slideshow
    setupCTASlideshow();

    // Profile dropdown functionality
    setupProfileDropdown();
});

// Initialize sale page
function initializeSalePage() {
    loadSaleProducts();
    updateSaleStats();
    updateResultsCounter();
}

// Load and display sale products
function loadSaleProducts() {
    renderSaleProducts(getFilteredSaleProducts());
}

// Create sale product card HTML - Clean minimalist design matching collections page
function createSaleProductCard(product) {
    // Handle decimal ratings for star display
    const fullStars = Math.floor(product.rating);
    const hasHalfStar = product.rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    const stars = '★'.repeat(fullStars) +
                  (hasHalfStar ? '⭐' : '') +
                  '☆'.repeat(emptyStars);

    // Generate product detail page URL
    const productUrl = getSaleProductDetailUrl(product);

    return `
        <div class="product-card" onclick="window.location.href='${productUrl}'">
            <div class="product-image">
                <img src="${product.image}" alt="${product.title}">
                <div class="product-overlay">
                    <button class="btn btn-primary" onclick="event.stopPropagation(); openSaleQuickView(${product.id})">
                        <i class="fas fa-eye"></i> Quick View
                    </button>
                    <button class="btn btn-outline wishlist-btn" onclick="event.stopPropagation(); toggleSaleWishlist(this, ${product.id})">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-title">${product.title}</h3>
                <p class="product-category">${product.category}</p>
                <div class="product-rating">
                    <span class="stars">${stars}</span>
                    <span class="rating-count">(${product.ratingCount})</span>
                </div>
                <div class="product-price">
                    <span class="current-price">${product.salePrice.toFixed(2)} ETB</span>
                </div>
                <div class="product-seller">
                    <span class="seller-name">Ethiopian Artisan</span>
                    <span class="seller-location">Addis Ababa</span>
                </div>
            </div>
        </div>
    `;
}

// Function to generate product detail page URL for sale products
function getSaleProductDetailUrl(product) {
    // Map specific sale products to their dedicated pages
    const saleProductPageMap = {
        1: 'product-habesha-dress.html', // Traditional Habesha Dress
        // Add more specific product pages as they are created
    };

    // If there's a specific page for this product, use it
    if (saleProductPageMap[product.id]) {
        return saleProductPageMap[product.id];
    }

    // Otherwise, use the generic product detail page with parameters
    const productSlug = product.title.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');

    return `product-detail.html?id=${product.id}&slug=${productSlug}&sale=true`;
}

// Function to open quick view modal for sale products
function openSaleQuickView(productId) {
    const product = saleProducts.find(p => p.id === productId);
    if (product) {
        console.log('Opening quick view for sale product:', product.title);
        // TODO: Implement quick view modal
        // For now, redirect to product detail page
        window.location.href = getSaleProductDetailUrl(product);
    }
}

// Function to toggle wishlist for sale products
function toggleSaleWishlist(button, productId) {
    const icon = button.querySelector('i');

    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        button.classList.remove('btn-outline');
        button.classList.add('btn-primary');
        console.log('Added sale product', productId, 'to wishlist');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        button.classList.remove('btn-primary');
        button.classList.add('btn-outline');
        console.log('Removed sale product', productId, 'from wishlist');
    }
}

// Render sale products to grid
function renderSaleProducts(products) {
    const grid = document.getElementById('sale-grid');
    if (!grid) return;

    grid.innerHTML = products.map(product => createSaleProductCard(product)).join('');

    // Attach event listeners
    attachSaleProductEventListeners();
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

// Setup filter event listeners
function setupSaleFilterListeners() {
    // Category checkboxes
    const categoryCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSaleFilters);
    });

    // Price inputs
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');

    if (minPriceInput) minPriceInput.addEventListener('input', updateSaleFilters);
    if (maxPriceInput) maxPriceInput.addEventListener('input', updateSaleFilters);

    // Rating radio buttons
    const ratingRadios = document.querySelectorAll('.rating-group input[type="radio"]');
    ratingRadios.forEach(radio => {
        radio.addEventListener('change', updateSaleFilters);
    });

    // Sort select
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) sortSelect.addEventListener('change', updateSaleFilters);

    // Clear filters button
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearAllSaleFilters);
}

// Update filters from form inputs
function updateSaleFilters() {
    // Get selected categories
    const categoryCheckboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]:checked');
    currentFilters.categories = Array.from(categoryCheckboxes).map(cb => cb.value);

    // Get price range
    const minPrice = document.getElementById('min-price').value;
    const maxPrice = document.getElementById('max-price').value;
    currentFilters.minPrice = minPrice ? parseFloat(minPrice) : 0;
    currentFilters.maxPrice = maxPrice ? parseFloat(maxPrice) : 5000;

    // Get rating
    const selectedRating = document.querySelector('.rating-group input[type="radio"]:checked');
    currentFilters.rating = selectedRating ? parseFloat(selectedRating.value) : 0;

    // Get sort option
    const sortSelect = document.getElementById('sort-select');
    currentFilters.sortBy = sortSelect ? sortSelect.value : 'discount';

    // Apply filters and update display
    applySaleFilters();

    // Update active filter tags
    updateActiveFilterTags();
}

// Apply current filters to sale products
function applySaleFilters() {
    filteredSaleProducts = saleProducts.filter(product => {
        // Category filter
        if (currentFilters.categories.length > 0 &&
            !currentFilters.categories.includes(product.category)) {
            return false;
        }

        // Price filter (sale price)
        if (product.salePrice < currentFilters.minPrice ||
            product.salePrice > currentFilters.maxPrice) {
            return false;
        }

        // Rating filter
        if (currentFilters.rating > 0) {
            if (currentFilters.rating === 5) {
                // 5 stars only - exact match
                if (product.rating !== 5) return false;
            } else {
                // All other ratings - greater than or equal
                if (product.rating < currentFilters.rating) return false;
            }
        }

        return true;
    });

    // Sort products
    sortSaleProducts();

    // Update display
    renderSaleProducts(filteredSaleProducts);
    updateResultsCounter();
}

// Update active filter tags
function updateActiveFilterTags() {
    const activeFiltersContainer = document.getElementById('active-filters');
    if (!activeFiltersContainer) return;

    let filterTags = [];

    // Add category filters
    currentFilters.categories.forEach(category => {
        filterTags.push({
            type: 'category',
            value: category,
            label: category
        });
    });

    // Add price range filter
    if (currentFilters.minPrice > 0 || currentFilters.maxPrice < 5000) {
        const minPrice = currentFilters.minPrice || 0;
        const maxPrice = currentFilters.maxPrice || 5000;
        filterTags.push({
            type: 'price',
            value: `${minPrice}-${maxPrice}`,
            label: `${minPrice} - ${maxPrice} ETB`
        });
    }

    // Add rating filter
    if (currentFilters.rating > 0) {
        let label;
        if (currentFilters.rating === 5) {
            label = '★★★★★ 5 stars only';
        } else if (currentFilters.rating === 4.5) {
            label = '★★★★★ 4.5+ stars';
        } else if (currentFilters.rating === 4) {
            label = '★★★★☆ 4+ stars';
        } else if (currentFilters.rating === 3.5) {
            label = '★★★★☆ 3.5+ stars';
        } else if (currentFilters.rating === 3) {
            label = '★★★☆☆ 3+ stars';
        } else if (currentFilters.rating === 2) {
            label = '★★☆☆☆ 2+ stars';
        } else {
            label = `${currentFilters.rating}+ stars`;
        }

        filterTags.push({
            type: 'rating',
            value: currentFilters.rating,
            label: label
        });
    }

    // Render filter tags
    activeFiltersContainer.innerHTML = filterTags.map(tag => `
        <div class="filter-tag">
            <span>${tag.label}</span>
            <button class="remove-filter" onclick="removeFilter('${tag.type}', '${tag.value}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

// Remove individual filter
function removeFilter(type, value) {
    switch(type) {
        case 'category':
            const categoryCheckbox = document.querySelector(`input[value="${value}"]`);
            if (categoryCheckbox) categoryCheckbox.checked = false;
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
    updateSaleFilters();
}

// Sort filtered sale products
function sortSaleProducts() {
    switch(currentFilters.sortBy) {
        case 'discount':
            filteredSaleProducts.sort((a, b) => b.discountPercent - a.discountPercent);
            break;
        case 'price-low':
            filteredSaleProducts.sort((a, b) => a.salePrice - b.salePrice);
            break;
        case 'price-high':
            filteredSaleProducts.sort((a, b) => b.salePrice - a.salePrice);
            break;
        case 'rating':
            filteredSaleProducts.sort((a, b) => b.rating - a.rating);
            break;
        case 'savings':
            filteredSaleProducts.sort((a, b) => (b.originalPrice - b.salePrice) - (a.originalPrice - a.salePrice));
            break;
    }
}

// Get filtered sale products
function getFilteredSaleProducts() {
    return filteredSaleProducts;
}

// Update results counter
function updateResultsCounter() {
    const counter = document.getElementById('results-count');
    if (counter) {
        const count = filteredSaleProducts.length;
        const total = saleProducts.length;

        if (count === total) {
            counter.textContent = `Showing all ${total} sale items`;
        } else {
            counter.textContent = `Showing ${count} of ${total} sale items`;
        }
    }
}

// Update sale statistics
function updateSaleStats() {
    const saleCountElement = document.getElementById('sale-count');
    if (saleCountElement) {
        saleCountElement.textContent = saleProducts.length;
    }
}

// Clear all filters
function clearAllSaleFilters() {
    // Reset filter object
    currentFilters = {
        categories: [],
        minPrice: 0,
        maxPrice: 5000,
        rating: 0,
        sortBy: 'discount'
    };

    // Reset form inputs
    document.querySelectorAll('.checkbox-group input[type="checkbox"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('.rating-group input[type="radio"]').forEach(radio => radio.checked = false);
    document.getElementById('min-price').value = '';
    document.getElementById('max-price').value = '';
    document.getElementById('sort-select').value = 'discount';

    // Apply filters
    applySaleFilters();

    // Update active filter tags
    updateActiveFilterTags();
}

// Setup profile dropdown
function setupProfileDropdown() {
    const profileDropdown = document.querySelector('.profile-dropdown');
    const userIcon = document.querySelector('.user-icon');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (userIcon && dropdownMenu) {
        userIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// Setup CTA Slideshow
function setupCTASlideshow() {
    const slides = document.querySelectorAll('.cta-slide');
    const dots = document.querySelectorAll('.slide-dot');
    let currentSlide = 0;
    let slideInterval;

    if (slides.length === 0) return;

    // Function to show specific slide
    function showSlide(index) {
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        // Add active class to current slide and dot
        slides[index].classList.add('active');
        dots[index].classList.add('active');

        currentSlide = index;
    }

    // Function to go to next slide
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    // Auto-advance slides
    function startSlideshow() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }

    function stopSlideshow() {
        clearInterval(slideInterval);
    }

    // Add click event listeners to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            stopSlideshow();
            startSlideshow(); // Restart auto-advance
        });
    });

    // Pause slideshow on hover
    const slideshowContainer = document.querySelector('.slideshow-container');
    if (slideshowContainer) {
        slideshowContainer.addEventListener('mouseenter', stopSlideshow);
        slideshowContainer.addEventListener('mouseleave', startSlideshow);
    }

    // Start the slideshow
    startSlideshow();
}

// Mobile filters are now handled by the unified collapsible design

// Attach event listeners to product cards
function attachSaleProductEventListeners() {
    // Event listeners are now handled via inline onclick handlers in the HTML
    // This function is kept for any additional functionality that might be needed

    // The product cards now use the standard .product-card class
    // which inherits hover effects from the main styles.css
}
