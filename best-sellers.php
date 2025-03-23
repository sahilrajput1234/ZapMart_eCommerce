<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get best selling products
$bestSellers = getPopularProducts($conn, 12);

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
    <title>Best Sellers - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/best-sellers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="best-sellers-page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content" data-aos="fade-right">
                    <h1>Best Sellers</h1>
                    <p>Discover our most popular products that customers love</p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">5000+</span>
                            <span class="stat-label">Happy Customers</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">4.8</span>
                            <span class="stat-label">Average Rating</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">24h</span>
                            <span class="stat-label">Customer Support</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image" data-aos="fade-left">
                    <img src="assets/images/products/<?php echo rand(1, 2) == 1 ? 'smartphone.svg' : 'laptop.svg'; ?>" alt="Best Selling Products">
                    <div class="hero-badge">
                        <span class="badge-text">TOP RATED</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories-section">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2>Shop by Category</h2>
                    <p>Explore our best-selling products across different categories</p>
                </div>
                <div class="category-tabs" data-aos="fade-up">
                    <button class="tab-btn active" data-category="all">All Products</button>
                    <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                        <button class="tab-btn" data-category="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Products Grid -->
        <section class="products-section">
            <div class="container">
                <div class="products-grid">
                    <?php foreach ($bestSellers as $product): 
                        $loop++;
                        // Get appropriate image 
                        $productImage = !empty($product['image']) ? $product['image'] : getProductImage($product['name']);
                        // Get brand logo if available
                        $brandLogo = isset($product['brand_name']) ? getBrandLogo($product['brand_name']) : null;
                        
                        // Determine if this is a featured product (every 3rd product)
                        $isFeatured = ($loop % 3 === 0);
                    ?>
                        <div class="product-card <?php echo $isFeatured ? 'featured' : ''; ?>" 
                            data-aos="<?php echo $isFeatured ? 'zoom-in' : 'fade-up'; ?>"
                            data-aos-delay="<?php echo $loop * 50; ?>"
                            data-category="<?php echo $product['category_id'] ?? 'all'; ?>">
                            
                            <?php if ($isFeatured): ?>
                            <div class="featured-badge">
                                <i class="fas fa-award"></i> Featured
                            </div>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <img src="<?php echo $productImage; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                
                                <?php if ($brandLogo): ?>
                                <div class="brand-logo">
                                    <img src="<?php echo $brandLogo; ?>" alt="<?php echo htmlspecialchars($product['brand_name'] ?? ''); ?>">
                                </div>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <button class="action-btn view-btn" onclick="quickView(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn cart-btn" onclick="quickAddToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <button class="action-btn wishlist-btn" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-meta">
                                    <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Electronics'); ?></span>
                                    
                                    <div class="product-rating">
                                        <?php 
                                            $rating = $product['average_rating'] ?? rand(3, 5);
                                            for ($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="fas fa-star <?php echo $i <= $rating ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
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
                                
                                <div class="product-footer">
                                    <div class="sold-count">
                                        <i class="fas fa-fire"></i> 
                                        <span><?php echo rand(50, 500); ?> sold</span>
                                    </div>
                                    <button class="add-to-cart" onclick="quickAddToCart(<?php echo $product['id']; ?>)">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Empty State -->
                <div class="empty-state" style="display: none;">
                    <img src="assets/images/products/<?php echo rand(1, 2) == 1 ? 'camera.svg' : 'headphones.svg'; ?>" alt="No products found">
                    <h3>No products found</h3>
                    <p>Try adjusting your filter or category selection</p>
                    <button class="reset-btn" onclick="resetFilters()">Reset Filters</button>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section" data-aos="fade-up">
            <div class="container">
                <div class="section-header">
                    <h2>What Our Customers Say</h2>
                    <p>Read reviews from customers who love our best-selling products</p>
                </div>
                
                <div class="testimonials-slider">
                    <div class="testimonial-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="testimonial-rating">
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                        </div>
                        <p class="testimonial-text">"The product quality exceeded my expectations. Fast shipping and excellent customer service. Will definitely be shopping here again!"</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <span>JD</span>
                            </div>
                            <div class="author-info">
                                <h4>John Doe</h4>
                                <p>Verified Buyer</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="testimonial-rating">
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                        </div>
                        <p class="testimonial-text">"I've purchased multiple items from this store and have always been satisfied with the quality and service. Highly recommend!"</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <span>JS</span>
                            </div>
                            <div class="author-info">
                                <h4>Jane Smith</h4>
                                <p>Verified Buyer</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="testimonial-rating">
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star filled"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Great products at competitive prices. The website is easy to navigate and the checkout process is smooth. Will shop here again."</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <span>MS</span>
                            </div>
                            <div class="author-info">
                                <h4>MOHD SAHIL</h4>
                                <p>Verified Buyer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section" data-aos="fade-up">
            <div class="container">
                <div class="newsletter-content">
                    <h2>Stay Updated</h2>
                    <p>Subscribe to our newsletter for exclusive deals and updates on new best sellers</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Category tabs functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const productCards = document.querySelectorAll('.product-card');
            const emptyState = document.querySelector('.empty-state');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const category = this.getAttribute('data-category');
                    let hasVisibleProducts = false;
                    
                    productCards.forEach(card => {
                        if (category === 'all' || card.getAttribute('data-category') === category) {
                            card.style.display = 'flex';
                            hasVisibleProducts = true;
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    // Show/hide empty state
                    emptyState.style.display = hasVisibleProducts ? 'none' : 'flex';
                });
            });
        });

        // Reset filters function
        function resetFilters() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const productCards = document.querySelectorAll('.product-card');
            const emptyState = document.querySelector('.empty-state');
            
            // Set "All Products" tab as active
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-category') === 'all') {
                    btn.classList.add('active');
                }
            });
            
            // Show all products
            productCards.forEach(card => {
                card.style.display = 'flex';
            });
            
            // Hide empty state
            emptyState.style.display = 'none';
        }

        // Quick View function
        function quickView(productId) {
            console.log('Quick view for product ID: ' + productId);
            // Show success notification
            showNotification('Product details loaded', 'success');
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
    </script>
</body>
</html>