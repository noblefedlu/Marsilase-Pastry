[file name]: debug_test.php
[file content begin]
<?php
session_start();
?>
<div class="section">
    <div class="container-narrow">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">System Diagnostics</h2>
            </div>
            <div class="card-body">
                <h4 class="text-primary mb-4">Marsilase Pastry System Status</h4>

                <!-- Database Test -->
                <div class="mb-4">
                    <h5>Database Connection</h5>
                    <?php
                    include 'config.php';
                    if ($conn) {
                        echo '<div class="alert alert-success">✅ Database connected successfully</div>';
                        
                        // Test orders table
                        $result = $conn->query("SHOW TABLES LIKE 'orders'");
                        if ($result->num_rows > 0) {
                            echo '<div class="alert alert-success">✅ Orders table exists</div>';
                        } else {
                            echo '<div class="alert alert-danger">❌ Orders table missing</div>';
                        }
                        
                        // Test products tables
                        $tables = ['cakes', 'ice_creams', 'soft_drinks', 'hot_drinks', 'cake_sizes'];
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
                        echo '<div class="alert alert-danger">❌ Database connection failed</div>';
                    }
                    ?>
                </div>

                <!-- Session Test -->
                <div class="mb-4">
                    <h5>Session Status</h5>
                    <?php
                    $_SESSION['debug_test'] = 'working';
                    echo '<div class="alert alert-' . ($_SESSION['debug_test'] === 'working' ? 'success' : 'danger') . '">';
                    echo "Session test: " . ($_SESSION['debug_test'] === 'working' ? '✅ Working' : '❌ Failed');
                    echo '</div>';
                    
                    echo '<div class="alert alert-' . (isset($_SESSION['cart']) ? 'info' : 'secondary') . '">';
                    echo "Cart session: " . (isset($_SESSION['cart']) ? '✅ Exists (' . count($_SESSION['cart']) . ' items)' : '❌ Not set');
                    echo '</div>';
                    ?>
                </div>

                <!-- File System Test -->
                <div class="mb-4">
                    <h5>File System Check</h5>
                    <?php
                    $files = [
                        'config.php',
                        'cart.php', 
                        'orders.php',
                        'components/header.php',
                        'components/footer.php',
                        'pages/customize-cake.php',
                        'pages/review.php',
                        'pages/customer-info.php',
                        'pages/thank-you.php',
                        'pages/about.php',
                        'pages/contact.php',
                        'pages/testimonials.php'
                    ];
                    
                    foreach ($files as $file) {
                        $exists = file_exists($file);
                        echo "<div class='alert alert-" . ($exists ? 'success' : 'danger') . "'>";
                        echo "$file: " . ($exists ? '✅ Found' : '❌ Missing');
                        echo "</div>";
                    }
                    ?>
                </div>

                <!-- Quick Actions -->
                <div class="mb-4">
                    <h5>Quick Actions</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="?page=home" class="btn btn-primary btn-sm">Go to Home</a>
                        <a href="?page=review" class="btn btn-outline-primary btn-sm">Test Cart</a>
                        <button onclick="clearCart()" class="btn btn-outline-warning btn-sm">Clear Cart</button>
                        <button onclick="testAddToCart()" class="btn btn-outline-success btn-sm">Test Add to Cart</button>
                    </div>
                </div>

                <!-- Direct Order Test -->
                <div class="mb-4">
                    <h5>Order System Test</h5>
                    <form action="orders.php" method="POST" class="row g-2">
                        <input type="hidden" name="action" value="submit_order">
                        <div class="col-md-3">
                            <input type="text" name="customer_name" class="form-control form-control-sm" placeholder="Name" value="Test Customer" required>
                        </div>
                        <div class="col-md-3">
                            <input type="tel" name="customer_phone" class="form-control form-control-sm" placeholder="Phone" value="1234567890" required>
                        </div>
                        <div class="col-md-3">
                            <input type="email" name="customer_email" class="form-control form-control-sm" placeholder="Email" value="test@example.com" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Test Order Submit</button>
                        </div>
                        <input type="hidden" name="delivery_address" value="Test Address">
                        <input type="hidden" name="delivery_date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </form>
                </div>

                <!-- System Info -->
                <div class="mb-4">
                    <h5>System Information</h5>
                    <div class="card bg-light">
                        <div class="card-body">
                            <pre class="mb-0 small"><?php
                            echo "PHP Version: " . PHP_VERSION . "\n";
                            echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
                            echo "Session ID: " . session_id() . "\n";
                            echo "Cart Items: " . (isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0);
                            ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCart() {
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=clear_cart'
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}

function testAddToCart() {
    const testData = {
        action: 'add_to_cart',
        product_type: 'cake',
        product_id: '1',
        product_name: 'Test Cake',
        flavor: 'Vanilla',
        size: 'Small',
        toppings: '[]',
        quantity: '1',
        special_notes: 'Test order',
        unit_price: '1200',
        total_price: '1200'
    };
    
    const formData = new FormData();
    for (const key in testData) {
        formData.append(key, testData[key]);
    }
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.success ? '✅ Test item added to cart!' : '❌ Error: ' + data.message);
        location.reload();
    })
    .catch(error => {
        alert('❌ Network error: ' + error);
    });
}
</script>

<style>
.alert {
    margin-bottom: 0.5rem;
    padding: 0.75rem 1rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

pre {
    background: transparent;
    border: none;
    font-family: 'Courier New', monospace;
}
</style>