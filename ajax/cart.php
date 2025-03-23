<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Set default response
$response = [
    'success' => false,
    'message' => 'Invalid request',
    'cartCount' => count($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0,
    'cartTotal' => calculateCartTotal($conn, $_SESSION['cart'])
];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add to cart
    if (isset($_POST['product_id'], $_POST['quantity'])) {
        $productId = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Validate product and quantity
        if ($productId > 0 && $quantity > 0) {
            // Check if product exists and has enough stock
            $stmt = $conn->prepare("SELECT id, stock, name, price, sale_price FROM products WHERE id = ? AND stock >= ?");
            $stmt->execute([$productId, $quantity]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Add to cart or update quantity
                if (isset($_SESSION['cart'][$productId])) {
                    // Update quantity
                    $_SESSION['cart'][$productId] += $quantity;
                } else {
                    // Add new item
                    $_SESSION['cart'][$productId] = $quantity;
                }
                
                // Check if exceeding stock
                if ($_SESSION['cart'][$productId] > $product['stock']) {
                    $_SESSION['cart'][$productId] = $product['stock'];
                }
                
                // Update response
                $response['success'] = true;
                $response['message'] = 'Product added to cart';
                $response['product'] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['sale_price'] ? $product['sale_price'] : $product['price'],
                    'quantity' => $_SESSION['cart'][$productId]
                ];
                $response['cartCount'] = array_sum($_SESSION['cart']);
                $response['cartTotal'] = calculateCartTotal($conn, $_SESSION['cart']);
            } else {
                $response['message'] = 'Product not available or not enough stock';
            }
        } else {
            $response['message'] = 'Invalid product or quantity';
        }
    }
    // Remove from cart
    elseif (isset($_POST['remove_id'])) {
        $productId = (int)$_POST['remove_id'];
        
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            
            $response['success'] = true;
            $response['message'] = 'Product removed from cart';
            $response['cartCount'] = array_sum($_SESSION['cart']);
            $response['cartTotal'] = calculateCartTotal($conn, $_SESSION['cart']);
        } else {
            $response['message'] = 'Product not in cart';
        }
    }
    // Update quantity
    elseif (isset($_POST['update_id'], $_POST['update_quantity'])) {
        $productId = (int)$_POST['update_id'];
        $quantity = (int)$_POST['update_quantity'];
        
        if (isset($_SESSION['cart'][$productId]) && $quantity > 0) {
            // Check stock
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if ($product && $quantity <= $product['stock']) {
                $_SESSION['cart'][$productId] = $quantity;
                
                $response['success'] = true;
                $response['message'] = 'Cart updated';
                $response['cartCount'] = array_sum($_SESSION['cart']);
                $response['cartTotal'] = calculateCartTotal($conn, $_SESSION['cart']);
            } else {
                $response['message'] = 'Not enough stock available';
            }
        } elseif (isset($_SESSION['cart'][$productId]) && $quantity <= 0) {
            // Remove item if quantity is 0 or less
            unset($_SESSION['cart'][$productId]);
            
            $response['success'] = true;
            $response['message'] = 'Product removed from cart';
            $response['cartCount'] = array_sum($_SESSION['cart']);
            $response['cartTotal'] = calculateCartTotal($conn, $_SESSION['cart']);
        } else {
            $response['message'] = 'Product not in cart or invalid quantity';
        }
    }
    // Clear cart
    elseif (isset($_POST['clear_cart']) && $_POST['clear_cart'] === 'true') {
        $_SESSION['cart'] = [];
        
        $response['success'] = true;
        $response['message'] = 'Cart cleared';
        $response['cartCount'] = 0;
        $response['cartTotal'] = 0;
    }
}
// Handle GET request
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get cart content
    if (isset($_GET['get_cart']) && $_GET['get_cart'] === 'true') {
        $cartItems = getCartItems($conn, $_SESSION['cart']);
        
        $response['success'] = true;
        $response['message'] = 'Cart retrieved';
        $response['items'] = $cartItems;
        $response['cartCount'] = array_sum($_SESSION['cart']);
        $response['cartTotal'] = calculateCartTotal($conn, $_SESSION['cart']);
    }
}

// Set content type to JSON
header('Content-Type: application/json');

// Return response
echo json_encode($response); 