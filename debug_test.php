
<?php
session_start();
echo "<h2 class='text-primary'>Marsilase Pastry Debug Test</h2>";

// Test database
include 'config.php';
if ($conn) {
    echo "<div class='alert alert-success'>✅ Database connected</div>";
    
    // Test orders table
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($result->num_rows > 0) {
        echo "<div class='alert alert-success'>✅ Orders table exists</div>";
        
        // Test order insertion
        $test_order = 'TEST-' . time();
        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, delivery_address, customer_address, delivery_date, total_amount) VALUES (?, 'Test Customer', '1234567890', 'test@test.com', 'Test Address', 'Test Address', CURDATE(), 100)");
        
        if ($stmt && $stmt->bind_param("s", $test_order) && $stmt->execute()) {
            echo "<div class='alert alert-success'>✅ Order insertion works</div>";
            $stmt->close();
            
            // Clean up test order
            $conn->query("DELETE FROM orders WHERE order_number = '$test_order'");
        } else {
            echo "<div class='alert alert-danger'>❌ Order insertion failed: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>❌ Orders table missing</div>";
    }
    
    // Test products tables
    $tables = ['cakes', 'ice_creams', 'soft_drinks', 'hot_drinks', 'cake_sizes', 'flavors', 'toppings'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<div class='alert alert-success'>✅ $table table exists</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠️ $table table missing</div>";
        }
    }
    
    $conn->close();
} else {
    echo "<div class='alert alert-danger'>❌ Database connection failed</div>";
}

// Test session
$_SESSION['test'] = 'working';
echo "<div class='alert alert-" . ($_SESSION['test'] === 'working' ? 'success' : 'danger') . "'>";
echo "Session test: " . ($_SESSION['test'] === 'working' ? '✅ Working' : '❌ Failed');
echo "</div>";

// Test file paths
$files = ['orders.php', 'cart.php', 'config.php', 'components/header.php', 'components/footer.php'];
foreach ($files as $file) {
    echo "<div class='alert alert-" . (file_exists($file) ? 'success' : 'danger') . "'>";
    echo "$file: " . (file_exists($file) ? '✅ Exists' : '❌ Missing');
    echo "</div>";
}

// Test cart functionality
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
echo "<div class='alert alert-info'>Cart items: " . count($_SESSION['cart']) . "</div>";

// Test direct order submission
echo "<h3 class='text-primary'>Direct Order Test</h3>";
echo '<form action="orders.php" method="POST" class="mb-3">
    <input type="hidden" name="action" value="submit_order">
    <input type="hidden" name="customer_name" value="Test Customer">
    <input type="hidden" name="customer_phone" value="1234567890">
    <input type="hidden" name="customer_email" value="test@example.com">
    <input type="hidden" name="delivery_address" value="Test Address">
    <input type="hidden" name="delivery_date" value="' . date('Y-m-d', strtotime('+1 day')) . '">
    <button type="submit" class="btn btn-primary">Test Direct Order Submit</button>
</form>';

echo "<h3 class='text-primary'>File Structure Check</h3>";
echo "<div class='bg-dark text-light p-3 rounded'>";
echo "<pre class='mb-0'>";
system('ls -la');
echo "</pre>";
echo "</div>";

// Add some basic styling
echo "<style>
.alert { margin: 5px 0; padding: 10px; border-radius: 5px; }
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
</style>";
?>