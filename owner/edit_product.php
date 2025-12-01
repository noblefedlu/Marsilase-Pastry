<?php
session_start();
require_once '../common/connection.php';
requireOwner();

$product_id = $_GET['id'] ?? 0;

global $conn;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo '<div class="alert alert-danger">Product not found</div>';
    exit;
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Product Name *</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($product['category']) ?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Price (ETB) *</label>
            <input type="number" class="form-control" name="price" step="0.01" min="0" 
                   value="<?= number_format($product['price'], 2) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Discount Price (ETB)</label>
            <input type="number" class="form-control" name="discount_price" step="0.01" min="0" 
                   value="<?= number_format($product['discount_price'], 2) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Stock Quantity</label>
            <input type="number" class="form-control" name="stock_quantity" min="0" 
                   value="<?= $product['stock_quantity'] ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Product Image</label>
            <?php if ($product['image_path']): ?>
            <div class="mb-2">
                <img src="../<?= htmlspecialchars($product['image_path']) ?>" 
                     alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
            </div>
            <?php endif; ?>
            <input type="file" class="form-control" name="image" accept="image/*">
            <small class="text-muted">Leave empty to keep current image</small>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" 
                   <?= $product['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label">Active Product</label>
        </div>
    </div>
</div>