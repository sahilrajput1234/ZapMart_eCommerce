<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$success = '';
$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $success = 'You are already subscribed to our newsletter.';
            } else {
                // Insert new subscriber
                $stmt = $conn->prepare("INSERT INTO subscribers (email, status, created_at) VALUES (?, 'active', NOW())");
                $stmt->execute([$email]);
                
                $success = 'Thank you for subscribing to our newsletter!';
                $email = ''; // Clear the form
            }
        } catch (PDOException $e) {
            // Create subscribers table if it doesn't exist
            if ($e->getCode() == '42S02') { // Base table or view not found
                try {
                    $conn->exec("
                        CREATE TABLE IF NOT EXISTS subscribers (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            email VARCHAR(100) NOT NULL UNIQUE,
                            status ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
                            created_at DATETIME NOT NULL,
                            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    ");
                    
                    // Try inserting again
                    $stmt = $conn->prepare("INSERT INTO subscribers (email, status, created_at) VALUES (?, 'active', NOW())");
                    $stmt->execute([$email]);
                    
                    $success = 'Thank you for subscribing to our newsletter!';
                    $email = ''; // Clear the form
                } catch (PDOException $e2) {
                    $error = 'Sorry, we could not process your request. Please try again later.';
                    error_log($e2->getMessage());
                }
            } else {
                $error = 'Sorry, we could not process your request. Please try again later.';
                error_log($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribe to Newsletter - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .subscribe-page {
            padding: 60px 0;
            background-color: #f8f9fa;
            min-height: 70vh;
            display: flex;
            align-items: center;
        }
        
        .subscribe-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .subscribe-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .subscribe-header h1 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .subscribe-header p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .subscribe-form {
            margin-top: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #4a90e2;
            outline: none;
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid #e74c3c;
        }
        
        .success-message {
            color: #2ecc71;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(46, 204, 113, 0.1);
            border-left: 4px solid #2ecc71;
        }
        
        .benefits {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .benefits h3 {
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
        }
        
        .benefits ul {
            padding-left: 20px;
        }
        
        .benefits li {
            margin-bottom: 10px;
            color: #555;
        }
        
        .benefits li i {
            color: #2ecc71;
            margin-right: 10px;
        }
        
        .privacy-note {
            margin-top: 20px;
            font-size: 13px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="subscribe-page">
        <div class="container">
            <div class="subscribe-container">
                <div class="subscribe-header">
                    <h1>Subscribe to Our Newsletter</h1>
                    <p>Stay updated with the latest products, exclusive offers, and helpful tips.</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="subscribe.php" class="subscribe-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email address" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="consent" required>
                            I consent to receive emails about products, offers, and news from ZapMart.
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="width: 100%;">Subscribe Now</button>
                </form>
                
                <div class="benefits">
                    <h3>Benefits of Subscribing:</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Be the first to know about new products</li>
                        <li><i class="fas fa-check"></i> Receive exclusive discount codes</li>
                        <li><i class="fas fa-check"></i> Get seasonal sale notifications</li>
                        <li><i class="fas fa-check"></i> Access to subscriber-only offers</li>
                    </ul>
                </div>
                
                <p class="privacy-note">
                    By subscribing, you agree to our <a href="privacy.php">Privacy Policy</a>. 
                    You can unsubscribe at any time by clicking the unsubscribe link in our emails.
                </p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>