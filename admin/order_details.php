<?php
// admin/order-details.php
require_once 'config.php';
requireAdminAuth();

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    header('Location: orders.php');
    exit;
}

// Get order details
$order_query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $admin_notes = sanitizeInput($_POST['admin_notes'] ?? '');
    
    $update_stmt = $conn->prepare("UPDATE orders SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("ssi", $new_status, $admin_notes, $order_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Order status updated successfully!";
        logAdminAction('Order Status Update', "Updated order #{$order['order_number']} to $new_status");
        header("Location: order-details.php?id=$order_id");
        exit;
    }
    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .order-header {
            background: linear-gradient(135deg, #f56e10, #e7540a);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <!-- Order Header -->
        <div class="order-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="fw-bold mb-2">Order #<?= $order['order_number'] ?></h2>
                    <p class="mb-0 opacity-75">
                        Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge status-badge bg-<?= 
                        $order['status'] === 'delivered' ? 'success' : 
                        ($order['status'] === 'pending' ? 'warning' : 
                        ($order['status'] === 'cancelled' ? 'danger' : 'info'))
                    ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                    <div class="mt-2">
                        <a href="orders.php" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Orders
                        </a>
                        <button class="btn btn-light btn-sm" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Order Information -->
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Flavor</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                            <?php if ($item['special_notes']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($item['special_notes']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark text-uppercase">
                                                <?= $item['product_type'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($item['flavor'] ?? 'Standard') ?></td>
                                        <td class="text-center"><?= $item['quantity'] ?></td>
                                        <td class="text-end">ETB <?= number_format($item['unit_price'], 2) ?></td>
                                        <td class="text-end fw-bold">ETB <?= number_format($item['total_price'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                        <td class="text-end fw-bold">ETB <?= number_format($order['total_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Total Amount:</td>
                                        <td class="text-end fw-bold text-primary fs-5">
                                            ETB <?= number_format($order['total_amount'], 2) ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Status Update -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Update Order Status</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Order Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                        <option value="ready" <?= $order['status'] === 'ready' ? 'selected' : '' ?>>Ready</option>
                                        <option value="out_for_delivery" <?= $order['status'] === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Admin Notes</label>
                                    <textarea name="admin_notes" class="form-control" rows="3" 
                                              placeholder="Add internal notes about this order..."><?= htmlspecialchars($order['admin_notes'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="update_status" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>Update Order Status
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Customer Information -->
                <div class="info-card">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-person me-2"></i>Customer Information
                    </h6>
                    <p class="mb-2">
                        <strong>Name:</strong><br>
                        <?= htmlspecialchars($order['customer_name']) ?>
                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>">
                            <?= htmlspecialchars($order['customer_email']) ?>
                        </a>
                    </p>
                    <p class="mb-2">
                        <strong>Phone:</strong><br>
                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>">
                            <?= htmlspecialchars($order['customer_phone']) ?>
                        </a>
                    </p>
                </div>

                <!-- Delivery Information -->
                <div class="info-card">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-truck me-2"></i>Delivery Information
                    </h6>
                    <?php if ($order['delivery_address']): ?>
                    <p class="mb-2">
                        <strong>Address:</strong><br>
                        <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($order['delivery_date']): ?>
                    <p class="mb-2">
                        <strong>Delivery Date:</strong><br>
                        <?= date('F j, Y', strtotime($order['delivery_date'])) ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($order['delivery_time']): ?>
                    <p class="mb-2">
                        <strong>Delivery Time:</strong><br>
                        <?= htmlspecialchars($order['delivery_time']) ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($order['special_instructions']): ?>
                    <p class="mb-0">
                        <strong>Special Instructions:</strong><br>
                        <?= nl2br(htmlspecialchars($order['special_instructions'])) ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Order Summary -->
                <div class="info-card">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-receipt me-2"></i>Order Summary
                    </h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order Number:</span>
                        <strong>#<?= $order['order_number'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order Date:</span>
                        <strong><?= date('M j, Y', strtotime($order['created_at'])) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Status:</span>
                        <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                            <?= ucfirst($order['payment_status']) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items:</span>
                        <strong><?= count($order_items) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total Amount:</span>
                        <strong class="text-primary fs-5">ETB <?= number_format($order['total_amount'], 2) ?></strong>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="info-card">
                    <h6 class="fw-semibold mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>?subject=Order%20Update%20-%20<?= $order['order_number'] ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-envelope me-2"></i>Email Customer
                        </a>
                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" 
                           class="btn btn-outline-info btn-sm">
                            <i class="bi bi-telephone me-2"></i>Call Customer
                        </a>
                        <a href="order-print.php?id=<?= $order_id ?>" 
                           class="btn btn-outline-secondary btn-sm" target="_blank">
                            <i class="bi bi-printer me-2"></i>Print Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>