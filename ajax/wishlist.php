<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Please login to manage your wishlist'
    ]);
    exit;
}

// Get user ID
$userId = $_SESSION['user_id'];

// Set default response
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON data for AJAX requests
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $content = file_get_contents("php://input");
        $data = json_decode($content, true);
        
        // Move JSON data to $_POST for consistent processing
        if ($data && is_array($data)) {
            foreach ($data as $key => $value) {
                $_POST[$key] = $value;
            }
        }
    }
    
    // Add to wishlist
    if (isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Check if already in wishlist
            $stmt = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existingItem = $stmt->fetch();
            
            if (!$existingItem) {
                // Add to wishlist
                $stmt = $conn->prepare("INSERT INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, NOW())");
                
                if ($stmt->execute([$userId, $productId])) {
                    $response['success'] = true;
                    $response['message'] = 'Product added to wishlist';
                    $response['wishlistId'] = $conn->lastInsertId();
                } else {
                    $response['message'] = 'Failed to add product to wishlist';
                }
            } else {
                $response['success'] = true;
                $response['message'] = 'Product already in wishlist';
                $response['wishlistId'] = $existingItem['id'];
            }
        } else {
            $response['message'] = 'Product not found';
        }
    }
    // Remove from wishlist
    elseif (isset($_POST['remove_id'])) {
        $productId = (int)$_POST['remove_id'];
        
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        
        if ($stmt->execute([$userId, $productId])) {
            $response['success'] = true;
            $response['message'] = 'Product removed from wishlist';
        } else {
            $response['message'] = 'Failed to remove product from wishlist';
        }
    }
    // Clear wishlist
    elseif (isset($_POST['clear_wishlist']) && $_POST['clear_wishlist'] === 'true') {
        $stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ?");
        
        if ($stmt->execute([$userId])) {
            $response['success'] = true;
            $response['message'] = 'Wishlist cleared';
        } else {
            $response['message'] = 'Failed to clear wishlist';
        }
    }
}
// Handle GET request
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get wishlist items
    if (isset($_GET['get_wishlist']) && $_GET['get_wishlist'] === 'true') {
        $stmt = $conn->prepare("
            SELECT w.id as wishlist_id, w.created_at, p.*, c.name as category_name
            FROM wishlists w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        $wishlistItems = $stmt->fetchAll();
        
        $response['success'] = true;
        $response['message'] = 'Wishlist retrieved';
        $response['items'] = $wishlistItems;
        $response['count'] = count($wishlistItems);
    }
    // Check if product is in wishlist
    elseif (isset($_GET['check_product'])) {
        $productId = (int)$_GET['check_product'];
        
        $stmt = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $item = $stmt->fetch();
        
        $response['success'] = true;
        $response['inWishlist'] = !!$item;
        $response['wishlistId'] = $item ? $item['id'] : null;
    }
}

// Set content type to JSON
header('Content-Type: application/json');

// Return response
echo json_encode($response); 