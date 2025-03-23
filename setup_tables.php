<?php
require_once 'config/database.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database/orders.sql');
    
    // Execute the SQL commands
    $conn->exec($sql);
    
    echo "Tables created successfully!";
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?> 