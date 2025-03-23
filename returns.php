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
    <title>Returns & Refunds Policy - ZapMart</title>
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

        /* Return Policy Container */
        .returns-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        .returns-page-title {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .returns-page-title h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .returns-page-title p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .returns-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        /* Returns Main Section */
        .returns-info {
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

        .returns-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
        }

        .returns-header h2 {
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .returns-header h2 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .returns-body {
            padding: 30px;
        }

        .returns-section {
            margin-bottom: 30px;
        }

        .returns-section:last-child {
            margin-bottom: 0;
        }

        .returns-section h3 {
            font-size: 1.3rem;
            color: var(--text-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .returns-section h3 i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .returns-section p {
            color: var(--light-text);
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .returns-section ul {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 15px;
        }

        .returns-section ul li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
            color: var(--light-text);
            line-height: 1.6;
        }

        .returns-section ul li:before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--secondary-color);
        }

        .returns-section ul.not-eligible li:before {
            content: '\f00d';
            color: var(--danger-color);
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

        /* Return Process Steps */
        .return-steps {
            margin: 30px 0;
        }

        .step-item {
            display: flex;
            margin-bottom: 25px;
            opacity: 0;
            transform: translateX(-20px);
            animation: fadeInLeft 0.5s forwards;
        }

        .step-item:nth-child(1) { animation-delay: 0.3s; }
        .step-item:nth-child(2) { animation-delay: 0.4s; }
        .step-item:nth-child(3) { animation-delay: 0.5s; }
        .step-item:nth-child(4) { animation-delay: 0.6s; }

        .step-item:last-child {
            margin-bottom: 0;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .step-content {
            flex: 1;
            background: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--input-shadow);
        }

        .step-content h4 {
            margin-top: 0;
            color: var(--text-color);
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .step-content p {
            margin-bottom: 0;
            color: var(--light-text);
        }

        @keyframes fadeInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
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

        /* Return Form CTA */
        .return-form-cta {
            padding: 30px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 15px;
            color: var(--white);
            text-align: center;
            margin-top: 30px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.5s forwards;
        }

        .return-form-cta h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .return-form-cta p {
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .start-return-btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--white);
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
        }

        .start-return-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 255, 255, 0.3);
        }

        /* Timeline */
        .refund-timeline {
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

        /* Animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .returns-wrapper {
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
            <div class="loader-icon">↩️</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="returns-container">
        <div class="returns-page-title">
            <h1>Returns & Refunds Policy</h1>
            <p>Easy returns and hassle-free refunds for your peace of mind</p>
        </div>
        
        <div class="returns-wrapper">
            <div class="returns-info">
                <div class="returns-header">
                    <h2><i class="fas fa-undo-alt"></i> Returns Policy</h2>
                </div>
                
                <div class="returns-body">
                    <div class="alert-box">
                        <h4>30-Day Return Policy</h4>
                        <p>We accept returns within 30 days of delivery for most items. Simply initiate a return through your account or contact our customer service team.</p>
                    </div>
                    
                    <div class="returns-section">
                        <h3><i class="fas fa-clipboard-check"></i> Return Eligibility</h3>
                        <p>To be eligible for a return, your item must meet the following criteria:</p>
                        
                        <ul>
                            <li>The item must be returned within 30 days of delivery</li>
                            <li>The item must be in its original condition</li>
                            <li>The item must be unused with all original tags and packaging</li>
                            <li>You must have the original receipt or proof of purchase</li>
                            <li>The item must not be on the list of non-returnable items</li>
                        </ul>
                        
                        <p>Items that are <strong>not eligible</strong> for return include:</p>
                        
                        <ul class="not-eligible">
                            <li>Personalized or custom-made items</li>
                            <li>Gift cards</li>
                            <li>Downloadable software products</li>
                            <li>Intimate apparel and swimwear for hygiene reasons</li>
                            <li>Food, perishable items, and groceries</li>
                            <li>Unsealed health and personal care items</li>
                        </ul>
                    </div>
                    
                    <div class="returns-section">
                        <h3><i class="fas fa-exchange-alt"></i> Return Process</h3>
                        <p>Follow these simple steps to return your item:</p>
                        
                        <div class="return-steps">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Initiate Your Return</h4>
                                    <p>Log in to your ZapMart account, go to "My Orders," and select the item you want to return. Alternatively, contact our customer service.</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Package Your Return</h4>
                                    <p>Pack the item securely in its original packaging with all tags, manuals, and accessories. Include your return form in the package.</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Ship Your Return</h4>
                                    <p>Drop off your package at the nearest authorized shipping center or schedule a pickup. Use the prepaid shipping label if provided.</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <h4>Receive Your Refund</h4>
                                    <p>Once we receive and inspect your return, we'll process your refund. The amount will be credited back to your original payment method.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="returns-section">
                        <h3><i class="fas fa-rupee-sign"></i> Refund Process</h3>
                        <p>Once your return is received and inspected, we will process your refund according to the following timeline:</p>
                        
                        <div class="refund-timeline">
                            <div class="timeline-line"></div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Return Received (Day 1)</h4>
                                    <p>Our team receives your return package and begins the inspection process.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Inspection (Days 1-2)</h4>
                                    <p>We verify that the returned item meets our return policy requirements.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Refund Processed (Days 2-3)</h4>
                                    <p>Once approved, we process your refund to your original payment method.</p>
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Refund Reflected (Days 3-10)</h4>
                                    <p>Depending on your payment provider, it may take 3-10 business days for the refund to appear in your account.</p>
                                </div>
                            </div>
                        </div>
                        
                        <p><strong>Note:</strong> The refund will be issued to the original payment method used for the purchase. For credit/debit card payments, please allow 5-10 business days for the refund to reflect in your account statement. For UPI and bank transfers, the refund may take 3-5 business days.</p>
                    </div>
                    
                    <div class="returns-section">
                        <h3><i class="fas fa-sync-alt"></i> Exchanges</h3>
                        <p>If you'd like to exchange an item rather than receive a refund, please follow these steps:</p>
                        
                        <ul>
                            <li>Initiate a return for the original item using the process above</li>
                            <li>Place a new order for the replacement item you want</li>
                            <li>Add a note in the "Order Notes" section mentioning that this is an exchange</li>
                            <li>Once your return is processed, you'll receive a refund for the original item</li>
                        </ul>
                        
                        <p>For faster exchanges, you can also contact our customer service team directly at support@zapmart.com or call us at +91-9876543210.</p>
                    </div>
                    
                    <div class="returns-section">
                        <h3><i class="fas fa-shipping-fast"></i> Return Shipping Costs</h3>
                        <p>Return shipping costs are handled as follows:</p>
                        
                        <ul>
                            <li>For items that are defective, damaged, or incorrectly shipped: ZapMart covers return shipping</li>
                            <li>For returns due to change of mind or other reasons: Customer is responsible for return shipping costs</li>
                            <li>For ZapMart Premium members: Free return shipping on most items</li>
                        </ul>
                        
                        <p>In cases where ZapMart covers the return shipping, a prepaid shipping label will be provided. For customer-paid returns, you can choose your preferred shipping carrier.</p>
                    </div>
                    
                    <div class="return-form-cta">
                        <h3>Ready to Start Your Return?</h3>
                        <p>Our online return process is quick and simple. Get started now!</p>
                        <a href="initiate-return.php" class="start-return-btn">Initiate Return</a>
                    </div>
                </div>
            </div>
            
            <div class="faq-section">
                <div class="faq-header">
                    <h2><i class="fas fa-question-circle"></i> Returns FAQs</h2>
                </div>
                
                <div class="faq-body">
                    <div class="faq-item active">
                        <div class="faq-question">
                            <span>How do I return an item?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Log in to your ZapMart account, go to "My Orders," select the order containing the item you want to return, and follow the prompts to complete your return request. You can also contact our customer service team for assistance.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How long do I have to return an item?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Most items can be returned within 30 days of delivery. Some product categories may have different return windows, which will be clearly specified on the product page.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>When will I get my refund?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Once your return is received and approved, we'll process your refund within 2-3 business days. Depending on your payment method, it may take an additional 3-10 business days for the refund to reflect in your account.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Can I return a gift I received?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, you can return gifts. You'll need the order number and the email address of the person who placed the order. The refund will be issued as a ZapMart gift card to you, not to the original purchaser.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What if my item arrived damaged?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            If your item arrived damaged or defective, please contact our customer service team immediately and provide photos of the damaged item. We'll arrange for a return or replacement at no cost to you.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Do I have to pay for return shipping?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Return shipping costs depend on the reason for return. For defective, damaged, or incorrectly shipped items, ZapMart covers the return shipping. For returns due to change of mind, customers typically cover the return shipping cost.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Can I exchange an item instead of returning it?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, you can exchange items. You'll need to initiate a return for the original item and place a new order for the replacement item. Add a note that it's an exchange in the order notes section.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What items cannot be returned?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Items that cannot be returned include personalized items, gift cards, downloadable software, intimate apparel, food and perishables, and unsealed health and personal care items. Please check the product description for specific return eligibility.
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