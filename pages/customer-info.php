<?php
if (empty($_SESSION['cart'])) {
    header('Location: ?page=home');
    exit;
}
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Progress Steps -->
            <div class="card border-0 shadow-card-lg mb-4 fade-in-up">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                                    <i class="bi bi-cart-check text-dark fs-5"></i>
                                </div>
                                <span class="fw-bold text-primary small">Cart</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-check text-dark fs-5"></i>
                                </div>
                                <span class="fw-bold text-primary small">Details</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex flex-column align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                                    <i class="bi bi-check-circle text-muted fs-5"></i>
                                </div>
                                <span class="fw-bold text-muted small">Complete</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-card-lg border-0 rounded-lg fade-in-up">
                <div class="card-header bg-gradient text-white py-4">
                    <h4 class="mb-0 fw-bold display-font text-center">
                        <i class="bi bi-person-circle me-2"></i>Customer Information
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form id="customerInfoForm" class="needs-validation" novalidate>
                        <!-- Personal Information -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3 display-font">
                                    <i class="bi bi-person me-2"></i>Personal Information
                                </h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control hover-glow" name="customer_name" required 
                                       placeholder="Enter your full name">
                                <div class="invalid-feedback">
                                    Please provide your full name.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control hover-glow" name="customer_phone" required 
                                       placeholder="Enter your phone number">
                                <div class="invalid-feedback">
                                    Please provide a valid phone number.
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control hover-glow" name="customer_email" required 
                                       placeholder="Enter your email address">
                                <div class="form-text">We'll send order confirmation to this email</div>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="fw-bold text-primary mb-3 display-font">
                                    <i class="bi bi-truck me-2"></i>Delivery Information
                                </h6>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Delivery Address <span class="text-danger">*</span></label>
                                <textarea class="form-control hover-glow" name="delivery_address" rows="3" required 
                                          placeholder="Enter complete delivery address (Street, City, Area, Landmark)"></textarea>
                                <div class="invalid-feedback">
                                    Please provide the delivery address.
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Billing Address</label>
                                <textarea class="form-control hover-glow" name="customer_address" rows="3" 
                                          placeholder="Enter billing address (if different from delivery address)"></textarea>
                                <div class="form-text">Leave blank if same as delivery address</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control hover-glow" name="delivery_date" required 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                <div class="invalid-feedback">
                                    Please select a delivery date.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Preferred Delivery Time</label>
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
                                <h6 class="fw-bold text-primary mb-3 display-font">
                                    <i class="bi bi-chat-left-text me-2"></i>Additional Information
                                </h6>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Special Instructions</label>
                                <textarea class="form-control hover-glow" name="special_instructions" rows="3" 
                                          placeholder="Any special delivery instructions, allergies, or additional requests?"></textarea>
                                <div class="form-text">Let us know if you have any specific requirements</div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="email_notifications" id="emailNotifications" checked>
                                    <label class="form-check-label" for="emailNotifications">
                                        Send me order updates and promotions via email
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Summary Preview -->
                        <div class="card border-0 bg-light hover-glow mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3 display-font">Order Summary</h6>
                                <?php 
                                $total = 0;
                                foreach ($_SESSION['cart'] as $item): 
                                    $total += $item['total_price'];
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted"><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></small>
                                    <small class="fw-semibold">Birr <?= number_format($item['total_price'], 2) ?></small>
                                </div>
                                <?php endforeach; ?>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="fs-5">Total Amount:</strong>
                                    <strong class="fs-4 text-primary">Birr <?= number_format($total, 2) ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="row g-3 mt-4 pt-3 border-top">
                            <div class="col-md-6">
                                <a href="?page=review" class="btn btn-outline-primary w-100 py-3 hover-glow">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Cart
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success w-100 py-3 fw-bold hover-glow">
                                    <i class="bi bi-check-circle me-2"></i>Place Order
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>Your information is secure and will not be shared with third parties
                            </small>
                        </div>
                    </form>
                </div>
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
    
    // Real-time validation
    form.querySelectorAll('input[required], textarea[required]').forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
    
    // Set minimum date to tomorrow
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    
    const dateInput = document.querySelector('input[name="delivery_date"]');
    dateInput.min = minDate;
    dateInput.value = minDate;
    
    // Auto-fill billing address if delivery address changes
    const deliveryAddress = document.querySelector('textarea[name="delivery_address"]');
    const billingAddress = document.querySelector('textarea[name="customer_address"]');
    
    deliveryAddress.addEventListener('input', function() {
        if (!billingAddress.value) {
            billingAddress.value = this.value;
        }
    });
});

function validateField(field) {
    if (field.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    } else if (field.type === 'tel') {
        if (field.value.trim().length < 5) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    } else {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    }
}

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
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        hideLoader();
        console.log('Order response:', data);
        
        if (data.success) {
            showSuccessMessage('🎉 Order placed successfully! Redirecting...');
            setTimeout(() => {
                window.location.href = '?page=thank-you&order_id=' + data.order_id;
            }, 2000);
        } else {
            showError('Error: ' + (data.message || 'Unknown error occurred'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error:', error);
        showError('Error submitting order: ' + error.message + '. Please check your connection and try again.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showSuccessMessage(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    successDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    successDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <span class="fw-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(successDiv);
    
    setTimeout(() => {
        if (successDiv.parentElement) {
            successDiv.remove();
        }
    }, 5000);
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    errorDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    errorDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <span class="fw-medium">${message}</span>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(errorDiv);
    
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 8000);
}
</script>