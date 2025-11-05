<?php
// admin/products.php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle product actions
if (isset($_POST['add_product'])) {
    // Add new product logic
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $stock_quantity = $_POST['stock_quantity'];
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = 'uploads/products/' . $filename;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO cakes (name, description, image_url, price, category, is_featured, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdssi", $name, $description, $image_url, $price, $category, $is_featured, $stock_quantity);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding product.";
    }
    $stmt->close();
}

if (isset($_POST['update_product'])) {
    // Update product logic
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $stock_quantity = $_POST['stock_quantity'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image_url = $_POST['current_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // Delete old image if exists
            if (!empty($image_url) && file_exists('../' . $image_url)) {
                unlink('../' . $image_url);
            }
            $image_url = 'uploads/products/' . $filename;
        }
    }
    
    $stmt = $conn->prepare("UPDATE cakes SET name = ?, description = ?, image_url = ?, price = ?, category = ?, is_featured = ?, stock_quantity = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssdssiii", $name, $description, $image_url, $price, $category, $is_featured, $stock_quantity, $is_active, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating product.";
    }
    $stmt->close();
}

if (isset($_GET['delete_id'])) {
    // Delete product logic
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
    } else {
        $_SESSION['error_message'] = "Error deleting product.";
    }
}

// Get all products
$products = $conn->query("SELECT * FROM cakes ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Product Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Product
            </button>
        </div>

        <!-- Products Grid -->
        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <img src="<?= !empty($product['image_url']) ? '../' . $product['image_url'] : 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         style="height: 200px; object-fit: cover;">
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
                        <p class="card-text text-muted small"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-primary">ETB <?= number_format($product['price'], 2) ?></strong>
                            <span class="text-muted">Stock: <?= $product['stock_quantity'] ?></span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100">
                            <button class="btn btn-outline-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProductModal"
                                    onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <a href="?delete_id=<?= $product['id'] ?>" 
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this product?')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
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
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="chocolate">Chocolate</option>
                                    <option value="vanilla">Vanilla</option>
                                    <option value="fruit">Fruit</option>
                                    <option value="special">Special</option>
                                    <option value="caramel">Caramel</option>
                                    <option value="coffee">Coffee</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price (ETB) *</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" class="form-control" min="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="featuredCheck">
                                    <label class="form-check-label" for="featuredCheck">Featured Product</label>
                                </div>
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
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" id="editName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category" id="editCategory" class="form-select" required>
                                    <option value="chocolate">Chocolate</option>
                                    <option value="vanilla">Vanilla</option>
                                    <option value="fruit">Fruit</option>
                                    <option value="special">Special</option>
                                    <option value="caramel">Caramel</option>
                                    <option value="coffee">Coffee</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price (ETB) *</label>
                                <input type="number" name="price" id="editPrice" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" name="stock_quantity" id="editStock" class="form-control" min="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Product Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <div class="mt-2" id="currentImagePreview"></div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="editFeatured">
                                    <label class="form-check-label" for="editFeatured">Featured Product</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="editActive">
                                    <label class="form-check-label" for="editActive">Active Product</label>
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