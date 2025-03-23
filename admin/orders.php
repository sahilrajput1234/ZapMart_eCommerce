<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT o.*, u.username, u.email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE 1=1";
$params = [];

// Apply filters
if ($status) {
    $query .= " AND o.status = ?";
    $params[] = $status;
}

if ($date_range) {
    $dates = explode(' - ', $date_range);
    if (count($dates) == 2) {
        $query .= " AND DATE(o.created_at) BETWEEN ? AND ?";
        $params[] = trim($dates[0]);
        $params[] = trim($dates[1]);
    }
}

if ($search) {
    $query .= " AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Add sorting
$query .= " ORDER BY o.created_at DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Get order statistics
    $stats = [
        'total' => 0,
        'pending' => 0,
        'processing' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'revenue' => 0
    ];

    $stmt = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as revenue
        FROM orders");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error fetching orders: " . $e->getMessage();
    $orders = [];
    $stats = [
        'total' => 0,
        'pending' => 0,
        'processing' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'revenue' => 0
    ];
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $_SESSION['success_msg'] = "Order status updated successfully! 🎉";
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error updating order status: " . $e->getMessage();
    }
    
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - ZapMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --success-color: #2ecc71;
            --text-color: #2c3e50;
            --light-text: #95a5a6;
            --border-color: #ecf0f1;
            --light-bg: #f9fafb;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.6s forwards;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: inline-block;
            animation: bounce 2s infinite;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--text-color);
            margin: 10px 0;
        }

        .stat-label {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Filters */
        .filters-container {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0;
            animation: fadeInUp 0.6s 0.6s forwards;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-bottom: 8px;
        }

        .filter-input {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Orders Table */
        .orders-container {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            opacity: 0;
            animation: fadeInUp 0.6s 0.7s forwards;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table th {
            background: var(--light-bg);
            font-weight: 600;
            color: var(--text-color);
        }

        .orders-table tbody tr {
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }

        .orders-table tbody tr:nth-child(1) { animation-delay: 0.8s; }
        .orders-table tbody tr:nth-child(2) { animation-delay: 0.9s; }
        .orders-table tbody tr:nth-child(3) { animation-delay: 1.0s; }
        .orders-table tbody tr:nth-child(4) { animation-delay: 1.1s; }
        .orders-table tbody tr:nth-child(5) { animation-delay: 1.2s; }

        .orders-table tbody tr:hover {
            background: var(--light-bg);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: rgba(241, 196, 15, 0.1);
            color: var(--warning-color);
        }

        .status-processing {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info-color);
        }

        .status-completed {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        .status-cancelled {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--light-bg);
            color: var(--text-color);
        }

        .action-btn:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .orders-table {
                font-size: 0.9rem;
            }

            .status-badge {
                padding: 4px 8px;
            }
        }

        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar (reuse the same sidebar from add-product.php) -->
    <div class="sidebar">
        <!-- ... Sidebar content ... -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-emoji">📦</span>
                Orders Management
            </h1>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <span class="stat-icon">📊</span>
                <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⏳</span>
                <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🔄</span>
                <div class="stat-value"><?php echo number_format($stats['processing']); ?></div>
                <div class="stat-label">Processing</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">✅</span>
                <div class="stat-value"><?php echo number_format($stats['completed']); ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">💰</span>
                <div class="stat-value">₹<?php echo number_format($stats['revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-container">
            <form method="GET" class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Search 🔍</label>
                    <input type="text" name="search" class="filter-input" 
                           placeholder="Order #, customer..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status 📋</label>
                    <select name="status" class="filter-input">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Date Range 📅</label>
                    <input type="text" name="date_range" class="filter-input" id="dateRange"
                           placeholder="Select date range" value="<?php echo htmlspecialchars($date_range); ?>">
                </div>
                <div class="filter-group" style="justify-content: flex-end;">
                    <button type="submit" class="action-btn" style="margin-top: auto;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="orders-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['username']); ?><br>
                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="action-btn" onclick="updateStatus(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Update Modal (will be implemented with JavaScript) -->
    <div id="statusModal" class="modal" style="display: none;">
        <!-- Modal content will be added dynamically -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        // Initialize date range picker
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // View order details
        function viewOrder(orderId) {
            // Implement view order functionality
            window.location.href = `view-order.php?id=${orderId}`;
        }

        // Update order status
        function updateStatus(orderId) {
            // Implement status update modal
            const newStatus = prompt('Enter new status (pending/processing/completed/cancelled):');
            if (newStatus) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="${orderId}">
                    <input type="hidden" name="status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

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