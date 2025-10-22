<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die('<div class="alert alert-danger">Unauthorized access</div>');
}

$order_id = $_GET['id'] ?? 0;

$order_query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<div class="order-details">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-primary mb-0">
            <i class="bi bi-receipt me-2"></i>Order #<?= $order['order_number'] ?>
        </h5>
        <span class="badge bg-<?= 
            $order['status'] === 'pending' ? 'warning' : 
            ($order['status'] === 'delivered' ? 'success' : 'danger') 
        ?> fs-6">
            <?= ucfirst($order['status']) ?>
        </span>
    </div>
    
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-cart me-2"></i>Order Items
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Flavor</th>
                            <th>Size</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= $item['product_name'] ?></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">
                                    <?= $item['product_type'] ?>
                                </span>
                            </td>
                            <td><?= $item['flavor'] ?></td>
                            <td>
                                <?php if ($item['size'] && $item['size'] !== 'N/A'): ?>
                                    <span class="badge bg-light text-dark"><?= $item['size'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill"><?= $item['quantity'] ?></span>
                            </td>
                            <td class="text-end fw-semibold text-primary">
                                Birr <?= number_format($item['total_price'], 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <div class="row justify-content-between align-items-center">
                <div class="col-md-6">
                    <div class="text-muted small">
                        <div><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <h4 class="text-primary mb-0">
                        Total: Birr <?= number_format($order['total_amount'], 2) ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>
</div>