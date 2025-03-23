<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get current settings
try {
    $stmt = $conn->query("SELECT * FROM settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error fetching settings: " . $e->getMessage();
    $settings = [];
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->beginTransaction();

        // Site Information
        $site_name = $_POST['site_name'];
        $site_description = $_POST['site_description'];
        $contact_email = $_POST['contact_email'];
        $contact_phone = $_POST['contact_phone'];
        $address = $_POST['address'];

        // Social Media Links
        $facebook_url = $_POST['facebook_url'];
        $twitter_url = $_POST['twitter_url'];
        $instagram_url = $_POST['instagram_url'];

        // E-commerce Settings
        $currency_symbol = $_POST['currency_symbol'];
        $tax_rate = $_POST['tax_rate'];
        $min_order_amount = $_POST['min_order_amount'];
        $shipping_fee = $_POST['shipping_fee'];

        // Email Settings
        $smtp_host = $_POST['smtp_host'];
        $smtp_port = $_POST['smtp_port'];
        $smtp_username = $_POST['smtp_username'];
        $smtp_password = $_POST['smtp_password'];
        $smtp_encryption = $_POST['smtp_encryption'];

        // Update settings
        $update_stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) 
                                     VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        $settings_to_update = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'contact_email' => $contact_email,
            'contact_phone' => $contact_phone,
            'address' => $address,
            'facebook_url' => $facebook_url,
            'twitter_url' => $twitter_url,
            'instagram_url' => $instagram_url,
            'currency_symbol' => $currency_symbol,
            'tax_rate' => $tax_rate,
            'min_order_amount' => $min_order_amount,
            'shipping_fee' => $shipping_fee,
            'smtp_host' => $smtp_host,
            'smtp_port' => $smtp_port,
            'smtp_username' => $smtp_username,
            'smtp_encryption' => $smtp_encryption
        ];

        // Only update SMTP password if provided
        if (!empty($smtp_password)) {
            $settings_to_update['smtp_password'] = $smtp_password;
        }

        foreach ($settings_to_update as $key => $value) {
            $update_stmt->execute([$key, $value]);
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success_msg'] = "Settings updated successfully! 🎉";

        // Refresh page to show updated settings
        header('Location: settings.php');
        exit;

    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error_msg'] = "Error updating settings: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - ZapMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --success-color: #2ecc71;
            --text-color: #2c3e50;
            --light-text: #95a5a6;
            --border-color: #ecf0f1;
            --light-bg: #f9fafb;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInDown 0.5s ease;
        }

        .page-title {
            font-size: 1.8rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-emoji {
            font-size: 2.2rem;
            animation: wave 2s infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
        }

        /* Settings Container */
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .settings-card {
            background: var(--white);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
        }

        .settings-card:nth-child(1) { animation-delay: 0.1s; }
        .settings-card:nth-child(2) { animation-delay: 0.2s; }
        .settings-card:nth-child(3) { animation-delay: 0.3s; }
        .settings-card:nth-child(4) { animation-delay: 0.4s; }

        .settings-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .settings-card-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
            animation: bounce 2s infinite;
        }

        .settings-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .input-group .form-input {
            flex: 1;
        }

        .input-addon {
            background: var(--light-bg);
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Save Button */
        .save-settings {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-color);
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s 0.5s backwards;
        }

        .save-settings:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
            background: #2980b9;
        }

        .save-settings:active {
            transform: translateY(-1px);
        }

        /* Alert Messages */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            color: var(--white);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background: var(--success-color);
        }

        .alert-error {
            background: var(--danger-color);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Animations */
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

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }

            .settings-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .save-settings {
                width: calc(100% - 40px);
                right: 20px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar (reuse the same sidebar from other admin pages) -->
    <div class="sidebar">
        <!-- ... Sidebar content ... -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-emoji">⚙️</span>
                System Settings
            </h1>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                    echo $_SESSION['success_msg'];
                    unset($_SESSION['success_msg']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    echo $_SESSION['error_msg'];
                    unset($_SESSION['error_msg']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="settings-container">
                <!-- Site Information -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="settings-card-icon">🏢</span>
                        <h2 class="settings-card-title">Site Information</h2>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Site Description</label>
                        <textarea name="site_description" class="form-input" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="tel" name="contact_phone" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-input" rows="2"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="settings-card-icon">🌐</span>
                        <h2 class="settings-card-title">Social Media</h2>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Facebook URL</label>
                        <input type="url" name="facebook_url" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Twitter URL</label>
                        <input type="url" name="twitter_url" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Instagram URL</label>
                        <input type="url" name="instagram_url" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                    </div>
                </div>

                <!-- E-commerce Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="settings-card-icon">💰</span>
                        <h2 class="settings-card-title">E-commerce Settings</h2>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '₹'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax Rate (%)</label>
                        <div class="input-group">
                            <input type="number" name="tax_rate" class="form-input" step="0.01" min="0" max="100"
                                   value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>" required>
                            <span class="input-addon">%</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Minimum Order Amount</label>
                        <div class="input-group">
                            <span class="input-addon">₹</span>
                            <input type="number" name="min_order_amount" class="form-input" min="0"
                                   value="<?php echo htmlspecialchars($settings['min_order_amount'] ?? '0'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shipping Fee</label>
                        <div class="input-group">
                            <span class="input-addon">₹</span>
                            <input type="number" name="shipping_fee" class="form-input" min="0"
                                   value="<?php echo htmlspecialchars($settings['shipping_fee'] ?? '0'); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <span class="settings-card-icon">📧</span>
                        <h2 class="settings-card-title">Email Settings</h2>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-input" 
                               value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-input" 
                               placeholder="Leave blank to keep current password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Encryption</label>
                        <select name="smtp_encryption" class="form-input">
                            <option value="tls" <?php echo ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="save-settings">
                <i class="fas fa-save"></i>
                Save Settings
            </button>
        </form>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const emailInput = document.querySelector('input[name="contact_email"]');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(emailInput.value)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                emailInput.focus();
            }
        });
    </script>
</body>
</html> 