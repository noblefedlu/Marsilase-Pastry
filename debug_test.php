<?php
session_start();
echo "<h2>Marsilase Pastry Debug Test</h2>";

// Test database
include 'config.php';
if ($conn) {
    echo "✅ Database connected<br>";
    
    // Test orders table
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($result->num_rows > 0) {
        echo "✅ Orders table exists<br>";
        
        // Test order insertion
        $test_order = 'TEST-' . time();
        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, delivery_address, customer_address, delivery_date, total_amount) VALUES (?, 'Test', '123', 'test@test.com', 'Test', 'Test', CURDATE(), 100)");
        if ($stmt && $stmt->bind_param("s", $test_order) && $stmt->execute()) {
            echo "✅ Order insertion works<br>";
            $stmt->close();
        } else {
            echo "❌ Order insertion failed<br>";
        }
    } else {
        echo "❌ Orders table missing<br>";
    }
    
    $conn->close();
} else {
    echo "❌ Database connection failed<br>";
}

// Test session
$_SESSION['test'] = 'working';
echo "Session test: " . ($_SESSION['test'] === 'working' ? '✅ Working' : '❌ Failed') . "<br>";

// Test file paths
$files = ['orders.php', 'cart.php', 'config.php'];
foreach ($files as $file) {
    echo "$file: " . (file_exists($file) ? '✅ Exists' : '❌ Missing') . "<br>";
}

// Test direct order submission
echo "<h3>Direct Order Test</h3>";
echo '<form action="orders.php" method="POST">
    <input type="hidden" name="action" value="submit_order">
    <input type="hidden" name="customer_name" value="Test Customer">
    <input type="hidden" name="customer_phone" value="1234567890">
    <input type="hidden" name="customer_email" value="test@example.com">
    <input type="hidden" name="delivery_address" value="Test Address">
    <input type="hidden" name="delivery_date" value="' . date('Y-m-d', strtotime('+1 day')) . '">
    <button type="submit" class="btn btn-primary">Test Direct Order Submit</button>
</form>';

echo "<h3>File Structure Check</h3>";
echo "<pre>";
system('ls -la');
echo "</pre>";
?>