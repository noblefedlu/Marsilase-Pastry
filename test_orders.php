<?php
$order_number = $_GET['order_number'] ?? '';
$order = null;
$order_items = [];

if (!empty($order_number)) {
    // Fetch order details
    $order_query = "SELECT * FROM orders WHERE order_number = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
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

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Track Order Header -->
            <div class="text-center mb-5 fade-in-up">
                <h2 class="fw-bold text-primary mb-3">Track Your Order</h2>
                <p class="text-muted">Enter your order number to check the status of your delivery</p>
            </div>

            <!-- Search Form -->
            <div class="card shadow-card border-0 mb-4 fade-in-up">
                <div class="card-body p-4">
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
                                    <i class="bi bi-search me-2"></i>Track
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
                    <div class="fade-in-up">
                        <!-- Order Status Timeline -->
                        <div class="card shadow-card border-0 mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-truck me-2"></i>
                                    Order #<?= $order['order_number'] ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Status Timeline -->
                                <div class="row text-center mb-4">
                                    <div class="col-3">
                                        <div class="status-step <?= $order['status'] !== 'cancelled' ? 'active' : '' ?>">
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

                                <!-- Current Status -->
                                <div class="alert alert-<?= 
                                    $order['status'] === 'delivered' ? 'success' : 
                                    ($order['status'] === 'cancelled' ? 'danger' : 'info')
                                ?> mb-0">
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
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-semibold">
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
                                            <?= $order['customer_phone'] ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Email:</strong><br>
                                            <?= $order['customer_email'] ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Information -->
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-semibold">
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
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
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
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-end">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order_items as $item): ?>
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
                                                        <td class="text-center"><?= $item['quantity'] ?></td>
                                                        <td class="text-end fw-semibold">
                                                            Birr <?= number_format($item['total_price'], 2) ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="4" class="text-end fw-bold">Total:</td>
                                                        <td class="text-end fw-bold text-primary">
                                                            Birr <?= number_format($order['total_amount'], 2) ?>
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
                    <div class="card border-0 shadow-card fade-in-up">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-search display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Order Not Found</h4>
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
    background: #dee2e6;
    z-index: 1;
}

.status-step:first-child::before {
    display: none;
}

.status-step.active::before {
    background: var(--primary);
}

.step-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: #6c757d;
    position: relative;
    z-index: 2;
}

.status-step.active .step-icon {
    background: var(--primary);
    color: white;
}

.step-label {
    font-weight: 600;
    margin-bottom: 0.25rem;
}
</style>