<?php
// admin/manage_products.php
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

// Handle product actions
if (isset($_POST['action']) && $_POST['action'] === 'update_product') {
    $product_id = $_POST['product_id'];
    $field = $_POST['field'];
    $value = $_POST['value'];
    
    // Validate field to prevent SQL injection
    $allowed_fields = ['price', 'stock_quantity', 'is_featured', 'is_active', 'name', 'description', 'category'];
    if (in_array($field, $allowed_fields)) {
        $stmt = $conn->prepare("UPDATE cakes SET $field = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $product_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Get all products with error handling
$products = [];
$products_result = $conn->query("SELECT * FROM cakes ORDER BY created_at DESC");
if ($products_result) {
    $products = $products_result->fetch_all(MYSQLI_ASSOC);
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

        <!-- Products Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Products</h5>
                <button class="btn btn-primary btn-sm" onclick="addNewProduct()">
                    <i class="bi bi-plus me-1"></i>
                    Add Product
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Featured</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-cake display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No products found</p>
                                        <button class="btn btn-primary" onclick="addNewProduct()">
                                            Add Your First Product
                                        </button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['image_path'])): ?>
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
                                        <div class="fw-semibold"><?= htmlspecialchars($product['name']) ?></div>
                                        <small class="text-muted"><?= ucfirst($product['category'] ?? 'Uncategorized') ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars(substr($product['description'] ?? '', 0, 50)) ?>
                                            <?= strlen($product['description'] ?? '') > 50 ? '...' : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_product">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="hidden" name="field" value="price">
                                            <input type="number" name="value" value="<?= $product['price'] ?? 0 ?>" 
                                                   step="0.01" class="form-control form-control-sm" 
                                                   style="width: 100px;"
                                                   onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_product">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="hidden" name="field" value="stock_quantity">
                                            <input type="number" name="value" value="<?= $product['stock_quantity'] ?? 0 ?>" 
                                                   class="form-control form-control-sm" 
                                                   style="width: 80px;"
                                                   onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_product">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="hidden" name="field" value="is_featured">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="value" value="1" 
                                                       <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>
                                                       onchange="this.form.submit()">
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_product">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="hidden" name="field" value="is_active">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="value" value="1" 
                                                       <?= ($product['is_active'] ?? 0) ? 'checked' : '' ?>
                                                       onchange="this.form.submit()">
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="editProduct(<?= $product['id'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="deleteProduct(<?= $product['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="addProductForm">
                    <input type="hidden" name="action" value="add_product">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_price" class="form-label">Price (ETB)</label>
                                <input type="number" class="form-control" id="product_price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="product_category" name="category" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="product_stock" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="product_stock" name="stock_quantity" min="0" value="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_description" class="form-label">Description</label>
                        <textarea class="form-control" id="product_description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="product_image" name="image" accept="image/*">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="product_featured" name="is_featured" value="1">
                                <label class="form-check-label" for="product_featured">Featured Product</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="product_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="product_active">Active Product</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
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
            </div>
        </div>
    </div>
</div>

<script>
function addNewProduct() {
    // Reset form
    document.getElementById('addProductForm').reset();
    // Show modal
    new bootstrap.Modal(document.getElementById('addProductModal')).show();
}

function editProduct(productId) {
    // Load product data via AJAX
    fetch(`?page=admin-get-product&product_id=${productId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editProductContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        })
        .catch(error => {
            document.getElementById('editProductContent').innerHTML = `
                <div class="alert alert-danger">
                    Failed to load product details. Please try again.
                </div>
            `;
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        });
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="product_id" value="${productId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Handle add product form submission
document.getElementById('addProductForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    this.submit();
});
</script>

<?php 
// Close connection
$conn->close();
?>