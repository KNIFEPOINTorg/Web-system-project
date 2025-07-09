// Dashboard JavaScript for Seller Account

// Global variables for category system
let productCategorySelector = null;
let customCategoryForm = null;

// Check authentication and redirect if not authorized
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in and authorized
    if (!isLoggedIn()) {
        showNotification('Please log in to access the dashboard.', 'error');
        window.location.href = 'signin.html';
        return;
    }

    const currentUser = getCurrentUser();
    if (!canSell()) {
        showNotification('Access denied. This dashboard is only available for Shop & Sell accounts.', 'error');
        window.location.href = 'index.html';
        return;
    }

    // Initialize dashboard
    initializeDashboard();
    loadSellerData();
    setupEventListeners();
    updateCartIcon();
    initializeCategorySystem();
});

// Initialize dashboard with user data
function initializeDashboard() {
    const currentUser = getCurrentUser();
    
    // Update welcome message
    const welcomeMessage = document.getElementById('welcome-message');
    if (welcomeMessage) {
        welcomeMessage.textContent = `Welcome back, ${currentUser.fullName}!`;
    }
    
    // Show seller-specific sections
    const sellerSections = document.querySelectorAll('.seller-only');
    sellerSections.forEach(section => {
        section.style.display = 'block';
    });
    
    // Hide shopper-only sections
    const shopperSections = document.querySelectorAll('.shopper-only');
    shopperSections.forEach(section => {
        section.style.display = 'none';
    });
}

// Load seller-specific data
function loadSellerData() {
    const currentUser = getCurrentUser();
    
    // Load seller stats
    loadSellerStats();
    
    // Load seller products
    loadSellerProducts();
    
    // Load recent orders
    loadRecentOrders();
    
    // Load account information
    loadAccountInfo();
}

// Load seller statistics
function loadSellerStats() {
    const currentUser = getCurrentUser();
    
    // Demo data for seller stats
    const stats = {
        totalProducts: 6,
        totalOrders: 42,
        totalRevenue: 137500,
        sellerRating: currentUser.sellerRating || 4.8
    };

    // Update stat cards
    updateStatCard('total-products', stats.totalProducts);
    updateStatCard('total-orders', stats.totalOrders);
    updateStatCard('total-revenue', `${stats.totalRevenue.toLocaleString()} ETB`);
    updateStatCard('seller-rating', stats.sellerRating);
}

// Update individual stat card
function updateStatCard(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

// Load seller products
function loadSellerProducts() {
    const currentUser = getCurrentUser();
    const sellerProducts = getProductsForSeller(currentUser.username);
    
    const productsGrid = document.getElementById('seller-products');
    if (productsGrid && sellerProducts.length > 0) {
        productsGrid.innerHTML = sellerProducts.slice(0, 6).map(product => `
            <div class="product-card">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.title}">
                </div>
                <div class="product-info">
                    <h3>${product.title}</h3>
                    <div class="product-price">${product.price.toLocaleString()} ETB</div>
                    <div class="product-status status-active">Active</div>
                    <div class="product-actions">
                        <button class="btn-outline" onclick="editProduct(${product.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    } else if (productsGrid) {
        productsGrid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-box" style="font-size: 3rem; color: #ccc; margin-bottom: 16px;"></i>
                <h3>No products yet</h3>
                <p>Start by adding your first product to the marketplace.</p>
                <button class="btn-primary" onclick="openAddProductModal()">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
        `;
    }
}

// Get products for specific seller
function getProductsForSeller(username) {
    // This would normally come from a database
    // For demo, we'll use the products from sellers.js
    if (typeof getSellerProducts === 'function') {
        return getSellerProducts(username);
    }
    
    // Fallback demo products
    return [
        {
            id: 1,
            title: 'Traditional Habesha Dress',
            price: 5050,
            image: 'https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80',
            category: 'Traditional Clothing'
        },
        {
            id: 2,
            title: 'Silver Filigree Necklace',
            price: 8750,
            image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=688&q=80',
            category: 'Jewelry'
        }
    ];
}

// Load recent orders
function loadRecentOrders() {
    const recentOrders = [
        {
            id: 'LFL-2024-001',
            customer: 'Hanan Tadesse',
            product: 'Traditional Habesha Dress',
            amount: 5050,
            status: 'delivered',
            date: '2024-01-20'
        },
        {
            id: 'LFL-2024-002',
            customer: 'Meron Bekele',
            product: 'Silver Filigree Necklace',
            amount: 8750,
            status: 'shipped',
            date: '2024-01-19'
        },
        {
            id: 'LFL-2024-003',
            customer: 'Dawit Alemayehu',
            product: 'Ethiopian Coffee Beans',
            amount: 1600,
            status: 'processing',
            date: '2024-01-18'
        }
    ];
    
    const ordersTable = document.getElementById('recent-orders');
    if (ordersTable) {
        ordersTable.innerHTML = recentOrders.map(order => `
            <tr>
                <td>${order.id}</td>
                <td>${order.customer}</td>
                <td>${order.product}</td>
                <td>$${order.amount.toLocaleString()} ETB</td>
                <td><span class="order-status status-${order.status}">${order.status}</span></td>
                <td>${formatDate(order.date)}</td>
                <td>
                    <button class="btn-outline" onclick="viewOrder('${order.id}')">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
}

// Load account information
function loadAccountInfo() {
    const currentUser = getCurrentUser();
    const accountInfo = document.getElementById('account-info');
    
    if (accountInfo) {
        accountInfo.innerHTML = `
            <div class="info-section">
                <h3>Business Information</h3>
                <div class="info-item">
                    <span class="info-label">Business Name:</span>
                    <span class="info-value">${currentUser.businessName || 'Not set'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Location:</span>
                    <span class="info-value">${currentUser.businessLocation || 'Not set'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Seller Rating:</span>
                    <span class="info-value">${currentUser.sellerRating || 'N/A'} ★</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Sales:</span>
                    <span class="info-value">${currentUser.totalSales || 0}</span>
                </div>
            </div>
            <div class="info-section">
                <h3>Account Details</h3>
                <div class="info-item">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">${currentUser.fullName}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">${currentUser.email}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Account Type:</span>
                    <span class="info-value">${currentUser.userType}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member Since:</span>
                    <span class="info-value">${formatDate(currentUser.joinDate)}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Verified:</span>
                    <span class="info-value">${currentUser.isVerified ? 'Yes' : 'No'}</span>
                </div>
            </div>
        `;
    }
}

// Setup event listeners
function setupEventListeners() {
    // Product form submission
    const addProductForm = document.getElementById('add-product-form');
    if (addProductForm) {
        addProductForm.addEventListener('submit', saveNewProduct);
    }

    // Close modal when clicking outside
    const addProductModal = document.getElementById('add-product-modal');
    if (addProductModal) {
        addProductModal.addEventListener('click', function(e) {
            if (e.target === addProductModal) {
                closeAddProductModal();
            }
        });
    }

    const customCategoryModal = document.getElementById('custom-category-modal');
    if (customCategoryModal) {
        customCategoryModal.addEventListener('click', function(e) {
            if (e.target === customCategoryModal) {
                closeCustomCategoryModal();
            }
        });
    }

    // Category filter change
    const categoryFilter = document.getElementById('category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            filterProductsByCategory(this.value);
        });
    }

    // Status filter change
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterProductsByStatus(this.value);
        });
    }

    // Product search
    const productSearch = document.getElementById('product-search');
    if (productSearch) {
        productSearch.addEventListener('input', function() {
            searchProducts(this.value);
        });
    }

    // Image upload handling
    const productImages = document.getElementById('product-images');
    if (productImages) {
        productImages.addEventListener('change', handleImageUpload);
    }
}

// Initialize category system
function initializeCategorySystem() {
    // Initialize category selector for product form
    if (typeof initializeCategorySelector === 'function') {
        productCategorySelector = initializeCategorySelector('product-category-selector', {
            showAmharic: false,
            allowCustom: true,
            required: true,
            onSelectionChange: function(selection) {
                console.log('Category selected:', selection);
            }
        });
    }

    // Populate category filter dropdown
    populateCategoryFilter();
}

// Populate category filter dropdown
function populateCategoryFilter() {
    const categoryFilter = document.getElementById('category-filter');
    if (!categoryFilter || typeof CategoryManager === 'undefined') return;

    const categories = CategoryManager.getAllCategories();

    // Clear existing options (except "All Categories")
    categoryFilter.innerHTML = '<option value="">All Categories</option>';

    // Add categories
    Object.values(categories).forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        categoryFilter.appendChild(option);
    });
}

// Open add product modal
function openAddProductModal() {
    const modal = document.getElementById('add-product-modal');
    if (modal) {
        modal.style.display = 'flex';

        // Initialize category selector if not already done
        if (!productCategorySelector && typeof initializeCategorySelector === 'function') {
            productCategorySelector = initializeCategorySelector('product-category-selector', {
                showAmharic: false,
                allowCustom: true,
                required: true,
                onSelectionChange: function(selection) {
                    // Category selection changed
                }
            });
        }
    }
}

// Open custom category modal
function openCustomCategoryModal() {
    const modal = document.getElementById('custom-category-modal');
    if (modal) {
        modal.style.display = 'flex';

        // Initialize custom category form if not already done
        if (!customCategoryForm && typeof CustomCategoryForm === 'function') {
            customCategoryForm = new CustomCategoryForm('custom-category-form-container');
        }
    }
}

// Close add product modal
function closeAddProductModal() {
    const modal = document.getElementById('add-product-modal');
    if (modal) {
        modal.style.display = 'none';
        // Reset form
        const form = document.getElementById('add-product-form');
        if (form) form.reset();

        // Reset category selector
        if (productCategorySelector) {
            productCategorySelector.clearSelection();
        }
    }
}

// Close custom category modal
function closeCustomCategoryModal() {
    const modal = document.getElementById('custom-category-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Save new product
function saveNewProduct(event) {
    event.preventDefault();

    const form = document.getElementById('add-product-form');
    if (!form) return;

    const formData = new FormData(form);

    // Get category selection
    const categorySelection = productCategorySelector ? productCategorySelector.getSelection() : null;

    const productData = {
        name: formData.get('productName'),
        description: formData.get('productDescription'),
        price: parseFloat(formData.get('productPrice')),
        quantity: parseInt(formData.get('productQuantity')),
        sku: formData.get('productSku'),
        weight: parseFloat(formData.get('productWeight')),
        tags: formData.get('productTags'),
        featured: formData.get('productFeatured') === 'on',
        category: categorySelection,
        images: formData.getAll('productImages')
    };

    // Validate required fields
    if (!productData.name || !productData.description || !productData.price || !productData.quantity) {
        showErrorToast('Please fill in all required fields');
        return;
    }

    // Validate category selection
    if (!categorySelection || !categorySelection.isValid) {
        showErrorToast('Please select a valid product category');
        return;
    }

    // Validate category selection using CategoryManager
    if (typeof CategoryManager !== 'undefined') {
        const validation = CategoryManager.validateCategorySelection(
            categorySelection.category,
            categorySelection.subcategory,
            categorySelection.type
        );

        if (!validation.valid) {
            showErrorToast(validation.error);
            return;
        }
    }

    // Save product data (would normally be sent to server)

    // Show success message
    showSuccessToast('Product added successfully!');

    // Close modal and refresh products
    closeAddProductModal();
    loadSellerProducts();
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

function editProduct(productId) {
    showNotification('Edit product functionality coming soon!', 'info');
}

function viewOrder(orderId) {
    showNotification('Order details functionality coming soon!', 'info');
}

// Filter products by category
function filterProductsByCategory(categoryId) {
    // Filter products display by category
    loadSellerProducts(); // Reload with filter
}

// Filter products by status
function filterProductsByStatus(status) {
    // Filter products display by status
    loadSellerProducts(); // Reload with filter
}

// Search products
function searchProducts(query) {
    // Search through products
    loadSellerProducts(); // Reload with search
}

// Handle image upload
function handleImageUpload(event) {
    const files = event.target.files;
    const previewContainer = document.getElementById('image-preview');

    if (!previewContainer) return;

    // Clear existing previews
    previewContainer.innerHTML = '';

    // Show preview for each selected image
    Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <button type="button" class="remove-image" onclick="removeImagePreview(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        }
    });
}

// Remove image preview
function removeImagePreview(index) {
    const previewContainer = document.getElementById('image-preview');
    const previewItems = previewContainer.querySelectorAll('.image-preview-item');

    if (previewItems[index]) {
        previewItems[index].remove();
    }
}

// Show notification (fallback if toast notifications not available)
function showNotification(message, type = 'success') {
    // Try to use toast notifications first
    if (typeof showSuccessToast === 'function' && type === 'success') {
        showSuccessToast(message);
        return;
    }
    if (typeof showErrorToast === 'function' && type === 'error') {
        showErrorToast(message);
        return;
    }
    if (typeof showInfoToast === 'function' && type === 'info') {
        showInfoToast(message);
        return;
    }

    // Fallback notification display
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 10px 20px;
        background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#007bff'};
        color: white; border-radius: 4px; z-index: 1000;
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Export functions for global use
window.openAddProductModal = openAddProductModal;
window.closeAddProductModal = closeAddProductModal;
window.openCustomCategoryModal = openCustomCategoryModal;
window.closeCustomCategoryModal = closeCustomCategoryModal;
window.removeImagePreview = removeImagePreview;
