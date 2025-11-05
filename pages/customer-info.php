[file name]: pages/customer-info.php
[file content begin]
<div class="section">
    <div class="container-narrow">
        <!-- Progress Steps -->
        <div class="card mb-5">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mb-2" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <span class="small fw-semibold">Cart</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mb-2" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <span class="small fw-semibold">Details</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center mb-2" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <span class="small text-muted">Complete</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Customer Information</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Please provide your details for order processing and delivery</p>

                        <form id="customerInfoForm">
                            <!-- Personal Information -->
                            <div class="mb-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-person me-2 text-primary"></i>
                                    Personal Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Full Name *</label>
                                        <input type="text" class="form-control" name="customer_name" required 
                                               placeholder="Enter your full name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Phone Number *</label>
                                        <input type="tel" class="form-control" name="customer_phone" required 
                                               placeholder="Enter your phone number">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Email Address *</label>
                                        <input type="email" class="form-control" name="customer_email" required 
                                               placeholder="Enter your email address">
                                        <div class="form-text">We'll send order confirmation to this email</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Information -->
                            <div class="mb-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-truck me-2 text-primary"></i>
                                    Delivery Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Delivery Address *</label>
                                        <textarea class="form-control" name="delivery_address" rows="3" required 
                                                  placeholder="Enter complete delivery address (Street, City, Area, Landmark)"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Delivery Date *</label>
                                        <input type="date" class="form-control" name="delivery_date" required 
                                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Preferred Time</label>
                                        <select class="form-select" name="delivery_time">
                                            <option value="09:00-12:00">Morning (9:00 AM - 12:00 PM)</option>
                                            <option value="12:00-15:00">Afternoon (12:00 PM - 3:00 PM)</option>
                                            <option value="15:00-18:00">Evening (3:00 PM - 6:00 PM)</option>
                                            <option value="18:00-21:00">Night (6:00 PM - 9:00 PM)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="mb-4">
                                <h5 class="mb-3">
                                    <i class="bi bi-chat-left-text me-2 text-primary"></i>
                                    Additional Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Special Instructions</label>
                                        <textarea class="form-control" name="special_instructions" rows="3" 
                                                  placeholder="Any special delivery instructions, allergies, or additional requests?"></textarea>
                                        <div class="form-text">Let us know if you have any specific requirements</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3">Order Summary</h6>
                                    <?php 
                                    $total = 0;
                                    foreach ($_SESSION['cart'] as $item): 
                                        $total += $item['total_price'];
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted"><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></small>
                                        <small class="fw-semibold">ETB <?= number_format($item['total_price'], 2) ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Total Amount:</strong>
                                        <strong class="text-primary">ETB <?= number_format($total, 2) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="?page=review" class="btn btn-secondary w-100">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Back to Cart
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Place Order
                                    </button>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Your information is secure and will not be shared with third parties
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('customerInfoForm');
    
    // Set minimum date to tomorrow
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    
    const dateInput = document.querySelector('input[name="delivery_date"]');
    dateInput.min = minDate;
    dateInput.value = minDate;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitOrder();
    });
});

function submitOrder() {
    const form = document.getElementById('customerInfoForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Validate form first
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing Order...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    formData.append('action', 'submit_order');
    
    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.text().then(text => {
            try {
                const data = JSON.parse(text);
                return { ok: response.ok, data: data };
            } catch (e) {
                console.error('Raw response:', text);
                throw new Error('Server returned invalid JSON. Response: ' + text.substring(0, 100));
            }
        });
    })
    .then(({ ok, data }) => {
        if (!ok) {
            throw new Error(data.message || `HTTP error! status: ${ok}`);
        }
        
        console.log('Order response:', data);
        if (data.success) {
            window.location.href = '?page=thank-you&order_id=' + data.order_id;
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting order: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>
[file content end]