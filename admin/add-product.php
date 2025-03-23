<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Function to generate a unique slug
function generateUniqueSlug($conn, $name) {
    // Convert the name to lowercase and replace spaces with hyphens
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Remove leading and trailing hyphens
    $slug = trim($slug, '-');
    
    // Check if the slug already exists
    $originalSlug = $slug;
    $counter = 1;
    
    do {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    } while ($count > 0);
    
    return $slug;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    
    try {
        // Generate a unique slug for the product
        $slug = generateUniqueSlug($conn, $name);
        
        // Insert product with the generated slug
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, slug, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $slug]);
        $product_id = $conn->lastInsertId();

        // Handle image upload
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $fileName = time() . '_' . $_FILES['images']['name'][$key];
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $filePath)) {
                    $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                    $stmt->execute([$product_id, 'uploads/products/' . $fileName]);
                }
            }
        }

        $_SESSION['success_msg'] = "Product added successfully! 🎉";
        header('Location: products.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding product: " . $e->getMessage();
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
    <title>Add Product - ZapMart Admin</title>
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

        /* Page Loader */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loader {
            width: 60px;
            height: 60px;
            position: relative;
        }

        .loader-circle {
            width: 100%;
            height: 100%;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        .loader-emoji {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            animation: bounce 1.5s infinite;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        @keyframes bounce {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
        }

        .page-loader.hide {
            opacity: 0;
            pointer-events: none;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--white);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 20px 0;
            z-index: 100;
        }

        .sidebar-header {
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 10px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--light-bg);
            color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            background: var(--light-bg);
            min-height: 100vh;
            overflow-y: auto;
        }

        .page-header {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.5rem;
            margin: 0;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-emoji {
            font-size: 2rem;
            animation: wave 2s infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
        }

        /* Form Container */
        .form-container {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        /* Image Upload */
        .image-upload {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .image-upload:hover {
            border-color: var(--primary-color);
            background: rgba(52, 152, 219, 0.05);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .upload-text {
            color: #666;
            margin-bottom: 10px;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .image-preview {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        /* Buttons */
        .btn-container {
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

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 10px;
            }

            .main-content {
                padding: 10px;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Page Loader -->
    <div class="page-loader">
        <div class="loader">
            <div class="loader-circle"></div>
            <div class="loader-emoji">📦</div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-logo">
                <i class="fas fa-bolt"></i>
                ZapMart Admin
            </a>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link active">
                    <i class="fas fa-box"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link">
                    <i class="fas fa-tags"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-emoji">📦</span>
                Add New Product
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
                    <label class="form-label">Product Name 📝</label>
                    <input type="text" name="name" class="form-control" required
                           placeholder="Enter product name">
                </div>

                <div class="form-group">
                    <label class="form-label">Description 📄</label>
                    <textarea name="description" class="form-control" rows="4" required
                              placeholder="Enter product description"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Price 💰</label>
                    <input type="number" name="price" class="form-control" step="0.01" required
                           placeholder="Enter product price">
                </div>

                <div class="form-group">
                    <label class="form-label">Stock Quantity 📦</label>
                    <input type="number" name="stock" class="form-control" required
                           placeholder="Enter stock quantity">
                </div>

                <div class="form-group">
                    <label class="form-label">Category 🏷️</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Product Images 🖼️</label>
                    <div class="image-upload" id="imageUpload">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <p class="upload-text">Drag & drop images here or click to browse</p>
                        <p class="form-text">Upload up to 5 images (PNG, JPG, JPEG)</p>
                        <input type="file" name="images[]" multiple accept="image/*" style="display: none" id="imageInput">
                    </div>
                    <div class="preview-container" id="imagePreview"></div>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
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
        const imagePreview = document.getElementById('imagePreview');
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
            if (imagePreview.children.length + files.length > maxImages) {
                alert(`You can only upload up to ${maxImages} images`);
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
                    preview.className = 'image-preview';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-image" onclick="removePreview(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    imagePreview.appendChild(preview);
                };
                reader.readAsDataURL(file);
            });
        }

        function removePreview(button) {
            button.parentElement.remove();
        }

        // Form Validation
        const productForm = document.getElementById('productForm');
        productForm.addEventListener('submit', (e) => {
            const name = productForm.querySelector('[name="name"]').value;
            const description = productForm.querySelector('[name="description"]').value;
            const price = productForm.querySelector('[name="price"]').value;
            const stock = productForm.querySelector('[name="stock"]').value;
            const category = productForm.querySelector('[name="category_id"]').value;

            if (!name || !description || !price || !stock || !category) {
                e.preventDefault();
                alert('Please fill in all required fields');
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