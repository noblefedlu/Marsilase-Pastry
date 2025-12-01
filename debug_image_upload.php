<?php
// debug_image_upload.php - Test image upload specifically
session_start();
require_once 'config.php';

echo "<h2>Image Upload Debug</h2>";

// Test 1: Check uploads directory
$upload_dir = 'uploads/products/';
echo "<h3>1. Upload Directory Check:</h3>";
echo "Path: " . realpath($upload_dir) . "<br>";
echo "Exists: " . (is_dir($upload_dir) ? "✅ Yes" : "❌ No") . "<br>";
echo "Writable: " . (is_writable($upload_dir) ? "✅ Yes" : "❌ No") . "<br>";

// Test 2: Check current products
echo "<h3>2. Current Products in Database:</h3>";
$products = $conn->query("SELECT id, name, image_path FROM products ORDER BY id DESC");
if ($products && $products->num_rows > 0) {
    while ($product = $products->fetch_assoc()) {
        $image_exists = !empty($product['image_path']) && file_exists($product['image_path']);
        echo "ID: {$product['id']} | Name: {$product['name']} | ";
        echo "Image Path: '{$product['image_path']}' | ";
        echo "Exists: " . ($image_exists ? "✅" : "❌") . "<br>";
        
        if ($image_exists) {
            echo "<img src='{$product['image_path']}' style='max-width: 100px; margin: 5px;'><br>";
        }
    }
} else {
    echo "No products found.<br>";
}

// Test 3: Test file upload permissions
echo "<h3>3. File Upload Test:</h3>";
if ($_FILES) {
    echo "Files received: " . print_r($_FILES, true) . "<br>";
}

$conn->close();
?>