<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all active brands
$brands = getActiveBrands($conn);

// Get featured brands (brands with most products)
$featuredBrands = getFeaturedBrands($conn, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brands - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/brands.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="brands-page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content" data-aos="fade-right">
                    <h1>Our Brands</h1>
                    <p>Discover our curated collection of premium brands</p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($brands); ?>+</span>
                            <span class="stat-label">Brands</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">1000+</span>
                            <span class="stat-label">Products</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">24h</span>
                            <span class="stat-label">Support</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image" data-aos="fade-left">
                    <img src="assets/images/patterns/brand-pattern.svg" alt="Brand Pattern">
                    <div class="hero-badge">
                        <span class="badge-text">PREMIUM</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Brands -->
        <section class="featured-brands">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2>Featured Brands</h2>
                    <p>Our most popular brands with the widest selection of products</p>
                </div>
                <div class="featured-brands-grid">
                    <?php foreach ($featuredBrands as $brand): ?>
                        <div class="featured-brand-card" data-aos="fade-up">
                            <div class="brand-logo">
                                <img src="assets/images/brands/<?php echo strtolower($brand['name']); ?>.svg" 
                                     alt="<?php echo htmlspecialchars($brand['name']); ?>">
                            </div>
                            <div class="brand-info">
                                <h3><?php echo htmlspecialchars($brand['name']); ?></h3>
                                <p><?php echo htmlspecialchars($brand['description']); ?></p>
                                <div class="brand-stats">
                                    <span><i class="fas fa-box"></i> <?php echo $brand['product_count']; ?> Products</span>
                                    <span><i class="fas fa-star"></i> <?php echo number_format($brand['average_rating'], 1); ?></span>
                                </div>
                                <a href="products.php?brand=<?php echo $brand['id']; ?>" class="view-products">
                                    View Products <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- All Brands Grid -->
        <section class="all-brands">
            <div class="container">
                <div class="section-header" data-aos="fade-up">
                    <h2>All Brands</h2>
                    <p>Browse through our complete collection of brands</p>
                </div>
                <div class="brands-grid">
                    <?php foreach ($brands as $brand): ?>
                        <div class="brand-card" data-aos="fade-up">
                            <div class="brand-logo">
                                <img src="assets/images/brands/<?php echo strtolower($brand['name']); ?>.svg" 
                                     alt="<?php echo htmlspecialchars($brand['name']); ?>">
                            </div>
                            <div class="brand-info">
                                <h3><?php echo htmlspecialchars($brand['name']); ?></h3>
                                <p><?php echo htmlspecialchars($brand['description']); ?></p>
                                <div class="brand-stats">
                                    <span><i class="fas fa-box"></i> <?php echo $brand['product_count']; ?> Products</span>
                                </div>
                                <a href="products.php?brand=<?php echo $brand['id']; ?>" class="view-products">
                                    View Products <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section" data-aos="fade-up">
            <div class="container">
                <div class="newsletter-content">
                    <h2>Stay Updated</h2>
                    <p>Subscribe to our newsletter for updates on new brands and exclusive deals</p>
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
    </script>
</body>
</html>