// Seller Profile JavaScript - Ethiopian Marketplace
// Displays detailed seller information and products

// Current seller data
let currentSeller = null;
let sellerProducts = [];

// Initialize seller profile when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeSellerProfile();
    setupEventListeners();
    updateCartIcon();
});

// Initialize seller profile
function initializeSellerProfile() {
    // Get seller ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const sellerId = urlParams.get('id');
    
    if (!sellerId) {
        showErrorToast('Seller not found');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 2000);
        return;
    }
    
    // Load seller data
    loadSellerData(sellerId);
    
    // Setup navigation
    setupSectionNavigation();
    
    // Show initial section
    showSection('products');
}

// Load seller data
function loadSellerData(sellerId) {
    // Get seller from demo sellers
    if (typeof getSellerById === 'function') {
        currentSeller = getSellerById(sellerId);
    }
    
    if (!currentSeller) {
        showErrorToast('Seller not found');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 2000);
        return;
    }
    
    // Update UI with seller information
    updateSellerInfo();
    
    // Load seller products
    loadSellerProducts();
}

// Update seller information in UI
function updateSellerInfo() {
    if (!currentSeller) return;
    
    // Update header information
    document.getElementById('seller-business-name').textContent = currentSeller.businessName;
    document.getElementById('seller-owner-name').textContent = currentSeller.ownerName;
    document.getElementById('seller-location').textContent = `${currentSeller.location.city}, ${currentSeller.location.region}`;
    
    // Update avatar
    const avatarImg = document.getElementById('seller-avatar');
    if (avatarImg && currentSeller.profileImage) {
        avatarImg.src = currentSeller.profileImage;
        avatarImg.alt = currentSeller.businessName;
    }
    
    // Show verified badge if verified
    if (currentSeller.verified) {
        const verifiedBadge = document.getElementById('verified-badge');
        if (verifiedBadge) {
            verifiedBadge.style.display = 'flex';
        }
    }
    
    // Update stats
    document.getElementById('seller-rating').textContent = currentSeller.rating.toFixed(1);
    document.getElementById('total-reviews').textContent = currentSeller.totalReviews.toLocaleString();
    document.getElementById('total-sales').textContent = currentSeller.totalSales.toLocaleString();
    
    // Update member since
    const joinedYear = new Date(currentSeller.joinedDate).getFullYear();
    document.getElementById('member-since').textContent = joinedYear;
    
    // Update rating stars
    updateRatingStars('rating-stars', currentSeller.rating);
    updateRatingStars('overall-stars', currentSeller.rating);
    
    // Update about section
    updateAboutSection();
}

// Update rating stars display
function updateRatingStars(elementId, rating) {
    const container = document.getElementById(elementId);
    if (!container) return;
    
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
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
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < emptyStars; i++) {
        starsHTML += '<i class="far fa-star"></i>';
    }
    
    container.innerHTML = starsHTML;
}

// Update about section
function updateAboutSection() {
    if (!currentSeller) return;
    
    // Update description
    document.getElementById('seller-description').textContent = currentSeller.description;
    
    // Update business details
    document.getElementById('business-type').textContent = currentSeller.businessType;
    document.getElementById('established-year').textContent = currentSeller.established;
    document.getElementById('languages').textContent = currentSeller.languages.join(', ');
    
    // Update specialties
    const specialtiesContainer = document.getElementById('specialties');
    if (specialtiesContainer && currentSeller.specialties) {
        specialtiesContainer.innerHTML = currentSeller.specialties.map(specialty => 
            `<span class="specialty-tag">${specialty}</span>`
        ).join('');
    }
    
    // Update business hours
    updateBusinessHours();
    
    // Update reviews count
    document.getElementById('overall-rating').textContent = currentSeller.rating.toFixed(1);
    document.getElementById('reviews-count').textContent = currentSeller.totalReviews;
}

// Update business hours
function updateBusinessHours() {
    const hoursContainer = document.getElementById('business-hours');
    if (!hoursContainer || !currentSeller.businessHours) return;
    
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    const hoursHTML = days.map((day, index) => {
        const hours = currentSeller.businessHours[day] || 'Closed';
        return `
            <div class="hours-item">
                <span class="day">${dayNames[index]}</span>
                <span class="time">${hours}</span>
            </div>
        `;
    }).join('');
    
    hoursContainer.innerHTML = hoursHTML;
}

// Load seller products
function loadSellerProducts() {
    // Get products from home page data that belong to this seller
    if (typeof featuredProducts !== 'undefined' && typeof trendingProducts !== 'undefined') {
        const allProducts = [...featuredProducts, ...trendingProducts];
        sellerProducts = allProducts.filter(product => 
            product.sellerId === currentSeller.id ||
            product.seller === currentSeller.businessName ||
            product.sellerOwner === currentSeller.ownerName
        );
    }
    
    // Display products
    displayProducts(sellerProducts);
}

// Display products
function displayProducts(products) {
    const container = document.getElementById('seller-products');
    if (!container) return;
    
    if (products.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No products available</h3>
                <p>This seller hasn't listed any products yet.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = products.map(product => `
        <div class="product-card" onclick="viewProduct(${product.id})">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
                <div class="product-overlay">
                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); addToCart(${product.id})">
                        <i class="fas fa-shopping-cart"></i>
                        Add to Cart
                    </button>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-price">
                    <span class="current-price">${product.price.toLocaleString()}</span>
                    <span class="price-currency">ETB</span>
                </div>
                <div class="product-rating">
                    <div class="stars">
                        ${generateStars(product.rating)}
                    </div>
                    <span class="reviews">(${product.reviews})</span>
                </div>
            </div>
            <div class="product-actions">
                <button class="btn btn-primary" onclick="event.stopPropagation(); addToCart(${product.id})">
                    <i class="fas fa-cart-plus"></i>
                    Add to Cart
                </button>
                <button class="btn btn-outline" onclick="event.stopPropagation(); addToWishlist(${product.id})">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// Generate stars HTML
function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    let starsHTML = '';
    
    for (let i = 0; i < fullStars; i++) {
        starsHTML += '<i class="fas fa-star"></i>';
    }
    
    if (hasHalfStar) {
        starsHTML += '<i class="fas fa-star-half-alt"></i>';
    }
    
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < emptyStars; i++) {
        starsHTML += '<i class="far fa-star"></i>';
    }
    
    return starsHTML;
}

// Setup section navigation
function setupSectionNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const section = this.getAttribute('data-section');
            if (section) {
                showSection(section);
                
                // Update active state
                navLinks.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
}

// Show specific section
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.seller-section');
    sections.forEach(section => section.classList.remove('active'));
    
    // Show target section
    const targetSection = document.getElementById(`${sectionName}-section`);
    if (targetSection) {
        targetSection.classList.add('active');
    }
}

// Setup event listeners
function setupEventListeners() {
    // Category filter
    const categoryFilter = document.getElementById('category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            filterProducts();
        });
    }
    
    // Sort filter
    const sortFilter = document.getElementById('sort-filter');
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            sortProducts();
        });
    }
}

// Filter products by category
function filterProducts() {
    const categoryFilter = document.getElementById('category-filter');
    const selectedCategory = categoryFilter.value;
    
    let filteredProducts = sellerProducts;
    
    if (selectedCategory) {
        filteredProducts = sellerProducts.filter(product => 
            product.category === selectedCategory
        );
    }
    
    displayProducts(filteredProducts);
}

// Sort products
function sortProducts() {
    const sortFilter = document.getElementById('sort-filter');
    const sortBy = sortFilter.value;
    
    let sortedProducts = [...sellerProducts];
    
    switch (sortBy) {
        case 'price-low':
            sortedProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            sortedProducts.sort((a, b) => b.price - a.price);
            break;
        case 'popular':
            sortedProducts.sort((a, b) => b.reviews - a.reviews);
            break;
        case 'newest':
        default:
            // Keep original order (newest first)
            break;
    }
    
    displayProducts(sortedProducts);
}

// Seller actions
function contactSeller() {
    if (!currentSeller) return;
    
    // In a real app, this would open a contact form or messaging system
    showInfoToast(`Contact ${currentSeller.businessName} at ${currentSeller.email}`);
}

function followSeller() {
    if (!currentSeller) return;
    
    // In a real app, this would add the seller to user's followed sellers
    showSuccessToast(`Now following ${currentSeller.businessName}!`);
}

// Product actions
function viewProduct(productId) {
    window.location.href = `product-detail.html?id=${productId}`;
}

function addToCart(productId) {
    // Add to cart logic here
    showSuccessToast('Product added to cart!');
}

function addToWishlist(productId) {
    // Add to wishlist logic here
    showSuccessToast('Product added to wishlist!');
}

// Export functions for global use
window.contactSeller = contactSeller;
window.followSeller = followSeller;
window.viewProduct = viewProduct;
window.addToCart = addToCart;
window.addToWishlist = addToWishlist;
