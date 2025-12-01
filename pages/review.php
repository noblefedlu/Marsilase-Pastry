<?php
// review.php - Shopping Cart Review Page

// Start session with proper configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_httponly' => true,
        'cookie_secure' => false,
        'use_strict_mode' => true
    ]);
}

// Debug: Log cart contents
error_log("Review Page - Session ID: " . session_id());
error_log("Review Page - Cart Contents: " . print_r($_SESSION['cart'] ?? 'EMPTY', true));

// Check if cart is empty
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

// Debug info
$debug_info = "Cart Summary: $total_items items, ETB " . number_format($total_price, 2);
error_log("Review Page - " . $debug_info);
?>

<style>
.cart-item {
    background: white;
    color: var(--text-dark);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid var(--neutral-200);
    transition: all 0.3s ease;
}

.cart-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.cart-item h5 {
    color: var(--text-dark);
}

.text-muted {
    color: var(--text-muted) !important;
}

.btn-outline-secondary {
    border-color: var(--neutral-300);
    color: var(--text-dark);
}

.btn-outline-secondary:hover {
    background: var(--neutral-200);
    border-color: var(--neutral-400);
}

.border-bottom {
    border-bottom-color: var(--neutral-200) !important;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-display {
    min-width: 40px;
    text-align: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.debug-badge {
    position: fixed;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.8rem;
    z-index: 9999;
}
</style>

<!-- Debug Badge - Remove in production -->
<div class="debug-badge">
    🛒 <?= $total_items ?> items
</div>

<div class="section">
    <div class="container-narrow">
        <div class="d-flex align-items-center justify-content-between mb-5">
            <div>
                <h1 class="display-4 display-font mb-2">Shopping Cart</h1>
                <p class="text-muted">Review your items before checkout</p>
                <small class="text-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Cart ID: <?= substr(session_id(), 0, 8) ?>... | 
                    Items: <?= $total_items ?> | 
                    Total: ETB <?= number_format($total_price, 2) ?>
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="?page=home" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Continue Shopping
                </a>
                <a href="debug_cart.php" class="btn btn-outline-secondary">
                    <i class="bi bi-bug me-2"></i>
                    Debug
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Cart Items (<?= $total_items ?>)</h5>
                        <span class="badge bg-primary"><?= count($cart_items) ?> unique items</span>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item border-bottom pb-4 mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-2"><?= htmlspecialchars($item['product_name'] ?? 'Product') ?></h5>
                                    <div class="text-muted small mb-2">
                                        <span class="me-3">
                                            <i class="bi bi-tag me-1"></i>
                                            Type: <?= htmlspecialchars($item['product_type'] ?? 'Cake') ?>
                                        </span>
                                        <span class="me-3">
                                            <i class="bi bi-rulers me-1"></i>
                                            Size: <?= htmlspecialchars($item['size'] ?? 'Standard') ?>
                                        </span>
                                        <span>
                                            <i class="bi bi-palette me-1"></i>
                                            Flavor: <?= htmlspecialchars($item['flavor'] ?? 'Custom') ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($item['special_notes'])): ?>
                                    <div class="text-muted small">
                                        <i class="bi bi-chat-left-text me-1"></i>
                                        <strong>Notes:</strong> <?= htmlspecialchars($item['special_notes']) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            Added: <?= $item['added_at'] ?? 'Unknown' ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="quantity-controls d-flex align-items-center justify-content-end mb-2">
                                        <button class="btn btn-outline-secondary btn-sm" 
                                                onclick="updateCartItem('<?= $item['cart_item_id'] ?? '' ?>', -1)"
                                                title="Decrease Quantity">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <span class="quantity-display mx-3"><?= $item['quantity'] ?? 1 ?></span>
                                        <button class="btn btn-outline-secondary btn-sm" 
                                                onclick="updateCartItem('<?= $item['cart_item_id'] ?? '' ?>', 1)"
                                                title="Increase Quantity">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="fw-bold text-primary fs-5">
                                        ETB <?= number_format($item['total_price'] ?? 0, 2) ?>
                                    </div>
                                    <small class="text-muted">
                                        ETB <?= number_format($item['unit_price'] ?? 0, 2) ?> each
                                    </small>
                                    <div class="mt-2">
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="removeCartItem('<?= $item['cart_item_id'] ?? '' ?>')"
                                                title="Remove Item">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
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
                        
                        <?php if ($total_price > 0): ?>
                        <button class="btn btn-primary w-100 btn-lg" onclick="proceedToCheckout()">
                            <i class="bi bi-credit-card me-2"></i>
                            Proceed to Checkout
                        </button>
                        <?php else: ?>
                        <button class="btn btn-secondary w-100 btn-lg" disabled>
                            <i class="bi bi-cart-x me-2"></i>
                            Cart Empty
                        </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-danger w-100 mt-2" onclick="clearCart()">
                            <i class="bi bi-trash me-2"></i>
                            Clear Cart
                        </button>

                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Free delivery on orders over ETB 500
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Cart Debug Info -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-bug me-2"></i>Debug Info
                        </h6>
                        <div class="small text-muted">
                            <div>Session: <?= substr(session_id(), 0, 8) ?>...</div>
                            <div>Items: <?= $total_items ?></div>
                            <div>Unique: <?= count($cart_items) ?></div>
                            <div>Subtotal: ETB <?= number_format($total_price, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cart management functions
function updateCartItem(cartItemId, change) {
    if (!cartItemId) {
        console.error('No cart item ID provided');
        showToast('❌ Error: Missing item ID', 'error');
        return;
    }

    console.log('Updating cart item:', cartItemId, 'Change:', change);

    const formData = new FormData();
    formData.append('action', 'update_cart');
    formData.append('cart_item_id', cartItemId);
    formData.append('quantity', change);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Update response:', data);
        if (data.success) {
            showToast('✅ Cart updated successfully', 'success');
            // Reload after a short delay to show the toast
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
        showToast('❌ Error updating cart: ' + error.message, 'error');
    });
}

function removeCartItem(cartItemId) {
    if (!cartItemId) {
        console.error('No cart item ID provided');
        return;
    }

    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    console.log('Removing cart item:', cartItemId);

    const formData = new FormData();
    formData.append('action', 'remove_from_cart');
    formData.append('cart_item_id', cartItemId);

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Remove response:', data);
        if (data.success) {
            showToast('✅ Item removed from cart', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        showToast('❌ Error removing item: ' + error.message, 'error');
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart? This action cannot be undone.')) {
        return;
    }

    console.log('Clearing cart...');

    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=clear_cart'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Clear cart response:', data);
        if (data.success) {
            showToast('✅ Cart cleared successfully', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error clearing cart:', error);
        showToast('❌ Error clearing cart: ' + error.message, 'error');
    });
}

function proceedToCheckout() {
    console.log('Proceeding to checkout...');
    // Redirect to customer information page
    window.location.href = '?page=customer-info';
}

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0 position-fixed`;
    toast.style.zIndex = '9999';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.minWidth = '300px';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after hide
    toast.addEventListener('hidden.bs.toast', () => {
        document.body.removeChild(toast);
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Review page loaded');
    console.log('Cart items count:', <?= count($cart_items) ?>);
    console.log('Total items:', <?= $total_items ?>);
    console.log('Total price:', <?= $total_price ?>);
});
</script>