<?php
$icecream_id = $_GET['icecream_id'] ?? '';
$ice_cream = null;

// Find the selected ice cream
foreach ($ice_creams as $ic) {
    if ($ic['id'] == $icecream_id) {
        $ice_cream = $ic;
        break;
    }
}

if (!$ice_cream) {
    echo '<div class="container my-5 text-center">
            <p class="text-muted">No ice cream selected for customization.</p>
            <a href="?page=home" class="btn btn-primary rounded-pill mt-3"><i class="bi bi-arrow-left"></i> Back to Home</a>
          </div>';
    return;
}
?>

<div class="container my-5">
    <div class="d-flex align-items-center mb-4">
        <a href="?page=home" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h2 class="mb-0 fw-bold">Customize Your <?= $ice_cream['name'] ?> Ice Cream</h2>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-lg border-0 p-4">
                <form id="iceCreamCustomizationForm">
                    <input type="hidden" name="product_id" value="<?= $ice_cream['id'] ?>">
                    <input type="hidden" name="product_type" value="ice_cream">
                    
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Select Ice Cream Flavor</h5>
                        <div class="d-flex flex-wrap gap-2" id="iceCreamFlavors">
                            <?php foreach ($ice_cream_flavors as $flavor): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill flavor-btn" 
                                    data-flavor="<?= $flavor ?>" onclick="selectIceCreamFlavor('<?= $flavor ?>')">
                                <?= $flavor ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Add Toppings</h5>
                        <div class="d-flex flex-wrap gap-2" id="iceCreamToppings">
                            <?php foreach ($toppings as $topping): ?>
                            <button type="button" class="btn btn-outline-primary rounded-pill topping-btn" 
                                    data-topping="<?= $topping ?>" onclick="toggleIceCreamTopping('<?= $topping ?>')">
                                <?= $topping ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Quantity</h5>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary rounded-circle" onclick="updateIceCreamQuantity(-1)"><i class="bi bi-dash"></i></button>
                            <span class="mx-3 fw-bold fs-5" id="iceCreamQuantity">1</span>
                            <button type="button" class="btn btn-outline-secondary rounded-circle" onclick="updateIceCreamQuantity(1)"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Special Notes</h5>
                        <textarea class="form-control" rows="3" id="iceCreamSpecialNotes" placeholder="e.g., 'Extra chocolate sauce on the side'"></textarea>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <a href="?page=home" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="button" class="btn btn-success rounded-pill px-5" onclick="addIceCreamToCart()">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm rounded-lg border-0 p-4 summary-sticky">
                <h5 class="fw-bold mb-3">Order Summary</h5>
                <div class="d-flex align-items-center mb-3">
                    <div class="product-thumb" style="background: <?= $ice_cream['color'] ?>; width: 60px; height: 60px; border-radius: 8px;"></div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?= $ice_cream['name'] ?></h6>
                        <small class="text-muted">Base price: Birr <?= number_format($ice_cream['price'], 2) ?></small>
                    </div>
                </div>

                <div class="summary-details">
                    <p><strong>Flavor:</strong> <span id="summaryFlavor">Vanilla</span></p>
                    <p><strong>Toppings:</strong> <span id="summaryToppings">None</span></p>
                    <p><strong>Quantity:</strong> <span id="summaryQuantity">1</span></p>

                    <div class="border-top pt-3 mt-3">
                        <h5 class="d-flex justify-content-between">
                            <span>Total:</span>
                            <span id="summaryTotal">Birr <?= number_format($ice_cream['price'], 2) ?></span>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let iceCreamCustomization = {
    flavor: 'Vanilla',
    toppings: [],
    quantity: 1,
    specialNotes: '',
    basePrice: <?= $ice_cream['price'] ?>
};

function selectIceCreamFlavor(flavor) {
    iceCreamCustomization.flavor = flavor;
    document.querySelectorAll('#iceCreamFlavors .flavor-btn').forEach(btn => {
        btn.classList.toggle('btn-primary', btn.dataset.flavor === flavor);
        btn.classList.toggle('btn-outline-primary', btn.dataset.flavor !== flavor);
    });
    updateIceCreamSummary();
}

function toggleIceCreamTopping(topping) {
    const index = iceCreamCustomization.toppings.indexOf(topping);
    if (index > -1) {
        iceCreamCustomization.toppings.splice(index, 1);
    } else {
        iceCreamCustomization.toppings.push(topping);
    }
    
    document.querySelectorAll('#iceCreamToppings .topping-btn').forEach(btn => {
        if (btn.dataset.topping === topping) {
            btn.classList.toggle('btn-primary', iceCreamCustomization.toppings.includes(topping));
            btn.classList.toggle('btn-outline-primary', !iceCreamCustomization.toppings.includes(topping));
        }
    });
    updateIceCreamSummary();
}

function updateIceCreamQuantity(amount) {
    iceCreamCustomization.quantity = Math.max(1, iceCreamCustomization.quantity + amount);
    document.getElementById('iceCreamQuantity').textContent = iceCreamCustomization.quantity;
    updateIceCreamSummary();
}

function updateIceCreamSummary() {
    document.getElementById('summaryFlavor').textContent = iceCreamCustomization.flavor;
    document.getElementById('summaryToppings').textContent = 
        iceCreamCustomization.toppings.length ? iceCreamCustomization.toppings.join(", ") : "None";
    document.getElementById('summaryQuantity').textContent = iceCreamCustomization.quantity;
    
    const total = iceCreamCustomization.basePrice * iceCreamCustomization.quantity;
    document.getElementById('summaryTotal').textContent = 'Birr ' + total.toFixed(2);
}

function addIceCreamToCart() {
    const totalPrice = iceCreamCustomization.basePrice * iceCreamCustomization.quantity;
    
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('product_type', 'ice_cream');
    formData.append('product_id', '<?= $ice_cream['id'] ?>');
    formData.append('product_name', '<?= $ice_cream['name'] ?>');
    formData.append('flavor', iceCreamCustomization.flavor);
    formData.append('toppings', JSON.stringify(iceCreamCustomization.toppings));
    formData.append('quantity', iceCreamCustomization.quantity);
    formData.append('special_notes', iceCreamCustomization.specialNotes);
    formData.append('unit_price', iceCreamCustomization.basePrice);
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
    selectIceCreamFlavor('Vanilla');
    document.getElementById('iceCreamSpecialNotes').addEventListener('input', function() {
        iceCreamCustomization.specialNotes = this.value;
    });
});
</script>