<?php
session_start();
require_once '../common/connection.php';
requireOwner();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    
    if (empty($name) || $price <= 0) {
        $error = 'Product name and price are required';
    } else {
        // SIMPLE IMAGE UPLOAD - NO COMPLEX CHECKS
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../uploads/products/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $filename = uniqid() . '.jpg';
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = '../uploads/products/' . $filename;
                $message = "Product added successfully with image!";
            } else {
                $message = "Product added but image upload failed";
            }
        } else {
            $message = "Product added without image";
        }
        
        // Insert product
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_path, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssds", $name, $description, $price, $image_path);
        
        if ($stmt->execute()) {
            $message = "Product added successfully!" . ($image_path ? " With image." : " No image.");
        } else {
            $error = 'Failed to add product: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Get products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Emergency Products Page</h2>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_product">
            <div class="mb-3">
                <label>Product Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Price</label>
                <input type="number" name="price" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
                <label>Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
        
        <hr>
        
        <h3>Current Products</h3>
        <?php while ($product = $products->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5><?= $product['name'] ?></h5>
                <p><?= $product['description'] ?></p>
                <p>Price: ETB <?= $product['price'] ?></p>
                <?php if ($product['image_path']): ?>
                    <img src="../<?= $product['image_path'] ?>" width="100" onerror="this.style.display='none'">
                <?php else: ?>
                    <em>No image</em>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>