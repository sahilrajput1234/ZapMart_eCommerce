<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$formData = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'phone' => ''
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData['username'] = trim($_POST['username']);
    $formData['email'] = trim($_POST['email']);
    $formData['first_name'] = trim($_POST['first_name']);
    $formData['last_name'] = trim($_POST['last_name']);
    $formData['phone'] = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form data
    if (empty($formData['username']) || empty($formData['email']) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$formData['username'], $formData['email']]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            if ($existingUser['username'] === $formData['username']) {
                $error = 'Username already exists';
            } else {
                $error = 'Email already registered';
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, phone, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            try {
                $stmt->execute([
                    $formData['username'],
                    $formData['email'],
                    $hashed_password,
                    $formData['first_name'],
                    $formData['last_name'],
                    $formData['phone']
                ]);
                
                $success = 'Registration successful! You can now log in.';
                $formData = [
                    'username' => '',
                    'email' => '',
                    'first_name' => '',
                    'last_name' => '',
                    'phone' => ''
                ];
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
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
    <title>Create Account - ZapMart</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji.css/dist/emoji.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --accent-color: #f39c12;
            --text-color: #333;
            --light-text: #666;
            --lighter-text: #999;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
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

        /* Registration Form */
        .register-container {
            display: flex;
            flex: 1;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .register-wrapper {
            width: 1000px;
            max-width: 100%;
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            position: relative;
            min-height: 600px;
            display: flex;
            flex-direction: column;
        }

        .register-header {
            background: linear-gradient(135deg, #3498db 0%, #2574a9 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .register-header::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: url('assets/images/patterns/register-pattern.svg') repeat;
            opacity: 0.1;
            animation: patternMove 20s linear infinite;
        }

        @keyframes patternMove {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(10%, 10%) rotate(10deg); }
        }

        .register-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }

        .register-logo-icon {
            width: 60px;
            height: 60px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 30px;
            margin-right: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .register-title {
            position: relative;
        }

        .register-title h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .register-title p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .register-body {
            padding: 40px;
        }

        .message {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            opacity: 0;
            transform: translateY(-10px);
            animation: showMessage 0.3s forwards;
        }

        .error-message {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
        }

        .success-message {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        @keyframes showMessage {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message i {
            margin-right: 10px;
            font-size: 1rem;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group label .required {
            color: var(--error-color);
            margin-left: 3px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            background: var(--light-bg);
            transition: all 0.3s ease;
            box-shadow: var(--input-shadow);
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--lighter-text);
            transition: all 0.3s ease;
        }

        .input-with-icon input:focus {
            border-color: var(--primary-color);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .input-with-icon input:focus + i {
            color: var(--primary-color);
        }

        .password-strength {
            height: 5px;
            border-radius: 5px;
            background: var(--border-color);
            margin-top: 8px;
            overflow: hidden;
            position: relative;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 5px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .password-strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
            color: var(--light-text);
        }

        .register-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            width: 100%;
            margin-top: 20px;
        }

        .register-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .register-button:active {
            transform: translateY(0);
        }

        .login-link {
            margin-top: 30px;
            text-align: center;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .privacy-terms {
            margin-top: 20px;
            text-align: center;
            color: var(--light-text);
            font-size: 0.8rem;
        }

        .privacy-terms a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .privacy-terms a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Steps indicator */
        .registration-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 25px;
            position: relative;
        }

        .step::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background: var(--border-color);
            top: 20px;
            left: 50%;
            z-index: 1;
        }

        .step:last-child::after {
            display: none;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-bg);
            border: 2px solid var(--border-color);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: var(--light-text);
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .step.completed .step-number {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }

        .step-label {
            font-size: 0.8rem;
            color: var(--light-text);
            text-align: center;
        }

        .step.active .step-label {
            color: var(--text-color);
            font-weight: 500;
        }

        /* Form steps */
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeIn 0.5s forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Navigation buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .prev-step, .next-step {
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .prev-step {
            background: var(--light-bg);
            color: var(--light-text);
            border: 1px solid var(--border-color);
        }

        .prev-step:hover {
            background: var(--border-color);
        }

        .next-step {
            background: var(--primary-color);
            color: white;
            border: none;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .next-step:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .next-step:active {
            transform: translateY(0);
        }

        /* Animation classes */
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animation delays */
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .register-header {
                padding: 20px;
            }
            
            .register-body {
                padding: 20px;
            }
            
            .registration-steps {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-icon">🛒</div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="register-container">
        <div class="register-wrapper fade-in-up">
            <div class="register-header">
                <div class="register-logo fade-in-up">
                    <div class="register-logo-icon">
                        <span class="ec ec-shopping-bags"></span>
                    </div>
                </div>
                <div class="register-title">
                    <h1 class="fade-in-up">Create Account</h1>
                    <p class="fade-in-up delay-1">Join our community and enjoy exclusive benefits</p>
                </div>
            </div>
            
            <div class="register-body">
                <?php if ($error): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="message success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <!-- Registration Steps -->
                <div class="registration-steps fade-in-up delay-1">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <div class="step-label">Account Details</div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-label">Personal Info</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>
                
                <form id="register-form" action="register.php" method="post">
                    <!-- Step 1: Account Details -->
                    <div class="form-step active" data-step="1">
                        <div class="form-group fade-in-up delay-2">
                            <label for="username">Username <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="text" id="username" name="username" placeholder="Choose a username" value="<?php echo htmlspecialchars($formData['username']); ?>" required>
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        
                        <div class="form-group fade-in-up delay-3">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="email" id="email" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        
                        <div class="form-group fade-in-up delay-4">
                            <label for="password">Password <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar"></div>
                            </div>
                            <div class="password-strength-text">Password strength</div>
                        </div>
                        
                        <div class="form-group fade-in-up delay-5">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        
                        <div class="form-navigation">
                            <button type="button" class="next-step">Continue <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                    
                    <!-- Step 2: Personal Information -->
                    <div class="form-step" data-step="2">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <input type="text" id="first_name" name="first_name" placeholder="Your first name" value="<?php echo htmlspecialchars($formData['first_name']); ?>" required>
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <div class="input-with-icon">
                                    <input type="text" id="last_name" name="last_name" placeholder="Your last name" value="<?php echo htmlspecialchars($formData['last_name']); ?>" required>
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-with-icon">
                                <input type="tel" id="phone" name="phone" placeholder="Your phone number" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                                <i class="fas fa-phone"></i>
                            </div>
                        </div>
                        
                        <div class="form-navigation">
                            <button type="button" class="prev-step"><i class="fas fa-arrow-left"></i> Back</button>
                            <button type="button" class="next-step">Continue <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                    
                    <!-- Step 3: Review & Submit -->
                    <div class="form-step" data-step="3">
                        <div class="form-group">
                            <h3>Review Your Information</h3>
                            <p>Please review your information before submitting:</p>
                            
                            <div id="review-info" class="review-info">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="privacy-terms">
                                By clicking "Complete Registration", you agree to our 
                                <a href="terms.php">Terms of Service</a> and 
                                <a href="privacy.php">Privacy Policy</a>
                            </div>
                        </div>
                        
                        <div class="form-navigation">
                            <button type="button" class="prev-step"><i class="fas fa-arrow-left"></i> Back</button>
                            <button type="submit" class="register-button">
                                <i class="fas fa-user-plus"></i> Complete Registration
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="login-link fade-in-up delay-5">
                    Already have an account? <a href="login.php">Sign In</a>
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
                }, 1000);
            });

            // Step Navigation
            const steps = document.querySelectorAll('.step');
            const formSteps = document.querySelectorAll('.form-step');
            const nextBtns = document.querySelectorAll('.next-step');
            const prevBtns = document.querySelectorAll('.prev-step');
            const registerForm = document.getElementById('register-form');
            const reviewInfo = document.getElementById('review-info');
            
            let currentStep = 1;
            
            function updateSteps() {
                // Update step indicators
                steps.forEach((step, index) => {
                    step.classList.remove('active', 'completed');
                    if (index + 1 === currentStep) {
                        step.classList.add('active');
                    } else if (index + 1 < currentStep) {
                        step.classList.add('completed');
                    }
                });
                
                // Update form steps visibility
                formSteps.forEach((formStep) => {
                    formStep.classList.remove('active');
                    if (parseInt(formStep.dataset.step) === currentStep) {
                        formStep.classList.add('active');
                    }
                });
                
                // If we're on review step, populate review information
                if (currentStep === 3) {
                    populateReviewInfo();
                }
            }
            
            function goToNextStep() {
                if (validateCurrentStep()) {
                    currentStep++;
                    if (currentStep > formSteps.length) {
                        currentStep = formSteps.length;
                    }
                    updateSteps();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
            
            function goToPrevStep() {
                currentStep--;
                if (currentStep < 1) {
                    currentStep = 1;
                }
                updateSteps();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            
            function validateCurrentStep() {
                const currentFormStep = document.querySelector(`.form-step[data-step="${currentStep}"]`);
                const requiredInputs = currentFormStep.querySelectorAll('input[required]');
                
                let isValid = true;
                
                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('error');
                        
                        // Add error message if it doesn't exist
                        const errorMessage = input.parentElement.querySelector('.input-error');
                        if (!errorMessage) {
                            const errorElement = document.createElement('div');
                            errorElement.className = 'input-error';
                            errorElement.textContent = 'This field is required';
                            input.parentElement.appendChild(errorElement);
                        }
                    } else {
                        input.classList.remove('error');
                        
                        // Remove error message if it exists
                        const errorMessage = input.parentElement.querySelector('.input-error');
                        if (errorMessage) {
                            errorMessage.remove();
                        }
                    }
                });
                
                // Specific validation for step 1
                if (currentStep === 1) {
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('confirm_password');
                    
                    if (password.value && password.value.length < 6) {
                        isValid = false;
                        showInputError(password, 'Password must be at least 6 characters long');
                    }
                    
                    if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
                        isValid = false;
                        showInputError(confirmPassword, 'Passwords do not match');
                    }
                }
                
                return isValid;
            }
            
            function showInputError(input, message) {
                input.classList.add('error');
                
                // Add or update error message
                let errorMessage = input.parentElement.querySelector('.input-error');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'input-error';
                    input.parentElement.appendChild(errorMessage);
                }
                
                errorMessage.textContent = message;
            }
            
            function populateReviewInfo() {
                const username = document.getElementById('username').value;
                const email = document.getElementById('email').value;
                const firstName = document.getElementById('first_name').value;
                const lastName = document.getElementById('last_name').value;
                const phone = document.getElementById('phone').value;
                
                reviewInfo.innerHTML = `
                    <div class="review-item">
                        <div class="review-label">Username:</div>
                        <div class="review-value">${username}</div>
                    </div>
                    <div class="review-item">
                        <div class="review-label">Email:</div>
                        <div class="review-value">${email}</div>
                    </div>
                    <div class="review-item">
                        <div class="review-label">Name:</div>
                        <div class="review-value">${firstName} ${lastName}</div>
                    </div>
                    ${phone ? `
                    <div class="review-item">
                        <div class="review-label">Phone:</div>
                        <div class="review-value">${phone}</div>
                    </div>
                    ` : ''}
                `;
            }
            
            // Event listeners for next and previous buttons
            nextBtns.forEach(btn => {
                btn.addEventListener('click', goToNextStep);
            });
            
            prevBtns.forEach(btn => {
                btn.addEventListener('click', goToPrevStep);
            });
            
            // Password strength meter
            const passwordInput = document.getElementById('password');
            const strengthBar = document.querySelector('.password-strength-bar');
            const strengthText = document.querySelector('.password-strength-text');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 6) {
                    strength += 20;
                }
                
                if (password.length >= 8) {
                    strength += 20;
                }
                
                if (password.match(/[a-z]+/)) {
                    strength += 20;
                }
                
                if (password.match(/[A-Z]+/)) {
                    strength += 20;
                }
                
                if (password.match(/[0-9]+/)) {
                    strength += 20;
                }
                
                if (password.match(/[^a-zA-Z0-9]+/)) {
                    strength += 20;
                }
                
                strengthBar.style.width = `${Math.min(strength, 100)}%`;
                
                // Set color based on strength
                if (strength < 40) {
                    strengthBar.style.backgroundColor = '#e74c3c';
                    strengthText.textContent = 'Weak password';
                } else if (strength < 70) {
                    strengthBar.style.backgroundColor = '#f39c12';
                    strengthText.textContent = 'Medium strength';
                } else {
                    strengthBar.style.backgroundColor = '#2ecc71';
                    strengthText.textContent = 'Strong password';
                }
            });
            
            // Show/hide password
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            
            function createTogglePassword(field) {
                const togglePassword = document.createElement('i');
                togglePassword.className = 'fas fa-eye toggle-password';
                togglePassword.style.position = 'absolute';
                togglePassword.style.right = '15px';
                togglePassword.style.top = '50%';
                togglePassword.style.transform = 'translateY(-50%)';
                togglePassword.style.color = '#999';
                togglePassword.style.cursor = 'pointer';
                
                field.parentElement.appendChild(togglePassword);
                
                togglePassword.addEventListener('click', function() {
                    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
                    field.setAttribute('type', type);
                    this.className = `fas ${type === 'password' ? 'fa-eye' : 'fa-eye-slash'} toggle-password`;
                });
            }
            
            createTogglePassword(passwordField);
            createTogglePassword(confirmPasswordField);
            
            // Input animations
            const inputs = document.querySelectorAll('.input-with-icon input');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-5px)';
                    this.parentElement.style.transition = 'transform 0.3s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
            
            // Form submission
            registerForm.addEventListener('submit', function(e) {
                if (!validateCurrentStep()) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>