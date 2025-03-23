<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all deals
$deals = getDailyDeals($conn, 12);

// Filter deals by category if requested
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
if ($categoryId > 0) {
    $filteredDeals = array_filter($deals, function($deal) use ($categoryId) {
        return $deal['category_id'] == $categoryId;
    });
    $deals = $filteredDeals;
}

// Get all categories for filter
$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Deals & Offers - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
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
            border-top-color: var(--danger-color);
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

        .deal-icon {
            font-size: 1.8rem;
            opacity: 0;
            transform: scale(0.5);
            animation: popIn 3s infinite;
        }

        .deal-icon:nth-child(1) { animation-delay: 0s; }
        .deal-icon:nth-child(2) { animation-delay: 0.5s; }
        .deal-icon:nth-child(3) { animation-delay: 1s; }
        .deal-icon:nth-child(4) { animation-delay: 1.5s; }

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

        /* Deals Page Styles */
        .deals-page-header {
            background: linear-gradient(135deg, #ff4e50 0%, #f9d423 100%);
            padding: 80px 0;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .deals-page-header h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .deals-page-header p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 30px;
            position: relative;
            z-index: 2;
        }
        
        .header-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .shape {
            position: absolute;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .shape-1 {
            width: 150px;
            height: 150px;
            top: -30px;
            left: 10%;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 80px;
            height: 80px;
            top: 60%;
            left: 15%;
            animation: float 6s ease-in-out infinite 1s;
        }
        
        .shape-3 {
            width: 200px;
            height: 200px;
            top: 10%;
            right: 10%;
            animation: float 10s ease-in-out infinite 2s;
        }
        
        .shape-4 {
            width: 120px;
            height: 120px;
            bottom: -30px;
            right: 20%;
            animation: float 7s ease-in-out infinite 1.5s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0) rotate(0deg); }
        }
        
        .deals-container {
            padding: 60px 0;
            background-color: #f8f9fa;
        }
        
        .deals-filters {
            margin-bottom: 40px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .filter-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }
        
        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .category-filter {
            padding: 8px 16px;
            border-radius: 30px;
            background-color: #f0f0f0;
            color: #555;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            border: none;
            outline: none;
        }
        
        .category-filter:hover {
            background-color: #e0e0e0;
        }
        
        .category-filter.active {
            background-color: #ff4e50;
            color: white;
        }
        
        .countdown-banner {
            background: linear-gradient(135deg, #3a1c71 0%, #d76d77 50%, #ffaf7b 100%);
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 40px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .countdown-content {
            position: relative;
            z-index: 2;
        }
        
        .countdown-title {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .countdown-text {
            font-size: 1.1rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .countdown-timer {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .timer-item {
            background-color: rgba(255,255,255,0.15);
            border-radius: 8px;
            padding: 10px 15px;
            text-align: center;
            min-width: 70px;
        }
        
        .timer-number {
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
            margin-bottom: 5px;
        }
        
        .timer-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            opacity: 0.8;
        }
        
        .countdown-button {
            display: inline-block;
            padding: 12px 25px;
            background-color: white;
            color: #3a1c71;
            font-weight: 600;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .countdown-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .countdown-waves {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 20%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEyODAgMTQwIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxnIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xNSkiPjxwYXRoIGQ9Ik0xMjgwIDBIMFYxNDBoMTI4MHoiLz48L2c+PC9zdmc+') center/cover no-repeat;
        }
        
        .deals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .deal-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .deal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .deal-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #ff4e50;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }
        
        .deal-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .deal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .deal-card:hover .deal-image img {
            transform: scale(1.1);
        }
        
        .deal-content {
            padding: 20px;
        }
        
        .deal-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .deal-title {
            font-size: 1.25rem;
            margin-bottom: 15px;
            font-weight: 600;
            line-height: 1.3;
            color: #333;
        }
        
        .deal-title a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .deal-title a:hover {
            color: #ff4e50;
        }
        
        .deal-price {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .original-price {
            color: #999;
            text-decoration: line-through;
            margin-right: 10px;
            font-size: 1rem;
        }
        
        .discounted-price {
            color: #ff4e50;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .discount-percent {
            margin-left: auto;
            background-color: #fff3f3;
            color: #ff4e50;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .deal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .deal-timer {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
        }
        
        .deal-timer i {
            margin-right: 5px;
            color: #ff4e50;
        }
        
        .deal-action {
            display: flex;
            gap: 10px;
        }
        
        .btn-cart, .btn-wishlist {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-cart {
            background-color: #ff4e50;
            color: white;
        }
        
        .btn-wishlist {
            background-color: #f0f0f0;
            color: #666;
        }
        
        .btn-cart:hover {
            background-color: #e6383a;
        }
        
        .btn-wishlist:hover {
            background-color: #ff4e50;
            color: white;
        }
        
        .no-deals {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .no-deals i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-deals h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .no-deals p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .scroll-animation {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .scroll-animation.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        @media (max-width: 768px) {
            .deals-page-header h1 {
                font-size: 2.5rem;
            }
            
            .countdown-timer {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .deals-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
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
                    <span class="deal-icon">🔥</span>
                    <span class="deal-icon">💰</span>
                    <span class="deal-icon">🏷️</span>
                    <span class="deal-icon">💎</span>
                </div>
            </div>
            <div class="loader-text">Loading Hot Deals</div>
            <div class="loader-subtext">Finding the best discounts for you...</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>
    
    <div class="deals-page-header">
        <div class="header-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
        <div class="container">
            <h1>Flash Deals & Special Offers</h1>
            <p>Discover amazing deals with big discounts on your favorite products. Limited-time offers that you don't want to miss!</p>
        </div>
    </div>
    
    <main class="deals-container">
        <div class="container">
            <div class="deals-filters scroll-animation">
                <h3 class="filter-title">Filter by Category</h3>
                <div class="category-filters">
                    <a href="deals.php" class="category-filter <?php echo $categoryId === 0 ? 'active' : ''; ?>">All Deals</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="deals.php?category=<?php echo $category['id']; ?>" class="category-filter <?php echo $categoryId === (int)$category['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="countdown-banner scroll-animation">
                <div class="countdown-waves"></div>
                <div class="countdown-content">
                    <h2 class="countdown-title">Summer Sale Ends Soon!</h2>
                    <p class="countdown-text">Up to 70% off on selected items. Hurry, offers expire in:</p>
                    <div class="countdown-timer" id="sale-countdown">
                        <div class="timer-item">
                            <span class="timer-number" id="days">00</span>
                            <span class="timer-label">Days</span>
                        </div>
                        <div class="timer-item">
                            <span class="timer-number" id="hours">00</span>
                            <span class="timer-label">Hours</span>
                        </div>
                        <div class="timer-item">
                            <span class="timer-number" id="minutes">00</span>
                            <span class="timer-label">Minutes</span>
                        </div>
                        <div class="timer-item">
                            <span class="timer-number" id="seconds">00</span>
                            <span class="timer-label">Seconds</span>
                        </div>
                    </div>
                    <a href="#all-deals" class="countdown-button">Shop Now</a>
                </div>
            </div>
            
            <div id="all-deals">
                <?php if (empty($deals)): ?>
                    <div class="no-deals scroll-animation">
                        <i class="fas fa-search"></i>
                        <h3>No deals found</h3>
                        <p>We couldn't find any deals matching your criteria.</p>
                        <a href="deals.php" class="btn-primary">Show All Deals</a>
                    </div>
                <?php else: ?>
                    <div class="deals-grid">
                        <?php foreach ($deals as $index => $product): ?>
                            <div class="deal-card scroll-animation" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                <div class="deal-badge">SALE</div>
                                <div class="deal-image">
                                    <a href="product.php?id=<?php echo $product['id']; ?>">
                                        <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/products/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </a>
                                </div>
                                <div class="deal-content">
                                    <span class="deal-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                                    <h3 class="deal-title">
                                        <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                    </h3>
                                    <div class="deal-price">
                                        <?php if ($product['sale_price'] && $product['regular_price']): ?>
                                            <span class="original-price">₹<?php echo number_format($product['regular_price'], 2); ?></span>
                                            <span class="discounted-price">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                            <?php 
                                                $discountPercent = round(($product['regular_price'] - $product['sale_price']) / $product['regular_price'] * 100);
                                            ?>
                                            <span class="discount-percent"><?php echo $discountPercent; ?>% OFF</span>
                                        <?php else: ?>
                                            <span class="discounted-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="deal-footer">
                                        <div class="deal-timer">
                                            <i class="far fa-clock"></i>
                                            <span>Ends in <span class="random-time" data-hours="<?php echo rand(24, 72); ?>">2 days</span></span>
                                        </div>
                                        <div class="deal-action">
                                            <button class="btn-cart" onclick="quickAddToCart(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <button class="btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page Loader
            const pageLoader = document.querySelector('.page-loader');
            window.addEventListener('load', function() {
                setTimeout(function() {
                    pageLoader.classList.add('hide');
                }, 1500); // Slightly longer delay to show the animation
            });

            // Summer Sale Countdown Timer
            const countdownDate = new Date();
            countdownDate.setDate(countdownDate.getDate() + 3); // Sale ends in 3 days
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = countdownDate - now;
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById("days").innerHTML = days.toString().padStart(2, '0');
                document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
                document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
                document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');
            }
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
            
            // Random time for each product
            document.querySelectorAll('.random-time').forEach(timeElement => {
                const hours = parseInt(timeElement.dataset.hours);
                let timeText;
                
                if (hours < 24) {
                    timeText = `${hours} hours`;
                } else {
                    const days = Math.floor(hours / 24);
                    timeText = `${days} day${days > 1 ? 's' : ''}`;
                }
                
                timeElement.textContent = timeText;
            });
            
            // Scroll animations
            const scrollAnimations = document.querySelectorAll('.scroll-animation');
            
            function checkScroll() {
                scrollAnimations.forEach(animation => {
                    const elementTop = animation.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementTop < windowHeight * 0.85) {
                        animation.classList.add('active');
                    }
                });
            }
            
            // Initial check
            checkScroll();
            
            // Check on scroll
            window.addEventListener('scroll', checkScroll);
            
            // Quick add to cart function
            window.quickAddToCart = function(productId) {
                // Create a small circle element that flies to cart
                const circle = document.createElement('div');
                circle.style.position = 'fixed';
                circle.style.width = '20px';
                circle.style.height = '20px';
                circle.style.backgroundColor = '#ff4e50';
                circle.style.borderRadius = '50%';
                circle.style.zIndex = '9999';
                
                // Get button position
                const button = event.currentTarget;
                const buttonRect = button.getBoundingClientRect();
                
                // Get cart icon position
                const cartIcon = document.querySelector('.cart-icon');
                const cartRect = cartIcon.getBoundingClientRect();
                
                // Set initial position
                circle.style.top = buttonRect.top + buttonRect.height/2 + 'px';
                circle.style.left = buttonRect.left + buttonRect.width/2 + 'px';
                
                // Add to DOM
                document.body.appendChild(circle);
                
                // Animate
                circle.style.transition = 'all 0.8s cubic-bezier(0.215, 0.61, 0.355, 1)';
                setTimeout(() => {
                    circle.style.top = cartRect.top + cartRect.height/2 + 'px';
                    circle.style.left = cartRect.left + cartRect.width/2 + 'px';
                    circle.style.opacity = '0';
                    circle.style.transform = 'scale(0.2)';
                }, 10);
                
                // Remove after animation
                setTimeout(() => {
                    circle.remove();
                }, 800);
                
                // Actually add to cart via AJAX
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
            
            // Add to wishlist function
            window.addToWishlist = function(productId) {
                const button = event.currentTarget;
                const icon = button.querySelector('i');
                
                // Toggle heart icon
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
                
                if (icon.classList.contains('fas')) {
                    // Heart animation
                    button.classList.add('animate__animated', 'animate__heartBeat');
                    setTimeout(() => {
                        button.classList.remove('animate__animated', 'animate__heartBeat');
                    }, 1000);
                    
                    showNotification('Product added to wishlist!', 'success');
                } else {
                    showNotification('Product removed from wishlist!', 'info');
                }
                
                // Send request to server
                fetch('ajax/wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            };
            
            // Notification function
            window.showNotification = function(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle'}"></i>
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