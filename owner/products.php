<?php
session_start();
require_once '../common/connection.php';
requireOwner();
requirePermission('manage_products');

$message = '';
$error = '';

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_product') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? 'Cookies');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        if (empty($name) || $price <= 0) {
            $error = 'Product name and price are required';
        } else {
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_dir = '../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = 'uploads/products/' . $filename;
                } else {
                    $error = 'Image upload failed, but product was added';
                }
            }
            
            // Get category_id based on category name
            $category_id = null;
            $cat_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
            if ($cat_stmt) {
                $cat_stmt->bind_param("s", $category);
                $cat_stmt->execute();
                $cat_result = $cat_stmt->get_result();
                if ($cat_result->num_rows > 0) {
                    $cat_row = $cat_result->fetch_assoc();
                    $category_id = $cat_row['id'];
                }
                $cat_stmt->close();
            }
            
            // Insert product
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, category, image_path, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("ssdissii", $name, $description, $price, $category_id, $category, $image_path, $is_active, $is_featured);
                
                if ($stmt->execute()) {
                    $message = 'Product added successfully!' . ($image_path ? ' With image.' : '');
                } else {
                    $error = 'Failed to add product: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
    
    elseif ($action === 'update_product') {
        $product_id = intval($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? 'Cookies');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        // Get category_id first
        $category_id = null;
        $cat_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        if ($cat_stmt) {
            $cat_stmt->bind_param("s", $category);
            $cat_stmt->execute();
            $cat_result = $cat_stmt->get_result();
            if ($cat_result->num_rows > 0) {
                $cat_row = $cat_result->fetch_assoc();
                $category_id = $cat_row['id'];
            }
            $cat_stmt->close();
        }
        
        // Handle image update
        $has_new_image = false;
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'uploads/products/' . $filename;
                $has_new_image = true;
            }
        }
        
        if ($has_new_image) {
            // Update with new image
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, category = ?, image_path = ?, is_active = ?, is_featured = ? WHERE id = ?");
            
            if ($stmt) {
                $stmt->bind_param("ssdisiiii", $name, $description, $price, $category_id, $category, $image_path, $is_active, $is_featured, $product_id);
                
                if ($stmt->execute()) {
                    $message = 'Product updated successfully!';
                } else {
                    $error = 'Failed to update product: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, category = ?, is_active = ?, is_featured = ? WHERE id = ?");
            
            if ($stmt) {
                $stmt->bind_param("ssdiiiii", $name, $description, $price, $category_id, $category, $is_active, $is_featured, $product_id);
                
                if ($stmt->execute()) {
                    $message = 'Product updated successfully!';
                } else {
                    $error = 'Failed to update product: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
    
    elseif ($action === 'delete_product') {
        $product_id = intval($_POST['product_id'] ?? 0);
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        
        if ($stmt) {
            $stmt->bind_param("i", $product_id);
            
            if ($stmt->execute()) {
                $message = 'Product deleted successfully!';
            } else {
                $error = 'Failed to delete product: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    }
}

// Get all products
$products = [];
$categories = [];

// Get products with their category names
$products_result = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
if ($products_result) {
    $products = $products_result->fetch_all(MYSQLI_ASSOC);
}

// Get available categories
$categories_result = $conn->query("SELECT name FROM categories ORDER BY display_order");
if ($categories_result) {
    $categories = $categories_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Owner Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --owner-primary: #2c3e50;
            --owner-secondary: #34495e;
            --owner-accent: #e74c3c;
        }
        
        .owner-nav { 
            background: var(--owner-primary); 
        }
        
        .btn-owner {
            background: var(--owner-primary);
            color: white;
            border: none;
        }
        
        .btn-owner:hover {
            background: var(--owner-secondary);
            color: white;
        }
        
        .product-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e9ecef;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .current-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg owner-nav">
        <div class="container">
            <span class="navbar-brand text-white">
                <i class="bi bi-box-seam me-2"></i>Product Management
            </span>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="nav-link text-white me-3">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <a href="manage_admins.php" class="nav-link text-white me-3">
                    <i class="bi bi-people me-1"></i>Admins
                </a>
                <a href="reports.php" class="nav-link text-white me-3">
                    <i class="bi bi-graph-up me-1"></i>Reports
                </a>
                <a href="logout.php" class="nav-link text-white">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Product Management</h2>
            <div>
                <span class="badge bg-primary me-2"><?= count($products) ?> products</span>
                <button class="btn btn-owner" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Product
                </button>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Products Grid -->
        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100">
                    <?php if (!empty($product['image_path'])): ?>
                    <img src="../<?= $product['image_path'] ?>" class="card-img-top" alt="<?= $product['name'] ?>" style="height: 200px; object-fit: cover;" onerror="this.style.display='none'">
                    <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars($product['description']) ?></p>
                        
                        <div class="mb-2">
                            <span class="badge bg-primary">ETB <?= number_format($product['price'], 2) ?></span>
                            <?php if (!empty($product['category_name'])): ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($product['category_name']) ?></span>
                            <?php endif; ?>
                            <span class="badge <?= $product['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                            <?php if ($product['is_featured']): ?>
                            <span class="badge bg-warning">Featured</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-auto">
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($product)) ?>)">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="bi bi-box"></i>
            <h5>No products found</h5>
            <p>Get started by adding your first product to the system.</p>
            <button class="btn btn-owner" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle me-2"></i>Add First Product
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Describe the product..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category *</label>
                                    <select class="form-select" name="category" required>
                                        <option value="Cookies">Cookies</option>
                                        <option value="Arabian Sweets">Arabian Sweets</option>
                                        <option value="Torta Cake">Torta Cake</option>
                                        <option value="Mini cakes">Mini cakes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price (ETB) *</label>
                                    <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <div class="form-text">Recommended: Square image, max 2MB</div>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" checked>
                                    <label class="form-check-label">Active Product</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured">
                                    <label class="form-check-label">Featured Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-owner">Add Product</button>
                    </div>
                </form>
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
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_product">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="Describe the product..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category *</label>
                                    <select class="form-select" name="category" id="edit_category" required>
                                        <option value="Cookies">Cookies</option>
                                        <option value="Arabian Sweets">Arabian Sweets</option>
                                        <option value="Torta Cake">Torta Cake</option>
                                        <option value="Mini cakes">Mini cakes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price (ETB) *</label>
                                    <input type="number" class="form-control" name="price" id="edit_price" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <div class="form-text">Recommended: Square image, max 2MB</div>
                                    <div id="currentImageContainer" class="mt-2">
                                        <img id="currentImage" src="" class="current-image" style="display: none;">
                                        <div id="noImageMessage" class="text-muted" style="display: none;">No image uploaded</div>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                                    <label class="form-check-label">Active Product</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="edit_is_featured">
                                    <label class="form-check-label">Featured Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-owner">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openEditModal(product) {
        // Populate the edit form with product data
        document.getElementById('edit_product_id').value = product.id;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_description').value = product.description || '';
        document.getElementById('edit_price').value = product.price;
        document.getElementById('edit_category').value = product.category;
        document.getElementById('edit_is_active').checked = product.is_active == 1;
        document.getElementById('edit_is_featured').checked = product.is_featured == 1;
        
        // Handle current image display
        const currentImage = document.getElementById('currentImage');
        const noImageMessage = document.getElementById('noImageMessage');
        
        if (product.image_path) {
            currentImage.src = '../' + product.image_path;
            currentImage.style.display = 'block';
            noImageMessage.style.display = 'none';
        } else {
            currentImage.style.display = 'none';
            noImageMessage.style.display = 'block';
        }
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        editModal.show();
    }
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Reset add product form when modal is closed
    document.getElementById('addProductModal').addEventListener('hidden.bs.modal', function () {
        this.querySelector('form').reset();
    });
    
    // Reset edit product form when modal is closed
    document.getElementById('editProductModal').addEventListener('hidden.bs.modal', function () {
        const currentImage = document.getElementById('currentImage');
        const noImageMessage = document.getElementById('noImageMessage');
        currentImage.style.display = 'none';
        noImageMessage.style.display = 'none';
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>