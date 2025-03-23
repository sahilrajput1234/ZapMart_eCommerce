<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$username = '';
$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check login credentials
        $sql = "SELECT * FROM users WHERE (username = :username OR email = :email)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            
            // Remember me functionality
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 days
                
                // Store token in database
                $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, FROM_UNIXTIME(:expires))");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':expires', $expires);
                $stmt->execute();
                
                // Set cookie
                setcookie('remember_token', $token, $expires, '/');
            }
            
            // Redirect to homepage or requested page
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']);
            
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ZapMart</title>
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
            --input-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .page-loader.hide {
            opacity: 0;
            visibility: hidden;
        }

        .loader {
            width: 70px;
            height: 70px;
            position: relative;
        }

        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        .loader-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30px;
            animation: pulse 1.5s infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.8); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
            100% { transform: translate(-50%, -50%) scale(0.8); }
        }

        /* Login Container */
        .login-container {
            max-width: 1000px;
            width: 100%;
            margin: 40px auto;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            display: flex;
            min-height: calc(100vh - 220px);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards 0.4s;
        }

        /* Login Sidebar */
        .login-sidebar {
            width: 45%;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: var(--white);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 90%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.1) 4%, transparent 4%),
                radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.1) 4%, transparent 4%),
                radial-gradient(circle at 40% 30%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.1) 6%, transparent 6%),
                radial-gradient(circle at 70% 40%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.1) 5%, transparent 5%),
                radial-gradient(circle at 90% 70%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.1) 4%, transparent 4%);
            opacity: 0;
            animation: fadeIn 1s forwards 0.8s;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .brand-logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .brand-logo span {
            margin-left: 10px;
            letter-spacing: 1px;
        }

        .welcome-text {
            margin-bottom: 30px;
        }

        .welcome-text h2 {
            font-size: 2.2rem;
            margin-bottom: 15px;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.6s forwards 0.6s;
        }

        .welcome-text p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.8;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.6s forwards 0.8s;
        }

        .login-features {
            margin-top: 40px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateX(-10px);
            animation: fadeInLeft 0.5s forwards;
        }

        .feature-item:nth-child(1) { animation-delay: 1.0s; }
        .feature-item:nth-child(2) { animation-delay: 1.2s; }
        .feature-item:nth-child(3) { animation-delay: 1.4s; }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }

        .feature-text {
            font-size: 1rem;
        }

        .feature-text strong {
            display: block;
            margin-bottom: 3px;
        }

        .feature-text span {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Login Form */
        .login-form-container {
            width: 55%;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 2rem;
            color: var(--text-color);
            margin-bottom: 10px;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.6s forwards 0.5s;
        }

        .login-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.6s forwards 0.7s;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.5s forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.9s; }
        .form-group:nth-child(2) { animation-delay: 1.1s; }
        .form-group:nth-child(3) { animation-delay: 1.3s; }
        .form-group:nth-child(4) { animation-delay: 1.5s; }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: var(--input-shadow);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .form-control.error {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 45px;
            color: var(--lighter-text);
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .form-error {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
            animation: shakeError 0.6s;
        }

        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
            accent-color: var(--primary-color);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            margin-bottom: 25px;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.6s forwards 1.7s;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            opacity: 0;
            animation: fadeIn 0.6s forwards 1.9s;
        }

        .divider span {
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .divider-text {
            padding: 0 15px;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
            opacity: 0;
            animation: fadeIn 0.6s forwards 2.1s;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--input-shadow);
        }

        .social-btn.facebook {
            background: #3b5998;
        }

        .social-btn.google {
            background: #db4437;
        }

        .social-btn.twitter {
            background: #1da1f2;
        }

        .social-btn.apple {
            background: #000000;
        }

        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .register-link {
            text-align: center;
            margin-top: 10px;
            font-size: 0.95rem;
            color: var(--light-text);
            opacity: 0;
            animation: fadeIn 0.6s forwards 2.3s;
        }

        .register-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Error Alert */
        .error-alert {
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid var(--danger-color);
            color: var(--danger-color);
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            animation: slideInDown 0.4s;
            opacity: 0;
            transform: translateY(-10px);
            animation: fadeInDown 0.4s forwards;
        }

        .error-alert i {
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

        @keyframes fadeInDown {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .login-container {
                max-width: 95%;
                margin: 20px auto;
                flex-direction: column;
                min-height: auto;
            }

            .login-sidebar {
                width: 100%;
                padding: 30px;
            }

            .login-form-container {
                width: 100%;
                padding: 30px;
            }
        }

        @media (max-width: 576px) {
            .login-container {
                margin: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .login-sidebar {
                padding: 20px;
            }

            .login-form-container {
                padding: 20px;
            }

            .social-login {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-icon">🔐</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="login-container">
        <div class="login-sidebar">
            <div class="brand-logo">
                <i class="fas fa-shopping-bag"></i>
                <span>ZapMart</span>
            </div>
            <div class="welcome-text">
                <h2>Welcome Back!</h2>
                <p>Log in to access your account, track orders, and enjoy a personalized shopping experience.</p>
            </div>
            <div class="login-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="feature-text">
                        <strong>Order Tracking</strong>
                        <span>Track your orders in real-time</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="feature-text">
                        <strong>Exclusive Offers</strong>
                        <span>Access to member-only deals</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="feature-text">
                        <strong>Wishlist</strong>
                        <span>Save your favorite products</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-form-container">
            <div class="login-header">
                <h1>Login to Your Account</h1>
                <p>Enter your credentials to access your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post" id="login-form">
                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                    <span class="form-error">Please enter a valid username or email</span>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <span class="password-toggle" id="password-toggle"><i class="fas fa-eye"></i></span>
                    <span class="form-error">Please enter your password</span>
                </div>
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        Remember me
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-login">Login <i class="fas fa-arrow-right"></i></button>
            </form>

            <div class="divider">
                <span></span>
                <div class="divider-text">Or login with</div>
                <span></span>
            </div>

            <div class="social-login">
                <div class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                </div>
                <div class="social-btn google">
                    <i class="fab fa-google"></i>
                </div>
                <div class="social-btn twitter">
                    <i class="fab fa-twitter"></i>
                </div>
                <div class="social-btn apple">
                    <i class="fab fa-apple"></i>
                </div>
            </div>

            <div class="register-link">
                Don't have an account? <a href="register.php">Register Now</a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page Loader
            const pageLoader = document.querySelector('.page-loader');
            window.addEventListener('load', function() {
                setTimeout(function() {
                    pageLoader.classList.add('hide');
                }, 800);
            });
            
            // Password Toggle
            const passwordToggle = document.getElementById('password-toggle');
            const passwordInput = document.getElementById('password');
            
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Change the icon
                const icon = passwordToggle.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
            
            // Form Validation
            const loginForm = document.getElementById('login-form');
            const usernameInput = document.getElementById('username');
            const usernameError = usernameInput.nextElementSibling;
            const passwordError = passwordInput.nextElementSibling.nextElementSibling;
            
            loginForm.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset errors
                usernameInput.classList.remove('error');
                passwordInput.classList.remove('error');
                usernameError.style.display = 'none';
                passwordError.style.display = 'none';
                
                // Validate username
                if (usernameInput.value.trim() === '') {
                    usernameInput.classList.add('error');
                    usernameError.style.display = 'block';
                    isValid = false;
                }
                
                // Validate password
                if (passwordInput.value.trim() === '') {
                    passwordInput.classList.add('error');
                    passwordError.style.display = 'block';
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Input animations
            const formControls = document.querySelectorAll('.form-control');
            
            formControls.forEach(input => {
                // Add focus effect
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('.form-label').style.color = 'var(--primary-color)';
                });
                
                // Remove focus effect
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('.form-label').style.color = 'var(--text-color)';
                });
            });
            
            // Social login buttons
            const socialButtons = document.querySelectorAll('.social-btn');
            
            socialButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // This would typically authenticate with the social provider
                    // For now, just show a notification
                    alert(`${this.className.split(' ')[1]} login is not implemented yet.`);
                });
            });
        });
    </script>
</body>
</html>