
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="section-title">Review Your Order</h1>
                <p class="section-subtitle">Almost there! Review your items before placing your order</p>
            </div>

            <?php if (empty($_SESSION['cart'])): ?>
                <!-- Empty Cart State -->
                <div class="text-center py-5">
                    <div class="empty-cart-icon mb-4">
                        <i class="bi bi-cart-x display-1 text-primary"></i>
                    </div>
                    <h3 class="text-dark mb-3">Your cart is empty</h3>
                    <p class="text-medium mb-4">Looks like you haven't added any delicious items to your cart yet.</p>
                    <a href="?page=home" class="btn btn-primary btn-lg hover-glow">
                        <i class="bi bi-arrow-left me-2"></i>Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <!-- Order Items -->
                    <div class="col-lg-8">
                        <div class="cart-items-card glass-card rounded-4 p-4 hover-glow">
                            <h4 class="mb-4 text-dark">
                                <i class="bi bi-bag-check me-2"></i>
                                Order Items (<?= count($_SESSION['cart']) ?>)
                            </h4>
                            
                            <?php 
                            $total = 0;
                            foreach ($_SESSION['cart'] as $index => $item): 
                                $total += $item['total_price'];
                            ?>
                            <div class="cart-item card border-0 bg-light mb-3 hover-glow">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <div class="product-thumb rounded-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);">
                                                <i class="bi bi-cake2 text-white fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="text-dark mb-1"><?= htmlspecialchars($item['product_name']) ?></h5>
                                            <p class="text-medium mb-1 small">
                                                <span class="badge bg-primary me-1"><?= $item['product_type'] ?></span>
                                                <?php if (!empty($item['size'])): ?>
                                                <span class="badge bg-secondary me-1"><?= $item['size'] ?></span>
                                                <?php endif; ?>
                                                <span class="badge bg-light text-dark"><?= $item['flavor'] ?></span>
                                            </p>
                                            <?php if (!empty($item['special_notes'])): ?>
                                            <p class="text-medium mb-0 small">
                                                <em>"<?= htmlspecialchars($item['special_notes']) ?>"</em>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <div class="quantity-controls d-flex align-items-center justify-content-center">
                                                <span class="fw-bold text-dark">Qty: <?= $item['quantity'] ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="fw-bold text-primary fs-5 me-2">
                                                    ETB <?= number_format($item['total_price'], 2) ?>
                                                </span>
                                                <button class="btn btn-outline-danger btn-sm hover-glow" 
                                                        onclick="removeFromCart(<?= $index ?>)" 
                                                        title="Remove item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="order-summary-card glass-card rounded-4 p-4 hover-glow">
                            <h4 class="mb-4 text-dark">
                                <i class="bi bi-receipt me-2"></i>
                                Order Summary
                            </h4>
                            
                            <div class="summary-details mb-4">
                                <?php foreach ($_SESSION['cart'] as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-medium small"><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></span>
                                    <span class="fw-medium text-dark">ETB <?= number_format($item['total_price'], 2) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <hr>

                            <div class="total-section mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-medium">Subtotal</span>
                                    <span class="fw-bold text-dark">ETB <?= number_format($total, 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-medium">Delivery</span>
                                    <span class="fw-bold text-success">FREE</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-medium">Tax</span>
                                    <span class="fw-bold text-dark">Included</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                    <span class="fs-5 fw-bold text-dark">Total Amount:</span>
                                    <span class="fs-4 fw-bold text-primary">ETB <?= number_format($total, 2) ?></span>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <div class="d-grid gap-2">
                                    <a href="?page=home" class="btn btn-outline-primary hover-glow">
                                        <i class="bi bi-plus-circle me-2"></i>Add More Items
                                    </a>
                                    <a href="?page=customer-info" class="btn btn-primary btn-lg py-3 fw-bold hover-glow">
                                        <i class="bi bi-bag-check me-2"></i>Proceed to Checkout
                                    </a>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <small class="text-medium">
                                        <i class="bi bi-shield-check me-1"></i>Secure checkout · No payment required
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function removeFromCart(index) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'remove_from_cart');
    formData.append('index', index);
    
    showLoader();
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            showToast('Item removed from cart');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('Error removing item: ' + data.message);
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error:', error);
        showToast('Error removing item from cart');
    });
}
</script>

<style>
.cart-items-card, .order-summary-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.cart-items-card:hover, .order-summary-card:hover {
    transform: translateY(-2px);
}

.cart-item {
    transition: transform 0.2s ease;
}

.cart-item:hover {
    transform: translateX(5px);
}

.empty-cart-icon {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
    40% {transform: translateY(-10px);}
    60% {transform: translateY(-5px);}
}

.quantity-controls .btn {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
</style>