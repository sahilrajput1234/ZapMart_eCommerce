<?php
require_once 'config/database.php';

try {
    // Check if the product_images table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'product_images'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        // Check if image_path column exists
        $stmt = $conn->query("SHOW COLUMNS FROM product_images LIKE 'image_path'");
        $hasImagePath = $stmt->rowCount() > 0;

        // Check if image_url column exists
        $stmt = $conn->query("SHOW COLUMNS FROM product_images LIKE 'image_url'");
        $hasImageUrl = $stmt->rowCount() > 0;

        if ($hasImagePath && !$hasImageUrl) {
            // Rename column from image_path to image_url
            $conn->exec("ALTER TABLE product_images CHANGE image_path image_url VARCHAR(255) NOT NULL");
            echo "Successfully renamed column image_path to image_url\n";
        } elseif (!$hasImagePath && !$hasImageUrl) {
            // Add image_url column if neither exists
            $conn->exec("ALTER TABLE product_images ADD COLUMN image_url VARCHAR(255) NOT NULL");
            echo "Successfully added image_url column\n";
        }

        // Add missing columns if they don't exist
        $stmt = $conn->query("SHOW COLUMNS FROM product_images LIKE 'is_main'");
        if ($stmt->rowCount() == 0) {
            $conn->exec("ALTER TABLE product_images ADD COLUMN is_main TINYINT(1) NOT NULL DEFAULT 0");
            echo "Successfully added is_main column\n";
        }

        $stmt = $conn->query("SHOW COLUMNS FROM product_images LIKE 'sort_order'");
        if ($stmt->rowCount() == 0) {
            $conn->exec("ALTER TABLE product_images ADD COLUMN sort_order INT(11) NOT NULL DEFAULT 0");
            echo "Successfully added sort_order column\n";
        }
    } else {
        // Create the product_images table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS `product_images` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `image_url` varchar(255) NOT NULL,
            `is_main` tinyint(1) NOT NULL DEFAULT 0,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `product_id` (`product_id`),
            CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "Successfully created product_images table\n";
    }

    echo "Database update completed successfully!\n";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?> 