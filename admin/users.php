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
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Base query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

// Apply filters
if ($search) {
    $query .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($role) {
    $query .= " AND role = ?";
    $params[] = $role;
}

if ($status) {
    $query .= " AND status = ?";
    $params[] = $status;
}

// Add sorting
$query .= " ORDER BY created_at DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Get user statistics
    $stmt = $conn->query("SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins,
        SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as total_customers,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
        COUNT(DISTINCT CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN id END) as active_last_month
        FROM users");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error fetching users: " . $e->getMessage();
    $users = [];
    $stats = [
        'total_users' => 0,
        'total_admins' => 0,
        'total_customers' => 0,
        'active_users' => 0,
        'inactive_users' => 0,
        'active_last_month' => 0
    ];
}

// Handle user status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'];
    
    if ($_POST['action'] === 'update_status') {
        $new_status = $_POST['status'];
        try {
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            $_SESSION['success_msg'] = "User status updated successfully! 🎉";
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Error updating user status: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete') {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success_msg'] = "User deleted successfully! 🗑️";
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Error deleting user: " . $e->getMessage();
        }
    }
    
    header('Location: users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - ZapMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInDown 0.5s ease;
        }

        .page-title {
            font-size: 1.8rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-emoji {
            font-size: 2.2rem;
            animation: wave 2s infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
        }

        .add-user-btn {
            padding: 12px 24px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .add-user-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.5s forwards;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

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
            animation: fadeInUp 0.5s 0.5s forwards;
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
            display: flex;
            align-items: center;
            gap: 5px;
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

        /* Users Table */
        .users-container {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            opacity: 0;
            animation: fadeInUp 0.5s 0.6s forwards;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table th {
            background: var(--light-bg);
            font-weight: 600;
            color: var(--text-color);
        }

        .users-table tbody tr {
            transition: all 0.3s ease;
            animation: fadeIn 0.5s forwards;
        }

        .users-table tbody tr:hover {
            background: var(--light-bg);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 3px;
        }

        .user-email {
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-active {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        .status-inactive {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .role-admin {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }

        .role-customer {
            background: rgba(241, 196, 15, 0.1);
            color: var(--warning-color);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: var(--light-bg);
            color: var(--text-color);
        }

        .btn-delete {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .btn:hover {
            transform: translateY(-2px);
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

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
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
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar (reuse the same sidebar from other admin pages) -->
    <div class="sidebar">
        <!-- ... Sidebar content ... -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-emoji">👥</span>
                Users Management
            </h1>
            <a href="add-user.php" class="add-user-btn">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">👥</span>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">👨‍💼</span>
                <div class="stat-value"><?php echo number_format($stats['total_admins']); ?></div>
                <div class="stat-label">Administrators</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🛍️</span>
                <div class="stat-value"><?php echo number_format($stats['total_customers']); ?></div>
                <div class="stat-label">Customers</div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">📊</span>
                <div class="stat-value"><?php echo number_format($stats['active_last_month']); ?></div>
                <div class="stat-label">Active Last Month</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-container">
            <form method="GET" class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-search"></i> Search Users
                    </label>
                    <input type="text" name="search" class="filter-input" 
                           placeholder="Name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-user-tag"></i> Role
                    </label>
                    <select name="role" class="filter-input">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        <option value="customer" <?php echo $role === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <select name="status" class="filter-input">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="filter-group" style="justify-content: flex-end;">
                    <button type="submit" class="btn btn-edit" style="margin-top: auto;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="users-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <img src="<?php echo !empty($user['avatar']) ? $user['avatar'] : '../assets/images/default-avatar.png'; ?>" 
                                         alt="User Avatar" class="user-avatar">
                                    <div>
                                        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <button class="btn btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Edit user function
        function editUser(userId) {
            window.location.href = `edit-user.php?id=${userId}`;
        }

        // Delete user function with confirmation
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="${userId}">
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