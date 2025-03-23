<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar">
    <div class="sidebar-logo">
        <h1><i class="fas fa-store"></i>ZapMart<span>Admin</span></h1>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="products.php" class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Products
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
        </li>
        <li>
            <a href="brands.php" class="<?php echo $current_page === 'brands.php' ? 'active' : ''; ?>">
                <i class="fas fa-copyright"></i> Brands
            </a>
        </li>
        <li>
            <a href="orders.php" class="<?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
        </li>
        <li>
            <a href="payment-methods.php" class="<?php echo $current_page === 'payment-methods.php' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> Payment Methods
            </a>
        </li>
        <li>
            <a href="users.php" class="<?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>