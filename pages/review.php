
<div class="container my-5">
    <div class="d-flex align-items-center mb-4">
        <a href="?page=home" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0 fw-bold">Review Your Order</h2>
    </div>

    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
        <span id="errorMessage"></span>
        <button type="button" class="btn-close" onclick="hideError()"></button>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm rounded-lg border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="bi bi-journal-text"></i> Order Summary</h5>
                    <div id="cartItems">
                        <?php if (empty($_SESSION['cart'])): ?>
                        <div class="text-center py-5">
                            <p class="text-muted">Your cart is empty.</p>
                            <a href="?page=home" class="btn btn-link mt-2">Start Shopping</a>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush mb-4">
                            <?php 
                            $total = 0;
                            foreach ($_SESSION['cart'] as $index => $item): 
                                $total += $item['total_price'];
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex flex-column align-items-start w-75">
                                    <span class="fw-bold"><?= htmlspecialchars($item['product_name']) ?> (<?= htmlspecialchars($item['product_type']) ?>)</span>
                                    <span class="text-muted small">
                                        <?php if ($item['product_type'] === 'cake'): ?>
                                        Size: <?= htmlspecialchars($item['size_label']) ?>, 
                                        <?php endif; ?>
                                        Flavor: <?= htmlspecialchars($item['flavor']) ?>, Qty: <?= $item['quantity'] ?>
                                    </span>
                                    <?php if (!empty($item['toppings'])): 
                                        $toppings = json_decode($item['toppings'], true);
                                        if (is_array($toppings) && !empty($toppings)): ?>
                                    <span class="text-muted small">Toppings: <?= htmlspecialchars(implode(", ", $toppings)) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($item['special_notes'])): ?>
                                    <span class="text-muted small">Note: "<?= htmlspecialchars($item['special_notes']) ?>"</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex align-items-center">
                                    <span class="text-primary fw-bold me-2">Birr <?= number_format($item['total_price'], 2) ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" 
                                            onclick="removeFromCart(<?= $index ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="d-flex justify-content-between align-items-center fw-bold text-primary mt-4 border-top pt-3">
                            <span>Total:</span>
                            <span>Birr <?= number_format($total, 2) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-5">
        <a href="?page=home" class="btn btn-secondary rounded-pill px-4 me-3">Continue Shopping</a>

        <button type="button" class="btn btn-success rounded-pill px-5" id="submitOrderBtn" 
                onclick="submitOrder()" <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>
            <span id="submitText">Place Order</span>
            <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
        </button>
    </div>
</div>

<script>
function removeFromCart(index) {
    const formData = new FormData();
    formData.append('action', 'remove_from_cart');
    formData.append('index', index);
    
    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showError('Error removing item: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error removing item');
    });
}

function submitOrder() {
    const submitBtn = document.getElementById('submitOrderBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    submitBtn.disabled = true;
    submitText.textContent = 'Processing...';
    submitSpinner.classList.remove('d-none');

    // Create form data with required fields (using default values since we removed the form)
    const formData = new FormData();
    formData.append('action', 'submit_order');
    formData.append('name', 'Customer');
    formData.append('phone', '0000000000');
    formData.append('address', 'Delivery Address');
    formData.append('date', new Date().toISOString().split('T')[0]);
    formData.append('instructions', '');
    
    fetch('api/orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '?page=thank-you&order_id=' + data.order_id;
        } else {
            showError(data.message || 'Order submission failed');
            submitBtn.disabled = false;
            submitText.textContent = 'Place Order';
            submitSpinner.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('There was an error submitting your order. Please try again.');
        submitBtn.disabled = false;
        submitText.textContent = 'Place Order';
        submitSpinner.classList.add('d-none');
    });
}

function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorAlert').classList.remove('d-none');
}

function hideError() {
    document.getElementById('errorAlert').classList.add('d-none');
}
</script>