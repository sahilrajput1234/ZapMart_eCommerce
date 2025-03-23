<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Base query
$baseQuery = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE 1=1
";
$countQuery = "SELECT COUNT(*) FROM products p WHERE 1=1";
$params = [];

// Add filters to query
if ($category) {
    $baseQuery .= " AND p.category_id = :category";
    $countQuery .= " AND p.category_id = :category";
    $params[':category'] = $category;
}

if ($search) {
    $baseQuery .= " AND (p.name LIKE :search OR p.description LIKE :search)";
    $countQuery .= " AND (p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($min_price !== null) {
    $baseQuery .= " AND p.price >= :min_price";
    $countQuery .= " AND p.price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price !== null) {
    $baseQuery .= " AND p.price <= :max_price";
    $countQuery .= " AND p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $baseQuery .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $baseQuery .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $baseQuery .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $baseQuery .= " ORDER BY p.name DESC";
        break;
    default:
        $baseQuery .= " ORDER BY p.created_at DESC";
}

// Add pagination
$baseQuery .= " LIMIT :offset, :per_page";

// Get total products count
$stmt = $conn->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$totalProducts = $stmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get products
$stmt = $conn->prepare($baseQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - ZapMart</title>
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
        }

        .products-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .page-title p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .products-wrapper {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }

        /* Filters Sidebar */
        .filters-sidebar {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow);
            height: fit-content;
        }

        .filter-section {
            margin-bottom: 25px;
        }

        .filter-section:last-child {
            margin-bottom: 0;
        }

        .filter-title {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .filter-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .filter-item {
            margin-bottom: 10px;
        }

        .filter-link {
            color: var(--light-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .filter-link:hover {
            color: var(--primary-color);
        }

        .filter-link.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        .price-range {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .price-input {
            width: 100px;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 0.9rem;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
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

        .badge-new {
            background: var(--primary-color);
            color: var(--white);
        }

        .badge-sale {
            background: var(--danger-color);
            color: var(--white);
        }

        .product-details {
            padding: 20px;
        }

        .product-category {
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .product-name {
            color: var(--text-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-decoration: none;
            display: block;
            transition: color 0.3s ease;
        }

        .product-name:hover {
            color: var(--primary-color);
        }

        .product-price {
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
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--white);
            color: var(--light-text);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .wishlist-btn:hover {
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        /* Sorting and Search */
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .search-box {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 20px;
            padding-left: 40px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
        }

        .sort-select {
            padding: 12px 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-color);
            background: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sort-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }

        .page-link {
            padding: 8px 16px;
            background: var(--white);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--light-bg);
        }

        .page-link.active {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        @media (max-width: 1024px) {
            .products-wrapper {
                grid-template-columns: 1fr;
            }

            .filters-sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .products-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

            <div class="products-container">
        <div class="page-title">
            <h1>Our Products</h1>
            <p>Discover our wide range of quality products</p>
                    </div>

        <div class="products-wrapper">
            <!-- Filters Sidebar -->
            <div class="filters-sidebar">
                        <div class="filter-section">
                    <h3 class="filter-title">Categories</h3>
                            <ul class="filter-list">
                        <li class="filter-item">
                            <a href="products.php" class="filter-link <?php echo !$category ? 'active' : ''; ?>">
                                All Categories
                            </a>
                                </li>
                        <?php foreach ($categories as $cat): ?>
                            <li class="filter-item">
                                <a href="?category=<?php echo $cat['id']; ?>" 
                                   class="filter-link <?php echo $category == $cat['id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="filter-section">
                    <h3 class="filter-title">Price Range</h3>
                    <form action="" method="get" class="price-range">
                        <input type="number" name="min_price" placeholder="Min" class="price-input" 
                               value="<?php echo $min_price; ?>">
                        <span>to</span>
                        <input type="number" name="max_price" placeholder="Max" class="price-input"
                               value="<?php echo $max_price; ?>">
                    </form>
                            </div>
                        </div>

            <!-- Products Content -->
            <div class="products-content">
                <div class="products-header">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Search products..." class="search-input" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <select class="sort-select">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                            </select>
                        </div>

                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <?php if (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                                    <div class="product-badge badge-new">New</div>
                                <?php elseif ($product['sale_price']): ?>
                                    <div class="product-badge badge-sale">Sale</div>
                                <?php endif; ?>

                                <div class="product-image">
                                    <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/products/placeholder.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>

                                <div class="product-details">
                                    <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="product-name">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>

                                    <div class="product-price">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="current-price">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                            <span class="original-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                            <?php 
                                                $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                                            ?>
                                            <span class="discount-badge"><?php echo $discount; ?>% OFF</span>
                                        <?php else: ?>
                                            <span class="current-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-actions">
                                        <button class="add-to-cart">
                                            <i class="fas fa-shopping-cart"></i>
                                            Add to Cart
                            </button>
                                        <button class="wishlist-btn">
                                            <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                        </div>
                            <?php endforeach; ?>
                        </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <h2>No Products Found</h2>
                        <p>Try adjusting your search or filter criteria</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.querySelector('.search-input');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const searchQuery = this.value.trim();
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('search', searchQuery);
                    currentUrl.searchParams.delete('page');
                    window.location.href = currentUrl.toString();
                }, 500);
            });

            // Sort functionality
            const sortSelect = document.querySelector('.sort-select');
            sortSelect.addEventListener('change', function() {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('sort', this.value);
                currentUrl.searchParams.delete('page');
                window.location.href = currentUrl.toString();
            });

            // Price range form submission
            const priceForm = document.querySelector('.price-range');
            const priceInputs = priceForm.querySelectorAll('input[type="number"]');

            priceInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const currentUrl = new URL(window.location.href);
                    const minPrice = priceForm.querySelector('[name="min_price"]').value;
                    const maxPrice = priceForm.querySelector('[name="max_price"]').value;

                    if (minPrice) currentUrl.searchParams.set('min_price', minPrice);
                    else currentUrl.searchParams.delete('min_price');

                    if (maxPrice) currentUrl.searchParams.set('max_price', maxPrice);
                    else currentUrl.searchParams.delete('max_price');

                    currentUrl.searchParams.delete('page');
                    window.location.href = currentUrl.toString();
                });
            });

            // Add to cart functionality
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productCard = this.closest('.product-card');
                    const productId = productCard.querySelector('.product-name').href.split('id=')[1];
                    
                    // Add to cart animation
                    this.innerHTML = '<i class="fas fa-check"></i> Added';
                    this.style.background = 'var(--secondary-color)';
                    
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                        this.style.background = '';
                    }, 2000);

                    // Here you would typically make an AJAX call to add the item to cart
                });
            });

            // Wishlist functionality
            const wishlistButtons = document.querySelectorAll('.wishlist-btn');
            wishlistButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (icon.classList.contains('far')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.style.color = 'var(--danger-color)';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.style.color = '';
                    }
                });
            });
        });
    </script>
</body>
</html>