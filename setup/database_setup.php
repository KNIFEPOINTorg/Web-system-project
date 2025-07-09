<?php
/**
 * Database Setup Script
 * Sets up the complete database structure for LFL Shop
 */

require_once '../database/config.php';

echo "<h1>🗄️ LFL Shop Database Setup</h1>";
echo "<div style='font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px;'>";

try {
    $db = new Database();
    
    echo "<h2>📋 Step 1: Creating Users Table</h2>";
    
    // Drop existing table if it exists
    $db->query("DROP TABLE IF EXISTS users");
    $db->execute();
    echo "<p style='color: orange;'>🧹 Dropped existing users table</p>";
    
    // Create users table
    $db->query("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            user_type ENUM('customer', 'seller', 'admin') DEFAULT 'customer',
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(100),
            country VARCHAR(100) DEFAULT 'Ethiopia',
            is_active BOOLEAN DEFAULT TRUE,
            email_verified BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_user_type (user_type),
            INDEX idx_active (is_active)
        )
    ");
    $db->execute();
    echo "<p style='color: green;'>✅ Created users table</p>";
    
    echo "<h2>🛍️ Step 2: Creating Products Table</h2>";
    
    $db->query("DROP TABLE IF EXISTS products");
    $db->execute();
    
    $db->query("
        CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category_id INT,
            image_url VARCHAR(500),
            stock_quantity INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_seller (seller_id),
            INDEX idx_category (category_id),
            INDEX idx_active (is_active)
        )
    ");
    $db->execute();
    echo "<p style='color: green;'>✅ Created products table</p>";
    
    echo "<h2>📂 Step 3: Creating Categories Table</h2>";
    
    $db->query("DROP TABLE IF EXISTS categories");
    $db->execute();
    
    $db->query("
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            parent_id INT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_parent (parent_id),
            INDEX idx_active (is_active)
        )
    ");
    $db->execute();
    echo "<p style='color: green;'>✅ Created categories table</p>";
    
    echo "<h2>🛒 Step 4: Creating Cart Items Table</h2>";
    
    $db->query("DROP TABLE IF EXISTS cart_items");
    $db->execute();
    
    $db->query("
        CREATE TABLE cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_product (user_id, product_id),
            INDEX idx_user (user_id),
            INDEX idx_product (product_id)
        )
    ");
    $db->execute();
    echo "<p style='color: green;'>✅ Created cart_items table</p>";
    
    echo "<h2>📦 Step 5: Creating Orders Table</h2>";
    
    $db->query("DROP TABLE IF EXISTS orders");
    $db->execute();
    
    $db->query("
        CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            shipping_address TEXT NOT NULL,
            payment_method VARCHAR(50),
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_payment_status (payment_status)
        )
    ");
    $db->execute();
    echo "<p style='color: green;'>✅ Created orders table</p>";
    
    echo "<h2>📋 Step 6: Creating Order Items Table</h2>";
    
    $db->query("DROP TABLE IF EXISTS order_items");
    $db->execute();
    
    $db->query("
        CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_order (order_id),
            INDEX idx_product (product_id)
        )
    ");
    $db->execute();
    echo "<p style='color: green;'>✅ Created order_items table</p>";
    
    echo "<h2>👥 Step 7: Creating Demo Accounts</h2>";
    
    // Create demo accounts
    $customerPassword = password_hash('password', PASSWORD_DEFAULT);
    $db->query("
        INSERT INTO users (name, email, password, user_type, phone, address, city, is_active, email_verified) 
        VALUES ('Demo Customer', 'customer@demo.com', :password, 'customer', '+251911234567', 'Bole District, Addis Ababa', 'Addis Ababa', 1, 1)
    ");
    $db->bind(':password', $customerPassword);
    $db->execute();
    $customerId = $db->lastInsertId();
    echo "<p style='color: green;'>✅ Created customer account (ID: $customerId)</p>";
    
    $sellerPassword = password_hash('password', PASSWORD_DEFAULT);
    $db->query("
        INSERT INTO users (name, email, password, user_type, phone, address, city, is_active, email_verified) 
        VALUES ('Demo Seller', 'seller@demo.com', :password, 'seller', '+251922345678', 'Piassa, Addis Ababa', 'Addis Ababa', 1, 1)
    ");
    $db->bind(':password', $sellerPassword);
    $db->execute();
    $sellerId = $db->lastInsertId();
    echo "<p style='color: green;'>✅ Created seller account (ID: $sellerId)</p>";
    
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("
        INSERT INTO users (name, email, password, user_type, phone, address, city, is_active, email_verified) 
        VALUES ('Admin User', 'admin@demo.com', :password, 'admin', '+251933456789', 'Admin Office', 'Addis Ababa', 1, 1)
    ");
    $db->bind(':password', $adminPassword);
    $db->execute();
    $adminId = $db->lastInsertId();
    echo "<p style='color: green;'>✅ Created admin account (ID: $adminId)</p>";
    
    echo "<h2>📂 Step 8: Creating Sample Categories</h2>";
    
    $categories = [
        ['Electronics', 'Electronic devices and gadgets'],
        ['Clothing', 'Fashion and apparel'],
        ['Books', 'Books and educational materials'],
        ['Home & Garden', 'Home improvement and garden supplies'],
        ['Sports', 'Sports equipment and accessories']
    ];
    
    foreach ($categories as $category) {
        $db->query("INSERT INTO categories (name, description) VALUES (:name, :description)");
        $db->bind(':name', $category[0]);
        $db->bind(':description', $category[1]);
        $db->execute();
        echo "<p style='color: green;'>✅ Created category: {$category[0]}</p>";
    }
    
    echo "<h2>🛍️ Step 9: Creating Sample Products</h2>";
    
    $products = [
        ['Smartphone', 'Latest Android smartphone', 15000.00, 1, 50],
        ['Laptop', 'High-performance laptop', 45000.00, 1, 20],
        ['T-Shirt', 'Cotton t-shirt', 500.00, 2, 100],
        ['Jeans', 'Denim jeans', 1200.00, 2, 75],
        ['Programming Book', 'Learn to code', 800.00, 3, 30]
    ];
    
    foreach ($products as $product) {
        $db->query("
            INSERT INTO products (seller_id, name, description, price, category_id, stock_quantity) 
            VALUES (:seller_id, :name, :description, :price, :category_id, :stock_quantity)
        ");
        $db->bind(':seller_id', $sellerId);
        $db->bind(':name', $product[0]);
        $db->bind(':description', $product[1]);
        $db->bind(':price', $product[2]);
        $db->bind(':category_id', $product[3]);
        $db->bind(':stock_quantity', $product[4]);
        $db->execute();
        echo "<p style='color: green;'>✅ Created product: {$product[0]}</p>";
    }
    
    echo "<h2>✅ Database Setup Complete!</h2>";
    
    echo "<div style='background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; border-radius: 12px; margin: 30px 0; text-align: center;'>";
    echo "<h1>🎉 Database Setup Successful!</h1>";
    echo "<p>All tables have been created and sample data has been inserted.</p>";
    
    echo "<div style='background: rgba(255,255,255,0.2); padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>📊 Database Summary:</h3>";
    echo "<ul style='text-align: left; display: inline-block;'>";
    echo "<li>✅ Users table with 3 demo accounts</li>";
    echo "<li>✅ Products table with sample products</li>";
    echo "<li>✅ Categories table with 5 categories</li>";
    echo "<li>✅ Cart items table</li>";
    echo "<li>✅ Orders and order items tables</li>";
    echo "<li>✅ All foreign key relationships</li>";
    echo "<li>✅ Proper indexes for performance</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<h3>🔑 Demo Credentials:</h3>";
    echo "<p><strong>Customer:</strong> customer@demo.com / password</p>";
    echo "<p><strong>Seller:</strong> seller@demo.com / password</p>";
    echo "<p><strong>Admin:</strong> admin@demo.com / admin123</p>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='../index.php' style='background: white; color: #28a745; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 10px;'>🏠 Go to Dashboard</a>";
    echo "<a href='../tests/quick_test.php' style='background: white; color: #28a745; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 10px;'>🧪 Run Tests</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #dc3545; color: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2>❌ Database Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
    echo "</div>";
}

echo "</div>";
?>