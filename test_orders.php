
<?php
include 'config.php';

$order_number = $_GET['order_number'] ?? '';
$order = null;
$order_items = [];

if (!empty($order_number)) {
    // Fetch order details
    $order_query = "SELECT * FROM orders WHERE order_number = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if ($order) {
        // Fetch order items
        $items_query = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $order['id']);
        $stmt->execute();
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<div class="section">
    <div class="container-narrow">
        <!-- Track Order Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 display-font mb-3">Track Your Order</h1>
            <p class="text-lead">Enter your order number to check the status of your delivery</p>
        </div>

        <!-- Search Form -->
        <div class="card mb-5">
            <div class="card-body">
                <form id="trackOrderForm" method="GET" class="row g-3">
                    <input type="hidden" name="page" value="track-order">
                    
                    <div class="col-12">
                        <label class="form-label fw-semibold">Order Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="bi bi-receipt"></i>
                            </span>
                            <input type="text" class="form-control" name="order_number" 
                                   value="<?= htmlspecialchars($order_number) ?>" 
                                   placeholder="e.g., ORD-20231201-ABC123" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Track Order
                            </button>
                        </div>
                        <div class="form-text">
                            You can find your order number in your confirmation email
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($order_number)): ?>
            <?php if ($order): ?>
                <!-- Order Found -->
                <div class="order-details">
                    <!-- Order Status Timeline -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-truck me-2"></i>
                                Order #<?= $order['order_number'] ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Status Timeline -->
                            <div class="status-timeline">
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="status-step active">
                                            <div class="step-icon">
                                                <i class="bi bi-cart-check"></i>
                                            </div>
                                            <div class="step-label">Order Placed</div>
                                            <small class="text-muted"><?= date('M j, g:i A', strtotime($order['created_at'])) ?></small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="status-step <?= in_array($order['status'], ['processing', 'delivered']) ? 'active' : '' ?>">
                                            <div class="step-icon">
                                                <i class="bi bi-gear"></i>
                                            </div>
                                            <div class="step-label">Processing</div>
                                            <small class="text-muted">Preparing your order</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="status-step <?= $order['status'] === 'delivered' ? 'active' : '' ?>">
                                            <div class="step-icon">
                                                <i class="bi bi-truck"></i>
                                            </div>
                                            <div class="step-label">On the Way</div>
                                            <small class="text-muted">Out for delivery</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="status-step <?= $order['status'] === 'delivered' ? 'active' : '' ?>">
                                            <div class="step-icon">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                            <div class="step-label">Delivered</div>
                                            <small class="text-muted">Order completed</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Status -->
                            <div class="alert alert-<?= 
                                $order['status'] === 'delivered' ? 'success' : 
                                ($order['status'] === 'cancelled' ? 'danger' : 'info')
                            ?> mt-4 mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-<?= 
                                        $order['status'] === 'delivered' ? 'check-circle' : 
                                        ($order['status'] === 'cancelled' ? 'x-circle' : 'info-circle')
                                    ?> me-2 fs-4"></i>
                                    <div>
                                        <strong>Current Status: <?= ucfirst($order['status']) ?></strong>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <br>Your order is being processed and will be delivered on <?= date('F j, Y', strtotime($order['delivery_date'])) ?>
                                        <?php elseif ($order['status'] === 'delivered'): ?>
                                            <br>Your order was successfully delivered
                                        <?php elseif ($order['status'] === 'cancelled'): ?>
                                            <br>This order has been cancelled
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="row g-4">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-person me-2"></i>Customer Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>Name:</strong><br>
                                        <?= htmlspecialchars($order['customer_name']) ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Phone:</strong><br>
                                        <?= htmlspecialchars($order['customer_phone']) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Email:</strong><br>
                                        <?= htmlspecialchars($order['customer_email']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-geo-alt me-2"></i>Delivery Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>Address:</strong><br>
                                        <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Scheduled Delivery:</strong><br>
                                        <?= date('F j, Y', strtotime($order['delivery_date'])) ?>
                                    </p>
                                    <?php if (!empty($order['special_instructions'])): ?>
                                    <p class="mb-0">
                                        <strong>Instructions:</strong><br>
                                        <?= nl2br(htmlspecialchars($order['special_instructions'])) ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
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
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-end">Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_items as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary text-uppercase">
                                                            <?= htmlspecialchars($item['product_type']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($item['flavor']) ?></td>
                                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                                    <td class="text-end fw-semibold">
                                                        ETB <?= number_format($item['total_price'], 2) ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                                    <td class="text-end fw-bold text-primary">
                                                        ETB <?= number_format($order['total_amount'], 2) ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Order Not Found -->
                <div class="card text-center">
                    <div class="card-body py-5">
                        <i class="bi bi-search display-1 text-primary mb-3"></i>
                        <h4 class="mb-3">Order Not Found</h4>
                        <p class="text-muted mb-4">
                            We couldn't find an order with number "<?= htmlspecialchars($order_number) ?>".<br>
                            Please check your order number and try again.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="?page=track-order" class="btn btn-primary">Try Again</a>
                            <a href="?page=home" class="btn btn-outline-primary">Back to Home</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.status-step {
    position: relative;
    padding: 1rem 0;
}

.status-step::before {
    content: '';
    position: absolute;
    top: 40px;
    left: -50%;
    width: 100%;
    height: 3px;
    background: var(--primary-400);
    z-index: 1;
}

.status-step:first-child::before {
    display: none;
}

.status-step.active::before {
    background: var(--primary-500); /* Honey Gold */
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-400);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
    position: relative;
    z-index: 2;
    transition: var(--transition-base);
}

.status-step.active .step-icon {
    background: var(--primary-500); /* Honey Gold */
    color: white;
    transform: scale(1.1);
}

.step-label {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: white;
}

.status-step.active .step-label {
    color: var(--primary-500); /* Honey Gold */
}

.table th {
    border-bottom: 2px solid var(--primary-500);
    font-weight: 600;
    background: var(--primary-400);
    color: white;
}

.table td {
    border-bottom: 1px solid var(--primary-400);
    padding: 1rem 0.75rem;
    background: var(--primary-300);
    color: white;
}

.table-hover tbody tr:hover {
    background-color: var(--primary-400);
}

.card {
    background: var(--primary-300);
    color: white;
}

.card-header {
    background: var(--primary-400);
    color: white;
    border-bottom: 1px solid var(--primary-500);
}

.badge {
    background: var(--primary-500) !important;
}

.alert-info {
    background: rgba(244, 180, 0, 0.2);
    border-color: var(--primary-500);
    color: white;
}

.alert-success {
    background: rgba(160, 90, 44, 0.2);
    border-color: var(--primary-300);
    color: white;
}

.alert-danger {
    background: rgba(139, 77, 37, 0.2);
    border-color: var(--primary-400);
    color: white;
}
</style>