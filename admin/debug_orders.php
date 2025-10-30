<?php
session_start();
include '../config.php';

echo "<h2>Order System Debug</h2>";

// Test database connection
echo "<h3>1. Database Connection Test:</h3>";
if ($conn) {
    echo "✅ Connected to database successfully<br>";
    
    // Test basic query
    $result = $conn->query("SELECT 1");
    if ($result) {
        echo "✅ Basic query works<br>";
    } else {
        echo "❌ Basic query failed: " . $conn->error . "<br>";
    }
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
    exit;
}

// Check required tables
echo "<h3>2. Database Tables Check:</h3>";
$tables = ['orders', 'order_items', 'cakes', 'cake_sizes'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' is missing!<br>";
    }
}

// Test orders table structure
echo "<h3>3. Orders Table Structure:</h3>";
$result = $conn->query("DESCRIBE orders");
if ($result) {
    echo "Orders table columns:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']} ({$row['Type']})<br>";
    }
} else {
    echo "❌ Could not describe orders table: " . $conn->error . "<br>";
}

// Test order insertion
echo "<h3>4. Test Order Insertion:</h3>";
try {
    $test_order_num = 'TEST' . time();
    $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, delivery_address, delivery_date, total_amount) VALUES (?, 'Test Customer', '1234567890', 'Test Address', CURDATE(), 100.00)");
    
    if ($stmt && $stmt->bind_param("s", $test_order_num) && $stmt->execute()) {
        $test_order_id = $conn->insert_id;
        echo "✅ Test order inserted successfully (ID: $test_order_id)<br>";
        
        // Test order item insertion
        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_type, product_id, product_name, flavor, size, quantity, unit_price, total_price) VALUES (?, 'cake', 1, 'Test Cake', 'Vanilla', 'Small', 1, 100.00, 100.00)");
        
        if ($stmt2 && $stmt2->bind_param("i", $test_order_id) && $stmt2->execute()) {
            echo "✅ Test order item inserted successfully<br>";
        } else {
            echo "❌ Test order item failed: " . ($stmt2 ? $stmt2->error : $conn->error) . "<br>";
        }
        
        if ($stmt2) $stmt2->close();
        
        // Clean up
        $conn->query("DELETE FROM order_items WHERE order_id = $test_order_id");
        $conn->query("DELETE FROM orders WHERE id = $test_order_id");
        echo "✅ Test data cleaned up<br>";
        
    } else {
        echo "❌ Test order failed: " . ($stmt ? $stmt->error : $conn->error) . "<br>";
    }
    
    if ($stmt) $stmt->close();
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "<br>";
}

// Check session cart
echo "<h3>5. Session Check:</h3>";
if (empty($_SESSION['cart'])) {
    echo "ℹ️ Cart is empty (this is normal if you haven't added items)<br>";
} else {
    echo "Cart contains " . count($_SESSION['cart']) . " items<br>";
}

echo "<h3>6. Next Steps:</h3>";
echo "If tests above show errors, you need to:<br>";
echo "1. Run the SQL schema to create missing tables<br>";
echo "2. Check database permissions<br>";
echo "3. Verify the config.php database credentials<br>";

$conn->close();
?>