<?php
session_start();
require_once '../common/connection.php';
requireOwner();

// Get system statistics
$system_stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as today_orders,
        (SELECT COUNT(*) FROM orders WHERE status = 'pending') as pending_orders,
        (SELECT COUNT(*) FROM products WHERE is_active = 1) as active_products,
        (SELECT COUNT(*) FROM cakes WHERE is_active = 1) as active_cakes,
        (SELECT COUNT(*) FROM owners WHERE is_active = 1) as active_owners,
        (SELECT COUNT(*) FROM admins WHERE is_active = 1) as active_admins,
        (SELECT SUM(total_amount) FROM orders WHERE status = 'delivered' AND DATE(created_at) = CURDATE()) as today_revenue
")->fetch_assoc();

// Check for low stock products (if you have stock management)
$low_stock_products = $conn->query("
    SELECT name, stock_quantity 
    FROM products 
    WHERE stock_quantity > 0 AND stock_quantity <= 10 
    ORDER BY stock_quantity ASC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get system warnings
$warnings = [];
if ($system_stats['pending_orders'] > 10) {
    $warnings[] = "High number of pending orders: " . $system_stats['pending_orders'];
}

if (count($low_stock_products) > 0) {
    $warnings[] = count($low_stock_products) . " products are low in stock";
}

// Check disk space (simulated)
$disk_usage = 65; // Simulated percentage
if ($disk_usage > 80) {
    $warnings[] = "High disk usage: " . $disk_usage . "%";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health - Owner Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --owner-primary: #2c3e50;
            --owner-secondary: #34495e;
        }
        
        .owner-nav { background: var(--owner-primary); }
        
        .health-card { 
            border-left: 4px solid; 
            margin-bottom: 1rem; 
        }
        
        .health-good { border-left-color: #28a745; }
        .health-warning { border-left-color: #ffc107; }
        .health-critical { border-left-color: #dc3545; }
        
        .metric-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg owner-nav">
        <div class="container">
            <span class="navbar-brand text-white">
                <i class="bi bi-heart-pulse me-2"></i>System Health
            </span>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="nav-link text-white me-3">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <a href="logout.php" class="nav-link text-white">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="fw-bold mb-4">System Health Monitor</h2>

        <!-- System Status -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card health-card health-good">
                    <div class="card-body">
                        <div class="metric-value text-success"><?= $system_stats['active_products'] + $system_stats['active_cakes'] ?></div>
                        <div class="text-muted">Active Products</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card health-card health-good">
                    <div class="card-body">
                        <div class="metric-value text-primary"><?= $system_stats['today_orders'] ?></div>
                        <div class="text-muted">Today's Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card health-card <?= $system_stats['pending_orders'] > 10 ? 'health-warning' : 'health-good' ?>">
                    <div class="card-body">
                        <div class="metric-value <?= $system_stats['pending_orders'] > 10 ? 'text-warning' : 'text-info' ?>"><?= $system_stats['pending_orders'] ?></div>
                        <div class="text-muted">Pending Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card health-card health-good">
                    <div class="card-body">
                        <div class="metric-value text-success">ETB <?= number_format($system_stats['today_revenue'] ?? 0, 2) ?></div>
                        <div class="text-muted">Today's Revenue</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warnings -->
        <?php if (!empty($warnings)): ?>
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle me-2"></i>System Warnings</h6>
            <ul class="mb-0">
                <?php foreach ($warnings as $warning): ?>
                <li><?= $warning ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Low Stock Alert -->
        <?php if (!empty($low_stock_products)): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Products</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_products as $product): ?>
                            <tr>
                                <td><?= $product['name'] ?></td>
                                <td><?= $product['stock_quantity'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $product['stock_quantity'] <= 5 ? 'danger' : 'warning' ?>">
                                        <?= $product['stock_quantity'] <= 5 ? 'Very Low' : 'Low' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>PHP Version</strong></td>
                                <td><?= phpversion() ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database</strong></td>
                                <td>MySQL</td>
                            </tr>
                            <tr>
                                <td><strong>Server Time</strong></td>
                                <td><?= date('Y-m-d H:i:s') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Active Owners</strong></td>
                                <td><?= $system_stats['active_owners'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Active Admins</strong></td>
                                <td><?= $system_stats['active_admins'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>System Status</strong></td>
                                <td><span class="badge bg-success">Operational</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-body text-center">
                <h6>System Maintenance</h6>
                <div class="btn-group">
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Clear Cache
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="bi bi-database me-2"></i>Backup Database
                    </button>
                    <button class="btn btn-outline-info">
                        <i class="bi bi-file-earmark-text me-2"></i>System Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh system health every 2 minutes
        setTimeout(() => {
            window.location.reload();
        }, 120000);
    </script>
</body>
</html>
<?php $conn->close(); ?>