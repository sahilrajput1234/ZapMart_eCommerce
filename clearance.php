<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get clearance products (products with sale_price)
$query = "SELECT p.*, c.name as category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE p.sale_price > 0 AND p.sale_price < p.regular_price
          ORDER BY (p.regular_price - p.sale_price) / p.regular_price DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$clearanceProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Sale - ZapMart</title>
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

        /* Clearance Container */
        .clearance-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        .clearance-page-title {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .clearance-page-title h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .clearance-page-title p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        /* Sale Banner */
        .sale-banner {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
            position: relative;
            overflow: hidden;
        }

        .sale-banner h2 {
            font-size: 2rem;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sale-banner p {
            font-size: 1.2rem;
            margin: 0;
            opacity: 0.9;
        }

        .sale-badge {
            position: absolute;
            top: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            background-color: var(--accent-color);
            transform: rotate(45deg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sale-badge span {
            transform: rotate(-45deg);
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 50px;
            margin-right: 20px;
        }

        /* Clearance Timer */
        .clearance-timer {
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 40px;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.3s forwards;
        }

        .clearance-timer h3 {
            color: var(--text-color);
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .timer-container {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .timer-item {
            background: var(--light-bg);
            padding: 15px;
            border-radius: 10px;
            min-width: 80px;
        }

        .timer-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--danger-color);
            margin-bottom: 5px;
        }

        .timer-label {
            font-size: 0.9rem;
            color: var(--light-text);
            text-transform: uppercase;
        }

        /* Filter Section */
        .filter-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.4s forwards;
        }

        .filter-input {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sort-select, .category-select {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background-color: var(--white);
            color: var(--text-color);
            cursor: pointer;
            min-width: 180px;
            box-shadow: var(--input-shadow);
        }

        .sort-select:focus, .category-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-form {
            display: flex;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: 10px 0 0 10px;
            box-shadow: var(--input-shadow);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-btn {
            padding: 0 20px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 0 10px 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: var(--primary-dark);
        }

        /* Clearance Products */
        .clearance-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 200px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger-color);
            color: var(--white);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .product-details {
            padding: 20px;
        }

        .product-category {
            color: var(--lighter-text);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 10px;
            font-weight: 600;
            height: 50px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .original-price {
            color: var(--lighter-text);
            text-decoration: line-through;
            font-size: 0.9rem;
        }

        .clearance-price {
            color: var(--danger-color);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .add-to-cart {
            flex: 1;
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

        .wishlist-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg);
            color: var(--light-text);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .wishlist-btn:hover {
            background: var(--border-color);
            color: var(--danger-color);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.6s forwards;
        }

        .pagination-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-color);
        }

        .pagination-btn.active {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        .pagination-btn:hover:not(.active) {
            background: var(--light-bg);
        }

        /* No Products */
        .no-products {
            text-align: center;
            padding: 50px 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .no-products i {
            font-size: 4rem;
            color: var(--lighter-text);
            margin-bottom: 20px;
        }

        .no-products h3 {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .no-products p {
            color: var(--light-text);
            margin-bottom: 20px;
        }

        /* Animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card:nth-child(1) { animation-delay: 0.4s; }
        .product-card:nth-child(2) { animation-delay: 0.45s; }
        .product-card:nth-child(3) { animation-delay: 0.5s; }
        .product-card:nth-child(4) { animation-delay: 0.55s; }
        .product-card:nth-child(5) { animation-delay: 0.6s; }
        .product-card:nth-child(6) { animation-delay: 0.65s; }
        .product-card:nth-child(7) { animation-delay: 0.7s; }
        .product-card:nth-child(8) { animation-delay: 0.75s; }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form {
                max-width: 100%;
            }

            .filter-input {
                width: 100%;
            }

            .sort-select, .category-select {
                flex: 1;
                min-width: initial;
            }

            .timer-container {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-icon">🏷️</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="clearance-container">
        <div class="clearance-page-title">
            <h1>Clearance Sale</h1>
            <p>Huge discounts on selected items - limited time only!</p>
        </div>
        
        <div class="sale-banner">
            <h2>Mega Clearance Sale!</h2>
            <p>Save up to 70% on selected items while stocks last</p>
            <div class="sale-badge">
                <span>SALE</span>
            </div>
        </div>
        
        <div class="clearance-timer">
            <h3>Hurry! Sale Ends In:</h3>
            <div class="timer-container">
                <div class="timer-item">
                    <div class="timer-value" id="days">03</div>
                    <div class="timer-label">Days</div>
                </div>
                <div class="timer-item">
                    <div class="timer-value" id="hours">12</div>
                    <div class="timer-label">Hours</div>
                </div>
                <div class="timer-item">
                    <div class="timer-value" id="minutes">45</div>
                    <div class="timer-label">Minutes</div>
                </div>
                <div class="timer-item">
                    <div class="timer-value" id="seconds">30</div>
                    <div class="timer-label">Seconds</div>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <div class="filter-input">
                <select class="sort-select" id="sort-select">
                    <option value="discount_high">Highest Discount</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="newest">Newest Items</option>
                </select>
                
                <select class="category-select" id="category-select">
                    <option value="">All Categories</option>
                    <option value="electronics">Electronics</option>
                    <option value="clothing">Clothing</option>
                    <option value="home">Home & Kitchen</option>
                    <option value="beauty">Beauty & Personal Care</option>
                    <option value="sports">Sports & Outdoors</option>
                </select>
            </div>
            
            <form class="search-form">
                <input type="text" class="search-input" placeholder="Search clearance items...">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <?php if (!empty($clearanceProducts)): ?>
            <div class="clearance-products">
                <?php foreach ($clearanceProducts as $index => $product): ?>
                    <?php 
                        $discountPercent = round(($product['regular_price'] - $product['sale_price']) / $product['regular_price'] * 100);
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/products/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="discount-badge">-<?php echo $discountPercent; ?>%</div>
                        </div>
                        <div class="product-details">
                            <div class="product-category">
                                <?php echo !empty($product['category_name']) ? htmlspecialchars($product['category_name']) : 'Uncategorized'; ?>
                            </div>
                            <div class="product-name">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </div>
                            <div class="product-price">
                                <span class="original-price">₹<?php echo number_format($product['regular_price'], 2); ?></span>
                                <span class="clearance-price">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                            </div>
                            <div class="product-actions">
                                <form action="cart.php" method="post">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="add-to-cart">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </form>
                                <button class="wishlist-btn">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="pagination">
                <a href="#" class="pagination-btn"><i class="fas fa-chevron-left"></i></a>
                <a href="#" class="pagination-btn active">1</a>
                <a href="#" class="pagination-btn">2</a>
                <a href="#" class="pagination-btn">3</a>
                <a href="#" class="pagination-btn"><i class="fas fa-chevron-right"></i></a>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>No Clearance Products Available</h3>
                <p>Check back later for more clearance deals!</p>
                <a href="products.php" class="add-to-cart">
                    <i class="fas fa-shopping-bag"></i> Browse All Products
                </a>
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
            
            // Countdown Timer
            function updateCountdown() {
                // Set the date we're counting down to (3 days from now)
                const now = new Date();
                const countDownDate = new Date();
                countDownDate.setDate(now.getDate() + 3);
                countDownDate.setHours(23, 59, 59, 0);
                
                // Get current time
                const currentTime = now.getTime();
                
                // Find the distance between now and the countdown date
                const distance = countDownDate.getTime() - currentTime;
                
                // Time calculations for days, hours, minutes, and seconds
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // Display the results
                document.getElementById("days").textContent = days.toString().padStart(2, '0');
                document.getElementById("hours").textContent = hours.toString().padStart(2, '0');
                document.getElementById("minutes").textContent = minutes.toString().padStart(2, '0');
                document.getElementById("seconds").textContent = seconds.toString().padStart(2, '0');
            }
            
            // Update the countdown every 1 second
            updateCountdown();
            setInterval(updateCountdown, 1000);
            
            // Filter and sorting functionality
            const sortSelect = document.getElementById('sort-select');
            const categorySelect = document.getElementById('category-select');
            
            if (sortSelect && categorySelect) {
                sortSelect.addEventListener('change', filterProducts);
                categorySelect.addEventListener('change', filterProducts);
            }
            
            function filterProducts() {
                // In a real implementation, this would update the products displayed
                // For demo purposes, we'll just reload the page with query parameters
                const sort = sortSelect.value;
                const category = categorySelect.value;
                
                if (sort || category) {
                    let queryParams = [];
                    if (sort) queryParams.push(`sort=${sort}`);
                    if (category) queryParams.push(`category=${category}`);
                    
                    // window.location.href = `clearance.php?${queryParams.join('&')}`;
                    console.log(`Would navigate to: clearance.php?${queryParams.join('&')}`);
                }
            }
        });
    </script>
</body>
</html> 