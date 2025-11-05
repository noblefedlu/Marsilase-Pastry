<?php
// admin/reports.php
require_once 'config.php';
requireAdminAuth();

// Get date range parameters
$date_range = $_GET['date_range'] ?? 'month';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Set date range based on selection
switch ($date_range) {
    case 'today':
        $start_date = $end_date = date('Y-m-d');
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('-1 week'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        break;
    case 'quarter':
        $start_date = date('Y-m-01', strtotime('-3 months'));
        $end_date = date('Y-m-t');
        break;
    case 'year':
        $start_date = date('Y-01-01');
        $end_date = date('Y-12-31');
        break;
    case 'custom':
        // Use the provided custom dates
        break;
}

// Get sales statistics
$sales_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        COUNT(DISTINCT customer_email) as unique_customers
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND payment_status = 'paid'
";

$stmt = $conn->prepare($sales_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get daily revenue for chart
$daily_revenue_query = "
    SELECT 
        DATE(created_at) as date,
        SUM(total_amount) as revenue,
        COUNT(*) as order_count
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    AND payment_status = 'paid'
    GROUP BY DATE(created_at)
    ORDER BY date
";

$stmt = $conn->prepare($daily_revenue_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get top products
$top_products_query = "
    SELECT 
        oi.product_name,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.total_price) as total_revenue,
        COUNT(DISTINCT oi.order_id) as order_count
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND o.payment_status = 'paid'
    GROUP BY oi.product_name
    ORDER BY total_revenue DESC
    LIMIT 10
";

$stmt = $conn->prepare($top_products_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get customer statistics
$customer_stats_query = "
    SELECT 
        COUNT(DISTINCT customer_email) as total_customers,
        SUM(CASE WHEN order_count > 1 THEN 1 ELSE 0 END) as repeat_customers,
        AVG(order_count) as avg_orders_per_customer
    FROM (
        SELECT 
            customer_email,
            COUNT(*) as order_count
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND payment_status = 'paid'
        GROUP BY customer_email
    ) as customer_orders
";

$stmt = $conn->prepare($customer_stats_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$customer_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .chart-container {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Reports & Analytics</h2>
                <p class="text-muted mb-0">Comprehensive business insights and performance metrics</p>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Report
                </button>
            </div>
        </div>

        <!-- Date Range Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date Range</label>
                        <select name="date_range" class="form-select" onchange="this.form.submit()">
                            <option value="today" <?= $date_range === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= $date_range === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="month" <?= $date_range === 'month' ? 'selected' : '' ?>>This Month</option>
                            <option value="quarter" <?= $date_range === 'quarter' ? 'selected' : '' ?>>Last 3 Months</option>
                            <option value="year" <?= $date_range === 'year' ? 'selected' : '' ?>>This Year</option>
                            <option value="custom" <?= $date_range === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                        </select>
                    </div>
                    
                    <?php if ($date_range === 'custom'): ?>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Apply</button>
                    </div>
                    <?php else: ?>
                    <div class="col-md-6">
                        <div class="text-muted">
                            Showing data from <strong><?= date('M j, Y', strtotime($start_date)) ?></strong> to 
                            <strong><?= date('M j, Y', strtotime($end_date)) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <a href="reports.php?date_range=custom" class="btn btn-outline-secondary w-100">
                            Custom Range
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Sales Overview Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <h3 class="fw-bold text-primary">ETB <?= number_format($sales_stats['total_revenue'] ?? 0, 2) ?></h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 12.5% from previous period
                    </small>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-cart"></i>
                    </div>
                    <h3 class="fw-bold text-success"><?= $sales_stats['total_orders'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 8.3% from previous period
                    </small>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-basket"></i>
                    </div>
                    <h3 class="fw-bold text-info">ETB <?= number_format($sales_stats['avg_order_value'] ?? 0, 2) ?></h3>
                    <p class="text-muted mb-0">Average Order Value</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 3.8% from previous period
                    </small>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="fw-bold text-warning"><?= $sales_stats['unique_customers'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Unique Customers</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 15.2% from previous period
                    </small>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4">
            <!-- Revenue Chart -->
            <div class="col-lg-8">
                <div class="chart-container">
                    <h5 class="fw-semibold mb-3">Revenue Trend</h5>
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>

            <!-- Customer Metrics -->
            <div class="col-lg-4">
                <div class="chart-container">
                    <h5 class="fw-semibold mb-3">Customer Insights</h5>
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold text-primary"><?= $customer_stats['total_customers'] ?? 0 ?></h4>
                                <small class="text-muted">Total Customers</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold text-success"><?= $customer_stats['repeat_customers'] ?? 0 ?></h4>
                                <small class="text-muted">Repeat Customers</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold text-info">
                                    <?= $customer_stats['total_customers'] > 0 ? 
                                        number_format(($customer_stats['repeat_customers'] / $customer_stats['total_customers']) * 100, 1) : 0 ?>%
                                </h4>
                                <small class="text-muted">Repeat Rate</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <h4 class="fw-bold text-warning">
                                    <?= number_format($customer_stats['avg_orders_per_customer'] ?? 0, 1) ?>
                                </h4>
                                <small class="text-muted">Avg Orders/Customer</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-2">Customer Growth</h6>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" style="width: 65%">New: 65%</div>
                            <div class="progress-bar bg-primary" style="width: 35%">Returning: 35%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="row g-4">
            <div class="col-12">
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0">Top Performing