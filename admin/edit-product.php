<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
try {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_msg'] = "Product not found! 😕";
        header('Location: products.php');
        exit;
    }

    // Get product images
    $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error fetching product: " . $e->getMessage();
    header('Location: products.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $regular_price = !empty($_POST['regular_price']) ? (float)$_POST['regular_price'] : null;
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $status = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    
    try {
        // Update product
        $stmt = $conn->prepare("
            UPDATE products 
            SET name = ?, description = ?, price = ?, regular_price = ?, 
                sale_price = ?, stock = ?, category_id = ?, status = ?,
                is_featured = ?, is_new = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $name, $description, $price, $regular_price, $sale_price, 
            $stock, $category_id, $status, $is_featured, $is_new, $product_id
        ]);

        // Handle new image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $fileName = time() . '_' . $_FILES['images']['name'][$key];
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $filePath)) {
                    $stmt = $conn->prepare("
                        INSERT INTO product_images (product_id, image_url, sort_order) 
                        VALUES (?, ?, (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM product_images pi WHERE product_id = ?))
                    ");
                    $stmt->execute([$product_id, 'uploads/products/' . $fileName, $product_id]);
                }
            }
        }

        $_SESSION['success_msg'] = "Product updated successfully! 🎉";
        header('Location: products.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error updating product: " . $e->getMessage();
    }
}

// Get categories for dropdown
try {
    $stmt = $conn->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $_SESSION['error_msg'] = "Error fetching categories: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - ZapMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --text-color: #333;
            --light-bg: #f5f6fa;
            --border-color: #dcdde1;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
        }

        /* Page Loader with Emoji */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loader {
            text-align: center;
        }

        .loader-emoji {
            font-size: 48px;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .loader-text {
            margin-top: 15px;
            color: var(--text-color);
            font-size: 18px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        .page-loader.hide {
            opacity: 0;
            pointer-events: none;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            background: var(--light-bg);
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Form Container with Animation */
        .form-container {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
            transform: translateY(20px);
            opacity: 0;
            animation: slideIn 0.6s forwards;
        }

        @keyframes slideIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Form Groups with Hover Effects */
        .form-group {
            margin-bottom: 25px;
            position: relative;
            transition: transform 0.3s ease;
        }

        .form-group:hover {
            transform: translateX(5px);
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* Image Gallery */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .image-item:hover {
            transform: scale(1.05);
        }

        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .image-actions {
            position: absolute;
            top: 5px;
            right: 5px;
            display: flex;
            gap: 5px;
        }

        .image-action-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-color);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .image-action-btn:hover {
            background: var(--white);
            transform: scale(1.1);
        }

        .delete-btn:hover {
            color: var(--danger-color);
        }

        .main-btn:hover {
            color: var(--primary-color);
        }

        /* Custom Checkboxes */
        .custom-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox input {
            display: none;
        }

        .checkbox-icon {
            width: 22px;
            height: 22px;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .custom-checkbox input:checked + .checkbox-icon {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkbox-icon i {
            color: white;
            font-size: 14px;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
        }

        .custom-checkbox input:checked + .checkbox-icon i {
            opacity: 1;
            transform: scale(1);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f1f2f6;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: #dcdde1;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .image-gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-emoji">📦</div>
            <div class="loader-text">Preparing your product...</div>
        </div>
    </div>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-emoji">✏️</span>
                Edit Product
            </h1>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                    echo $_SESSION['success_msg'];
                    unset($_SESSION['success_msg']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    echo $_SESSION['error_msg'];
                    unset($_SESSION['error_msg']);
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tag"></i>
                        Product Name
                    </label>
                    <input type="text" name="name" class="form-control" required
                           value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-align-left"></i>
                        Description
                    </label>
                    <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-dollar-sign"></i>
                        Price
                    </label>
                    <input type="number" name="price" class="form-control" step="0.01" required
                           value="<?php echo $product['price']; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tag"></i>
                        Regular Price
                    </label>
                    <input type="number" name="regular_price" class="form-control" step="0.01"
                           value="<?php echo $product['regular_price']; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-percentage"></i>
                        Sale Price
                    </label>
                    <input type="number" name="sale_price" class="form-control" step="0.01"
                           value="<?php echo $product['sale_price']; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-box"></i>
                        Stock Quantity
                    </label>
                    <input type="number" name="stock" class="form-control" required
                           value="<?php echo $product['stock']; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-folder"></i>
                        Category
                    </label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        Status
                    </label>
                    <select name="status" class="form-control" required>
                        <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                        <span class="checkbox-icon">
                            <i class="fas fa-check"></i>
                        </span>
                        Featured Product
                    </label>
                </div>

                <div class="form-group">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="is_new" <?php echo $product['is_new'] ? 'checked' : ''; ?>>
                        <span class="checkbox-icon">
                            <i class="fas fa-check"></i>
                        </span>
                        New Product
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-images"></i>
                        Product Images
                    </label>
                    <div class="image-upload" id="imageUpload">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <p class="upload-text">Drag & drop images here or click to browse</p>
                        <p class="form-text">Upload up to 5 images (PNG, JPG, JPEG)</p>
                        <input type="file" name="images[]" multiple accept="image/*" style="display: none" id="imageInput">
                    </div>

                    <?php if (!empty($product_images)): ?>
                        <div class="image-gallery">
                            <?php foreach ($product_images as $image): ?>
                                <div class="image-item" data-id="<?php echo $image['id']; ?>">
                                    <img src="<?php echo '../' . $image['image_url']; ?>" alt="Product Image">
                                    <div class="image-actions">
                                        <button type="button" class="image-action-btn main-btn" title="Set as main image"
                                                onclick="setMainImage(<?php echo $image['id']; ?>)">
                                            <i class="fas fa-star"></i>
                                        </button>
                                        <button type="button" class="image-action-btn delete-btn" title="Delete image"
                                                onclick="deleteImage(<?php echo $image['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Page Loader
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.querySelector('.page-loader').classList.add('hide');
            }, 800);
        });

        // Image Upload Preview
        const imageUpload = document.getElementById('imageUpload');
        const imageInput = document.getElementById('imageInput');
        const maxImages = 5;

        imageUpload.addEventListener('click', () => imageInput.click());

        imageUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUpload.style.borderColor = 'var(--primary-color)';
            imageUpload.style.background = 'rgba(52, 152, 219, 0.05)';
        });

        imageUpload.addEventListener('dragleave', () => {
            imageUpload.style.borderColor = 'var(--border-color)';
            imageUpload.style.background = 'none';
        });

        imageUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUpload.style.borderColor = 'var(--border-color)';
            imageUpload.style.background = 'none';
            
            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        imageInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            const currentImages = document.querySelectorAll('.image-item').length;
            if (currentImages + files.length > maxImages) {
                alert(`You can only have up to ${maxImages} images`);
                return;
            }

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    alert('Please upload only image files');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    const preview = document.createElement('div');
                    preview.className = 'image-item';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="image-actions">
                            <button type="button" class="image-action-btn delete-btn" onclick="removePreview(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    document.querySelector('.image-gallery').appendChild(preview);
                };
                reader.readAsDataURL(file);
            });
        }

        function removePreview(button) {
            button.closest('.image-item').remove();
        }

        // Image Management Functions
        function setMainImage(imageId) {
            fetch(`set-main-image.php?id=${imageId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error setting main image');
                }
            });
        }

        function deleteImage(imageId) {
            if (confirm('Are you sure you want to delete this image?')) {
                fetch(`delete-image.php?id=${imageId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-id="${imageId}"]`).remove();
                    } else {
                        alert('Error deleting image');
                    }
                });
            }
        }

        // Form Validation
        const productForm = document.getElementById('productForm');
        productForm.addEventListener('submit', (e) => {
            const price = parseFloat(productForm.querySelector('[name="price"]').value);
            const regularPrice = parseFloat(productForm.querySelector('[name="regular_price"]').value);
            const salePrice = parseFloat(productForm.querySelector('[name="sale_price"]').value);

            if (regularPrice && price > regularPrice) {
                e.preventDefault();
                alert('Regular price must be greater than or equal to the current price');
            }

            if (salePrice && (salePrice > price || salePrice > regularPrice)) {
                e.preventDefault();
                alert('Sale price must be less than both current price and regular price');
            }
        });

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html> 