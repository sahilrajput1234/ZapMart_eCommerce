<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit;
}

// Get subcategories
$subcategories = getSubcategories($conn, $category_id);

// Get products in this category
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, b.name as brand_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.category_id = ? AND p.status = 'active'
    ORDER BY p.created_at DESC
");
$stmt->execute([$category_id]);
$products = $stmt->fetchAll();

// Get category breadcrumb
$breadcrumb = [];
$current = $category;
while ($current) {
    array_unshift($breadcrumb, $current);
    if ($current['parent_id']) {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$current['parent_id']]);
        $current = $stmt->fetch();
    } else {
        $current = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
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
            width: 100px;
            height: 100px;
            position: relative;
            margin-bottom: 20px;
        }

        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1.5s infinite linear;
        }

        .loader-icons {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            gap: 8px;
        }

        .category-icon {
            font-size: 1.8rem;
            opacity: 0;
            transform: scale(0.5);
            animation: popIn 3s infinite;
        }

        .category-icon:nth-child(1) { animation-delay: 0s; }
        .category-icon:nth-child(2) { animation-delay: 0.5s; }
        .category-icon:nth-child(3) { animation-delay: 1s; }
        .category-icon:nth-child(4) { animation-delay: 1.5s; }

        .loader-text {
            color: var(--text-color);
            font-size: 1.1rem;
            margin-top: 20px;
            opacity: 0;
            animation: fadeInOut 2s infinite;
        }

        .loader-subtext {
            color: var(--light-text);
            font-size: 0.9rem;
            margin-top: 8px;
            opacity: 0;
            animation: fadeInOut 2s infinite;
            animation-delay: 0.5s;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes popIn {
            0%, 45% { 
                transform: scale(0.5);
                opacity: 0;
            }
            50% { 
                transform: scale(1.2);
                opacity: 1;
            }
            55%, 100% { 
                transform: scale(1);
                opacity: 0;
            }
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        /* Category Page Styles */
        .category-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .category-header {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .category-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .category-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Category Page Specific Styles */
        .category-hero {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }

        .category-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/images/patterns/category-pattern.svg') repeat;
            opacity: 0.1;
            animation: patternMove 20s linear infinite;
        }

        @keyframes patternMove {
            0% { background-position: 0 0; }
            100% { background-position: 100% 100%; }
        }

        .category-image {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .category-image:hover img {
            transform: scale(1.1);
        }

        .breadcrumb {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item:hover {
            color: #3498db;
        }

        .breadcrumb-item:not(:last-child)::after {
            content: '/';
            margin-left: 10px;
            color: #999;
        }

        .subcategories {
            padding: 60px 0;
            background: #fff;
        }

        .subcategories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }

        .subcategory-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .subcategory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .subcategory-image {
            height: 150px;
            overflow: hidden;
        }

        .subcategory-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .subcategory-card:hover .subcategory-image img {
            transform: scale(1.1);
        }

        .subcategory-content {
            padding: 20px;
            text-align: center;
        }

        .subcategory-content h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .subcategory-content p {
            color: #666;
            font-size: 0.9rem;
        }

        .products-section {
            padding: 60px 0;
            background: #f8f9fa;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }

        .product-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .product-image {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
        }

        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 1;
        }

        .product-content {
            padding: 20px;
        }

        .product-brand {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .current-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .regular-price {
            font-size: 1rem;
            color: #999;
            text-decoration: line-through;
        }

        .sale-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #e74c3c;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .btn-add-cart {
            flex: 1;
            padding: 8px 15px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-add-cart:hover {
            background: #2980b9;
        }

        .btn-wishlist {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: #f8f9fa;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-wishlist:hover {
            background: #e74c3c;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }

        .btn-browse {
            display: inline-block;
            padding: 10px 25px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            transition: background 0.3s ease;
        }

        .btn-browse:hover {
            background: #2980b9;
        }

        @media (max-width: 768px) {
            .category-header h1 {
                font-size: 2rem;
            }

            .category-image {
                width: 150px;
                height: 150px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
                <div class="loader-icons">
                    <span class="category-icon">🛍️</span>
                    <span class="category-icon">👕</span>
                    <span class="category-icon">👜</span>
                    <span class="category-icon">👟</span>
                </div>
            </div>
            <div class="loader-text">Loading <?php echo htmlspecialchars($category['name']); ?></div>
            <div class="loader-subtext">Discovering amazing products for you...</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="category-container">
        <div class="category-header">
            <h1>
                <span><?php echo htmlspecialchars($category['name']); ?></span>
                <span class="category-emoji">
                    <?php
                    // Add appropriate emoji based on category name
                    $categoryEmojis = [
                        'Electronics' => '📱',
                        'Fashion' => '👕',
                        'Home' => '🏠',
                        'Books' => '📚',
                        'Sports' => '⚽',
                        'Beauty' => '💄',
                        'Toys' => '🎮',
                        'Food' => '🍔'
                    ];
                    echo $categoryEmojis[$category['name']] ?? '🛍️';
                    ?>
                </span>
            </h1>
            <p><?php echo htmlspecialchars($category['description'] ?? 'Explore our amazing collection of products'); ?></p>
        </div>

        <!-- Breadcrumb -->
        <div class="breadcrumb" data-aos="fade-up" data-aos-delay="100">
            <a href="index.php" class="breadcrumb-item">Home</a>
            <?php foreach ($breadcrumb as $item): ?>
                <a href="category.php?id=<?php echo $item['id']; ?>" class="breadcrumb-item">
                    <?php echo htmlspecialchars($item['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($subcategories)): ?>
        <!-- Subcategories Section -->
        <section class="subcategories">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2>Subcategories</h2>
                    <p>Explore our collection of <?php echo htmlspecialchars($category['name']); ?> products</p>
                </div>
                <div class="subcategories-grid">
                    <?php foreach ($subcategories as $subcategory): ?>
                        <a href="category.php?id=<?php echo $subcategory['id']; ?>" class="subcategory-card" data-aos="fade-up">
                            <div class="subcategory-image">
                                <img src="<?php echo !empty($subcategory['image']) ? $subcategory['image'] : 'assets/images/categories/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($subcategory['name']); ?>">
                            </div>
                            <div class="subcategory-content">
                                <h3><?php echo htmlspecialchars($subcategory['name']); ?></h3>
                                <p><?php echo htmlspecialchars($subcategory['description']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Products Section -->
        <section class="products-section">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2>All Products</h2>
                    <p>Browse our collection of <?php echo htmlspecialchars($category['name']); ?> products</p>
                </div>

                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" data-aos="fade-up">
                                <div class="product-image">
                                    <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/products/placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php if ($product['is_new']): ?>
                                        <span class="product-badge">New</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-content">
                                    <div class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></div>
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <div class="product-price">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="regular-price">₹<?php echo number_format($product['regular_price'], 2); ?></span>
                                            <span class="sale-price">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <button class="btn-add-cart" onclick="quickAddToCart(<?php echo $product['id']; ?>)">
                                            Add to Cart
                                        </button>
                                        <button class="btn-wishlist">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" data-aos="fade-up">
                        <i class="fas fa-box-open"></i>
                        <h3>No Products Found</h3>
                        <p>We couldn't find any products in this category.</p>
                        <a href="index.php" class="btn-browse">Browse All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page Loader
            const pageLoader = document.querySelector('.page-loader');
            window.addEventListener('load', function() {
                setTimeout(function() {
                    pageLoader.classList.add('hide');
                }, 1500); // Slightly longer delay to show the animation
            });

            // Initialize AOS
            AOS.init({
                duration: 800,
                once: true
            });

            // Quick add to cart functionality
            function quickAddToCart(productId) {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', 1);
                
                fetch('ajax/cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Product added to cart!', 'success');
                        updateCartCount(data.cartCount);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
            }

            // Notification function
            function showNotification(message, type = 'success') {
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
            }

            // Update cart count
            function updateCartCount(count) {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = count;
                    cartCount.classList.toggle('hide', count === 0);
                }
            }
        });
    </script>
</body>
</html>