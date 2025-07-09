# LFLshop - Ethiopian E-commerce Platform

LFLshop is a comprehensive e-commerce platform designed specifically for Ethiopian businesses and customers. It supports local artisans, promotes Ethiopian culture, and provides a seamless shopping experience with Ethiopian Birr (ETB) currency support.

## 🌟 Features

### Core Functionality
- **Multi-user System**: Support for customers, sellers, and administrators
- **Product Management**: Complete product catalog with categories, images, and inventory
- **Shopping Cart**: Full cart functionality with session persistence
- **Order Management**: Order processing, tracking, and status updates
- **Payment Integration**: Multiple payment methods including cash on delivery
- **User Authentication**: Secure login/registration with session management

### Ethiopian-Specific Features
- **Currency Support**: Native Ethiopian Birr (ETB) formatting and calculations
- **Local Business Focus**: Designed for Ethiopian artisans and local businesses
- **Cultural Products**: Categories for traditional textiles, coffee, spices, and handicrafts
- **Ethiopian Phone Validation**: Support for Ethiopian phone number formats (+251, 09/07)
- **Localized Content**: Ethiopian-specific product categories and descriptions

### Technical Features
- **Responsive Design**: Mobile-first approach with touch-friendly interfaces
- **Security**: CSRF protection, input validation, and secure session management
- **Performance**: Caching system, image optimization, and lazy loading
- **Accessibility**: WCAG 2.1 AA compliance with keyboard navigation and screen reader support
- **SEO Optimized**: Meta tags, structured data, and search engine friendly URLs

## 🛠 Technology Stack

### Backend
- **PHP 7.4+**: Server-side logic and API endpoints
- **MySQL 5.7+**: Database for storing products, users, and orders
- **Apache/Nginx**: Web server with mod_rewrite support

### Frontend
- **HTML5**: Semantic markup with accessibility features
- **CSS3**: Modern styling with CSS Grid and Flexbox
- **JavaScript ES6+**: Modular architecture with classes and modules
- **Font Awesome**: Icon library for UI elements

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Quick Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/lflshop.git
   cd lflshop
   ```

2. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE lflshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Import schema
   mysql -u root -p lflshop < database/enhanced_schema.sql
   ```

3. **Configuration**
   ```bash
   # Copy environment configuration
   cp .env.example .env
   
   # Edit database credentials
   nano .env
   ```

4. **Set Permissions**
   ```bash
   # Make upload directories writable
   chmod 755 uploads/
   chmod 755 uploads/products/
   chmod 755 uploads/users/
   chmod 755 logs/
   ```

5. **Initialize Database**
   ```bash
   # Run setup script
   php setup_database.php
   ```

### 🎭 Demo Accounts
The setup script creates demo accounts:
- **Customer**: `customer@demo.com` / `password`
- **Seller**: `seller@demo.com` / `password`
- **Admin**: `admin@demo.com` / `admin123`

## 📁 Project Structure

```
lflshop/
├── 🔌 api/                    # API endpoints
│   ├── auth.php               # Authentication API
│   ├── products.php           # Products API
│   ├── cart.php               # Shopping cart API
│   ├── orders.php             # Orders API
│   ├── payment.php            # Payment processing API
│   ├── cors_config.php        # CORS configuration
│   └── error_handler.php      # Error handling
├── 🎨 css/                    # Stylesheets
│   ├── design-system.css      # Design system variables
│   ├── styles.css             # Main styles
│   ├── mobile-optimization.css # Mobile responsiveness
│   └── accessibility.css      # Accessibility improvements
├── 🗄️ database/               # Database files
│   ├── config.php             # Database configuration
│   └── enhanced_schema.sql    # Database schema
├── 🌐 html/                   # HTML pages
│   ├── index.html             # Homepage
│   ├── collections.html       # Product listings
│   ├── signin.html            # Login page
│   ├── signup.html            # Registration page
│   ├── cart.html              # Shopping cart
│   ├── checkout.html          # Checkout process
│   ├── 404.html               # Error pages
│   ├── 403.html
│   └── 500.html
├── ⚡ javascript/             # JavaScript modules
│   ├── config.js              # Configuration and utilities
│   ├── auth.js                # Authentication manager
│   ├── cart.js                # Cart manager
│   ├── collections.js         # Product listings
│   ├── form-validator.js      # Form validation
│   ├── accessibility-helper.js # Accessibility features
│   ├── performance-optimizer.js # Performance optimization
│   └── dependency-loader.js   # Dependency management
├── 🐘 php/                    # PHP classes and handlers
│   ├── classes/               # PHP classes
│   ├── handlers/              # Request handlers
│   └── config/                # Configuration files
├── 📁 uploads/                # File uploads
│   ├── products/              # Product images
│   ├── users/                 # User avatars
│   └── categories/            # Category images
├── 📚 docs/                   # Documentation
│   ├── API_DOCUMENTATION.md
│   └── JAVASCRIPT_DOCUMENTATION.md
└── 🔧 includes/               # Utility files
    ├── cache.php              # Caching system
    └── security.php           # Security utilities
```

## 🎯 Usage

### For Customers
1. **Browse Products**: Visit the homepage or collections page
2. **Search**: Use the search functionality to find specific products
3. **Add to Cart**: Click "Add to Cart" on product pages
4. **Checkout**: Review cart and complete purchase with ETB pricing
5. **Track Orders**: View order status in customer dashboard

### For Sellers
1. **Register**: Create a seller account with Ethiopian phone number
2. **Add Products**: Use the seller dashboard to add products
3. **Manage Inventory**: Update stock levels and product information
4. **Process Orders**: View and update order statuses
5. **View Analytics**: Track sales and performance in ETB

### For Administrators
1. **User Management**: Manage customer and seller accounts
2. **Product Moderation**: Approve/reject product listings
3. **Order Oversight**: Monitor all orders and transactions
4. **System Configuration**: Manage site settings and categories

## 📡 API Documentation

### Authentication Endpoints
- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=register` - User registration
- `GET /api/auth.php?action=check` - Check authentication status
- `POST /api/auth.php?action=logout` - User logout

### Product Endpoints
- `GET /api/products.php?action=list` - Get product list
- `GET /api/products.php?action=single&id={id}` - Get single product
- `GET /api/products.php?action=featured` - Get featured products
- `GET /api/products.php?action=categories` - Get categories

### Cart Endpoints
- `GET /api/cart.php` - Get cart contents
- `POST /api/cart.php?action=add` - Add item to cart
- `PUT /api/cart.php` - Update cart item
- `DELETE /api/cart.php?item_id={id}` - Remove cart item

For complete API documentation, see [API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md).

## 🔒 Security Features

- ✅ Input validation and sanitization
- ✅ SQL injection prevention with prepared statements
- ✅ XSS protection with output encoding
- ✅ CSRF token validation
- ✅ Secure session management
- ✅ File upload restrictions
- ✅ CORS security configuration
- ✅ Rate limiting protection

## ⚡ Performance Features

- 🚀 Database query caching
- 🖼️ Image lazy loading
- 📦 CSS and JavaScript optimization
- 🗜️ Gzip compression
- 📊 Performance monitoring
- 💾 API response caching

## ♿ Accessibility Features

- 🎯 WCAG 2.1 AA compliance
- ⌨️ Keyboard navigation support
- 🔊 Screen reader compatibility
- 🎨 High contrast mode
- 📱 Mobile-friendly design
- 🔍 Focus management

## 🌍 Browser Support
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
- 📧 Email: support@lflshop.com
- 📚 Documentation: [docs/](docs/)
- 🐛 Issues: GitHub Issues

## 🙏 Acknowledgments

- 🇪🇹 Ethiopian artisans and local businesses
- 🌍 Open source community
- 👥 Contributors and testers
- 🏛️ Cultural heritage preservation organizations

---

**LFLshop** - Supporting Ethiopian creators and preserving cultural heritage through e-commerce. 🇪🇹
