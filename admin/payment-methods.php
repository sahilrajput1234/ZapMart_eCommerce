<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_method'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon']);
        
        $sql = "INSERT INTO payment_methods (name, description, icon) VALUES ('$name', '$description', '$icon')";
        if ($conn->query($sql)) {
            $success_message = "Payment method added successfully!";
        } else {
            $error_message = "Error adding payment method: " . $conn->error;
        }
    } elseif (isset($_POST['update_method'])) {
        $id = (int)$_POST['id'];
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "UPDATE payment_methods SET name='$name', description='$description', icon='$icon', is_active=$is_active WHERE id=$id";
        if ($conn->query($sql)) {
            $success_message = "Payment method updated successfully!";
        } else {
            $error_message = "Error updating payment method: " . $conn->error;
        }
    } elseif (isset($_POST['delete_method'])) {
        $id = (int)$_POST['id'];
        
        $sql = "DELETE FROM payment_methods WHERE id=$id";
        if ($conn->query($sql)) {
            $success_message = "Payment method deleted successfully!";
        } else {
            $error_message = "Error deleting payment method: " . $conn->error;
        }
    }
}

// Get all payment methods
$sql = "SELECT * FROM payment_methods ORDER BY created_at DESC";
$result = $conn->query($sql);
$payment_methods = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payment_methods[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Payment Methods</h1>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMethodModal">
                        Add New Payment Method
                    </button>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_methods as $method): ?>
                            <tr>
                                <td><i class="<?php echo htmlspecialchars($method['icon']); ?>"></i></td>
                                <td><?php echo htmlspecialchars($method['name']); ?></td>
                                <td><?php echo htmlspecialchars($method['description']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $method['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $method['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-btn" 
                                            data-id="<?php echo $method['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($method['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($method['description']); ?>"
                                            data-icon="<?php echo htmlspecialchars($method['icon']); ?>"
                                            data-active="<?php echo $method['is_active']; ?>"
                                            data-toggle="modal" 
                                            data-target="#editMethodModal">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn"
                                            data-id="<?php echo $method['id']; ?>"
                                            data-toggle="modal"
                                            data-target="#deleteMethodModal">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Method Modal -->
    <div class="modal fade" id="addMethodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Payment Method</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Icon (Font Awesome class)</label>
                            <input type="text" class="form-control" name="icon" placeholder="fas fa-credit-card">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="add_method" class="btn btn-primary">Add Method</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Method Modal -->
    <div class="modal fade" id="editMethodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Payment Method</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Icon (Font Awesome class)</label>
                            <input type="text" class="form-control" name="icon" id="edit_icon">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="is_active" id="edit_active">
                                <label class="custom-control-label" for="edit_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_method" class="btn btn-primary">Update Method</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Method Modal -->
    <div class="modal fade" id="deleteMethodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Payment Method</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete this payment method?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_method" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle edit button clicks
            $('.edit-btn').click(function() {
                $('#edit_id').val($(this).data('id'));
                $('#edit_name').val($(this).data('name'));
                $('#edit_description').val($(this).data('description'));
                $('#edit_icon').val($(this).data('icon'));
                $('#edit_active').prop('checked', $(this).data('active') == 1);
            });

            // Handle delete button clicks
            $('.delete-btn').click(function() {
                $('#delete_id').val($(this).data('id'));
            });
        });
    </script>
</body>
</html> 