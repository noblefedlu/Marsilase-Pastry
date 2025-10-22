<?php
session_start();
include '../config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get orders data
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? '';

// Build query
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE 1=1";

$params = [];
$types = '';

if ($status_filter !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($date_filter)) {
    $query .= " AND DATE(o.created_at) = ?";
    $params[] = $date_filter;
    $types .= 's';
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get status counts
$status_counts = [];
$result = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// Get today's orders and total revenue
$today_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'delivered'")->fetch_assoc()['revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: #8B4513;
        }
        
        .navbar-brand {
            color: white;
            font-weight: 600;
        }
        
        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 1.25rem;
            font-weight: 600;
            color: #333;
        }
        
        .table th {
            background: #8B4513;
            color: white;
            border: none;
            padding: 1rem;
        }
        
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-delivered { background: #d1edff; color: #004085; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        
        .btn-primary {
            background: #8B4513;
            border: none;
        }
        
        .btn-primary:hover {
            background: #654321;
        }
        
        .filters-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #666;
        }
        
        .nav-link {
            color: white !important;
        }
        
        .nav-link:hover {
            color: #f8f9fa !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
            </span>
            <div class="navbar-nav ms-auto">
                <a href="change_password.php" class="nav-link me-3">
                    <i class="bi bi-key me-1"></i>Change Password
                </a>
                <a href="../index.php" class="nav-link me-3">
                    <i class="bi bi-arrow-left me-1"></i>Main Site
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2">Welcome, <?= $_SESSION['admin_full_name'] ?>!</h3>
                    <p class="text-muted mb-0">You are logged in as <?= $_SESSION['admin_role'] ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        <?= date('F j, Y') ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number text-primary"><?= array_sum($status_counts) ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-warning"><?= $status_counts['pending'] ?? 0 ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?= $status_counts['delivered'] ?? 0 ?></div>
                <div class="stat-label">Delivered</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info">Birr <?= number_format($total_revenue, 2) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <h5 class="mb-3">Filter Orders</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="all">All Statuses</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="date" name="date" value="<?= $date_filter ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Management</h5>
                <span class="badge bg-primary"><?= count($orders) ?> orders</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                        <h5 class="text-muted">No orders found</h5>
                        <p class="text-muted">No orders match your current filters.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-semibold">#<?= $order['order_number'] ?></td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($order['customer_name']) ?></div>
                                    <small class="text-muted"><?= $order['customer_phone'] ?></small>
                                </td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td class="fw-bold">Birr <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge badge-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                onclick="viewOrder(<?= $order['id'] ?>)">
                                            View
                                        </button>
                                        <select class="form-select form-select-sm" onchange="updateStatus(<?= $order['id'] ?>, this.value)">
                                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
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
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetails">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewOrder(orderId) {
        fetch(`order_details.php?id=${orderId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('orderDetails').innerHTML = html;
                new bootstrap.Modal(document.getElementById('orderModal')).show();
            })
            .catch(error => {
                console.error('Error loading order details:', error);
                alert('Error loading order details');
            });
    }

    function updateStatus(orderId, status) {
        if (confirm('Are you sure you want to update this order status?')) {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);
            
            fetch('update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        }
    }
    </script>
</body>
</html>

<?php
$conn->close();
?>