<?php
// admin_get_order_details.php
session_start();
require_once 'config.php';

$order_id = $_GET['order_id'] ?? 0;

// Get order details
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$order_items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id")->fetch_all(MYSQLI_ASSOC);

if ($order):
?>
    <div class="row">
        <div class="col-md-6">
            <h6>Customer Information</h6>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
        </div>
        <div class="col-md-6">
            <h6>Delivery Information</h6>
            <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
            <p><strong>Date:</strong> <?= date('F j, Y', strtotime($order['delivery_date'])) ?></p>
            <p><strong>Time:</strong> <?= $order['delivery_time'] ?></p>
        </div>
    </div>

    <hr>

    <h6>Order Items</h6>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Product</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= htmlspecialchars($item['product_type']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>ETB <?= number_format($item['unit_price'], 2) ?></td>
                <td>ETB <?= number_format($item['total_price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td><strong>ETB <?= number_format($order['total_amount'], 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($order['special_instructions'])): ?>
    <div class="mt-3">
        <h6>Special Instructions</h6>
        <p><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></p>
    </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-warning">Order not found.</div>
<?php endif; ?>

<?php $conn->close(); ?>