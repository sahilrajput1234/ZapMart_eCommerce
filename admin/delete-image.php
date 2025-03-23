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
    // Get image details
    $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch();

    if (!$image) {
        throw new Exception('Image not found');
    }

    // Delete the physical file
    $file_path = '../' . $image['image_url'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Delete the database record
    $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$image_id]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 