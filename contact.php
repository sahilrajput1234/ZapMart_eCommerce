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
    <title>Contact Us - ZapMart</title>
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

        /* Contact Container */
        .contact-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        .contact-page-title {
            text-align: center;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        .contact-page-title h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .contact-page-title p {
            color: var(--light-text);
            font-size: 1.1rem;
        }

        .contact-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        /* Contact Form Section */
        .contact-form-section {
            flex: 1 1 60%;
            min-width: 300px;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.2s forwards;
        }

        .contact-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
        }

        .contact-header h2 {
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .contact-header h2 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .contact-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
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
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            display: inline-block;
            padding: 15px 30px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Contact Info Section */
        .contact-info-section {
            flex: 1 1 35%;
            min-width: 300px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .contact-info {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.4s forwards;
        }

        .info-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 20px;
        }

        .info-header h2 {
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .info-header h2 i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .info-body {
            padding: 30px;
        }

        .info-item {
            display: flex;
            margin-bottom: 25px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .info-icon i {
            font-size: 1.3rem;
            color: var(--primary-color);
        }

        .info-content h3 {
            font-size: 1.1rem;
            color: var(--text-color);
            margin: 0 0 5px 0;
        }

        .info-content p {
            color: var(--light-text);
            margin: 0;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-3px);
        }

        /* Map Section */
        .map-section {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s 0.6s forwards;
            height: 300px;
        }

        .map-container {
            width: 100%;
            height: 100%;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--white);
            border-radius: 10px;
            padding: 15px 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            z-index: 1000;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification.success {
            border-left: 4px solid var(--secondary-color);
        }

        .notification.error {
            border-left: 4px solid var(--danger-color);
        }

        .notification-content {
            flex: 1;
            margin-right: 15px;
        }

        .notification-content i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .notification-content i.fa-check-circle {
            color: var(--secondary-color);
        }

        .notification-content i.fa-exclamation-circle {
            color: var(--danger-color);
        }

        .notification-close {
            background: none;
            border: none;
            color: var(--lighter-text);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .notification-close:hover {
            color: var(--text-color);
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
            border-left: 4px solid var(--secondary-color);
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
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
            .contact-wrapper {
                flex-direction: column;
            }
            
            .contact-form-section {
                order: 2;
            }
            
            .contact-info-section {
                order: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-icon">📞</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="contact-container">
        <div class="contact-page-title">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you! Get in touch with our team</p>
        </div>
        
        <div class="contact-wrapper">
            <div class="contact-form-section">
                <div class="contact-header">
                    <h2><i class="fas fa-envelope"></i> Send Us a Message</h2>
                </div>
                
                <div class="contact-body">
                    <?php if (isset($_SESSION['contact_success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $_SESSION['contact_success']; ?>
                            <?php unset($_SESSION['contact_success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['contact_error'])): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $_SESSION['contact_error']; ?>
                            <?php unset($_SESSION['contact_error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="contactForm" action="process_contact.php" method="post">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="contact-info-section">
                <div class="contact-info">
                    <div class="info-header">
                        <h2><i class="fas fa-info-circle"></i> Contact Information</h2>
                    </div>
                    
                    <div class="info-body">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                            <h3>Our Location</h3>Dasna Dehat Usman Colony Ghziabad Pin-201001</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="info-content">
                                <h3>Phone Number</h3>
                                <p>+91 7428802860<br>+91 8860973245</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h3>Email Address</h3>
                                <p>mohadshahil@zapmart.com<br>support@zapmart.com</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <h3>Working Hours</h3>
                                <p>Monday - Friday: 9:00 AM - 8:00 PM<br>Saturday & Sunday: 10:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <div class="info-content">
                                <h3>Social Media</h3>
                                <div class="social-links">
                                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="map-section">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.15830869428!2d-74.11976397304903!3d40.69766374874431!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1661794387945!5m2!1sen!2s" allowfullscreen="" loading="lazy"></iframe>
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
            
            // Contact Form Submission
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    // For demo purposes, we'll show a notification
                    // But we'll still allow the form to submit normally
                    
                    // If you want to implement AJAX submission instead, uncomment these lines:
                    // e.preventDefault();
                    // showNotification('Your message has been sent successfully! We will get back to you soon.', 'success');
                    // contactForm.reset();
                    
                    // The form will now submit normally to process_contact.php
                });
            }
            
            // Show notification function
            window.showNotification = function(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <div class="notification-content">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="notification-close"><i class="fas fa-times"></i></button>
                `;
                
                document.body.appendChild(notification);
                
                // Show notification
                setTimeout(() => {
                    notification.classList.add('show');
                }, 10);
                
                // Close notification
                const close = notification.querySelector('.notification-close');
                close.addEventListener('click', () => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                });
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        notification.classList.remove('show');
                        setTimeout(() => {
                            if (document.body.contains(notification)) {
                                notification.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            };
        });
    </script>
</body>
</html> 