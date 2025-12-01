<?php
require_once 'config.php';

echo "<h2>User Page Products Debug</h2>";

// Check connection
if ($conn) {
    echo "✅ Database connected successfully<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Check products table
$result = $conn->query("SHOW TABLES LIKE 'products'");
if ($result->num_rows > 0) {
    echo "✅ Products table exists<br>";
} else {
    echo "❌ Products table does not exist!<br>";
}

// Show active products (what user page should show)
echo "<h3>Active Products (is_active = 1):</h3>";
$active_products = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC");
if ($active_products && $active_products->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Active</th><th>Image</th></tr>";
    while ($product = $active_products->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>ETB {$product['price']}</td>";
        echo "<td>" . ($product['is_active'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . ($product['image_path'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No active products found!<br>";
}

// Show all products for comparison
echo "<h3>All Products in Database:</h3>";
$all_products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($all_products && $all_products->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Active</th><th>Image</th></tr>";
    while ($product = $all_products->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>ETB {$product['price']}</td>";
        echo "<td>" . ($product['is_active'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . ($product['image_path'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No products found in database at all!<br>";
}

$conn->close();
?>