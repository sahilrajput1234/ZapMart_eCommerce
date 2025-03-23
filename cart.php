<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart items with details
$cartItems = [];
$cartTotal = 0;
$cartCount = 0;

if (!empty($_SESSION['cart'])) {
    $cartItems = getCartItems($conn, $_SESSION['cart']);
    $cartTotal = calculateCartTotal($conn, $_SESSION['cart']);
    $cartCount = array_sum($_SESSION['cart']);
}

// Check for minimum order
$minimumOrder = 50; // Minimum order amount for free shipping in rupees (₹)
$shipping = $cartTotal >= $minimumOrder ? 0 : 10;

// Calculate tax (assuming 8% tax rate)
$taxRate = 0.08;
$tax = $cartTotal * $taxRate;

// Calculate grand total
$grandTotal = $cartTotal + $shipping + $tax;

// Process cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Update item quantity
        if ($action === 'update' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity > 0) {
                $_SESSION['cart'][$productId] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
            
            // Redirect to avoid form resubmission
            header('Location: cart.php');
            exit;
        }
        
        // Remove item from cart
        if ($action === 'remove' && isset($_POST['product_id'])) {
            $productId = (int)$_POST['product_id'];
            unset($_SESSION['cart'][$productId]);
            
            // Redirect to avoid form resubmission
            header('Location: cart.php');
            exit;
        }
        
        // Clear entire cart
        if ($action === 'clear') {
            $_SESSION['cart'] = [];
            
            // Redirect to avoid form resubmission
            header('Location: cart.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji.css/dist/emoji.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --accent-color: #f39c12;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-text: #666;
            --lighter-text: #999;
            --border-color: #e1e1e1;
            --light-bg: #f9f9f9;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --input-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .page-loader.hide {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            width: 70px;
            height: 70px;
            position: relative;
        }

        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        .loader-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30px;
            animation: pulse 1.5s infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.8); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
            100% { transform: translate(-50%, -50%) scale(0.8); }
        }

        /* Cart Container */
        .cart-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        .cart-page-title {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .cart-page-title h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .cart-page-title p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .cart-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        /* Cart Items Section */
        .cart-items {
            flex: 1 1 65%;
            min-width: 300px;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
        }

        .cart-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h2 {
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .cart-header h2 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .cart-header .cart-count {
            background: var(--white);
            color: var(--primary-color);
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .cart-body {
            padding: 20px;
        }

        .cart-items-list {
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
            position: relative;
            opacity: 0;
            transform: translateX(-20px);
            animation: fadeInLeft 0.5s forwards;
        }

        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .cart-item:nth-child(1) { animation-delay: 0.3s; }
        .cart-item:nth-child(2) { animation-delay: 0.4s; }
        .cart-item:nth-child(3) { animation-delay: 0.5s; }
        .cart-item:nth-child(4) { animation-delay: 0.6s; }
        .cart-item:nth-child(5) { animation-delay: 0.7s; }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            overflow: hidden;
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: var(--input-shadow);
            transition: transform 0.3s ease;
        }

        .cart-item:hover .cart-item-image {
            transform: scale(1.05);
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
            display: block;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .cart-item-name:hover {
            color: var(--primary-color);
        }

        .cart-item-category {
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .cart-item-price {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .cart-item-price .original-price {
            color: var(--lighter-text);
            text-decoration: line-through;
            margin-right: 10px;
            font-weight: 400;
        }

        .cart-item-price .sale-price {
            color: var(--danger-color);
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 25px;
            overflow: hidden;
            box-shadow: var(--input-shadow);
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg);
            border: none;
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--border-color);
        }

        .quantity-input {
            width: 40px;
            height: 35px;
            border: none;
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            text-align: center;
            font-size: 0.9rem;
        }

        .quantity-input:focus {
            outline: none;
        }

        .remove-item {
            color: var(--lighter-text);
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .remove-item:hover {
            color: var(--danger-color);
        }

        .cart-empty {
            text-align: center;
            padding: 50px 20px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.3s forwards;
        }

        .cart-empty i {
            font-size: 4rem;
            color: var(--lighter-text);
            margin-bottom: 20px;
        }

        .cart-empty h3 {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .cart-empty p {
            color: var(--light-text);
            margin-bottom: 20px;
        }

        .shop-now-btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .shop-now-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        /* Cart Summary */
        .cart-summary {
            flex: 1 1 30%;
            min-width: 300px;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            align-self: flex-start;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.4s forwards;
        }

        .summary-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
        }

        .summary-header h2 {
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .summary-header h2 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .summary-body {
            padding: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--light-text);
        }

        .summary-value {
            font-weight: 600;
        }

        .summary-total {
            background: var(--light-bg);
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            margin-top: 20px;
        }

        .summary-total-label {
            font-weight: 600;
            color: var(--text-color);
        }

        .summary-total-value {
            font-weight: bold;
            color: var(--primary-color);
        }

        .free-shipping-alert {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
            padding: 10px 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .free-shipping-alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .minimum-order-alert {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--accent-color);
            padding: 10px 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .minimum-order-alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .free-shipping-progress {
            margin: 15px 0;
            border-radius: 5px;
            overflow: hidden;
            background: var(--border-color);
            height: 6px;
        }

        .free-shipping-bar {
            height: 100%;
            background: var(--secondary-color);
            transition: width 1s ease;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .checkout-btn:active {
            transform: translateY(0);
        }

        .checkout-btn:disabled {
            background: var(--lighter-text);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--light-text);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .continue-shopping i {
            margin-right: 5px;
        }

        .continue-shopping:hover {
            color: var(--primary-color);
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .apply-coupon {
            display: flex;
            flex: 1;
            max-width: 350px;
        }

        .coupon-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: 10px 0 0 10px;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .coupon-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .apply-coupon-btn {
            padding: 12px 20px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 0 10px 10px 0;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .apply-coupon-btn:hover {
            background: var(--primary-dark);
        }

        .clear-cart-btn {
            padding: 12px 20px;
            background: var(--light-bg);
            color: var(--light-text);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .clear-cart-btn i {
            margin-right: 8px;
        }

        .clear-cart-btn:hover {
            background: var(--border-color);
            color: var(--text-color);
        }

        /* Animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cart-item-enter {
            opacity: 0;
            transform: translateX(-20px);
        }

        .cart-item-enter-active {
            opacity: 1;
            transform: translateX(0);
            transition: all 0.5s ease;
        }

        .cart-item-exit {
            opacity: 1;
        }

        .cart-item-exit-active {
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.5s ease;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--white);
            border-radius: 10px;
            padding: 15px 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            z-index: 1000;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification.success {
            border-left: 4px solid var(--secondary-color);
        }

        .notification.error {
            border-left: 4px solid var(--danger-color);
        }

        .notification-content {
            flex: 1;
            margin-right: 15px;
        }

        .notification-content i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .notification-content i.fa-check-circle {
            color: var(--secondary-color);
        }

        .notification-content i.fa-exclamation-circle {
            color: var(--danger-color);
        }

        .notification-close {
            background: none;
            border: none;
            color: var(--lighter-text);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .notification-close:hover {
            color: var(--text-color);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
            }

            .cart-item-image {
                width: 100%;
                height: 200px;
                margin-right: 0;
                margin-bottom: 15px;
            }

            .cart-actions {
                flex-direction: column;
                gap: 15px;
            }

            .apply-coupon {
                width: 100%;
                max-width: 100%;
            }

            .clear-cart-btn {
                width: 100%;
                justify-content: center;
            }

            .cart-item-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-icon">🛒</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="cart-container">
        <div class="cart-page-title">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>
        
        <div class="cart-wrapper">
            <div class="cart-items">
                <div class="cart-header">
                    <h2><i class="fas fa-shopping-basket"></i> Your Cart</h2>
                    <span class="cart-count"><?php echo $cartCount; ?> items</span>
                </div>
                
                <div class="cart-body">
                    <?php if (!empty($cartItems)): ?>
                        <div class="cart-items-list">
                            <?php $itemDelay = 0.3; foreach ($cartItems as $item): $itemDelay += 0.1; ?>
                                <div class="cart-item" style="animation-delay: <?php echo $itemDelay; ?>s;">
                                    <div class="cart-item-image">
                                        <img src="<?php echo !empty($item['image']) ? $item['image'] : 'assets/images/products/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="cart-item-details">
                                        <a href="product.php?id=<?php echo $item['id']; ?>" class="cart-item-name">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                        <div class="cart-item-category">
                                            <?php echo !empty($item['category_name']) ? htmlspecialchars($item['category_name']) : 'Uncategorized'; ?>
                                        </div>
                                        <div class="cart-item-price">
                                            <?php if ($item['sale_price']): ?>
                                                <span class="original-price">₹<?php echo number_format($item['regular_price'], 2); ?></span>
                                                <span class="sale-price">₹<?php echo number_format($item['sale_price'], 2); ?></span>
                                            <?php else: ?>
                                                <span>₹<?php echo number_format($item['price'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cart-item-actions">
                                            <form method="post" action="cart.php" class="update-quantity-form">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                <div class="quantity-control">
                                                    <button type="button" class="quantity-btn decrease">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" name="quantity" value="<?php echo $_SESSION['cart'][$item['id']]; ?>" min="1" max="99" class="quantity-input">
                                                    <button type="button" class="quantity-btn increase">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            <form method="post" action="cart.php" class="remove-item-form">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="remove-item">
                                                    <i class="fas fa-trash-alt"></i> Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="cart-actions">
                            <div class="apply-coupon">
                                <input type="text" placeholder="Coupon Code" class="coupon-input">
                                <button class="apply-coupon-btn">Apply</button>
                            </div>
                            
                            <form method="post" action="cart.php">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="clear-cart-btn">
                                    <i class="fas fa-trash"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="cart-empty">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Looks like you haven't added any products to your cart yet.</p>
                            <a href="products.php" class="shop-now-btn">Shop Now</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="cart-summary">
                <div class="summary-header">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                </div>
                
                <div class="summary-body">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">₹<?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Shipping</span>
                        <span class="summary-value">
                            <?php if ($shipping > 0): ?>
                                ₹<?php echo number_format($shipping, 2); ?>
                            <?php else: ?>
                                <span style="color: var(--secondary-color);">Free</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Tax (8%)</span>
                        <span class="summary-value">₹<?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <?php if ($cartTotal > 0 && $cartTotal < $minimumOrder): ?>
                        <div class="minimum-order-alert">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Get Free Shipping!</strong>
                                <p>Spend ₹<?php echo number_format($minimumOrder - $cartTotal, 2); ?> more to get free shipping.</p>
                                <div class="free-shipping-progress">
                                    <div class="free-shipping-bar" style="width: <?php echo ($cartTotal / $minimumOrder) * 100; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($cartTotal >= $minimumOrder): ?>
                        <div class="free-shipping-alert">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>You've Got Free Shipping!</strong>
                                <p>Your order qualifies for free shipping.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-total">
                        <span class="summary-total-label">Total</span>
                        <span class="summary-total-value">₹<?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                    
                    <a href="<?php echo !empty($cartItems) ? 'checkout.php' : 'javascript:void(0)'; ?>" 
                       class="checkout-btn" 
                       <?php echo empty($cartItems) ? 'disabled' : ''; ?>>
                        Proceed to Checkout
                    </a>
                    
                    <a href="products.php" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page Loader
            const pageLoader = document.querySelector('.page-loader');
            window.addEventListener('load', function() {
                setTimeout(function() {
                    pageLoader.classList.add('hide');
                }, 800);
            });
            
            // Quantity Controls
            const quantityControls = document.querySelectorAll('.quantity-control');
            
            quantityControls.forEach(control => {
                const decreaseBtn = control.querySelector('.decrease');
                const increaseBtn = control.querySelector('.increase');
                const input = control.querySelector('.quantity-input');
                const form = control.closest('.update-quantity-form');
                
                decreaseBtn.addEventListener('click', () => {
                    let value = parseInt(input.value);
                    if (value > 1) {
                        input.value = value - 1;
                        form.submit();
                    }
                });
                
                increaseBtn.addEventListener('click', () => {
                    let value = parseInt(input.value);
                    if (value < 99) {
                        input.value = value + 1;
                        form.submit();
                    }
                });
                
                input.addEventListener('change', () => {
                    let value = parseInt(input.value);
                    if (value < 1) {
                        input.value = 1;
                    } else if (value > 99) {
                        input.value = 99;
                    }
                    form.submit();
                });
            });
            
            // Show notification function
            window.showNotification = function(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="notification-close"><i class="fas fa-times"></i></button>
                `;
                
                document.body.appendChild(notification);
                
                // Show notification
                setTimeout(() => {
                    notification.classList.add('show');
                }, 10);
                
                // Close notification
                const close = notification.querySelector('.notification-close');
                close.addEventListener('click', () => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                });
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        notification.classList.remove('show');
                        setTimeout(() => {
                            if (document.body.contains(notification)) {
                                notification.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            };
            
            // Apply coupon functionality
            const applyBtn = document.querySelector('.apply-coupon-btn');
            if (applyBtn) {
                applyBtn.addEventListener('click', function() {
                    const couponInput = document.querySelector('.coupon-input');
                    if (couponInput.value.trim()) {
                        showNotification('Coupon applied successfully!', 'success');
                    } else {
                        showNotification('Please enter a coupon code', 'error');
                    }
                });
            }
            
            // Animation on scroll
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.cart-item');
                
                elements.forEach(element => {
                    const position = element.getBoundingClientRect();
                    
                    // Check if element is in viewport
                    if (position.top < window.innerHeight && position.bottom >= 0) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateX(0)';
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            
            // Initialize
            animateOnScroll();
        });
    </script>
</body>
</html> 