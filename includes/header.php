<?php
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartItems = getCartItems($conn, $_SESSION['cart']);
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<header class="main-header">
    <div class="header-top">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <h1>ZapMart</h1>
                </a>
            </div>

            <div class="search-bar">
                <form action="search.php" method="GET">
                    <select name="category" class="category-select">
                        <option value="">All Categories</option>
                        <?php
                        $categories = getCategories($conn);
                        foreach ($categories as $category) {
                            echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <input type="text" name="q" placeholder="Search products...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="dropdown-toggle">
                            <i class="fas fa-user"></i>
                            <?php echo $_SESSION['user_name']; ?>
                        </button>
                        <div class="dropdown-menu">
                            <a href="account.php">My Account</a>
                            <a href="orders.php">My Orders</a>
                            <a href="wishlist.php">Wishlist</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Login</a>
                    <a href="register.php" class="btn-register">Register</a>
                <?php endif; ?>

                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-count"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container">
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
                All Categories
            </button>

            <ul class="nav-links">
                <li><a href="deals.php">Today's Deals</a></li>
                <li><a href="new-arrivals.php">New Arrivals</a></li>
                <li><a href="best-sellers.php">Best Sellers</a></li>
                <li><a href="brands.php">Brands</a></li>
                <li><a href="clearance.php">Clearance</a></li>
            </ul>
        </div>
    </nav>

    <div class="mega-menu">
        <div class="container">
            <div class="category-columns">
                <?php
                $categories = getCategories($conn);
                $totalCategories = count($categories);
                $categoriesPerColumn = ceil($totalCategories / 4);
                
                for ($i = 0; $i < $totalCategories; $i += $categoriesPerColumn) {
                    echo '<div class="category-column">';
                    for ($j = $i; $j < min($i + $categoriesPerColumn, $totalCategories); $j++) {
                        $category = $categories[$j];
                        echo '<div class="category-item">';
                        echo '<h3>' . $category['name'] . '</h3>';
                        echo '<ul>';
                        // Get subcategories
                        $subcategories = getSubcategories($conn, $category['id']);
                        foreach ($subcategories as $sub) {
                            echo '<li><a href="category.php?id=' . $sub['id'] . '">' . $sub['name'] . '</a></li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</header>