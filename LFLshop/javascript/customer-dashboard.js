// Customer Dashboard JavaScript - Ethiopian Marketplace
// Comprehensive dashboard functionality for customers

// Current customer data
let currentCustomer = null;
let customerOrders = [];
let customerWishlist = [];

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', async function() {
    if (!await checkAuthentication()) {
        window.location.href = 'signin.html';
        return;
    }

    initializeDashboard();
    setupEventListeners();
    await loadCustomerData();
    updateCartIcon();
});

async function checkAuthentication() {
    try {
        const response = await fetch('/api/auth.php?action=check');
        const data = await response.json();
        return data.success;
    } catch (error) {
        return false;
    }
}

// Initialize dashboard functionality
function initializeDashboard() {
    const currentUser = getCurrentUser();
    if (!currentUser) {
        showErrorToast('Please log in to access your dashboard.');
        setTimeout(() => {
            window.location.href = 'signin.html';
        }, 2000);
        return;
    }

    // Load customer information
    loadCustomerInfo(currentUser);

    // Setup navigation
    setupSidebarNavigation();

    // Load initial section
    showSection('overview');
}

// Load customer information
function loadCustomerInfo(user) {
    currentCustomer = user;

    // Update UI with customer info
    updateCustomerInfo();
}

// Update customer information in UI
function updateCustomerInfo() {
    const customerNameElement = document.getElementById('customer-name');
    if (customerNameElement && currentCustomer) {
        customerNameElement.textContent = currentCustomer.name || currentCustomer.fullName;
    }

    // Update stats
    updateDashboardStats();
}

// Update dashboard statistics
function updateDashboardStats() {
    // Calculate stats from customer data
    const totalOrders = customerOrders.length;
    const wishlistItems = customerWishlist.length;
    const totalSpent = calculateTotalSpent();
    const reviewsGiven = 0; // Placeholder

    // Update stat cards
    updateStatCard('total-orders', totalOrders);
    updateStatCard('wishlist-items', wishlistItems);
    updateStatCard('total-spent', `${totalSpent.toLocaleString()} ETB`);
    updateStatCard('reviews-given', reviewsGiven);

    // Update navigation badges
    updateNavBadge('orders-count', totalOrders);
    updateNavBadge('wishlist-count', wishlistItems);
}

// Helper function to update navigation badges
function updateNavBadge(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
        element.style.display = value > 0 ? 'block' : 'none';
    }
}

// Calculate total spent
function calculateTotalSpent() {
    return customerOrders.reduce((total, order) => {
        return total + (order.total || 0);
    }, 0);
}

// Setup sidebar navigation
function setupSidebarNavigation() {
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            const section = this.getAttribute('data-section');
            if (section) {
                showSection(section);

                // Update active state
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
}

// Show specific dashboard section
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.dashboard-section');
    sections.forEach(section => section.classList.remove('active'));

    // Show target section
    const targetSection = document.getElementById(`${sectionName}-section`);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    // Load section-specific data
    loadSectionData(sectionName);
}

// Load data for specific section
function loadSectionData(sectionName) {
    switch (sectionName) {
        case 'overview':
            loadOverviewData();
            break;
        case 'orders':
            loadOrdersData();
            break;
        case 'wishlist':
            loadWishlistData();
            break;
        case 'browsing-history':
            loadBrowsingHistoryData();
            break;
        default:
            console.log(`Loading ${sectionName} section...`);
    }
}

// Load overview section data
function loadOverviewData() {
    loadRecentOrders();
    loadRecommendations();
}

// Load customer-specific data
function loadCustomerData() {
    // Load customer orders
    loadCustomerOrders();

    // Load customer wishlist
    loadCustomerWishlist();

    // Update dashboard after loading data
    updateDashboardStats();
}

// Load orders for current customer
function loadCustomerOrders() {
    // Sample orders data - in real app this would come from backend
    customerOrders = [
        {
            id: 'ORD-001',
            products: [{ name: 'Traditional Habesha Dress', quantity: 1, price: 8500 }],
            total: 8500,
            status: 'delivered',
            date: '2024-01-15'
        },
        {
            id: 'ORD-002',
            products: [{ name: 'Ethiopian Coffee - Yirgacheffe', quantity: 1, price: 1250 }],
            total: 1250,
            status: 'shipped',
            date: '2024-01-14'
        }
    ];
}

// Load wishlist for current customer
function loadCustomerWishlist() {
    // Sample wishlist data
    customerWishlist = [
        {
            id: 2,
            name: 'Silver Ethiopian Cross',
            price: 4200,
            seller: 'Alemayehu Silver Works'
        },
        {
            id: 4,
            name: 'Traditional Clay Jebena',
            price: 2800,
            seller: 'Pottery Masters Dire Dawa'
        }
    ];
}

// Load orders section data
function loadOrdersData() {
    // This will be expanded to show full order management
    console.log('Loading orders data...');
}

// Load wishlist section data
function loadWishlistData() {
    // This will be expanded to show wishlist management
    console.log('Loading wishlist data...');
}

// Load browsing history data
function loadBrowsingHistoryData() {
    // This will be expanded to show browsing history
    console.log('Loading browsing history data...');
}

// Update individual stat card
function updateStatCard(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

// Load recent orders for overview
function loadRecentOrders() {
    const container = document.getElementById('recent-orders');
    if (!container) return;

    // Sample recent orders
    const recentOrders = [
        {
            id: 'ORD-001',
            product: 'Traditional Habesha Dress',
            seller: 'Meron Traditional Crafts',
            amount: 8500,
            status: 'Delivered',
            date: '2024-01-15'
        },
        {
            id: 'ORD-002',
            product: 'Ethiopian Coffee - Yirgacheffe',
            seller: 'Highland Coffee Cooperative',
            amount: 1250,
            status: 'Shipped',
            date: '2024-01-14'
        }
    ];

    if (recentOrders.length === 0) {
        container.innerHTML = '<p class="empty-state">No orders yet. <a href="collections.html">Start shopping!</a></p>';
        return;
    }

    container.innerHTML = recentOrders.map(order => `
        <div class="order-item">
            <div class="order-info">
                <h4>${order.id}</h4>
                <p>${order.product}</p>
                <span class="order-date">${order.date}</span>
            </div>
            <div class="order-amount">
                <span class="amount">${order.amount.toLocaleString()} ETB</span>
                <span class="status status-${order.status.toLowerCase()}">${order.status}</span>
            </div>
        </div>
    `).join('');
}

// Load wishlist items
function loadWishlist() {
    const wishlistItems = [
        {
            id: 15,
            name: 'Handmade Silver Bracelet',
            price: 4380,
            image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'
        },
        {
            id: 8,
            name: 'Mitmita Spice Blend',
            price: 940,
            image: 'https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'
        },
        {
            id: 20,
            name: 'Wooden Figurine Collection',
            price: 8125,
            image: 'https://images.unsplash.com/photo-1629196914168-3a2652305f9f?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'
        }
    ];
    
    const wishlistContainer = document.getElementById('wishlist-items');
    if (wishlistContainer) {
        if (wishlistItems.length > 0) {
            wishlistContainer.innerHTML = wishlistItems.map(item => `
                <div class="wishlist-item">
                    <div class="item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="item-info">
                        <h4>${item.name}</h4>
                        <div class="item-price">${item.price.toLocaleString()} ETB</div>
                        <button class="btn-primary" onclick="addToCartFromWishlist(${item.id})">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            wishlistContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Save items you love for later.</p>
                </div>
            `;
        }
    }
}

// Load recommendations for overview
function loadRecommendations() {
    const container = document.getElementById('recommendations');
    if (!container) return;

    // Sample recommendations based on browsing history
    const recommendations = [
        {
            id: 6,
            name: 'Handwoven Cotton Shawl',
            price: 3200,
            image: 'https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80',
            seller: 'Meron Traditional Crafts'
        },
        {
            id: 5,
            name: 'Berbere Spice Blend',
            price: 850,
            image: 'https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80',
            seller: 'Spice Route Ethiopia'
        }
    ];

    container.innerHTML = recommendations.map(product => `
        <div class="product-item">
            <div class="product-info">
                <h4>${product.name}</h4>
                <p>By ${product.seller}</p>
                <span class="price">${product.price.toLocaleString()} ETB</span>
            </div>
            <div class="product-actions">
                <button class="btn btn-sm btn-primary" onclick="viewProduct(${product.id})">View</button>
            </div>
        </div>
    `).join('');
}

// Load account information
function loadAccountInfo() {
    const currentUser = getCurrentUser();
    const accountInfo = document.getElementById('account-info');
    
    if (accountInfo) {
        accountInfo.innerHTML = `
            <div class="info-section">
                <h3>Personal Information</h3>
                <div class="info-item">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">${currentUser.fullName}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">${currentUser.email || 'Not provided'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">${currentUser.phone || 'Not provided'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Type:</span>
                    <span class="info-value">${currentUser.userType}</span>
                </div>
            </div>
            <div class="info-section">
                <h3>Account Status</h3>
                <div class="info-item">
                    <span class="info-label">Member Since:</span>
                    <span class="info-value">${formatDate(currentUser.joinDate)}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Verified:</span>
                    <span class="info-value">${currentUser.isVerified ? 'Yes' : 'No'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Reward Points:</span>
                    <span class="info-value">450 points</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Preferred Language:</span>
                    <span class="info-value">English</span>
                </div>
            </div>
        `;
    }
}

// Setup event listeners
function setupEventListeners() {
    // View all orders button
    const viewAllOrdersBtn = document.getElementById('view-all-orders-btn');
    if (viewAllOrdersBtn) {
        viewAllOrdersBtn.addEventListener('click', () => {
            showNotification('Order history page coming soon!', 'info');
        });
    }
    
    // Manage wishlist button
    const manageWishlistBtn = document.getElementById('manage-wishlist-btn');
    if (manageWishlistBtn) {
        manageWishlistBtn.addEventListener('click', () => {
            showNotification('Wishlist management page coming soon!', 'info');
        });
    }
    
    // Edit profile button
    const editProfileBtn = document.getElementById('edit-profile-btn');
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', () => {
            showNotification('Profile editing page coming soon!', 'info');
        });
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function viewOrder(orderId) {
    console.log('View order:', orderId);
    showNotification('Order details page coming soon!', 'info');
}

function addToCartFromWishlist(itemId) {
    console.log('Add to cart from wishlist:', itemId);
    showNotification('Item added to cart!', 'success');
}

function addToCartFromRecommendations(itemId) {
    console.log('Add to cart from recommendations:', itemId);
    showNotification('Item added to cart!', 'success');
}

// Product interaction functions
function viewProduct(productId) {
    window.location.href = `product-detail.html?id=${productId}`;
}

function addToCart(productId) {
    // Add to cart logic here
    showSuccessToast('Product added to cart!');
}

// Export functions for global use
window.showSection = showSection;
window.viewProduct = viewProduct;
window.addToCart = addToCart;
