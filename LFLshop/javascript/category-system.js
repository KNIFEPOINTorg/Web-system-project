// LFLshop Category Management System
// Comprehensive hierarchical category structure for Ethiopian marketplace

// Main category data structure with Ethiopian cultural integration
const categorySystem = {
    // Traditional Ethiopian Categories
    'traditional-textiles': {
        id: 'traditional-textiles',
        name: 'Traditional Textiles',
        nameAmharic: 'ባህላዊ ጨርቃ ጨርቅ',
        icon: 'fas fa-tshirt',
        description: 'Authentic Ethiopian traditional clothing and textiles',
        descriptionAmharic: 'ትክክለኛ የኢትዮጵያ ባህላዊ ልብስ እና ጨርቃ ጨርቅ',
        cultural: true,
        featured: true,
        subcategories: {
            'habesha-dresses': {
                id: 'habesha-dresses',
                name: 'Habesha Dresses',
                nameAmharic: 'የሀበሻ ቀሚስ',
                description: 'Traditional Ethiopian dresses with cultural patterns',
                types: ['Kemis', 'Netela Dress', 'Wedding Dress', 'Festival Dress', 'Modern Habesha']
            },
            'shawls-netela': {
                id: 'shawls-netela',
                name: 'Shawls & Netela',
                nameAmharic: 'ሻል እና ነጠላ',
                description: 'Traditional Ethiopian shawls and wraps',
                types: ['Cotton Netela', 'Silk Netela', 'Decorative Shawls', 'Prayer Shawls', 'Ceremonial Wraps']
            },
            'traditional-mens': {
                id: 'traditional-mens',
                name: 'Traditional Men\'s Wear',
                nameAmharic: 'ባህላዊ የወንዶች ልብስ',
                description: 'Traditional clothing for Ethiopian men',
                types: ['Habesha Suit', 'Traditional Shirt', 'Cultural Pants', 'Ceremonial Wear', 'Festival Clothing']
            },
            'cultural-accessories': {
                id: 'cultural-accessories',
                name: 'Cultural Accessories',
                nameAmharic: 'ባህላዊ መለዋወጫዎች',
                description: 'Traditional Ethiopian clothing accessories',
                types: ['Traditional Belts', 'Cultural Headwear', 'Ceremonial Items', 'Traditional Bags', 'Cultural Footwear']
            }
        }
    },

    'jewelry-accessories': {
        id: 'jewelry-accessories',
        name: 'Jewelry & Accessories',
        nameAmharic: 'ጌጣጌጥ እና መለዋወጫዎች',
        icon: 'fas fa-gem',
        description: 'Traditional and modern jewelry with Ethiopian craftsmanship',
        descriptionAmharic: 'ባህላዊ እና ዘመናዊ ጌጣጌጥ በኢትዮጵያ እጅ ሥራ',
        cultural: true,
        featured: true,
        subcategories: {
            'silver-crosses': {
                id: 'silver-crosses',
                name: 'Ethiopian Silver Crosses',
                nameAmharic: 'የኢትዮጵያ ብር መስቀል',
                description: 'Traditional Orthodox crosses and religious jewelry',
                types: ['Lalibela Cross', 'Gondar Cross', 'Axum Cross', 'Modern Cross', 'Pendant Crosses']
            },
            'traditional-jewelry': {
                id: 'traditional-jewelry',
                name: 'Traditional Jewelry',
                nameAmharic: 'ባህላዊ ጌጣጌጥ',
                description: 'Authentic Ethiopian traditional jewelry pieces',
                types: ['Silver Earrings', 'Traditional Necklaces', 'Bracelets', 'Rings', 'Hair Ornaments']
            },
            'modern-accessories': {
                id: 'modern-accessories',
                name: 'Modern Accessories',
                nameAmharic: 'ዘመናዊ መለዋወጫዎች',
                description: 'Contemporary jewelry and fashion accessories',
                types: ['Fashion Jewelry', 'Watches', 'Sunglasses', 'Bags', 'Belts']
            }
        }
    },

    'coffee-beverages': {
        id: 'coffee-beverages',
        name: 'Coffee & Beverages',
        nameAmharic: 'ቡና እና መጠጦች',
        icon: 'fas fa-coffee',
        description: 'Premium Ethiopian coffee and traditional beverages',
        descriptionAmharic: 'ከፍተኛ ጥራት ያለው የኢትዮጵያ ቡና እና ባህላዊ መጠጦች',
        cultural: true,
        featured: true,
        subcategories: {
            'single-origin-beans': {
                id: 'single-origin-beans',
                name: 'Single-Origin Coffee Beans',
                nameAmharic: 'ነጠላ ምንጭ የቡና ፍሬ',
                description: 'Premium coffee beans from specific Ethiopian regions',
                types: ['Yirgacheffe', 'Sidamo', 'Harrar', 'Limu', 'Jimma', 'Kaffa']
            },
            'coffee-accessories': {
                id: 'coffee-accessories',
                name: 'Coffee Accessories',
                nameAmharic: 'የቡና መሳሪያዎች',
                description: 'Traditional and modern coffee brewing equipment',
                types: ['Grinders', 'Filters', 'Brewing Equipment', 'Storage Containers', 'Serving Sets']
            },
            'traditional-brewing': {
                id: 'traditional-brewing',
                name: 'Traditional Brewing Equipment',
                nameAmharic: 'ባህላዊ የቡና ማዘጋጃ መሳሪያዎች',
                description: 'Authentic Ethiopian coffee ceremony equipment',
                types: ['Jebenas', 'Coffee Cups', 'Roasting Pans', 'Incense Burners', 'Ceremony Sets']
            }
        }
    },

    'pottery-ceramics': {
        id: 'pottery-ceramics',
        name: 'Pottery & Ceramics',
        nameAmharic: 'ሸክላ እና ሴራሚክ',
        icon: 'fas fa-vase',
        description: 'Traditional Ethiopian pottery and ceramic art',
        descriptionAmharic: 'ባህላዊ የኢትዮጵያ ሸክላ እና ሴራሚክ ጥበብ',
        cultural: true,
        featured: true,
        subcategories: {
            'jebenas': {
                id: 'jebenas',
                name: 'Coffee Jebenas',
                nameAmharic: 'የቡና ጀበና',
                description: 'Traditional Ethiopian coffee pots',
                types: ['Clay Jebenas', 'Decorative Jebenas', 'Ceremonial Jebenas', 'Modern Jebenas', 'Miniature Jebenas']
            },
            'decorative-vessels': {
                id: 'decorative-vessels',
                name: 'Decorative Vessels',
                nameAmharic: 'ማስዋቢያ ዕቃዎች',
                description: 'Artistic pottery and decorative ceramic pieces',
                types: ['Vases', 'Bowls', 'Decorative Plates', 'Art Sculptures', 'Wall Hangings']
            },
            'dinnerware': {
                id: 'dinnerware',
                name: 'Dinnerware & Tableware',
                nameAmharic: 'የምግብ ዕቃዎች',
                description: 'Functional pottery for dining and serving',
                types: ['Plates', 'Bowls', 'Cups', 'Serving Dishes', 'Traditional Eating Sets']
            }
        }
    },

    'spices-food': {
        id: 'spices-food',
        name: 'Spices & Food Products',
        nameAmharic: 'ቅመማ ቅመም እና የምግብ ተዋጽኦዎች',
        icon: 'fas fa-pepper-hot',
        description: 'Authentic Ethiopian spices and traditional food products',
        descriptionAmharic: 'ትክክለኛ የኢትዮጵያ ቅመማ ቅመም እና ባህላዊ የምግብ ተዋጽኦዎች',
        cultural: true,
        featured: true,
        subcategories: {
            'traditional-spices': {
                id: 'traditional-spices',
                name: 'Traditional Spice Blends',
                nameAmharic: 'ባህላዊ የቅመማ ቅመም ድብልቅ',
                description: 'Authentic Ethiopian spice mixtures',
                types: ['Berbere', 'Mitmita', 'Shiro Powder', 'Korerima', 'Fenugreek']
            },
            'honey-products': {
                id: 'honey-products',
                name: 'Honey & Natural Products',
                nameAmharic: 'ማር እና ተፈጥሯዊ ተዋጽኦዎች',
                description: 'Pure Ethiopian honey and natural food products',
                types: ['Wild Honey', 'Processed Honey', 'Honeycomb', 'Bee Products', 'Natural Sweeteners']
            },
            'packaged-foods': {
                id: 'packaged-foods',
                name: 'Packaged Traditional Foods',
                nameAmharic: 'የታሸጉ ባህላዊ ምግቦች',
                description: 'Ready-to-cook traditional Ethiopian foods',
                types: ['Injera Mix', 'Sauce Mixes', 'Snack Foods', 'Beverages', 'Preserved Foods']
            }
        }
    },

    // Modern E-commerce Categories
    'electronics': {
        id: 'electronics',
        name: 'Electronics',
        nameAmharic: 'ኤሌክትሮኒክስ',
        icon: 'fas fa-laptop',
        description: 'Modern electronics and technology products',
        descriptionAmharic: 'ዘመናዊ ኤሌክትሮኒክስ እና ቴክኖሎጂ ምርቶች',
        cultural: false,
        featured: false,
        subcategories: {
            'mobile-phones': {
                id: 'mobile-phones',
                name: 'Mobile Phones & Tablets',
                nameAmharic: 'ሞባይል ስልክ እና ታብሌት',
                description: 'Smartphones, tablets, and mobile accessories',
                types: ['Smartphones', 'Feature Phones', 'Tablets', 'Phone Cases', 'Chargers', 'Accessories']
            },
            'computers': {
                id: 'computers',
                name: 'Computers & Laptops',
                nameAmharic: 'ኮምፒውተር እና ላፕቶፕ',
                description: 'Desktop computers, laptops, and computer accessories',
                types: ['Laptops', 'Desktop PCs', 'Monitors', 'Keyboards', 'Mice', 'Storage']
            },
            'audio-equipment': {
                id: 'audio-equipment',
                name: 'Audio & Video Equipment',
                nameAmharic: 'የድምጽ እና የቪዲዮ መሳሪያዎች',
                description: 'Audio systems, headphones, and video equipment',
                types: ['Headphones', 'Speakers', 'Audio Systems', 'Cameras', 'Video Equipment']
            }
        }
    },

    'clothing-fashion': {
        id: 'clothing-fashion',
        name: 'Clothing & Fashion',
        nameAmharic: 'ልብስ እና ፋሽን',
        icon: 'fas fa-tshirt',
        description: 'Modern clothing and fashion items',
        descriptionAmharic: 'ዘመናዊ ልብስ እና ፋሽን ዕቃዎች',
        cultural: false,
        featured: false,
        subcategories: {
            'mens-clothing': {
                id: 'mens-clothing',
                name: 'Men\'s Clothing',
                nameAmharic: 'የወንዶች ልብስ',
                description: 'Modern clothing for men',
                types: ['Shirts', 'Pants', 'Suits', 'Casual Wear', 'Sportswear', 'Underwear']
            },
            'womens-clothing': {
                id: 'womens-clothing',
                name: 'Women\'s Clothing',
                nameAmharic: 'የሴቶች ልብስ',
                description: 'Modern clothing for women',
                types: ['Dresses', 'Tops', 'Pants', 'Skirts', 'Formal Wear', 'Casual Wear']
            },
            'shoes-footwear': {
                id: 'shoes-footwear',
                name: 'Shoes & Footwear',
                nameAmharic: 'ጫማ እና የእግር ልብስ',
                description: 'Footwear for all occasions',
                types: ['Dress Shoes', 'Casual Shoes', 'Sports Shoes', 'Sandals', 'Boots', 'Traditional Footwear']
            }
        }
    },

    'home-garden': {
        id: 'home-garden',
        name: 'Home & Garden',
        nameAmharic: 'ቤት እና የአትክልት ስፍራ',
        icon: 'fas fa-home',
        description: 'Home improvement and garden supplies',
        descriptionAmharic: 'የቤት ማሻሻያ እና የአትክልት ስፍራ አቅርቦቶች',
        cultural: false,
        featured: false,
        subcategories: {
            'furniture': {
                id: 'furniture',
                name: 'Furniture',
                nameAmharic: 'የቤት እቃዎች',
                description: 'Home and office furniture',
                types: ['Living Room', 'Bedroom', 'Dining Room', 'Office Furniture', 'Storage', 'Traditional Furniture']
            },
            'home-decor': {
                id: 'home-decor',
                name: 'Home Decor',
                nameAmharic: 'የቤት ማስዋቢያ',
                description: 'Decorative items for home',
                types: ['Wall Art', 'Decorative Objects', 'Lighting', 'Textiles', 'Plants', 'Ethiopian Decor']
            },
            'kitchen-items': {
                id: 'kitchen-items',
                name: 'Kitchen & Dining',
                nameAmharic: 'ኩሽና እና የምግብ ቤት',
                description: 'Kitchen appliances and dining items',
                types: ['Cookware', 'Small Appliances', 'Utensils', 'Storage', 'Dining Sets', 'Traditional Cooking']
            }
        }
    }
};

// Category utility functions
const CategoryManager = {
    // Get all categories
    getAllCategories() {
        return categorySystem;
    },

    // Get category by ID
    getCategoryById(categoryId) {
        return categorySystem[categoryId] || null;
    },

    // Get subcategory by parent and subcategory ID
    getSubcategoryById(parentId, subcategoryId) {
        const parent = this.getCategoryById(parentId);
        return parent?.subcategories?.[subcategoryId] || null;
    },

    // Get featured categories (Ethiopian traditional categories)
    getFeaturedCategories() {
        return Object.values(categorySystem).filter(cat => cat.featured);
    },

    // Get cultural categories
    getCulturalCategories() {
        return Object.values(categorySystem).filter(cat => cat.cultural);
    },

    // Search categories by name (supports both English and Amharic)
    searchCategories(query) {
        const results = [];
        const searchTerm = query.toLowerCase();

        Object.values(categorySystem).forEach(category => {
            // Search main category
            if (category.name.toLowerCase().includes(searchTerm) ||
                category.nameAmharic.includes(query) ||
                category.description.toLowerCase().includes(searchTerm)) {
                results.push({
                    type: 'category',
                    category: category,
                    subcategory: null
                });
            }

            // Search subcategories
            if (category.subcategories) {
                Object.values(category.subcategories).forEach(subcategory => {
                    if (subcategory.name.toLowerCase().includes(searchTerm) ||
                        subcategory.nameAmharic?.includes(query) ||
                        subcategory.description.toLowerCase().includes(searchTerm)) {
                        results.push({
                            type: 'subcategory',
                            category: category,
                            subcategory: subcategory
                        });
                    }
                });
            }
        });

        return results;
    },

    // Get category breadcrumb
    getCategoryBreadcrumb(categoryId, subcategoryId = null) {
        const category = this.getCategoryById(categoryId);
        if (!category) return [];

        const breadcrumb = [category];

        if (subcategoryId && category.subcategories) {
            const subcategory = category.subcategories[subcategoryId];
            if (subcategory) {
                breadcrumb.push(subcategory);
            }
        }

        return breadcrumb;
    },

    // Validate category selection
    validateCategorySelection(categoryId, subcategoryId = null, type = null) {
        const category = this.getCategoryById(categoryId);
        if (!category) {
            return { valid: false, error: 'Invalid category selected' };
        }

        if (subcategoryId) {
            const subcategory = this.getSubcategoryById(categoryId, subcategoryId);
            if (!subcategory) {
                return { valid: false, error: 'Invalid subcategory selected' };
            }

            if (type && subcategory.types && !subcategory.types.includes(type)) {
                return { valid: false, error: 'Invalid product type for selected subcategory' };
            }
        }

        return { valid: true };
    }
};

// Export for global use
window.categorySystem = categorySystem;
window.CategoryManager = CategoryManager;
