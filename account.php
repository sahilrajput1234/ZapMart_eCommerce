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

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent orders
$stmt = $conn->prepare("
    SELECT o.*, COUNT(oi.order_id) as total_items 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = :user_id 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle profile update
$updateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        $stmt = $conn->prepare("
            UPDATE users 
            SET username = :username, email = :email, phone = :phone 
            WHERE id = :user_id
        ");
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':user_id', $userId);
        
        if ($stmt->execute()) {
            $updateMessage = 'Profile updated successfully!';
            // Update session data
            $_SESSION['user_name'] = $username;
            $_SESSION['user_email'] = $email;
        } else {
            $updateMessage = 'Error updating profile.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - ZapMart</title>
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

        .account-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .account-header {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .account-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .account-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .account-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        /* Sidebar */
        .account-sidebar {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateX(-20px);
            animation: fadeInLeft 0.6s forwards 0.2s;
        }

        .user-info {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .user-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .user-email {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .account-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .account-menu li {
            margin-bottom: 10px;
        }

        .account-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .account-menu a:hover {
            background: var(--light-bg);
            color: var(--primary-color);
        }

        .account-menu a.active {
            background: var(--primary-color);
            color: var(--white);
        }

        .account-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .account-content {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards 0.4s;
        }

        .account-card {
            background: var(--white);
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h2 {
            font-size: 1.5rem;
            color: var(--text-color);
            margin: 0;
        }

        .card-header .btn {
            padding: 8px 15px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .card-header .btn:hover {
            background: var(--primary-dark);
        }

        /* Profile Form */
        .profile-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: grid;
            gap: 8px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control {
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        /* Recent Orders */
        .order-list {
            display: grid;
            gap: 15px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: var(--light-bg);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .order-info {
            flex: 1;
        }

        .order-id {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .order-date {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .order-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Success Message */
        .success-message {
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid var(--secondary-color);
            color: var(--secondary-color);
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            animation: slideInDown 0.4s;
        }

        .success-message i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 992px) {
            .account-grid {
                grid-template-columns: 1fr;
            }

            .account-sidebar {
                position: sticky;
                top: 20px;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-status {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="account-container">
        <div class="account-header">
            <h1>My Account</h1>
            <p>Manage your profile and view your orders</p>
        </div>

        <div class="account-grid">
            <!-- Sidebar -->
            <div class="account-sidebar">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <ul class="account-menu">
                    <li><a href="#profile" class="active"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a></li>
                    <li><a href="#wishlist"><i class="fas fa-heart"></i> Wishlist</a></li>
                    <li><a href="#addresses"><i class="fas fa-map-marker-alt"></i> Addresses</a></li>
                    <li><a href="#settings"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php" style="color: var(--danger-color);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="account-content">
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-value"><?php echo count($recentOrders); ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-value">0</div>
                        <div class="stat-label">Wishlist Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="stat-value">0</div>
                        <div class="stat-label">Saved Addresses</div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="account-card">
                    <div class="card-header">
                        <h2>Profile Information</h2>
                    </div>

                    <?php if ($updateMessage): ?>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $updateMessage; ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="account.php" method="post" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn">Update Profile</button>
                    </form>
                </div>

                <!-- Recent Orders -->
                <div class="account-card">
                    <div class="card-header">
                        <h2>Recent Orders</h2>
                        <a href="orders.php" class="btn">View All</a>
                    </div>

                    <div class="order-list">
                        <?php if (!empty($recentOrders)): ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <div class="order-id">Order #<?php echo $order['id']; ?></div>
                                        <div class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                                    </div>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No orders found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menu item active state
            const menuItems = document.querySelectorAll('.account-menu a');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Form validation
            const profileForm = document.querySelector('.profile-form');
            profileForm.addEventListener('submit', function(e) {
                const usernameInput = this.querySelector('input[name="username"]');
                const emailInput = this.querySelector('input[name="email"]');
                
                if (!usernameInput.value.trim() || !emailInput.value.trim()) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        });
    </script>
</body>
</html>