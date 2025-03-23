<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured categories
$categories = getCategories($conn);

// Get featured products
$featuredProducts = getFeaturedProducts($conn, 8);

// Get daily deals
$deals = getDailyDeals($conn, 4);

// Get latest products
$latestProducts = getLatestProducts($conn, 8);

// Get recommended products if user is logged in
if (isset($_SESSION['user_id'])) {
    $recommendations = getRecommendedProducts($conn, $_SESSION['user_id'], 4);
} else {
    $recommendations = getPopularProducts($conn, 4);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZapMart - Online Shopping</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Hero Slider -->
        <section class="hero-slider">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide" style="background-image: url('assets/images/banners/banner1.svg');">
                        <div class="slide-content">
                            <h2>New Arrivals</h2>
                            <p>Discover the latest products for this season</p>
                            <a href="products.php?filter=new" class="btn-primary">Shop Now</a>
                        </div>
                    </div>
                    <div class="swiper-slide" style="background-image: url('assets/images/banners/banner2.svg');">
                        <div class="slide-content">
                            <h2>Summer Sale</h2>
                            <p>Up to 50% off on selected items</p>
                            <a href="products.php?filter=sale" class="btn-primary">View Offers</a>
                        </div>
                    </div>
                    <div class="swiper-slide" style="background-image: url('assets/images/banners/banner3.svg');">
                        <div class="slide-content">
                            <h2>Electronics</h2>
                            <p>Latest gadgets and tech accessories</p>
                            <a href="category.php?id=1" class="btn-primary">Explore</a>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </section>

        <!-- Promo Banners -->
        <section class="promo-banners">
            <div class="container">
                <div class="banner-grid">
                    <div class="promo-banner">
                        <img src="assets/images/promos/free-shipping.svg" alt="Free Shipping">
                        <div class="banner-content">
                            <h3>Free Shipping</h3>
                            <p>On orders over ₹50</p>
                        </div>
                    </div>
                    <div class="promo-banner">
                        <img src="assets/images/promos/discount.svg" alt="Special Discount">
                        <div class="banner-content">
                            <h3>20% OFF</h3>
                            <p>Use code: SUMMER20</p>
                        </div>
                    </div>
                    <div class="promo-banner">
                        <img src="assets/images/promos/support.svg" alt="24/7 Support">
                        <div class="banner-content">
                            <h3>24/7 Support</h3>
                            <p>Customer service</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Categories -->
        <section class="featured-categories">
            <div class="container">
                <div class="section-header">
                    <h2>Shop by Category</h2>
                    <a href="categories.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="category-grid">
                    <?php 
                    $categoryCount = 0;
                    foreach ($categories as $category): 
                        if ($categoryCount >= 5) break;
                        $categoryCount++;
                    ?>
                        <div class="category-card">
                            <a href="category.php?id=<?php echo $category['id']; ?>">
                                <div class="category-img">
                                    <img src="<?php echo !empty($category['image']) ? $category['image'] : 'assets/images/categories/placeholder.svg'; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                </div>
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                <span class="shop-now">Shop Now <i class="fas fa-arrow-right"></i></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="featured-products">
            <div class="container">
                <div class="section-header">
                    <h2>Featured Products</h2>
                    <a href="products.php?filter=featured" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="products-carousel">
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($featuredProducts as $product): ?>
                                <div class="swiper-slide">
                                    <?php include 'includes/product-card.php'; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Deal of the Day -->
        <section class="deal-of-the-day">
            <div class="container">
                <div class="section-header">
                    <h2>Deals of the Day</h2>
                    <a href="products.php?filter=deal" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="deal-grid">
                    <?php foreach ($deals as $product): ?>
                        <div class="deal-card">
                            <div class="deal-badge">
                                <span>DEAL</span>
                            </div>
                            <div class="deal-image">
                                <a href="product.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/products/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </a>
                            </div>
                            <div class="deal-content">
                                <h3><a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                <div class="deal-price">
                                    <?php if ($product['sale_price']): ?>
                                        <span class="regular-price">₹<?php echo number_format($product['regular_price'], 2); ?></span>
                                        <span class="sale-price">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                        <?php 
                                            $discount = round(($product['regular_price'] - $product['sale_price']) / $product['regular_price'] * 100);
                                        ?>
                                        <span class="discount-badge"><?php echo $discount; ?>% OFF</span>
                                    <?php else: ?>
                                        <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="deal-timer" data-ends="2023-12-31">
                                    <div class="timer-item">
                                        <span class="days">00</span>
                                        <span class="label">Days</span>
                                    </div>
                                    <div class="timer-item">
                                        <span class="hours">00</span>
                                        <span class="label">Hours</span>
                                    </div>
                                    <div class="timer-item">
                                        <span class="minutes">00</span>
                                        <span class="label">Mins</span>
                                    </div>
                                    <div class="timer-item">
                                        <span class="seconds">00</span>
                                        <span class="label">Secs</span>
                                    </div>
                                </div>
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-primary">Shop Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Large Banner -->
        <section class="large-banner">
            <div class="container">
                <div class="banner" style="background-image: url('assets/images/banners/wide-banner.svg');">
                    <div class="banner-content">
                        <h2>End of Season Sale</h2>
                        <h3>Up to 75% Off</h3>
                        <p>Limited time offer. Shop now for amazing deals!</p>
                        <a href="products.php?filter=sale" class="btn-primary">Shop Now</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Latest Products -->
        <section class="latest-products">
            <div class="container">
                <div class="section-header">
                    <h2>New Arrivals</h2>
                    <a href="products.php?sort=newest" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="product-grid">
                    <?php foreach ($latestProducts as $product): ?>
                        <?php include 'includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Recommended For You -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <section class="recommended-products">
            <div class="container">
                <div class="section-header">
                    <h2>Recommended For You</h2>
                    <a href="products.php?filter=recommended" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="product-grid">
                    <?php foreach ($recommendations as $product): ?>
                        <?php include 'includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Newsletter -->
        <section class="newsletter">
            <div class="container">
                <div class="newsletter-content">
                    <div class="newsletter-text">
                        <h3>Subscribe to our Newsletter</h3>
                        <p>Get updates on new products, sales, and more.</p>
                    </div>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit" class="btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize hero slider
            new Swiper('.hero-slider .swiper', {
                loop: true,
                autoplay: {
                    delay: 5000,
                },
                pagination: {
                    el: '.hero-slider .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.hero-slider .swiper-button-next',
                    prevEl: '.hero-slider .swiper-button-prev',
                },
            });
            
            // Initialize products carousel
            new Swiper('.products-carousel .swiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                navigation: {
                    nextEl: '.products-carousel .swiper-button-next',
                    prevEl: '.products-carousel .swiper-button-prev',
                },
                pagination: {
                    el: '.products-carousel .swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                    },
                    768: {
                        slidesPerView: 3,
                    },
                    1024: {
                        slidesPerView: 4,
                    },
                },
            });
            
            // Deal timer
            const dealTimers = document.querySelectorAll('.deal-timer');
            dealTimers.forEach(timer => {
                const endDate = new Date(timer.dataset.ends).getTime();
                
                const updateTimer = () => {
                    const now = new Date().getTime();
                    const distance = endDate - now;
                    
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    timer.querySelector('.days').textContent = days.toString().padStart(2, '0');
                    timer.querySelector('.hours').textContent = hours.toString().padStart(2, '0');
                    timer.querySelector('.minutes').textContent = minutes.toString().padStart(2, '0');
                    timer.querySelector('.seconds').textContent = seconds.toString().padStart(2, '0');
                };
                
                updateTimer();
                setInterval(updateTimer, 1000);
            });
            
            // Quick add to cart
            window.quickAddToCart = function(productId) {
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
            };
            
            // Notification function
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
            
            // Update cart count
            window.updateCartCount = function(count) {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = count;
                    cartCount.classList.toggle('hide', count === 0);
                }
            };
        });
    </script>
</body>
</html>