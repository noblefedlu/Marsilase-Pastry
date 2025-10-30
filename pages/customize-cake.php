<?php
$cake_id = $_GET['cake_id'] ?? '';
$cake = null;

// Find the selected cake
foreach ($cakes as $c) {
    if ($c['id'] == $cake_id) {
        $cake = $c;
        break;
    }
}

if (!$cake) {
    echo '<div class="container my-5 text-center">
            <div class="card border-0 shadow-card-lg hover-glow">
                <div class="card-body py-5">
                    <i class="bi bi-cake display-1 text-muted mb-3"></i>
                    <p class="text-muted">No cake selected for customization.</p>
                    <a href="?page=home" class="btn btn-primary rounded-pill mt-3 hover-glow"><i class="bi bi-arrow-left"></i> Back to Home</a>
                </div>
            </div>
          </div>';
    return;
}

// Prepare cake sizes array for JavaScript
$cake_sizes_js = [];
foreach ($cake_sizes as $size) {
    $cake_sizes_js[$size['id']] = $size;
}
?>

<div class="container my-5">
    <div class="d-flex align-items-center mb-4 fade-in-up">
        <a href="?page=home" class="btn btn-outline-primary me-3 hover-glow"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0 fw-bold text-gradient display-font">Customize Your <?= htmlspecialchars($cake['name']) ?> Cake</h2>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-card-lg rounded-lg border-0 p-4 fade-in-up hover-glow">
                <form id="cakeCustomizationForm">
                    <input type="hidden" name="product_id" value="<?= $cake['id'] ?>">
                    <input type="hidden" name="product_type" value="cake">
                    
                    <!-- Cake Preview -->
                    <div class="mb-4 text-center slide-up">
                        <div class="product-image mx-auto hover-glow" style="width: 320px; height: 220px; background: <?= $cake['color'] ?? '#d4af37' ?>; border-radius: var(--radius); overflow: hidden; position: relative;">
                            <?php if (!empty($cake['image_url'])): ?>
                                <img src="<?= $cake['image_url'] ?>" alt="<?= htmlspecialchars($cake['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-cake2 text-white" style="font-size: 4rem;"></i>
                            <?php endif; ?>
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-primary fs-6">Customizable</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cake Flavor -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 text-primary display-font"><i class="bi bi-palette me-2"></i> Select Cake Flavor</h5>
                        <div class="d-flex flex-wrap gap-2" id="cakeFlavors">
                            <?php foreach ($cake_flavors as $flavor): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill flavor-btn hover-glow px-4 py-2" 
                                    data-flavor="<?= $flavor ?>" onclick="selectCakeFlavor('<?= $flavor ?>')">
                                <i class="bi bi-check-circle me-2 d-none"></i>
                                <?= $flavor ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Cake Size -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 text-primary display-font"><i class="bi bi-rulers me-2"></i> Cake Size</h5>
                        <div class="d-flex flex-wrap gap-2" id="cakeSizes">
                            <?php foreach ($cake_sizes as $size): 
                                $sizePrice = $cake['price'] * $size['priceModifier'];
                            ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill size-btn hover-glow px-4 py-3" 
                                    data-size="<?= $size['id'] ?>" onclick="selectCakeSize('<?= $size['id'] ?>')">
                                <div class="text-start">
                                    <div class="fw-bold"><?= $size['name'] ?></div>
                                    <small class="text-muted">+ Birr <?= number_format($sizePrice - $cake['price'], 2) ?></small>
                                </div>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Toppings -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 text-primary display-font"><i class="bi bi-stars me-2"></i> Add Toppings</h5>
                        <p class="text-muted mb-3">Select your favorite toppings (included in base price)</p>
                        <div class="d-flex flex-wrap gap-2" id="cakeToppings">
                            <?php foreach ($toppings as $topping): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill topping-btn hover-glow px-3 py-2" 
                                    data-topping="<?= $topping ?>" onclick="toggleCakeTopping('<?= $topping ?>')">
                                <i class="bi bi-plus-circle me-2"></i>
                                <?= $topping ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 text-primary display-font"><i class="bi bi-hash me-2"></i> Quantity</h5>
                        <div class="d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn hover-glow d-flex align-items-center justify-content-center" 
                                    style="width: 50px; height: 50px;" onclick="updateCakeQuantity(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="mx-4 fw-bold fs-3" id="cakeQuantity" style="min-width: 60px; text-align: center;">1</span>
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn hover-glow d-flex align-items-center justify-content-center" 
                                    style="width: 50px; height: 50px;" onclick="updateCakeQuantity(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Special Notes -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 text-primary display-font"><i class="bi bi-chat-left-text me-2"></i> Special Notes</h5>
                        <textarea class="form-control hover-glow" rows="4" id="cakeSpecialNotes" 
                                  placeholder="e.g., &#10;• Write 'Happy Birthday' with blue icing&#10;• Add extra chocolate decorations&#10;• Special dietary requirements"></textarea>
                        <div class="form-text">Let us know any special requests or instructions</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-3 mt-4 slide-up">
                        <a href="?page=home" class="btn btn-outline-primary rounded-pill px-4 py-2 hover-glow">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="button" class="btn btn-success rounded-pill px-5 py-2 fw-bold hover-glow" onclick="addCakeToCart()">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart - <span id="addToCartPrice">Birr <?= number_format($cake['price'], 2) ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-card-lg rounded-lg border-0 p-4 summary-sticky fade-in-up hover-glow">
                <h5 class="fw-bold mb-3 text-primary display-font"><i class="bi bi-journal-text me-2"></i> Order Summary</h5>
                
                <!-- Cake Info -->
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="product-thumb hover-glow" style="background: <?= $cake['color'] ?>; width: 70px; height: 70px; border-radius: var(--radius-sm); overflow: hidden;">
                        <?php if (!empty($cake['image_url'])): ?>
                            <img src="<?= $cake['image_url'] ?>" alt="<?= htmlspecialchars($cake['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-cake2 text-dark d-flex align-items-center justify-content-center h-100" style="font-size: 1.8rem;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold display-font"><?= htmlspecialchars($cake['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($cake['description']) ?></small>
                    </div>
                </div>

                <!-- Customization Details -->
                <div class="summary-details">
                    <div class="order-summary-item d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Flavor:</span>
                        <span id="summaryFlavor" class="fw-bold text-dark">Vanilla</span>
                    </div>
                    
                    <div class="order-summary-item d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Size:</span>
                        <span id="summarySize" class="fw-bold text-dark">Small (0.5kg)</span>
                    </div>
                    
                    <div class="order-summary-item d-flex justify-content-between align-items-start mb-3">
                        <span class="text-muted">Toppings:</span>
                        <span id="summaryToppings" class="fw-bold text-dark text-end">None</span>
                    </div>
                    
                    <div class="order-summary-item d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Quantity:</span>
                        <span id="summaryQuantity" class="fw-bold text-dark">1</span>
                    </div>

                    <!-- Total Price -->
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Total:</span>
                            <span id="summaryTotal" class="fw-bold fs-4 text-primary">Birr <?= number_format($cake['price'], 2) ?></span>
                        </div>
                        <small class="text-muted">Inclusive of all customization</small>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-clock text-primary me-2"></i>
                        <small class="text-muted">Ready in 2-4 hours</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-truck text-primary me-2"></i>
                        <small class="text-muted">Free delivery over Birr 500</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        <small class="text-muted">Quality guaranteed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cake sizes data from PHP
const cakeSizes = <?= json_encode($cake_sizes_js) ?>;
const cakeBasePrice = <?= $cake['price'] ?>;

// Cake customization object
let cakeCustomization = {
    flavor: 'Vanilla',
    size: 'small',
    toppings: [],
    quantity: 1,
    specialNotes: ''
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Select first flavor by default
    selectCakeFlavor('<?= $cake_flavors[0] ?? 'Vanilla' ?>');
    
    // Select small size by default
    selectCakeSize('small');
    
    // Initialize special notes listener
    document.getElementById('cakeSpecialNotes').addEventListener('input', function() {
        cakeCustomization.specialNotes = this.value;
    });
});

// Select cake flavor
function selectCakeFlavor(flavor) {
    cakeCustomization.flavor = flavor;
    
    // Update button states
    document.querySelectorAll('#cakeFlavors .flavor-btn').forEach(btn => {
        const icon = btn.querySelector('.bi-check-circle');
        if (btn.dataset.flavor === flavor) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
            icon.classList.remove('d-none');
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
            icon.classList.add('d-none');
        }
    });
    
    updateSummary();
}

// Toggle cake topping
function toggleCakeTopping(topping) {
    const index = cakeCustomization.toppings.indexOf(topping);
    
    if (index > -1) {
        // Remove topping
        cakeCustomization.toppings.splice(index, 1);
    } else {
        // Add topping
        cakeCustomization.toppings.push(topping);
    }
    
    // Update button states
    document.querySelectorAll('#cakeToppings .topping-btn').forEach(btn => {
        if (btn.dataset.topping === topping) {
            if (cakeCustomization.toppings.includes(topping)) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                btn.innerHTML = `<i class="bi bi-check-circle me-2"></i>${topping}`;
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
                btn.innerHTML = `<i class="bi bi-plus-circle me-2"></i>${topping}`;
            }
        }
    });
    
    updateSummary();
}

// Select cake size
function selectCakeSize(size) {
    cakeCustomization.size = size;
    
    // Update button states
    document.querySelectorAll('#cakeSizes .size-btn').forEach(btn => {
        if (btn.dataset.size === size) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
        }
    });
    
    updateSummary();
}

// Update quantity
function updateCakeQuantity(amount) {
    const newQuantity = cakeCustomization.quantity + amount;
    
    // Limit quantity between 1 and 10
    if (newQuantity >= 1 && newQuantity <= 10) {
        cakeCustomization.quantity = newQuantity;
        document.getElementById('cakeQuantity').textContent = newQuantity;
        updateSummary();
    }
}

// Update order summary
function updateSummary() {
    // Update flavor
    document.getElementById('summaryFlavor').textContent = cakeCustomization.flavor;
    
    // Update size
    const sizeObj = cakeSizes[cakeCustomization.size];
    document.getElementById('summarySize').textContent = sizeObj ? sizeObj.name : 'Small (0.5kg)';
    
    // Update toppings
    const toppingsText = cakeCustomization.toppings.length ? 
        cakeCustomization.toppings.join(", ") : "None";
    document.getElementById('summaryToppings').textContent = toppingsText;
    
    // Update quantity
    document.getElementById('summaryQuantity').textContent = cakeCustomization.quantity;
    
    // Calculate and update total price
    const sizeObjPrice = cakeSizes[cakeCustomization.size];
    const sizeMultiplier = sizeObjPrice ? parseFloat(sizeObjPrice.priceModifier) : 1.0;
    const total = cakeBasePrice * sizeMultiplier * cakeCustomization.quantity;
    document.getElementById('summaryTotal').textContent = 'Birr ' + total.toFixed(2);
    document.getElementById('addToCartPrice').textContent = 'Birr ' + total.toFixed(2);
}

// Add cake to cart
function addCakeToCart() {
    const sizeObj = cakeSizes[cakeCustomization.size];
    const sizeMultiplier = sizeObj ? parseFloat(sizeObj.priceModifier) : 1.0;
    const unitPrice = cakeBasePrice * sizeMultiplier;
    const totalPrice = unitPrice * cakeCustomization.quantity;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_type', 'cake');
    formData.append('product_id', '<?= $cake['id'] ?>');
    formData.append('product_name', '<?= $cake['name'] ?>');
    formData.append('flavor', cakeCustomization.flavor);
    formData.append('size', cakeCustomization.size);
    formData.append('size_label', sizeObj ? sizeObj.name : 'Small (0.5kg)');
    formData.append('toppings', JSON.stringify(cakeCustomization.toppings));
    formData.append('quantity', cakeCustomization.quantity);
    formData.append('special_notes', cakeCustomization.specialNotes);
    formData.append('unit_price', unitPrice);
    formData.append('total_price', totalPrice);
    
    showLoader();
    
    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            showToast('🎉 <?= htmlspecialchars($cake['name']) ?> added to cart!');
            setTimeout(() => {
                window.location.href = '?page=home';
            }, 1500);
        } else {
            showToast('❌ Error adding to cart: ' + data.message);
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error:', error);
        showToast('❌ Error adding to cart. Please try again.');
    });
}
</script>