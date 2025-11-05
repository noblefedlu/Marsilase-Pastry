[file name]: test_order_submission.php
[file content begin]
<?php
session_start();
require_once 'config.php';

// Clear any existing cart
$_SESSION['cart'] = [];

// Add a test item to cart
$_SESSION['cart']['test_item_1'] = [
    'cart_item_id' => 'test_item_1',
    'product_type' => 'cake',
    'product_id' => 1,
    'product_name' => 'Chocolate Fantasy Cake',
    'flavor' => 'Chocolate',
    'size' => 'Medium',
    'quantity' => 1,
    'unit_price' => 1200.00,
    'total_price' => 1200.00,
    'special_notes' => 'Test order for debugging',
    'added_at' => date('Y-m-d H:i:s')
];

echo "<h3>Test Cart Created Successfully!</h3>";
echo "<p>Cart items: " . count($_SESSION['cart']) . "</p>";
echo "<pre>Cart contents: " . print_r($_SESSION['cart'], true) . "</pre>";

// Test database connection
echo "<h3>Testing Database Connection:</h3>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Database connection successful!</p>";
    
    // Test if orders table exists
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>Orders table exists</p>";
    } else {
        echo "<p style='color: red;'>Orders table missing!</p>";
    }
    
    // Test if order_items table exists
    $result = $conn->query("SHOW TABLES LIKE 'order_items'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>Order_items table exists</p>";
    } else {
        echo "<p style='color: red;'>Order_items table missing!</p>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<p><a href='?page=customer-info' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Checkout Page</a></p>";
echo "<p>After clicking the link above, fill out the customer info form and submit the order.</p>";
?>
[file content end]