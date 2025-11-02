
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Progress Steps -->
            <div class="progress-steps-card glass-card rounded-4 p-4 mb-5 hover-glow">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div class="step-label">Cart</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="step active">
                            <div class="step-icon">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="step-label">Details</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="step">
                            <div class="step-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="step-label">Complete</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information Form -->
            <div class="customer-form-card glass-card rounded-4 p-5 hover-glow">
                <h2 class="section-title text-center mb-4">Customer Information</h2>
                <p class="text-center text-medium mb-5">Please provide your details for order processing and delivery</p>

                <form id="customerInfoForm" class="needs-validation" novalidate>
                    <!-- Personal Information -->
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <h5 class="text-dark mb-3">
                                <i class="bi bi-person me-2"></i>Personal Information
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control hover-glow" name="customer_name" required 
                                   placeholder="Enter your full name">
                            <div class="invalid-feedback">
                                Please provide your full name.
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control hover-glow" name="customer_phone" required 
                                   placeholder="Enter your phone number">
                            <div class="invalid-feedback">
                                Please provide a valid phone number.
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control hover-glow" name="customer_email" required 
                                   placeholder="Enter your email address">
                            <div class="form-text text-medium">We'll send order confirmation to this email</div>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <h5 class="text-dark mb-3">
                                <i class="bi bi-truck me-2"></i>Delivery Information
                            </h5>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Delivery Address <span class="text-danger">*</span></label>
                            <textarea class="form-control hover-glow" name="delivery_address" rows="3" required 
                                      placeholder="Enter complete delivery address (Street, City, Area, Landmark)"></textarea>
                            <div class="invalid-feedback">
                                Please provide the delivery address.
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control hover-glow" name="delivery_date" required 
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            <div class="invalid-feedback">
                                Please select a delivery date.
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Preferred Time</label>
                            <select class="form-select hover-glow" name="delivery_time">
                                <option value="09:00-12:00">Morning (9:00 AM - 12:00 PM)</option>
                                <option value="12:00-15:00">Afternoon (12:00 PM - 3:00 PM)</option>
                                <option value="15:00-18:00">Evening (3:00 PM - 6:00 PM)</option>
                                <option value="18:00-21:00">Night (6:00 PM - 9:00 PM)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <h5 class="text-dark mb-3">
                                <i class="bi bi-chat-left-text me-2"></i>Additional Information
                            </h5>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Special Instructions</label>
                            <textarea class="form-control hover-glow" name="special_instructions" rows="3" 
                                      placeholder="Any special delivery instructions, allergies, or additional requests?"></textarea>
                            <div class="form-text text-medium">Let us know if you have any specific requirements</div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="order-summary-preview glass-card rounded-4 p-4 mb-4 hover-glow">
                        <h5 class="text-dark mb-3">Order Summary</h5>
                        <?php 
                        $total = 0;
                        foreach ($_SESSION['cart'] as $item): 
                            $total += $item['total_price'];
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-medium"><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></small>
                            <small class="fw-semibold text-dark">ETB <?= number_format($item['total_price'], 2) ?></small>
                        </div>
                        <?php endforeach; ?>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="fs-5 text-dark">Total Amount:</strong>
                            <strong class="fs-4 text-primary">ETB <?= number_format($total, 2) ?></strong>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row g-3 mt-4 pt-4 border-top">
                        <div class="col-md-6">
                            <a href="?page=review" class="btn btn-outline-primary w-100 py-3 hover-glow">
                                <i class="bi bi-arrow-left me-2"></i>Back to Cart
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold hover-glow">
                                <i class="bi bi-check-circle me-2"></i>Place Order
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-medium">
                            <i class="bi bi-shield-check me-1"></i>Your information is secure and will not be shared with third parties
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('customerInfoForm');
    
    // Bootstrap validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        submitOrder();
    });
    
    // Set minimum date to tomorrow
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    
    const dateInput = document.querySelector('input[name="delivery_date"]');
    dateInput.min = minDate;
    dateInput.value = minDate;
});

function submitOrder() {
    const form = document.getElementById('customerInfoForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing Order...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    formData.append('action', 'submit_order');
    
    showLoader();
    
    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        
        if (data.success) {
            showToast('🎉 Order placed successfully! Redirecting...');
            setTimeout(() => {
                window.location.href = '?page=thank-you&order_id=' + data.order_id;
            }, 2000);
        } else {
            showToast('Error: ' + (data.message || 'Unknown error occurred'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error:', error);
        showToast('Error submitting order. Please try again.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>

<style>
.progress-steps-card, .customer-form-card, .order-summary-preview {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.progress-steps-card:hover, .customer-form-card:hover, .order-summary-preview:hover {
    transform: translateY(-2px);
}

.step {
    position: relative;
    padding: 1rem 0;
}

.step.completed .step-icon {
    background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
    color: var(--primary-light);
}

.step.active .step-icon {
    background: var(--primary-dark);
    color: var(--primary-light);
    transform: scale(1.1);
}

.step:not(.completed):not(.active) .step-icon {
    background: var(--primary-light);
    color: var(--text-medium);
    border: 2px solid var(--primary-medium);
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

.step-label {
    font-weight: 600;
    color: var(--text-dark);
}

.step.completed .step-label,
.step.active .step-label {
    color: var(--primary-dark);
}

.form-control:hover, .form-select:hover {
    border-color: var(--primary-medium);
    box-shadow: 0 0 0 0.2rem rgba(194, 134, 90, 0.1);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-dark);
    box-shadow: 0 0 0 0.2rem rgba(74, 46, 43, 0.1);
}
</style>