<?php
// pages/review.php
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo '
    <div class="section">
        <div class="container-narrow text-center">
            <div class="card">
                <div class="card-body py-5">
                    <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
                    <h3 class="mb-3">Your Cart is Empty</h3>
                    <p class="text-muted mb-4">Add some delicious items to your cart first!</p>
                    <a href="?page=home" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>';
    return;
}

$cart_items = $_SESSION['cart'];
$total_price = 0;
$total_items = 0;

foreach ($cart_items as $item) {
    $total_price += $item['total_price'];
    $total_items += $item['quantity'];
}
?>

<div class="section">
    <div class="container-narrow">
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <h1 class="display-4 display-font mb-2">Shopping Cart</h1>
                <p class="text-muted">Review your items before checkout</p>
            </div>
            <a href="?page=home" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>
                Continue Shopping
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item border-bottom pb-4 mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-2"><?= htmlspecialchars($item['product_name'] ?? 'Product') ?></h5>
                                    <div class="text-muted small mb-2">
                                        <span class="me-3">Size: <?= htmlspecialchars($item['size'] ?? 'Standard') ?></span>
                                        <span>Flavor: <?= htmlspecialchars($item['flavor'] ?? 'Custom') ?></span>
                                    </div>
                                    <?php if (!empty($item['special_notes'])): ?>
                                    <div class="text-muted small">
                                        <strong>Notes:</strong> <?= htmlspecialchars($item['special_notes']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end mb-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="updateCartItem('<?= $item['cart_item_id'] ?? '' ?>', -1)">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <span class="mx-3 fw-bold"><?= $item['quantity'] ?? 1 ?></span>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="updateCartItem('<?= $item['cart_item_id'] ?? '' ?>', 1)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="fw-bold text-primary fs-5">
                                        ETB <?= number_format($item['total_price'] ?? 0, 2) ?>
                                    </div>
                                    <small class="text-muted">ETB <?= number_format($item['unit_price'] ?? 0, 2) ?> each</small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Items (<?= $total_items ?>):</span>
                            <span class="fw-bold">ETB <?= number_format($total_price, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Delivery:</span>
                            <span class="fw-bold"><?= $total_price > 500 ? 'FREE' : 'ETB 50.00' ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fs-5 fw-bold">Total:</span>
                            <span class="fs-4 fw-bold text-primary">
                                ETB <?= number_format($total_price > 500 ? $total_price : $total_price + 50, 2) ?>
                            </span>
                        </div>
                        <button class="btn btn-primary w-100 btn-lg" onclick="proceedToCheckout()">
                            <i class="bi bi-credit-card me-2"></i>
                            Proceed to Checkout
                        </button>
                        <button class="btn btn-outline-danger w-100 mt-2" onclick="clearCart()">
                            <i class="bi bi-trash me-2"></i>
                            Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateCartItem(cartItemId, change) {
    if (!cartItemId) {
        console.error('No cart item ID provided');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_cart');
    formData.append('cart_item_id', cartItemId);
    formData.append('quantity', change);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating cart: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating cart');
    });
}

function clearCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        fetch('cart.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'clear_cart' })
        })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error clearing cart');
    });
    }
}

function proceedToCheckout() {
    // FIXED: Redirect to customer information page instead of checkout
    window.location.href = '?page=customer-info';
}
</script>