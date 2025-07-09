# LFL Shop - XAMPP Integration Guide

## 🚀 Quick Start with XAMPP

### Prerequisites
- XAMPP installed on your system
- Project located in `C:\xampp\htdocs\LFLshop\`

### 1. Start XAMPP Services

#### Option A: Use the Startup Script
```bash
# Double-click the provided batch file
start_xampp.bat
```

#### Option B: Manual Setup
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service
4. Ensure both services show "Running" status

### 2. Access Setup Page
Open your browser and navigate to:
```
http://localhost/LFLshop/xampp_setup.php
```

This will automatically check your system and guide you through setup.

## 📋 System Requirements Check

The setup page will verify:
- ✅ XAMPP environment detection
- ✅ PHP version compatibility (7.4+)
- ✅ MySQL connection
- ✅ Database tables existence
- ✅ File permissions
- ✅ Required PHP extensions

## 🗄️ Database Setup

### Automatic Setup
1. Visit `http://localhost/LFLshop/xampp_setup.php`
2. Click "Setup Database" if needed
3. The system will create all required tables and demo data

### Manual Setup
If you prefer manual setup:
```bash
# Access phpMyAdmin
http://localhost/phpmyadmin

# Create database
CREATE DATABASE lflshop;

# Run setup script
http://localhost/LFLshop/setup_database.php
```

## 🔧 Configuration

### Database Configuration
The system is pre-configured for XAMPP defaults:
- **Host:** localhost
- **Username:** root
- **Password:** (empty)
- **Database:** lflshop

### File Structure for XAMPP
```
C:\xampp\htdocs\LFLshop\
├── index.php                 # Project dashboard
├── xampp_setup.php          # XAMPP integration setup
├── start_xampp.bat          # Quick startup script
├── .htaccess                # Apache configuration
├── html/                    # Main website files
│   ├── index.html          # Customer-facing homepage
│   ├── signin.html         # Sign in page
│   └── signup.html         # Sign up page
├── api/                     # REST API endpoints
├── admin/                   # Admin panel
├── database/               # Database configuration
├── css/                    # Stylesheets
├── javascript/             # JavaScript files
└── uploads/                # File uploads
```

## 🌐 Access URLs

### Main Access Points
- **Setup & Status:** `http://localhost/LFLshop/xampp_setup.php`
- **Project Dashboard:** `http://localhost/LFLshop/index.php`
- **Main Website:** `http://localhost/LFLshop/html/index.html`
- **Admin Panel:** `http://localhost/LFLshop/admin/`

### User Access
- **Sign In:** `http://localhost/LFLshop/html/signin.html`
- **Sign Up:** `http://localhost/LFLshop/html/signup.html`
- **Customer Dashboard:** `http://localhost/LFLshop/html/customer-dashboard.html`
- **Seller Dashboard:** `http://localhost/LFLshop/html/seller-dashboard.html`

### API Endpoints
- **Authentication:** `http://localhost/LFLshop/api/auth.php`
- **Products:** `http://localhost/LFLshop/api/products.php`
- **Cart:** `http://localhost/LFLshop/api/cart.php`
- **Orders:** `http://localhost/LFLshop/api/orders.php`

## 👥 Demo Accounts

After setup, these demo accounts are available:

### Customer Account
- **Email:** customer@demo.com
- **Password:** password
- **Access:** Customer dashboard, shopping features

### Seller Account
- **Email:** seller@demo.com
- **Password:** password
- **Access:** Seller dashboard, product management

### Admin Account
- **Email:** admin@demo.com
- **Password:** admin123
- **Access:** Full admin panel, system management

## 🔧 Troubleshooting

### Common Issues

#### Apache Won't Start
- Check if port 80 is in use
- Try changing Apache port in XAMPP config
- Disable IIS or other web servers

#### MySQL Won't Start
- Check if port 3306 is in use
- Stop other MySQL services
- Check XAMPP logs for errors

#### Database Connection Failed
1. Ensure MySQL is running in XAMPP
2. Check database credentials in `database/config.php`
3. Create database manually in phpMyAdmin

#### Permission Errors
- Ensure XAMPP has write permissions
- Check folder permissions for uploads/logs
- Run XAMPP as administrator if needed

#### 404 Errors
- Verify .htaccess file exists
- Check Apache mod_rewrite is enabled
- Ensure files are in correct directory

### Debug Mode
Enable debug mode by adding to `database/config.php`:
```php
define('DEBUG_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📊 Performance Optimization

### XAMPP Optimization
1. **Increase PHP limits** in `php.ini`:
   ```ini
   memory_limit = 256M
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   ```

2. **Enable caching** in `.htaccess` (already configured)

3. **Optimize MySQL** in `my.ini`:
   ```ini
   innodb_buffer_pool_size = 128M
   query_cache_size = 32M
   ```

## 🔒 Security Considerations

### For Development (XAMPP)
- Default configuration is suitable for local development
- Demo accounts have simple passwords for testing
- Debug mode may expose sensitive information

### For Production
- Change all default passwords
- Enable HTTPS
- Configure proper file permissions
- Disable debug mode
- Use environment variables for sensitive data

## 📱 Mobile Testing

Test responsive design on mobile:
```
# Use browser dev tools or access from mobile device
http://[your-ip]:80/LFLshop/html/index.html
```

## 🔄 Updates and Maintenance

### Backup Database
```bash
# Via phpMyAdmin or command line
mysqldump -u root lflshop > backup.sql
```

### Update Files
- Replace files in `C:\xampp\htdocs\LFLshop\`
- Clear browser cache
- Test functionality

## 📞 Support

### Getting Help
1. Check the setup page for system status
2. Review XAMPP logs in `C:\xampp\apache\logs\`
3. Check browser console for JavaScript errors
4. Verify database connection in phpMyAdmin

### Useful Commands
```bash
# Restart XAMPP services
# Use XAMPP Control Panel

# Check PHP configuration
http://localhost/LFLshop/phpinfo.php

# Test database connection
http://localhost/LFLshop/test_db.php
```

## 🎯 Next Steps

After successful XAMPP integration:

1. **Explore the website** - Browse products, test user registration
2. **Try admin features** - Manage users, products, orders
3. **Test API endpoints** - Use browser dev tools or Postman
4. **Customize design** - Modify CSS and templates
5. **Add products** - Use seller dashboard to add inventory
6. **Configure payments** - Set up payment processing
7. **Deploy to production** - When ready for live environment

---

## 🏆 Success Indicators

Your XAMPP integration is successful when:
- ✅ All services show "Running" in XAMPP Control Panel
- ✅ Setup page shows all green checkmarks
- ✅ You can access the main website
- ✅ Demo accounts can sign in successfully
- ✅ Database operations work (add to cart, etc.)
- ✅ Admin panel is accessible

**Happy developing with LFL Shop on XAMPP! 🚀**