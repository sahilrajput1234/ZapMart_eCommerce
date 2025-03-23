<?php
// Script to fix database schema issues

// Load database connection
require_once 'config/database.php';

echo "Starting database schema check and fixes...\n";

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    try {
        $sql = "SHOW COLUMNS FROM {$table} LIKE '{$column}'";
        $result = $conn->query($sql);
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to check if a table exists
function tableExists($conn, $table) {
    try {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $conn->query($sql);
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Check if brands table exists
if (!tableExists($conn, 'brands')) {
    echo "Creating brands table...\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `brands` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `slug` varchar(100) NOT NULL,
        `description` text DEFAULT NULL,
        `logo` varchar(255) DEFAULT NULL,
        `website` varchar(255) DEFAULT NULL,
        `status` enum('active','inactive') NOT NULL DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    try {
        $conn->exec($sql);
        echo "Brands table created successfully.\n";
        
        // Insert sample brands
        $sql = "
        INSERT INTO `brands` (`name`, `slug`, `description`, `logo`, `website`, `status`) VALUES
        ('Apple', 'apple', 'Leading technology company known for innovative products', 'assets/images/brands/apple.png', 'https://www.apple.com', 'active'),
        ('Samsung', 'samsung', 'Global electronics and technology company', 'assets/images/brands/samsung.png', 'https://www.samsung.com', 'active'),
        ('Nike', 'nike', 'World\'s leading athletic footwear and apparel company', 'assets/images/brands/nike.png', 'https://www.nike.com', 'active'),
        ('Adidas', 'adidas', 'German athletic apparel and footwear corporation', 'assets/images/brands/adidas.png', 'https://www.adidas.com', 'active'),
        ('Sony', 'sony', 'Japanese multinational conglomerate corporation', 'assets/images/brands/sony.png', 'https://www.sony.com', 'active'),
        ('LG', 'lg', 'South Korean multinational electronics company', 'assets/images/brands/lg.png', 'https://www.lg.com', 'active'),
        ('Dell', 'dell', 'American technology company that develops computers', 'assets/images/brands/dell.png', 'https://www.dell.com', 'active'),
        ('HP', 'hp', 'American multinational information technology company', 'assets/images/brands/hp.png', 'https://www.hp.com', 'active')
        ";
        $conn->exec($sql);
        echo "Sample brands inserted.\n";
    } catch (PDOException $e) {
        echo "Error creating brands table: " . $e->getMessage() . "\n";
    }
}

// Check if brand_id column exists in products table
if (tableExists($conn, 'products') && !columnExists($conn, 'products', 'brand_id')) {
    echo "Adding brand_id column to products table...\n";
    
    try {
        $sql = "ALTER TABLE products ADD COLUMN brand_id INT(11) DEFAULT NULL";
        $conn->exec($sql);
        echo "Added brand_id column to products table.\n";
        
        // Add foreign key constraint
        $sql = "ALTER TABLE products ADD CONSTRAINT products_ibfk_2 FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL";
        $conn->exec($sql);
        echo "Added foreign key constraint for brand_id.\n";
        
        // Update sample products with brand IDs
        $updates = [
            "UPDATE products SET brand_id = 1 WHERE name LIKE '%Smartphone%' OR name LIKE '%Smart Watch%'",
            "UPDATE products SET brand_id = 2 WHERE name LIKE '%Laptop%'",
            "UPDATE products SET brand_id = 3 WHERE name LIKE '%T-Shirt%'",
            "UPDATE products SET brand_id = 4 WHERE name LIKE '%Headphones%'",
            "UPDATE products SET brand_id = 5 WHERE name LIKE '%Coffee Maker%'",
            "UPDATE products SET brand_id = 6 WHERE name LIKE '%Yoga Mat%'",
            "UPDATE products SET brand_id = 7 WHERE name LIKE '%Novel%'",
            "UPDATE products SET brand_id = 8 WHERE name LIKE '%Wireless%'"
        ];
        
        foreach ($updates as $sql) {
            $count = $conn->exec($sql);
            echo "Updated " . $count . " products with " . $sql . "\n";
        }
    } catch (PDOException $e) {
        echo "Error adding brand_id column: " . $e->getMessage() . "\n";
    }
}

echo "Database schema check and fixes completed.\n";
?> 