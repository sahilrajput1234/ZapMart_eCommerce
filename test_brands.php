<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Brand Test Page</h1>";

// Test 1: Check if brands table exists
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'brands'");
    $tableExists = $stmt->rowCount() > 0;
    echo "<p>Test 1: Brands table exists: " . ($tableExists ? "YES" : "NO") . "</p>";
} catch (PDOException $e) {
    echo "<p>Test 1 Error: " . $e->getMessage() . "</p>";
}

// Test 2: Try to get all brands
try {
    $stmt = $conn->query("SELECT * FROM brands WHERE status = 'active' ORDER BY name");
    $brands = $stmt->fetchAll();
    echo "<p>Test 2: Successfully retrieved " . count($brands) . " brands</p>";
    
    echo "<ul>";
    foreach ($brands as $brand) {
        echo "<li>" . htmlspecialchars($brand['name']) . " - Logo: " . $brand['logo'] . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p>Test 2 Error: " . $e->getMessage() . "</p>";
}

// Test 3: Try to get featured brands (brands with most products)
try {
    $stmt = $conn->query("
        SELECT b.*, COUNT(p.id) as product_count 
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id 
        WHERE b.status = 'active' 
        GROUP BY b.id 
        ORDER BY product_count DESC 
        LIMIT 4
    ");
    $featuredBrands = $stmt->fetchAll();
    echo "<p>Test 3: Successfully retrieved " . count($featuredBrands) . " featured brands</p>";
    
    echo "<ul>";
    foreach ($featuredBrands as $brand) {
        echo "<li>" . htmlspecialchars($brand['name']) . " - Products: " . $brand['product_count'] . "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p>Test 3 Error: " . $e->getMessage() . "</p>";
}

// Test 4: Check if brand images exist
echo "<h2>Brand Images:</h2>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
foreach ($brands as $brand) {
    echo "<div style='text-align: center; margin-bottom: 20px;'>";
    echo "<img src='" . $brand['logo'] . "' alt='" . htmlspecialchars($brand['name']) . "' style='width: 150px; height: 150px;'><br>";
    echo "<span>" . htmlspecialchars($brand['name']) . "</span>";
    echo "</div>";
}
echo "</div>";
?> 