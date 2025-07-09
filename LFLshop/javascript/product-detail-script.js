// Product Detail Page JavaScript

document.addEventListener('DOMContentLoaded', async function() {
    // Check if this is a dynamic product page
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    if (productId) {
        // Load product data dynamically
        await loadProductData(productId);
    }

    // Initialize cart icon
    updateCartIcon();

    // Initialize all functionality
    initImageGallery();
    initProductOptions();
    initTabs();
    initReviewSystem();
    initModals();
    initPhotoUpload();
    await loadSellerInfo();
    await loadProductReviews();
    await loadRelatedProducts();
});

async function loadProductData(productId) {
    try {
        const response = await fetch(`/api/products.php?action=single&id=${productId}`);
        const data = await response.json();

        if (data.success) {
            const product = data.data;
            updateProductDisplay(product);
        } else {
            console.error('Failed to load product:', data.message);
            showNotification('Product not found', 'error');
        }
    } catch (error) {
        console.error('Error loading product:', error);
        showNotification('Failed to load product', 'error');
    }
}

function updateProductDisplay(product) {
    // Update product title
    const titleElement = document.querySelector('.product-title, h1');
    if (titleElement) titleElement.textContent = product.name;

    // Update product description
    const descElement = document.querySelector('.product-description');
    if (descElement) descElement.textContent = product.description;

    // Update price
    const priceElement = document.querySelector('.current-price');
    if (priceElement) {
        priceElement.textContent = `${parseFloat(product.price).toLocaleString()} ETB`;
    }

    // Update sale price if exists
    if (product.sale_price) {
        const salePriceElement = document.querySelector('.sale-price');
        if (salePriceElement) {
            salePriceElement.textContent = `${parseFloat(product.sale_price).toLocaleString()} ETB`;
        }
    }

    // Update product image
    const imageElement = document.querySelector('.product-image img');
    if (imageElement && product.image) {
        imageElement.src = product.image;
        imageElement.alt = product.name;
    }

    // Update seller info
    const sellerElement = document.querySelector('.seller-name');
    if (sellerElement) sellerElement.textContent = product.seller_name;

    // Update location
    const locationElement = document.querySelector('.product-location');
    if (locationElement) locationElement.textContent = product.location;

    // Store product data globally for cart operations
    window.currentProduct = product;
}

// Get current product data for cart
function getCurrentProductData() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    // Default product data (for static page)
    let product = {
        id: 9, // Default to Habesha dress
        title: document.querySelector('.product-title')?.textContent || 'Traditional Habesha Dress',
        price: parseFloat(document.querySelector('.current-price')?.textContent.replace(/[^\d.]/g, '') || '5050'),
        originalPrice: parseFloat(document.querySelector('.original-price')?.textContent.replace(/[^\d.]/g, '') || '6750'),
        image: document.getElementById('main-image')?.src || 'https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80',
        category: 'Traditional Clothing'
    };

    // If we have a product ID, try to get the specific product data
    if (productId) {
        const allProducts = [
            {
                id: 1,
                title: "Handcrafted Leather Sandals",
                category: "Footwear",
                price: 65.00,
                originalPrice: 85.00,
                image: "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1160&q=80"
            },
            {
                id: 2,
                title: "Traditional Incense Burner",
                category: "Home Decor",
                price: 32.50,
                originalPrice: 45.00,
                image: "https://images.unsplash.com/photo-1602166242292-93a00e63e8e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
            },
            {
                id: 9,
                title: "Traditional Habesha Dress",
                category: "Traditional Clothing",
                price: 89.99,
                originalPrice: 119.99,
                image: "https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80"
            }
        ];

        const foundProduct = allProducts.find(p => p.id == productId);
        if (foundProduct) {
            product = foundProduct;
        }
    }

    return product;
}

// Show notification function
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) return;

    const messageElement = notification.querySelector('.notification-message');
    const iconElement = notification.querySelector('.notification-icon');

    messageElement.textContent = message;
    notification.className = `notification ${type}`;

    // Set appropriate icon
    if (type === 'success') {
        iconElement.className = 'notification-icon fas fa-check-circle';
    } else if (type === 'error') {
        iconElement.className = 'notification-icon fas fa-exclamation-circle';
    }

    notification.style.display = 'block';
    setTimeout(() => notification.classList.add('show'), 100);

    // Auto hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.style.display = 'none', 300);
    }, 3000);

    // Close button
    const closeBtn = notification.querySelector('.notification-close');
    if (closeBtn) {
        closeBtn.onclick = function() {
            notification.classList.remove('show');
            setTimeout(() => notification.style.display = 'none', 300);
        };
    }
}

// Load seller information
function loadSellerInfo() {
    const product = getCurrentProductData();
    const seller = getSellerForProduct(product.id);

    if (!seller) return;

    const sellerSection = document.getElementById('seller-section');
    if (!sellerSection) return;

    sellerSection.innerHTML = `
        <div class="seller-header">
            <img src="${seller.avatar}" alt="${seller.name}" class="seller-avatar">
            <div class="seller-info">
                <h3>${seller.name}</h3>
                <div class="seller-rating">
                    <span class="stars">${'★'.repeat(Math.floor(seller.rating))}${'☆'.repeat(5 - Math.floor(seller.rating))}</span>
                    <span>${seller.rating} (${seller.reviewCount} reviews)</span>
                </div>
                <div class="seller-location">
                    <i class="fas fa-map-marker-alt"></i> ${seller.location}
                </div>
                ${seller.verified ? '<div class="verified-seller"><i class="fas fa-check-circle"></i> Verified Seller</div>' : ''}
            </div>
        </div>
        <div class="seller-description">
            <p>${seller.description}</p>
        </div>
        <div class="seller-stats">
            <div class="stat-item">
                <span class="stat-number">${seller.yearsInBusiness}</span>
                <span class="stat-label">Years in Business</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">${seller.totalProducts}</span>
                <span class="stat-label">Products</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">${seller.totalSales.toLocaleString()}</span>
                <span class="stat-label">Total Sales</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">${seller.rating}</span>
                <span class="stat-label">Seller Rating</span>
            </div>
        </div>
    `;
}

// Load product reviews
function loadProductReviews() {
    const product = getCurrentProductData();
    const reviews = getProductReviews(product.id);
    const stats = getReviewStats(product.id);

    // Update review summary
    const reviewSummary = document.getElementById('review-summary');
    if (reviewSummary) {
        reviewSummary.innerHTML = `
            <div class="average-rating">
                <span class="rating-number">${stats.averageRating}</span>
                <div class="rating-stars">${'★'.repeat(Math.floor(stats.averageRating))}${'☆'.repeat(5 - Math.floor(stats.averageRating))}</div>
                <span class="review-count">Based on ${stats.totalReviews} reviews</span>
            </div>
        `;
    }

    // Load reviews list
    displayReviews(reviews);

    // Setup review filters
    setupReviewFilters();
}

// Display reviews
function displayReviews(reviews, sortBy = 'newest') {
    const reviewsList = document.getElementById('reviews-list');
    if (!reviewsList) return;

    const sortedReviews = sortReviews(reviews, sortBy);

    if (sortedReviews.length === 0) {
        reviewsList.innerHTML = `
            <div class="no-reviews">
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        `;
        return;
    }

    reviewsList.innerHTML = sortedReviews.map(review => `
        <div class="review-item">
            <div class="review-header">
                <div class="reviewer-info">
                    <div class="reviewer-avatar">
                        ${review.customerName.charAt(0).toUpperCase()}
                    </div>
                    <div class="reviewer-details">
                        <h4>${review.customerName}</h4>
                        <div class="review-rating">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</div>
                    </div>
                </div>
                <div class="review-date">
                    ${formatReviewDate(review.date)}
                    ${review.verified ? '<span class="verified-purchase">Verified Purchase</span>' : ''}
                </div>
            </div>
            <div class="review-content">
                <h5>${review.title}</h5>
                <p class="review-text">${review.content}</p>
                ${review.photos && review.photos.length > 0 ? `
                    <div class="review-photos">
                        ${review.photos.map(photo => `
                            <img src="${photo}" alt="Review photo" class="review-photo" onclick="openImageModal('${photo}')">
                        `).join('')}
                    </div>
                ` : ''}
            </div>
            <div class="review-actions">
                <button class="helpful-btn" onclick="markHelpful(${review.id})">
                    <i class="fas fa-thumbs-up"></i> Helpful (${review.helpful})
                </button>
            </div>
        </div>
    `).join('');
}

// Setup review filters
function setupReviewFilters() {
    const sortSelect = document.getElementById('review-sort');
    const ratingFilter = document.getElementById('rating-filter');

    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const product = getCurrentProductData();
            let reviews = getProductReviews(product.id);

            // Apply rating filter if set
            if (ratingFilter && ratingFilter.value !== 'all') {
                const targetRating = parseInt(ratingFilter.value);
                reviews = reviews.filter(review => review.rating === targetRating);
            }

            displayReviews(reviews, this.value);
        });
    }

    if (ratingFilter) {
        ratingFilter.addEventListener('change', function() {
            const product = getCurrentProductData();
            let reviews = getProductReviews(product.id);

            // Apply rating filter
            if (this.value !== 'all') {
                const targetRating = parseInt(this.value);
                reviews = reviews.filter(review => review.rating === targetRating);
            }

            // Apply current sort
            const sortBy = sortSelect ? sortSelect.value : 'newest';
            displayReviews(reviews, sortBy);
        });
    }
}

// Format review date
function formatReviewDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else if (diffDays < 30) {
        const weeks = Math.floor(diffDays / 7);
        return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
    } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30);
        return `${months} month${months > 1 ? 's' : ''} ago`;
    } else {
        return date.toLocaleDateString();
    }
}

// Mark review as helpful
function markHelpful(reviewId) {
    // In a real application, this would make an API call
    console.log('Marked review as helpful:', reviewId);
    showNotification('Thank you for your feedback!', 'success');
}

// Open image modal for review photos
function openImageModal(imageSrc) {
    // Reuse existing image modal functionality
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');

    if (modal && modalImage) {
        modalImage.src = imageSrc;
        modal.style.display = 'flex';
    }
});

// Function to load product data dynamically
function loadProductData(productId) {
    // This would typically fetch from an API, but for now we'll use static data
    const allProducts = [
        {
            id: 1,
            title: "Handcrafted Leather Sandals",
            category: "Footwear",
            rating: 4,
            ratingCount: 12,
            price: 65.00,
            originalPrice: 85.00,
            images: [
                "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1160&q=80",
                "https://images.unsplash.com/photo-1590047698081-c5f87b5d28e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                "https://images.unsplash.com/photo-1583391733956-6c78276477e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
            ],
            description: "These handcrafted leather sandals represent the finest Ethiopian leatherwork tradition. Made from premium leather and featuring traditional designs, they offer both comfort and cultural authenticity.",
            specifications: {
                "Material": "Premium Ethiopian Leather",
                "Origin": "Handmade in Ethiopia",
                "Style": "Traditional Ethiopian Sandals",
                "Sole Type": "Leather Sole",
                "Closure": "Adjustable Straps",
                "Weight": "Approximately 400g"
            }
        },
        {
            id: 2,
            title: "Traditional Incense Burner",
            category: "Home Decor",
            rating: 5,
            ratingCount: 8,
            price: 32.50,
            originalPrice: 45.00,
            images: [
                "https://images.unsplash.com/photo-1602166242292-93a00e63e8e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                "https://images.unsplash.com/photo-1590047698081-c5f87b5d28e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                "https://images.unsplash.com/photo-1583391733956-6c78276477e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
            ],
            description: "This traditional Ethiopian incense burner is crafted from clay and designed for burning frankincense and other aromatic resins. Perfect for creating a peaceful atmosphere in your home.",
            specifications: {
                "Material": "Traditional Clay",
                "Origin": "Handmade in Ethiopia",
                "Style": "Traditional Incense Burner",
                "Dimensions": "15cm x 10cm",
                "Weight": "Approximately 300g",
                "Use": "Frankincense and Resin Burning"
            }
        },
        {
            id: 9,
            title: "Traditional Habesha Dress",
            category: "Textiles",
            rating: 5,
            ratingCount: 24,
            price: 89.99,
            originalPrice: 120.00,
            images: [
                "https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80",
                "https://images.unsplash.com/photo-1590047698081-c5f87b5d28e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80",
                "https://images.unsplash.com/photo-1583391733956-6c78276477e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80"
            ],
            description: "This exquisite Habesha Kemis is a magnificent embodiment of Ethiopian cultural heritage and craftsmanship. Each dress is meticulously handwoven by skilled artisans in Ethiopia, showcasing the rich textile traditions that have been passed down through generations.",
            specifications: {
                "Material": "100% Premium Cotton",
                "Origin": "Handmade in Ethiopia",
                "Style": "Traditional Habesha Kemis",
                "Sleeve Type": "Long Sleeve",
                "Neckline": "Traditional Round Neck",
                "Length": "Floor Length (Ankle)",
                "Closure": "Traditional Tie Closure",
                "Weight": "Approximately 800g"
            }
        }
    ];

    const product = allProducts.find(p => p.id == productId);

    if (product) {
        updateProductDisplay(product);
    }
}

// Function to update product display with dynamic data
function updateProductDisplay(product) {
    // Update title
    const titleElement = document.querySelector('.product-title');
    if (titleElement) {
        titleElement.textContent = product.title;
        document.title = `${product.title} - LFLshop`;
    }

    // Update breadcrumb
    const currentPageElement = document.querySelector('.current-page');
    if (currentPageElement) {
        currentPageElement.textContent = product.title;
    }

    // Update rating
    const ratingTextElement = document.querySelector('.rating-text');
    if (ratingTextElement) {
        ratingTextElement.textContent = `${product.rating}.0 (${product.ratingCount} reviews)`;
    }

    // Update price
    const currentPriceElement = document.querySelector('.current-price');
    const originalPriceElement = document.querySelector('.original-price');
    const discountBadgeElement = document.querySelector('.discount-badge');

    if (currentPriceElement) {
        currentPriceElement.textContent = `${product.price.toLocaleString()} ETB`;
    }

    if (originalPriceElement && product.originalPrice) {
        originalPriceElement.textContent = `${product.originalPrice.toLocaleString()} ETB`;
        const discount = Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100);
        if (discountBadgeElement) {
            discountBadgeElement.textContent = `${discount}% OFF`;
        }
    } else if (originalPriceElement) {
        originalPriceElement.style.display = 'none';
        if (discountBadgeElement) {
            discountBadgeElement.style.display = 'none';
        }
    }

    // Update images
    if (product.images && product.images.length > 0) {
        const mainImage = document.getElementById('main-image');
        const zoomedImage = document.getElementById('zoomed-image');
        const thumbnails = document.querySelectorAll('.thumbnail');

        if (mainImage) {
            mainImage.src = product.images[0];
            mainImage.alt = product.title;
        }

        if (zoomedImage) {
            zoomedImage.src = product.images[0];
        }

        // Update thumbnails
        thumbnails.forEach((thumbnail, index) => {
            if (product.images[index]) {
                thumbnail.dataset.image = product.images[index];
                const img = thumbnail.querySelector('img');
                if (img) {
                    img.src = product.images[index];
                    img.alt = `${product.title} - View ${index + 1}`;
                }
            }
        });
    }

    // Update description
    const descriptionContent = document.querySelector('.description-content');
    if (descriptionContent && product.description) {
        const descriptionParagraph = descriptionContent.querySelector('p');
        if (descriptionParagraph) {
            descriptionParagraph.textContent = product.description;
        }
    }

    // Update specifications
    if (product.specifications) {
        const specGrid = document.querySelector('.spec-grid');
        if (specGrid) {
            specGrid.innerHTML = Object.entries(product.specifications).map(([label, value]) => `
                <div class="spec-item">
                    <span class="spec-label">${label}:</span>
                    <span class="spec-value">${value}</span>
                </div>
            `).join('');
        }
    }

    // Update reviews tab count
    const reviewsTabBtn = document.querySelector('[data-tab="reviews"]');
    if (reviewsTabBtn) {
        reviewsTabBtn.textContent = `Reviews (${product.ratingCount})`;
    }
}

// Image Gallery Functionality
function initImageGallery() {
    const mainImage = document.getElementById('main-image');
    const thumbnails = document.querySelectorAll('.thumbnail');
    const zoomBtn = document.getElementById('zoom-btn');
    const zoomModal = document.getElementById('zoom-modal');
    const zoomedImage = document.getElementById('zoomed-image');
    const zoomClose = document.getElementById('zoom-close');

    // Thumbnail click handlers
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const newImageSrc = this.dataset.image;
            mainImage.src = newImageSrc;
            zoomedImage.src = newImageSrc;
            
            // Update active thumbnail
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Zoom functionality
    zoomBtn.addEventListener('click', function() {
        zoomedImage.src = mainImage.src;
        zoomModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    zoomClose.addEventListener('click', function() {
        zoomModal.classList.remove('active');
        document.body.style.overflow = '';
    });

    zoomModal.addEventListener('click', function(e) {
        if (e.target === zoomModal) {
            zoomModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

// Product Options Functionality
function initProductOptions() {
    // Size selection
    const sizeButtons = document.querySelectorAll('.size-btn');
    sizeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            sizeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Quantity controls
    const qtyMinus = document.getElementById('qty-minus');
    const qtyPlus = document.getElementById('qty-plus');
    const qtyInput = document.getElementById('qty-input');

    qtyMinus.addEventListener('click', function() {
        const currentValue = parseInt(qtyInput.value);
        if (currentValue > 1) {
            qtyInput.value = currentValue - 1;
        }
    });

    qtyPlus.addEventListener('click', function() {
        const currentValue = parseInt(qtyInput.value);
        const maxValue = parseInt(qtyInput.max);
        if (currentValue < maxValue) {
            qtyInput.value = currentValue + 1;
        }
    });

    // Add to cart functionality
    const addToCartBtn = document.getElementById('add-to-cart');
    addToCartBtn.addEventListener('click', function() {
        const selectedSize = document.querySelector('.size-btn.active')?.dataset.size;
        const quantity = parseInt(qtyInput.value);

        if (!selectedSize) {
            showNotification('Please select a size', 'error');
            return;
        }

        // Get current product data
        const product = getCurrentProductData();

        // Add to cart
        addToCart(product, quantity, selectedSize);

        // Show success animation
        this.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
        this.style.background = '#28a745';

        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
            this.style.background = '';
        }, 2000);
    });

    // Add to wishlist functionality
    const addToWishlistBtn = document.getElementById('add-to-wishlist');
    addToWishlistBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        
        if (icon.classList.contains('far')) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            this.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
            this.style.background = '#C53A2B';
            this.style.color = 'white';
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            this.innerHTML = '<i class="far fa-heart"></i> Add to Wishlist';
            this.style.background = '';
            this.style.color = '';
        }
    });
}

// Tabs Functionality
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all buttons and panels
            tabButtons.forEach(b => b.classList.remove('active'));
            tabPanels.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked button and corresponding panel
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // Size chart modal
    const sizeChartLink = document.querySelector('.size-chart-link');
    const sizeChartModal = document.getElementById('size-chart-modal');
    const sizeChartClose = document.getElementById('size-chart-close');

    if (sizeChartLink) {
        sizeChartLink.addEventListener('click', function(e) {
            e.preventDefault();
            sizeChartModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    sizeChartClose.addEventListener('click', function() {
        sizeChartModal.classList.remove('active');
        document.body.style.overflow = '';
    });

    sizeChartModal.addEventListener('click', function(e) {
        if (e.target === sizeChartModal) {
            sizeChartModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

// Review System Functionality
function initReviewSystem() {
    // Star rating input
    const starInputs = document.querySelectorAll('.star-rating-input i');
    let selectedRating = 0;

    starInputs.forEach((star, index) => {
        star.addEventListener('click', function() {
            selectedRating = index + 1;
            updateStarDisplay(selectedRating);
        });

        star.addEventListener('mouseover', function() {
            updateStarDisplay(index + 1);
        });
    });

    document.querySelector('.star-rating-input').addEventListener('mouseleave', function() {
        updateStarDisplay(selectedRating);
    });

    function updateStarDisplay(rating) {
        starInputs.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('far');
                star.classList.add('fas', 'active');
            } else {
                star.classList.remove('fas', 'active');
                star.classList.add('far');
            }
        });
    }

    // Review form submission
    const reviewForm = document.getElementById('review-form');
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            rating: selectedRating,
            title: document.getElementById('review-title').value,
            text: document.getElementById('review-text').value,
            photos: Array.from(document.querySelectorAll('.uploaded-photo img')).map(img => img.src)
        };

        if (selectedRating === 0) {
            alert('Please select a rating');
            return;
        }

        if (!formData.title.trim() || !formData.text.trim()) {
            alert('Please fill in all required fields');
            return;
        }

        // Submit review logic here
        console.log('Submitting review:', formData);
        
        // Show success message
        alert('Thank you for your review! It will be published after moderation.');
        
        // Reset form
        reviewForm.reset();
        selectedRating = 0;
        updateStarDisplay(0);
        document.getElementById('uploaded-photos').innerHTML = '';
    });

    // Review filter
    const filterSelect = document.querySelector('.filter-select');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const filterValue = this.value;
            console.log('Filtering reviews by:', filterValue);
            // Implement filtering logic here
            loadReviews(filterValue);
        });
    }
}

// Modal Functionality
function initModals() {
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.zoom-modal.active, .size-chart-modal.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });
}

// Photo Upload Functionality
function initPhotoUpload() {
    const photoUploadArea = document.getElementById('photo-upload');
    const photoInput = document.getElementById('photo-input');
    const uploadedPhotos = document.getElementById('uploaded-photos');

    // Click to upload
    photoUploadArea.addEventListener('click', function() {
        photoInput.click();
    });

    // File input change
    photoInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    // Drag and drop
    photoUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    photoUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    photoUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(files) {
        const maxFiles = 5;
        const maxSize = 5 * 1024 * 1024; // 5MB
        const currentPhotos = uploadedPhotos.children.length;

        if (currentPhotos + files.length > maxFiles) {
            alert(`You can only upload up to ${maxFiles} photos`);
            return;
        }

        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) {
                alert('Please upload only image files');
                return;
            }

            if (file.size > maxSize) {
                alert('Each photo must be less than 5MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const photoDiv = document.createElement('div');
                photoDiv.className = 'uploaded-photo';
                photoDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Uploaded photo">
                    <button type="button" class="remove-photo" onclick="removePhoto(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                uploadedPhotos.appendChild(photoDiv);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Remove uploaded photo
function removePhoto(button) {
    button.parentElement.remove();
}

// Load Reviews
function loadReviews(filter = 'newest') {
    const reviewItems = document.getElementById('review-items');
    
    // Sample review data
    const sampleReviews = [
        {
            id: 1,
            name: 'Almaz Tadesse',
            verified: true,
            rating: 5,
            title: 'Absolutely stunning Habesha Kemis!',
            text: 'The craftsmanship is incredible, and the fabric feels luxurious. I received so many compliments at my event. Highly recommend!',
            date: '2 weeks ago',
            photos: ['https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'],
            helpful: 15,
            unhelpful: 1
        },
        {
            id: 2,
            name: 'Fatima Ahmed',
            verified: true,
            rating: 4,
            title: 'Beautiful dress, but sizing could be better',
            text: 'The dress is gorgeous and the embroidery is exquisite. However, I wish there was a more detailed size guide. Overall very happy with the purchase.',
            date: '1 month ago',
            photos: [],
            helpful: 8,
            unhelpful: 2
        },
        {
            id: 3,
            name: 'Zeinab Mohammed',
            verified: false,
            rating: 5,
            title: 'Perfect for cultural events',
            text: 'Wore this to a traditional ceremony and felt absolutely beautiful. The quality is outstanding and delivery was fast.',
            date: '3 weeks ago',
            photos: ['https://images.unsplash.com/photo-1590047698081-c5f87b5d28e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'],
            helpful: 12,
            unhelpful: 0
        }
    ];

    // Clear existing reviews
    reviewItems.innerHTML = '';

    // Render reviews
    sampleReviews.forEach(review => {
        const reviewElement = createReviewElement(review);
        reviewItems.appendChild(reviewElement);
    });
}

// Create Review Element
function createReviewElement(review) {
    const reviewDiv = document.createElement('div');
    reviewDiv.className = 'review-item';
    
    const photosHtml = review.photos.map(photo => 
        `<div class="review-photo" onclick="openImageZoom('${photo}')">
            <img src="${photo}" alt="Review photo">
        </div>`
    ).join('');

    reviewDiv.innerHTML = `
        <div class="review-header">
            <div class="reviewer-info">
                <div class="reviewer-avatar">${review.name.charAt(0)}</div>
                <div class="reviewer-details">
                    <h4>${review.name}</h4>
                    ${review.verified ? '<span class="verified">✓ Verified Purchase</span>' : ''}
                </div>
            </div>
            <span class="review-date">${review.date}</span>
        </div>
        <div class="review-rating">
            ${'<i class="fas fa-star"></i>'.repeat(review.rating)}
            ${'<i class="far fa-star"></i>'.repeat(5 - review.rating)}
        </div>
        <h5 class="review-title">${review.title}</h5>
        <p class="review-text">${review.text}</p>
        ${review.photos.length > 0 ? `<div class="review-photos">${photosHtml}</div>` : ''}
        <div class="review-actions">
            <button class="helpful-btn" onclick="voteHelpful(${review.id}, true)">
                <i class="fas fa-thumbs-up"></i> Helpful (${review.helpful})
            </button>
            <button class="helpful-btn" onclick="voteHelpful(${review.id}, false)">
                <i class="fas fa-thumbs-down"></i> Not Helpful (${review.unhelpful})
            </button>
        </div>
    `;

    return reviewDiv;
}

// Vote Helpful
function voteHelpful(reviewId, isHelpful) {
    console.log(`Voting ${isHelpful ? 'helpful' : 'not helpful'} for review ${reviewId}`);
    // Implement voting logic here
}

// Open Image Zoom
function openImageZoom(imageSrc) {
    const zoomModal = document.getElementById('zoom-modal');
    const zoomedImage = document.getElementById('zoomed-image');
    
    zoomedImage.src = imageSrc;
    zoomModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Load Related Products
function loadRelatedProducts() {
    const relatedGrid = document.getElementById('related-products-grid');
    
    // Sample related products
    const relatedProducts = [
        {
            id: 1,
            name: 'Traditional Shawl',
            price: '2580 ETB',
            originalPrice: '3370 ETB',
            image: 'https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            rating: 4.3
        },
        {
            id: 2,
            name: 'Embroidered Scarf',
            price: '1685 ETB',
            originalPrice: null,
            image: 'https://images.unsplash.com/photo-1583391733956-6c78276477e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            rating: 4.7
        },
        {
            id: 3,
            name: 'Cultural Jewelry Set',
            price: '4490 ETB',
            originalPrice: '5615 ETB',
            image: 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            rating: 4.5
        },
        {
            id: 4,
            name: 'Traditional Headwrap',
            price: '1405 ETB',
            originalPrice: null,
            image: 'https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
            rating: 4.2
        }
    ];

    relatedGrid.innerHTML = relatedProducts.map(product => `
        <div class="product-card" onclick="window.location.href='product-detail.html?id=${product.id}'">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
                ${product.originalPrice ? '<span class="sale-badge">SALE</span>' : ''}
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <div class="product-rating">
                    <div class="stars">
                        ${'<i class="fas fa-star"></i>'.repeat(Math.floor(product.rating))}
                        ${product.rating % 1 !== 0 ? '<i class="fas fa-star-half-alt"></i>' : ''}
                        ${'<i class="far fa-star"></i>'.repeat(5 - Math.ceil(product.rating))}
                    </div>
                    <span>(${Math.floor(Math.random() * 50) + 10})</span>
                </div>
                <div class="product-price">
                    <span class="current-price">${product.price}</span>
                    ${product.originalPrice ? `<span class="original-price">${product.originalPrice}</span>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}
