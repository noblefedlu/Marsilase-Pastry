[file name]: customize-cake.php
[file content begin]
<?php
$cake_id = $_GET['cake_id'] ?? '';
$cake = null;

// Define cake sizes
$cake_sizes = [
    [
        'id' => 1,
        'name' => 'Small (0.5kg)',
        'price' => 50.00,
        'serves' => '2-4'
    ],
    [
        'id' => 2,
        'name' => 'Medium (1kg)',
        'price' => 100.00,
        'serves' => '4-6'
    ],
    [
        'id' => 3,
        'name' => 'Large (2kg)',
        'price' => 180.00,
        'serves' => '8-12'
    ]
];

// Find the selected cake
foreach ($cakes as $c) {
    if ($c['id'] == $cake_id) {
        $cake = $c;
        break;
    }
}

if (!$cake) {
    echo '<div class="section">
        <div class="container-narrow text-center">
            <div class="card">
                <div class="card-body py-5">
                    <i class="bi bi-cake display-1 text-muted mb-3"></i>
                    <h3 class="mb-3">Cake Not Found</h3>
                    <p class="text-muted mb-4">The selected cake could not be found.</p>
                    <a href="?page=home" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>';
    return;
}
?>

<div class="section">
    <div class="container-narrow">
        <div class="d-flex align-items-center mb-5">
            <a href="?page=home" class="btn btn-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="display-4 display-font mb-2">Customize Your <?= htmlspecialchars($cake['name']) ?></h1>
                <p class="text-muted">Create your perfect cake with our customization options</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form id="cakeCustomizationForm">
                            <input type="hidden" name="product_id" value="<?= $cake['id'] ?>">
                            <input type="hidden" name="product_type" value="cake">
                            
                            <!-- Cake Preview -->
                            <div class="text-center mb-5">
                                <div class="bg-light rounded-3 p-4 mb-3" style="max-width: 400px; margin: 0 auto;">
                                    <img src="<?= $cake['image_url'] ?: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80' ?>" 
                                         alt="<?= htmlspecialchars($cake['name']) ?>" 
                                         class="img-fluid rounded-2"
                                         style="max-height: 200px;">
                                </div>
                                <h4><?= htmlspecialchars($cake['name']) ?></h4>
                                <p class="text-muted"><?= htmlspecialchars($cake['description']) ?></p>
                            </div>

                            <!-- Cake Size -->
                            <div class="mb-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-rulers me-2 text-primary"></i>
                                    Cake Size
                                </h5>
                                <div class="row g-3" id="cakeSizes">
                                    <?php 
                                    $first_size = true;
                                    if (!empty($cake_sizes)) {
                                        foreach ($cake_sizes as $size): 
                                    ?>
                                    <div class="col-md-4">
                                        <div class="card size-option <?= $first_size ? 'border-primary border-2' : '' ?>" 
                                             data-size="<?= $size['id'] ?? 1 ?>" 
                                             data-price="<?= $size['price'] ?? 0 ?>"
                                             data-name="<?= htmlspecialchars($size['name'] ?? 'Small') ?>">
                                            <div class="card-body text-center">
                                                <h6 class="mb-2"><?= $size['name'] ?? 'Small' ?></h6>
                                                <p class="text-muted small mb-2">Serves <?= $size['serves'] ?? '2-4' ?></p>
                                                <div class="fw-bold text-primary">
                                                    + ETB <?= number_format($size['price'] ?? 0, 2) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                        $first_size = false;
                                        endforeach; 
                                    } else {
                                        echo '<div class="col-12 text-center text-muted">No sizes available</div>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="mb-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-hash me-2 text-primary"></i>
                                    Quantity
                                </h5>
                                <div class="d-flex align-items-center gap-3" style="max-width: 200px;">
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(-1)">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <span class="fw-bold fs-4" id="quantityDisplay">1</span>
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(1)">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Special Notes -->
                            <div class="mb-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-chat-left-text me-2 text-primary"></i>
                                    Special Notes
                                </h5>
                                <textarea class="form-control" name="special_notes" rows="4" 
                                          placeholder="Any special requests, dietary requirements, or customization details..."></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-3">
                                <a href="?page=home" class="btn btn-secondary">Cancel</a>
                                <button type="button" class="btn btn-primary flex-fill" onclick="addToCart()" id="addToCartBtn">
                                    <i class="bi bi-cart-plus me-2"></i>
                                    Add to Cart - ETB <span id="totalPrice"><?= number_format($cake['price'] + ($cake_sizes[0]['price'] ?? 0), 2) ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary rounded-2 d-flex align-items-center justify-content-center text-white me-3" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-cake2"></i>
                            </div>
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($cake['name']) ?></h6>
                                <small class="text-muted">Base price: ETB <?= number_format($cake['price'], 2) ?></small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Size:</span>
                                <span id="summarySize" class="fw-semibold"><?= $cake_sizes[0]['name'] ?? 'Small' ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Size Price:</span>
                                <span id="summarySizePrice" class="fw-semibold">+ ETB <?= number_format($cake_sizes[0]['price'] ?? 0, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Quantity:</span>
                                <span id="summaryQuantity" class="fw-semibold">1</span>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold">Total:</span>
                            <span id="summaryTotal" class="fs-4 fw-bold text-primary">
                                ETB <?= number_format($cake['price'] + ($cake_sizes[0]['price'] ?? 0), 2) ?>
                            </span>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex align-items-center text-muted small mb-2">
                                <i class="bi bi-clock me-2"></i>
                                <span>Ready in 2-4 hours</span>
                            </div>
                            <div class="d-flex align-items-center text-muted small mb-2">
                                <i class="bi bi-truck me-2"></i>
                                <span>Free delivery over ETB 500</span>
                            </div>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-shield-check me-2"></i>
                                <span>Quality guaranteed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentQuantity = 1;
let currentSizeId = '<?= $cake_sizes[0]['id'] ?? 1 ?>';
let currentSizePrice = <?= $cake_sizes[0]['price'] ?? 0 ?>;
let currentSizeName = '<?= $cake_sizes[0]['name'] ?? 'Small' ?>';
let basePrice = <?= $cake['price'] ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize size selection only if sizes exist
    const sizeOptions = document.querySelectorAll('.size-option');
    if (sizeOptions.length > 0) {
        sizeOptions.forEach(option => {
            option.addEventListener('click', function() {
                selectSize(
                    this.dataset.size, 
                    parseFloat(this.dataset.price || 0),
                    this.dataset.name || 'Small'
                );
            });
        });

        // Select first size by default
        selectSize(
            sizeOptions[0].dataset.size || '1',
            parseFloat(sizeOptions[0].dataset.price || 0),
            sizeOptions[0].dataset.name || 'Small'
        );
    }
});

function selectSize(sizeId, sizePrice, sizeName) {
    currentSizeId = sizeId;
    currentSizePrice = sizePrice;
    currentSizeName = sizeName;
    
    // Update UI
    document.querySelectorAll('.size-option').forEach(option => {
        if (option.dataset.size === sizeId) {
            option.classList.add('border-primary', 'border-2');
        } else {
            option.classList.remove('border-primary', 'border-2');
        }
    });

    updateSummary();
}

function updateQuantity(change) {
    const newQuantity = currentQuantity + change;
    if (newQuantity >= 1 && newQuantity <= 10) {
        currentQuantity = newQuantity;
        document.getElementById('quantityDisplay').textContent = newQuantity;
        updateSummary();
    }
}

function updateSummary() {
    const total = (basePrice + currentSizePrice) * currentQuantity;

    // Update summary display
    document.getElementById('summarySize').textContent = currentSizeName;
    document.getElementById('summarySizePrice').textContent = '+ ETB ' + currentSizePrice.toFixed(2);
    document.getElementById('summaryQuantity').textContent = currentQuantity;
    document.getElementById('summaryTotal').textContent = 'ETB ' + total.toFixed(2);
    document.getElementById('totalPrice').textContent = total.toFixed(2);
}

function addToCart() {
    const unitPrice = basePrice + currentSizePrice;
    const totalPrice = unitPrice * currentQuantity;
    const specialNotes = document.querySelector('textarea[name="special_notes"]').value;

    // Validate required fields
    if (!currentSizeId || currentQuantity < 1) {
        showToast('❌ Please select a size and quantity', 'error');
        return;
    }

    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_type', 'cake');
    formData.append('product_id', '<?= $cake['id'] ?>');
    formData.append('product_name', '<?= $cake['name'] ?>');
    formData.append('flavor', 'Custom');
    formData.append('size', currentSizeName);
    formData.append('quantity', currentQuantity);
    formData.append('special_notes', specialNotes);
    formData.append('unit_price', unitPrice);
    formData.append('total_price', totalPrice);

    const addToCartBtn = document.getElementById('addToCartBtn');
    const originalText = addToCartBtn.innerHTML;
    
    // Show loading state
    addToCartBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Adding...';
    addToCartBtn.disabled = true;

    // Send request to cart.php
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
        console.log('Cart response:', data);
        
        if (data.success) {
            showToast('🎉 <?= htmlspecialchars($cake['name']) ?> added to cart!', 'success');
            
            // Update cart count in navigation if exists
            if (typeof updateCartCount === 'function') {
                updateCartCount(data.cart_count);
            }
            
            // Test if cart is actually saved by checking immediately
            return fetch('cart.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'get_cart' })
            });
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .then(response => response.json())
    .then(cartData => {
        console.log('Cart verification:', cartData);
        
        // Redirect to cart page after a short delay
        setTimeout(() => {
            window.location.href = '?page=review';
        }, 1500);
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showToast('❌ Error adding to cart: ' + error.message, 'error');
        
        // Reset button
        addToCartBtn.innerHTML = originalText;
        addToCartBtn.disabled = false;
    });
}

// Add this helper function if not exists
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0 position-fixed`;
    toast.style.zIndex = '9999';
    toast.style.top = '20px';
    toast.style.right = '20px';
    
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
</script>

<style>
.size-option {
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
    color: var(--text-dark);
    border: 1px solid var(--neutral-200);
}

.size-option:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.size-option.border-primary {
    border-color: var(--primary-100) !important;
    background: rgba(95, 55, 43, 0.1);
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}

/* Update card styles in customize page */
.card {
    background: white;
    color: var(--text-dark);
}

.card-header {
    background: white;
    border-bottom: 1px solid var(--neutral-200);
    color: var(--text-dark);
}

.form-control, .form-select {
    background: white;
    color: var(--text-dark);
    border: 1px solid var(--neutral-300);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-100);
    box-shadow: 0 0 0 0.2rem rgba(95, 55, 43, 0.1);
}

.btn-outline-secondary {
    border-color: var(--neutral-400);
    color: var(--text-dark);
}

.btn-outline-secondary:hover {
    background: var(--neutral-200);
    border-color: var(--neutral-500);
}

.text-primary {
    color: var(--primary-100) !important;
}

.text-muted {
    color: var(--text-muted) !important;
}

.bg-light {
    background: var(--neutral-100) !important;
    color: var(--text-dark) !important;
}

.bg-primary {
    background: var(--primary-100) !important;
}
</style>