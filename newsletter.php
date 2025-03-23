<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle newsletter subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Add subscription logic here
            $_SESSION['subscription_success'] = true;
            header('Location: newsletter.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - ZapMart</title>
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
            margin: 0;
            padding: 0;
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

        .newsletter-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .newsletter-header {
            text-align: center;
            margin-bottom: 60px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .newsletter-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .newsletter-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .subscription-form {
            background: var(--white);
            border-radius: 15px;
            padding: 40px;
            box-shadow: var(--shadow);
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
        }

        .form-title {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .form-group {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
        }

        .email-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .email-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--input-shadow);
        }

        .subscribe-btn {
            padding: 15px 30px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .subscribe-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .benefits-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .benefit-card {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
        }

        .benefit-card:nth-child(1) { animation: fadeInUp 0.6s 0.3s forwards; }
        .benefit-card:nth-child(2) { animation: fadeInUp 0.6s 0.4s forwards; }
        .benefit-card:nth-child(3) { animation: fadeInUp 0.6s 0.5s forwards; }
        .benefit-card:nth-child(4) { animation: fadeInUp 0.6s 0.6s forwards; }

        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .benefit-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: inline-block;
            animation: bounce 2s infinite;
        }

        .benefit-title {
            font-size: 1.2rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .benefit-description {
            color: var(--light-text);
            line-height: 1.6;
        }

        .past-newsletters {
            background: var(--white);
            border-radius: 15px;
            padding: 40px;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.7s forwards;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .newsletter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .newsletter-preview {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .newsletter-preview:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .preview-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .preview-content {
            padding: 20px;
        }

        .preview-date {
            color: var(--lighter-text);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .preview-title {
            color: var(--text-color);
            font-size: 1.1rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .preview-description {
            color: var(--light-text);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .read-more {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .read-more:hover {
            color: var(--primary-dark);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }

        .success-message {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .newsletter-header h1 {
                font-size: 2rem;
            }

            .form-group {
                flex-direction: column;
            }

            .subscribe-btn {
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
            <div class="loader-icon">📨</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="newsletter-container">
        <div class="newsletter-header">
            <h1>
                <span>Newsletter Subscription</span>
                📬
            </h1>
            <p>Stay updated with our latest products, exclusive offers, and fashion trends delivered straight to your inbox!</p>
        </div>

        <div class="subscription-form">
            <?php if (isset($_SESSION['subscription_success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Thank you for subscribing! Please check your email to confirm your subscription.</span>
                </div>
                <?php unset($_SESSION['subscription_success']); ?>
            <?php endif; ?>

            <h2 class="form-title">
                <span>Subscribe Now</span>
                ✨
            </h2>
            <form method="post" action="newsletter.php" class="form-group">
                <input type="email" name="email" class="email-input" placeholder="Enter your email address" required>
                <button type="submit" class="subscribe-btn">
                    <i class="fas fa-paper-plane"></i>
                    Subscribe
                </button>
            </form>
        </div>

        <div class="benefits-section">
            <div class="benefit-card">
                <span class="benefit-icon">🎁</span>
                <h3 class="benefit-title">Exclusive Offers</h3>
                <p class="benefit-description">Be the first to know about our special deals and promotional offers.</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon">🔔</span>
                <h3 class="benefit-title">New Arrivals</h3>
                <p class="benefit-description">Get instant updates when new products arrive in our store.</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon">💡</span>
                <h3 class="benefit-title">Style Tips</h3>
                <p class="benefit-description">Receive fashion advice and styling tips from our experts.</p>
            </div>
            <div class="benefit-card">
                <span class="benefit-icon">🎯</span>
                <h3 class="benefit-title">Personalized Content</h3>
                <p class="benefit-description">Get recommendations based on your preferences and shopping history.</p>
            </div>
        </div>

        <div class="past-newsletters">
            <h2 class="section-title">
                <span>Past Newsletters</span>
                📚
            </h2>
            <div class="newsletter-grid">
                <div class="newsletter-preview">
                    <img src="assets/images/newsletter/summer-collection.jpg" alt="Summer Collection" class="preview-image">
                    <div class="preview-content">
                        <div class="preview-date">June 15, 2023</div>
                        <h3 class="preview-title">Summer Collection Launch 🌞</h3>
                        <p class="preview-description">Discover our latest summer collection featuring breathable fabrics and vibrant colors.</p>
                        <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="newsletter-preview">
                    <img src="assets/images/newsletter/holiday-special.jpg" alt="Holiday Special" class="preview-image">
                    <div class="preview-content">
                        <div class="preview-date">May 28, 2023</div>
                        <h3 class="preview-title">Holiday Special Deals 🎉</h3>
                        <p class="preview-description">Check out our exclusive holiday deals with up to 50% off on selected items.</p>
                        <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="newsletter-preview">
                    <img src="assets/images/newsletter/style-guide.jpg" alt="Style Guide" class="preview-image">
                    <div class="preview-content">
                        <div class="preview-date">May 15, 2023</div>
                        <h3 class="preview-title">Spring Style Guide 🌸</h3>
                        <p class="preview-description">Learn how to style your spring wardrobe with our expert fashion tips.</p>
                        <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
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

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html> 