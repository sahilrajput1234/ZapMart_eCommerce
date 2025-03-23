<?php
require_once 'config/database.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database/wishlist.sql');
    
    // Execute the SQL
    $conn->exec($sql);
    
    echo "Wishlist table created successfully!";
} catch (PDOException $e) {
    echo "Error creating wishlist table: " . $e->getMessage();
}
?> 