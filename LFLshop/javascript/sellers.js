// LFLshop Seller Account System

// Demo Seller Accounts
const sellers = [
    {
        id: 1,
        name: "Addis Crafts",
        description: "Traditional Ethiopian handicrafts and artisanal goods from the heart of Addis Ababa",
        location: "Merkato, Addis Ababa",
        rating: 4.8,
        reviewCount: 156,
        yearsInBusiness: 8,
        establishedDate: "2016",
        specialties: ["Traditional Crafts", "Pottery", "Home Decor"],
        avatar: "https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80",
        verified: true,
        totalProducts: 6,
        totalSales: 1247
    },
    {
        id: 2,
        name: "Heritage Textiles",
        description: "Authentic Ethiopian textiles and traditional clothing crafted by skilled artisans",
        location: "Piassa, Addis Ababa",
        rating: 4.9,
        reviewCount: 203,
        yearsInBusiness: 12,
        establishedDate: "2012",
        specialties: ["Traditional Clothing", "Textiles", "Habesha Kemis"],
        avatar: "https://images.unsplash.com/photo-1544829885-9a56ca0fe9e7?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80",
        verified: true,
        totalProducts: 6,
        totalSales: 2156
    },
    {
        id: 3,
        name: "Bole Artisans",
        description: "Fine jewelry and precious accessories showcasing Ethiopian craftsmanship",
        location: "Bole, Addis Ababa",
        rating: 4.7,
        reviewCount: 89,
        yearsInBusiness: 5,
        establishedDate: "2019",
        specialties: ["Jewelry", "Silver Work", "Traditional Accessories"],
        avatar: "https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80",
        verified: true,
        totalProducts: 6,
        totalSales: 678
    },
    {
        id: 4,
        name: "Merkato Traders",
        description: "Premium Ethiopian coffee, spices, and gourmet food products",
        location: "Merkato, Addis Ababa",
        rating: 4.6,
        reviewCount: 134,
        yearsInBusiness: 15,
        establishedDate: "2009",
        specialties: ["Coffee", "Spices", "Food Products"],
        avatar: "https://images.unsplash.com/photo-1559525839-d9d1e38b0a35?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80",
        verified: true,
        totalProducts: 6,
        totalSales: 1834
    }
];

// Product-to-Seller Distribution
const productSellerMapping = {
    // Addis Crafts (Traditional Crafts, Pottery, Home Decor)
    1: 1,  // Handcrafted Leather Sandals
    2: 1,  // Traditional Incense Burner
    11: 1, // Handwoven Basket
    12: 1, // Clay Coffee Pot
    16: 1, // Wooden Sculpture
    24: 1, // Clay Water Pitcher
    
    // Heritage Textiles (Traditional Clothing, Textiles)
    9: 2,  // Traditional Habesha Dress
    10: 2, // Embroidered Shawl
    13: 2, // Cotton Scarf
    14: 2, // Traditional Blanket
    17: 2, // Silk Headwrap
    23: 2, // Handwoven Table Runner
    
    // Bole Artisans (Jewelry, Silver Work)
    3: 3,  // Silver Filigree Necklace
    4: 3,  // Traditional Earrings
    5: 3,  // Handcrafted Bracelet
    6: 3,  // Ethiopian Cross Pendant
    21: 3, // Ethiopian Cross Pendant (duplicate)
    22: 3, // Korarima Spice Pods (moved to jewelry category as decorative)
    
    // Merkato Traders (Coffee, Spices, Food)
    7: 4,  // Ethiopian Coffee Beans
    8: 4,  // Berbere Spice Mix
    15: 4, // Honey Collection
    18: 4, // Organic Wild Honey
    19: 4, // Traditional Leather Shoes (moved to food/general category)
    20: 4  // Wooden Figurine Collection (moved to general category)
};

// Customer Reviews Data
const customerReviews = {
    // Reviews for Traditional Habesha Dress (Product ID: 9)
    9: [
        {
            id: 1,
            customerName: "Hanan Tadesse",
            rating: 5,
            date: "2024-01-15",
            title: "Absolutely Beautiful!",
            content: "This dress exceeded my expectations! The quality of the fabric is exceptional and the embroidery work is stunning. I wore it to my cousin's wedding and received so many compliments. The fit is perfect and it's very comfortable to wear all day.",
            helpful: 23,
            verified: true,
            photos: ["https://images.unsplash.com/photo-1594549181132-9045fed330ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80"]
        },
        {
            id: 2,
            customerName: "Meron Bekele",
            rating: 5,
            date: "2024-01-08",
            title: "Authentic and High Quality",
            content: "As someone who appreciates traditional Ethiopian clothing, I can say this is the real deal. The craftsmanship is excellent and you can tell it's made by skilled artisans. The colors are vibrant and the fabric feels luxurious.",
            helpful: 18,
            verified: true,
            photos: []
        },
        {
            id: 3,
            customerName: "Sara Alemayehu",
            rating: 4,
            date: "2023-12-22",
            title: "Great for Special Occasions",
            content: "Perfect for cultural events and celebrations. The dress is well-made and the design is traditional yet elegant. Only minor issue was the delivery took a bit longer than expected, but the quality makes up for it.",
            helpful: 12,
            verified: true,
            photos: []
        },
        {
            id: 4,
            customerName: "Bethlehem Girma",
            rating: 5,
            date: "2023-12-10",
            title: "Worth Every Birr!",
            content: "I've been looking for an authentic Habesha kemis for months and this one is perfect. The attention to detail is remarkable and it fits beautifully. Heritage Textiles really knows their craft!",
            helpful: 15,
            verified: true,
            photos: []
        },
        {
            id: 5,
            customerName: "Rahel Tesfaye",
            rating: 5,
            date: "2023-11-28",
            title: "Excellent Customer Service",
            content: "Not only is the dress gorgeous, but the seller was very helpful with sizing questions. They responded quickly and provided detailed measurements. The dress arrived exactly as described.",
            helpful: 9,
            verified: true,
            photos: []
        }
    ],
    
    // Reviews for Silver Filigree Necklace (Product ID: 3)
    3: [
        {
            id: 6,
            customerName: "Tigist Haile",
            rating: 5,
            date: "2024-01-20",
            title: "Exquisite Craftsmanship",
            content: "This necklace is a work of art! The silver filigree work is incredibly detailed and delicate. It's become my favorite piece of jewelry. Bole Artisans creates truly beautiful pieces.",
            helpful: 14,
            verified: true,
            photos: ["https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80"]
        },
        {
            id: 7,
            customerName: "Almaz Worku",
            rating: 4,
            date: "2024-01-12",
            title: "Beautiful but Delicate",
            content: "Stunning necklace with intricate details. The silver work is authentic and beautiful. Just be careful when wearing it as the filigree is quite delicate. Perfect for special occasions.",
            helpful: 8,
            verified: true,
            photos: []
        },
        {
            id: 8,
            customerName: "Selamawit Desta",
            rating: 5,
            date: "2023-12-30",
            title: "Perfect Gift",
            content: "Bought this as a gift for my sister and she absolutely loves it! The packaging was beautiful and the necklace is even more stunning in person. Highly recommend!",
            helpful: 11,
            verified: true,
            photos: []
        }
    ],
    
    // Reviews for Ethiopian Coffee Beans (Product ID: 7)
    7: [
        {
            id: 9,
            customerName: "Dawit Mulugeta",
            rating: 5,
            date: "2024-01-18",
            title: "Best Coffee I've Ever Had",
            content: "As a coffee enthusiast, I can confidently say this is exceptional coffee. The aroma is incredible and the taste is rich and complex. Merkato Traders sources the finest beans!",
            helpful: 19,
            verified: true,
            photos: []
        },
        {
            id: 10,
            customerName: "Mahlet Assefa",
            rating: 5,
            date: "2024-01-05",
            title: "Authentic Ethiopian Flavor",
            content: "This coffee brings back memories of traditional coffee ceremonies. The beans are fresh and the flavor profile is exactly what you'd expect from premium Ethiopian coffee.",
            helpful: 16,
            verified: true,
            photos: []
        },
        {
            id: 11,
            customerName: "Yonas Kebede",
            rating: 4,
            date: "2023-12-15",
            title: "Great Quality",
            content: "Excellent coffee beans with a wonderful aroma. The roast is perfect and the packaging keeps them fresh. Will definitely order again!",
            helpful: 7,
            verified: true,
            photos: []
        }
    ]
};

// Function to get seller by ID
function getSellerById(sellerId) {
    return sellers.find(seller => seller.id === sellerId);
}

// Function to get seller for a product
function getSellerForProduct(productId) {
    const sellerId = productSellerMapping[productId];
    return getSellerById(sellerId);
}

// Function to get reviews for a product
function getProductReviews(productId) {
    return customerReviews[productId] || [];
}

// Function to calculate review statistics
function getReviewStats(productId) {
    const reviews = getProductReviews(productId);
    if (reviews.length === 0) {
        return {
            averageRating: 0,
            totalReviews: 0,
            ratingBreakdown: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
        };
    }
    
    const totalRating = reviews.reduce((sum, review) => sum + review.rating, 0);
    const averageRating = totalRating / reviews.length;
    
    const ratingBreakdown = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    reviews.forEach(review => {
        ratingBreakdown[review.rating]++;
    });
    
    return {
        averageRating: Math.round(averageRating * 10) / 10,
        totalReviews: reviews.length,
        ratingBreakdown
    };
}

// Function to sort reviews
function sortReviews(reviews, sortBy = 'newest') {
    const sortedReviews = [...reviews];
    
    switch (sortBy) {
        case 'oldest':
            return sortedReviews.sort((a, b) => new Date(a.date) - new Date(b.date));
        case 'highest':
            return sortedReviews.sort((a, b) => b.rating - a.rating);
        case 'lowest':
            return sortedReviews.sort((a, b) => a.rating - b.rating);
        case 'helpful':
            return sortedReviews.sort((a, b) => b.helpful - a.helpful);
        case 'newest':
        default:
            return sortedReviews.sort((a, b) => new Date(b.date) - new Date(a.date));
    }
}

// Export functions for global use
window.sellers = sellers;
window.productSellerMapping = productSellerMapping;
window.customerReviews = customerReviews;
window.getSellerById = getSellerById;
window.getSellerForProduct = getSellerForProduct;
window.getProductReviews = getProductReviews;
window.getReviewStats = getReviewStats;
window.sortReviews = sortReviews;
