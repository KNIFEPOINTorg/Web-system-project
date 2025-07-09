// Dashboard JavaScript

// Sample user data (in production, this would come from your backend)
const userData = {
    id: 1,
    firstName: "Sarah",
    lastName: "Johnson",
    email: "sarah@example.com",
    phone: "+1 (555) 987-6543",
    userType: "shop-only", // "shop-only" or "shop-sell"
    joinDate: "2024-01-15",
    avatar: null,
    totalSpent: 485.50,
    membershipLevel: "Bronze",
    loyaltyPoints: 245
};

// Sample orders data
const ordersData = [
    {
        id: "LFL-2024-001",
        date: "2024-01-20",
        status: "delivered",
        total: 89.99,
        items: [
            {
                name: "Traditional Habesha Dress",
                image: "https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80",
                price: 89.99,
                quantity: 1
            }
        ]
    },
    {
        id: "LFL-2024-002",
        date: "2024-01-18",
        status: "out-for-delivery",
        total: 156.00,
        items: [
            {
                name: "Silver Filigree Necklace",
                image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=688&q=80",
                price: 156.00,
                quantity: 1
            }
        ]
    },
    {
        id: "LFL-2024-003",
        date: "2024-01-15",
        status: "pending",
        total: 28.50,
        items: [
            {
                name: "Premium Yirgacheffe Coffee",
                image: "https://images.unsplash.com/photo-1559525839-d9d1e38b0a35?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                price: 28.50,
                quantity: 1
            }
        ]
    }
];

// Sample wishlist data
const wishlistData = [
    {
        id: 1,
        name: "Handwoven Storage Basket",
        image: "https://images.unsplash.com/photo-1519219788971-8d9797e0928e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1169&q=80",
        price: 45.00,
        category: "Home Decor",
        rating: 4,
        dateAdded: "2024-01-18"
    },
    {
        id: 2,
        name: "Ethiopian Coffee Ceremony Set",
        image: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80",
        price: 89.00,
        category: "Coffee",
        rating: 5,
        dateAdded: "2024-01-16"
    },
    {
        id: 3,
        name: "Authentic Berbere Spice Mix",
        image: "https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        price: 18.99,
        category: "Spices",
        rating: 5,
        dateAdded: "2024-01-20"
    },
    {
        id: 4,
        name: "Traditional Ethiopian Shawl",
        image: "https://images.unsplash.com/photo-1590047698081-c5f87b5d28e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        price: 78.50,
        category: "Textiles",
        rating: 4,
        dateAdded: "2024-01-14"
    },
    {
        id: 5,
        name: "Silver Filigree Earrings",
        image: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=688&q=80",
        price: 92.00,
        category: "Jewelry",
        rating: 5,
        dateAdded: "2024-01-12"
    }
];

// Sample recommendations data (for shoppers)
const recommendationsData = [
    {
        id: 1,
        name: "Handcrafted Leather Sandals",
        image: "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1160&q=80",
        price: 65.00,
        category: "Footwear",
        rating: 4,
        reason: "Based on your recent purchases"
    },
    {
        id: 2,
        name: "Traditional Incense Burner",
        image: "https://images.unsplash.com/photo-1602166242292-93a00e63e8e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        price: 32.50,
        category: "Home Decor",
        rating: 5,
        reason: "Complements your wishlist items"
    },
    {
        id: 3,
        name: "Ethiopian Honey",
        image: "https://images.unsplash.com/photo-1587049352851-8d4e89133924?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        price: 24.99,
        category: "Food",
        rating: 4,
        reason: "Popular with coffee lovers"
    }
];

// Sample browsing history
const browsingHistoryData = [
    {
        id: 1,
        name: "Traditional Jebena Coffee Pot",
        image: "https://images.unsplash.com/photo-1530968033775-2c92736b131e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        price: 52.00,
        category: "Pottery",
        viewedDate: "2024-01-21"
    },
    {
        id: 2,
        name: "Colorful Woven Scarf",
        image: "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        price: 42.00,
        category: "Textiles",
        viewedDate: "2024-01-20"
    }
];

// Sample products data (for sellers)
const productsData = [
    {
        id: 1,
        name: "Handcrafted Leather Sandals",
        image: "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1160&q=80",
        price: 65.00,
        stock: 12,
        status: "active",
        sales: 8
    },
    {
        id: 2,
        name: "Traditional Incense Burner",
        image: "https://images.unsplash.com/photo-1602166242292-93a00e63e8e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
        price: 32.50,
        stock: 5,
        status: "active",
        sales: 15
    }
];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Setup navigation
    setupSidebarNavigation();
    
    // Setup profile dropdown
    setupProfileDropdown();
    
    // Load initial data
    loadDashboardData();
});

// Initialize dashboard based on user type
function initializeDashboard() {
    // Update user information
    updateUserInfo();
    
    // Show/hide seller-specific elements
    toggleSellerElements();
    
    // Load default section
    switchSection('overview');
}

// Update user information in the UI
function updateUserInfo() {
    const fullName = `${userData.firstName} ${userData.lastName}`;
    const userTypeDisplay = userData.userType === 'shop-sell' ? 'Shop & Sell' : 'Shop Only';
    
    // Update navigation
    document.getElementById('user-name').textContent = fullName;
    document.getElementById('user-type').textContent = userTypeDisplay;
    
    // Update sidebar
    document.getElementById('sidebar-user-name').textContent = fullName;
    document.getElementById('sidebar-user-email').textContent = userData.email;
    document.getElementById('sidebar-user-type').textContent = userTypeDisplay;
    
    // Update profile form
    document.getElementById('profile-firstName').value = userData.firstName;
    document.getElementById('profile-lastName').value = userData.lastName;
    document.getElementById('profile-email').value = userData.email;
    document.getElementById('profile-phone').value = userData.phone;
}

// Toggle seller/shopper-specific elements
function toggleSellerElements() {
    const sellerElements = document.querySelectorAll('.seller-only');
    const shopperElements = document.querySelectorAll('.shopper-only');
    const upgradeBtn = document.querySelector('.upgrade-btn');

    if (userData.userType === 'shop-sell') {
        // Show seller elements
        sellerElements.forEach(element => {
            element.style.display = element.tagName === 'LI' ? 'block' :
                                   element.classList.contains('stat-card') ? 'flex' : 'block';
        });
        // Hide shopper-only elements
        shopperElements.forEach(element => {
            element.style.display = 'none';
        });
        if (upgradeBtn) upgradeBtn.style.display = 'none';
    } else {
        // Hide seller elements
        sellerElements.forEach(element => {
            element.style.display = 'none';
        });
        // Show shopper-only elements
        shopperElements.forEach(element => {
            element.style.display = element.tagName === 'LI' ? 'block' :
                                   element.classList.contains('stat-card') ? 'flex' : 'block';
        });
        if (upgradeBtn) upgradeBtn.style.display = 'block';
    }
}

// Setup sidebar navigation
function setupSidebarNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            switchSection(section);
        });
    });
}

// Switch dashboard sections
function switchSection(sectionName) {
    // Update navigation
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    document.querySelector(`[data-section="${sectionName}"]`).parentElement.classList.add('active');
    
    // Update content
    document.querySelectorAll('.dashboard-section').forEach(section => {
        section.classList.remove('active');
    });
    
    document.getElementById(sectionName).classList.add('active');
    
    // Load section-specific data
    loadSectionData(sectionName);
}

// Load section-specific data
function loadSectionData(sectionName) {
    switch(sectionName) {
        case 'orders':
            loadOrders();
            break;
        case 'wishlist':
            loadWishlist();
            break;
        case 'recommendations':
            if (userData.userType === 'shop-only') {
                loadRecommendations();
            }
            break;
        case 'browsing-history':
            if (userData.userType === 'shop-only') {
                loadBrowsingHistory();
            }
            break;
        case 'loyalty':
            if (userData.userType === 'shop-only') {
                loadLoyaltyProgram();
            }
            break;
        case 'products':
            if (userData.userType === 'shop-sell') {
                loadProducts();
            }
            break;
        case 'sales':
            if (userData.userType === 'shop-sell') {
                loadSalesAnalytics();
            }
            break;
    }
}

// Load dashboard overview data
function loadDashboardData() {
    // Update stats
    document.getElementById('total-orders').textContent = ordersData.length;
    document.getElementById('wishlist-count').textContent = wishlistData.length;

    if (userData.userType === 'shop-sell') {
        document.getElementById('products-count').textContent = productsData.length;
        document.getElementById('total-earnings').textContent = '$2,450';
    } else if (userData.userType === 'shop-only') {
        document.getElementById('total-spent').textContent = `$${userData.totalSpent.toFixed(2)}`;
        document.getElementById('loyalty-points').textContent = userData.loyaltyPoints;
    }
}

// Load orders
function loadOrders() {
    const ordersList = document.getElementById('orders-list');
    
    if (ordersData.length === 0) {
        ordersList.innerHTML = '<p>No orders found.</p>';
        return;
    }
    
    const ordersHTML = ordersData.map(order => `
        <div class="order-item">
            <div class="order-header">
                <div class="order-info">
                    <h4>Order #${order.id}</h4>
                    <p>Placed on ${formatDate(order.date)}</p>
                </div>
                <div class="order-status">
                    <span class="status-badge ${order.status}">${capitalizeFirst(order.status)}</span>
                    <span class="order-total">$${order.total.toFixed(2)}</span>
                </div>
            </div>
            <div class="order-items">
                ${order.items.map(item => `
                    <div class="order-item-detail">
                        <img src="${item.image}" alt="${item.name}" class="item-image">
                        <div class="item-info">
                            <h5>${item.name}</h5>
                            <p>Quantity: ${item.quantity} × $${item.price.toFixed(2)}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `).join('');
    
    ordersList.innerHTML = ordersHTML;
    
    // Setup order filters
    setupOrderFilters();
}

// Setup order filters
function setupOrderFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active filter
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter orders
            const filter = this.getAttribute('data-filter');
            filterOrders(filter);
        });
    });
}

// Filter orders
function filterOrders(filter) {
    const filteredOrders = filter === 'all' ? ordersData : 
                          ordersData.filter(order => order.status === filter);
    
    const ordersList = document.getElementById('orders-list');
    
    if (filteredOrders.length === 0) {
        ordersList.innerHTML = `<p>No ${filter} orders found.</p>`;
        return;
    }
    
    // Re-render with filtered data
    // (Implementation similar to loadOrders but with filteredOrders)
}

// Load wishlist
function loadWishlist() {
    const wishlistGrid = document.getElementById('wishlist-grid');
    
    if (wishlistData.length === 0) {
        wishlistGrid.innerHTML = '<p>Your wishlist is empty.</p>';
        return;
    }
    
    const wishlistHTML = wishlistData.map(item => `
        <div class="wishlist-item">
            <div class="item-image-container">
                <img src="${item.image}" alt="${item.name}" class="wishlist-image">
                <button class="remove-wishlist-btn" onclick="removeFromWishlist(${item.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="item-details">
                <h4>${item.name}</h4>
                <p class="item-category">${item.category}</p>
                <div class="item-rating">
                    ${'★'.repeat(item.rating)}${'☆'.repeat(5 - item.rating)}
                </div>
                <div class="item-price">$${item.price.toFixed(2)}</div>
                <button class="btn-primary add-to-cart-btn">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </div>
        </div>
    `).join('');
    
    wishlistGrid.innerHTML = wishlistHTML;
}

// Load recommendations (for shoppers)
function loadRecommendations() {
    const recommendationsGrid = document.getElementById('recommendations-grid');

    if (recommendationsData.length === 0) {
        recommendationsGrid.innerHTML = '<p>No recommendations available at the moment.</p>';
        return;
    }

    const recommendationsHTML = recommendationsData.map(item => `
        <div class="recommendation-item">
            <div class="item-image-container">
                <img src="${item.image}" alt="${item.name}" class="recommendation-image">
                <div class="recommendation-reason">${item.reason}</div>
            </div>
            <div class="item-details">
                <h4>${item.name}</h4>
                <p class="item-category">${item.category}</p>
                <div class="item-rating">
                    ${'★'.repeat(item.rating)}${'☆'.repeat(5 - item.rating)}
                </div>
                <div class="item-price">$${item.price.toFixed(2)}</div>
                <div class="item-actions">
                    <button class="btn-secondary add-to-wishlist-btn" onclick="addToWishlist(${item.id})">
                        <i class="fas fa-heart"></i> Wishlist
                    </button>
                    <button class="btn-primary add-to-cart-btn">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    recommendationsGrid.innerHTML = recommendationsHTML;
}

// Load browsing history (for shoppers)
function loadBrowsingHistory() {
    const browsingHistoryGrid = document.getElementById('browsing-history-grid');

    if (browsingHistoryData.length === 0) {
        browsingHistoryGrid.innerHTML = '<p>No browsing history available.</p>';
        return;
    }

    const browsingHistoryHTML = browsingHistoryData.map(item => `
        <div class="browsing-history-item">
            <div class="item-image-container">
                <img src="${item.image}" alt="${item.name}" class="history-image">
                <div class="viewed-date">Viewed ${formatDate(item.viewedDate)}</div>
            </div>
            <div class="item-details">
                <h4>${item.name}</h4>
                <p class="item-category">${item.category}</p>
                <div class="item-price">$${item.price.toFixed(2)}</div>
                <div class="item-actions">
                    <button class="btn-secondary add-to-wishlist-btn">
                        <i class="fas fa-heart"></i> Wishlist
                    </button>
                    <button class="btn-primary view-product-btn">
                        <i class="fas fa-eye"></i> View Again
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    browsingHistoryGrid.innerHTML = browsingHistoryHTML;
}

// Load loyalty program (for shoppers)
function loadLoyaltyProgram() {
    // Update membership level
    document.getElementById('membership-level').textContent = `${userData.membershipLevel} Member`;
    document.getElementById('current-points').textContent = userData.loyaltyPoints;

    // Calculate progress to next tier
    const tierThresholds = { Bronze: 300, Silver: 600, Gold: 1000 };
    const currentTier = userData.membershipLevel;
    const nextTier = currentTier === 'Bronze' ? 'Silver' : currentTier === 'Silver' ? 'Gold' : 'Platinum';
    const pointsToNext = tierThresholds[nextTier] - userData.loyaltyPoints;
    const progressPercent = (userData.loyaltyPoints / tierThresholds[nextTier]) * 100;

    // Update progress bar
    const progressFill = document.querySelector('.progress-fill');
    if (progressFill) {
        progressFill.style.width = `${progressPercent}%`;
    }

    // Update next tier text
    const nextTierElement = document.querySelector('.next-tier p');
    if (nextTierElement) {
        nextTierElement.textContent = `${pointsToNext} points to ${nextTier} tier`;
    }
}

// Load products (for sellers)
function loadProducts() {
    const productsGrid = document.getElementById('products-grid');

    if (productsData.length === 0) {
        productsGrid.innerHTML = '<p>No products listed yet.</p>';
        return;
    }

    const productsHTML = productsData.map(product => `
        <div class="product-item">
            <img src="${product.image}" alt="${product.name}" class="product-image">
            <div class="product-details">
                <h4>${product.name}</h4>
                <div class="product-stats">
                    <span class="product-price">${product.price.toLocaleString()} ETB</span>
                    <span class="product-stock">Stock: ${product.stock}</span>
                    <span class="product-sales">Sales: ${product.sales}</span>
                </div>
                <div class="product-actions">
                    <button class="btn-secondary">Edit</button>
                    <button class="btn-primary">View</button>
                </div>
            </div>
        </div>
    `).join('');

    productsGrid.innerHTML = productsHTML;
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
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Remove from wishlist
function removeFromWishlist(itemId) {
    const index = wishlistData.findIndex(item => item.id === itemId);
    if (index > -1) {
        wishlistData.splice(index, 1);
        loadWishlist();
        
        // Update wishlist count
        document.getElementById('wishlist-count').textContent = wishlistData.length;
        
        // Show notification
        showNotification('Item removed from wishlist', 'success');
    }
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to sign out?')) {
        // Clear session data
        localStorage.removeItem('lflshop_session');
        
        // Redirect to home page
        window.location.href = 'index.html';
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="notification-icon fas fa-check-circle"></i>
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.style.display = 'block';
    }, 100);
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
    
    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.remove();
    });
}

// Add to wishlist function
function addToWishlist(itemId) {
    // Find item in recommendations
    const item = recommendationsData.find(rec => rec.id === itemId);
    if (item) {
        // Add to wishlist data
        const wishlistItem = {
            id: wishlistData.length + 1,
            name: item.name,
            image: item.image,
            price: item.price,
            category: item.category,
            rating: item.rating,
            dateAdded: new Date().toISOString().split('T')[0]
        };

        wishlistData.push(wishlistItem);

        // Update wishlist count
        document.getElementById('wishlist-count').textContent = wishlistData.length;

        // Show notification
        showNotification('Item added to wishlist!', 'success');
    }
}

// Clear browsing history
function clearBrowsingHistory() {
    if (confirm('Are you sure you want to clear your browsing history?')) {
        browsingHistoryData.length = 0;
        loadBrowsingHistory();
        showNotification('Browsing history cleared', 'success');
    }
}

// Setup clear history button
document.addEventListener('DOMContentLoaded', function() {
    const clearHistoryBtn = document.querySelector('.clear-history-btn');
    if (clearHistoryBtn) {
        clearHistoryBtn.addEventListener('click', clearBrowsingHistory);
    }
});

// Make functions globally available
window.switchSection = switchSection;
window.removeFromWishlist = removeFromWishlist;
window.addToWishlist = addToWishlist;
window.clearBrowsingHistory = clearBrowsingHistory;
window.logout = logout;
