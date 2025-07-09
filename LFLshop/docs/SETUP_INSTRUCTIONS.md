# 🚀 LFLshop Complete Setup Instructions

## ⚠️ CRITICAL SECURITY NOTICE
This is a **PRODUCTION-READY** e-commerce system. Follow these steps exactly for secure deployment.

## 📋 Prerequisites
- XAMPP with PHP 7.4+ and MySQL 5.7+
- GD extension enabled for image processing
- Write permissions on project directory

## 🔧 Step-by-Step Setup

### 1. **Database Setup (Enhanced Schema)**
```bash
# Navigate to project directory
cd c:\xampp\htdocs\LFLshop

# Run enhanced database setup
http://localhost/LFLshop/setup_enhanced_database.php
```

### 2. **Create Admin User**
```bash
# Create admin account (REQUIRED)
http://localhost/LFLshop/create_admin.php
```
**⚠️ IMPORTANT:** Change admin password immediately after first login!

### 3. **Fix Navigation Links**
```bash
# Fix any broken navigation paths
http://localhost/LFLshop/fix_navigation.php
```

### 4. **Create Required Directories**
```bash
# Create upload directories with proper permissions
mkdir uploads
mkdir uploads/products
mkdir uploads/profiles
mkdir cache
mkdir logs

# Set permissions (Windows)
icacls uploads /grant Everyone:F
icacls cache /grant Everyone:F
icacls logs /grant Everyone:F
```

### 5. **Security Configuration**
Create `.htaccess` in root directory:
```apache
# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Hide sensitive files
<Files "*.log">
    Deny from all
</Files>

<Files "*.lock">
    Deny from all
</Files>

# Prevent access to sensitive directories
RedirectMatch 403 ^/logs/
RedirectMatch 403 ^/cache/
```

### 6. **Test System**
1. **Homepage:** `http://localhost/LFLshop/html/index.html`
2. **Admin Dashboard:** `http://localhost/LFLshop/html/admin_dashboard.html`
3. **Customer Login:** `http://localhost/LFLshop/html/signin.html`

## 🔑 Default Credentials

### Demo Accounts
- **Customer:** `customer@example.com` / `password`
- **Seller:** `seller@example.com` / `password.r`

### Admin Account
- **Admin:** `admin@lflshop.com` / `Admin123!`
- **⚠️ CHANGE PASSWORD IMMEDIATELY**

### Demo Payment Cards
- **Success:** `4111 1111 1111 1111`
- **Declined:** `4000 0000 0000 0002`
- **CVV:** Any 3 digits
- **Expiry:** Any future date

## 🛡️ Security Checklist

### ✅ Immediate Actions (REQUIRED)
- [ ] Change admin password
- [ ] Delete setup files: `create_admin.php`, `setup_*.php`, `fix_*.php`
- [ ] Set proper file permissions
- [ ] Configure SSL/HTTPS
- [ ] Update database credentials

### ✅ Production Deployment
- [ ] Move to production server
- [ ] Configure proper domain
- [ ] Set up automated backups
- [ ] Configure email notifications
- [ ] Set up monitoring

## 🔧 Configuration Files

### Database Configuration
File: `database/config.php`
```php
// Update for production
define('DB_HOST', 'your_host');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

### Security Settings
File: `includes/security.php`
- CSRF protection enabled
- Rate limiting configured
- Input validation active
- Session security enforced

## 📊 Features Included

### ✅ Core E-commerce
- User registration/authentication
- Product catalog with categories
- Shopping cart functionality
- Order management system
- Payment processing (demo)
- Review and rating system

### ✅ Admin Features
- Complete admin dashboard
- User management
- Order tracking
- Product management
- Analytics and reporting
- System logs

### ✅ Seller Features
- Seller dashboard
- Product management
- Order fulfillment
- Sales analytics
- Image upload system

### ✅ Security Features
- Password hashing (bcrypt)
- CSRF protection
- Input validation/sanitization
- Rate limiting
- Session security
- File upload security
- SQL injection prevention

## 🚨 Troubleshooting

### Common Issues

**1. Database Connection Failed**
```bash
# Check MySQL is running
# Verify credentials in database/config.php
# Ensure database exists
```

**2. File Upload Errors**
```bash
# Check upload directory permissions
# Verify GD extension is enabled
# Check file size limits in php.ini
```

**3. Session Issues**
```bash
# Check session.save_path in php.ini
# Verify session directory permissions
# Clear browser cookies
```

**4. API Errors**
```bash
# Check .htaccess configuration
# Verify mod_rewrite is enabled
# Check error logs in logs/
```

## 📞 Support

### Error Logs
- **API Errors:** `logs/api_errors.log`
- **Security Events:** `logs/security.log`
- **PHP Errors:** Check XAMPP error logs

### Debug Mode
For development only, enable debug mode in `database/config.php`:
```php
define('DEBUG_MODE', true);
```

## 🎯 Next Steps

1. **Customize Design:** Update CSS and branding
2. **Add Products:** Use seller dashboard to add inventory
3. **Configure Email:** Set up SMTP for notifications
4. **Payment Gateway:** Replace demo payment with real processor
5. **SEO Optimization:** Add meta tags and sitemaps
6. **Performance:** Implement caching and CDN

## ⚠️ PRODUCTION WARNINGS

### 🚫 NEVER DO IN PRODUCTION:
- Leave debug mode enabled
- Use default passwords
- Skip SSL/HTTPS
- Ignore security updates
- Expose setup files
- Use demo payment in live environment

### ✅ ALWAYS DO IN PRODUCTION:
- Regular security audits
- Automated backups
- Monitor error logs
- Update dependencies
- Use strong passwords
- Implement proper logging

---

**🎉 Your LFLshop e-commerce platform is now ready for production use!**

For additional support or customization, refer to the code documentation and security guidelines.