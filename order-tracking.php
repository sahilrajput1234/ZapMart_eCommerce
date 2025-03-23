<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366F1;
            --primary-dark: #4F46E5;
            --secondary-color: #10B981;
            --text-color: #1F2937;
            --light-text: #6B7280;
            --border-color: #E5E7EB;
            --light-bg: #F3F4F6;
            --white: #ffffff;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .tracking-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInUp 0.6s ease;
        }

        .tracking-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .tracking-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .tracking-form {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow);
            max-width: 600px;
            margin: 0 auto 40px;
            animation: fadeInUp 0.6s ease 0.2s both;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn-track {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-track:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .tracking-result {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow);
            margin-top: 40px;
            animation: fadeInUp 0.6s ease 0.4s both;
            display: none;
        }

        .tracking-result.active {
            display: block;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .info-item {
            padding: 15px;
            background: var(--light-bg);
            border-radius: 8px;
        }

        .info-item h4 {
            color: var(--light-text);
            margin: 0 0 5px;
            font-size: 0.9rem;
        }

        .info-item p {
            color: var(--text-color);
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .tracking-timeline {
            position: relative;
            margin-top: 40px;
        }

        .timeline-line {
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            padding-left: 45px;
            margin-bottom: 30px;
        }

        .timeline-dot {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            background: var(--white);
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        .timeline-dot i {
            color: var(--primary-color);
            font-size: 1rem;
        }

        .timeline-content {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 8px;
            position: relative;
        }

        .timeline-content::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 12px;
            width: 16px;
            height: 16px;
            background: var(--light-bg);
            transform: rotate(45deg);
        }

        .timeline-date {
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .timeline-title {
            color: var(--text-color);
            margin: 0 0 5px;
            font-weight: 600;
        }

        .timeline-text {
            color: var(--light-text);
            margin: 0;
            line-height: 1.5;
        }

        .completed .timeline-dot {
            background: var(--primary-color);
        }

        .completed .timeline-dot i {
            color: var(--white);
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

        @media (max-width: 768px) {
            .tracking-header h1 {
                font-size: 2rem;
            }

            .tracking-form,
            .tracking-result {
                padding: 20px;
            }

            .order-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="tracking-container">
        <div class="tracking-header">
            <h1>Track Your Order</h1>
            <p>Enter your order number to track your shipment</p>
        </div>

        <div class="tracking-form">
            <form id="trackingForm">
                <div class="form-group">
                    <label class="form-label">Order Number</label>
                    <input type="text" class="form-control" id="orderNumber" placeholder="Enter your order number" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" placeholder="Enter your email address" required>
                </div>
                <button type="submit" class="btn-track">
                    <i class="fas fa-search"></i> Track Order
                </button>
            </form>
        </div>

        <div class="tracking-result">
            <div class="order-info">
                <div class="info-item">
                    <h4>Order Number</h4>
                    <p>#ORD123456789</p>
                </div>
                <div class="info-item">
                    <h4>Order Status</h4>
                    <p>In Transit</p>
                </div>
                <div class="info-item">
                    <h4>Estimated Delivery</h4>
                    <p>June 15, 2024</p>
                </div>
                <div class="info-item">
                    <h4>Shipping Method</h4>
                    <p>Express Delivery</p>
                </div>
            </div>

            <div class="tracking-timeline">
                <div class="timeline-line"></div>
                
                <div class="timeline-item completed">
                    <div class="timeline-dot">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">June 12, 2024 - 10:30 AM</div>
                        <h3 class="timeline-title">Order Confirmed</h3>
                        <p class="timeline-text">Your order has been confirmed and is being processed</p>
                    </div>
                </div>

                <div class="timeline-item completed">
                    <div class="timeline-dot">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">June 13, 2024 - 2:15 PM</div>
                        <h3 class="timeline-title">Order Packed</h3>
                        <p class="timeline-text">Your order has been packed and is ready for shipping</p>
                    </div>
                </div>

                <div class="timeline-item completed">
                    <div class="timeline-dot">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">June 14, 2024 - 9:45 AM</div>
                        <h3 class="timeline-title">In Transit</h3>
                        <p class="timeline-text">Your package is on its way to the delivery address</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date">Expected: June 15, 2024</div>
                        <h3 class="timeline-title">Out for Delivery</h3>
                        <p class="timeline-text">Your package will be delivered to your address</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.getElementById('trackingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const button = this.querySelector('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Tracking...';
            button.disabled = true;

            // Simulate API call
            setTimeout(() => {
                document.querySelector('.tracking-result').classList.add('active');
                
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;

                // Smooth scroll to results
                document.querySelector('.tracking-result').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 1500);
        });
    </script>
</body>
</html> 