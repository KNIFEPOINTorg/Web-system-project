// Home Page JavaScript - Dynamic Content and Interactions
// Handles role-based content, product displays, and user interactions

// Enhanced featured products data with demo integration
function getFeaturedProducts() {
    // Base featured products with demo seller integration
    let featuredProducts = [
        {
            id: 1,
            name: "Traditional Habesha Dress",
            description: "Handwoven traditional Ethiopian dress with intricate patterns and cultural motifs",
            price: 8500,
            image: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
            sellerId: "meron_textiles",
            seller: "Meron Traditional Textiles",
            sellerOwner: "Meron Tadesse",
            location: "Addis Ababa",
            rating: 4.8,
            reviews: 127,
            category: "traditional-textiles",
            tags: ["traditional", "habesha", "dress", "handwoven"],
            featured: true
        },
    {
        id: 2,
        name: "Silver Ethiopian Cross",
        description: "Handcrafted silver cross pendant with traditional Ethiopian Orthodox design",
        price: 4200,
        image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        sellerId: "sara_jewelry",
        seller: "Sara Silver Crafts",
        sellerOwner: "Sara Bekele",
        location: "Lalibela",
        rating: 4.9,
        reviews: 89,
        category: "jewelry-accessories",
        tags: ["silver", "cross", "religious", "handcrafted"],
        featured: true
    },
    {
        id: 3,
        name: "Ethiopian Coffee - Yirgacheffe",
        description: "Premium single-origin coffee beans from the birthplace of coffee",
        price: 1250,
        image: "https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        sellerId: "desta_coffee",
        seller: "Highland Coffee Cooperative",
        sellerOwner: "Desta Haile",
        location: "Yirgacheffe",
        rating: 4.7,
        reviews: 203,
        category: "coffee-beverages",
        tags: ["coffee", "yirgacheffe", "single-origin", "premium"]
    },
    {
        id: 4,
        name: "Traditional Clay Jebena",
        description: "Authentic Ethiopian coffee pot made from local clay using traditional methods",
        price: 2800,
        image: "https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        sellerId: "rahel_pottery",
        seller: "Pottery Masters Dire Dawa",
        sellerOwner: "Rahel Girma",
        location: "Dire Dawa",
        rating: 4.6,
        reviews: 156,
        category: "pottery-ceramics",
        tags: ["jebena", "clay", "coffee", "traditional"],
        featured: true
    }];

    // Integrate with demo data if available
    if (typeof demoCategoryProducts !== 'undefined') {
        // Add some demo products to featured products
        Object.keys(demoCategoryProducts).forEach(categoryId => {
            const categoryProducts = demoCategoryProducts[categoryId];
            if (categoryProducts.length > 0) {
                // Add first product from each category as featured
                const firstProduct = { ...categoryProducts[0] };
                firstProduct.featured = true;

                // Check if not already in featured products
                const exists = featuredProducts.find(p => p.id === firstProduct.id);
                if (!exists && featuredProducts.length < 8) {
                    featuredProducts.push(firstProduct);
                }
            }
        });
    }

    return featuredProducts;
}

const trendingProducts = [
    {
        id: 5,
        name: "Berbere Spice Blend",
        description: "Authentic Ethiopian spice blend with 16 traditional ingredients",
        price: 850,
        image: "https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        sellerId: "yohannes_spice",
        seller: "Spice Route Ethiopia",
        sellerOwner: "Yohannes Mulugeta",
        location: "Bahir Dar",
        rating: 4.8,
        reviews: 342,
        trending: true,
        category: "spices-food",
        tags: ["berbere", "spice", "traditional", "blend"]
    },
    {
        id: 6,
        name: "Handwoven Cotton Shawl",
        description: "Beautiful cotton shawl with traditional Ethiopian patterns and colors",
        price: 3200,
        image: "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        sellerId: "meron_seller",
        seller: "Meron Traditional Crafts",
        sellerOwner: "Meron Tadesse",
        location: "Addis Ababa",
        rating: 4.5,
        reviews: 98,
        trending: true,
        category: "traditional-textiles",
        tags: ["shawl", "cotton", "handwoven", "traditional"]
    },
    {
        id: 7,
        name: "Silver Ethiopian Earrings",
        description: "Traditional silver earrings with Ethiopian cultural motifs and filigree work",
        price: 5500,
        image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        sellerId: "alemayehu_seller",
        seller: "Alemayehu Silver Works",
        sellerOwner: "Alemayehu Bekele",
        location: "Lalibela",
        rating: 4.9,
        reviews: 67,
        trending: true,
        category: "jewelry-accessories",
        tags: ["earrings", "silver", "traditional", "filigree"]
    },
    {
        id: 8,
        name: "Sidamo Coffee Beans",
        description: "Rich and full-bodied coffee beans from the Sidamo region",
        price: 1150,
        image: "https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80",
        sellerId: "desta_coffee",
        seller: "Highland Coffee Cooperative",
        sellerOwner: "Desta Haile",
        location: "Yirgacheffe",
        rating: 4.6,
        reviews: 189,
        trending: true,
        category: "coffee-beverages",
        tags: ["coffee", "sidamo", "beans", "premium"]
    }
];

// Initialize home page functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeHomePage();
    setupEventListeners();
    updateCartIcon();
});

// Initialize all home page components
function initializeHomePage() {
    setupHeroActions();
    displayFeaturedProducts();
    displayTrendingProducts();
    setupSellerFooterLinks();
    setupCategoryNavigation();
    animateCounters();
}

// Setup hero section actions based on user status
function setupHeroActions() {
    const heroActions = document.getElementById('hero-actions');
    const currentUser = getCurrentUser();
    
    let actionsHTML = '';
    
    if (!currentUser) {
        // Guest user actions
        actionsHTML = `
            <button class="btn btn-primary hero-cta" onclick="window.location.href='collections.html'">
                <i class="fas fa-shopping-bag"></i>
                Start Shopping
            </button>
            <button class="btn btn-outline hero-cta" onclick="window.location.href='signup.html'">
                <i class="fas fa-user-plus"></i>
                Join LFLshop
            </button>
        `;
    } else if (currentUser.userType === 'shop-only') {
        // Shop Only user actions
        actionsHTML = `
            <button class="btn btn-primary hero-cta" onclick="window.location.href='collections.html'">
                <i class="fas fa-shopping-bag"></i>
                Continue Shopping
            </button>
            <button class="btn btn-secondary hero-cta" onclick="showBecomeSeller()">
                <i class="fas fa-store"></i>
                Become a Seller
            </button>
        `;
    } else {
        // Shop & Sell user actions
        actionsHTML = `
            <button class="btn btn-primary hero-cta" onclick="window.location.href='collections.html'">
                <i class="fas fa-shopping-bag"></i>
                Shop Products
            </button>
            <button class="btn btn-secondary hero-cta" onclick="window.location.href='seller-dashboard.html'">
                <i class="fas fa-chart-line"></i>
                Seller Dashboard
            </button>
        `;
    }
    
    heroActions.innerHTML = actionsHTML;
}

// Display featured products with loading animation
function displayFeaturedProducts() {
    const grid = document.getElementById('featured-products-grid');
    if (!grid) return;

    // Add loading state
    grid.innerHTML = '<div class="loading-spinner">Loading featured products...</div>';

    // Get featured products (including demo data)
    const featuredProducts = getFeaturedProducts();

    // Simulate loading delay for better UX
    setTimeout(() => {
        grid.innerHTML = featuredProducts.map(product => createProductCard(product)).join('');

        // Add loading animation to cards and keyboard support
        const cards = grid.querySelectorAll('.product-card');
        cards.forEach((card, index) => {
            card.classList.add('loading');
            card.style.animationDelay = `${index * 0.1}s`;

            // Add keyboard navigation
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.click();
                }
            });
        });
    }, 300);
}

// Display trending products with loading animation
function displayTrendingProducts() {
    const grid = document.getElementById('trending-products-grid');
    if (!grid) return;

    // Add loading state
    grid.innerHTML = '<div class="loading-spinner">Loading trending products...</div>';

    // Simulate loading delay for better UX
    setTimeout(() => {
        grid.innerHTML = trendingProducts.map(product => createProductCard(product)).join('');

        // Add loading animation to cards and keyboard support
        const cards = grid.querySelectorAll('.product-card');
        cards.forEach((card, index) => {
            card.classList.add('loading');
            card.style.animationDelay = `${index * 0.1}s`;

            // Add keyboard navigation
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.click();
                }
            });
        });
    }, 500);
}

// Create product card HTML - Clean design without sale elements
function createProductCard(product) {
    const trendingBadge = product.trending ?
        `<div class="product-badge trending">🔥 Trending</div>` : '';

    const featuredBadge = product.featured ?
        `<div class="product-badge featured">⭐ Featured</div>` : '';

    return `
        <div class="product-card" onclick="viewProduct(${product.id})" tabindex="0" role="button" aria-label="View ${product.name}">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}" loading="lazy">
                ${trendingBadge}
                ${featuredBadge}
                <div class="product-overlay">
                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); addToCart(${product.id})" aria-label="Add ${product.name} to cart">
                        <i class="fas fa-shopping-cart"></i>
                        Add to Cart
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); addToWishlist(${product.id})" aria-label="Add ${product.name} to wishlist">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-title">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-rating">
                    <div class="stars">
                        ${generateStars(product.rating)}
                    </div>
                    <span class="rating-text">${product.rating} (${product.reviews} reviews)</span>
                </div>
                <div class="product-price">
                    <span class="current-price">${product.price.toLocaleString()} ETB</span>
                </div>
                <div class="product-seller">
                    <i class="fas fa-store"></i>
                    <span>By <a href="#" onclick="event.stopPropagation(); viewSellerProfile('${product.sellerId}')" class="seller-link">${product.seller}</a></span>
                    <span class="seller-location">${product.location}</span>
                </div>
            </div>
        </div>
    `;
}

// Generate star rating HTML
function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
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

// Setup seller footer links based on user status
function setupSellerFooterLinks() {
    const sellerLinks = document.getElementById('seller-links');
    const sectionTitle = document.getElementById('seller-section-title');
    const currentUser = getCurrentUser();
    
    let linksHTML = '';
    let title = 'For Sellers';
    
    if (!currentUser) {
        // Guest user
        title = 'Become a Seller';
        linksHTML = `
            <li><a href="signup.html">Sign Up to Sell</a></li>
            <li><a href="seller-benefits.html">Seller Benefits</a></li>
            <li><a href="how-to-sell.html">How to Sell</a></li>
            <li><a href="seller-success.html">Success Stories</a></li>
            <li><a href="seller-support.html">Seller Support</a></li>
        `;
    } else if (currentUser.userType === 'shop-only') {
        // Shop Only user
        title = 'Start Selling';
        linksHTML = `
            <li><a href="upgrade-account.html">Upgrade to Seller</a></li>
            <li><a href="seller-benefits.html">Seller Benefits</a></li>
            <li><a href="seller-requirements.html">Requirements</a></li>
            <li><a href="seller-fees.html">Fees & Pricing</a></li>
            <li><a href="seller-support.html">Get Support</a></li>
        `;
    } else {
        // Shop & Sell user
        title = 'Seller Resources';
        linksHTML = `
            <li><a href="seller-dashboard.html">Seller Dashboard</a></li>
            <li><a href="add-product.html">Add Products</a></li>
            <li><a href="manage-orders.html">Manage Orders</a></li>
            <li><a href="seller-analytics.html">View Analytics</a></li>
            <li><a href="seller-support.html">Seller Support</a></li>
        `;
    }
    
    sectionTitle.textContent = title;
    sellerLinks.innerHTML = linksHTML;
}

// Setup category navigation
function setupCategoryNavigation() {
    const categoryCards = document.querySelectorAll('.category-card');
    
    categoryCards.forEach(card => {
        const button = card.querySelector('.category-btn');
        const categoryType = card.getAttribute('data-category');
        
        // Add click functionality to the entire card
        card.addEventListener('click', function(e) {
            if (e.target === button) return;
            
            // Navigate to collections with category filter
            window.location.href = `collections.html?category=${categoryType}`;
        });
        
        // Button click functionality
        if (button) {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                window.location.href = `collections.html?category=${categoryType}`;
            });
        }
    });
}

// Animate counters in hero section
function animateCounters() {
    const counters = document.querySelectorAll('.trust-number');
    
    const animateCounter = (element, target) => {
        const increment = target / 100;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            if (target >= 1000) {
                element.textContent = Math.floor(current / 1000) + 'K+';
            } else {
                element.textContent = Math.floor(current) + '+';
            }
        }, 20);
    };
    
    // Animate when in viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const text = element.textContent;
                const target = parseInt(text.replace(/[^\d]/g, ''));
                
                animateCounter(element, target);
                observer.unobserve(element);
            }
        });
    });
    
    counters.forEach(counter => observer.observe(counter));
}

// Setup event listeners
function setupEventListeners() {
    // Welcome message for logged-in users
    displayWelcomeMessage();
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Display welcome message for logged-in users
function displayWelcomeMessage() {
    const currentUser = getCurrentUser();
    if (currentUser) {
        showSuccessToast(`Welcome back, ${currentUser.name}! 🇪🇹`);
    }
}

// Product interaction functions
function viewProduct(productId) {
    window.location.href = `product-detail.html?id=${productId}`;
}

function addToCart(productId) {
    // Find product in both arrays
    const product = [...featuredProducts, ...trendingProducts].find(p => p.id === productId);
    if (product) {
        addToCartFunction(product);
        showSuccessToast(`${product.name} added to cart!`);
    }
}

function addToWishlist(productId) {
    const product = [...featuredProducts, ...trendingProducts].find(p => p.id === productId);
    if (product) {
        // Add to wishlist logic here
        showInfoToast(`${product.name} added to wishlist!`);
    }
}

// Show become seller modal
function showBecomeSeller() {
    // This would open a modal or redirect to upgrade page
    showInfoToast('Redirecting to seller upgrade page...');
    setTimeout(() => {
        window.location.href = 'upgrade-account.html';
    }, 1000);
}

// Enhanced seller profile navigation with demo integration
function viewSellerProfile(sellerId) {
    console.log(`Viewing seller profile: ${sellerId}`);

    // Check if seller exists in demo data first
    if (typeof demoSellers !== 'undefined' && demoSellers[sellerId]) {
        const seller = demoSellers[sellerId];

        // Navigate to seller profile page with seller data
        const sellerProfileUrl = `seller-profile.html?seller=${sellerId}`;
        window.location.href = sellerProfileUrl;

        showSuccessToast(`Viewing ${seller.businessName} profile`);
        return;
    }

    // Check if seller exists in main seller system
    if (typeof getSellerById === 'function') {
        const seller = getSellerById(sellerId);
        if (seller) {
            // Redirect to seller dashboard or profile page
            window.location.href = `seller-profile.html?id=${sellerId}`;
        } else {
            showErrorToast('Seller not found');
        }
    } else {
        // Fallback if seller-accounts.js not loaded
        window.location.href = `seller-profile.html?id=${sellerId}`;
    }
}

// Export functions for global use
window.viewProduct = viewProduct;
window.addToCart = addToCart;
window.addToWishlist = addToWishlist;
window.showBecomeSeller = showBecomeSeller;
window.viewSellerProfile = viewSellerProfile;
