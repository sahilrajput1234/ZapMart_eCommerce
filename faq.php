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
    <title>FAQ - ZapMart</title>
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

        .faq-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .faq-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .faq-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .faq-search {
            max-width: 600px;
            margin: 0 auto 40px;
            position: relative;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
        }

        .search-input {
            width: 100%;
            padding: 15px 20px;
            padding-left: 50px;
            border: 2px solid var(--border-color);
            border-radius: 30px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 5px rgba(52, 152, 219, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
            font-size: 1.2rem;
        }

        .faq-categories {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.3s forwards;
        }

        .category-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            background: var(--white);
            color: var(--text-color);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .faq-list {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.4s forwards;
        }

        .faq-category {
            margin-bottom: 40px;
        }

        .category-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        .faq-item {
            background: var(--white);
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-question {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .faq-question h3 {
            font-size: 1.1rem;
            color: var(--text-color);
            margin: 0;
            padding-right: 20px;
        }

        .faq-toggle {
            width: 24px;
            height: 24px;
            background: var(--light-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .faq-toggle i {
            color: var(--primary-color);
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-answer p {
            color: var(--light-text);
            line-height: 1.6;
            margin: 0;
            padding-bottom: 20px;
        }

        .faq-item.active {
            background: var(--white);
        }

        .faq-item.active .faq-question {
            background: var(--primary-color);
        }

        .faq-item.active .faq-question h3 {
            color: var(--white);
        }

        .faq-item.active .faq-toggle {
            background: var(--white);
            transform: rotate(180deg);
        }

        .faq-item.active .faq-answer {
            max-height: 1000px;
        }

        .contact-support {
            text-align: center;
            margin-top: 60px;
            padding: 40px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.5s forwards;
        }

        .contact-support h2 {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .contact-support p {
            color: var(--light-text);
            margin-bottom: 25px;
        }

        .contact-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .contact-btn i {
            margin-right: 8px;
        }

        .contact-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .faq-header h1 {
                font-size: 2rem;
            }

            .category-btn {
                font-size: 0.9rem;
                padding: 8px 16px;
            }

            .faq-question h3 {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="faq-container">
        <div class="faq-header">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about our products, services, and policies</p>
        </div>

        <div class="faq-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search for questions...">
        </div>

        <div class="faq-categories">
            <button class="category-btn active" data-category="all">All Questions</button>
            <button class="category-btn" data-category="orders">Orders & Shipping</button>
            <button class="category-btn" data-category="returns">Returns & Refunds</button>
            <button class="category-btn" data-category="products">Products</button>
            <button class="category-btn" data-category="account">Account & Security</button>
            <button class="category-btn" data-category="payment">Payment</button>
        </div>

        <div class="faq-list">
            <!-- Orders & Shipping -->
            <div class="faq-category" data-category="orders">
                <h2 class="category-title">Orders & Shipping</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How long does shipping take?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Standard shipping typically takes 3-5 business days within India. Express shipping options are available for faster delivery, usually within 1-2 business days. International shipping may take 7-14 business days depending on the destination.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How can I track my order?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Once your order ships, you'll receive a tracking number via email. You can also track your order by logging into your account and visiting the Order History section. Our tracking system provides real-time updates on your package's location.</p>
                    </div>
                </div>
            </div>

            <!-- Returns & Refunds -->
            <div class="faq-category" data-category="returns">
                <h2 class="category-title">Returns & Refunds</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What is your return policy?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>We offer a 30-day return policy for most items. Products must be unused and in their original packaging. Some items, such as personalized products or intimate wear, are not eligible for return. Please visit our Returns page for detailed information.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How long do refunds take to process?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Once we receive your return, refunds are typically processed within 3-5 business days. The time it takes for the refund to appear in your account depends on your payment method and bank processing times.</p>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="faq-category" data-category="products">
                <h2 class="category-title">Products</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Are your products authentic?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, all products sold on ZapMart are 100% authentic. We source our products directly from authorized manufacturers and distributors. We have a strict no-counterfeit policy and regularly verify our suppliers.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What if a product is out of stock?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>You can sign up for email notifications when out-of-stock items become available. Simply click the "Notify Me" button on the product page. We regularly restock popular items and update our inventory daily.</p>
                    </div>
                </div>
            </div>

            <!-- Account & Security -->
            <div class="faq-category" data-category="account">
                <h2 class="category-title">Account & Security</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How can I reset my password?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>To reset your password, click the "Forgot Password" link on the login page. Enter your email address, and we'll send you instructions to create a new password. For security reasons, password reset links expire after 24 hours.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Is my personal information secure?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we take data security seriously. We use industry-standard encryption to protect your personal and payment information. Our systems are regularly updated and monitored for security threats.</p>
                    </div>
                </div>
            </div>

            <!-- Payment -->
            <div class="faq-category" data-category="payment">
                <h2 class="category-title">Payment</h2>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What payment methods do you accept?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>We accept various payment methods including credit/debit cards (Visa, MasterCard, American Express), UPI, net banking, and popular digital wallets. All payments are processed securely through our payment gateway.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Is it safe to save my card information?</h3>
                        <div class="faq-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, when you choose to save your card information, it's stored securely with our payment processor, not on our servers. We use tokenization to ensure your card details are protected.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-support">
            <h2>Still have questions?</h2>
            <p>Our support team is here to help you with any questions or concerns</p>
            <a href="contact.php" class="contact-btn">
                <i class="fas fa-envelope"></i>
                Contact Support
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // FAQ Toggle
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', () => {
                    const currentlyActive = document.querySelector('.faq-item.active');
                    if (currentlyActive && currentlyActive !== item) {
                        currentlyActive.classList.remove('active');
                    }
                    item.classList.toggle('active');
                });
            });

            // Category Filter
            const categoryBtns = document.querySelectorAll('.category-btn');
            const categories = document.querySelectorAll('.faq-category');
            
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const category = btn.dataset.category;
                    
                    // Update active button
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    // Show/hide categories
                    categories.forEach(cat => {
                        if (category === 'all' || cat.dataset.category === category) {
                            cat.style.display = 'block';
                        } else {
                            cat.style.display = 'none';
                        }
                    });
                });
            });

            // Search Functionality
            const searchInput = document.querySelector('.search-input');
            const faqQuestions = document.querySelectorAll('.faq-question h3');
            
            searchInput.addEventListener('input', () => {
                const searchTerm = searchInput.value.toLowerCase();
                
                faqQuestions.forEach(question => {
                    const item = question.closest('.faq-item');
                    const text = question.textContent.toLowerCase();
                    
                    if (text.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Show/hide categories based on visible questions
                categories.forEach(category => {
                    const visibleQuestions = category.querySelectorAll('.faq-item[style="display: block"]');
                    if (visibleQuestions.length === 0) {
                        category.style.display = 'none';
                    } else {
                        category.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html> 