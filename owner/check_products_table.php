<?php
session_start();
require_once '../common/connection.php';
requireOwner();

echo "<h2>Checking Products in Database</h2>";

// Check cakes table
$result = $conn->query("SELECT * FROM cakes ORDER BY id");
if ($result->num_rows > 0) {
    echo "<h3>Cakes in Database:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Active</th><th>Featured</th><th>Image</th></tr>";
    while ($cake = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$cake['id']}</td>";
        echo "<td>{$cake['name']}</td>";
        echo "<td>ETB " . number_format($cake['price'], 2) . "</td>";
        echo "<td>" . ($cake['is_active'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . ($cake['is_featured'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . ($cake['image_url'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No cakes found in database!</p>";
}

// Check if there's a products table (in case you have both)
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows > 0) {
    echo "<h3>Products Table Exists:</h3>";
    $products_result = $conn->query("SELECT * FROM products");
    if ($products_result->num_rows > 0) {
        echo "<p>Products table has {$products_result->num_rows} records</p>";
    } else {
        echo "<p>Products table is empty</p>";
    }
}

$conn->close();
?>