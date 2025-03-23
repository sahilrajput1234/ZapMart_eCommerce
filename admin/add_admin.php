<?php
require_once '../config/database.php';

// Admin credentials
$username = 'mohd';
$password = 'Sahil123';
$email = 'mohd@gmail.com';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "Admin user already exists!";
    } else {
        // Insert new admin
        $stmt = $conn->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $hashedPassword, $email])) {
            echo "Admin user created successfully!<br>";
            echo "Username: " . htmlspecialchars($username) . "<br>";
            echo "Email: " . htmlspecialchars($email) . "<br>";
            echo "Password: " . $password . "<br>";
            echo "<br>You can now login at <a href='login.php'>login page</a>";
        } else {
            echo "Error creating admin user.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 