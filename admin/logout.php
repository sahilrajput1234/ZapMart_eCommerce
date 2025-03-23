<?php
session_start();

// Check if the user confirmed the logout
if (isset($_POST['confirm_logout'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page after a short delay
    header("Refresh: 2; URL=login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - ZapMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f6fa;
            --border-color: #dcdde1;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
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
            transition: opacity 0.5s ease-out;
        }

        .loader {
            width: 60px;
            height: 60px;
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

        .loader-emoji {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            animation: wave 1.5s infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @keyframes wave {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
            50% { transform: translate(-50%, -50%) rotate(15deg); }
        }

        .page-loader.hide {
            opacity: 0;
            pointer-events: none;
        }

        /* Logout Container */
        .logout-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logout-emoji {
            font-size: 48px;
            margin-bottom: 20px;
            display: inline-block;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }

        .logout-title {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .logout-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .buttons-container {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f1f2f6;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: #dcdde1;
            transform: translateY(-2px);
        }

        /* Success Message */
        .success-message {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s ease;
        }

        .success-message.show {
            opacity: 1;
            visibility: visible;
        }

        .success-emoji {
            font-size: 64px;
            margin-bottom: 20px;
            animation: successBounce 1s;
        }

        @keyframes successBounce {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .redirect-message {
            color: #666;
            margin-top: 15px;
            font-size: 14px;
        }

        .redirect-dots {
            display: inline-block;
            width: 12px;
            animation: dots 1.5s infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }

        /* Decorative Elements */
        .decoration {
            position: absolute;
            border-radius: 50%;
            background: var(--primary-color);
            opacity: 0.1;
            z-index: -1;
        }

        .decoration-1 {
            width: 100px;
            height: 100px;
            top: -20px;
            left: -20px;
        }

        .decoration-2 {
            width: 80px;
            height: 80px;
            bottom: -10px;
            right: -10px;
            background: var(--secondary-color);
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .logout-container {
                margin: 20px;
                padding: 30px;
            }

            .buttons-container {
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
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-emoji">👋</div>
        </div>
    </div>

    <div class="logout-container">
        <!-- Decorative Elements -->
        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>

        <?php if (!isset($_POST['confirm_logout'])): ?>
            <!-- Logout Confirmation -->
            <div class="logout-emoji">👋</div>
            <h1 class="logout-title">Leaving So Soon?</h1>
            <p class="logout-message">
                Are you sure you want to log out of your admin account? 
                You'll need to log back in to access the dashboard.
            </p>
            <div class="buttons-container">
                <form method="post">
                    <button type="submit" name="confirm_logout" class="btn btn-primary">
                        <i class="fas fa-sign-out-alt"></i> Yes, Log Out
                    </button>
                </form>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        <?php else: ?>
            <!-- Success Message -->
            <div class="success-message show">
                <div class="success-emoji">✨</div>
                <h2 class="logout-title">Successfully Logged Out!</h2>
                <p class="logout-message">Thank you for using ZapMart Admin Panel</p>
                <p class="redirect-message">
                    Redirecting to login page<span class="redirect-dots"></span>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Page Loader
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.querySelector('.page-loader').classList.add('hide');
            }, 800);
        });

        // Animate dots for redirect message
        if (document.querySelector('.redirect-dots')) {
            let dots = '';
            setInterval(() => {
                dots = dots.length >= 3 ? '' : dots + '.';
                document.querySelector('.redirect-dots').textContent = dots;
            }, 500);
        }

        // Button hover effect
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('mouseover', () => {
                button.style.transform = 'translateY(-2px)';
            });
            
            button.addEventListener('mouseout', () => {
                button.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html> 