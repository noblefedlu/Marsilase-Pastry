<?php
$drink_id = $_GET['drink_id'] ?? '';
$hot_drink = null;

foreach ($hot_drinks as $drink) {
    if ($drink['id'] == $drink_id) {
        $hot_drink = $drink;
        break;
    }
}

if (!$hot_drink) {
    echo '<div class="container my-5 text-center">
            <div class="card shadow-sm rounded-lg border-0 p-5">
                <i class="bi bi-cup-hot display-1 text-muted mb-3"></i>
                <h4 class="text-muted">No hot drink selected</h4>
                <p class="text-muted mb-4">Please select a hot drink to customize</p>
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
            <h2 class="mb-0 fw-bold">Customize Your <?= $hot_drink['name'] ?></h2>
            <p class="text-muted mb-0">Brew your perfect hot beverage</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-lg border-0 p-4">
                <form id="hotDrinkCustomizationForm">
                    <input type="hidden" name="product_id" value="<?= $hot_drink['id'] ?>">
                    <input type="hidden" name="product_type" value="hot_drink">
                    
                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-thermometer-high"></i>Select Strength</h5>
                        <div class="d-flex flex-wrap gap-2" id="hotDrinkFlavors">
                            <?php foreach ($hot_drink_flavors as $flavor): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill flavor-btn" 
                                    data-flavor="<?= $flavor ?>" onclick="selectHotDrinkFlavor('<?= $flavor ?>')">
                                <?= $flavor ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-plus-circle"></i>Add Extras</h5>
                        <div class="d-flex flex-wrap gap-2" id="hotDrinkExtras">
                            <?php foreach (['Extra Sugar', 'Less Sugar', 'No Sugar', 'Extra Milk', 'No Milk', 'Whipped Cream', 'Cinnamon', 'Chocolate Shavings'] as $extra): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill extra-btn" 
                                    data-extra="<?= $extra ?>" onclick="toggleHotDrinkExtra('<?= $extra ?>')">
                                <?= $extra ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-arrows-fullscreen"></i>Size</h5>
                        <div class="d-flex flex-wrap gap-2" id="hotDrinkSizes">
                            <button type="button" class="btn btn-outline-primary rounded-pill size-btn" 
                                    data-size="small" onclick="selectHotDrinkSize('small')">
                                Small (+Birr 0.00)
                            </button>
                            <button type="button" class="btn btn-outline-primary rounded-pill size-btn" 
                                    data-size="medium" onclick="selectHotDrinkSize('medium')">
                                Medium (+Birr 5.00)
                            </button>
                            <button type="button" class="btn btn-outline-primary rounded-pill size-btn" 
                                    data-size="large" onclick="selectHotDrinkSize('large')">
                                Large (+Birr 10.00)
                            </button>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-thermometer-sun"></i>Temperature</h5>
                        <div class="d-flex flex-wrap gap-2" id="hotDrinkTemperatures">
                            <button type="button" class="btn btn-outline-primary rounded-pill temp-btn" 
                                    data-temp="hot" onclick="selectHotDrinkTemperature('hot')">
                                Hot
                            </button>
                            <button type="button" class="btn btn-outline-primary rounded-pill temp-btn" 
                                    data-temp="warm" onclick="selectHotDrinkTemperature('warm')">
                                Warm
                            </button>
                            <button type="button" class="btn btn-outline-primary rounded-pill temp-btn" 
                                    data-temp="extra_hot" onclick="selectHotDrinkTemperature('extra_hot')">
                                Extra Hot
                            </button>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-cart"></i>Quantity</h5>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn" onclick="updateHotDrinkQuantity(-1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="mx-3 fw-bold fs-5 quantity-display" id="hotDrinkQuantity">1</span>
                            <button type="button" class="btn btn-outline-secondary rounded-circle quantity-btn" onclick="updateHotDrinkQuantity(1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="customization-option">
                        <h5 class="option-title"><i class="bi bi-chat-left-text"></i>Special Notes</h5>
                        <textarea class="form-control" rows="3" id="hotDrinkSpecialNotes" 
                                  placeholder="e.g., 'Extra foam', 'Light on sugar', etc."></textarea>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4 pt-3 border-top">
                        <a href="?page=home" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                        <button type="button" class="btn btn-success rounded-pill px-5 fw-bold" onclick="addHotDrinkToCart()">
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
                    <div class="product-thumb" style="background: <?= $hot_drink['color'] ?>"></div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold"><?= $hot_drink['name'] ?></h6>
                        <small class="text-muted">Base price: Birr <?= number_format($hot_drink['price'], 2) ?></small>
                    </div>
                </div>

                <div class="summary-details">
                    <div class="order-summary-item">
                        <span>Strength:</span>
                        <span id="summaryFlavor" class="fw-bold">Regular</span>
                    </div>
                    <div class="order-summary-item">
                        <span>Size:</span>
                        <span id="summarySize" class="fw-bold">Small</span>
                    </div>
                    <div class="order-summary-item">
                        <span>Temperature:</span>
                        <span id="summaryTemperature" class="fw-bold">Hot</span>
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
                            <span id="summaryTotal">Birr <?= number_format($hot_drink['price'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const sizePrices = {
    'small': 0,
    'medium': 5,
    'large': 10
};

const sizeLabels = {
    'small': 'Small',
    'medium': 'Medium',
    'large': 'Large'
};

const temperatureLabels = {
    'hot': 'Hot',
    'warm': 'Warm',
    'extra_hot': 'Extra Hot'
};

let hotDrinkCustomization = {
    flavor: 'Regular',
    size: 'small',
    temperature: 'hot',
    extras: [],
    quantity: 1,
    specialNotes: '',
    basePrice: <?= $hot_drink['price'] ?>
};

function selectHotDrinkFlavor(flavor) {
    hotDrinkCustomization.flavor = flavor;
    document.querySelectorAll('#hotDrinkFlavors .flavor-btn').forEach(btn => {
        btn.classList.toggle('btn-primary', btn.dataset.flavor === flavor);
        btn.classList.toggle('btn-outline-primary', btn.dataset.flavor !== flavor);
    });
    updateHotDrinkSummary();
}

function toggleHotDrinkExtra(extra) {
    const index = hotDrinkCustomization.extras.indexOf(extra);
    if (index > -1) {
        hotDrinkCustomization.extras.splice(index, 1);
    } else {
        hotDrinkCustomization.extras.push(extra);
    }
    
    document.querySelectorAll('#hotDrinkExtras .extra-btn').forEach(btn => {
        if (btn.dataset.extra === extra) {
            btn.classList.toggle('btn-primary', hotDrinkCustomization.extras.includes(extra));
            btn.classList.toggle('btn-outline-primary', !hotDrinkCustomization.extras.includes(extra));
        }
    });
    updateHotDrinkSummary();
}

function selectHotDrinkSize(size) {
    hotDrinkCustomization.size = size;
    document.querySelectorAll('#hotDrinkSizes .size-btn').forEach(btn => {
        btn.classList.toggle('btn-primary', btn.dataset.size === size);
        btn.classList.toggle('btn-outline-primary', btn.dataset.size !== size);
    });
    updateHotDrinkSummary();
}

function selectHotDrinkTemperature(temp) {
    hotDrinkCustomization.temperature = temp;
    document.querySelectorAll('#hotDrinkTemperatures .temp-btn').forEach(btn => {
        btn.classList.toggle('btn-primary', btn.dataset.temp === temp);
        btn.classList.toggle('btn-outline-primary', btn.dataset.temp !== temp);
    });
    updateHotDrinkSummary();
}

function updateHotDrinkQuantity(amount) {
    hotDrinkCustomization.quantity = Math.max(1, hotDrinkCustomization.quantity + amount);
    document.getElementById('hotDrinkQuantity').textContent = hotDrinkCustomization.quantity;
    updateHotDrinkSummary();
}

function updateHotDrinkSummary() {
    document.getElementById('summaryFlavor').textContent = hotDrinkCustomization.flavor;
    document.getElementById('summarySize').textContent = sizeLabels[hotDrinkCustomization.size];
    document.getElementById('summaryTemperature').textContent = temperatureLabels[hotDrinkCustomization.temperature];
    document.getElementById('summaryExtras').textContent = 
        hotDrinkCustomization.extras.length ? hotDrinkCustomization.extras.join(", ") : "None";
    document.getElementById('summaryQuantity').textContent = hotDrinkCustomization.quantity;
    
    const sizePrice = sizePrices[hotDrinkCustomization.size];
    const total = (hotDrinkCustomization.basePrice + sizePrice) * hotDrinkCustomization.quantity;
    document.getElementById('summaryTotal').textContent = 'Birr ' + total.toFixed(2);
}

function addHotDrinkToCart() {
    const sizePrice = sizePrices[hotDrinkCustomization.size];
    const totalPrice = (hotDrinkCustomization.basePrice + sizePrice) * hotDrinkCustomization.quantity;
    
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_type', 'hot_drink');
    formData.append('product_id', '<?= $hot_drink['id'] ?>');
    formData.append('product_name', '<?= $hot_drink['name'] ?>');
    formData.append('flavor', hotDrinkCustomization.flavor);
    formData.append('size', hotDrinkCustomization.size);
    formData.append('size_label', sizeLabels[hotDrinkCustomization.size]);
    formData.append('toppings', JSON.stringify({
        extras: hotDrinkCustomization.extras,
        temperature: hotDrinkCustomization.temperature
    }));
    formData.append('quantity', hotDrinkCustomization.quantity);
    formData.append('special_notes', hotDrinkCustomization.specialNotes);
    formData.append('unit_price', hotDrinkCustomization.basePrice);
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
    selectHotDrinkFlavor('Regular');
    selectHotDrinkSize('small');
    selectHotDrinkTemperature('hot');
    document.getElementById('hotDrinkSpecialNotes').addEventListener('input', function() {
        hotDrinkCustomization.specialNotes = this.value;
    });
});
</script>