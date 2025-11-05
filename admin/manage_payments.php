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

$message = '';
$error = '';

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_payment_status') {
            $order_id = $_POST['order_id'];
            $payment_status = $_POST['payment_status'];
            $notes = $_POST['notes'] ?? '';

            $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, payment_notes = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("ssi", $payment_status, $notes, $order_id);
                if ($stmt->execute()) {
                    $message = "Payment status updated successfully!";
                    
                    // Log the action
                    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
                    $action_desc = "Updated payment status for order #$order_id to $payment_status";
                    $log_stmt->bind_param("isss", $_SESSION['admin_id'], $action_desc, $action_desc, $_SERVER['REMOTE_ADDR']);
                    $log_stmt->execute();
                    $log_stmt->close();
                } else {
                    $error = "Failed to update payment status: " . $stmt->error;
                }
                $stmt->close();
            }
        }

        if ($_POST['action'] === 'process_refund') {
            $order_id = $_POST['order_id'];
            $refund_amount = $_POST['refund_amount'];
            $refund_reason = $_POST['refund_reason'];
            
            // Update order with refund information
            $stmt = $conn->prepare("UPDATE orders SET refund_amount = ?, refund_reason = ?, payment_status = 'refunded', updated_at = NOW() WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("dsi", $refund_amount, $refund_reason, $order_id);
                if ($stmt->execute()) {
                    $message = "Refund processed successfully!";
                    
                    // Log refund action
                    $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
                    $action_desc = "Processed refund of ETB $refund_amount for order #$order_id";
                    $log_stmt->bind_param("isss", $_SESSION['admin_id'], $action_desc, $action_desc, $_SERVER['REMOTE_ADDR']);
                    $log_stmt->execute();
                    $log_stmt->close();
                } else {
                    $error = "Failed to process refund: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Get filter parameters
$payment_status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query with filters
$query = "
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           (SELECT SUM(total_price) FROM order_items WHERE order_id = o.id) as items_total
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE 1=1
";

$params = [];
$types = '';

if ($payment_status_filter) {
    $query .= " AND o.payment_status = ?";
    $params[] = $payment_status_filter;
    $types .= 's';
}

if ($date_from) {
    $query .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if ($date_to) {
    $query .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

// Get orders with payment information
$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $orders_result = $stmt->get_result();
    $orders = $orders_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $orders = [];
    $error = "Failed to load orders: " . $conn->error;
}

// Payment statistics
$payment_stats = $conn->query("
    SELECT 
        payment_status,
        COUNT(*) as count,
        SUM(total_amount) as total_amount
    FROM orders 
    GROUP BY payment_status
")->fetch_all(MYSQLI_ASSOC);

// Monthly payment summary
$monthly_summary = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        payment_status,
        COUNT(*) as order_count,
        SUM(total_amount) as total_amount
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), payment_status
    ORDER BY month DESC, payment_status
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Payment Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#paymentReportModal">
                            <i class="bi bi-download me-1"></i>
                            Export Report
                        </button>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Payment Statistics -->
                <div class="row mb-4">
                    <?php foreach ($payment_stats as $stat): ?>
                    <div class="col-xl-2 col-md-4 mb-3">
                        <div class="card border-left-<?= 
                            $stat['payment_status'] === 'paid' ? 'success' : 
                            ($stat['payment_status'] === 'pending' ? 'warning' : 
                            ($stat['payment_status'] === 'failed' ? 'danger' : 'secondary'))
                        ?> shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stat['count'] ?></div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 text-<?= 
                                        $stat['payment_status'] === 'paid' ? 'success' : 
                                        ($stat['payment_status'] === 'pending' ? 'warning' : 
                                        ($stat['payment_status'] === 'failed' ? 'danger' : 'secondary'))
                                    ?>">
                                        <?= ucfirst($stat['payment_status']) ?>
                                    </div>
                                    <small class="text-muted">ETB <?= number_format($stat['total_amount'] ?? 0, 2) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Payment Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?= $payment_status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="paid" <?= $payment_status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="failed" <?= $payment_status_filter === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="refunded" <?= $payment_status_filter === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                    <option value="cancelled" <?= $payment_status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Transactions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-credit-card display-1 text-muted"></i>
                                <p class="text-muted mt-3">No payment transactions found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Payment Status</th>
                                            <th>Order Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= $order['order_number'] ?></td>
                                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                                            <td>ETB <?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= ucfirst($order['payment_method'] ?? 'cash') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $order['payment_status'] === 'paid' ? 'success' : 
                                                    ($order['payment_status'] === 'pending' ? 'warning' : 
                                                    ($order['payment_status'] === 'failed' ? 'danger' : 'secondary'))
                                                ?>">
                                                    <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $order['status'] === 'delivered' ? 'success' : 
                                                    ($order['status'] === 'pending' ? 'warning' : 'secondary')
                                                ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#paymentModal"
                                                            data-order-id="<?= $order['id'] ?>"
                                                            data-current-status="<?= $order['payment_status'] ?? 'pending' ?>"
                                                            data-order-number="<?= $order['order_number'] ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($order['payment_status'] === 'paid' && $order['status'] !== 'cancelled'): ?>
                                                    <button class="btn btn-outline-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#refundModal"
                                                            data-order-id="<?= $order['id'] ?>"
                                                            data-order-number="<?= $order['order_number'] ?>"
                                                            data-order-amount="<?= $order['total_amount'] ?>">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <a href="../?page=admin-orders&id=<?= $order['id'] ?>" class="btn btn-outline-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
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

                <!-- Monthly Summary -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Monthly Payment Summary (Last 6 Months)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Payment Status</th>
                                        <th>Orders</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_summary as $summary): ?>
                                    <tr>
                                        <td><?= date('F Y', strtotime($summary['month'] . '-01')) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $summary['payment_status'] === 'paid' ? 'success' : 
                                                ($summary['payment_status'] === 'pending' ? 'warning' : 'secondary')
                                            ?>">
                                                <?= ucfirst($summary['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $summary['order_count'] ?></td>
                                        <td>ETB <?= number_format($summary['total_amount'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Payment Status Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_payment_status">
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <div class="modal-body">
                        <p><strong>Order #:</strong> <span id="modalOrderNumber"></span></p>
                        <div class="mb-3">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="failed">Failed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Add any payment notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="process_refund">
                    <input type="hidden" name="order_id" id="refundOrderId">
                    <div class="modal-body">
                        <p><strong>Order #:</strong> <span id="refundOrderNumber"></span></p>
                        <p><strong>Order Amount:</strong> ETB <span id="refundOrderAmount"></span></p>
                        <div class="mb-3">
                            <label class="form-label">Refund Amount (ETB)</label>
                            <input type="number" step="0.01" name="refund_amount" class="form-control" id="refundAmount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Refund Reason</label>
                            <textarea name="refund_reason" class="form-control" rows="3" placeholder="Reason for refund..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Process Refund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="paymentReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Payment Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="export_payments.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-select" required>
                                <option value="csv">CSV Format</option>
                                <option value="pdf">PDF Format</option>
                                <option value="excel">Excel Format</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Range</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" name="export_date_from" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="export_date_to" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Include Columns</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="order_number" checked>
                                <label class="form-check-label">Order Number</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="customer" checked>
                                <label class="form-check-label">Customer Information</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="amount" checked>
                                <label class="form-check-label">Amount</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="status" checked>
                                <label class="form-check-label">Payment Status</label>
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
        // Payment modal handler
        const paymentModal = document.getElementById('paymentModal');
        paymentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const orderNumber = button.getAttribute('data-order-number');
            const currentStatus = button.getAttribute('data-current-status');
            
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('modalOrderNumber').textContent = orderNumber;
            document.querySelector('select[name="payment_status"]').value = currentStatus;
        });

        // Refund modal handler
        const refundModal = document.getElementById('refundModal');
        refundModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const orderNumber = button.getAttribute('data-order-number');
            const orderAmount = button.getAttribute('data-order-amount');
            
            document.getElementById('refundOrderId').value = orderId;
            document.getElementById('refundOrderNumber').textContent = orderNumber;
            document.getElementById('refundOrderAmount').textContent = orderAmount;
            document.getElementById('refundAmount').value = orderAmount;
            document.getElementById('refundAmount').max = orderAmount;
        });

        // Validate refund amount
        document.getElementById('refundAmount').addEventListener('input', function() {
            const maxAmount = parseFloat(this.max);
            const currentAmount = parseFloat(this.value);
            
            if (currentAmount > maxAmount) {
                this.value = maxAmount;
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