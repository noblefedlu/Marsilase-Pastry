<?php
session_start();
require_once '../common/connection.php';
requireOwner();

echo "<h2>Image Upload Debug</h2>";

// Check uploads directory
$upload_dir = '../uploads/products/';
echo "<h3>1. Upload Directory Check:</h3>";
if (!is_dir($upload_dir)) {
    echo "❌ Upload directory doesn't exist<br>";
    echo "Creating directory... ";
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Created successfully<br>";
    } else {
        echo "❌ Failed to create directory<br>";
    }
} else {
    echo "✅ Upload directory exists<br>";
}

// Check permissions
echo "Directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";
echo "Is writable: " . (is_writable($upload_dir) ? '✅ Yes' : '❌ No') . "<br>";

// Test file upload
echo "<h3>2. Test File Upload:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    $test_file = $_FILES['test_image'];
    echo "File name: " . $test_file['name'] . "<br>";
    echo "File size: " . $test_file['size'] . " bytes<br>";
    echo "File type: " . $test_file['type'] . "<br>";
    echo "Temp path: " . $test_file['tmp_name'] . "<br>";
    echo "Error code: " . $test_file['error'] . "<br>";
    
    if ($test_file['error'] === 0) {
        $filename = uniqid() . '_test.' . pathinfo($test_file['name'], PATHINFO_EXTENSION);
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($test_file['tmp_name'], $target_path)) {
            echo "✅ File uploaded successfully to: $target_path<br>";
            echo "File exists: " . (file_exists($target_path) ? '✅ Yes' : '❌ No') . "<br>";
            echo "File size on disk: " . filesize($target_path) . " bytes<br>";
            
            // Test if file is accessible via web
            $web_path = '../uploads/products' . $filename;
            echo "Web path: $web_path<br>";
        } else {
            echo "❌ File move failed<br>";
        }
    }
}

// Check current products and their images
echo "<h3>3. Current Products Image Status:</h3>";
$result = $conn->query("SELECT id, name, image_path FROM products");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists</th><th>Web Accessible</th></tr>";
    while ($product = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>{$product['image_path']}</td>";
        
        $file_exists = '❌ No';
        $web_accessible = '❌ No';
        
        if (!empty($product['image_path'])) {
            $full_path = '../' . $product['image_path'];
            $file_exists = file_exists($full_path) ? '✅ Yes' : '❌ No';
            
            // Test web accessibility
            if (file_exists($full_path)) {
                $web_path = $product['image_path'];
                $web_accessible = '✅ Yes';
            }
        }
        
        echo "<td>$file_exists</td>";
        echo "<td>$web_accessible</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No products found<br>";
}
?>

<h3>4. Test Image Upload Form:</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_image" accept="image/*" required>
    <button type="submit" class="btn btn-primary">Test Upload</button>
</form>

<style>
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background: #f5f5f5; }
</style>