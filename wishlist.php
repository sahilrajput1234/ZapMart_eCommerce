<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Handle wishlist actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        
        switch ($_POST['action']) {
            case 'remove':
                // Remove item from wishlist
                $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':product_id' => $productId
                ]);
                break;
                
            case 'add_to_cart':
                // Add item to cart and remove from wishlist
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
                
                // Remove from wishlist
                $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':product_id' => $productId
                ]);
                break;
        }
        
        // Redirect to avoid form resubmission
        header('Location: wishlist.php');
        exit;
    }
}

// Get wishlist items with product details
$stmt = $conn->prepare("
    SELECT w.*, p.*, c.name as category_name 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE w.user_id = :user_id 
    ORDER BY w.created_at DESC
");
$stmt->execute([':user_id' => $userId]);
$wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Page Loader Styles */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
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

        .loader-container {
            text-align: center;
            position: relative;
        }

        .loader {
            width: 80px;
            height: 80px;
            position: relative;
            margin-bottom: 20px;
        }

        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        .loader-hearts {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            display: flex;
            gap: 5px;
        }

        .heart {
            animation: float 1.5s infinite ease-in-out;
        }

        .heart:nth-child(1) { animation-delay: 0s; }
        .heart:nth-child(2) { animation-delay: 0.2s; }
        .heart:nth-child(3) { animation-delay: 0.4s; }

        .loader-text {
            color: var(--text-color);
            font-size: 1.1rem;
            margin-top: 15px;
            opacity: 0.8;
            animation: pulse 1.5s infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 0.5; }
        }

        .wishlist-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .wishlist-header {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .wishlist-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .wishlist-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
        }

        .wishlist-item {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            position: relative;
        }

        .wishlist-item:hover {
            transform: translateY(-5px);
        }

        .wishlist-image {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
        }

        .wishlist-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .wishlist-item:hover .wishlist-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 1;
        }

        .badge-sale {
            background: var(--danger-color);
            color: var(--white);
        }

        .wishlist-details {
            padding: 20px;
        }

        .wishlist-category {
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .wishlist-name {
            color: var(--text-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-decoration: none;
            display: block;
            transition: color 0.3s ease;
        }

        .wishlist-name:hover {
            color: var(--primary-color);
        }

        .wishlist-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .current-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .original-price {
            color: var(--lighter-text);
            text-decoration: line-through;
            font-size: 0.9rem;
        }

        .discount-badge {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .wishlist-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
        }

        .add-to-cart {
            padding: 10px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-to-cart:hover {
            background: var(--primary-dark);
        }

        .remove-item {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--white);
            color: var(--light-text);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-item:hover {
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .empty-wishlist {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.3s forwards;
        }

        .empty-wishlist i {
            font-size: 4rem;
            color: var(--lighter-text);
            margin-bottom: 20px;
        }

        .empty-wishlist h2 {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .empty-wishlist p {
            color: var(--light-text);
            margin-bottom: 20px;
        }

        .browse-products-btn {
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

        .browse-products-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .notification-content {
            flex: 1;
            margin-right: 15px;
        }

        .notification-content i {
            margin-right: 10px;
            font-size: 1.2rem;
            color: var(--secondary-color);
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

        @media (max-width: 768px) {
            .wishlist-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader-container">
            <div class="loader">
                <div class="loader-circle"></div>
                <div class="loader-hearts">
                    <span class="heart">❤️</span>
                    <span class="heart">💝</span>
                    <span class="heart">💖</span>
                </div>
            </div>
            <div class="loader-text">Loading your wishlist...</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="wishlist-container">
        <div class="wishlist-header">
            <h1>
                <span>My Wishlist</span>
                <span class="header-icon">💝</span>
            </h1>
            <p>Keep track of all your favorite items in one place</p>
        </div>

        <?php if (!empty($wishlistItems)): ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-item">
                        <?php if ($item['sale_price']): ?>
                            <div class="product-badge badge-sale">Sale</div>
                        <?php endif; ?>

                        <div class="wishlist-image">
                            <img src="<?php echo !empty($item['image']) ? $item['image'] : 'assets/images/products/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>

                        <div class="wishlist-details">
                            <div class="wishlist-category"><?php echo htmlspecialchars($item['category_name']); ?></div>
                            <a href="product.php?id=<?php echo $item['id']; ?>" class="wishlist-name">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>

                            <div class="wishlist-price">
                                <?php if ($item['sale_price']): ?>
                                    <span class="current-price">₹<?php echo number_format($item['sale_price'], 2); ?></span>
                                    <span class="original-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <?php 
                                        $discount = round((($item['price'] - $item['sale_price']) / $item['price']) * 100);
                                    ?>
                                    <span class="discount-badge"><?php echo $discount; ?>% OFF</span>
                                <?php else: ?>
                                    <span class="current-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="wishlist-actions">
                                <form method="post" action="wishlist.php" class="add-to-cart-form">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="add-to-cart">
                                        <i class="fas fa-shopping-cart"></i>
                                        Add to Cart
                                    </button>
                                </form>
                                
                                <form method="post" action="wishlist.php" class="remove-form">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="remove-item">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <i class="far fa-heart"></i>
                <h2>Your wishlist is empty</h2>
                <p>Add items that you like to your wishlist</p>
                <a href="products.php" class="browse-products-btn">Browse Products</a>
            </div>
        <?php endif; ?>
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

            // Show notification function
            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas fa-check-circle"></i>
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
            }

            // Handle form submissions
            document.querySelectorAll('.add-to-cart-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch('wishlist.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (response.ok) {
                            showNotification('Item added to cart successfully!');
                            // Remove the item from wishlist
                            this.closest('.wishlist-item').remove();
                            // Check if wishlist is empty
                            if (document.querySelectorAll('.wishlist-item').length === 0) {
                                location.reload();
                            }
                        }
                    });
                });
            });

            document.querySelectorAll('.remove-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch('wishlist.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (response.ok) {
                            showNotification('Item removed from wishlist');
                            // Remove the item from the grid
                            this.closest('.wishlist-item').remove();
                            // Check if wishlist is empty
                            if (document.querySelectorAll('.wishlist-item').length === 0) {
                                location.reload();
                            }
                        }
                    });
                });
            });
        });
    </script>
</body>
</html> 