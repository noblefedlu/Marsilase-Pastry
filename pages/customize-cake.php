[file name]: pages/customize-cake.php
[file content begin]
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
            <div class="card border-0 shadow-card-lg hover-glow glass">
                <div class="card-body py-5">
                    <i class="bi bi-cake display-1 text-muted mb-3"></i>
                    <p class="text-muted">No cake selected for customization.</p>
                    <a href="?page=home" class="btn btn-primary rounded-pill mt-3 hover-glow glass"><i class="bi bi-arrow-left"></i> Back to Home</a>
                </div>
            </div>
          </div>';
    return;
}

// Prepare cake sizes array for JavaScript with proper fallbacks
$cake_sizes_js = [];
foreach ($cake_sizes as $size) {
    $cake_sizes_js[$size['id']] = [
        'name' => $size['name'] ?? 'Small',
        'priceModifier' => $size['priceModifier'] ?? 1.0,
        'serves' => $size['serves'] ?? '2-4'
    ];
}
?>

<div class="container my-5">
    <div class="d-flex align-items-center mb-4 fade-in-up">
        <a href="?page=home" class="btn btn-outline-primary me-3 hover-glow glass">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="mb-0 fw-bold text-gradient display-font">Customize Your <?= htmlspecialchars($cake['name']) ?></h2>
            <p class="text-muted mb-0">Create your perfect cake with our customization options</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-card-lg rounded-lg border-0 p-4 fade-in-up hover-glow glass">
                <form id="cakeCustomizationForm">
                    <input type="hidden" name="product_id" value="<?= $cake['id'] ?>">
                    <input type="hidden" name="product_type" value="cake">
                    
                    <!-- Cake Preview -->
                    <div class="mb-4 text-center slide-up">
                        <div class="cake-preview-container">
                            <div class="cake-preview glass" id="cakePreview" style="background: <?= $cake['color'] ?? '#C2865A' ?>;">
                                <?php if (!empty($cake['image_url'])): ?>
                                    <img src="<?= $cake['image_url'] ?>" alt="<?= htmlspecialchars($cake['name']) ?>">
                                <?php else: ?>
                                    <i class="bi bi-cake2 text-white"></i>
                                <?php endif; ?>
                            </div>
                            <div class="preview-sparkles">
                                <div class="sparkle"></div>
                                <div class="sparkle"></div>
                                <div class="sparkle"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Cake Flavor -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 display-font"><i class="bi bi-palette me-2" style="color: #4A2E2B;"></i> Select Cake Flavor</h5>
                        <div class="flavor-grid" id="cakeFlavors">
                            <?php foreach ($cake_flavors as $flavor): ?>
                            <div class="flavor-option glass" data-flavor="<?= $flavor ?>">
                                <div class="flavor-icon">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <span><?= $flavor ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Cake Size -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 display-font"><i class="bi bi-rulers me-2" style="color: #4A2E2B;"></i> Cake Size</h5>
                        <div class="size-grid" id="cakeSizes">
                            <?php foreach ($cake_sizes as $size): 
                                $sizePrice = $cake['price'] * ($size['priceModifier'] ?? 1.0);
                                $sizeName = $size['name'] ?? 'Small';
                                $serves = $size['serves'] ?? '2-4';
                            ?>
                            <div class="size-option glass" data-size="<?= $size['id'] ?>">
                                <div class="size-info">
                                    <div class="size-name"><?= $sizeName ?></div>
                                    <div class="size-serves">Serves <?= $serves ?></div>
                                    <div class="size-price" style="color: #4A2E2B;">+ Birr <?= number_format($sizePrice - $cake['price'], 2) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Toppings -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 display-font"><i class="bi bi-stars me-2" style="color: #4A2E2B;"></i> Add Toppings</h5>
                        <p class="text-muted mb-3">Select your favorite toppings (most are included in base price)</p>
                        <div class="toppings-grid" id="cakeToppings">
                            <?php foreach ($toppings as $topping): ?>
                            <div class="topping-option glass" data-topping="<?= $topping ?>">
                                <div class="topping-icon">
                                    <i class="bi bi-plus"></i>
                                </div>
                                <span><?= $topping ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 display-font"><i class="bi bi-hash me-2" style="color: #4A2E2B;"></i> Quantity</h5>
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn glass" onclick="updateCakeQuantity(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="quantity-display" id="cakeQuantity">1</span>
                            <button type="button" class="quantity-btn glass" onclick="updateCakeQuantity(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Special Notes -->
                    <div class="mb-4 slide-up">
                        <h5 class="fw-bold mb-3 display-font"><i class="bi bi-chat-left-text me-2" style="color: #4A2E2B;"></i> Special Notes</h5>
                        <textarea class="form-control hover-glow glass" rows="4" id="cakeSpecialNotes" 
                                  placeholder="e.g., &#10;• Write &#39;Happy Birthday&#39; with blue icing&#10;• Add extra chocolate decorations&#10;• Special dietary requirements"></textarea>
                        <div class="form-text">Let us know any special requests or instructions</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-3 mt-4 slide-up">
                        <a href="?page=home" class="btn btn-outline-primary rounded-pill px-4 py-2 hover-glow glass">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="button" class="btn btn-success rounded-pill px-5 py-2 fw-bold hover-glow glass" onclick="addCakeToCart()" id="addToCartBtn">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart - <span id="addToCartPrice">Birr <?= number_format($cake['price'], 2) ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-card-lg rounded-lg border-0 p-4 summary-sticky fade-in-up hover-glow glass">
                <h5 class="fw-bold mb-3 display-font"><i class="bi bi-journal-text me-2" style="color: #4A2E2B;"></i> Order Summary</h5>
                
                <!-- Cake Info -->
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="product-thumb hover-glow glass" style="background: <?= $cake['color'] ?? '#C2865A' ?>; width: 70px; height: 70px; border-radius: 12px; overflow: hidden;">
                        <?php if (!empty($cake['image_url'])): ?>
                            <img src="<?= $cake['image_url'] ?>" alt="<?= htmlspecialchars($cake['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-cake2 text-white d-flex align-items-center justify-content-center h-100" style="font-size: 1.8rem;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold display-font"><?= htmlspecialchars($cake['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($cake['description'] ?? 'Delicious custom cake') ?></small>
                    </div>
                </div>

                <!-- Customization Details -->
                <div class="summary-details">
                    <div class="order-summary-item d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Flavor:</span>
                        <span id="summaryFlavor" class="fw-bold text-dark"><?= $cake_flavors[0] ?? 'Vanilla' ?></span>
                    </div>
                    
                    <div class="order-summary-item d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Size:</span>
                        <span id="summarySize" class="fw-bold text-dark"><?= $cake_sizes[0]['name'] ?? 'Small' ?></span>
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
                            <span id="summaryTotal" class="fw-bold fs-4" style="color: #4A2E2B;">Birr <?= number_format($cake['price'], 2) ?></span>
                        </div>
                        <small class="text-muted">Inclusive of all customization</small>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="mt-4 p-3 rounded glass">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-clock me-2" style="color: #4A2E2B;"></i>
                        <small class="text-muted">Ready in 2-4 hours</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-truck me-2" style="color: #4A2E2B;"></i>
                        <small class="text-muted">Free delivery over Birr 500</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-check me-2" style="color: #4A2E2B;"></i>
                        <small class="text-muted">Quality guaranteed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cake-preview-container {
    position: relative;
    width: 320px;
    height: 220px;
    margin: 0 auto;
}

.cake-preview {
    width: 100%;
    height: 100%;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.4s ease;
    backdrop-filter: blur(10px);
    animation: cakeGlow 3s ease-in-out infinite alternate;
}

.cake-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-sparkles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.preview-sparkles .sparkle {
    position: absolute;
    width: 20px;
    height: 20px;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>') no-repeat center;
    animation: sparkle 3s infinite;
    opacity: 0;
}

.preview-sparkles .sparkle:nth-child(1) { top: 20%; left: 25%; animation-delay: 0s; }
.preview-sparkles .sparkle:nth-child(2) { top: 60%; right: 30%; animation-delay: 1s; }
.preview-sparkles .sparkle:nth-child(3) { bottom: 30%; left: 40%; animation-delay: 2s; }

@keyframes cakeGlow {
    from {
        box-shadow: 0 0 20px rgba(194, 134, 90, 0.3);
    }
    to {
        box-shadow: 0 0 40px rgba(194, 134, 90, 0.6);
    }
}

@keyframes sparkle {
    0%, 100% { opacity: 0; transform: scale(0); }
    50% { opacity: 1; transform: scale(1); }
}

.flavor-grid, .size-grid, .toppings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.flavor-option, .size-option, .topping-option {
    padding: 1rem;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(194, 134, 90, 0.2);
}

.flavor-option:hover, .size-option:hover, .topping-option:hover {
    transform: translateY(-5px);
    border-color: #C2865A;
    box-shadow: 0 5px 15px rgba(194, 134, 90, 0.2);
}

.flavor-option.selected, .size-option.selected, .topping-option.selected {
    border-color: #C2865A;
    background: rgba(194, 134, 90, 0.1);
}

.flavor-icon, .topping-icon {
    width: 40px;
    height: 40px;
    background: #C2865A;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    color: white;
    transition: all 0.3s ease;
}

.flavor-option.selected .flavor-icon, .topping-option.selected .topping-icon {
    background: #4A2E2B;
}

.flavor-icon i, .topping-icon i {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.flavor-option.selected .flavor-icon i, .topping-option.selected .topping-icon i {
    opacity: 1;
}

.size-info {
    text-align: center;
}

.size-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.size-serves {
    font-size: 0.9rem;
    color: #6B3E2C;
    margin-bottom: 0.25rem;
}

.size-price {
    font-weight: 600;
}

.quantity-selector {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.quantity-btn {
    width: 50px;
    height: 50px;
    border: 2px solid #C2865A;
    background: rgba(248, 233, 210, 0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #C2865A;
    transition: all 0.3s ease;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.quantity-btn:hover {
    background: #C2865A;
    color: white;
    transform: scale(1.1);
}

.quantity-display {
    font-size: 1.5rem;
    font-weight: 600;
    min-width: 60px;
    text-align: center;
}

.summary-sticky {
    position: sticky;
    top: 100px;
    backdrop-filter: blur(10px);
}

@media (max-width: 768px) {
    .cake-preview-container {
        width: 100%;
        max-width: 280px;
    }
    
    .flavor-grid, .size-grid, .toppings-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
}

.spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Cake sizes data from PHP
const cakeSizes = <?= json_encode($cake_sizes_js) ?>;
const cakeBasePrice = <?= $cake['price'] ?>;

// Cake customization object
let cakeCustomization = {
    flavor: '<?= $cake_flavors[0] ?? 'Vanilla' ?>',
    size: '<?= $cake_sizes[0]['id'] ?? 'small' ?>',
    toppings: [],
    quantity: 1,
    specialNotes: ''
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Select first flavor by default
    selectCakeFlavor('<?= $cake_flavors[0] ?? 'Vanilla' ?>');
    
    // Select first size by default
    selectCakeSize('<?= $cake_sizes[0]['id'] ?? 'small' ?>');
    
    // Initialize special notes listener
    document.getElementById('cakeSpecialNotes').addEventListener('input', function() {
        cakeCustomization.specialNotes = this.value;
    });
    
    // Add click events to flavor options
    document.querySelectorAll('.flavor-option').forEach(option => {
        option.addEventListener('click', function() {
            selectCakeFlavor(this.dataset.flavor);
        });
    });
    
    // Add click events to size options
    document.querySelectorAll('.size-option').forEach(option => {
        option.addEventListener('click', function() {
            selectCakeSize(this.dataset.size);
        });
    });
    
    // Add click events to topping options
    document.querySelectorAll('.topping-option').forEach(option => {
        option.addEventListener('click', function() {
            toggleCakeTopping(this.dataset.topping);
        });
    });
});

// Select cake flavor
function selectCakeFlavor(flavor) {
    cakeCustomization.flavor = flavor;
    
    // Update button states
    document.querySelectorAll('.flavor-option').forEach(option => {
        if (option.dataset.flavor === flavor) {
            option.classList.add('selected');
        } else {
            option.classList.remove('selected');
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
    document.querySelectorAll('.topping-option').forEach(option => {
        if (option.dataset.topping === topping) {
            option.classList.toggle('selected');
        }
    });
    
    updateSummary();
}

// Select cake size
function selectCakeSize(size) {
    cakeCustomization.size = size;
    
    // Update button states
    document.querySelectorAll('.size-option').forEach(option => {
        if (option.dataset.size === size) {
            option.classList.add('selected');
        } else {
            option.classList.remove('selected');
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
    document.getElementById('summarySize').textContent = sizeObj ? sizeObj.name : 'Small';
    
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
    formData.append('size_label', sizeObj ? sizeObj.name : 'Small');
    formData.append('toppings', JSON.stringify(cakeCustomization.toppings));
    formData.append('quantity', cakeCustomization.quantity);
    formData.append('special_notes', cakeCustomization.specialNotes);
    formData.append('unit_price', unitPrice);
    formData.append('total_price', totalPrice);
    
    // Disable button and show loading
    const addToCartBtn = document.getElementById('addToCartBtn');
    const originalText = addToCartBtn.innerHTML;
    addToCartBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Adding...';
    addToCartBtn.disabled = true;
    
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
            
            // Redirect to review page after a short delay
            setTimeout(() => {
                window.location.href = '?page=review';
            }, 1500);
        } else {
            showToast('❌ Error: ' + data.message);
            // Re-enable button
            addToCartBtn.innerHTML = originalText;
            addToCartBtn.disabled = false;
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error:', error);
        showToast('❌ Error adding to cart. Please try again.');
        // Re-enable button
        addToCartBtn.innerHTML = originalText;
        addToCartBtn.disabled = false;
    });
}
</script>
[file content end]