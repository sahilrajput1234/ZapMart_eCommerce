<?php
require_once '../config/database.php';

// Admin credentials
$username = 'mohd';
$password = 'Sahil123';
$email = 'mohd@gmail.com';

// Hash the password properly
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // First, delete any existing user with same username or email
    $stmt = $conn->prepare("DELETE FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);

    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $hashedPassword, $email])) {
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f0f8ff; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>";
        echo "<h2 style='color: #2980b9;'>Admin User Created Successfully! ✅</h2>";
        echo "<div style='background: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
        echo "<p><strong>Password:</strong> " . $password . "</p>";
        echo "</div>";
        echo "<p>You can now <a href='login.php' style='color: #2980b9; text-decoration: none;'>login to the admin panel</a></p>";
        echo "</div>";
    } else {
        echo "Error creating admin user.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 