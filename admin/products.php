<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = (int)$_POST['product_id'];
    try {
        // Delete product images first
        $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        $_SESSION['success_msg'] = "Product deleted successfully!";
        header('Location: products.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error deleting product: " . $e->getMessage();
    }
}

// Get all products with their categories
try {
    $stmt = $conn->query("
        SELECT p.*, c.name as category_name, 
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as main_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error fetching products: " . $e->getMessage();
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Admin Dashboard</title>
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

        /* Sidebar styles from dashboard.php */
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
            background: var(--light-bg);
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

        .page-title {
            font-size: 1.5rem;
            margin: 0;
            color: var(--text-color);
        }

        .add-product-btn {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .add-product-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        /* Products Table */
        .products-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
            min-height: 300px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: var(--light-bg);
            font-weight: 600;
            color: var(--text-color);
        }

        tbody tr:hover {
            background: var(--light-bg);
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
        }

        .product-name {
            color: var(--text-color);
            font-weight: 500;
            text-decoration: none;
        }

        .product-name:hover {
            color: var(--primary-color);
        }

        .product-category {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .product-price {
            font-weight: 600;
            color: var(--text-color);
        }

        .stock-status {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .in-stock {
            background: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }

        .low-stock {
            background: rgba(241, 196, 15, 0.1);
            color: var(--warning-color);
        }

        .out-of-stock {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .btn-edit:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-delete {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .btn-delete:hover {
            background: var(--danger-color);
            color: white;
        }

        .btn-images {
            background: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }

        .btn-images:hover {
            background: var(--secondary-color);
            color: white;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            margin: 0 0 10px;
            color: var(--text-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 10px;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link active">
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
                <h1 class="page-title">Products Management</h1>
                <a href="add-product.php" class="add-product-btn">
                    <i class="fas fa-plus"></i>
                    Add New Product
                </a>
            </div>

            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                        echo $_SESSION['success_msg'];
                        unset($_SESSION['success_msg']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                        echo $_SESSION['error_msg'];
                        unset($_SESSION['error_msg']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="products-table">
                <div class="table-responsive">
                    <?php if (!empty($products)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo !empty($product['main_image']) ? '../' . $product['main_image'] : '../assets/images/products/placeholder.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 class="product-image">
                                        </td>
                                        <td>
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="product-name">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="product-category">
                                                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="product-price">
                                                ₹<?php echo number_format($product['price'], 2); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $product['stock']; ?></td>
                                        <td>
                                            <?php if ($product['stock'] > 10): ?>
                                                <span class="stock-status in-stock">In Stock</span>
                                            <?php elseif ($product['stock'] > 0): ?>
                                                <span class="stock-status low-stock">Low Stock</span>
                                            <?php else: ?>
                                                <span class="stock-status out-of-stock">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="add-product-images.php?id=<?php echo $product['id']; ?>" class="btn btn-images">
                                                    <i class="fas fa-images"></i> Images
                                                </a>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" class="btn btn-delete">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h3>No Products Found</h3>
                            <p>Start by adding your first product to the store.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Page Loader
        window.addEventListener('load', () => {
            document.querySelector('.page-loader').classList.add('hide');
        });

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>