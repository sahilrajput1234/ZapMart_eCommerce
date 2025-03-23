<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error_msg'] = "User not found! 😕";
        header('Location: users.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error fetching user: " . $e->getMessage();
    header('Location: users.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $status = $_POST['status'];
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validate input
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long";
        }

        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Check if username or email already exists (excluding current user)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username or email already exists";
        }

        if (empty($errors)) {
            // Update user data
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ?, 
                                      status = ?, phone = ?, address = ?, updated_at = NOW() 
                                      WHERE id = ?");
                $stmt->execute([$username, $email, $hashed_password, $role, $status, $phone, $address, $user_id]);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, 
                                      status = ?, phone = ?, address = ?, updated_at = NOW() 
                                      WHERE id = ?");
                $stmt->execute([$username, $email, $role, $status, $phone, $address, $user_id]);
            }

            $_SESSION['success_msg'] = "User updated successfully! 🎉";
            header('Location: users.php');
            exit;
        } else {
            $_SESSION['error_msg'] = implode("<br>", $errors);
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error updating user: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - ZapMart Admin</title>
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

        .user-info {
            background: var(--white);
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.5s ease;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .user-details h3 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .user-details p {
            margin: 5px 0 0;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
        }

        /* Form Container */
        .form-container {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .section-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            animation: bounce 2s infinite;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .form-input {
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--light-text);
            cursor: pointer;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: var(--text-color);
        }

        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Action Buttons */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--light-bg);
            color: var(--text-color);
        }

        .btn-danger {
            background: var(--danger-color);
            color: var(--white);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        /* Alert Messages */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            color: var(--white);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background: var(--success-color);
        }

        .alert-error {
            background: var(--danger-color);
        }

        /* Password Change Section */
        .password-change {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .password-change-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .password-fields {
            display: none;
        }

        .password-fields.show {
            display: block;
            animation: fadeIn 0.3s ease;
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

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .user-info {
                flex-direction: column;
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
                <span class="page-emoji">✏️</span>
                Edit User
            </h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($user['username'], 0, 1); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
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

        <div class="form-container">
            <form method="POST" action="" id="edit-user-form">
                <!-- Account Information -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">👤</span>
                        <h2 class="section-title">Account Information</h2>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-input" required 
                                   minlength="3" value="<?php echo htmlspecialchars($user['username']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Role & Status -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">🔑</span>
                        <h2 class="section-title">Role & Status</h2>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">User Role</label>
                            <select name="role" class="form-select" required>
                                <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>
                                    Customer
                                </option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                    Administrator
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>
                                    Active
                                </option>
                                <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="section-icon">📞</span>
                        <h2 class="section-title">Contact Information</h2>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-input" rows="3"><?php 
                                echo htmlspecialchars($user['address'] ?? ''); 
                            ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Password Change Section -->
                <div class="form-section">
                    <div class="password-change">
                        <div class="password-change-toggle" id="togglePassword">
                            <i class="fas fa-key"></i>
                            <span>Change Password</span>
                        </div>
                        <div class="password-fields" id="passwordFields">
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <div class="password-toggle">
                                    <input type="password" name="password" class="form-input" 
                                           minlength="6" placeholder="Enter new password">
                                    <button type="button" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password change toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordFields = document.getElementById('passwordFields');

        togglePassword.addEventListener('click', () => {
            passwordFields.classList.toggle('show');
            const icon = togglePassword.querySelector('i');
            if (passwordFields.classList.contains('show')) {
                icon.classList.remove('fa-key');
                icon.classList.add('fa-lock-open');
            } else {
                icon.classList.remove('fa-lock-open');
                icon.classList.add('fa-key');
            }
        });

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Form validation
        const form = document.getElementById('edit-user-form');
        form.addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            
            if (password && password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
            }
        });
    </script>
</body>
</html> 