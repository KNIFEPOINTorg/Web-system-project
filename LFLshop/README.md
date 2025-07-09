# LFLshop - Ethiopian E-commerce Platform

A modern, responsive e-commerce platform designed to support Ethiopian creators and showcase authentic Ethiopian products.

## 🌟 Features

### 🛍️ Core E-commerce
- **Product Catalog**: Browse and search Ethiopian products
- **Shopping Cart**: Add, remove, and manage items
- **User Authentication**: Secure login/register system
- **Order Management**: Track orders and purchase history
- **Wishlist**: Save favorite products
- **Search & Filters**: Advanced product search functionality

### 👥 User Roles
- **Customers**: Browse, purchase, and manage orders
- **Sellers**: Manage products, inventory, and sales
- **Admins**: Full system administration and control

### 🎨 Modern Design
- **Glassmorphism UI**: Modern, elegant design with backdrop blur effects
- **Responsive Design**: Works perfectly on all devices
- **Ethiopian Theme**: Cultural elements and authentic styling
- **Smooth Animations**: Enhanced user experience with transitions
- **Accessibility**: WCAG compliant design

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (Apache, MySQL, PHP)
- **Web Browser** (Chrome, Firefox, Safari, Edge)

### Installation

1. **Set up XAMPP**
   - Start Apache and MySQL services
   - Navigate to the LFLshop folder

2. **Database Setup**
   - Access `http://localhost/Web-system-project/LFLshop/config/setup_database.php`
   - Run the database setup script
   - Create demo accounts if needed

3. **Access the Application**
   - **Main Site**: `http://localhost/Web-system-project/LFLshop/`
   - **Admin Panel**: `http://localhost/Web-system-project/LFLshop/admin_control_panel.php`
   - **Login**: `http://localhost/Web-system-project/LFLshop/login.php`

## 🔐 Default Credentials

### Admin Access
- **Password**: `admin123`
- **Access**: `http://localhost/Web-system-project/LFLshop/admin_control_panel.php`

### Demo Accounts
- **Customer**: `customer@demo.com` / `password`
- **Seller**: `seller@demo.com` / `password`
- **Admin**: `admin@demo.com` / `admin123`

## 📁 Project Structure

```
LFLshop/
├── admin/                  # Admin panel files
├── api/                    # REST API endpoints
├── auth/                   # Authentication system
├── config/                 # Configuration files
├── css/                    # Stylesheets
├── database/               # Database configuration
├── docs/                   # Documentation
├── html/                   # Static HTML pages
├── includes/               # Shared PHP components
├── javascript/             # Client-side scripts
├── pages/                  # Application pages
├── php/                    # PHP classes and utilities
├── uploads/                # User uploaded files
├── Logo/                   # Brand assets
├── IMG/                    # Images and media
├── index.php               # Main entry point
├── login.php               # User login
├── register.php            # User registration
└── admin_control_panel.php # Admin interface
```

## 🛠️ Development

### Key Components
- **User Management**: Registration, login, profiles
- **Product Management**: CRUD operations, categories
- **Order System**: Cart, checkout, order tracking
- **Admin Panel**: System administration and monitoring
- **Search System**: Advanced product search and filtering

## 🔒 Security Features

- **Password Hashing**: Secure bcrypt hashing
- **Input Validation**: Server-side validation
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Output escaping
- **CSRF Protection**: Token-based protection
- **Session Management**: Secure session handling

## 📱 Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers

---

**Built with ❤️ for Ethiopian creators and entrepreneurs**