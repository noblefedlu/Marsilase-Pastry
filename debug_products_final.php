<?php
require_once 'config.php';

echo "<h2>Final Products Integration Check</h2>";

// Check both tables
$cakes = $conn->query("SELECT COUNT(*) as count FROM cakes WHERE is_active = TRUE")->fetch_assoc()['count'];
$products = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = TRUE")->fetch_assoc()['count'];

echo "<h3>Database Status:</h3>";
echo "<p>Cakes (active): $cakes</p>";
echo "<p>Products (active): $products</p>";
echo "<p><strong>Total Items Available: " . ($cakes + $products) . "</strong></p>";

// Show all active items
echo "<h3>All Active Items:</h3>";
$all_items = $conn->query("
    (SELECT id, name, 'cake' as type, price, discount_price, category, image_url as image FROM cakes WHERE is_active = TRUE)
    UNION ALL
    (SELECT id, name, 'product' as type, price, discount_price, category, image_path as image FROM products WHERE is_active = TRUE)
    ORDER BY type, name
");

if ($all_items && $all_items->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Price</th><th>Discount</th><th>Category</th><th>Image</th></tr>";
    while ($item = $all_items->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$item['id']}</td>";
        echo "<td>{$item['name']}</td>";
        echo "<td>{$item['type']}</td>";
        echo "<td>ETB " . number_format($item['price'], 2) . "</td>";
        echo "<td>" . ($item['discount_price'] > 0 ? "ETB " . number_format($item['discount_price'], 2) : '-') . "</td>";
        echo "<td>{$item['category']}</td>";
        echo "<td>" . ($item['image'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No active items found!</p>";
}

$conn->close();
?>