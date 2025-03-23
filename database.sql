-- Create database
CREATE DATABASE IF NOT EXISTS ecommercres;
USE ecommercres;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `first_name` varchar(50) DEFAULT NULL,
    `last_name` varchar(50) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `city` varchar(50) DEFAULT NULL,
    `state` varchar(50) DEFAULT NULL,
    `zip` varchar(20) DEFAULT NULL,
    `country` varchar(50) DEFAULT NULL,
    `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `last_login` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Remember tokens table for "Remember Me" functionality
CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token` varchar(255) NOT NULL,
    `expires_at` datetime NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `icon` varchar(50) DEFAULT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `short_description` text DEFAULT NULL,
    `price` decimal(10,2) NOT NULL,
    `regular_price` decimal(10,2) DEFAULT NULL,
    `sale_price` decimal(10,2) DEFAULT NULL,
    `stock` int(11) NOT NULL DEFAULT 0,
    `sku` varchar(50) DEFAULT NULL,
    `image` varchar(255) DEFAULT NULL,
    `category_id` int(11) DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `is_featured` tinyint(1) NOT NULL DEFAULT 0,
    `is_deal` tinyint(1) NOT NULL DEFAULT 0,
    `is_new` tinyint(1) NOT NULL DEFAULT 0,
    `average_rating` decimal(3,1) NOT NULL DEFAULT 0.0,
    `reviews_count` int(11) NOT NULL DEFAULT 0,
    `sales_count` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `brand_id` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `category_id` (`category_id`),
    KEY `status` (`status`),
    CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product images table (for multiple images per product)
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `image_url` varchar(255) NOT NULL,
    `is_main` tinyint(1) NOT NULL DEFAULT 0,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product attributes table
CREATE TABLE IF NOT EXISTS `product_attributes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `attribute_name` varchar(50) NOT NULL,
    `attribute_value` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `rating` int(11) NOT NULL,
    `comment` text DEFAULT NULL,
    `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `total` decimal(10,2) NOT NULL,
    `subtotal` decimal(10,2) NOT NULL,
    `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
    `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
    `shipping_method` varchar(50) DEFAULT NULL,
    `payment_method` varchar(50) DEFAULT NULL,
    `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `transaction_id` varchar(100) DEFAULT NULL,
    `first_name` varchar(50) NOT NULL,
    `last_name` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `address` text NOT NULL,
    `city` varchar(50) NOT NULL,
    `state` varchar(50) NOT NULL,
    `zip` varchar(20) NOT NULL,
    `country` varchar(50) NOT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `total` decimal(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlists table
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_product` (`user_id`,`product_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coupons table
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(50) NOT NULL,
    `type` enum('percentage','fixed') NOT NULL,
    `value` decimal(10,2) NOT NULL,
    `min_order` decimal(10,2) DEFAULT NULL,
    `max_discount` decimal(10,2) DEFAULT NULL,
    `starts_at` datetime DEFAULT NULL,
    `expires_at` datetime DEFAULT NULL,
    `usage_limit` int(11) DEFAULT NULL,
    `usage_count` int(11) NOT NULL DEFAULT 0,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Brands table
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

-- Insert sample brands
INSERT INTO `brands` (`name`, `slug`, `description`, `logo`, `website`, `status`) VALUES
('Apple', 'apple', 'Leading technology company known for innovative products', 'assets/images/brands/apple.png', 'https://www.apple.com', 'active'),
('Samsung', 'samsung', 'Global electronics and technology company', 'assets/images/brands/samsung.png', 'https://www.samsung.com', 'active'),
('Nike', 'nike', 'World\'s leading athletic footwear and apparel company', 'assets/images/brands/nike.png', 'https://www.nike.com', 'active'),
('Adidas', 'adidas', 'German athletic apparel and footwear corporation', 'assets/images/brands/adidas.png', 'https://www.adidas.com', 'active'),
('Sony', 'sony', 'Japanese multinational conglomerate corporation', 'assets/images/brands/sony.png', 'https://www.sony.com', 'active'),
('LG', 'lg', 'South Korean multinational electronics company', 'assets/images/brands/lg.png', 'https://www.lg.com', 'active'),
('Dell', 'dell', 'American technology company that develops computers', 'assets/images/brands/dell.png', 'https://www.dell.com', 'active'),
('HP', 'hp', 'American multinational information technology company', 'assets/images/brands/hp.png', 'https://www.hp.com', 'active');

-- Insert sample data

-- Insert admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `created_at`) VALUES
('admin', 'mohd@gmail.com', '$2y$10$Ue0EFdpEPJgeYRlwxo2lHeJkxAHMjAdG6BqtV8FQf6/BTJPSiWAie', 'Admin', 'User', 'admin', NOW());

-- Insert sample categories
INSERT INTO `categories` (`name`, `slug`, `description`, `image`, `status`) VALUES
('Electronics', 'electronics', 'Electronic devices and gadgets', 'assets/images/categories/electronics.jpg', 'active'),
('Clothing', 'clothing', 'Clothing and apparel', 'assets/images/categories/clothing.jpg', 'active'),
('Home & Kitchen', 'home-kitchen', 'Home and kitchen products', 'assets/images/categories/home-kitchen.jpg', 'active'),
('Books', 'books', 'Books and literature', 'assets/images/categories/books.jpg', 'active'),
('Sports & Outdoors', 'sports-outdoors', 'Sports equipment and outdoor gear', 'assets/images/categories/sports.jpg', 'active');

-- Insert sample products
INSERT INTO `products` (`name`, `slug`, `description`, `short_description`, `price`, `regular_price`, `sale_price`, `stock`, `sku`, `image`, `category_id`, `status`, `is_featured`, `is_deal`, `is_new`, `created_at`) VALUES
('Smartphone X', 'smartphone-x', 'High-end smartphone with the latest features and specs.', 'Latest smartphone with advanced features', 899.99, 999.99, 899.99, 50, 'PHONE-X', 'assets/images/products/smartphone.jpg', 1, 'active', 1, 1, 1, NOW()),
('Laptop Pro', 'laptop-pro', 'Professional laptop for work and entertainment.', 'Powerful laptop for professionals', 1299.99, 1499.99, NULL, 25, 'LAPTOP-PRO', 'assets/images/products/laptop.jpg', 1, 'active', 1, 0, 1, NOW()),
('Men\'s T-Shirt', 'mens-tshirt', 'Comfortable cotton t-shirt for men.', 'Comfortable cotton t-shirt', 24.99, 29.99, 24.99, 100, 'TSHIRT-M', 'assets/images/products/tshirt.jpg', 2, 'active', 0, 1, 0, NOW()),
('Wireless Headphones', 'wireless-headphones', 'Premium wireless headphones with noise cancellation.', 'Noise-cancelling wireless headphones', 149.99, 199.99, 149.99, 30, 'AUDIO-WH', 'assets/images/products/headphones.jpg', 1, 'active', 1, 1, 0, NOW()),
('Coffee Maker', 'coffee-maker', 'Automatic coffee maker for your morning brew.', 'Automatic coffee maker', 79.99, 99.99, NULL, 40, 'HOME-CM', 'assets/images/products/coffee-maker.jpg', 3, 'active', 0, 0, 1, NOW()),
('Yoga Mat', 'yoga-mat', 'Non-slip yoga mat for your exercise routine.', 'High-quality non-slip yoga mat', 29.99, 39.99, 29.99, 60, 'SPORT-YM', 'assets/images/products/yoga-mat.jpg', 5, 'active', 0, 1, 0, NOW()),
('Novel: The Adventure', 'novel-the-adventure', 'Best-selling novel about an epic adventure.', 'Best-selling adventure novel', 14.99, 19.99, 14.99, 75, 'BOOK-ADV', 'assets/images/products/book.jpg', 4, 'active', 1, 0, 0, NOW()),
('Smart Watch', 'smart-watch', 'Smart watch with health tracking features.', 'Health tracking smart watch', 199.99, 249.99, 199.99, 35, 'WEAR-SW', 'assets/images/products/smartwatch.jpg', 1, 'active', 1, 1, 1, NOW());

-- Insert sample reviews
INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(1, 1, 5, 'Great smartphone! Excellent camera and battery life.', 'approved', NOW()),
(2, 1, 4, 'Good laptop for the price. Fast and reliable.', 'approved', NOW()),
(4, 1, 5, 'Excellent sound quality and noise cancellation works very well.', 'approved', NOW()),
(8, 1, 4, 'Nice watch with good features. Battery life could be better.', 'approved', NOW());

-- Update sample products with brand IDs
UPDATE `products` SET `brand_id` = 1 WHERE `name` LIKE '%Smartphone%' OR `name` LIKE '%Smart Watch%';
UPDATE `products` SET `brand_id` = 2 WHERE `name` LIKE '%Laptop%';
UPDATE `products` SET `brand_id` = 3 WHERE `name` LIKE '%T-Shirt%';
UPDATE `products` SET `brand_id` = 4 WHERE `name` LIKE '%Headphones%';
UPDATE `products` SET `brand_id` = 5 WHERE `name` LIKE '%Coffee Maker%';
UPDATE `products` SET `brand_id` = 6 WHERE `name` LIKE '%Yoga Mat%';
UPDATE `products` SET `brand_id` = 7 WHERE `name` LIKE '%Novel%';
UPDATE `products` SET `brand_id` = 8 WHERE `name` LIKE '%Wireless%';