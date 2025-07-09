<?php
/**
 * Enhanced Database Setup Script
 * Ensures all required tables are created from enhanced_schema.sql
 */

require_once 'database/config.php';

echo "<h1>LFLshop Database Setup</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .error { color: red; background: #ffe8e8; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .info { color: blue; background: #e8f0ff; padding: 10px; margin: 10px 0; border-radius: 5px; }
</style>";

try {
    $db = new Database();

    // Read and execute the enhanced schema
    $schemaFile = 'database/enhanced_schema.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $sql = file_get_contents($schemaFile);

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo "<div class='info'>Executing " . count($statements) . " SQL statements...</div>";

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }

        try {
            $db->query($statement);
            $db->execute();
            $successCount++;

            // Extract table name for reporting
            if (preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches)) {
                echo "<div class='success'>✅ Created table: {$matches[1]}</div>";
            } elseif (preg_match('/DROP TABLE\s+IF EXISTS\s+(\w+)/i', $statement, $matches)) {
                echo "<div class='info'>🗑️ Dropped table: {$matches[1]}</div>";
            }

        } catch (Exception $e) {
            $errorCount++;
            echo "<div class='error'>❌ Error executing statement: " . $e->getMessage() . "</div>";
        }
    }

    echo "<div class='info'>Setup completed: $successCount successful, $errorCount errors</div>";

    // Verify all required tables exist
    $requiredTables = [
        'users', 'categories', 'products', 'orders', 'order_items',
        'cart_items', 'reviews', 'product_images', 'notifications',
        'user_sessions', 'admin_logs', 'payment_transactions'
    ];

    echo "<h2>Table Verification</h2>";

    foreach ($requiredTables as $table) {
        try {
            $db->query("SHOW TABLES LIKE '$table'");
            $result = $db->single();

            if ($result) {
                echo "<div class='success'>✅ Table '$table': EXISTS</div>";
            } else {
                echo "<div class='error'>❌ Table '$table': MISSING</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error checking table '$table': " . $e->getMessage() . "</div>";
        }
    }

    // Insert demo data if tables are empty
    echo "<h2>Demo Data Setup</h2>";

    // Check if demo data already exists
    $db->query("SELECT COUNT(*) as count FROM users WHERE email LIKE '%@demo.com'");
    $demoUsers = $db->single();

    if ($demoUsers['count'] == 0) {
        echo "<div class='info'>Creating demo accounts...</div>";

        // Create demo users
        $demoAccounts = [
            ['customer@demo.com', 'Customer Demo', 'password', 'customer'],
            ['seller@demo.com', 'Seller Demo', 'password', 'seller'],
            ['admin@demo.com', 'Admin Demo', 'admin123', 'admin']
        ];

        foreach ($demoAccounts as $account) {
            $hashedPassword = password_hash($account[2], PASSWORD_DEFAULT);

            $db->query("
                INSERT INTO users (email, name, password, user_type, is_active, email_verified, created_at)
                VALUES (:email, :name, :password, :user_type, 1, 1, NOW())
            ");
            $db->bind(':email', $account[0]);
            $db->bind(':name', $account[1]);
            $db->bind(':password', $hashedPassword);
            $db->bind(':user_type', $account[3]);

            if ($db->execute()) {
                echo "<div class='success'>✅ Created demo {$account[3]}: {$account[0]}</div>";
            } else {
                echo "<div class='error'>❌ Failed to create demo {$account[3]}: {$account[0]}</div>";
            }
        }
    } else {
        echo "<div class='info'>Demo accounts already exist</div>";
    }

    // Create sample categories if none exist
    $db->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $db->single();

    if ($categoryCount['count'] == 0) {
        echo "<div class='info'>Creating sample categories...</div>";

        $categories = [
            ['Ethiopian Coffee', 'ethiopian-coffee', 'Premium coffee beans from Ethiopia'],
            ['Traditional Textiles', 'traditional-textiles', 'Handwoven Ethiopian textiles'],
            ['Spices & Herbs', 'spices-herbs', 'Authentic Ethiopian spices'],
            ['Handicrafts', 'handicrafts', 'Traditional Ethiopian handicrafts'],
            ['Jewelry', 'jewelry', 'Ethiopian traditional jewelry']
        ];

        foreach ($categories as $category) {
            $db->query("
                INSERT INTO categories (name, slug, description, is_active, created_at)
                VALUES (:name, :slug, :description, 1, NOW())
            ");
            $db->bind(':name', $category[0]);
            $db->bind(':slug', $category[1]);
            $db->bind(':description', $category[2]);

            if ($db->execute()) {
                echo "<div class='success'>✅ Created category: {$category[0]}</div>";
            }
        }
    } else {
        echo "<div class='info'>Categories already exist</div>";
    }

    // Insert Ethiopian demo products
    echo "<div class='info'>🇪🇹 Adding Ethiopian demo products...</div>";

    $demoProducts = [
        ['Premium Ethiopian Coffee Beans', 'Authentic single-origin coffee from Ethiopian highlands', 450.00, 'food', '../IMG/arthur-a-M-yKK5TJ7iw-unsplash.jpg', 'Addis Coffee Roasters'],
        ['Traditional Habesha Dress', 'Beautiful handwoven Ethiopian dress with intricate embroidery', 2500.00, 'fashion', '../IMG/sepehr-salehi-0T_TM-JOPYo-unsplash.jpg', 'Ethiopian Textile Arts'],
        ['Berbere Spice Blend', 'Authentic Ethiopian berbere spice mix from traditional recipe', 180.00, 'food', '../IMG/mohammad-mardani-U_Mkmwb57Fk-unsplash.jpg', 'Spice Masters Ethiopia'],
        ['Handcrafted Ethiopian Jewelry', 'Traditional Ethiopian jewelry by local artisans', 850.00, 'jewelry', '../IMG/apple-lao-onjfPTehjhE-unsplash.jpg', 'Ethiopian Artisan Collective']
    ];

    foreach ($demoProducts as $product) {
        $db->query("INSERT INTO products (name, description, price, category, image, seller_name, stock_quantity, is_active, created_at)
                    VALUES (:name, :description, :price, :category, :image, :seller, 50, 1, NOW())");
        $db->bind(':name', $product[0]);
        $db->bind(':description', $product[1]);
        $db->bind(':price', $product[2]);
        $db->bind(':category', $product[3]);
        $db->bind(':image', $product[4]);
        $db->bind(':seller', $product[5]);
        $db->execute();
        echo "<div class='success'>✅ Added: {$product[0]}</div>";
    }

    echo "<div class='success'><h2>✅ Database setup completed successfully!</h2></div>";
    echo "<div class='info'>🇪🇹 LFLshop Ethiopian marketplace ready with " . count($demoProducts) . " demo products!</div>";

} catch (Exception $e) {
    echo "<div class='error'><h2>❌ Database setup failed!</h2>";
    echo "Error: " . $e->getMessage() . "</div>";
}
?>