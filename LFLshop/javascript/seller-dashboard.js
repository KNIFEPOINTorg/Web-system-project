// Seller Dashboard JavaScript
// Handles product management, orders, and analytics

// Global variables
let sellerProducts = [];
let sellerOrders = [];
let currentTab = 'products';

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupEventListeners();
    loadSellerData();
    updateDashboardStats();
});

// Initialize dashboard functionality
async function initializeDashboard() {
    await loadSellerProducts();
    await loadSellerOrders();
    console.log(`Loaded ${sellerProducts.length} products and ${sellerOrders.length} orders`);
}

async function loadSellerProducts() {
    try {
        const response = await fetch('/api/products.php?action=seller');
        const data = await response.json();

        if (data.success) {
            sellerProducts = data.data || [];
        } else {
            sellerProducts = [];
            console.error('Failed to load products:', data.message);
        }
    } catch (error) {
        console.error('Error loading products:', error);
        sellerProducts = [];
    }
}

async function loadSellerOrders() {
    try {
        const response = await fetch('/api/orders.php?action=seller');
        const data = await response.json();

        if (data.success) {
            sellerOrders = data.data || [];
        } else {
            sellerOrders = [];
            console.error('Failed to load orders:', data.message);
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        sellerOrders = [];
    }
}

// Setup event listeners
function setupEventListeners() {
    // Tab navigation
    const navTabs = document.querySelectorAll('.nav-tab');
    navTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchTab(tabName);
        });
    });

    // Product form submission
    const productForm = document.getElementById('product-form');
    if (productForm) {
        productForm.addEventListener('submit', handleProductSubmission);
    }

    // Image upload
    const imageUploadArea = document.getElementById('image-upload-area');
    const imageInput = document.getElementById('product-images');

    if (imageUploadArea && imageInput) {
        imageUploadArea.addEventListener('click', () => imageInput.click());
        imageUploadArea.addEventListener('dragover', handleDragOver);
        imageUploadArea.addEventListener('drop', handleImageDrop);
        imageInput.addEventListener('change', handleImageSelect);
    }
}

// Switch between dashboard tabs
function switchTab(tabName) {
    // Update navigation
    const navTabs = document.querySelectorAll('.nav-tab');
    navTabs.forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.tab === tabName) {
            tab.classList.add('active');
        }
    });

    // Update content
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });

    const activeTab = document.getElementById(`${tabName}-tab`);
    if (activeTab) {
        activeTab.classList.add('active');
    }

    currentTab = tabName;

    // Load tab-specific content
    switch(tabName) {
        case 'products':
            displayProducts();
            break;
        case 'orders':
            displayOrders();
            break;
        case 'analytics':
            displayAnalytics();
            break;
    }
}

// Handle product form submission
async function handleProductSubmission(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const productData = {
        name: formData.get('title'),
        description: formData.get('description'),
        category_id: parseInt(formData.get('category')),
        price: parseFloat(formData.get('price')),
        stock_quantity: parseInt(formData.get('quantity')),
        location: formData.get('region'),
        status: 'active'
    };

    if (!productData.name || !productData.description || !productData.price || !productData.stock_quantity) {
        showToast('Please fill in all required fields', 'error');
        return;
    }

    const imageFiles = document.getElementById('product-images').files;
    if (imageFiles.length > 0) {
        productData.image = getPlaceholderImage(productData.category_id);
    }

    try {
        const response = await fetch('/api/products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        });

        const data = await response.json();

        if (data.success) {
            showToast('Product added successfully!', 'success');
            await loadSellerProducts();
            resetForm();
            switchTab('products');
            updateDashboardStats();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error adding product:', error);
        showToast('Failed to add product', 'error');
    }
}

// Get placeholder image based on category
function getPlaceholderImage(category) {
    const imageMap = {
        'Textiles': 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Clothing': 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Jewelry': 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Coffee': 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Pottery': 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Spices': 'https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Home Decor': 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Art': 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Footwear': 'https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Food': 'https://images.unsplash.com/photo-1596359900106-7aa61d0a8309?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        'Merchandise': 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'
    };

    return imageMap[category] || 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
}

// Update collections page data
function updateCollectionsData() {
    // Trigger collections page to reload data
    if (typeof loadProductsFromStorage === 'function') {
        loadProductsFromStorage();
    }
}

// Display products in the products tab
function displayProducts() {
    const productsGrid = document.getElementById('seller-products-grid');
    if (!productsGrid) return;

    if (sellerProducts.length === 0) {
        productsGrid.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3>No Products Yet</h3>
                <p>Start by adding your first Ethiopian product to the marketplace</p>
                <button class="btn-primary" onclick="switchTab('add-product')">Add Your First Product</button>
            </div>
        `;
        return;
    }

    const productsHTML = sellerProducts.map(product => `
        <div class="product-card-dashboard">
            <div class="product-image-dashboard">
                <img src="${product.image}" alt="${product.name}">
                <div class="product-status-badge ${product.status || 'active'}">${(product.status || 'active').toUpperCase()}</div>
            </div>
            <div class="product-info-dashboard">
                <div class="product-header-dashboard">
                    <h3 class="product-title-dashboard">${product.name}</h3>
                    <div class="product-category-badge">${product.category}</div>
                </div>
                <div class="product-meta-dashboard">
                    <div class="product-price-dashboard">
                        <span class="price-amount">${product.price.toLocaleString()}</span>
                        <span class="price-currency">ETB</span>
                    </div>
                    <div class="product-stats">
                        <span class="stat-item">
                            <i class="fas fa-eye"></i>
                            ${product.views || 0} views
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-shopping-cart"></i>
                            ${product.orders || 0} orders
                        </span>
                    </div>
                </div>
                <div class="product-actions-dashboard">
                    <button class="action-btn primary" onclick="editProduct(${product.id})" title="Edit Product">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="action-btn secondary" onclick="viewProduct(${product.id})" title="View Product">
                        <i class="fas fa-eye"></i>
                        View
                    </button>
                    <button class="action-btn danger" onclick="deleteProduct(${product.id})" title="Delete Product">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    productsGrid.innerHTML = productsHTML;
}

// Display orders in the orders tab
function displayOrders() {
    const ordersList = document.getElementById('orders-list');
    if (!ordersList) return;

    if (sellerOrders.length === 0) {
        ordersList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>No Orders Yet</h3>
                <p>Orders will appear here when customers purchase your products</p>
            </div>
        `;
        return;
    }

    const ordersHTML = sellerOrders.map(order => `
        <div class="order-card">
            <div class="order-header">
                <span class="order-id">Order #${order.id}</span>
                <span class="order-status ${order.status}">${order.status}</span>
            </div>
            <div class="order-details">
                <p><strong>Customer:</strong> ${order.customerName}</p>
                <p><strong>Total:</strong> ${order.total.toLocaleString()} ETB</p>
                <p><strong>Date:</strong> ${new Date(order.date).toLocaleDateString()}</p>
            </div>
        </div>
    `).join('');

    ordersList.innerHTML = ordersHTML;
}

// Display analytics
function displayAnalytics() {
    const totalSales = sellerOrders.reduce((sum, order) => sum + order.total, 0);
    document.getElementById('total-sales').textContent = totalSales.toLocaleString();
}

// Update dashboard statistics
function updateDashboardStats() {
    document.getElementById('total-products').textContent = sellerProducts.length;
    document.getElementById('total-orders').textContent = sellerOrders.length;

    const totalRevenue = sellerOrders.reduce((sum, order) => sum + order.total, 0);
    document.getElementById('total-revenue').textContent = totalRevenue.toLocaleString();
}

// Reset product form
function resetForm() {
    const form = document.getElementById('product-form');
    if (form) {
        form.reset();
        clearImagePreview();
    }
}

// Image handling functions
function handleDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}

function handleImageDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');

    const files = e.dataTransfer.files;
    handleImageFiles(files);
}

function handleImageSelect(e) {
    const files = e.target.files;
    handleImageFiles(files);
}

function handleImageFiles(files) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';

    Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <button class="preview-remove" onclick="removePreviewImage(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                preview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        }
    });
}

function removePreviewImage(button) {
    button.parentElement.remove();
}

function clearImagePreview() {
    const preview = document.getElementById('image-preview');
    if (preview) {
        preview.innerHTML = '';
    }
}

// Product management functions
function editProduct(productId) {
    const product = sellerProducts.find(p => p.id === productId);
    if (product) {
        // Populate form with product data
        document.getElementById('product-title').value = product.name;
        document.getElementById('product-description').value = product.description;
        document.getElementById('product-category').value = product.category;
        document.getElementById('product-region').value = product.region;
        document.getElementById('product-price').value = product.price;
        document.getElementById('product-quantity').value = product.quantity;

        // Switch to add product tab for editing
        switchTab('add-product');
        showToast('Product loaded for editing', 'info');
    }
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        sellerProducts = sellerProducts.filter(p => p.id !== productId);
        localStorage.setItem('lflshop_products', JSON.stringify(sellerProducts));
        updateCollectionsData();
        displayProducts();
        updateDashboardStats();
        showToast('Product deleted successfully', 'success');
    }
}

// Load seller data (placeholder for real implementation)
function loadSellerData() {
    // In a real app, this would load seller-specific data from the server
    console.log('Loading seller data...');
}

// Show toast notification
function showToast(message, type = 'info') {
    if (typeof window.showSuccessToast === 'function') {
        window.showSuccessToast(message);
    } else {
        // Fallback toast implementation
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas fa-check-circle toast-icon"></i>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        container.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }
}

// Make functions globally available
window.switchTab = switchTab;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;
window.removePreviewImage = removePreviewImage;
window.resetForm = resetForm;


