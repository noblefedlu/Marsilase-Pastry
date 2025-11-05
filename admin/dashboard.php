<?php
// admin/dashboard.php
require_once 'config.php';
requireAdminAuth();

// Get dashboard statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'")->fetch_assoc()['revenue'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) as count FROM cakes WHERE is_active = TRUE")->fetch_assoc()['count'];
$unread_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'")->fetch_assoc()['count'];

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get monthly revenue data for chart
$monthly_revenue = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE payment_status = 'paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-500: #f56e10;
            --primary-600: #e7540a;
            --neutral-50: #f8fafc;
            --neutral-100: #f1f5f9;
            --neutral-200: #e2e8f0;
            --neutral-700: #334155;
            --neutral-900: #0f172a;
        }
        
        .sidebar {
            background: var(--neutral-900);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 280px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: var(--neutral-200);
            padding: 0.75rem 1.25rem;
            margin: 0.25rem 0.75rem;
            border-radius: 0.75rem;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--primary-500);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            transition: all 0.3s;
            background: var(--neutral-50);
            min-height: 100vh;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border: 1px solid var(--neutral-200);
            transition: all 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .bg-primary-light { background: rgba(245, 110, 16, 0.1); color: var(--primary-500); }
        .bg-success-light { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .bg-warning-light { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .bg-info-light { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .bg-purple-light { background: rgba(168, 85, 247, 0.1); color: #a855f7; }
        .bg-pink-light { background: rgba(236, 72, 153, 0.1); color: #ec4899; }
        
        .top-bar {
            background: white;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            margin-bottom: 2rem;
            border: 1px solid var(--neutral-200);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold">Dashboard Overview</h2>
                    <p class="text-muted mb-0">Welcome back, <?= $_SESSION['admin_name'] ?>! 👋</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar3 me-2"></i>
                            <?= date('F j, Y') ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Today</a></li>
                            <li><a class="dropdown-item" href="#">This Week</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light border dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" 
                                 style="width: 32px; height: 32px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span><?= $_SESSION['admin_name'] ?></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-light">
                        <i class="bi bi-cart"></i>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= $total_orders ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 12% from last month
                    </small>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-light">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= $pending_orders ?></h3>
                    <p class="text-muted mb-0">Pending Orders</p>
                    <small class="text-warning">
                        Needs attention
                    </small>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-icon bg-success-light">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= $completed_orders ?></h3>
                    <p class="text-muted mb-0">Completed Orders</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 8% from last month
                    </small>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-icon bg-info-light">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">ETB <?= number_format($total_revenue, 2) ?></h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 15% from last month
                    </small>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-icon bg-purple-light">
                        <i class="bi bi-cake"></i>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= $total_products ?></h3>
                    <p class="text-muted mb-0">Active Products</p>
                    <small class="text-info">
                        All in stock
                    </small>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-icon bg-pink-light">
                        <i class="bi bi-chat-left"></i>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= $unread_messages ?></h3>
                    <p class="text-muted mb-0">Unread Messages</p>
                    <small class="text-warning">
                        Needs reply
                    </small>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Orders -->
        <div class="row g-4">
            <!-- Revenue Chart -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Revenue Overview</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Last 6 Months
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Last 3 Months</a></li>
                                <li><a class="dropdown-item" href="#">Last 6 Months</a></li>
                                <li><a class="dropdown-item" href="#">This Year</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="orders.php?status=pending" class="btn btn-outline-warning text-start">
                                <i class="bi bi-clock me-2"></i>Pending Orders
                                <span class="badge bg-warning ms-2"><?= $pending_orders ?></span>
                            </a>
                            <a href="products.php?action=add" class="btn btn-outline-primary text-start">
                                <i class="bi bi-plus-circle me-2"></i>Add New Product
                            </a>
                            <a href="messages.php" class="btn btn-outline-info text-start">
                                <i class="bi bi-chat-left me-2"></i>View Messages
                                <span class="badge bg-info ms-2"><?= $unread_messages ?></span>
                            </a>
                            <a href="reports.php" class="btn btn-outline-success text-start">
                                <i class="bi bi-graph-up me-2"></i>View Reports
                            </a>
                            <a href="customers.php" class="btn btn-outline-secondary text-start">
                                <i class="bi bi-people me-2"></i>Manage Customers
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="orders.php" class="btn btn-sm btn-primary">View All Orders</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary">#<?= $order['order_number'] ?></strong>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></small>
                                        </td>
                                        <td><?= $order['item_count'] ?> items</td>
                                        <td class="fw-bold">ETB <?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['status'] === 'delivered' ? 'success' : 
                                                ($order['status'] === 'pending' ? 'warning' : 
                                                ($order['status'] === 'confirmed' ? 'info' : 'secondary'))
                                            ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($recent_orders)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x display-1 text-muted"></i>
                            <h4 class="mt-3 text-muted">No orders yet</h4>
                            <p class="text-muted">Orders will appear here once customers start placing them.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column(array_reverse($monthly_revenue), 'month')) ?>,
                datasets: [{
                    label: 'Monthly Revenue (ETB)',
                    data: <?= json_encode(array_column(array_reverse($monthly_revenue), 'revenue')) ?>,
                    borderColor: '#f56e10',
                    backgroundColor: 'rgba(245, 110, 16, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>