<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $icon = $_POST['icon'];
    
    try {
        // Insert category
        $stmt = $conn->prepare("
            INSERT INTO categories (name, description, status, icon, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$name, $description, $status, $icon]);

        $_SESSION['success_msg'] = "Category added successfully! 🎉";
        header('Location: categories.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding category: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - ZapMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366F1;
            --primary-dark: #4F46E5;
            --secondary-color: #10B981;
            --danger-color: #EF4444;
            --warning-color: #F59E0B;
            --text-color: #1F2937;
            --text-light: #6B7280;
            --light-bg: #F3F4F6;
            --border-color: #E5E7EB;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            display: flex;
            color: var(--text-color);
            overflow-x: hidden;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 250px;
            position: relative;
        }

        /* Enhanced Page Header */
        .page-header {
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: var(--white);
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            animation: slideDown 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(79, 70, 229, 0.1) 100%);
            animation: rotate 15s linear infinite;
            z-index: 0;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Enhanced Emoji Animation */
        .page-emoji {
            font-size: 3rem;
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: floatEmoji 3s ease-in-out infinite;
            position: relative;
            display: inline-block;
        }

        .page-emoji::after {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, rgba(99, 102, 241, 0) 70%);
            border-radius: 50%;
            left: -10%;
            top: -10%;
            z-index: -1;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes floatEmoji {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(5deg); }
            66% { transform: translateY(-5px) rotate(-5deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }

        /* Enhanced Form Container */
        .form-container {
            background: var(--white);
            border-radius: 2rem;
            box-shadow: var(--shadow-lg);
            padding: 3rem;
            margin-bottom: 2rem;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(229, 231, 235, 0.5);
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            z-index: 1;
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(50px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Form Groups */
        .form-group {
            margin-bottom: 2rem;
            position: relative;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-group:hover {
            transform: translateX(10px);
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-color);
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 1rem;
            font-size: 1.125rem;
            transition: all 0.3s ease;
            background: var(--white);
            color: var(--text-color);
            box-shadow: var(--shadow-sm);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.15);
            transform: translateY(-2px);
        }

        /* Enhanced Emoji Picker */
        .emoji-picker {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            gap: 0.75rem;
            padding: 1.5rem;
            background: var(--light-bg);
            border-radius: 1rem;
            margin-top: 0.75rem;
            animation: fadeIn 0.5s ease-out;
            border: 1px solid var(--border-color);
        }

        .emoji-option {
            font-size: 2rem;
            padding: 0.75rem;
            border-radius: 0.75rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid transparent;
            box-shadow: var(--shadow-sm);
        }

        .emoji-option:hover {
            background: var(--white);
            transform: scale(1.15) translateY(-5px);
            box-shadow: var(--shadow);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .selected-emoji {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            transform: scale(1.15);
            box-shadow: var(--shadow);
            animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes popIn {
            from { transform: scale(0.8); }
            to { transform: scale(1.15); }
        }

        /* Enhanced Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            margin-top: 2.5rem;
            padding-top: 2.5rem;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 1.25rem 2rem;
            border: none;
            border-radius: 1rem;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            min-width: 180px;
            justify-content: center;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            animation: shine 5s infinite;
        }

        @keyframes shine {
            to { left: 100%; }
        }

        .btn i {
            font-size: 1.25rem;
            transition: transform 0.3s ease;
        }

        .btn:hover i {
            transform: translateX(3px) scale(1.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3), 0 0 0 rgba(99, 102, 241, 0);
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4), 0 0 0 5px rgba(99, 102, 241, 0.1);
        }

        .btn-primary:active {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-color);
            border: 2px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            background: var(--light-bg);
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-secondary:active {
            transform: translateY(-2px);
        }

        /* Enhanced Alerts */
        .alert {
            padding: 1.25rem 1.75rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideInDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            animation: alertTimer 5s linear forwards;
        }

        @keyframes alertTimer {
            to { width: 100%; }
        }

        .alert i {
            font-size: 1.5rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            border-left: 5px solid #047857;
        }

        .alert-error {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
            border-left: 5px solid #B91C1C;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-2rem);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Add to Cart Button Animation */
        .add-to-cart-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 100;
            border: none;
        }

        .add-to-cart-btn:hover {
            transform: scale(1.15);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .add-to-cart-btn i {
            animation: cartBounce 2s ease infinite;
        }

        @keyframes cartBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }

            .form-container {
                padding: 2rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .page-emoji {
                font-size: 2.5rem;
            }

            .emoji-picker {
                grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            }

            .emoji-option {
                font-size: 1.75rem;
            }
        }

        /* Additional Animations */
        @keyframes shimmer {
            0% { background-position: -100% 0; }
            100% { background-position: 100% 0; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-emoji">📁</span>
                Add New Category
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

        <div class="form-container">
            <form method="POST" id="categoryForm">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tag"></i>
                        Category Name
                    </label>
                    <input type="text" name="name" class="form-control" required
                           placeholder="Enter category name">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-align-left"></i>
                        Description
                    </label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Enter category description"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-smile"></i>
                        Category Icon
                    </label>
                    <input type="hidden" name="icon" id="selectedIcon" required>
                    <div class="emoji-picker">
                        <?php
                        $emojis = ['📱', '💻', '🖥️', '⌚', '🎮', '🎧', '📷', '🔊', '📺', '🖨️', 
                                  '🏠', '👕', '👜', '🛋️', '🎁', '🛒', '🏷️', '💎', '🎨', '📚',
                                  '🍔', '☕', '🍕', '🍎', '🥑', '🚗', '✈️', '🏆', '⚽', '🏋️‍♂️'];
                        foreach ($emojis as $emoji) {
                            echo "<div class='emoji-option' onclick='selectEmoji(this)' data-emoji='$emoji'>$emoji</div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        Status
                    </label>
                    <select name="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Category
                    </button>
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Add to Cart Button -->
        <button class="add-to-cart-btn" id="addToCartBtn">
            <i class="fas fa-shopping-cart"></i>
        </button>
    </div>

    <script>
        // Emoji Selection
        function selectEmoji(element) {
            // Remove previous selection
            document.querySelectorAll('.emoji-option').forEach(el => {
                el.classList.remove('selected-emoji');
            });
            
            // Add selection to clicked emoji
            element.classList.add('selected-emoji');
            
            // Update hidden input
            document.getElementById('selectedIcon').value = element.dataset.emoji;
            
            // Add pop effect
            element.style.transform = 'scale(1.3)';
            setTimeout(() => {
                element.style.transform = 'scale(1.15)';
            }, 200);
        }

        // Add to Cart Button Animation
        const addToCartBtn = document.getElementById('addToCartBtn');
        addToCartBtn.addEventListener('click', function() {
            // Create floating emoji animation
            const emoji = document.createElement('div');
            emoji.textContent = '🛒';
            emoji.style.position = 'fixed';
            emoji.style.fontSize = '2rem';
            emoji.style.bottom = '2rem';
            emoji.style.right = '2rem';
            emoji.style.zIndex = '99';
            emoji.style.pointerEvents = 'none';
            document.body.appendChild(emoji);
            
            // Animate the emoji
            anime({
                targets: emoji,
                translateY: -100,
                opacity: [1, 0],
                duration: 1000,
                easing: 'easeOutExpo',
                complete: function() {
                    document.body.removeChild(emoji);
                }
            });
            
            // Create success toast
            const toast = document.createElement('div');
            toast.className = 'alert alert-success';
            toast.style.position = 'fixed';
            toast.style.bottom = '2rem';
            toast.style.left = '50%';
            toast.style.transform = 'translateX(-50%)';
            toast.style.zIndex = '999';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> Item added to cart!';
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        });

        // Form Validation
        const categoryForm = document.getElementById('categoryForm');
        categoryForm.addEventListener('submit', (e) => {
            const name = categoryForm.querySelector('[name="name"]').value.trim();
            const icon = document.getElementById('selectedIcon').value;

            if (name.length < 2) {
                e.preventDefault();
                shakeElement(categoryForm.querySelector('[name="name"]'));
                showToast('Category name must be at least 2 characters long', 'error');
            }

            if (!icon) {
                e.preventDefault();
                shakeElement(document.querySelector('.emoji-picker'));
                showToast('Please select a category icon', 'error');
            }
        });
        
        // Shake animation for invalid fields
        function shakeElement(element) {
            element.style.animation = 'none';
            setTimeout(() => {
                element.style.animation = 'shake 0.5s cubic-bezier(.36,.07,.19,.97) both';
            }, 10);
        }
        
        // Toast notification
        function showToast(message, type = 'error') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type}`;
            toast.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation' : 'check'}-circle"></i> ${message}`;
            toast.style.position = 'fixed';
            toast.style.top = '2rem';
            toast.style.right = '2rem';
            toast.style.zIndex = '9999';
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Advanced form field animations
        document.querySelectorAll('.form-control').forEach(field => {
            field.addEventListener('focus', () => {
                field.closest('.form-group').style.transform = 'scale(1.02) translateX(15px)';
                field.style.boxShadow = '0 0 0 5px rgba(99, 102, 241, 0.15)';
            });

            field.addEventListener('blur', () => {
                field.closest('.form-group').style.transform = '';
                if (!field.value) {
                    field.style.boxShadow = '';
                }
            });

            // Add typing effect
            field.addEventListener('input', () => {
                field.style.borderColor = 'var(--primary-color)';
            });
        });

        // Add hover effect to emoji picker with 3D transform
        document.querySelectorAll('.emoji-option').forEach(emoji => {
            emoji.addEventListener('mouseover', () => {
                emoji.style.transform = 'scale(1.15) translateY(-5px) rotateY(10deg)';
                emoji.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
            });

            emoji.addEventListener('mouseout', () => {
                if (!emoji.classList.contains('selected-emoji')) {
                    emoji.style.transform = '';
                    emoji.style.boxShadow = '';
                }
            });
        });
        
        // Add page entrance animation
        document.addEventListener('DOMContentLoaded', () => {
            anime({
                targets: '.form-group',
                translateX: [50, 0],
                opacity: [0, 1],
                delay: anime.stagger(100, {start: 300}),
                easing: 'easeOutQuad'
            });
            
            anime({
                targets: '.action-buttons',
                translateY: [20, 0],
                opacity: [0, 1],
                delay: 800,
                easing: 'easeOutQuad'
            });
        });
    </script>
    
    <!-- Add Anime.js for advanced animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    
    <style>
        /* Add keyframe animations */
        @keyframes shake {
            10%, 90% { transform: translateX(-1px); }
            20%, 80% { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-4px); }
            40%, 60% { transform: translateX(4px); }
        }
    </style>
</body>
</html> 