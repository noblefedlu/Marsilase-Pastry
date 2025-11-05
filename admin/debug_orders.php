<?php
session_start();

// Define the root directory and config path
$root_dir = dirname(dirname(__FILE__));
$config_path = $root_dir . '/config.php';

// Check if config file exists before including it
if (!file_exists($config_path)) {
    die("Configuration file not found. Please check if config.php exists in the root directory.");
}

require_once $config_path;

// Check database connection
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../?page=admin-login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Orders - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --cream: #FFF5E1;
            --orange: #FF914D;
            --white: #FFFFFF;
            --brown: #3A2E1F;
            --light-orange: #FFE8D6;
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }
        
        body {
            background: var(--cream);
            color: var(--brown);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .debug-card {
            background: linear-gradient(135deg, var(--white) 0%, var(--light-orange) 100%);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .test-success {
            color: #198754;
            font-weight: 600;
        }
        
        .test-error {
            color: #dc3545;
            font-weight: 600;
        }
        
        .test-warning {
            color: #ffc107;
            font-weight: 600;
        }
        
        pre {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid var(--orange);
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background: var(--orange);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #E5813D;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 145, 77, 0.4);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">System Debug</h1>
                        <p class="text-muted mb-0">Diagnose and troubleshoot system issues</p>
                    </div>
                    <a href="../?page=admin-dashboard" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                </div>

                <div class="glass-card p-4 fade-in">
                    <h4 class="mb-4"><i class="bi bi-bug me-2"></i>Order System Debug</h4>
                    
                    <?php
                    echo "<div class='debug-card'>";
                    echo "<h5 class='mb-3'>1. Database Connection Test:</h5>";
                    if ($conn) {
                        echo "<p class='test-success'>✅ Connected to database successfully</p>";
                        
                        // Test basic query
                        $result = $conn->query("SELECT 1");
                        if ($result) {
                            echo "<p class='test-success'>✅ Basic query works</p>";
                        } else {
                            echo "<p class='test-error'>❌ Basic query failed: " . $conn->error . "</p>";
                        }
                    } else {
                        echo "<p class='test-error'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
                    }
                    echo "</div>";

                    // Check required tables
                    echo "<div class='debug-card'>";
                    echo "<h5 class='mb-3'>2. Database Tables Check:</h5>";
                    $tables = ['orders', 'order_items', 'cakes', 'admins'];
                    $missing_tables = [];
                    
                    foreach ($tables as $table) {
                        $result = $conn->query("SHOW TABLES LIKE '$table'");
                        if ($result && $result->num_rows > 0) {
                            echo "<p class='test-success'>✅ Table '$table' exists</p>";
                        } else {
                            echo "<p class='test-error'>❌ Table '$table' is missing!</p>";
                            $missing_tables[] = $table;
                        }
                    }
                    echo "</div>";

                    // Test orders table structure
                    echo "<div class='debug-card'>";
                    echo "<h5 class='mb-3'>3. Orders Table Structure:</h5>";
                    $result = $conn->query("DESCRIBE orders");
                    if ($result) {
                        echo "<p class='test-success'>✅ Orders table structure:</p>";
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-sm'>";
                        echo "<thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>";
                        echo "<tbody>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>{$row['Field']}</strong></td>";
                            echo "<td>{$row['Type']}</td>";
                            echo "<td>{$row['Null']}</td>";
                            echo "<td>{$row['Key']}</td>";
                            echo "<td>{$row['Default']}</td>";
                            echo "<td>{$row['Extra']}</td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table></div>";
                    } else {
                        echo "<p class='test-error'>❌ Could not describe orders table: " . $conn->error . "</p>";
                    }
                    echo "</div>";

                    // Test order insertion
                    echo "<div class='debug-card'>";
                    echo "<h5 class='mb-3'>4. Test Order Insertion:</h5>";
                    try {
                        $test_order_num = 'TEST' . time();
                        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, delivery_address, delivery_date, total_amount, status) VALUES (?, 'Test Customer', '1234567890', 'Test Address', CURDATE(), 100.00, 'pending')");
                        
                        if ($stmt) {
                            $stmt->bind_param("s", $test_order_num);
                            if ($stmt->execute()) {
                                $test_order_id = $conn->insert_id;
                                echo "<p class='test-success'>✅ Test order inserted successfully (ID: $test_order_id)</p>";
                                
                                // Test order item insertion
                                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_type, product_id, product_name, flavor, size, quantity, unit_price, total_price) VALUES (?, 'cake', 1, 'Test Cake', 'Vanilla', 'Small', 1, 100.00, 100.00)");
                                
                                if ($stmt2) {
                                    $stmt2->bind_param("i", $test_order_id);
                                    if ($stmt2->execute()) {
                                        echo "<p class='test-success'>✅ Test order item inserted successfully</p>";
                                    } else {
                                        echo "<p class='test-error'>❌ Test order item failed: " . $stmt2->error . "</p>";
                                    }
                                    $stmt2->close();
                                } else {
                                    echo "<p class='test-error'>❌ Failed to prepare order item statement: " . $conn->error . "</p>";
                                }
                                
                                // Clean up
                                $conn->query("DELETE FROM order_items WHERE order_id = $test_order_id");
                                $conn->query("DELETE FROM orders WHERE id = $test_order_id");
                                echo "<p class='test-success'>✅ Test data cleaned up</p>";
                                
                            } else {
                                echo "<p class='test-error'>❌ Test order failed: " . $stmt->error . "</p>";
                            }
                            $stmt->close();
                        } else {
                            echo "<p class='test-error'>❌ Failed to prepare order statement: " . $conn->error . "</p>";
                        }
                        
                    } catch (Exception $e) {
                        echo "<p class='test-error'>❌ Test failed: " . $e->getMessage() . "</p>";
                    }
                    echo "</div>";

                    // Check session
                    echo "<div class='debug-card'>";
                    echo "<h5 class='mb-3'>5. Session Check:</h5>";
                    if (empty($_SESSION['cart'])) {
                        echo "<p class='test-warning'>ℹ️ Cart is empty (this is normal if you haven't added items)</p>";
                    } else {
                        echo "<p class='test-success'>✅ Cart contains " . count($_SESSION['cart']) . " items</p>";
                    }
                    
                    echo "<p><strong>Admin Session:</strong> " . ($_SESSION['admin_logged_in'] ? 'Logged In' : 'Not Logged In') . "</p>";
                    echo "<p><strong>Admin Name:</strong> " . ($_SESSION['admin_name'] ?? 'Not set') . "</p>";
                    echo "</div>";

                    // System information
                    echo "<div class='debug-card'>";
                    echo "<h5 class='mb-3'>6. System Information:</h5>";
                    echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
                    echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
                    // Safely determine database host and name (avoid undefined constant notices)
                    $dbHost = 'Unknown';
                    if (defined('DB_HOST')) {
                        // use constant() to avoid accidental undefined-constant usage
                        $dbHost = constant('DB_HOST');
                    } elseif (getenv('DB_HOST')) {
                        $dbHost = getenv('DB_HOST');
                    } elseif (isset($conn) && !empty($conn->host_info)) {
                        // fallback to mysqli connection info when available
                        $dbHost = $conn->host_info;
                    }
                    $dbName = 'Unknown';
                    if (defined('DB_NAME')) {
                        $dbName = constant('DB_NAME');
                    } elseif (isset($conn) && $resDb = $conn->query("SELECT DATABASE()")) {
                        $rowDb = $resDb->fetch_row();
                        $dbName = $rowDb[0] ?? 'Unknown';
                        $resDb->free();
                    }
                    echo "<p><strong>Database Host:</strong> " . htmlspecialchars($dbHost) . "</p>";
                    echo "<p><strong>Database Name:</strong> " . htmlspecialchars($dbName) . "</p>";
                    echo "</div>";

                    // Recommendations
                    echo "<div class='debug-card'>";
                    echo "<h5 class='mb-3'>7. Next Steps:</h5>";
                    if (!empty($missing_tables)) {
                        echo "<div class='alert alert-warning'>";
                        echo "<p><strong>Missing Tables Detected:</strong></p>";
                        echo "<ul>";
                        foreach ($missing_tables as $table) {
                            echo "<li>$table</li>";
                        }
                        echo "</ul>";
                        echo "<p>You need to run the SQL schema to create these tables.</p>";
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-success'>";
                        echo "<p><strong>All basic checks passed!</strong></p>";
                        echo "<p>Your system appears to be configured correctly.</p>";
                        echo "</div>";
                    }
                    
                    echo "<p>If tests above show errors, you need to:</p>";
                    echo "<ol>";
                    echo "<li>Run the SQL schema to create missing tables</li>";
                    echo "<li>Check database permissions</li>";
                    echo "<li>Verify the config.php database credentials</li>";
                    echo "<li>Check file permissions for the uploads directory</li>";
                    echo "</ol>";
                    echo "</div>";
                    ?>
                    
                    <div class="text-center mt-4">
                        <button class="btn btn-primary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Run Tests Again
                        </button>
                        <a href="../?page=admin-dashboard" class="btn btn-outline-primary">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>