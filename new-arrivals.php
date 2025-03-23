<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get latest products
$latestProducts = getLatestProducts($conn, 12);

// Get categories for filtering
$categories = getCategories($conn);

// Function to get appropriate product image based on name
function getProductImage($productName) {
    $name = strtolower($productName);
    $possibleImages = [
        'smartphone' => 'smartphone.svg',
        'smart watch' => 'smartwatch.svg',
        'laptop' => 'laptop.svg',
        'headphone' => 'headphones.svg',
        'camera' => 'camera.svg',
        'speaker' => 'speaker.svg',
        'tablet' => 'tablet.svg',
        'monitor' => 'monitor.svg',
        'keyboard' => 'keyboard.svg',
        'mouse' => 'mouse.svg',
        'gaming' => 'gaming-console.svg',
        'console' => 'gaming-console.svg',
        'wireless' => 'wireless-earbuds.svg',
        'earbuds' => 'wireless-earbuds.svg'
    ];
    
    foreach ($possibleImages as $key => $img) {
        if (strpos($name, $key) !== false) {
            return 'assets/images/products/' . $img;
        }
    }
    
    // Default to a random product image if no match
    $defaultImages = array_values($possibleImages);
    return 'assets/images/products/' . $defaultImages[array_rand($defaultImages)];
}

// Function to get brand logo based on brand name
function getBrandLogo($brandName) {
    if (empty($brandName)) return null;
    
    $name = strtolower($brandName);
    $possibleBrands = [
        'apple' => 'apple.svg',
        'samsung' => 'samsung.svg',
        'lg' => 'lg.svg',
        'sony' => 'sony.svg',
        'dell' => 'dell.svg',
        'hp' => 'hp.svg',
        'lenovo' => 'lenovo.svg',
        'asus' => 'asus.svg',
        'logitech' => 'logitech.svg',
        'bose' => 'bose.svg',
        'jbl' => 'jbl.svg',
        'adidas' => 'adidas.svg'
    ];
    
    foreach ($possibleBrands as $key => $logo) {
        if (strpos($name, $key) !== false) {
            return 'assets/images/brands/' . $logo;
        }
    }
    
    return null;
}

// Loop counter for animations
$loop = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Arrivals - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/new-arrivals.css">
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

        /* Page Loader */
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

        .new-arrival-icon {
            font-size: 1.8rem;
            opacity: 0;
            transform: scale(0.5);
            animation: popIn 3s infinite;
        }

        .new-arrival-icon:nth-child(1) { animation-delay: 0s; }
        .new-arrival-icon:nth-child(2) { animation-delay: 0.5s; }
        .new-arrival-icon:nth-child(3) { animation-delay: 1s; }
        .new-arrival-icon:nth-child(4) { animation-delay: 1.5s; }

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

        /* New Arrivals Page Styles */
        .new-arrivals-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .new-arrivals-header {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .new-arrivals-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .new-arrivals-header p {
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

        /* Add your existing new arrivals page styles here */

    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader-container">
            <div class="loader">
                <div class="loader-circle"></div>
                <div class="loader-icons">
                    <span class="new-arrival-icon">✨</span>
                    <span class="new-arrival-icon">🆕</span>
                    <span class="new-arrival-icon">🎁</span>
                    <span class="new-arrival-icon">🌟</span>
                </div>
            </div>
            <div class="loader-text">Loading New Arrivals</div>
            <div class="loader-subtext">Discovering the latest products for you...</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <main class="new-arrivals-page">
        <!-- Hero Section -->
        <section class="hero-section" data-aos="fade-down">
            <div class="container">
                <div class="hero-content">
                    <h1>New Arrivals</h1>
                    <p>Discover our latest collection of products</p>
                </div>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="filters-section" data-aos="fade-up">
            <div class="container">
                <div class="filters-wrapper">
                    <div class="filter-group">
                        <label for="category-filter">Category</label>
                        <select id="category-filter" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="sort-filter">Sort By</label>
                        <select id="sort-filter" class="filter-select">
                            <option value="newest">Newest First</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="price-range">Price Range</label>
                        <div class="price-range">
                            <input type="range" id="price-range" min="0" max="1000" step="10">
                            <div class="price-inputs">
                                <input type="number" id="min-price" placeholder="Min">
                                <span>-</span>
                                <input type="number" id="max-price" placeholder="Max">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Grid -->
        <section class="products-section">
            <div class="container">
                <div class="products-grid">
                    <?php foreach ($latestProducts as $product): 
                        $loop++;
                        // Get appropriate image 
                        $productImage = !empty($product['image']) ? $product['image'] : getProductImage($product['name']);
                        // Get brand logo if available
                        $brandLogo = isset($product['brand_name']) ? getBrandLogo($product['brand_name']) : null;
                    ?>
                        <div class="product-card" data-aos="fade-up" data-aos-delay="<?php echo $loop * 50; ?>">
                            <div class="product-image">
                                <img src="<?php echo $productImage; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php if ($brandLogo): ?>
                                <div class="brand-logo">
                                    <img src="<?php echo $brandLogo; ?>" alt="<?php echo htmlspecialchars($product['brand_name'] ?? ''); ?>">
                                </div>
                                <?php endif; ?>
                                <div class="product-overlay">
                                    <button class="quick-view-btn" onclick="quickView(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="add-to-cart-btn" onclick="quickAddToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <button class="add-to-wishlist-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Electronics'); ?></div>
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price">
                                    <?php if (isset($product['sale_price']) && $product['sale_price']): ?>
                                        <span class="regular-price">₹<?php echo number_format($product['regular_price'], 2); ?></span>
                                        <span class="sale-price">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                        <?php 
                                            $discount = round(($product['regular_price'] - $product['sale_price']) / $product['regular_price'] * 100);
                                        ?>
                                        <span class="discount-badge"><?php echo $discount; ?>% OFF</span>
                                    <?php else: ?>
                                        <span class="current-price">₹<?php echo number_format($product['price'] ?? 99.99, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= ($product['average_rating'] ?? 4) ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="rating-count">(<?php echo $product['reviews_count'] ?? rand(10, 100); ?>)</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Load More Button -->
        <div class="load-more-wrapper" data-aos="fade-up">
            <button class="load-more-btn">
                Load More Products
                <i class="fas fa-spinner fa-spin"></i>
            </button>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Price range slider
        const priceRange = document.getElementById('price-range');
        const minPrice = document.getElementById('min-price');
        const maxPrice = document.getElementById('max-price');

        priceRange.addEventListener('input', function() {
            maxPrice.value = this.value;
            minPrice.value = 0;
        });

        // Filter change handlers
        document.getElementById('category-filter').addEventListener('change', filterProducts);
        document.getElementById('sort-filter').addEventListener('change', filterProducts);
        document.getElementById('price-range').addEventListener('change', filterProducts);

        function filterProducts() {
            // Add your filtering logic here
            console.log('Filtering products...');
        }

        // Load more products
        document.querySelector('.load-more-btn').addEventListener('click', function() {
            this.classList.add('loading');
            // Add your load more logic here
            setTimeout(() => {
                this.classList.remove('loading');
            }, 1000);
        });

        // Quick View function
        function quickView(productId) {
            console.log('Quick view for product ID: ' + productId);
            // Implement modal popup logic here
        }

        // Add to Cart function
        function quickAddToCart(productId) {
            console.log('Adding product ID ' + productId + ' to cart');
            // Show success notification
            showNotification('Product added to cart!', 'success');
        }

        // Add to Wishlist function
        function addToWishlist(productId) {
            console.log('Adding product ID ' + productId + ' to wishlist');
            // Show success notification
            showNotification('Product added to wishlist!', 'success');
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

        document.addEventListener('DOMContentLoaded', function() {
            // Page Loader
            const pageLoader = document.querySelector('.page-loader');
            window.addEventListener('load', function() {
                setTimeout(function() {
                    pageLoader.classList.add('hide');
                }, 1500); // Slightly longer delay to show the animation
            });

            // Add your existing new arrivals page scripts here
        });
    </script>
</body>
</html>