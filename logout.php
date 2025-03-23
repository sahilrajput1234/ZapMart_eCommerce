<?php
session_start();

// Clear the remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    require_once 'config/database.php';
    
    // Clear the remember token from database if user is logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE users SET remember_token = NULL, token_expires = NULL WHERE id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
    }
    
    // Delete the cookie by setting it to expire in the past
    setcookie('remember_token', '', time() - 3600, '/');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Show a success message using JavaScript and redirect
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --text-color: #333;
            --light-text: #666;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .logout-container {
            background: var(--white);
            padding: 40px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: fadeInUp 0.6s ease;
        }

        .logout-icon {
            width: 80px;
            height: 80px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary-color);
            font-size: 2.5rem;
        }

        .logout-message {
            margin-bottom: 30px;
        }

        .logout-message h2 {
            color: var(--text-color);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .logout-message p {
            color: var(--light-text);
            margin: 0;
            line-height: 1.6;
        }

        .loader {
            width: 40px;
            height: 40px;
            border: 3px solid var(--primary-color);
            border-top-color: transparent;
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

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
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="logout-message">
            <h2>Logging Out</h2>
            <p>Please wait while we securely log you out...</p>
        </div>
        <div class="loader"></div>
    </div>

    <script>
        // Redirect to login page after a short delay
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2000);
    </script>
</body>
</html> 