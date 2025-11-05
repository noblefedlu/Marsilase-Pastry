<?php
// admin/products.php
require_once 'config.php';
requireAdminAuth();

// Handle product actions
if (isset($_POST['add_product'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $category = sanitizeInput($_POST['category']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $stock_quantity = intval($_POST['stock_quantity']);
    $serves = intval($_POST['serves']);
    $preparation_time = sanitizeInput($_POST['preparation_time']);
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image'], '../uploads/products/');
        if ($upload_result['success']) {
            $image_url = $upload_result['path'];
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO cakes (name, description, image_url, price, category, is_featured, stock_quantity, serves, preparation_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdssiis", $name, $description, $image_url, $price, $category, $is_featured, $stock_quantity, $serves, $preparation_time);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product added successfully!";
        logAdminAction('Product Added', "Added new product: $name");
    } else {
        $_SESSION['error_message'] = "Error adding product: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_POST['update_product'])) {
    $id = intval($_POST['product_id']);
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $category = sanitizeInput($_POST['category']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $stock_quantity = intval($_POST['stock_quantity']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $serves = intval($_POST['serves']);
    $preparation_time = sanitizeInput($_POST['preparation_time']);
    
    // Handle image upload
    $image_url = $_POST['current_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image'], '../uploads/products/');
        if ($upload_result['success']) {
            // Delete old image if exists
            if (!empty($image_url) && file_exists('../' . $image_url)) {
                unlink('../' . $image_url);
            }
            $image_url = $upload_result['path'];
        }
    }
    
    $stmt = $conn->prepare("UPDATE cakes SET name = ?, description = ?, image_url = ?, price = ?, category = ?, is_featured = ?, stock_quantity = ?, is_active = ?, serves = ?, preparation_time = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssdssiiisi", $name, $description, $image_url, $price, $category, $is_featured, $stock_quantity, $is_active, $serves, $preparation_time, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product updated successfully!";
        logAdminAction('Product Updated', "Updated product: $name");
    } else {
        $_SESSION['error_message'] = "Error updating product: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // Get image path to delete file
    $result = $conn->query("SELECT image_url FROM cakes WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        if (!empty($row['image_url']) && file_exists('../' . $row['image_url'])) {
            unlink('../' . $row['image_url']);
        }
    }
    
    if ($conn->query("DELETE FROM cakes WHERE id = $id")) {
        $_SESSION['success_message'] = "Product deleted successfully!";
        logAdminAction('Product Deleted', "Deleted product ID: $id");
    } else {
        $_SESSION['error_message'] = "Error deleting product.";
    }
}

// Toggle product status
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    $result = $conn->query("SELECT is_active FROM cakes WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $new_status = $row['is_active'] ? 0 : 1;
        $conn->query("UPDATE cakes SET is_active = $new_status WHERE id = $id");
        $_SESSION['success_message'] = "Product status updated!";
    }
}

// Get all products
$products = $conn->query("SELECT * FROM cakes ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Get product counts
$total_products = $conn->query("SELECT COUNT(*) as count FROM cakes")->fetch_assoc()['count'];
$active_products = $conn->query("SELECT COUNT(*) as count FROM cakes WHERE is_active = TRUE")->fetch_assoc()['count'];
$featured_products = $conn->query("SELECT COUNT(*) as count FROM cakes WHERE is_featured = TRUE")->fetch_assoc()['count'];
$low_stock_products = $conn->query("SELECT COUNT(*) as count FROM cakes WHERE stock_quantity <= 10 AND stock_quantity > 0")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .product-card {
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Product Management</h2>
                <p class="text-muted mb-0">Manage your cake products and inventory</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Product
            </button>
        </div>

        <!-- Product Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-cake text-primary fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $total_products ?></h4>
                            <span class="text-muted">Total Products</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $active_products ?></h4>
                            <span class="text-muted">Active Products</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-star text-warning fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $featured_products ?></h4>
                            <span class="text-muted">Featured</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $low_stock_products ?></h4>
                            <span class="text-muted">Low Stock</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <!-- Products Grid -->
        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-xl-4 col-lg-6 mb-4">
                <div class="card product-card h-100">
                    <img src="<?= !empty($product['image_url']) ? '../' . $product['image_url'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' ?>" 
                         class="card-img-top product-image" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <div>
                                <?php if ($product['is_featured']): ?>
                                    <span class="badge bg-warning">Featured</span>
                                <?php endif; ?>
                                <span class="badge bg-<?= $product['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                        
                        <p class="card-text text-muted small mb-2">
                            <?= htmlspecialchars(mb_strimwidth($product['description'], 0, 100, '...')) ?>
                        </p>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="text-muted">Category:</small>
                                <div class="fw-semibold"><?= ucfirst($product['category']) ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Serves:</small>
                                <div class="fw-semibold"><?= $product['serves'] ?> people</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Prep Time:</small>
                                <div class="fw-semibold"><?= $product['preparation_time'] ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Stock:</small>
                                <div class="fw-semibold text-<?= $product['stock_quantity'] == 0 ? 'danger' : ($product['stock_quantity'] <= 10 ? 'warning' : 'success') ?>">
                                    <?= $product['stock_quantity'] ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-primary fs-5">ETB <?= number_format($product['price'], 2) ?></strong>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editProductModal"
                                        onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?toggle_status=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-outline-<?= $product['is_active'] ? 'warning' : 'success' ?>"
                                   title="<?= $product['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="bi bi-power"></i>
                                </a>
                                <a href="?delete_id=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($product['name']) ?>? This action cannot be undone.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cake display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">No products yet</h4>
            <p class="text-muted">Get started by adding your first product.</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle me-2"></i>Add First Product
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Product Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="chocolate">Chocolate</option>
                                    <option value="vanilla">Vanilla</option>
                                    <option value="fruit">Fruit</option>
                                    <option value="special">Special Occasion</option>
                                    <option value="caramel">Caramel</option>
                                    <option value="coffee">Coffee</option>
                                    <option value="wedding">Wedding</option>
                                    <option value="birthday">Birthday</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Describe the product..."></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Price (ETB) *</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Serves (People)</label>
                                <input type="number" name="serves" class="form-control" min="1" value="4">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Preparation Time</label>
                                <input type="text" name="preparation_time" class="form-control" value="2-4 hours" 
                                       placeholder="e.g., 2-4 hours, 24 hours">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="featuredCheck">
                                    <label class="form-check-label fw-semibold" for="featuredCheck">Featured Product</label>
                                </div>
                                <small class="text-muted">Featured products will be highlighted on the website</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="editProductId">
                        <input type="hidden" name="current_image" id="editCurrentImage">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Product Name *</label>
                                <input type="text" name="name" id="editName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category *</label>
                                <select name="category" id="editCategory" class="form-select" required>
                                    <option value="chocolate">Chocolate</option>
                                    <option value="vanilla">Vanilla</option>
                                    <option value="fruit">Fruit</option>
                                    <option value="special">Special Occasion</option>
                                    <option value="caramel">Caramel</option>
                                    <option value="coffee">Coffee</option>
                                    <option value="wedding">Wedding</option>
                                    <option value="birthday">Birthday</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Price (ETB) *</label>
                                <input type="number" name="price" id="editPrice" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" id="editStock" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Serves (People)</label>
                                <input type="number" name="serves" id="editServes" class="form-control" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Preparation Time</label>
                                <input type="text" name="preparation_time" id="editPrepTime" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <div class="mt-2" id="currentImagePreview"></div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="editFeatured">
                                    <label class="form-check-label fw-semibold" for="editFeatured">Featured Product</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="editActive">
                                    <label class="form-check-label fw-semibold" for="editActive">Active Product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(product) {
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editName').value = product.name;
            document.getElementById('editDescription').value = product.description;
            document.getElementById('editPrice').value = product.price;
            document.getElementById('editCategory').value = product.category;
            document.getElementById('editStock').value = product.stock_quantity;
            document.getElementById('editServes').value = product.serves;
            document.getElementById('editPrepTime').value = product.preparation_time;
            document.getElementById('editFeatured').checked = product.is_featured == 1;
            document.getElementById('editActive').checked = product.is_active == 1;
            document.getElementById('editCurrentImage').value = product.image_url;
            
            // Show current image preview
            const imagePreview = document.getElementById('currentImagePreview');
            if (product.image_url) {
                imagePreview.innerHTML = `
                    <small class="text-muted">Current Image:</small><br>
                    <img src="../${product.image_url}" class="img-thumbnail mt-1" style="max-height: 100px;">
                `;
            } else {
                imagePreview.innerHTML = '<small class="text-muted">No image uploaded</small>';
            }
        }
    </script>
</body>
</html>