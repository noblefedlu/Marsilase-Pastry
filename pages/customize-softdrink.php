<?php
$drink_id = $_GET['drink_id'] ?? '';
$soft_drink = null;

foreach ($soft_drinks as $drink) {
    if ($drink['id'] == $drink_id) {
        $soft_drink = $drink;
        break;
    }
}

if (!$soft_drink) {
    echo '<div class="container my-5 text-center">
            <div class="card shadow-sm rounded-lg border-0 p-5">
                <i class="bi bi-cup-straw display-1 text-muted mb-3"></i>
                <h4 class="text-muted">No soft drink selected</h4>
                <p class="text-muted mb-4">Please select a soft drink to customize</p>
                <a href="?page=home" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>Back to Home
                </a>
            </div>
          </div>';
    return;
}
?>

<div class="container my-5">
    <div class="d-flex align-items-center mb-4">
        <a href="?page=home" class="btn btn-outline-secondary rounded-circle me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="mb-0 fw-bold">Customize Your <?= $soft_drink['name'] ?></h2>
            <p class="text-muted mb-0">Mix your perfect refreshing drink</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-lg border-0 p-4">
                <form id="softDrinkCustomizationForm">
                    <input type="hidden" name="product_id" value="<?= $soft_drink['id'] ?>">
                    <input type="hidden" name="product_type" value="soft_drink">
                    
                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-palette"></i>Select Flavor</h5>
                        <div class="d-flex flex-wrap gap-2" id="softDrinkFlavors">
                            <?php foreach ($soft_drink_flavors as $flavor): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill flavor-btn" 
                                    data-flavor="<?= $flavor ?>" onclick="selectSoftDrinkFlavor('<?= $flavor ?>')">
                                <?= $flavor ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-plus-circle"></i>Add Extras</h5>
                        <div class="d-flex flex-wrap gap-2" id="softDrinkExtras">
                            <?php foreach (['Ice Cubes', 'Lemon Slice', 'Mint Leaves'] as $extra): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill extra-btn" 
                                    data-extra="<?= $extra ?>" onclick="toggleSoftDrinkExtra('<?= $extra ?>')">
                                <?= $extra ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-cart"></i>Quantity</h5>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn" onclick="updateSoftDrinkQuantity(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="mx-3 fw-bold fs-5 quantity-display" id="softDrinkQuantity">1</span>
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn" onclick="updateSoftDrinkQuantity(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-chat-left-text"></i>Special Notes</h5>
                        <textarea class="form-control" rows="3" id="softDrinkSpecialNotes" 
                                  placeholder="e.g., 'Extra cold', 'No straw', etc."></textarea>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4 pt-3 border-top">
                        <a href="?page=home" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="button" class="btn btn-success rounded-pill px-5 fw-bold" onclick="addSoftDrinkToCart()">
                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm rounded-lg border-0 p-4 summary-sticky">
                <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="product-thumb" style="background: <?= $soft_drink['color'] ?>"></div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold"><?= $soft_drink['name'] ?></h6>
                        <small class="text-muted">Base price: Birr <?= number_format($soft_drink['price'], 2) ?></small>
                    </div>
                </div>

                <div class="summary-details">
                    <div class="order-summary-item">
                        <span>Flavor:</span>
                        <span id="summaryFlavor" class="fw-bold">Original</span>
                    </div>
                    <div class="order-summary-item">
                        <span>Extras:</span>
                        <span id="summaryExtras" class="fw-bold">None</span>
                    </div>
                    <div class="order-summary-item">
                        <span>Quantity:</span>
                        <span id="summaryQuantity" class="fw-bold">1</span>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="order-summary-item order-total">
                            <span>Total:</span>
                            <span id="summaryTotal">Birr <?= number_format($soft_drink['price'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let softDrinkCustomization = {
    flavor: 'Original',
    extras: [],
    quantity: 1,
    specialNotes: '',
    basePrice: <?= $soft_drink['price'] ?>
};

function selectSoftDrinkFlavor(flavor) {
    softDrinkCustomization.flavor = flavor;
    document.querySelectorAll('#softDrinkFlavors .flavor-btn').forEach(btn => {
        btn.classList.toggle('btn-primary', btn.dataset.flavor === flavor);
        btn.classList.toggle('btn-outline-primary', btn.dataset.flavor !== flavor);
    });
    updateSoftDrinkSummary();
}

function toggleSoftDrinkExtra(extra) {
    const index = softDrinkCustomization.extras.indexOf(extra);
    if (index > -1) {
        softDrinkCustomization.extras.splice(index, 1);
    } else {
        softDrinkCustomization.extras.push(extra);
    }
    
    document.querySelectorAll('#softDrinkExtras .extra-btn').forEach(btn => {
        if (btn.dataset.extra === extra) {
            btn.classList.toggle('btn-primary', softDrinkCustomization.extras.includes(extra));
            btn.classList.toggle('btn-outline-primary', !softDrinkCustomization.extras.includes(extra));
        }
    });
    updateSoftDrinkSummary();
}

function updateSoftDrinkQuantity(amount) {
    softDrinkCustomization.quantity = Math.max(1, softDrinkCustomization.quantity + amount);
    document.getElementById('softDrinkQuantity').textContent = softDrinkCustomization.quantity;
    updateSoftDrinkSummary();
}

function updateSoftDrinkSummary() {
    document.getElementById('summaryFlavor').textContent = softDrinkCustomization.flavor;
    document.getElementById('summaryExtras').textContent = 
        softDrinkCustomization.extras.length ? softDrinkCustomization.extras.join(", ") : "None";
    document.getElementById('summaryQuantity').textContent = softDrinkCustomization.quantity;
    
    const total = softDrinkCustomization.basePrice * softDrinkCustomization.quantity;
    document.getElementById('summaryTotal').textContent = 'Birr ' + total.toFixed(2);
}

function addSoftDrinkToCart() {
    const totalPrice = softDrinkCustomization.basePrice * softDrinkCustomization.quantity;
    
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_type', 'soft_drink');
    formData.append('product_id', '<?= $soft_drink['id'] ?>');
    formData.append('product_name', '<?= $soft_drink['name'] ?>');
    formData.append('flavor', softDrinkCustomization.flavor);
    formData.append('toppings', JSON.stringify(softDrinkCustomization.extras));
    formData.append('quantity', softDrinkCustomization.quantity);
    formData.append('special_notes', softDrinkCustomization.specialNotes);
    formData.append('unit_price', softDrinkCustomization.basePrice);
    formData.append('total_price', totalPrice);
    
    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '?page=home';
        } else {
            alert('Error adding to cart: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to cart');
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    selectSoftDrinkFlavor('Original');
    document.getElementById('softDrinkSpecialNotes').addEventListener('input', function() {
        softDrinkCustomization.specialNotes = this.value;
    });
});
</script>