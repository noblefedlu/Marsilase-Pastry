<?php
// debug_cart.php - Debug session and cart issues

// Start session with detailed configuration
session_start([
    'cookie_lifetime' => 86400, // 24 hours
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set to true if using HTTPS
    'use_strict_mode' => true
]);

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Function to test session writing
function testSessionWrite() {
    $_SESSION['debug_test_time'] = date('Y-m-d H:i:s');
    $_SESSION['debug_test_array'] = ['test' => 'value', 'number' => 123];
    session_write_close();
    session_start();
    return isset($_SESSION['debug_test_time']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Debug - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .debug-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
        }
        .success { border-left-color: #28a745 !important; }
        .warning { border-left-color: #ffc107 !important; }
        .danger { border-left-color: #dc3545 !important; }
        pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">🛒 Cart Debug Information</h1>

        <!-- Session Information -->
        <div class="debug-section">
            <h3>🔧 Session Configuration</h3>
            <div class="row">
                <div class="col-md-6">
                    <strong>Session ID:</strong> <?= session_id() ?><br>
                    <strong>Session Status:</strong> 
                    <?php 
                    switch(session_status()) {
                        case PHP_SESSION_DISABLED: echo 'DISABLED'; break;
                        case PHP_SESSION_NONE: echo 'NONE (No session active)'; break;
                        case PHP_SESSION_ACTIVE: echo 'ACTIVE'; break;
                        default: echo 'UNKNOWN';
                    }
                    ?><br>
                    <strong>Session Name:</strong> <?= session_name() ?><br>
                </div>
                <div class="col-md-6">
                    <strong>Save Path:</strong> <?= session_save_path() ?><br>
                    <strong>Cookie Lifetime:</strong> <?= ini_get('session.cookie_lifetime') ?><br>
                    <strong>Session Test:</strong> 
                    <?php 
                    if (testSessionWrite()) {
                        echo '<span class="text-success"> WRITABLE</span>';
                    } else {
                        echo '<span class="text-danger">❌ NOT WRITABLE</span>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Cart Contents -->
        <div class="debug-section <?= empty($_SESSION['cart']) ? 'warning' : 'success' ?>">
            <h3>🛍️ Cart Contents</h3>
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <div class="alert alert-success">
                    <strong> Cart has <?= count($_SESSION['cart']) ?> item(s)</strong>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_items = 0;
                        $total_price = 0;
                        foreach ($_SESSION['cart'] as $key => $item): 
                            $total_items += $item['quantity'] ?? 1;
                            $total_price += $item['total_price'] ?? 0;
                        ?>
                        <tr>
                            <td><code><?= substr($key, 0, 8) ?>...</code></td>
                            <td><?= htmlspecialchars($item['product_name'] ?? 'Unknown') ?></td>
                            <td><span class="badge bg-secondary"><?= $item['product_type'] ?? 'N/A' ?></span></td>
                            <td><span class="badge bg-info"><?= $item['size'] ?? 'N/A' ?></span></td>
                            <td><span class="badge bg-primary"><?= $item['quantity'] ?? 1 ?></span></td>
                            <td><strong>ETB <?= number_format($item['total_price'] ?? 0, 2) ?></strong></td>
                            <td><small><?= $item['added_at'] ?? 'Unknown' ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <td colspan="4"><strong>Total</strong></td>
                            <td><strong><?= $total_items ?> items</strong></td>
                            <td><strong>ETB <?= number_format($total_price, 2) ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Cart is empty or not set</strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- Full Session Data -->
        <div class="debug-section">
            <h3>📊 Full Session Data</h3>
            <pre><?php 
            ob_start();
            print_r($_SESSION);
            $session_data = ob_get_clean();
            echo htmlspecialchars($session_data);
            ?></pre>
        </div>

        <!-- POST Data (if any) -->
        <div class="debug-section">
            <h3>📨 POST Data</h3>
            <?php if (!empty($_POST)): ?>
                <pre><?php print_r($_POST); ?></pre>
            <?php else: ?>
                <p class="text-muted">No POST data received</p>
            <?php endif; ?>
        </div>

        <!-- GET Data -->
        <div class="debug-section">
            <h3>🔗 GET Parameters</h3>
            <?php if (!empty($_GET)): ?>
                <pre><?php print_r($_GET); ?></pre>
            <?php else: ?>
                <p class="text-muted">No GET parameters</p>
            <?php endif; ?>
        </div>

        <!-- Test Actions -->
        <div class="debug-section">
            <h3>🧪 Test Actions</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="debug_cart.php?add_test=1" class="btn btn-success w-100">
                        ➕ Add Test Item to Cart
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="debug_cart.php?clear_cart=1" class="btn btn-warning w-100">
                        🗑️ Clear Cart
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="debug_cart.php" class="btn btn-info w-100">
                        🔄 Refresh Page
                    </a>
                </div>
            </div>
        </div>

        <!-- Server Information -->
        <div class="debug-section">
            <h3>🖥️ Server Information</h3>
            <div class="row">
                <div class="col-md-6">
                    <strong>PHP Version:</strong> <?= PHP_VERSION ?><br>
                    <strong>Server Software:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?><br>
                    <strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?><br>
                </div>
                <div class="col-md-6">
                    <strong>Session Save Path:</strong> <?= session_save_path() ?><br>
                    <strong>Session GC Probability:</strong> <?= ini_get('session.gc_probability') ?>/<?= ini_get('session.gc_divisor') ?><br>
                    <strong>Session GC Max Lifetime:</strong> <?= ini_get('session.gc_maxlifetime') ?> seconds<br>
                </div>
            </div>
        </div>

        <!-- Test Results -->
        <div class="debug-section">
            <h3>📋 Test Results</h3>
            <?php
            // Handle test actions
            if (isset($_GET['add_test'])) {
                $_SESSION['cart']['test_item_' . time()] = [
                    'cart_item_id' => 'test_item_' . time(),
                    'product_type' => 'cake',
                    'product_id' => 999,
                    'product_name' => 'Debug Test Cake',
                    'flavor' => 'Vanilla',
                    'size' => 'Large',
                    'quantity' => 2,
                    'special_notes' => 'This is a test item for debugging',
                    'unit_price' => 150.00,
                    'total_price' => 300.00,
                    'added_at' => date('Y-m-d H:i:s')
                ];
                echo '<div class="alert alert-success"> Test item added to cart!</div>';
            }

            if (isset($_GET['clear_cart'])) {
                $_SESSION['cart'] = [];
                echo '<div class="alert alert-warning"> Cart cleared!</div>';
            }

            // Test cart functionality
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                echo '<div class="alert alert-success">';
                echo ' Cart functionality appears to be working';
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning">';
                echo '⚠️ Cart is empty. Try adding a test item above.';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>