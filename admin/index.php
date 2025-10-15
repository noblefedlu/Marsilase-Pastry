<?php
session_start();
include '../config.php';

// Strict authentication check - ALWAYS required
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Clear any existing session data
    session_unset();
    session_destroy();
    // Start fresh session for redirect
    session_start();
    $_SESSION['auth_redirect'] = basename($_SERVER['PHP_SELF']);
    header('Location: login.php');
    exit;
}

// Additional security: validate session data
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username']) || !isset($_SESSION['admin_role'])) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['auth_error'] = 'Invalid session data';
    header('Location: login.php');
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #8B4513;
            --secondary: #D4A574;
            --light: #FFF8F0;
            --dark: #5D4037;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
        }
        
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .order-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .admin-welcome {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="admin-panel">
    <nav class="navbar navbar-dark admin-header">
        <div class="container">
            <span class="navbar-brand fw-bold">
                <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
            </span>
            <div>
                <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
                <a href="manage_admins.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-people me-1"></i>Manage Admins
                </a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="admin-welcome">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-2">Welcome, <?= $_SESSION['admin_full_name'] ?>!</h2>
                    <p class="mb-0 opacity-75">You are logged in as <?= $_SESSION['admin_role'] ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../index.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Main Site
                    </a>
                </div>
            </div>
        </div>
        
        <h2 class="fw-bold mb-4 text-dark">Order Management</h2>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-clock display-6 mb-2"></i>
                        <h3><?= $status_counts['pending'] ?? 0 ?></h3>
                        <small>Pending Orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle display-6 mb-2"></i>
                        <h3><?= $status_counts['delivered'] ?? 0 ?></h3>
                        <small>Delivered</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card text-white bg-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle display-6 mb-2"></i>
                        <h3><?= $status_counts['cancelled'] ?? 0 ?></h3>
                        <small>Cancelled</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card text-white bg-secondary">
                    <div class="card-body text-center">
                        <i class="bi bi-inbox display-6 mb-2"></i>
                        <h3><?= array_sum($status_counts) ?></h3>
                        <small>Total Orders</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 text-dark">
                    <i class="bi bi-funnel me-2"></i>Filter Orders
                </h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="all">All Statuses</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="date" value="<?= $date_filter ?>" class="form-control">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-filter me-1"></i>Apply Filters
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">No orders found</h4>
                        <p class="text-muted">No orders match your current filters.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover order-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Delivery Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-semibold"><?= $order['order_number'] ?></td>
                                <td><?= $order['delivery_date'] ?></td>
                                <td class="fw-bold text-primary">Birr <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge status-badge bg-<?= getStatusBadge($order['status']) ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $order['item_count'] ?> items</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                onclick="viewOrder(<?= $order['id'] ?>)">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="updateStatus(<?= $order['id'] ?>, 'pending')">
                                                    <i class="bi bi-clock text-warning me-2"></i>Mark as Pending
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="updateStatus(<?= $order['id'] ?>, 'delivered')">
                                                    <i class="bi bi-check-circle text-success me-2"></i>Mark as Delivered
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" 
                                                   onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')">
                                                    <i class="bi bi-x-circle me-2"></i>Cancel Order
                                                </a>
                                            </li>
                                        </ul>
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
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>Order Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                alert('Error loading order details. Please try again.');
            });
    }

    function updateStatus(orderId, status) {
        const statusNames = {
            'pending': 'Pending',
            'delivered': 'Delivered', 
            'cancelled': 'Cancelled'
        };
        
        if (confirm(`Are you sure you want to mark this order as ${statusNames[status]}?`)) {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);
            
            fetch('update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status. Please check the console for details.');
            });
        }
    }
    </script>
</body>
</html>

<?php
function getStatusBadge($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        case 'canceled': return 'danger';
        default: return 'secondary';
    }
}

$conn->close();
?>