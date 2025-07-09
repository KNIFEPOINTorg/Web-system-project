// Seller Accounts System - Demo Ethiopian Sellers
// Comprehensive seller profiles with business information

// Demo seller accounts with Ethiopian business details
const demoSellers = [
    {
        id: 'seller_001',
        businessName: 'Meron Traditional Crafts',
        ownerName: 'Meron Tadesse',
        email: 'meron@merontraditional.et',
        phone: '+251 911 234 567',
        location: {
            city: 'Addis Ababa',
            region: 'Addis Ababa',
            address: 'Bole Road, Near Edna Mall'
        },
        businessType: 'Traditional Clothing & Textiles',
        established: '2018',
        description: 'Specializing in authentic Habesha dresses and traditional Ethiopian clothing with modern touches. Family business passed down through generations.',
        profileImage: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80',
        businessLicense: 'ET-AA-2018-001234',
        rating: 4.8,
        totalReviews: 342,
        totalSales: 1250,
        joinedDate: '2018-03-15',
        verified: true,
        specialties: ['Traditional Dresses', 'Habesha Kemis', 'Cultural Clothing'],
        languages: ['Amharic', 'English', 'Oromo'],
        socialMedia: {
            instagram: '@merontraditional',
            facebook: 'MeronTraditionalCrafts',
            telegram: '@meronshop'
        },
        businessHours: {
            monday: '8:00 AM - 6:00 PM',
            tuesday: '8:00 AM - 6:00 PM',
            wednesday: '8:00 AM - 6:00 PM',
            thursday: '8:00 AM - 6:00 PM',
            friday: '8:00 AM - 6:00 PM',
            saturday: '9:00 AM - 5:00 PM',
            sunday: 'Closed'
        },
        credentials: {
            username: 'meron_seller',
            password: 'MeronSeller2024!',
            userType: 'shop-and-sell'
        }
    },
    {
        id: 'seller_002',
        businessName: 'Alemayehu Silver Works',
        ownerName: 'Alemayehu Bekele',
        email: 'alemayehu@silverworks.et',
        phone: '+251 918 345 678',
        location: {
            city: 'Lalibela',
            region: 'Amhara',
            address: 'Near Rock Churches, Heritage Area'
        },
        businessType: 'Jewelry & Silver Crafts',
        established: '2015',
        description: 'Master silversmith creating traditional Ethiopian crosses, jewelry, and religious artifacts using ancient techniques.',
        profileImage: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80',
        businessLicense: 'ET-AM-2015-005678',
        rating: 4.9,
        totalReviews: 189,
        totalSales: 890,
        joinedDate: '2015-08-22',
        verified: true,
        specialties: ['Silver Crosses', 'Traditional Jewelry', 'Religious Artifacts'],
        languages: ['Amharic', 'English'],
        socialMedia: {
            instagram: '@alemayehusilver',
            facebook: 'AlemayehuSilverWorks'
        },
        businessHours: {
            monday: '7:00 AM - 5:00 PM',
            tuesday: '7:00 AM - 5:00 PM',
            wednesday: '7:00 AM - 5:00 PM',
            thursday: '7:00 AM - 5:00 PM',
            friday: '7:00 AM - 5:00 PM',
            saturday: '8:00 AM - 4:00 PM',
            sunday: '9:00 AM - 2:00 PM'
        },
        credentials: {
            username: 'alemayehu_seller',
            password: 'AlemSilver2024!',
            userType: 'shop-and-sell'
        }
    },
    {
        id: 'seller_003',
        businessName: 'Highland Coffee Cooperative',
        ownerName: 'Desta Haile',
        email: 'desta@highlandcoffee.et',
        phone: '+251 925 456 789',
        location: {
            city: 'Yirgacheffe',
            region: 'SNNPR',
            address: 'Coffee Plantation Area, Gedeo Zone'
        },
        businessType: 'Coffee & Agricultural Products',
        established: '2012',
        description: 'Family-owned coffee farm producing premium single-origin beans from the birthplace of coffee. Direct trade with international quality standards.',
        profileImage: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80',
        businessLicense: 'ET-SN-2012-009876',
        rating: 4.7,
        totalReviews: 456,
        totalSales: 2100,
        joinedDate: '2012-11-10',
        verified: true,
        specialties: ['Yirgacheffe Coffee', 'Single Origin', 'Organic Beans'],
        languages: ['Amharic', 'English', 'Sidamo'],
        socialMedia: {
            instagram: '@highlandcoffee_et',
            facebook: 'HighlandCoffeeCooperative',
            telegram: '@highlandcoffee'
        },
        businessHours: {
            monday: '6:00 AM - 6:00 PM',
            tuesday: '6:00 AM - 6:00 PM',
            wednesday: '6:00 AM - 6:00 PM',
            thursday: '6:00 AM - 6:00 PM',
            friday: '6:00 AM - 6:00 PM',
            saturday: '7:00 AM - 5:00 PM',
            sunday: '8:00 AM - 4:00 PM'
        },
        credentials: {
            username: 'desta_coffee',
            password: 'DestaCoffee2024!',
            userType: 'shop-and-sell'
        }
    },
    {
        id: 'seller_004',
        businessName: 'Pottery Masters Dire Dawa',
        ownerName: 'Rahel Girma',
        email: 'rahel@potterymastersdd.et',
        phone: '+251 912 567 890',
        location: {
            city: 'Dire Dawa',
            region: 'Dire Dawa',
            address: 'Industrial Area, Pottery Workshop'
        },
        businessType: 'Pottery & Ceramics',
        established: '2016',
        description: 'Traditional Ethiopian pottery workshop creating authentic jebenas, decorative vessels, and ceramic art pieces using local clay.',
        profileImage: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80',
        businessLicense: 'ET-DD-2016-003456',
        rating: 4.6,
        totalReviews: 234,
        totalSales: 670,
        joinedDate: '2016-05-18',
        verified: true,
        specialties: ['Coffee Jebenas', 'Decorative Pottery', 'Ceramic Art'],
        languages: ['Amharic', 'English', 'Somali'],
        socialMedia: {
            instagram: '@potterymastersdd',
            facebook: 'PotteryMastersDireDawa'
        },
        businessHours: {
            monday: '8:00 AM - 5:00 PM',
            tuesday: '8:00 AM - 5:00 PM',
            wednesday: '8:00 AM - 5:00 PM',
            thursday: '8:00 AM - 5:00 PM',
            friday: '8:00 AM - 5:00 PM',
            saturday: '9:00 AM - 4:00 PM',
            sunday: 'Closed'
        },
        credentials: {
            username: 'rahel_pottery',
            password: 'RahelPottery2024!',
            userType: 'shop-and-sell'
        }
    },
    {
        id: 'seller_005',
        businessName: 'Spice Route Ethiopia',
        ownerName: 'Yohannes Mulugeta',
        email: 'yohannes@spiceroute.et',
        phone: '+251 920 678 901',
        location: {
            city: 'Bahir Dar',
            region: 'Amhara',
            address: 'Central Market Area, Spice District'
        },
        businessType: 'Spices & Food Products',
        established: '2019',
        description: 'Premium Ethiopian spice blends and traditional food products. Sourcing directly from farmers across Ethiopia for authentic flavors.',
        profileImage: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80',
        businessLicense: 'ET-AM-2019-007890',
        rating: 4.8,
        totalReviews: 567,
        totalSales: 1890,
        joinedDate: '2019-02-14',
        verified: true,
        specialties: ['Berbere Spice', 'Mitmita', 'Traditional Blends'],
        languages: ['Amharic', 'English'],
        socialMedia: {
            instagram: '@spiceroute_ethiopia',
            facebook: 'SpiceRouteEthiopia',
            telegram: '@spiceroute_et'
        },
        businessHours: {
            monday: '7:00 AM - 7:00 PM',
            tuesday: '7:00 AM - 7:00 PM',
            wednesday: '7:00 AM - 7:00 PM',
            thursday: '7:00 AM - 7:00 PM',
            friday: '7:00 AM - 7:00 PM',
            saturday: '8:00 AM - 6:00 PM',
            sunday: '9:00 AM - 5:00 PM'
        },
        credentials: {
            username: 'yohannes_spice',
            password: 'YohannesSpice2024!',
            userType: 'shop-and-sell'
        }
    }
];

// Function to get seller by ID
function getSellerById(sellerId) {
    return demoSellers.find(seller => seller.id === sellerId);
}

// Function to get seller by business name
function getSellerByBusinessName(businessName) {
    return demoSellers.find(seller => seller.businessName === businessName);
}

// Function to get all sellers
function getAllSellers() {
    return demoSellers;
}

// Function to get sellers by location
function getSellersByLocation(city) {
    return demoSellers.filter(seller => seller.location.city === city);
}

// Function to get sellers by business type
function getSellersByType(businessType) {
    return demoSellers.filter(seller => seller.businessType === businessType);
}

// Function to authenticate seller
function authenticateSeller(username, password) {
    return demoSellers.find(seller => 
        seller.credentials.username === username && 
        seller.credentials.password === password
    );
}

// Export functions for global use
window.getSellerById = getSellerById;
window.getSellerByBusinessName = getSellerByBusinessName;
window.getAllSellers = getAllSellers;
window.getSellersByLocation = getSellersByLocation;
window.getSellersByType = getSellersByType;
window.authenticateSeller = authenticateSeller;
window.demoSellers = demoSellers;
