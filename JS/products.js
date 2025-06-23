
const ethiopianCategories = [
    {
        id: 1,
        name: 'Traditional Clothing',
        slug: 'traditional-clothing',
        description: 'Authentic Ethiopian traditional garments',
        image: 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 45
    },
    {
        id: 2,
        name: 'Coffee & Spices',
        slug: 'coffee-spices',
        description: 'Premium Ethiopian coffee and traditional spices',
        image: 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 32
    },
    {
        id: 3,
        name: 'Handicrafts',
        slug: 'handicrafts',
        description: 'Handmade crafts and artisanal products',
        image: 'https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 28
    },
    {
        id: 4,
        name: 'Jewelry',
        slug: 'jewelry',
        description: 'Traditional and modern Ethiopian jewelry',
        image: 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 19
    },
    {
        id: 5,
        name: 'Home Decor',
        slug: 'home-decor',
        description: 'Decorative items and household goods',
        image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 24
    },
    {
        id: 6,
        name: 'Art & Paintings',
        slug: 'art-paintings',
        description: 'Traditional and contemporary Ethiopian art',
        image: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 15
    },
    {
        id: 7,
        name: 'Religious Items',
        slug: 'religious-items',
        description: 'Religious artifacts and ceremonial items',
        image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 12
    },
    {
        id: 8,
        name: 'Textiles',
        slug: 'textiles',
        description: 'Traditional fabrics and textile products',
        image: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80',
        productCount: 21
    }
];

const sampleProducts = [
    {
        id: 1,
        name: 'Traditional Habesha Kemis - White',
        slug: 'traditional-habesha-kemis-white',
        description: 'Beautiful handwoven traditional Ethiopian dress with intricate border patterns. Made from high-quality cotton and featuring traditional Ethiopian embroidery.',
        shortDescription: 'Elegant traditional Ethiopian dress with handwoven details',
        price: 2500,
        currency: 'ETB',
        categoryId: 1,
        images: ['https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Piazza',
        artisan: 'Almaz Textile Cooperative',
        rating: 4.8,
        reviews: 24,
        inStock: true,
        featured: true
    },
    {
        id: 2,
        name: 'Ethiopian Coffee Beans - Yirgacheffe',
        slug: 'ethiopian-coffee-yirgacheffe',
        description: 'Premium single-origin coffee beans from the Yirgacheffe region. Known for their bright acidity and floral notes. Freshly roasted to order.',
        shortDescription: 'Premium Yirgacheffe coffee beans with floral notes',
        price: 450,
        currency: 'ETB',
        categoryId: 2,
        images: ['https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Bole Road',
        artisan: 'Yirgacheffe Coffee Farmers Union',
        rating: 4.9,
        reviews: 156,
        inStock: true,
        featured: true
    },
    {
        id: 3,
        name: 'Handwoven Ethiopian Basket',
        slug: 'handwoven-ethiopian-basket',
        description: 'Traditional Ethiopian basket handwoven from natural grass and palm leaves. Perfect for storage or as decorative piece.',
        shortDescription: 'Beautiful handwoven basket from natural materials',
        price: 350,
        currency: 'ETB',
        categoryId: 3,
        images: ['https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Merkato',
        artisan: 'Dorze Women Weavers',
        rating: 4.7,
        reviews: 43,
        inStock: true,
        featured: false
    },
    {
        id: 4,
        name: 'Berbere Spice Blend - Authentic',
        slug: 'berbere-spice-authentic',
        description: 'Authentic Ethiopian berbere spice blend made from carefully selected spices. Essential for traditional Ethiopian cooking.',
        shortDescription: 'Traditional Ethiopian spice blend for authentic flavors',
        price: 180,
        currency: 'ETB',
        categoryId: 2,
        images: ['https://images.unsplash.com/photo-1596040033229-a9821ebd058d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Churchill Avenue',
        artisan: 'Merkato Spice Merchants',
        rating: 4.6,
        reviews: 89,
        inStock: true,
        featured: true
    },
    {
        id: 5,
        name: 'Ethiopian Cross Pendant',
        slug: 'ethiopian-cross-pendant',
        description: 'Traditional Ethiopian Orthodox cross pendant made from sterling silver. Features intricate traditional patterns.',
        shortDescription: 'Sterling silver Ethiopian Orthodox cross pendant',
        price: 850,
        currency: 'ETB',
        categoryId: 4,
        images: ['https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, 4 Kilo',
        artisan: 'Lalibela Silver Crafters',
        rating: 4.9,
        reviews: 67,
        inStock: true,
        featured: false
    },
    {
        id: 6,
        name: 'Traditional Coffee Ceremony Set',
        slug: 'traditional-coffee-ceremony-set',
        description: 'Complete traditional Ethiopian coffee ceremony set including jebena, cups, and incense burner.',
        shortDescription: 'Complete set for traditional Ethiopian coffee ceremony',
        price: 1200,
        currency: 'ETB',
        categoryId: 2,
        images: ['https://images.unsplash.com/photo-1559056199-641a0ac8b55e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Africa Avenue',
        artisan: 'Traditional Pottery Collective',
        rating: 4.8,
        reviews: 32,
        inStock: true,
        featured: true
    },
    {
        id: 7,
        name: 'Ethiopian Honey - Pure Wildflower',
        slug: 'ethiopian-honey-wildflower',
        description: 'Pure wildflower honey harvested from the highlands of Ethiopia. Rich in flavor and natural nutrients.',
        shortDescription: 'Pure wildflower honey from Ethiopian highlands',
        price: 320,
        currency: 'ETB',
        categoryId: 2,
        images: ['https://images.unsplash.com/photo-1587049352846-4a222e784d38?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Bole District',
        artisan: 'Highland Honey Collective',
        rating: 4.7,
        reviews: 78,
        inStock: true,
        featured: false
    },
    {
        id: 8,
        name: 'Traditional Shamma Cloth',
        slug: 'traditional-shamma-cloth',
        description: 'Handwoven traditional Ethiopian shamma cloth made from pure cotton. Perfect for traditional ceremonies.',
        shortDescription: 'Handwoven traditional Ethiopian shamma cloth',
        price: 890,
        currency: 'ETB',
        categoryId: 1,
        images: ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Gulele',
        artisan: 'Traditional Weavers Guild',
        rating: 4.6,
        reviews: 45,
        inStock: true,
        featured: false
    },
    {
        id: 9,
        name: 'Ethiopian Incense Set',
        slug: 'ethiopian-incense-set',
        description: 'Traditional Ethiopian incense collection including frankincense and myrrh. Perfect for ceremonies and meditation.',
        shortDescription: 'Traditional incense collection with frankincense',
        price: 275,
        currency: 'ETB',
        categoryId: 7,
        images: ['https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, Ras Mekonnen Avenue',
        artisan: 'Sacred Scents Collective',
        rating: 4.8,
        reviews: 92,
        inStock: true,
        featured: false
    },
    {
        id: 10,
        name: 'Handcrafted Leather Bag',
        slug: 'handcrafted-leather-bag',
        description: 'Beautiful handcrafted leather bag made from premium Ethiopian leather. Features traditional patterns and modern design.',
        shortDescription: 'Premium handcrafted Ethiopian leather bag',
        price: 1450,
        currency: 'ETB',
        categoryId: 3,
        images: ['https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'],
        location: 'Addis Ababa, CMC',
        artisan: 'Addis Leather Works',
        rating: 4.5,
        reviews: 63,
        inStock: true,
        featured: false
    }
];

function displayFeaturedCategories() {
    const categoriesGrid = document.getElementById('categories-grid');
    if (!categoriesGrid) return;

    const featuredCategories = ethiopianCategories.slice(0, 4);

    categoriesGrid.innerHTML = featuredCategories.map(category => `
        <div class="category-card" onclick="viewCategory('${category.slug}')">
            <div class="category-info">
                <h3 class="category-name">${category.name}</h3>
                <p class="category-description">${category.description}</p>
                <span class="category-count">${category.productCount} Products</span>
            </div>
        </div>
    `).join('');
}

function displayFeaturedProducts() {
    const productsGrid = document.getElementById('featured-products-grid');
    if (!productsGrid) return;
    
    const featuredProducts = sampleProducts.filter(product => product.featured);
    
    productsGrid.innerHTML = featuredProducts.map(product => createProductCard(product)).join('');
}

function createProductCard(product) {
    return `
        <div class="product-card" onclick="viewProduct(${product.id})">
            <div class="product-image">
                <img src="${product.images[0]}" alt="${product.name}" loading="lazy">
                <div class="region-badge">
                    <i class="fas fa-map-marker-alt"></i>
                    ${product.location}
                </div>
                ${product.featured ? '<div class="featured-badge">Featured</div>' : ''}
            </div>
            <div class="product-info">
                <h3 class="product-title">${product.name}</h3>
                <p class="product-description">${product.shortDescription}</p>
                <div class="product-rating">
                    <div class="stars">
                        ${generateStars(product.rating)}
                    </div>
                    <span class="rating-text">${product.rating} (${product.reviews} reviews)</span>
                </div>
                <div class="product-price">
                    <span class="current-price">${product.price.toLocaleString()}</span>
                    <span class="price-currency">${product.currency}</span>
                </div>
                <div class="product-artisan">
                    <i class="fas fa-user"></i>
                    <span>by ${product.artisan}</span>
                </div>
            </div>
        </div>
    `;
}

function generateStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    
    let starsHtml = '';

    for (let i = 0; i < fullStars; i++) {
        starsHtml += '<i class="fas fa-star"></i>';
    }

    if (hasHalfStar) {
        starsHtml += '<i class="fas fa-star-half-alt"></i>';
    }

    for (let i = 0; i < emptyStars; i++) {
        starsHtml += '<i class="far fa-star"></i>';
    }

    return starsHtml;
}

function viewProduct(productId) {
    const product = sampleProducts.find(p => p.id === productId);
    if (product) {
        showNotification(`Viewing ${product.name} - Full product page will be implemented`, 'info');
    }
}

function viewCategory(categorySlug) {
    const category = ethiopianCategories.find(c => c.slug === categorySlug);
    if (category) {
        showNotification(`Browsing ${category.name} category - Collections page will be implemented`, 'info');
    }
}

function searchProducts(query) {
    const filteredProducts = sampleProducts.filter(product =>
        product.name.toLowerCase().includes(query.toLowerCase()) ||
        product.description.toLowerCase().includes(query.toLowerCase()) ||
        product.location.toLowerCase().includes(query.toLowerCase())
    );

    return filteredProducts;
}

function filterProductsByCategory(categoryId) {
    return sampleProducts.filter(product => product.categoryId === categoryId);
}

function filterProductsByLocation(location) {
    return sampleProducts.filter(product =>
        product.location.toLowerCase().includes(location.toLowerCase())
    );
}

function filterProductsByPriceRange(minPrice, maxPrice) {
    return sampleProducts.filter(product => 
        product.price >= minPrice && product.price <= maxPrice
    );
}

function initializeProducts() {
    displayFeaturedCategories();
    displayFeaturedProducts();
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('index') || window.location.pathname === '/') {
        initializeProducts();
    }
});
window.viewProduct = viewProduct;
window.viewCategory = viewCategory;
window.searchProducts = searchProducts;
