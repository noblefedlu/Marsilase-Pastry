
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

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Track Order Header -->
            <div class="text-center mb-5">
                <h2 class="section-title">Track Your Order</h2>
                <p class="section-subtitle">Enter your order number to check the status of your delivery</p>
            </div>

            <!-- Search Form -->
            <div class="search-card glass-card rounded-4 p-4 mb-4 hover-glow">
                <form id="trackOrderForm" method="GET" class="row g-3">
                    <input type="hidden" name="page" value="track-order">
                    
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Order Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="bi bi-receipt"></i>
                            </span>
                            <input type="text" class="form-control hover-glow" name="order_number" 
                                   value="<?= htmlspecialchars($order_number) ?>" 
                                   placeholder="e.g., ORD-20231201-ABC123" required>
                            <button type="submit" class="btn btn-primary hover-glow">
                                <i class="bi bi-search me-2"></i>Track Order
                            </button>
                        </div>
                        <div class="form-text text-medium">
                            You can find your order number in your confirmation email
                        </div>
                    </div>
                </form>
            </div>

            <?php if (!empty($order_number)): ?>
                <?php if ($order): ?>
                    <!-- Order Found -->
                    <div class="order-details">
                        <!-- Order Status Timeline -->
                        <div class="status-card glass-card rounded-4 mb-4 hover-glow">
                            <div class="card-header bg-primary text-white py-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-truck me-2"></i>
                                    Order #<?= $order['order_number'] ?>
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <!-- Status Timeline -->
                                <div class="status-timeline">
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <div class="status-step <?= $order['status'] !== 'cancelled' ? 'active' : '' ?>">
                                                <div class="step-icon">
                                                    <i class="bi bi-cart-check"></i>
                                                </div>
                                                <div class="step-label">Order Placed</div>
                                                <small class="text-medium"><?= date('M j, g:i A', strtotime($order['created_at'])) ?></small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="status-step <?= in_array($order['status'], ['processing', 'delivered']) ? 'active' : '' ?>">
                                                <div class="step-icon">
                                                    <i class="bi bi-gear"></i>
                                                </div>
                                                <div class="step-label">Processing</div>
                                                <small class="text-medium">Preparing your order</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="status-step <?= $order['status'] === 'delivered' ? 'active' : '' ?>">
                                                <div class="step-icon">
                                                    <i class="bi bi-truck"></i>
                                                </div>
                                                <div class="step-label">On the Way</div>
                                                <small class="text-medium">Out for delivery</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="status-step <?= $order['status'] === 'delivered' ? 'active' : '' ?>">
                                                <div class="step-icon">
                                                    <i class="bi bi-check-circle"></i>
                                                </div>
                                                <div class="step-label">Delivered</div>
                                                <small class="text-medium">Order completed</small>
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
                                <div class="info-card glass-card rounded-4 p-4 hover-glow h-100">
                                    <h6 class="fw-bold text-dark mb-3">
                                        <i class="bi bi-person me-2"></i>Customer Details
                                    </h6>
                                    <div class="info-content">
                                        <p class="mb-2">
                                            <strong class="text-dark">Name:</strong><br>
                                            <?= htmlspecialchars($order['customer_name']) ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong class="text-dark">Phone:</strong><br>
                                            <?= htmlspecialchars($order['customer_phone']) ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong class="text-dark">Email:</strong><br>
                                            <?= htmlspecialchars($order['customer_email']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Information -->
                            <div class="col-md-6">
                                <div class="info-card glass-card rounded-4 p-4 hover-glow h-100">
                                    <h6 class="fw-bold text-dark mb-3">
                                        <i class="bi bi-geo-alt me-2"></i>Delivery Details
                                    </h6>
                                    <div class="info-content">
                                        <p class="mb-2">
                                            <strong class="text-dark">Address:</strong><br>
                                            <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong class="text-dark">Scheduled Delivery:</strong><br>
                                            <?= date('F j, Y', strtotime($order['delivery_date'])) ?>
                                        </p>
                                        <?php if (!empty($order['special_instructions'])): ?>
                                        <p class="mb-0">
                                            <strong class="text-dark">Instructions:</strong><br>
                                            <?= nl2br(htmlspecialchars($order['special_instructions'])) ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="col-12">
                                <div class="items-card glass-card rounded-4 p-4 hover-glow">
                                    <h6 class="fw-bold text-dark mb-3">
                                        <i class="bi bi-cart me-2"></i>Order Items
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-dark">Product</th>
                                                    <th class="text-dark">Type</th>
                                                    <th class="text-dark">Flavor</th>
                                                    <th class="text-center text-dark">Qty</th>
                                                    <th class="text-end text-dark">Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_items as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($item['product_name']) ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary text-uppercase">
                                                            <?= htmlspecialchars($item['product_type']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-medium"><?= htmlspecialchars($item['flavor']) ?></td>
                                                    <td class="text-center text-dark"><?= $item['quantity'] ?></td>
                                                    <td class="text-end fw-semibold text-dark">
                                                        ETB <?= number_format($item['total_price'], 2) ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold text-dark">Total:</td>
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
                <?php else: ?>
                    <!-- Order Not Found -->
                    <div class="not-found-card glass-card rounded-4 p-5 text-center hover-glow">
                        <i class="bi bi-search display-1 text-primary mb-3"></i>
                        <h4 class="text-dark mb-3">Order Not Found</h4>
                        <p class="text-medium mb-4">
                            We couldn't find an order with number "<?= htmlspecialchars($order_number) ?>".<br>
                            Please check your order number and try again.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="?page=track-order" class="btn btn-primary hover-glow">Try Again</a>
                            <a href="?page=home" class="btn btn-outline-primary hover-glow">Back to Home</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.search-card, .status-card, .info-card, .items-card, .not-found-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.search-card:hover, .status-card:hover, .info-card:hover, .items-card:hover, .not-found-card:hover {
    transform: translateY(-2px);
}

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
    background: var(--primary-light);
    z-index: 1;
}

.status-step:first-child::before {
    display: none;
}

.status-step.active::before {
    background: var(--primary-medium);
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: var(--text-medium);
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.status-step.active .step-icon {
    background: var(--primary-medium);
    color: var(--primary-light);
    transform: scale(1.1);
}

.step-label {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--text-dark);
}

.status-step.active .step-label {
    color: var(--primary-dark);
}

.info-content p {
    margin-bottom: 1rem;
}

.table th {
    border-bottom: 2px solid var(--primary-medium);
}

.table td {
    border-bottom: 1px solid var(--primary-light);
}
</style>