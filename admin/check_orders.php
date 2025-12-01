<?php
session_start();
require_once './common/connection.php';
requireRole('admin');

echo "<h2>Checking Database</h2>";

// Check if orders table exists
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows > 0) {
    echo "✅ Orders table exists<br>";
    
    // Check if there are any orders
    $orders_result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $orders_count = $orders_result->fetch_assoc()['count'];
    echo "Orders in database: $orders_count<br>";
    
    if ($orders_count > 0) {
        echo "✅ You have orders in the database<br>";
    } else {
        echo "❌ No orders found in database<br>";
    }
} else {
    echo "❌ Orders table does not exist<br>";
}

// Check specific order
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if ($order) {
        echo "✅ Order ID $order_id exists: " . $order['order_number'] . "<br>";
    } else {
        echo "❌ Order ID $order_id does not exist<br>";
    }
}

$conn->close();
?>