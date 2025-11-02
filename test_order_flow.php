
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Flow Test - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-card { margin: 10px 0; padding: 15px; border-left: 4px solid #0d6efd; }
        .test-success { border-left-color: #198754; background: #d1e7dd; }
        .test-error { border-left-color: #dc3545; background: #f8d7da; }
        .test-warning { border-left-color: #ffc107; background: #fff3cd; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Order Flow Test</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Session Status</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        echo "<div class='test-card " . (isset($_SESSION['cart']) ? 'test-success' : 'test-warning') . "'>";
                        echo "<strong>Cart Session:</strong> " . (isset($_SESSION['cart']) ? 'Exists' : 'Not set');
                        echo "</div>";
                        
                        if (isset($_SESSION['cart'])) {
                            echo "<div class='test-card test-success'>";
                            echo "<strong>Cart Items:</strong> " . count($_SESSION['cart']);
                            echo "<br><small>Items: " . json_encode($_SESSION['cart']) . "</small>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="?page=home" class="btn btn-primary">Go to Home</a>
                            <a href="?page=customize-cake&cake_id=1" class="btn btn-outline-primary">Test Customize Cake</a>
                            <a href="?page=review" class="btn btn-outline-info">Test Cart Review</a>
                            <button onclick="clearCart()" class="btn btn-outline-warning">Clear Cart</button>
                            <button onclick="testAddToCart()" class="btn btn-outline-success">Test Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>File Check</h5>
            </div>
            <div class="card-body">
                <?php
                $files = [
                    'config.php',
                    'cart.php', 
                    'orders.php',
                    'components/header.php',
                    'components/footer.php',
                    'pages/customize-cake.php',
                    'pages/review.php',
                    'pages/customer-info.php'
                ];
                
                foreach ($files as $file) {
                    $exists = file_exists($file);
                    echo "<div class='test-card " . ($exists ? 'test-success' : 'test-error') . "'>";
                    echo "<strong>$file:</strong> " . ($exists ? '✅ Found' : '❌ Missing');
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Database Test</h5>
            </div>
            <div class="card-body">
                <?php
                include 'config.php';
                if ($conn) {
                    echo "<div class='test-card test-success'>";
                    echo "<strong>Database:</strong> ✅ Connected";
                    echo "</div>";
                    
                    // Test cakes table
                    $result = $conn->query("SELECT COUNT(*) as count FROM cakes");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo "<div class='test-card test-success'>";
                        echo "<strong>Cakes Table:</strong> ✅ " . $row['count'] . " cakes found";
                        echo "</div>";
                    } else {
                        echo "<div class='test-card test-error'>";
                        echo "<strong>Cakes Table:</strong> ❌ Error accessing table";
                        echo "</div>";
                    }
                    
                    $conn->close();
                } else {
                    echo "<div class='test-card test-error'>";
                    echo "<strong>Database:</strong> ❌ Connection failed";
                    echo "</div>";
                }
                ?>
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
</body>
</html>