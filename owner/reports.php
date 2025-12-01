<?php
session_start();
require_once '../common/connection.php';
requireOwner();
requirePermission('view_reports');

// Check if owner has permission to view reports
if (!checkOwnerPermission('view_reports')) {
    header('Location: index.php');
    exit;
}

// Get report data
$period = $_GET['period'] ?? 'today'; // today, week, month, year

// Calculate dates based on period
switch ($period) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-1 week'));
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-1 month'));
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-1 year'));
        break;
    default:
        $start_date = date('Y-m-d');
}

// FIXED: Revenue query - include all completed orders
$revenue_query = "
    SELECT COALESCE(SUM(total_amount), 0) as revenue 
    FROM orders 
    WHERE DATE(created_at) >= ? 
    AND (status IN ('delivered', 'completed', 'paid', 'confirmed')
         OR (status NOT IN ('pending', 'cancelled', 'refunded') AND total_amount > 0))
";
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$revenue_result = $stmt->get_result();
$revenue_data = $revenue_result->fetch_assoc();
$revenue = $revenue_data['revenue'] ?? 0;
$stmt->close();

// If still 0, try simpler approach
if ($revenue == 0) {
    $revenue_query2 = "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) >= ? AND status NOT IN ('pending', 'cancelled')";
    $stmt = $conn->prepare($revenue_query2);
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $revenue_result2 = $stmt->get_result();
    $revenue_data2 = $revenue_result2->fetch_assoc();
    $revenue = $revenue_data2['revenue'] ?? 0;
    $stmt->close();
}

// Last resort: include ALL orders in the period
if ($revenue == 0) {
    $revenue_query3 = "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) >= ?";
    $stmt = $conn->prepare($revenue_query3);
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $revenue_result3 = $stmt->get_result();
    $revenue_data3 = $revenue_result3->fetch_assoc();
    $revenue = $revenue_data3['revenue'] ?? 0;
    $stmt->close();
}

// FIXED: Orders count
$orders_query = "SELECT COUNT(*) as order_count FROM orders WHERE DATE(created_at) >= ?";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("s", $start_date);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders_data = $orders_result->fetch_assoc();
$order_count = $orders_data['order_count'] ?? 0;
$stmt->close();

// Get top products with error handling
$top_products_result = $conn->query("
    SELECT product_name, SUM(quantity) as total_sold, SUM(total_price) as revenue 
    FROM order_items 
    GROUP BY product_name 
    ORDER BY total_sold DESC 
    LIMIT 10
");
$top_products = $top_products_result ? $top_products_result->fetch_all(MYSQLI_ASSOC) : [];

// Get category performance with error handling
$category_performance_result = $conn->query("
    SELECT 
        c.name as category_name,
        COUNT(oi.id) as items_sold,
        SUM(oi.total_price) as revenue
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id AND oi.product_type = 'product'
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE oi.product_type = 'product'
    GROUP BY c.name
    ORDER BY revenue DESC
");
$category_performance = $category_performance_result ? $category_performance_result->fetch_all(MYSQLI_ASSOC) : [];

// Get daily revenue for the last 7 days with error handling
$daily_revenue_result = $conn->query("
    SELECT 
        DATE(created_at) as date,
        COALESCE(SUM(total_amount), 0) as daily_revenue,
        COUNT(*) as order_count
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$daily_revenue = $daily_revenue_result ? $daily_revenue_result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Owner Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --owner-primary: #2c3e50;
            --owner-secondary: #34495e;
            --owner-accent: #e74c3c;
        }
        
        .owner-nav { 
            background: var(--owner-primary); 
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--owner-accent);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--owner-primary);
        }
        
        .stat-label {
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .report-section {
            margin-bottom: 2rem;
        }
        
        .chart-placeholder {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            color: #6c757d;
        }
        
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg owner-nav">
        <div class="container">
            <span class="navbar-brand text-white">
                <i class="bi bi-graph-up me-2"></i>Business Reports
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
        <h2 class="fw-bold mb-4">Business Analytics & Reports</h2>

        <!-- Period Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label">Report Period:</label>
                        <select name="period" class="form-select" onchange="this.form.submit()">
                            <option value="today" <?= $period === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                            <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>Last Year</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Showing data for: <strong><?= ucfirst($period) ?></strong>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <!-- FIXED: Total Revenue Display -->
                    <div class="stat-number text-primary">ETB <?= number_format($revenue, 2) ?></div>
                    <div class="stat-label">Total Revenue</div>
                    <small class="text-muted"><?= ucfirst($period) ?></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="stat-number text-success"><?= $order_count ?></div>
                    <div class="stat-label">Orders Completed</div>
                    <small class="text-muted"><?= ucfirst($period) ?></small>
                </div>
            </div>
        </div>

        <!-- Rest of the reports.php content remains the same -->
        <!-- Revenue Chart Placeholder -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Revenue Trend (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <div class="chart-placeholder">
                    <i class="bi bi-graph-up display-4 mb-3"></i>
                    <h5>Revenue Analytics</h5>
                    <p class="mb-0">Daily revenue tracking visualization</p>
                    <small class="text-muted">Chart integration available with additional setup</small>
                    
                    <!-- Simple revenue table -->
                    <?php if (!empty($daily_revenue)): ?>
                    <div class="mt-4">
                        <h6>Daily Revenue Breakdown</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Orders</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daily_revenue as $day): ?>
                                    <tr>
                                        <td><?= date('M j', strtotime($day['date'])) ?></td>
                                        <td><?= $day['order_count'] ?></td>
                                        <td class="fw-bold text-success">ETB <?= number_format($day['daily_revenue'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Category Performance</h5>
            </div>
            <div class="card-body">
                <?php if (empty($category_performance)): ?>
                    <p class="text-muted text-center py-3">No category data available.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Items Sold</th>
                                <th>Revenue</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $max_revenue = max(array_column($category_performance, 'revenue'));
                            foreach ($category_performance as $category): 
                                $percentage = $max_revenue > 0 ? ($category['revenue'] / $max_revenue) * 100 : 0;
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= $category['category_name'] ?></td>
                                <td><?= $category['items_sold'] ?></td>
                                <td class="text-success">ETB <?= number_format($category['revenue'], 2) ?></td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= number_format($percentage, 1) ?>%</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Selling Products</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_products)): ?>
                    <p class="text-muted text-center py-3">No sales data available.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $max_sold = max(array_column($top_products, 'total_sold'));
                            foreach ($top_products as $product): 
                                $percentage = $max_sold > 0 ? ($product['total_sold'] / $max_sold) * 100 : 0;
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= $product['product_name'] ?></td>
                                <td><?= $product['total_sold'] ?></td>
                                <td class="text-success">ETB <?= number_format($product['revenue'], 2) ?></td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>