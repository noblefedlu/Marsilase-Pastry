<?php
session_start();

// Define the root directory and config path
$root_dir = dirname(dirname(__FILE__));
$config_path = $root_dir . '/config.php';

// Check if config file exists before requiring it
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

// Get date range parameters
$period = $_GET['period'] ?? 'month'; // day, week, month, year
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-t');

// Set date range based on period
if ($period === 'today') {
    $date_from = $date_to = date('Y-m-d');
} elseif ($period === 'week') {
    $date_from = date('Y-m-d', strtotime('monday this week'));
    $date_to = date('Y-m-d', strtotime('sunday this week'));
} elseif ($period === 'month') {
    $date_from = date('Y-m-01');
    $date_to = date('Y-m-t');
} elseif ($period === 'year') {
    $date_from = date('Y-01-01');
    $date_to = date('Y-12-31');
}

// Sales Analytics
$sales_data = $conn->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as order_count,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE created_at BETWEEN '$date_from' AND '$date_to 23:59:59'
    AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date
")->fetch_all(MYSQLI_ASSOC);

// Product Performance
$product_performance = $conn->query("
    SELECT 
        oi.product_name,
        SUM(oi.quantity) as total_sold,
        SUM(oi.total_price) as total_revenue,
        COUNT(DISTINCT oi.order_id) as order_count,
        AVG(oi.quantity) as avg_quantity_per_order
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at BETWEEN '$date_from' AND '$date_to 23:59:59'
    AND o.status != 'cancelled'
    GROUP BY oi.product_name
    ORDER BY total_sold DESC
")->fetch_all(MYSQLI_ASSOC);

// Customer Analytics
$customer_analytics = $conn->query("
    SELECT 
        COUNT(DISTINCT customer_phone) as total_customers,
        COUNT(DISTINCT CASE WHEN created_at BETWEEN '$date_from' AND '$date_to 23:59:59' THEN customer_phone END) as new_customers,
        AVG(total_amount) as avg_customer_spend,
        MAX(total_amount) as max_order_value,
        COUNT(*) as total_orders
    FROM orders 
    WHERE created_at BETWEEN '$date_from' AND '$date_to 23:59:59'
    AND status != 'cancelled'
")->fetch_assoc();

// Category Performance
$category_performance = $conn->query("
    SELECT 
        c.category,
        COUNT(oi.id) as items_sold,
        SUM(oi.total_price) as category_revenue,
        COUNT(DISTINCT o.id) as orders_count
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN cakes c ON oi.product_name = c.name
    WHERE o.created_at BETWEEN '$date_from' AND '$date_to 23:59:59'
    AND o.status != 'cancelled'
    GROUP BY c.category
    ORDER BY category_revenue DESC
")->fetch_all(MYSQLI_ASSOC);

// Time-based analytics
$hourly_sales = $conn->query("
    SELECT 
        HOUR(created_at) as hour,
        COUNT(*) as order_count,
        SUM(total_amount) as total_revenue
    FROM orders 
    WHERE created_at BETWEEN '$date_from' AND '$date_to 23:59:59'
    AND status != 'cancelled'
    GROUP BY HOUR(created_at)
    ORDER BY hour
")->fetch_all(MYSQLI_ASSOC);

// Payment method analysis
$payment_analysis = $conn->query("
    SELECT 
        COALESCE(payment_method, 'cash') as payment_method,
        COUNT(*) as order_count,
        SUM(total_amount) as total_amount,
        AVG(total_amount) as avg_amount
    FROM orders 
    WHERE created_at BETWEEN '$date_from' AND '$date_to 23:59:59'
    AND status != 'cancelled'
    GROUP BY COALESCE(payment_method, 'cash')
    ORDER BY total_amount DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Analytics & Reports</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>
                                Print
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="bi bi-download me-1"></i>
                                Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Date Range Selector -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Quick Period</label>
                                <select name="period" class="form-select" onchange="this.form.submit()">
                                    <option value="today" <?= $period === 'today' ? 'selected' : '' ?>>Today</option>
                                    <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>This Week</option>
                                    <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>This Month</option>
                                    <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>This Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>" 
                                       <?= $period !== 'custom' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>" 
                                       <?= $period !== 'custom' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Revenue</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ETB <?= number_format(array_sum(array_column($sales_data, 'total_revenue')), 2) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Orders</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $customer_analytics['total_orders'] ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Avg Order Value</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ETB <?= number_format($customer_analytics['avg_customer_spend'], 2) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            New Customers</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $customer_analytics['new_customers'] ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Sales Trend Chart -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Sales Trend</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Performance -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Top Products</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie pt-4 pb-2">
                                    <canvas id="productChart"></canvas>
                                </div>
                                <div class="mt-4 text-center small">
                                    <?php $top_products = array_slice($product_performance, 0, 5); ?>
                                    <?php foreach ($top_products as $product): ?>
                                    <span class="mr-2">
                                        <i class="bi bi-circle-fill text-primary"></i> 
                                        <?= htmlspecialchars($product['product_name']) ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Reports -->
                <div class="row">
                    <!-- Product Performance Table -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Product Performance</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Sold</th>
                                                <th>Revenue</th>
                                                <th>Orders</th>
                                                <th>Avg Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($product_performance, 0, 10) as $product): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                                <td><?= $product['total_sold'] ?></td>
                                                <td>ETB <?= number_format($product['total_revenue'], 2) ?></td>
                                                <td><?= $product['order_count'] ?></td>
                                                <td><?= number_format($product['avg_quantity_per_order'], 1) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Performance -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Category Performance</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Items Sold</th>
                                                <th>Revenue</th>
                                                <th>Orders</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($category_performance as $category): ?>
                                            <tr>
                                                <td><?= ucfirst($category['category']) ?></td>
                                                <td><?= $category['items_sold'] ?></td>
                                                <td>ETB <?= number_format($category['category_revenue'], 2) ?></td>
                                                <td><?= $category['orders_count'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Analytics -->
                <div class="row">
                    <!-- Hourly Sales -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Hourly Sales Distribution</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="hourlyChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Payment Method Analysis</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Method</th>
                                                <th>Orders</th>
                                                <th>Amount</th>
                                                <th>Average</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payment_analysis as $payment): ?>
                                            <tr>
                                                <td><?= ucfirst($payment['payment_method']) ?></td>
                                                <td><?= $payment['order_count'] ?></td>
                                                <td>ETB <?= number_format($payment['total_amount'], 2) ?></td>
                                                <td>ETB <?= number_format($payment['avg_amount'], 2) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Analytics Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="export_analytics.php">
                    <div class="modal-body">
                        <input type="hidden" name="date_from" value="<?= $date_from ?>">
                        <input type="hidden" name="date_to" value="<?= $date_to ?>">
                        <div class="mb-3">
                            <label class="form-label">Export Format</label>
                            <select name="format" class="form-select" required>
                                <option value="pdf">PDF Report</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="csv">CSV Data</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-select" required>
                                <option value="summary">Summary Report</option>
                                <option value="detailed">Detailed Analysis</option>
                                <option value="products">Product Performance</option>
                                <option value="customers">Customer Analytics</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Include Charts</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_charts" value="1" checked>
                                <label class="form-check-label">Include charts and graphs</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Export Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: [<?= implode(',', array_map(function($item) { 
                    return "'" . date('M j', strtotime($item['date'])) . "'"; 
                }, $sales_data)) ?>],
                datasets: [{
                    label: 'Revenue (ETB)',
                    data: [<?= implode(',', array_map(function($item) { 
                        return $item['total_revenue']; 
                    }, $sales_data)) ?>],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Orders',
                    data: [<?= implode(',', array_map(function($item) { 
                        return $item['order_count']; 
                    }, $sales_data)) ?>],
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (ETB)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Product Performance Chart
        const productCtx = document.getElementById('productChart').getContext('2d');
        const productChart = new Chart(productCtx, {
            type: 'doughnut',
            data: {
                labels: [<?= implode(',', array_map(function($item) { 
                    return "'" . $item['product_name'] . "'"; 
                }, array_slice($product_performance, 0, 5))) ?>],
                datasets: [{
                    data: [<?= implode(',', array_map(function($item) { 
                        return $item['total_revenue']; 
                    }, array_slice($product_performance, 0, 5))) ?>],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%',
            },
        });

        // Hourly Sales Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyChart = new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: [<?= implode(',', array_map(function($item) { 
                    return $item['hour']; 
                }, $hourly_sales)) ?>],
                datasets: [{
                    label: 'Orders',
                    data: [<?= implode(',', array_map(function($item) { 
                        return $item['order_count']; 
                    }, $hourly_sales)) ?>],
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Orders'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hour of Day'
                        }
                    }
                }
            }
        });

        // Period selector for custom dates
        document.querySelector('select[name="period"]').addEventListener('change', function() {
            const dateFrom = document.querySelector('input[name="date_from"]');
            const dateTo = document.querySelector('input[name="date_to"]');
            
            if (this.value === 'custom') {
                dateFrom.readOnly = false;
                dateTo.readOnly = false;
            } else {
                dateFrom.readOnly = true;
                dateTo.readOnly = true;
            }
        });
    </script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>