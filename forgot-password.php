<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
            $stmt->execute([$token, $expires, $email]);
            
            // Send reset email (you'll need to implement this)
            // sendPasswordResetEmail($email, $token);
            
            $message = "If an account exists with this email, you will receive password reset instructions.";
            $messageType = "success";
        } else {
            // For security, don't reveal if email exists or not
            $message = "If an account exists with this email, you will receive password reset instructions.";
            $messageType = "success";
        }
    } else {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ZapMart</title>
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

        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
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

        .loader-container {
            text-align: center;
            position: relative;
        }

        .loader {
            width: 100px;
            height: 100px;
            position: relative;
            margin-bottom: 20px;
        }

        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1.5s infinite linear;
        }

        .loader-icons {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            gap: 8px;
        }

        .reset-icon {
            font-size: 1.8rem;
            opacity: 0;
            transform: scale(0.5);
            animation: popIn 3s infinite;
        }

        .reset-icon:nth-child(1) { animation-delay: 0s; }
        .reset-icon:nth-child(2) { animation-delay: 0.5s; }
        .reset-icon:nth-child(3) { animation-delay: 1s; }
        .reset-icon:nth-child(4) { animation-delay: 1.5s; }

        .loader-text {
            color: var(--text-color);
            font-size: 1.1rem;
            margin-top: 20px;
            opacity: 0;
            animation: fadeInOut 2s infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes popIn {
            0%, 45% { 
                transform: scale(0.5);
                opacity: 0;
            }
            50% { 
                transform: scale(1.2);
                opacity: 1;
            }
            55%, 100% { 
                transform: scale(1);
                opacity: 0;
            }
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        /* Forgot Password Page Styles */
        .forgot-password-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 40px 20px;
        }

        .forgot-password-card {
            background: var(--white);
            border-radius: 15px;
            padding: 40px;
            box-shadow: var(--shadow);
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .forgot-password-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .forgot-password-title {
            font-size: 2rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .forgot-password-subtitle {
            color: var(--light-text);
            margin-bottom: 30px;
        }

        .forgot-password-form {
            max-width: 400px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .submit-btn {
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
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .back-to-login {
            display: block;
            margin-top: 20px;
            color: var(--light-text);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-to-login:hover {
            color: var(--primary-color);
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
        }

        .message.error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .forgot-password-card {
                padding: 30px 20px;
            }

            .forgot-password-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader-container">
            <div class="loader">
                <div class="loader-circle"></div>
                <div class="loader-icons">
                    <span class="reset-icon">🔑</span>
                    <span class="reset-icon">📧</span>
                    <span class="reset-icon">🔄</span>
                    <span class="reset-icon">✨</span>
                </div>
            </div>
            <div class="loader-text">Processing your request...</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="forgot-password-container">
        <div class="forgot-password-card">
            <div class="forgot-password-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1 class="forgot-password-title">Forgot Password?</h1>
            <p class="forgot-password-subtitle">Enter your email address and we'll send you instructions to reset your password.</p>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="forgot-password.php" method="POST" class="forgot-password-form">
                <div class="form-group">
                    <input type="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="Enter your email address"
                           required>
                </div>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>

            <a href="login.php" class="back-to-login">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
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
                }, 1500);
            });

            // Show loader on form submission
            const form = document.querySelector('.forgot-password-form');
            form.addEventListener('submit', function() {
                pageLoader.classList.remove('hide');
            });
        });
    </script>
</body>
</html> 