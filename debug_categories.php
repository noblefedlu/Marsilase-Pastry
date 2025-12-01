[file name]: debug_categories.php
[file content begin]
<?php
require_once 'config.php';

echo "<h2>Categories Debug Information</h2>";

// Check database connection
if ($conn) {
    echo "✅ Database connected successfully<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Check if categories table exists
$result = $conn->query("SHOW TABLES LIKE 'categories'");
if ($result->num_rows > 0) {
    echo "✅ Categories table exists<br>";
} else {
    echo "❌ Categories table does not exist!<br>";
}

// Check if products have category_id
echo "<h3>Checking Products Table Structure:</h3>";
$result = $conn->query("DESCRIBE products");
if ($result) {
    $has_category_id = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'category_id') {
            $has_category_id = true;
        }
    }
    echo "Products table has category_id: " . ($has_category_id ? "✅ Yes" : "❌ No") . "<br>";
}

// Check if cakes have category_id
$result = $conn->query("DESCRIBE cakes");
if ($result) {
    $has_category_id = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'category_id') {
            $has_category_id = true;
        }
    }
    echo "Cakes table has category_id: " . ($has_category_id ? "✅ Yes" : "❌ No") . "<br>";
}

// Show all categories
echo "<h3>All Categories in Database:</h3>";
$categories = $conn->query("SELECT * FROM categories ORDER BY id");
if ($categories && $categories->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Active</th></tr>";
    while ($cat = $categories->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>{$cat['name']}</td>";
        echo "<td>{$cat['description']}</td>";
        echo "<td>" . ($cat['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No categories found in database!<br>";
}

// Show products with their categories
echo "<h3>Products with Categories:</h3>";
$products_with_cats = $conn->query("
    SELECT p.id, p.name, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = TRUE
    LIMIT 10
");
if ($products_with_cats && $products_with_cats->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Product Name</th><th>Category</th></tr>";
    while ($product = $products_with_cats->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>" . ($product['category_name'] ? $product['category_name'] : '❌ NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No products with categories found!<br>";
}

// Show cakes with their categories
echo "<h3>Cakes with Categories:</h3>";
$cakes_with_cats = $conn->query("
    SELECT c.id, c.name, cat.name as category_name 
    FROM cakes c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    WHERE c.is_active = TRUE
    LIMIT 10
");
if ($cakes_with_cats && $cakes_with_cats->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Cake Name</th><th>Category</th></tr>";
    while ($cake = $cakes_with_cats->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$cake['id']}</td>";
        echo "<td>{$cake['name']}</td>";
        echo "<td>" . ($cake['category_name'] ? $cake['category_name'] : '❌ NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No cakes with categories found!<br>";
}

$conn->close();
?>
[file content end]