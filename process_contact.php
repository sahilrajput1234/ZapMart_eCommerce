<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // If no errors, save to database
    if (empty($errors)) {
        try {
            // Prepare the SQL statement
            $stmt = $conn->prepare("
                INSERT INTO contact_messages (name, email, subject, message, created_at)
                VALUES (:name, :email, :subject, :message, NOW())
            ");
            
            // Bind parameters
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            
            // Execute the statement
            $success = $stmt->execute();
            
            if ($success) {
                // Set success message
                $_SESSION['contact_success'] = "Thank you for your message! We'll get back to you soon.";
            } else {
                // Set error message
                $_SESSION['contact_error'] = "Sorry, there was a problem sending your message. Please try again later.";
            }
            
        } catch (Exception $e) {
            // Set error message
            $_SESSION['contact_error'] = "An error occurred: " . $e->getMessage();
        }
    } else {
        // Set error messages
        $_SESSION['contact_error'] = implode("<br>", $errors);
    }
} else {
    // If not a POST request, set error message
    $_SESSION['contact_error'] = "Invalid request method";
}

// Redirect back to contact page
header('Location: contact.php');
exit;
?> 