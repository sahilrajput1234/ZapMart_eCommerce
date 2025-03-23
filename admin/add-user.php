<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];
        $status = $_POST['status'];
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

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

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username or email already exists";
        }

        if (empty($errors)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, phone, address, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $email, $hashed_password, $role, $status, $phone, $address]);

            $_SESSION['success_msg'] = "User created successfully! 🎉";
            header('Location: users.php');
            exit;
        } else {
            $_SESSION['error_msg'] = implode("<br>", $errors);
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error creating user: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - ZapMart Admin</title>
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

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .strength-bar {
            height: 4px;
            flex: 1;
            background: var(--border-color);
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        .strength-text {
            color: var(--light-text);
            min-width: 80px;
            text-align: right;
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
                <span class="page-emoji">👤</span>
                Add New User
            </h1>
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
            <form method="POST" action="" id="add-user-form">
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
                                   minlength="3" placeholder="Enter username">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" required 
                                   placeholder="Enter email address">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="password-toggle">
                                <input type="password" name="password" class="form-input" required 
                                       minlength="6" placeholder="Enter password" id="password">
                                <button type="button" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-bar-fill"></div>
                                </div>
                                <span class="strength-text">Weak</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="password-toggle">
                                <input type="password" name="confirm_password" class="form-input" required 
                                       placeholder="Confirm password" id="confirm-password">
                                <button type="button" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
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
                                <option value="customer">Customer</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
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
                                   placeholder="Enter phone number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-input" rows="3" 
                                      placeholder="Enter address"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Create User
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

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.strength-bar-fill');
        const strengthText = document.querySelector('.strength-text');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let color = '';
            let text = '';

            // Length check
            if (password.length >= 8) strength += 25;
            // Uppercase check
            if (password.match(/[A-Z]/)) strength += 25;
            // Lowercase check
            if (password.match(/[a-z]/)) strength += 25;
            // Number check
            if (password.match(/[0-9]/)) strength += 25;

            if (strength <= 25) {
                color = '#e74c3c';
                text = 'Weak';
            } else if (strength <= 50) {
                color = '#f1c40f';
                text = 'Fair';
            } else if (strength <= 75) {
                color = '#3498db';
                text = 'Good';
            } else {
                color = '#2ecc71';
                text = 'Strong';
            }

            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        });

        // Form validation
        const form = document.getElementById('add-user-form');
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
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