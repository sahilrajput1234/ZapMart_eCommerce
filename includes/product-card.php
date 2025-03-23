<?php
// Ensure $product variable is defined
if (!isset($product) || empty($product)) {
    return;
}

// Set variables
$productId = $product['id'];
$productName = htmlspecialchars($product['name']);
$productImage = $product['image'] ?? 'assets/images/placeholder.jpg';
$productUrl = "product.php?id={$productId}";
$productCategoryId = $product['category_id'] ?? 0;
$productCategoryName = $product['category_name'] ?? '';

// Handle price display
$regularPrice = $product['regular_price'] ?? $product['price'] ?? 0;
$salePrice = $product['sale_price'] ?? 0;
$onSale = !empty($salePrice) && $salePrice < $regularPrice;
$displayPrice = $onSale ? $salePrice : $regularPrice;

// Other product details
$inStock = ($product['stock'] ?? 0) > 0;
$rating = $product['average_rating'] ?? 0;
$shortDescription = isset($product['short_description']) ? 
    htmlspecialchars(substr($product['short_description'], 0, 100)) . (strlen($product['short_description']) > 100 ? '...' : '') : 
    '';
?>

<div class="product-card" data-product-id="<?php echo $productId; ?>">
    <div class="product-badges">
        <?php if ($onSale): ?>
            <span class="badge badge-sale">Sale</span>
        <?php endif; ?>
        
        <?php if (isset($product['is_new']) && $product['is_new']): ?>
            <span class="badge badge-new">New</span>
        <?php endif; ?>
        
        <?php if (isset($product['is_featured']) && $product['is_featured']): ?>
            <span class="badge badge-featured">Featured</span>
        <?php endif; ?>
        
        <?php if (!$inStock): ?>
            <span class="badge badge-out-of-stock">Out of Stock</span>
        <?php endif; ?>
    </div>

    <div class="product-thumb">
        <a href="<?php echo $productUrl; ?>">
            <img src="<?php echo $productImage; ?>" alt="<?php echo $productName; ?>" class="product-image">
        </a>
        <div class="product-actions">
            <button type="button" class="action-btn add-to-cart-btn" <?php echo !$inStock ? 'disabled' : ''; ?> 
                    onclick="quickAddToCart(<?php echo $productId; ?>)">
                <i class="fas fa-shopping-cart"></i>
            </button>
            <button type="button" class="action-btn wishlist-btn" onclick="addToWishlist(<?php echo $productId; ?>)">
                <i class="far fa-heart"></i>
            </button>
            <button type="button" class="action-btn quick-view-btn" data-product-id="<?php echo $productId; ?>">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <div class="product-info">
        <?php if (!empty($productCategoryName)): ?>
            <div class="product-category">
                <a href="category.php?id=<?php echo $productCategoryId; ?>"><?php echo htmlspecialchars($productCategoryName); ?></a>
            </div>
        <?php endif; ?>

        <h3 class="product-title">
            <a href="<?php echo $productUrl; ?>"><?php echo $productName; ?></a>
        </h3>

        <div class="product-rating">
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= $rating): ?>
                        <i class="fas fa-star"></i>
                    <?php elseif ($i - 0.5 <= $rating): ?>
                        <i class="fas fa-star-half-alt"></i>
                    <?php else: ?>
                        <i class="far fa-star"></i>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php if (isset($product['reviews_count'])): ?>
                <span class="rating-count">(<?php echo $product['reviews_count']; ?>)</span>
            <?php endif; ?>
        </div>

        <div class="product-price">
            <?php if ($onSale): ?>
                <span class="regular-price">₹<?php echo number_format($regularPrice, 2); ?></span>
                <span class="sale-price">₹<?php echo number_format($salePrice, 2); ?></span>
            <?php else: ?>
                <span class="current-price">₹<?php echo number_format($displayPrice, 2); ?></span>
            <?php endif; ?>
        </div>

        <div class="product-description">
            <?php echo $shortDescription; ?>
        </div>

        <div class="list-view-actions">
            <a href="<?php echo $productUrl; ?>" class="btn-outline">View Details</a>
            <button type="button" class="btn-primary <?php echo !$inStock ? 'disabled' : ''; ?>" 
                    <?php echo !$inStock ? 'disabled' : ''; ?> 
                    onclick="quickAddToCart(<?php echo $productId; ?>)">
                <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
        </div>
    </div>
</div>