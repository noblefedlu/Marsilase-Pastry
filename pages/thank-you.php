<?php
$order_id = $_GET['order_id'] ?? '';
?>
<div class="container my-5 text-center">
    <div class="card shadow-sm rounded-lg p-5">
        <h2 class="fw-bold text-success mb-4">🎉 Thank You for Your Order!</h2>

        <?php if ($order_id): ?>
            <p class="fs-5">
                Your order has been placed successfully.<br />
                <strong>Order ID:</strong> #<?= $order_id ?>
            </p>
        <?php else: ?>
            <p class="fs-5">Your order has been placed successfully.</p>
        <?php endif; ?>

        <p class="text-muted mt-3">We'll contact you soon with delivery details.</p>

        <a href="?page=home" class="btn btn-primary rounded-pill px-4 mt-4">
            Back to Home
        </a>
    </div>
</div>