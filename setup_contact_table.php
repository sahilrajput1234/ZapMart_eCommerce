<?php
require_once 'config/database.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database/contact_messages.sql');
    
    // Execute the SQL
    $conn->exec($sql);
    
    echo "Contact messages table created successfully!";
} catch (PDOException $e) {
    echo "Error creating contact messages table: " . $e->getMessage();
}
?> 