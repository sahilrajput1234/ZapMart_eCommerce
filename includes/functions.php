<?php
/**
 * This file contains all the helper functions for the ecommerce website
 */

/**
 * Sanitize user input
 *
 * @param string $data The data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 *
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get user by ID
 *
 * @param PDO $conn Database connection
 * @param int $userId User ID
 * @return array|false User data array or false if not found
 */
function getUserById($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Get user by email
 *
 * @param PDO $conn Database connection
 * @param string $email User email
 * @return array|false User data array or false if not found
 */
function getUserByEmail($conn, $email) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Login user
 *
 * @param PDO $conn Database connection
 * @param string $email User email
 * @param string $password User password
 * @param bool $remember Whether to remember the user
 * @return bool True if login successful, false otherwise
 */
function loginUser($conn, $email, $password, $remember = false) {
    $user = getUserByEmail($conn, $email);
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Update last login time
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (86400 * 30); // 30 days
            
            $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', $expires)]);
            
            setcookie('remember_token', $token, $expires, '/', '', false, true);
        }
        
        return true;
    }
    
    return false;
}

/**
 * Register a new user
 *
 * @param PDO $conn Database connection
 * @param string $username Username
 * @param string $email Email
 * @param string $password Password
 * @return bool True if registration successful, false otherwise
 */
function registerUser($conn, $username, $email, $password) {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Insert the user
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, role, created_at) 
            VALUES (?, ?, ?, 'customer', NOW())
        ");
        $stmt->execute([$username, $email, $hashedPassword]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Logout user
 *
 * @return void
 */
function logoutUser() {
    // Unset remember me cookie if it exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

/**
 * Get categories
 *
 * @param PDO $conn Database connection
 * @return array Categories array
 */
function getCategories($conn) {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get products
 *
 * @param PDO $conn Database connection
 * @param int $limit Limit number of products
 * @param string $orderBy Order by clause
 * @param string $where Where clause
 * @param array $params Parameters for prepared statement
 * @return array Products array
 */
function getProducts($conn, $limit = 10, $orderBy = 'created_at DESC', $where = '1=1', $params = []) {
    $sql = "
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where
        ORDER BY $orderBy
        LIMIT $limit
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get featured products
 *
 * @param PDO $conn Database connection
 * @param int $limit Limit number of products
 * @return array Featured products array
 */
function getFeaturedProducts($conn, $limit = 8) {
    return getProducts($conn, $limit, 'p.created_at DESC', 'p.is_featured = 1');
}

/**
 * Get latest products
 *
 * @param PDO $conn Database connection
 * @param int $limit Limit number of products
 * @return array Latest products array
 */
function getLatestProducts($conn, $limit = 8) {
    return getProducts($conn, $limit);
}

/**
 * Get product by ID
 *
 * @param PDO $conn Database connection
 * @param int $productId Product ID
 * @return array|false Product data array or false if not found
 */
function getProductById($conn, $productId) {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

/**
 * Get product reviews
 *
 * @param PDO $conn Database connection
 * @param int $productId Product ID
 * @param int $limit Limit number of reviews
 * @return array Reviews array
 */
function getProductReviews($conn, $productId, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT r.*, u.username 
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$productId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Add a product review
 *
 * @param PDO $conn Database connection
 * @param int $userId User ID
 * @param int $productId Product ID
 * @param int $rating Rating (1-5)
 * @param string $comment Review comment
 * @return bool True if review added successfully, false otherwise
 */
function addReview($conn, $userId, $productId, $rating, $comment) {
    try {
        // Insert the review
        $stmt = $conn->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $productId, $rating, $comment]);
        
        // Update product average rating
        updateProductRating($conn, $productId);
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update product average rating
 *
 * @param PDO $conn Database connection
 * @param int $productId Product ID
 * @return void
 */
function updateProductRating($conn, $productId) {
    $stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
        FROM reviews
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);
    $result = $stmt->fetch();
    
    if ($result) {
        $avgRating = round($result['avg_rating'], 1);
        $reviewCount = $result['review_count'];
        
        $stmt = $conn->prepare("
            UPDATE products 
            SET average_rating = ?, reviews_count = ?
            WHERE id = ?
        ");
        $stmt->execute([$avgRating, $reviewCount, $productId]);
    }
}

/**
 * Calculate cart total
 *
 * @param PDO $conn Database connection
 * @param array $cart Cart array (product_id => quantity)
 * @return float Cart total
 */
function calculateCartTotal($conn, $cart) {
    $total = 0;
    
    if (!empty($cart)) {
        $productIds = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $stmt = $conn->prepare("
            SELECT id, price, sale_price
            FROM products
            WHERE id IN ($placeholders)
        ");
        $stmt->execute($productIds);
        $products = $stmt->fetchAll();
        
        foreach ($products as $product) {
            $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
            $total += $price * $cart[$product['id']];
        }
    }
    
    return $total;
}

/**
 * Get cart items with details
 *
 * @param PDO $conn Database connection
 * @param array $cart Cart array (product_id => quantity)
 * @return array Cart items with product details
 */
function getCartItems($conn, $cart) {
    $cartItems = [];
    
    if (!empty($cart) && is_array($cart)) {
        $productIds = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id IN ($placeholders)
        ");
        $stmt->execute($productIds);
        $products = $stmt->fetchAll();
        
        foreach ($products as $product) {
            $productId = $product['id'];
            $quantity = $cart[$productId];
            $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
            
            $cartItems[] = [
                'id' => $productId,
                'name' => $product['name'],
                'image' => $product['image'],
                'price' => $price,
                'regular_price' => $product['regular_price'],
                'quantity' => $quantity,
                'total' => $price * $quantity,
                'category' => $product['category_name']
            ];
        }
    }
    
    return $cartItems;
}

/**
 * Create a new order
 *
 * @param PDO $conn Database connection
 * @param int $userId User ID
 * @param array $orderData Order data
 * @param array $items Order items
 * @return int|false Order ID if successful, false otherwise
 */
function createOrder($conn, $userId, $orderData, $items) {
    try {
        $conn->beginTransaction();
        
        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, total, subtotal, tax, shipping_cost, shipping_method,
                payment_method, status, first_name, last_name, email,
                phone, address, city, state, zip, country,
                notes, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, NOW()
            )
        ");
        
        $stmt->execute([
            $userId,
            $orderData['total'],
            $orderData['subtotal'],
            $orderData['tax'],
            $orderData['shipping_cost'],
            $orderData['shipping_method'],
            $orderData['payment_method'],
            'pending', // Default status
            $orderData['first_name'],
            $orderData['last_name'],
            $orderData['email'],
            $orderData['phone'],
            $orderData['address'],
            $orderData['city'],
            $orderData['state'],
            $orderData['zip'],
            $orderData['country'],
            $orderData['notes'] ?? ''
        ]);
        
        $orderId = $conn->lastInsertId();
        
        // Insert order items
        foreach ($items as $item) {
            $stmt = $conn->prepare("
                INSERT INTO order_items (
                    order_id, product_id, quantity, price, total
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $item['total']
            ]);
            
            // Update product stock and sales count
            $stmt = $conn->prepare("
                UPDATE products
                SET stock = stock - ?, sales_count = sales_count + ?
                WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['quantity'], $item['id']]);
        }
        
        $conn->commit();
        return $orderId;
    } catch (PDOException $e) {
        $conn->rollBack();
        return false;
    }
}

/**
 * Get orders for a user
 *
 * @param PDO $conn Database connection
 * @param int $userId User ID
 * @param int $limit Limit number of orders
 * @return array Orders array
 */
function getUserOrders($conn, $userId, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT * FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get order by ID
 *
 * @param PDO $conn Database connection
 * @param int $orderId Order ID
 * @return array|false Order data array or false if not found
 */
function getOrderById($conn, $orderId) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

/**
 * Get order items
 *
 * @param PDO $conn Database connection
 * @param int $orderId Order ID
 * @return array Order items array
 */
function getOrderItems($conn, $orderId) {
    $stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

/**
 * Generate a random password
 *
 * @param int $length Password length
 * @return string Random password
 */
function generateRandomPassword($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    
    return $randomString;
}

/**
 * Format date for display
 *
 * @param string $dateString Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($dateString, $format = 'M j, Y') {
    $date = new DateTime($dateString);
    return $date->format($format);
}

/**
 * Format price for display
 *
 * @param float $price Price
 * @param string $currencySymbol Currency symbol
 * @return string Formatted price
 */
function formatPrice($price, $currencySymbol = '$') {
    return $currencySymbol . number_format($price, 2);
}

/**
 * Truncate text to a specific length
 *
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append Text to append if truncated
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Generate a pagination array
 *
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param int $range Number of pages to show before and after current page
 * @return array Pagination array
 */
function generatePagination($currentPage, $totalPages, $range = 2) {
    $pages = [];
    
    $startPage = max(1, $currentPage - $range);
    $endPage = min($totalPages, $currentPage + $range);
    
    if ($startPage > 1) {
        $pages[] = 1;
        if ($startPage > 2) {
            $pages[] = '...';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pages[] = $i;
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $pages[] = '...';
        }
        $pages[] = $totalPages;
    }
    
    return $pages;
}

/**
 * Get daily deals
 *
 * @param PDO $conn Database connection
 * @param int $limit Limit number of products
 * @return array Daily deals products array
 */
function getDailyDeals($conn, $limit = 8) {
    return getProducts($conn, $limit, 'p.created_at DESC', 'p.is_deal = 1');
}

/**
 * Get recommended products for a user
 *
 * @param PDO $conn Database connection
 * @param int $userId User ID
 * @param int $limit Limit number of products
 * @return array Recommended products array
 */
function getRecommendedProducts($conn, $userId, $limit = 8) {
    // Get user's order history
    $stmt = $conn->prepare("
        SELECT DISTINCT p.category_id
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$userId]);
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($categories)) {
        // If no order history, return popular products
        return getPopularProducts($conn, $limit);
    }
    
    // Get products from user's preferred categories
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $sql = "
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.category_id IN ($placeholders)
        ORDER BY p.average_rating DESC, p.created_at DESC
        LIMIT $limit
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($categories);
    return $stmt->fetchAll();
}

/**
 * Get popular products
 *
 * @param PDO $conn Database connection
 * @param int $limit Limit number of products
 * @return array Popular products array
 */
function getPopularProducts($conn, $limit = 8) {
    return getProducts($conn, $limit, 'p.sales_count DESC, p.average_rating DESC');
}

/**
 * Get subcategories for a parent category
 *
 * @param PDO $conn Database connection
 * @param int $parentId Parent category ID
 * @return array Subcategories array
 */
function getSubcategories($conn, $parentId) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
}

/**
 * Get all active brands
 * @param PDO $conn Database connection
 * @return array Array of active brands
 */
function getActiveBrands($conn) {
    $stmt = $conn->query("
        SELECT b.*, COUNT(p.id) as product_count 
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id 
        WHERE b.status = 'active' 
        GROUP BY b.id 
        ORDER BY b.name
    ");
    return $stmt->fetchAll();
}

/**
 * Get featured brands (brands with most products)
 * @param PDO $conn Database connection
 * @param int $limit Number of brands to return
 * @return array Array of featured brands
 */
function getFeaturedBrands($conn, $limit = 4) {
    $stmt = $conn->query("
        SELECT b.*, COUNT(p.id) as product_count, AVG(p.average_rating) as average_rating
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id 
        WHERE b.status = 'active' 
        GROUP BY b.id 
        ORDER BY product_count DESC, average_rating DESC
        LIMIT " . (int)$limit
    );
    return $stmt->fetchAll();
}