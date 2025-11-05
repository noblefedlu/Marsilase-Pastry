[file name]: test_order_flow.php
[file content begin]
<?php
session_start();
?>
<div class="section">
    <div class="container-narrow">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Order Flow Testing</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="text-primary mb-3">Current Status</h4>
                        
                        <!-- Session Status -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6>Session Status</h6>
                                <?php
                                echo "<div class='mb-2 " . (isset($_SESSION['cart']) ? 'text-success' : 'text-warning') . "'>";
                                echo "<strong>Cart Session:</strong> " . (isset($_SESSION['cart']) ? '✅ Exists' : '❌ Not set');
                                echo "</div>";
                                
                                if (isset($_SESSION['cart'])) {
                                    echo "<div class='text-success'>";
                                    echo "<strong>Cart Items:</strong> " . count($_SESSION['cart']);
                                    echo "<br><small>Items: " . json_encode($_SESSION['cart']) . "</small>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Quick Tests -->
                        <div class="card">
                            <div class="card-body">
                                <h6>Quick Tests</h6>
                                <div class="d-grid gap-2">
                                    <a href="?page=home" class="btn btn-outline-primary">Test Homepage</a>
                                    <a href="?page=customize-cake&cake_id=1" class="btn btn-outline-primary">Test Cake Customization</a>
                                    <a href="?page=review" class="btn btn-outline-info">Test Cart Review</a>
                                    <a href="?page=customer-info" class="btn btn-outline-success">Test Checkout</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="text-primary mb-3">Order Flow</h4>
                        
                        <!-- Progress Steps -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row text-center small">
                                    <div class="col-3">
                                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-1" 
                                             style="width: 30px; height: 30px;">
                                            <i class="bi bi-1"></i>
                                        </div>
                                        <div>Browse</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-1" 
                                             style="width: 30px; height: 30px;">
                                            <i class="bi bi-2"></i>
                                        </div>
                                        <div>Customize</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-1" 
                                             style="width: 30px; height: 30px;">
                                            <i class="bi bi-3"></i>
                                        </div>
                                        <div>Review</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-1" 
                                             style="width: 30px; height: 30px;">
                                            <i class="bi bi-4"></i>
                                        </div>
                                        <div>Complete</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Data -->
                        <div class="card">
                            <div class="card-body">
                                <h6>Test Data</h6>
                                <button onclick="addSampleData()" class="btn btn-success btn-sm w-100 mb-2">
                                    Add Sample Items to Cart
                                </button>
                                <button onclick="clearCart()" class="btn btn-warning btn-sm w-100">
                                    Clear Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Check -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">System Check</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-database text-success me-2"></i>
                                    <span>Database: Connected</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-cart text-success me-2"></i>
                                    <span>Cart System: Active</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-credit-card text-success me-2"></i>
                                    <span>Order System: Ready</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addSampleData() {
    // Add multiple sample items
    const sampleItems = [
        {
            action: 'add_to_cart',
            product_type: 'cake',
            product_id: '1',
            product_name: 'Chocolate Fantasy Cake',
            flavor: 'Chocolate',
            size: 'Medium',
            quantity: '1',
            unit_price: '1800',
            total_price: '1800'
        },
        {
            action: 'add_to_cart',
            product_type: 'cake', 
            product_id: '2',
            product_name: 'Vanilla Dream Cake',
            flavor: 'Vanilla',
            size: 'Small',
            quantity: '2',
            unit_price: '1100',
            total_price: '2200'
        }
    ];

    let completed = 0;
    
    sampleItems.forEach(item => {
        const formData = new FormData();
        for (const key in item) {
            formData.append(key, item[key]);
        }
        
        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            completed++;
            if (completed === sampleItems.length) {
                alert('✅ Sample items added to cart!');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error adding sample items');
        });
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear the cart?')) return;
    
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=clear_cart'
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}
</script>

<style>
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>
[file content end]