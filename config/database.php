<?php
/**
 * Database Configuration
 */

// Database credentials
$db_host = 'localhost';
$db_name = 'ecommercres';
$db_user = 'root';
$db_pass = '';
$db_charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$db_host;charset=$db_charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Create PDO instance
try {
    // First, try to connect without specifying the database
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
    
    // Check if database exists
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    $dbExists = $stmt->fetchColumn();
    
    if (!$dbExists) {
        // Create database if it doesn't exist
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        echo "<div style='background: #dff0d8; color: #3c763d; padding: 10px; margin: 10px 0; border: 1px solid #d6e9c6;'>
              Database '$db_name' created successfully.</div>";
    }
    
    // Select the database
    $conn->exec("USE `$db_name`");
    
    // Check if tables exist
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<div style='background: #fcf8e3; color: #8a6d3b; padding: 10px; margin: 10px 0; border: 1px solid #faebcc;'>
              No tables found in the database. Please run the database.sql script.</div>";
    }
    
} catch (PDOException $e) {
    // Show detailed error message with debugging information
    $errorMessage = 'Database Connection Error: ' . $e->getMessage();
    $errorDetails = '<pre>' . print_r(['DSN' => $dsn, 'User' => $db_user, 'Error' => $e->getMessage()], true) . '</pre>';
    
    echo "<div style='background: #f2dede; color: #a94442; padding: 10px; margin: 10px 0; border: 1px solid #ebccd1;'>
          <h3>Database Connection Failed</h3>
          <p>{$errorMessage}</p>
          <details>
            <summary>Error Details (click to expand)</summary>
            {$errorDetails}
          </details>
          <p>Please check your database configuration and make sure MySQL is running.</p>
        </div>";
    die();
}

/**
 * Helper function to execute a query and return all rows
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Results of query
 */
function db_query($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Helper function to execute a query and return a single row
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|false Single row or false if no result
 */
function db_query_row($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Helper function to execute a query and return the number of affected rows
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return int Number of affected rows
 */
function db_execute($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Helper function to get the last inserted ID
 * 
 * @return string Last inserted ID
 */
function db_last_insert_id() {
    global $conn;
    return $conn->lastInsertId();
}
?> 