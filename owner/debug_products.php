<?php
session_start();
require_once '../common/connection.php';
requireOwner();

echo "<h2>Products Debug Information</h2>";

// Check database connection
if ($conn) {
    echo "✅ Database connected successfully<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Check if products table exists
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows > 0) {
    echo "✅ Products table exists<br>";
} else {
    echo "❌ Products table does not exist!<br>";
    exit;
}

// Show all products in database
echo "<h3>All Products in Database:</h3>";
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($products && $products->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Active</th><th>Image</th><th>Created</th></tr>";
    while ($product = $products->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>ETB {$product['price']}</td>";
        echo "<td>" . ($product['is_active'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . ($product['image_path'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>{$product['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No products found in database<br>";
}

// Check table structure
echo "<h3>Products Table Structure:</h3>";
$structure = $conn->query("DESCRIBE products");
if ($structure) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>