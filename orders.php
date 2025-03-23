<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get all orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total orders count
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$totalOrders = $stmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Get orders for current page
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.order_id) as total_items,
           SUM(oi.quantity * oi.price) as order_total
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = :user_id 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
    LIMIT :offset, :per_page
");
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $perPage, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - ZapMart</title>
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

        .orders-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
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

        .orders-grid {
            display: grid;
            gap: 20px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards 0.2s;
        }

        .order-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
        }

        .order-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-id {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .order-date {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .order-body {
            padding: 20px;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-group {
            padding: 15px;
            background: var(--light-bg);
            border-radius: 10px;
        }

        .info-label {
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--text-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .order-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.1);
            color: var(--accent-color);
        }

        .status-processing {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .status-completed {
            background: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }

        .status-cancelled {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-details-btn {
            background: var(--primary-color);
            color: var(--white);
        }

        .view-details-btn:hover {
            background: var(--primary-dark);
        }

        .track-order-btn {
            background: var(--light-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .track-order-btn:hover {
            background: var(--border-color);
        }

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

        .no-orders {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .no-orders i {
            font-size: 4rem;
            color: var(--lighter-text);
            margin-bottom: 20px;
        }

        .no-orders h2 {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .no-orders p {
            color: var(--light-text);
            margin-bottom: 20px;
        }

        .shop-now-btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .shop-now-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-actions {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="orders-container">
        <div class="page-title">
            <h1>My Orders</h1>
            <p>View and track all your orders</p>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="orders-grid">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                            <div class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="order-body">
                            <div class="order-info">
                                <div class="info-group">
                                    <div class="info-label">Total Items</div>
                                    <div class="info-value"><?php echo $order['total_items']; ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Order Total</div>
                                    <div class="info-value">₹<?php echo number_format($order['order_total'], 2); ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Status</div>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="order-actions">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="action-btn view-details-btn">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="track-order.php?id=<?php echo $order['id']; ?>" class="action-btn track-order-btn">
                                    <i class="fas fa-truck"></i> Track Order
                                </a>
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
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
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
            <div class="no-orders">
                <i class="fas fa-box-open"></i>
                <h2>No Orders Yet</h2>
                <p>Looks like you haven't placed any orders yet.</p>
                <a href="products.php" class="shop-now-btn">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation on scroll
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.order-card');
                
                elements.forEach(element => {
                    const position = element.getBoundingClientRect();
                    
                    // Check if element is in viewport
                    if (position.top < window.innerHeight && position.bottom >= 0) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Initialize
        });
    </script>
</body>
</html> 