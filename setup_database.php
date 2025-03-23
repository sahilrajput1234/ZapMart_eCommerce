<?php
// Database setup script

// Read the SQL file
$sqlFile = file_get_contents('database.sql');

// Split SQL by semicolon
$queries = explode(';', $sqlFile);

// Database credentials
$db_host = 'localhost';
$db_name = 'ecommercres';
$db_user = 'root';
$db_pass = '';

try {
    // First connect without selecting database
    $dsn = "mysql:host=$db_host";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "Database '$db_name' created or already exists.<br>";
    
    // Select the database
    $pdo->exec("USE `$db_name`");
    echo "Selected database '$db_name'.<br>";
    
    // Execute each query
    $queryCount = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $pdo->exec($query);
            $queryCount++;
        } catch (PDOException $e) {
            echo "Error executing query: " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "Query: <pre>" . htmlspecialchars(substr($query, 0, 300)) . "...</pre><br>";
        }
    }
    
    echo "Successfully executed $queryCount queries.<br>";
    echo "<strong>Database setup completed successfully!</strong><br>";
    echo "<a href='index.php'>Go to homepage</a>";
    
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
} 