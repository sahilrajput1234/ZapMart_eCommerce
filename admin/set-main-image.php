<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get image ID from URL
$image_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get product ID for the image
    $stmt = $conn->prepare("SELECT product_id FROM product_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $product_id = $stmt->fetchColumn();

    if (!$product_id) {
        throw new Exception('Image not found');
    }

    // Start transaction
    $conn->beginTransaction();

    // Reset all images for this product to not be main
    $stmt = $conn->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?");
    $stmt->execute([$product_id]);

    // Set the selected image as main
    $stmt = $conn->prepare("UPDATE product_images SET is_main = 1 WHERE id = ?");
    $stmt->execute([$image_id]);

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 