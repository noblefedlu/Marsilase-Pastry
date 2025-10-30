<div class="container my-5">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 fade-in-up">
        <a href="?page=home" class="btn btn-outline-primary me-3 hover-glow">
            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
        </a>
        <div>
            <h2 class="mb-1 fw-bold text-gradient display-font">Review Your Order</h2>
            <p class="text-muted mb-0">Almost there! Review your items before placing your order.</p>
        </div>
    </div>

    <!-- Error Alert -->
    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show d-none fade-in-up" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <span id="errorMessage" class="fw-medium"></span>
        </div>
        <button type="button" class="btn-close" onclick="hideError()"></button>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <!-- Empty Cart State -->
        <div class="card border-0 shadow-card-lg fade-in-up hover-glow">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-cart-x display-1 text-gradient"></i>
                </div>
                <h3 class="text-gradient fw-bold mb-3 display-font">Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added any delicious items to your cart yet.</p>
                <a href="?page=home" class="btn btn-primary btn-lg px-4 hover-glow">
                    <i class="bi bi-arrow-left me-2"></i>Start Shopping
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Order Items -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-card-lg fade-in-up hover-glow">
                    <div class="card-header bg-transparent border-bottom-0 py-4">
                        <h5 class="card-title mb-0 fw-bold text-primary display-font">
                            <i class="bi bi-bag-check me-2"></i>Order Items (<?= count($_SESSION['cart']) ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php 
                        $total = 0;
                        foreach ($_SESSION['cart'] as $index => $item): 
                            $total += $item['total_price'];
                        ?>
                        <div class="cart-item p-4 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-3 d-flex align-items-center justify-content-center hover-glow" 
                                                 style="width: 80px; height: 80px; background: <?= $item['product_type'] === 'cake' ? '#d4af37' : ($item['product_type'] === 'ice_cream' ? '#8b4513' : ($item['product_type'] === 'soft_drink' ? '#d4af37' : '#8b4513')) ?>;">
                                                <i class="bi bi-<?= $item['product_type'] === 'cake' ? 'cake2' : ($item['product_type'] === 'ice_cream' ? 'ice-cream' : ($item['product_type'] === 'soft_drink' ? 'cup-straw' : 'cup-hot')) ?> text-dark fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="fw-bold mb-1 display-font"><?= htmlspecialchars($item['product_name']) ?></h6>
                                            <p class="text-muted small mb-1">
                                                <span class="badge bg-light text-dark me-2 text-uppercase"><?= str_replace('_', ' ', $item['product_type']) ?></span>
                                                <?php if ($item['product_type'] === 'cake' && !empty($item['size_label'])): ?>
                                                <span class="badge bg-primary me-2"><?= htmlspecialchars($item['size_label']) ?></span>
                                                <?php endif; ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($item['flavor']) ?></span>
                                            </p>
                                            <?php if (!empty($item['toppings'])): 
                                                $toppings = json_decode($item['toppings'], true);
                                                if (is_array($toppings) && !empty($toppings)): ?>
                                            <p class="text-muted small mb-1">
                                                <strong>Toppings:</strong> <?= htmlspecialchars(implode(", ", $toppings)) ?>
                                            </p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (!empty($item['special_notes'])): ?>
                                            <p class="text-muted small mb-0">
                                                <strong>Note:</strong> "<?= htmlspecialchars($item['special_notes']) ?>"
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="text-end">
                                            <div class="fw-bold text-primary fs-4 mb-1">
                                                Birr <?= number_format($item['total_price'], 2) ?>
                                            </div>
                                            <div class="text-muted small">
                                                Qty: <?= $item['quantity'] ?> × Birr <?= number_format($item['unit_price'], 2) ?>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm ms-3 hover-glow" 
                                                onclick="removeFromCart(<?= $index ?>)" 
                                                title="Remove item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-card-lg fade-in-up hover-glow">
                    <div class="card-header bg-transparent border-bottom-0 py-4">
                        <h5 class="card-title mb-0 fw-bold text-primary display-font">
                            <i class="bi bi-receipt me-2"></i>Order Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted small"><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></span>
                                <span class="fw-medium">Birr <?= number_format($item['total_price'], 2) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold">Birr <?= number_format($total, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Delivery</span>
                                <span class="fw-bold text-success">FREE</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Tax</span>
                                <span class="fw-bold">Included</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <span class="fw-bold fs-5">Total Amount:</span>
                                <span class="fw-bold fs-4 text-primary">Birr <?= number_format($total, 2) ?></span>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-grid gap-2">
                                <a href="?page=home" class="btn btn-outline-primary hover-glow">
                                    <i class="bi bi-plus-circle me-2"></i>Add More Items
                                </a>
                                <a href="?page=customer-info" class="btn btn-success btn-lg py-3 fw-bold hover-glow">
                                    <i class="bi bi-bag-check me-2"></i>Proceed to Checkout
                                </a>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>Secure checkout · No payment required
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromCart(index) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'remove_from_cart');
    formData.append('index', index);
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Item removed from cart');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showError('Error removing item: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error removing item from cart');
    });
}

function showError(message) {
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = message;
    errorAlert.classList.remove('d-none');
    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideError() {
    document.getElementById('errorAlert').classList.add('d-none');
}
</script>