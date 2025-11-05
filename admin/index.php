<?php
// Add this to handle the product details AJAX request
if (isset($_GET['page']) && $_GET['page'] === 'admin-get-product' && isset($_GET['product_id'])) {
    // Database connection
    $db_config = [
        'servername' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'marsilase_pastry'
    ];
    
    $conn = new mysqli(
        $db_config['servername'],
        $db_config['username'], 
        $db_config['password'],
        $db_config['dbname']
    );
    
    if ($conn->connect_error) {
        die("Database connection failed");
    }
    
    $product_id = intval($_GET['product_id']);
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM cakes WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($product): ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_product">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" 
                               value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price (ETB)</label>
                        <input type="number" class="form-control" id="edit_price" name="price" 
                               value="<?= $product['price'] ?>" step="0.01" min="0" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="edit_category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="edit_category" name="category" 
                               value="<?= htmlspecialchars($product['category'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        <?php if ($product['image_path']): ?>
                            <small class="text-muted">Current: <?= basename($product['image_path']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="edit_description" class="form-label">Description</label>
                <textarea class="form-control" id="edit_description" name="description" rows="3" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" 
                       <?= $product['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="edit_is_active">Active (available for purchase)</label>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">Product not found.</div>
    <?php endif;
    
    $conn->close();
    exit;
}
?>