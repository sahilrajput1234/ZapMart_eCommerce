<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get cart items
$cart_items = [];
$total = 0;
try {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $cart_items[] = $product;
            $total += $product['subtotal'];
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching cart items: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->beginTransaction();

        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, total_amount, shipping_address, shipping_city,
                shipping_postal_code, payment_method, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $total,
            $_POST['address'],
            $_POST['city'],
            $_POST['postal_code'],
            $_POST['payment_method']
        ]);
        $order_id = $conn->lastInsertId();

        // Create order items
        $stmt = $conn->prepare("
            INSERT INTO order_items (
                order_id, product_id, quantity, price
            ) VALUES (?, ?, ?, ?)
        ");
        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        // Commit transaction
        $conn->commit();

        // Clear cart
        unset($_SESSION['cart']);

        // Redirect to success page
        $_SESSION['success_msg'] = "Order placed successfully! 🎉";
        header('Location: order-confirmation.php?id=' . $order_id);
        exit;

    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error creating order: " . $e->getMessage());
        $_SESSION['error_msg'] = "Error processing your order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366F1;
            --primary-dark: #4F46E5;
            --secondary-color: #10B981;
            --text-color: #1F2937;
            --light-text: #6B7280;
            --border-color: #E5E7EB;
            --light-bg: #F3F4F6;
            --white: #ffffff;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        @media (max-width: 1024px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }

        .checkout-form {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow);
        }

        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-header h1 {
            font-size: 1.8rem;
            color: var(--text-color);
            margin: 0;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 1.2rem;
            color: var(--text-color);
            margin: 0;
        }

        .section-emoji {
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* Shipping Methods */
        .shipping-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .shipping-method {
            position: relative;
            padding: 20px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .shipping-method:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .shipping-method.selected {
            border-color: var(--primary-color);
            background: rgba(99, 102, 241, 0.05);
        }

        .shipping-method input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .shipping-method-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .shipping-method-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .shipping-method-price {
            color: var(--primary-color);
            font-weight: 600;
        }

        .shipping-method-description {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .shipping-method-eta {
            font-size: 0.9rem;
            color: var(--text-color);
        }

        /* Order Summary */
        .order-summary {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 20px;
        }

        .summary-header {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-header h2 {
            font-size: 1.2rem;
            color: var(--text-color);
            margin: 0;
        }

        .cart-items {
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .item-price {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .item-quantity {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .summary-totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: var(--light-text);
        }

        .summary-line.total {
            color: var(--text-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }

        .btn-checkout {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        /* Progress Steps */
        .checkout-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            padding: 0 40px;
        }

        .progress-line {
            position: absolute;
            top: 25px;
            left: 40px;
            right: 40px;
            height: 2px;
            background: var(--border-color);
            z-index: 1;
        }

        .progress-line-active {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .progress-step {
            position: relative;
            z-index: 2;
            background: var(--white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--light-text);
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .progress-step.active {
            color: var(--white);
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .progress-step.completed {
            color: var(--white);
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .progress-label {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 8px;
            white-space: nowrap;
            font-size: 0.9rem;
            color: var(--light-text);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .checkout-form {
            animation: fadeIn 0.6s ease;
        }

        .order-summary {
            animation: fadeIn 0.6s ease 0.2s both;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-container">
        <div class="checkout-form">
            <div class="form-header">
                <h1>Checkout</h1>
            </div>

            <div class="checkout-progress">
                <div class="progress-line">
                    <div class="progress-line-active" style="width: 33%;"></div>
                </div>
                <div class="progress-step completed">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="progress-label">Cart</span>
                </div>
                <div class="progress-step active">
                    <i class="fas fa-shipping-fast"></i>
                    <span class="progress-label">Shipping</span>
                </div>
                <div class="progress-step">
                    <i class="fas fa-credit-card"></i>
                    <span class="progress-label">Payment</span>
                </div>
            </div>

            <form method="POST" id="checkoutForm">
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-emoji">📦</span>
                        <h2>Shipping Information</h2>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Postal Code</label>
                        <input type="text" class="form-control" name="postal_code" required>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <span class="section-emoji">🚚</span>
                        <h2>Shipping Method</h2>
                    </div>

                    <div class="shipping-methods">
                        <label class="shipping-method">
                            <input type="radio" name="shipping_method" value="standard" required checked>
                            <div class="shipping-method-header">
                                <span class="shipping-method-name">Standard Shipping</span>
                                <span class="shipping-method-price">₹10.00</span>
                            </div>
                            <div class="shipping-method-description">
                                Regular delivery service
                            </div>
                            <div class="shipping-method-eta">
                                <i class="far fa-clock"></i> 3-5 business days
                            </div>
                        </label>

                        <label class="shipping-method">
                            <input type="radio" name="shipping_method" value="express" required>
                            <div class="shipping-method-header">
                                <span class="shipping-method-name">Express Shipping</span>
                                <span class="shipping-method-price">₹15.00</span>
                            </div>
                            <div class="shipping-method-description">
                                Faster delivery service
                            </div>
                            <div class="shipping-method-eta">
                                <i class="far fa-clock"></i> 2-3 business days
                            </div>
                        </label>

                        <label class="shipping-method">
                            <input type="radio" name="shipping_method" value="next_day" required>
                            <div class="shipping-method-header">
                                <span class="shipping-method-name">Next Day Delivery</span>
                                <span class="shipping-method-price">₹25.00</span>
                            </div>
                            <div class="shipping-method-description">
                                Guaranteed next day delivery
                            </div>
                            <div class="shipping-method-eta">
                                <i class="far fa-clock"></i> Next business day
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-checkout">
                    Continue to Payment
                </button>
            </form>
        </div>

        <div class="order-summary">
            <div class="summary-header">
                <h2>Order Summary</h2>
            </div>

            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                            <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-totals">
                <div class="summary-line">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-line">
                    <span>Shipping</span>
                    <span id="shippingCost">₹10.00</span>
                </div>
                <div class="summary-line total">
                    <span>Total</span>
                    <span id="totalAmount">₹<?php echo number_format($total + 10, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Shipping Method Selection
        const shippingMethods = document.querySelectorAll('.shipping-method');
        const shippingCostElement = document.getElementById('shippingCost');
        const totalAmountElement = document.getElementById('totalAmount');
        const subtotal = <?php echo $total; ?>;

        shippingMethods.forEach(method => {
            const radio = method.querySelector('input[type="radio"]');
            
            method.addEventListener('click', () => {
                // Update selected state
                shippingMethods.forEach(m => m.classList.remove('selected'));
                method.classList.add('selected');
                radio.checked = true;

                // Update shipping cost and total
                let shippingCost = 10;
                switch (radio.value) {
                    case 'express':
                        shippingCost = 15;
                        break;
                    case 'next_day':
                        shippingCost = 25;
                        break;
                }

                shippingCostElement.textContent = `₹${shippingCost.toFixed(2)}`;
                totalAmountElement.textContent = `₹${(subtotal + shippingCost).toFixed(2)}`;
            });
        });

        // Form Validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });

        // Progress Steps Animation
        const progressLine = document.querySelector('.progress-line-active');
        progressLine.style.width = '66%';
    </script>
</body>
</html> 