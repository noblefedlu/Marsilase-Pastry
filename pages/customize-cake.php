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
            <p class="text-muted">No cake selected for customization.</p>
            <a href="?page=home" class="btn btn-primary rounded-pill mt-3"><i class="bi bi-arrow-left"></i> Back to Home</a>
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
    <div class="d-flex align-items-center mb-4">
        <a href="?page=home" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0 fw-bold">Customize Your <?= htmlspecialchars($cake['name']) ?> Cake</h2>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-lg border-0 p-4">
                <form id="cakeCustomizationForm">
                    <input type="hidden" name="product_id" value="<?= $cake['id'] ?>">
                    <input type="hidden" name="product_type" value="cake">
                    
                    <!-- Cake Flavor -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-palette"></i> Select Cake Flavor</h5>
                        <div class="d-flex flex-wrap gap-2" id="cakeFlavors">
                            <?php foreach ($cake_flavors as $flavor): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill flavor-btn" 
                                    data-flavor="<?= $flavor ?>" onclick="selectCakeFlavor('<?= $flavor ?>')">
                                <?= $flavor ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Cake Size -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-rulers"></i> Cake Size</h5>
                        <div class="d-flex flex-wrap gap-2" id="cakeSizes">
                            <?php foreach ($cake_sizes as $size): 
                                $sizePrice = $cake['price'] * $size['priceModifier'];
                            ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill size-btn" 
                                    data-size="<?= $size['id'] ?>" onclick="selectCakeSize('<?= $size['id'] ?>')">
                                <?= $size['name'] ?>
                                <span class="badge bg-secondary ms-1">Birr <?= number_format($sizePrice, 2) ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Toppings -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-stars"></i> Add Toppings</h5>
                        <div class="d-flex flex-wrap gap-2" id="cakeToppings">
                            <?php foreach ($toppings as $topping): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill topping-btn" 
                                    data-topping="<?= $topping ?>" onclick="toggleCakeTopping('<?= $topping ?>')">
                                <?= $topping ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-hash"></i> Quantity</h5>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn" onclick="updateCakeQuantity(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="mx-3 fw-bold fs-5" id="cakeQuantity">1</span>
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn" onclick="updateCakeQuantity(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Special Notes -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-chat-left-text"></i> Special Notes</h5>
                        <textarea class="form-control" rows="3" id="cakeSpecialNotes" placeholder="e.g., 'Write Happy Birthday with blue icing'"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="?page=home" class="btn btn-secondary rounded-pill px-4">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="button" class="btn btn-success rounded-pill px-5" onclick="addCakeToCart()">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm rounded-lg border-0 p-4 summary-sticky">
                <h5 class="fw-bold mb-3"><i class="bi bi-journal-text"></i> Order Summary</h5>
                
                <!-- Cake Info -->
                <div class="d-flex align-items-center mb-3">
                    <div class="product-thumb" style="background: <?= $cake['color'] ?>; width: 60px; height: 60px; border-radius: 8px;"></div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($cake['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($cake['description']) ?></small>
                    </div>
                </div>

                <!-- Customization Details -->
                <div class="summary-details">
                    <div class="order-summary-item">
                        <span class="text-muted">Flavor:</span>
                        <span id="summaryFlavor" class="fw-bold text-dark">Vanilla</span>
                    </div>
                    
                    <div class="order-summary-item">
                        <span class="text-muted">Size:</span>
                        <span id="summarySize" class="fw-bold text-dark">Small (0.5kg)</span>
                    </div>
                    
                    <div class="order-summary-item">
                        <span class="text-muted">Toppings:</span>
                        <span id="summaryToppings" class="fw-bold text-dark">None</span>
                    </div>
                    
                    <div class="order-summary-item">
                        <span class="text-muted">Quantity:</span>
                        <span id="summaryQuantity" class="fw-bold text-dark">1</span>
                    </div>

                    <!-- Total Price -->
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Total:</span>
                            <span id="summaryTotal" class="fw-bold fs-4 text-primary">Birr <?= number_format($cake['price'], 2) ?></span>
                        </div>
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
        if (btn.dataset.flavor === flavor) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
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
            } else {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
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
    document.getElementById('summaryToppings').textContent = 
        cakeCustomization.toppings.length ? cakeCustomization.toppings.join(", ") : "None";
    
    // Update quantity
    document.getElementById('summaryQuantity').textContent = cakeCustomization.quantity;
    
    // Calculate and update total price
    const sizeObjPrice = cakeSizes[cakeCustomization.size];
    const sizeMultiplier = sizeObjPrice ? parseFloat(sizeObjPrice.priceModifier) : 1.0;
    const total = cakeBasePrice * sizeMultiplier * cakeCustomization.quantity;
    document.getElementById('summaryTotal').textContent = 'Birr ' + total.toFixed(2);
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
    
    // Send to cart API
    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to home page on success
            window.location.href = '?page=home';
        } else {
            alert('Error adding to cart: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to cart. Please try again.');
    });
}
</script>