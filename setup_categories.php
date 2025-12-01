<?php
// setup_categories.php - Complete setup file
require_once 'config.php';

echo "<h2>Setting up Categories System</h2>";

// Create categories table if not exists
$result = $conn->query("
    CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        image_url VARCHAR(500),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

if ($result) {
    echo "<p style='color: green;'>✅ Categories table created/exists</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to create categories table: " . $conn->error . "</p>";
}

// Insert categories with Mini cakes
$categories = [
    ['Cookies', 'Delicious homemade cookies in various flavors and styles'],
    ['Torta Cake', 'Premium layered cakes with exquisite fillings and decorations'],
    ['Arabian Sweets', 'Traditional Middle Eastern sweets and desserts'],
    ['Mini cakes', 'Adorable mini cakes perfect for individual servings or small gatherings']
];

$categories_inserted = 0;
foreach ($categories as $category) {
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $category[0], $category[1]);
    if ($stmt->execute()) {
        $categories_inserted++;
    }
    $stmt->close();
}

echo "<p style='color: green;'>✅ $categories_inserted categories inserted</p>";

// Add category_id columns to products and cakes tables if not exists
$result1 = $conn->query("ALTER TABLE cakes ADD COLUMN IF NOT EXISTS category_id INT DEFAULT 1");
$result2 = $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT DEFAULT 1");

if ($result1 && $result2) {
    echo "<p style='color: green;'>✅ Category columns added to products and cakes tables</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to add category columns</p>";
}

// Show current categories
echo "<h3>Current Categories:</h3>";
$categories_list = $conn->query("SELECT * FROM categories ORDER BY id");
if ($categories_list && $categories_list->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Active</th></tr>";
    while ($cat = $categories_list->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>{$cat['name']}</td>";
        echo "<td>{$cat['description']}</td>";
        echo "<td>" . ($cat['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Setup Completed!</h3>";
echo "<p><a href='?page=home'>Go to Homepage to see categories</a></p>";

$conn->close();
?>