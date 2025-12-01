<?php
session_start();
include './common/connection.php';
requireRole('admin');

echo "<h2>Orders in Database</h2>";

$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Created</th></tr>";
    while ($order = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$order['id']}</td>";
        echo "<td>{$order['order_number']}</td>";
        echo "<td>{$order['customer_name']}</td>";
        echo "<td>{$order['status']}</td>";
        echo "<td>ETB " . number_format($order['total_amount'], 2) . "</td>";
        echo "<td>{$order['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No orders found in database. This is why you're getting errors.</p>";
    echo "<p>You need to create some test orders first.</p>";
}

$conn->close();
?>