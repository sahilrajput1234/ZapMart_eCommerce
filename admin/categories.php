<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Function to create a URL-friendly slug from a string
function createSlug($string) {
    // Convert the string to lowercase
    $string = strtolower($string);
    
    // Replace spaces with hyphens
    $string = str_replace(' ', '-', $string);
    
    // Remove special characters
    $string = preg_replace('/[^a-z0-9\-]/', '', $string);
    
    // Remove multiple consecutive hyphens
    $string = preg_replace('/-+/', '-', $string);
    
    // Remove leading and trailing hyphens
    $string = trim($string, '-');
    
    return $string;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            
            // Add new category
            if ($action === 'add') {
                $name = trim($_POST['name']);
                $description = trim($_POST['description'] ?? '');
                $slug = createSlug($name);
                $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                
                // Validate input
                if (empty($name)) {
                    throw new Exception("Category name is required");
                }
                
                // Check if slug already exists
                $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
                $stmt->execute([$slug]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Category with this name already exists");
                }
                
                // Insert category
                $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, parent_id, created_at) 
                                      VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $slug, $description, $parent_id]);
                
                $_SESSION['success_msg'] = "Category added successfully! 🎉";
            }
            
            // Delete category
            elseif ($action === 'delete' && isset($_POST['category_id'])) {
                $category_id = (int)$_POST['category_id'];
                
                // Check if category has products
                $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $stmt->execute([$category_id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Cannot delete category with associated products");
                }
                
                // Delete category
                $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                
                $_SESSION['success_msg'] = "Category deleted successfully! 🗑️";
            }
            
            // Update category
            elseif ($action === 'update' && isset($_POST['category_id'])) {
                $category_id = (int)$_POST['category_id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description'] ?? '');
                $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                
                // Validate input
                if (empty($name)) {
                    throw new Exception("Category name is required");
                }
                
                // Update category
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, 
                                      updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $description, $parent_id, $category_id]);
                
                $_SESSION['success_msg'] = "Category updated successfully! ✨";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = $e->getMessage();
    }
}

// Fetch all categories
try {
    $stmt = $conn->query("
        SELECT c.*, 
               p.name as parent_name,
               (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.id
        ORDER BY c.created_at DESC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch parent categories for dropdown
    $stmt = $conn->query("SELECT id, name FROM categories WHERE parent_id IS NULL");
    $parent_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error fetching categories: " . $e->getMessage();
    $categories = [];
    $parent_categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - ZapMart Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --success-color: #2ecc71;
            --text-color: #2c3e50;
            --light-text: #95a5a6;
            --border-color: #ecf0f1;
            --light-bg: #f9fafb;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInDown 0.5s ease;
        }

        .page-title {
            font-size: 1.8rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-emoji {
            font-size: 2.2rem;
            animation: bounce 2s infinite;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            animation: fadeInUp 0.5s ease;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--light-bg);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--text-color);
        }

        .stat-info p {
            margin: 5px 0 0;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Category Grid */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
            animation: fadeInUp 0.5s ease 0.2s backwards;
        }

        .category-card {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s forwards;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .category-name {
            font-size: 1.2rem;
            color: var(--text-color);
            margin: 0;
            font-weight: 600;
        }

        .category-parent {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-top: 5px;
        }

        .category-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--light-text);
            cursor: pointer;
            font-size: 1rem;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .action-btn.delete:hover {
            color: var(--danger-color);
        }

        .category-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Add Category Form */
        .add-category {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            animation: fadeInUp 0.5s ease;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: var(--white);
            cursor: pointer;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 1.3rem;
            color: var(--text-color);
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--light-text);
            cursor: pointer;
            font-size: 1.5rem;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: var(--text-color);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        /* Alert Messages */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            color: var(--white);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background: var(--success-color);
        }

        .alert-error {
            background: var(--danger-color);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .category-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar (reuse the same sidebar from other admin pages) -->
    <div class="sidebar">
        <!-- ... Sidebar content ... -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <span class="page-emoji">📑</span>
                Categories
            </h1>
            <button class="btn btn-primary" onclick="showAddModal()">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--primary-color);">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($categories); ?></h3>
                    <p>Total Categories</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--secondary-color);">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($parent_categories); ?></h3>
                    <p>Parent Categories</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--warning-color);">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3><?php 
                        echo array_sum(array_column($categories, 'product_count')); 
                    ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
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

        <!-- Categories Grid -->
        <div class="category-grid">
            <?php foreach ($categories as $index => $category): ?>
                <div class="category-card" style="animation-delay: <?php echo $index * 0.1; ?>s">
                    <div class="category-header">
                        <div>
                            <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <?php if ($category['parent_name']): ?>
                                <div class="category-parent">
                                    <i class="fas fa-level-up-alt"></i> 
                                    <?php echo htmlspecialchars($category['parent_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="category-actions">
                            <button class="action-btn" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <p class="category-description">
                        <?php echo !empty($category['description']) ? 
                            htmlspecialchars($category['description']) : 
                            '<em>No description</em>'; ?>
                    </p>
                    <div class="category-info">
                        <div class="info-item">
                            <i class="fas fa-box"></i>
                            <?php echo $category['product_count']; ?> Products
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add Category</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="categoryForm" method="POST" action="">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="category_id" id="categoryId">
                
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="categoryName" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Parent Category (Optional)</label>
                    <select name="parent_id" id="parentCategory" class="form-select">
                        <option value="">None</option>
                        <?php foreach ($parent_categories as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>">
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="categoryDescription" class="form-input" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Category
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Delete Category</h2>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <p>Are you sure you want to delete this category? This action cannot be undone.</p>
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="category_id" id="deleteCategoryId">
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show/hide modals
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModal').style.display = 'block';
        }

        function editCategory(category) {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('formAction').value = 'update';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description;
            document.getElementById('parentCategory').value = category.parent_id || '';
            document.getElementById('categoryModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function deleteCategory(categoryId) {
            document.getElementById('deleteCategoryId').value = categoryId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Form validation
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            const name = document.getElementById('categoryName').value.trim();
            if (!name) {
                e.preventDefault();
                alert('Category name is required!');
            }
        });
    </script>
</body>
</html> 