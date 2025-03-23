<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .error-container {
            max-width: 800px;
            margin: 60px auto;
            text-align: center;
            padding: 40px 20px;
        }

        .error-icon {
            font-size: 5rem;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .back-home {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-home:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <div class="error-container">
            <i class="fas fa-exclamation-circle error-icon"></i>
            <h1 class="error-title">404 - Page Not Found</h1>
            <p class="error-message">
                Oops! The page you're looking for doesn't exist. The product might have been removed or is temporarily unavailable.
            </p>
            <a href="index.php" class="back-home">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>