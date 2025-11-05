<?php
// admin/admin-products.php
session_start();

// Define database credentials directly
$db_config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'marsilase_pastry'
];

// Create database connection
$conn = new mysqli(
    $db_config['servername'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['dbname']
);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ?page=admin-login');
    exit;
}

// Handle form actions
$message = '';
$message_type = '';

// Add new product
if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = 'uploads/' . $filename;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO cakes (name, description, price, category, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssi", $name, $description, $price, $category, $image_path, $is_active);
    
    if ($stmt->execute()) {
        $message = "Product added successfully!";
        $message_type = "success";
    } else {
        $message = "Error adding product: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Update product
if (isset($_POST['action']) && $_POST['action'] === 'update_product') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload if new image provided
    $image_sql = "";
    $params = [];
    $types = "ssdsi";
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = 'uploads/' . $filename;
            $image_sql = ", image_path = ?";
            $types .= "s";
            $params[] = $image_path;
        }
    }
    
    $stmt = $conn->prepare("UPDATE cakes SET name = ?, description = ?, price = ?, category = ?, is_active = ? $image_sql WHERE id = ?");
    $params = array_merge([$name, $description, $price, $category, $is_active], $params, [$id]);
    $types .= "i";
    
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $message = "Product updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating product: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Delete product
if (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $id = $_POST['id'];
    
    $stmt = $conn->prepare("DELETE FROM cakes WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting product: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Get all products
$products_result = $conn->query("
    SELECT * FROM cakes 
    ORDER BY created_at DESC
");

$products = [];
if ($products_result) {
    $products = $products_result->fetch_all(MYSQLI_ASSOC);
}

// Get product categories
$categories_result = $conn->query("SELECT DISTINCT category FROM cakes WHERE category IS NOT NULL");
$categories = [];
if ($categories_result) {
    $categories = $categories_result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="section">
    <div class="container-narrow">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-4 display-font mb-2">Product Management</h1>
                <p class="text-muted">Manage your cake products and inventory</p>
            </div>
            <a href="?page=admin-dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="card mb-5">
            <div class="card-header">
                <h5 class="mb-0">Add New Product</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (ETB)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" 
                                       list="categories" required>
                                <datalist id="categories">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['category']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Product Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active (available for purchase)</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Products</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-cake display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No products found</p>
                                        <a href="#add-product" class="btn btn-primary">Add Your First Product</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if ($product['image_path']): ?>
                                            <img src="../<?= htmlspecialchars($product['image_path']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px; border-radius: 8px;">
                                                <i class="bi bi-cake text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>
                                            <?= strlen($product['description']) > 100 ? '...' : '' ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?></td>
                                    <td>ETB <?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="editProduct(<?= $product['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editProductContent">
                <!-- Content loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editProduct(productId) {
    // Show loading state
    document.getElementById('editProductContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Create AJAX request to get product details
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?page=admin-get-product&product_id=${productId}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('editProductContent').innerHTML = xhr.responseText;
        } else {
            document.getElementById('editProductContent').innerHTML = `
                <div class="alert alert-danger">
                    Failed to load product details. Please try again.
                </div>
            `;
        }
    };
    xhr.onerror = function() {
        document.getElementById('editProductContent').innerHTML = `
            <div class="alert alert-danger">
                Network error. Please check your connection.
            </div>
        `;
    };
    xhr.send();
    
    // Show modal
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}
</script>

<?php 
// Close connection
$conn->close();
?>