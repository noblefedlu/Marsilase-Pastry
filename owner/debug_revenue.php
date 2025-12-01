<?php
session_start();
require_once '../common/connection.php';
requireOwner();

echo "<h2>Revenue Debug Information</h2>";

// Check orders table structure
echo "<h3>1. Orders Table Structure:</h3>";
$result = $conn->query("DESCRIBE orders");
if ($result) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
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
} else {
    echo "Error describing orders table: " . $conn->error;
}

// Check actual data in orders table
echo "<h3>2. Orders Data (Last 10):</h3>";
$result = $conn->query("SELECT id, order_number, customer_name, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Order #</th><th>Customer</th><th>Total Amount</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['order_number']}</td>";
        echo "<td>{$row['customer_name']}</td>";
        echo "<td>{$row['total_amount']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No orders found in database<br>";
}

// Test the revenue query directly
echo "<h3>3. Revenue Query Test:</h3>";

// Test 1: Total revenue from delivered orders
$query1 = "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status = 'delivered'";
$result1 = $conn->query($query1);
if ($result1) {
    $row1 = $result1->fetch_assoc();
    echo "Total Revenue (Delivered): ETB " . number_format($row1['revenue'], 2) . "<br>";
    echo "Query: $query1<br>";
    echo "Raw result: " . print_r($row1, true) . "<br>";
} else {
    echo "Query failed: " . $conn->error . "<br>";
}

// Test 2: Today's revenue
$query2 = "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()";
$result2 = $conn->query($query2);
if ($result2) {
    $row2 = $result2->fetch_assoc();
    echo "Today's Revenue: ETB " . number_format($row2['revenue'], 2) . "<br>";
    echo "Query: $query2<br>";
    echo "Raw result: " . print_r($row2, true) . "<br>";
} else {
    echo "Query failed: " . $conn->error . "<br>";
}

// Test 3: Check if there are any delivered orders at all
$query3 = "SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'";
$result3 = $conn->query($query3);
if ($result3) {
    $row3 = $result3->fetch_assoc();
    echo "Total Delivered Orders: " . $row3['count'] . "<br>";
}

// Test 4: Check orders by status
echo "<h3>4. Orders by Status:</h3>";
$query4 = "SELECT status, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue FROM orders GROUP BY status";
$result4 = $conn->query($query4);
if ($result4 && $result4->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Status</th><th>Count</th><th>Revenue</th></tr>";
    while ($row = $result4->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>ETB " . number_format($row['revenue'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>