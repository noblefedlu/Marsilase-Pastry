<?php
// admin/orders.php
require_once 'config.php';
requireAdminAuth();

// Handle status updates
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order status updated successfully!";
        logAdminAction('Order Status Update', "Updated order #$order_id to $new_status");
    } else {
        $_SESSION['error_message'] = "Error updating order status.";
    }
    $stmt->close();
}

// Handle order deletion
if (isset($_GET['delete_id'])) {
    $order_id = $_GET['delete_id'];
    
    if ($conn->query("DELETE FROM orders WHERE id = $order_id")) {
        $_SESSION['success_message'] = "Order deleted successfully!";
        logAdminAction('Order Deletion', "Deleted order #$order_id");
    } else {
        $_SESSION['error_message'] = "Error deleting order.";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search_term = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$query = "SELECT o.*, COUNT(oi.id) as item_count FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE 1=1";
$params = [];
$types = '';

if ($status_filter !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search_term)) {
    $query .= " AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ? OR o.customer_email LIKE ?)";
    $search_like = "%$search_term%";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= 'ssss';
}

if (!empty($date_from)) {
    $query .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $query .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get status counts for filter badges
$status_counts = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

$status_count_map = [];
foreach ($status_counts as $status_count) {
    $status_count_map[$status_count['status']] = $status_count['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table th { 
            border-top: none; 
            font-weight: 600;
            color: #64748b;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge { 
            font-size: 0.75rem; 
            font-weight: 500;
            padding: 0.35rem 0.75rem;
        }
        
        .order-row:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            transition: all 0.2s;
        }
        
        .filter-badge {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-badge:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Order Management</h2>
                <p class="text-muted mb-0">Manage and track customer orders</p>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Status Filter Badges -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Quick Filters</h6>
                <div class="d-flex flex-wrap gap-2">
                    <a href="orders.php" class="badge filter-badge <?= $status_filter === 'all' ? 'bg-primary' : 'bg-light text-dark' ?> text-decoration-none p-2">
                        All Orders <span class="badge bg-secondary ms-1"><?= array_sum($status_count_map) ?></span>
                    </a>
                    <?php foreach ($status_count_map as $status => $count): ?>
                    <a href="orders.php?status=<?= $status ?>" 
                       class="badge filter-badge <?= $status_filter === $status ? 'bg-primary' : 'bg-light text-dark' ?> text-decoration-none p-2">
                        <?= ucfirst($status) ?> <span class="badge bg-secondary ms-1"><?= $count ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status Filter</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Orders</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="preparing" <?= $status_filter === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                            <option value="ready" <?= $status_filter === 'ready' ? 'selected' : '' ?>>Ready</option>
                            <option value="out_for_delivery" <?= $status_filter === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                            <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="orders.php" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-semibold">Search Orders</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by order number, customer name, phone, or email..." 
                                   value="<?= htmlspecialchars($search_term) ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Orders (<?= count($orders) ?>)</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Delivery Date</th>
                                <th>Order Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr class="order-row">
                                <td>
                                    <strong class="text-primary">#<?= $order['order_number'] ?></strong>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($order['customer_phone']) ?>
                                    <?php if ($order['delivery_address']): ?>
                                    <br><small class="text-muted" title="<?= htmlspecialchars($order['delivery_address']) ?>">
                                        <i class="bi bi-geo-alt"></i> View Address
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= $order['item_count'] ?> items</span>
                                </td>
                                <td class="fw-bold text-success">ETB <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" 
                                                style="min-width: 140px;">
                                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                            <option value="ready" <?= $order['status'] === 'ready' ? 'selected' : '' ?>>Ready</option>
                                            <option value="out_for_delivery" <?= $order['status'] === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <?php if ($order['delivery_date']): ?>
                                        <?= date('M j, Y', strtotime($order['delivery_date'])) ?>
                                        <?php if ($order['delivery_time']): ?>
                                            <br><small class="text-muted"><?= $order['delivery_time'] ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="order-print.php?id=<?= $order['id'] ?>" class="btn btn-outline-secondary" title="Print" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="?delete_id=<?= $order['id'] ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete order #<?= $order['order_number'] ?>? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No orders found</h4>
                    <p class="text-muted">No orders match your current filters.</p>
                    <a href="orders.php" class="btn btn-primary">Clear Filters</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Orders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="export-orders.php">
                        <div class="mb-3">
                            <label class="form-label">Export Format</label>
                            <select name="format" class="form-select">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" name="export_date_from" class="form-control" placeholder="From">
                                </div>
                                <div class="col-6">
                                    <input type="date" name="export_date_to" class="form-control" placeholder="To">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="export_status" class="form-select">
                                <option value="all">All Orders</option>
                                <option value="pending">Pending</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Export</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit date filters when both are selected
        const dateFrom = document.querySelector('input[name="date_from"]');
        const dateTo = document.querySelector('input[name="date_to"]');
        
        [dateFrom, dateTo].forEach(input => {
            input.addEventListener('change', function() {
                if (dateFrom.value && dateTo.value) {
                    this.form.submit();
                }
            });
        });
    </script>
</body>
</html>