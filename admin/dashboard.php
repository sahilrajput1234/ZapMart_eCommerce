<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get database statistics
try {
    // Total products
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $totalProducts = $stmt->fetchColumn();

    // Total categories
    $stmt = $conn->query("SELECT COUNT(*) FROM categories");
    $totalCategories = $stmt->fetchColumn();

    // Total users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();

    // Total orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders");
    $totalOrders = $stmt->fetchColumn();

    // Recent products
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
    $recentProducts = $stmt->fetchAll();

    // Low stock products (less than 10 items)
    $stmt = $conn->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
    $lowStockProducts = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ZapMart</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f6fa;
            --border-color: #dcdde1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-bg);
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
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 3px solid var(--border-color);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        .page-loader.hide {
            opacity: 0;
            pointer-events: none;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 20px 0;
        }

        .sidebar-header {
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 10px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--light-bg);
            color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .welcome-text h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--text-color);
        }

        .welcome-text p {
            margin: 5px 0 0;
            color: #666;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .stat-title {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-color);
            margin: 0;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .action-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-color);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .action-title {
            font-size: 1.1rem;
            margin: 0;
        }

        /* Recent Items */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }

        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            background: var(--primary-color);
            color: white;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .card-body {
            padding: 20px;
        }

        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .item-list li {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-list li:last-child {
            border-bottom: none;
        }

        .item-name {
            color: var(--text-color);
            text-decoration: none;
        }

        .item-name:hover {
            color: var(--primary-color);
        }

        .item-stock {
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .stock-low {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .stock-medium {
            background: rgba(241, 196, 15, 0.1);
            color: var(--warning-color);
        }

        .stock-good {
            background: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 10px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader"></div>
    </div>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <i class="fas fa-bolt"></i>
                    ZapMart Admin
                </a>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link">
                        <i class="fas fa-box"></i>
                        Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="categories.php" class="nav-link">
                        <i class="fas fa-tags"></i>
                        Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="orders.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <div class="welcome-text">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! 👋</h1>
                    <p>Here's what's happening with your store today.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <h3 class="stat-title">Total Products</h3>
                        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: var(--primary-color);">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo number_format($totalProducts); ?></p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h3 class="stat-title">Categories</h3>
                        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: var(--secondary-color);">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo number_format($totalCategories); ?></p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h3 class="stat-title">Total Users</h3>
                        <div class="stat-icon" style="background: rgba(241, 196, 15, 0.1); color: var(--warning-color);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo number_format($totalUsers); ?></p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h3 class="stat-title">Total Orders</h3>
                        <div class="stat-icon" style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color);">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo number_format($totalOrders); ?></p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="add-product.php" class="action-card">
                    <i class="fas fa-plus-circle action-icon"></i>
                    <h3 class="action-title">Add Product</h3>
                </a>
                <a href="add-category.php" class="action-card">
                    <i class="fas fa-folder-plus action-icon"></i>
                    <h3 class="action-title">Add Category</h3>
                </a>
                <a href="orders.php" class="action-card">
                    <i class="fas fa-clipboard-list action-icon"></i>
                    <h3 class="action-title">View Orders</h3>
                </a>
                <a href="users.php" class="action-card">
                    <i class="fas fa-user-plus action-icon"></i>
                    <h3 class="action-title">Manage Users</h3>
                </a>
            </div>

            <!-- Recent Items Grid -->
            <div class="dashboard-grid">
                <!-- Recent Products -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-clock"></i> Recent Products</h2>
                    </div>
                    <div class="card-body">
                        <ul class="item-list">
                            <?php foreach ($recentProducts as $product): ?>
                                <li>
                                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="item-name">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                    <span class="item-stock <?php 
                                        if ($product['stock'] < 5) echo 'stock-low';
                                        elseif ($product['stock'] < 10) echo 'stock-medium';
                                        else echo 'stock-good';
                                    ?>">
                                        Stock: <?php echo $product['stock']; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h2>
                    </div>
                    <div class="card-body">
                        <ul class="item-list">
                            <?php foreach ($lowStockProducts as $product): ?>
                                <li>
                                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="item-name">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                    <span class="item-stock stock-low">
                                        Stock: <?php echo $product['stock']; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Page Loader
        window.addEventListener('load', () => {
            document.querySelector('.page-loader').classList.add('hide');
        });
    </script>
</body>
</html> 