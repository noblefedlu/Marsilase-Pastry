<?php
session_start();
require_once '../common/connection.php';
requireOwner();

echo "<h2>Fix Product Images</h2>";

// Get all products
$products = $conn->query("SELECT id, name, image_path FROM products");
$fixed_count = 0;

echo "<h3>Checking product images:</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Current Path</th><th>Status</th></tr>";

while ($product = $products->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['name']}</td>";
    echo "<td>{$product['image_path']}</td>";
    
    if (empty($product['image_path'])) {
        echo "<td>❌ No image path</td>";
    } else {
        $full_path = '../' . $product['image_path'];
        if (file_exists($full_path)) {
            echo "<td>✅ Image exists</td>";
        } else {
            echo "<td>❌ File missing - " . basename($full_path) . "</td>";
            $fixed_count++;
        }
    }
    echo "</tr>";
}

echo "</table>";

echo "<h3>Summary:</h3>";
echo "Total products: " . $products->num_rows . "<br>";
echo "Missing images: $fixed_count<br>";

if ($fixed_count > 0) {
    echo "<p><strong>Note:</strong> Some product images are missing from the server. You may need to re-upload them.</p>";
}
?>