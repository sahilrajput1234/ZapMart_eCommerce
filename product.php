<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get product ID from URL
$productId = 0;
if (isset($_GET['id'])) {
    $productId = (int)$_GET['id'];
} else {
    // Handle invalid URLs without product ID
    header('Location: 404.php');
    exit;
}

// Get product details
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: 404.php');
    exit;
}

// Get product images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt->execute([$productId]);
$productImages = $stmt->fetchAll();

// Get product reviews
$reviews = getProductReviews($conn, $productId);

// Calculate average rating
$averageRating = 0;
$totalReviews = count($reviews);
if ($totalReviews > 0) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $averageRating = round($totalRating / $totalReviews, 1);
}

// Get related products
$stmt = $conn->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ?
    LIMIT 4
");
$stmt->execute([$product['category_id'], $productId]);
$relatedProducts = $stmt->fetchAll();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5) {
        addReview($conn, $_SESSION['user_id'], $productId, $rating, $comment);
        header("Location: product.php?id=$productId#reviews");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/product-gallery.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="product-page">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a>
                <span>/</span>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>

            <div class="product-details">
                <div class="product-gallery">
                    <div class="swiper product-main-slider">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <?php if (!empty($productImages)): ?>
                                <?php foreach ($productImages as $image): ?>
                                <div class="swiper-slide">
                                    <img src="<?php echo $image['image_url']; ?>" alt="Product thumbnail">
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                    
                    <?php if (!empty($productImages)): ?>
                    <div class="swiper product-thumbs-slider">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <?php foreach ($productImages as $image): ?>
                            <div class="swiper-slide">
                                <img src="<?php echo $image['image_url']; ?>" alt="Product thumbnail">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-rating">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $averageRating): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $averageRating): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <a href="#reviews"><?php echo $totalReviews; ?> reviews</a>
                    </div>

                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="regular-price">₹<?php echo number_format($product['regular_price'], 2); ?></span>
                            <span class="sale-price">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                        <?php else: ?>
                            <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <form action="ajax/cart.php" method="POST" class="add-to-cart-form">
                            <div class="quantity-control">
                                <button type="button" class="decrease">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                                <button type="button" class="increase">+</button>
                            </div>
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn-primary add-to-cart">
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </button>
                            <button type="button" class="btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                <i class="far fa-heart"></i>
                                Add to Wishlist
                            </button>
                        </form>
                        <p class="stock-status in-stock">In Stock (<?php echo $product['stock']; ?> items)</p>
                    <?php else: ?>
                        <p class="stock-status out-of-stock">Out of Stock</p>
                    <?php endif; ?>

                    <div class="product-meta">
                        <p><strong>SKU:</strong> <?php echo $product['id']; ?></p>
                        <p><strong>Category:</strong> <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></p>
                        <?php if (isset($_SESSION['admin_id'])): ?>
                        <p><a href="admin/add-product-images.php?id=<?php echo $product['id']; ?>" class="btn-primary" style="display: inline-block; margin-top: 1rem;">
                            <i class="fas fa-images"></i> Add Images
                        </a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="product-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="description">Description</button>
                    <button class="tab-button" data-tab="reviews">Reviews (<?php echo $totalReviews; ?>)</button>
                </div>

                <div class="tab-content">
                    <div id="description" class="tab-panel active">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>

                    <div id="reviews" class="tab-panel">
                        <div class="reviews-summary">
                            <div class="average-rating">
                                <h3><?php echo $averageRating; ?></h3>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $averageRating): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i - 0.5 <= $averageRating): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p>Based on <?php echo $totalReviews; ?> reviews</p>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="review-form">
                                <h3>Write a Review</h3>
                                <form action="product.php?id=<?php echo $productId; ?>#reviews" method="POST">
                                    <div class="rating-input">
                                        <label>Your Rating:</label>
                                        <div class="stars">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                                                <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="comment">Your Review:</label>
                                        <textarea name="comment" id="comment" rows="5" required></textarea>
                                    </div>
                                    <button type="submit" class="btn-primary">Submit Review</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p class="login-prompt">Please <a href="login.php">login</a> to write a review.</p>
                        <?php endif; ?>

                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review">
                                    <div class="review-header">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : ' empty'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                    <p class="reviewer"><?php echo htmlspecialchars($review['username']); ?></p>
                                    <p class="review-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($relatedProducts)): ?>
            <section class="related-products">
                <h2>Related Products</h2>
                <div class="product-grid">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <?php include 'includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Image gallery
        function updateMainImage(imageUrl) {
            document.getElementById('main-product-image').src = imageUrl;
        }

        // Quantity controls
        document.querySelector('.decrease').addEventListener('click', function() {
            const input = this.nextElementSibling;
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        });

        document.querySelector('.increase').addEventListener('click', function() {
            const input = this.previousElementSibling;
            const currentValue = parseInt(input.value);
            const maxStock = parseInt(input.getAttribute('max'));
            if (currentValue < maxStock) {
                input.value = currentValue + 1;
            }
        });

        // Tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and panels
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
                
                // Add active class to clicked button and corresponding panel
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.add('active');
            });
        });

        // Rating stars
        document.querySelectorAll('.rating-input label').forEach(label => {
            label.addEventListener('mouseover', function() {
                const stars = this.closest('.stars').querySelectorAll('label');
                const starValue = this.previousElementSibling.value;
                
                stars.forEach((star, index) => {
                    if (index < starValue) {
                        star.querySelector('i').classList.remove('far');
                        star.querySelector('i').classList.add('fas');
                    } else {
                        star.querySelector('i').classList.remove('fas');
                        star.querySelector('i').classList.add('far');
                    }
                });
            });
        });

        // Add to cart
        document.querySelector('.add-to-cart-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Product added to cart!', 'success');
                    updateCartCount(data.cartCount);
                } else {
                    showNotification('Failed to add product to cart.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred.', 'error');
            }
        });

        // Add to wishlist
        async function addToWishlist(productId) {
            try {
                const response = await fetch('ajax/wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Product added to wishlist!', 'success');
                    const wishlistBtn = document.querySelector('.btn-wishlist');
                    wishlistBtn.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
                    wishlistBtn.disabled = true;
                } else {
                    showNotification(data.message || 'Failed to add to wishlist.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred.', 'error');
            }
        }
    </script>
</body>
</html>