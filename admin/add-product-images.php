<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: http://localhost/ecommercres/admin/login.php');
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['product_images'])) {
        $uploadDir = '../assets/images/products/';
        $uploadStatus = [];
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Handle multiple file uploads
        $files = $_FILES['product_images'];
        for ($i = 0; $i < count($files['name']); $i++) {
            $fileName = $files['name'][$i];
            $fileType = $files['type'][$i];
            $fileTmpName = $files['tmp_name'][$i];
            $fileError = $files['error'][$i];
            
            // Generate unique filename
            $uniqueName = uniqid() . '_' . $fileName;
            $targetPath = $uploadDir . $uniqueName;
            
            // Check if file is an image
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($fileType, $allowedTypes)) {
                $uploadStatus[] = [
                    'name' => $fileName,
                    'status' => 'error',
                    'message' => 'Invalid file type'
                ];
                continue;
            }
            
            if (move_uploaded_file($fileTmpName, $targetPath)) {
                // Save image path to database
                $imagePath = 'assets/images/products/' . $uniqueName;
                $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                
                if ($stmt->execute([$product_id, $imagePath])) {
                    $uploadStatus[] = [
                        'name' => $fileName,
                        'status' => 'success',
                        'message' => 'Image uploaded successfully'
                    ];
                } else {
                    $uploadStatus[] = [
                        'name' => $fileName,
                        'status' => 'error',
                        'message' => 'Failed to save to database'
                    ];
                }
            } else {
                $uploadStatus[] = [
                    'name' => $fileName,
                    'status' => 'error',
                    'message' => 'Failed to upload file'
                ];
            }
        }
        
        // Return JSON response for AJAX requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => $uploadStatus]);
            exit;
        }
    }
}

// Get existing product images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt->execute([$product_id]);
$existingImages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product Images - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f6fa;
            --border-color: #dcdde1;
        }

        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 3px solid var(--border-color);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        .page-loader.hide {
            opacity: 0;
            pointer-events: none;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .page-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin: 0;
        }

        .product-info {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .product-name {
            font-size: 1.2rem;
            color: var(--text-color);
            margin: 0 0 1rem 0;
        }

        .upload-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: var(--primary-color);
            background: var(--light-bg);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .upload-text {
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .upload-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .upload-btn:hover {
            background: #2980b9;
        }

        .preview-section {
            margin-top: 2rem;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .preview-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .preview-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .preview-remove {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .preview-remove:hover {
            background: #c0392b;
        }

        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 5px;
            color: white;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .notification.success {
            background: var(--secondary-color);
        }

        .notification.error {
            background: var(--danger-color);
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .preview-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader"></div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">📸 Add Product Images</h1>
            <a href="products.php" class="upload-btn">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <div class="product-info">
            <h2 class="product-name">
                <i class="fas fa-box"></i> 
                <?php echo htmlspecialchars($product['name']); ?>
            </h2>
        </div>

        <div class="upload-section">
            <form id="uploadForm" method="POST" enctype="multipart/form-data">
                <div class="upload-area" id="dropZone">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <p class="upload-text">Drag & Drop images here or click to select files</p>
                    <input type="file" id="fileInput" name="product_images[]" multiple accept="image/*" style="display: none;">
                    <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-plus"></i> Select Files
                    </button>
                </div>
            </form>

            <div class="preview-section">
                <h3>Existing Images</h3>
                <div class="preview-grid" id="imagePreview">
                    <?php foreach ($existingImages as $image): ?>
                        <div class="preview-item">
                            <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                 alt="Product Image" 
                                 class="preview-image">
                            <div class="preview-remove" data-id="<?php echo $image['id']; ?>">
                                <i class="fas fa-times"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Page Loader
        window.addEventListener('load', () => {
            document.querySelector('.page-loader').classList.add('hide');
        });

        // Drag and Drop functionality
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');
        const imagePreview = document.getElementById('imagePreview');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('highlight');
        }

        function unhighlight(e) {
            dropZone.classList.remove('highlight');
        }

        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            const formData = new FormData();
            
            [...files].forEach(file => {
                formData.append('product_images[]', file);
            });

            uploadFiles(formData);
        }

        function uploadFiles(formData) {
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                showNotification('Images uploaded successfully!', 'success');
                location.reload(); // Reload to show new images
            })
            .catch(error => {
                showNotification('Error uploading images', 'error');
            });
        }

        // Remove image functionality
        document.querySelectorAll('.preview-remove').forEach(button => {
            button.addEventListener('click', function() {
                const imageId = this.dataset.id;
                if (confirm('Are you sure you want to delete this image?')) {
                    // Add AJAX call to delete image
                    fetch(`delete-image.php?id=${imageId}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.preview-item').remove();
                            showNotification('Image deleted successfully', 'success');
                        } else {
                            showNotification('Error deleting image', 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('Error deleting image', 'error');
                    });
                }
            });
        });

        // Notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('show');
            }, 10);

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html> 