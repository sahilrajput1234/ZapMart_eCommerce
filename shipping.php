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
    <title>Shipping Policy - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji.css/dist/emoji.min.css">
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

        /* Shipping Container */
        .shipping-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        .shipping-page-title {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .shipping-page-title h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .shipping-page-title p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .shipping-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        /* Shipping Info Section */
        .shipping-info {
            flex: 1 1 65%;
            min-width: 300px;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
        }

        .shipping-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
        }

        .shipping-header h2 {
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .shipping-header h2 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .shipping-body {
            padding: 30px;
        }

        .shipping-section {
            margin-bottom: 30px;
        }

        .shipping-section:last-child {
            margin-bottom: 0;
        }

        .shipping-section h3 {
            font-size: 1.3rem;
            color: var(--text-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .shipping-section h3 i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .shipping-section p {
            color: var(--light-text);
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .shipping-section ul {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 15px;
        }

        .shipping-section ul li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
            color: var(--light-text);
            line-height: 1.6;
        }

        .shipping-section ul li:before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--secondary-color);
        }

        /* Shipping Rates Table */
        .shipping-rates {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: var(--input-shadow);
        }

        .shipping-rates th, .shipping-rates td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .shipping-rates th {
            background-color: var(--light-bg);
            color: var(--text-color);
            font-weight: 600;
        }

        .shipping-rates tr:nth-child(even) {
            background-color: var(--light-bg);
        }

        .shipping-rates tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .shipping-rates td:last-child {
            font-weight: 600;
        }

        .free-shipping-highlight {
            color: var(--secondary-color);
            font-weight: 600;
        }

        /* Timeline */
        .shipping-timeline {
            margin: 30px 0;
            position: relative;
        }

        .timeline-line {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 15px;
            width: 2px;
            background-color: var(--primary-color);
        }

        .timeline-item {
            padding-left: 50px;
            position: relative;
            margin-bottom: 25px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: 0;
            width: 30px;
            height: 30px;
            background: var(--white);
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            z-index: 1;
        }

        .timeline-content {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--input-shadow);
        }

        .timeline-content h4 {
            margin-top: 0;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .timeline-content p {
            margin-bottom: 0;
            color: var(--light-text);
        }

        /* FAQ Section */
        .faq-section {
            flex: 1 1 30%;
            min-width: 300px;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            align-self: flex-start;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.4s forwards;
        }

        .faq-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
        }

        .faq-header h2 {
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .faq-header h2 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .faq-body {
            padding: 30px;
        }

        .faq-item {
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
        }

        .faq-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .faq-question {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-question i {
            color: var(--primary-color);
            transition: transform 0.3s ease;
        }

        .faq-answer {
            color: var(--light-text);
            line-height: 1.6;
            display: none;
            padding-top: 10px;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-item.active .faq-answer {
            display: block;
        }

        /* Alert Box */
        .alert-box {
            padding: 20px;
            background-color: rgba(52, 152, 219, 0.1);
            border-left: 4px solid var(--primary-color);
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-box h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .alert-box p {
            color: var(--light-text);
            margin-bottom: 0;
        }

        /* Animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .shipping-wrapper {
                flex-direction: column;
            }
            
            .timeline-line {
                left: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-icon">🚚</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="shipping-container">
        <div class="shipping-page-title">
            <h1>Shipping Policy</h1>
            <p>Learn everything about our shipping methods, rates, and delivery times</p>
        </div>
        
        <div class="shipping-wrapper">
            <div class="shipping-info">
                <div class="shipping-header">
                    <h2><i class="fas fa-truck"></i> Shipping Information</h2>
                </div>
                
                <div class="shipping-body">
                    <div class="alert-box">
                        <h4>Free Shipping on Orders Over ₹50!</h4>
                        <p>We offer FREE standard shipping on all orders over ₹50 within India. Take advantage of this offer and shop now!</p>
                    </div>
                    
                    <div class="shipping-section">
                        <h3><i class="fas fa-shipping-fast"></i> Shipping Methods & Delivery Times</h3>
                        <p>At ZapMart, we strive to deliver your orders as quickly and efficiently as possible. Below are our shipping methods and estimated delivery times:</p>
                        
                        <div class="shipping-timeline">
                            <div class="timeline-line"></div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Order Processing</h4>
                                    <p>We typically process orders within 1-2 business days after payment confirmation.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Order Fulfillment</h4>
                                    <p>Your items are carefully packed and prepared for shipping at our warehouse.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>In Transit</h4>
                                    <p>Your package is on its way to you! Delivery times vary based on shipping method selected.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Delivery</h4>
                                    <p>Your package arrives at your doorstep. Enjoy your ZapMart products!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="shipping-section">
                        <h3><i class="fas fa-dollar-sign"></i> Shipping Rates</h3>
                        <p>Our shipping rates are based on the destination and shipping method you choose. See the table below for our standard rates:</p>
                        
                        <table class="shipping-rates">
                            <thead>
                                <tr>
                                    <th>Shipping Method</th>
                                    <th>Delivery Time</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Standard Shipping (India)</td>
                                    <td>3-5 business days</td>
                                    <td>₹10.00 (Free on orders over ₹50)</td>
                                </tr>
                                <tr>
                                    <td>Express Shipping (India)</td>
                                    <td>2-3 business days</td>
                                    <td>₹15.00</td>
                                </tr>
                                <tr>
                                    <td>Next Day Delivery (Metro Cities)</td>
                                    <td>1 business day</td>
                                    <td>₹25.00</td>
                                </tr>
                                <tr>
                                    <td>International Standard</td>
                                    <td>7-14 business days</td>
                                    <td>Starting at ₹20.00</td>
                                </tr>
                                <tr>
                                    <td>International Express</td>
                                    <td>3-5 business days</td>
                                    <td>Starting at ₹35.00</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <p><strong>Note:</strong> Shipping times may vary depending on location, customs processing (for international orders), and other unforeseen circumstances.</p>
                    </div>
                    
                    <div class="shipping-section">
                        <h3><i class="fas fa-globe-americas"></i> International Shipping</h3>
                        <p>We ship to most countries worldwide. International customers are responsible for all duties, import taxes, and customs fees. These are not included in the shipping cost.</p>
                        
                        <p>International shipping times can vary depending on:</p>
                        <ul>
                            <li>Customs clearance procedures in your country</li>
                            <li>Local delivery services</li>
                            <li>Weather conditions and other unforeseen circumstances</li>
                        </ul>
                        
                        <p>Please note that some products may not be available for international shipping due to shipping restrictions or regulations.</p>
                    </div>
                    
                    <div class="shipping-section">
                        <h3><i class="fas fa-search-location"></i> Order Tracking</h3>
                        <p>Once your order ships, you will receive a confirmation email with a tracking number and link. You can use this to monitor your package's journey to you.</p>
                        
                        <p>You can also track your order by:</p>
                        <ul>
                            <li>Logging into your ZapMart account and viewing your order history</li>
                            <li>Contacting our customer service team with your order number</li>
                        </ul>
                        
                        <p>If you have any questions about your shipment, please don't hesitate to contact our customer service team at support@zapmart.com.</p>
                    </div>
                    
                    <div class="shipping-section">
                        <h3><i class="fas fa-file-alt"></i> Shipping Policies</h3>
                        <ul>
                            <li>We ship orders Monday through Friday, excluding holidays.</li>
                            <li>Orders placed after 2:00 PM EST will be processed the next business day.</li>
                            <li>We are not responsible for shipping delays caused by weather, natural disasters, or carrier issues.</li>
                            <li>Address changes cannot be made once an order has been processed.</li>
                            <li>P.O. Box addresses are acceptable for standard shipping only.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="faq-section">
                <div class="faq-header">
                    <h2><i class="fas fa-question-circle"></i> Shipping FAQs</h2>
                </div>
                
                <div class="faq-body">
                    <div class="faq-item active">
                        <div class="faq-question">
                            <span>How long will it take to receive my order?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Delivery times depend on the shipping method you choose and your location. Standard shipping typically takes 3-5 business days within the US, while express options can deliver in 1-2 days. International orders generally take 7-14 business days.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How can I qualify for free shipping?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            We offer free standard shipping on all orders over ₹50 within India. Just add items to your cart with a total value of ₹50 or more, and the free shipping option will be automatically applied at checkout.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Do you ship internationally?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, we ship to most countries worldwide. International shipping rates start at ₹20.00 for standard shipping and ₹35.00 for express shipping. Please note that customers are responsible for all customs duties, taxes, and fees.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How can I track my order?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Once your order ships, you'll receive a confirmation email with tracking information. You can also log into your ZapMart account to view your order status and tracking details.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What if my package is lost or damaged?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            If your package is lost or arrives damaged, please contact our customer service team within 48 hours of delivery (or expected delivery for lost packages). We'll work with the shipping carrier to resolve the issue and ensure you're satisfied with the outcome.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Can I change my shipping address after placing an order?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Address changes can only be made if your order has not yet been processed. Please contact our customer service team immediately if you need to change your shipping address. Once an order has been processed, we cannot modify the shipping address.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Do you ship to P.O. Boxes?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, we accept P.O. Box addresses for standard shipping only. Express shipping and next-day delivery options require a physical street address.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What shipping carriers do you use?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            We primarily use USPS, FedEx, and UPS for our shipments, depending on the shipping method selected and the destination address. The specific carrier will be indicated in your shipping confirmation email.
                        </div>
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
            
            // FAQ Toggles
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    
                    // Close all FAQs
                    faqItems.forEach(faq => {
                        faq.classList.remove('active');
                    });
                    
                    // Open clicked FAQ if it wasn't already open
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html> 