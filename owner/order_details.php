<?php
session_start();
require_once '../common/connection.php';
requireOwner();
requirePermission('manage_orders');

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    header('Location: reports.php?error=order_id_required');
    exit;
}

// Get order details
$order_query = "SELECT o.* FROM orders o WHERE o.id = ?";
$stmt = $conn->prepare($order_query);

if (!$stmt) {
    die("Error preparing order query: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: reports.php?error=order_not_found');
    exit;
}

// Get order items
$items_query = "SELECT oi.*, 
                COALESCE(p.name, c.name) as product_name
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id AND oi.product_type = 'product'
                LEFT JOIN cakes c ON oi.product_id = c.id AND oi.product_type = 'cake'
                WHERE oi.order_id = ?";
                
$stmt = $conn->prepare($items_query);

if (!$stmt) {
    die("Error preparing items query: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Owner Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --owner-primary: #2c3e50;
            --owner-secondary: #34495e;
        }
        
        .owner-nav { 
            background: var(--owner-primary); 
        }
        
        .status-badge-pending { background: #ffc107; color: #000; }
        .status-badge-confirmed { background: #17a2b8; color: #fff; }
        .status-badge-preparing { background: #fd7e14; color: #fff; }
        .status-badge-ready { background: #20c997; color: #fff; }
        .status-badge-delivered { background: #28a745; color: #fff; }
        .status-badge-cancelled { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg owner-nav">
        <div class="container">
            <span class="navbar-brand text-white">
                <i class="bi bi-cart me-2"></i>Order Details
            </span>
            <div class="navbar-nav ms-auto">
                <a href="reports.php" class="nav-link text-white me-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to Reports
                </a>
                <a href="index.php" class="nav-link text-white me-3">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
                <a href="logout.php" class="nav-link text-white">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Order #<?= $order['order_number'] ?></h2>
            <div class="d-flex align-items-center gap-3">
                <div class="badge status-badge-<?= $order['status'] ?> fs-6">
                    <?= ucfirst($order['status']) ?>
                </div>
                <div class="text-muted">
                    <small>Owner View</small>
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email'] ?? 'Not provided') ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($order['delivery_address'] ?? 'Not provided') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Order Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        <p><strong>Delivery Date:</strong> <?= date('M j, Y', strtotime($order['delivery_date'] ?? $order['created_at'])) ?></p>
                        <p><strong>Delivery Time:</strong> <?= htmlspecialchars($order['delivery_time'] ?? 'Not specified') ?></p>
                        <p><strong>Total Amount:</strong> <span class="fw-bold text-primary">ETB <?= number_format($order['total_amount'], 2) ?></span></p>
                        <p><strong>Payment Method:</strong> <?= ucfirst($order['payment_method'] ?? 'Not specified') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Items (<?= count($order_items) ?>)</h5>
                <span class="badge bg-primary">Total: ETB <?= number_format($order['total_amount'], 2) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($order_items)): ?>
                    <p class="text-muted text-center py-3">No items found in this order.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                foreach ($order_items as $item): 
                                    $subtotal += $item['total_price'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></div>
                                        <?php if (!empty($item['customization'])): ?>
                                        <small class="text-muted d-block">Custom: <?= htmlspecialchars($item['customization']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($item['size'])): ?>
                                        <small class="text-muted d-block">Size: <?= htmlspecialchars($item['size']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($item['flavor'])): ?>
                                        <small class="text-muted d-block">Flavor: <?= htmlspecialchars($item['flavor']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= ucfirst($item['product_type'] ?? 'product') ?></span>
                                    </td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>ETB <?= number_format($item['unit_price'], 2) ?></td>
                                    <td class="fw-bold">ETB <?= number_format($item['total_price'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                    <td class="fw-bold">ETB <?= number_format($subtotal, 2) ?></td>
                                </tr>
                                <?php if (isset($order['delivery_fee']) && $order['delivery_fee'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Delivery Fee:</td>
                                    <td>ETB <?= number_format($order['delivery_fee'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Tax:</td>
                                    <td>ETB <?= number_format($order['tax_amount'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                    <td class="fw-bold">ETB <?= number_format($order['total_amount'], 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Notes -->
        <?php if (!empty($order['special_instructions'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Special Instructions</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="reports.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Reports
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>