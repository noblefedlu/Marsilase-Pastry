
<?php
$order_id = $_GET['order_id'] ?? '';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="success-card glass-card rounded-4 p-5 text-center hover-glow">
                <!-- Success Icon -->
                <div class="success-icon mb-4">
                    <div class="icon-circle rounded-circle d-inline-flex align-items-center justify-content-center">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                
                <!-- Main Message -->
                <h1 class="section-title mb-4">🎉 Thank You for Your Order!</h1>

                <?php if ($order_id): ?>
                    <div class="order-confirmation glass-card rounded-4 p-4 mb-4 hover-glow">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi bi-info-circle me-3 fs-2 text-primary"></i>
                            <div class="text-start">
                                <strong class="fs-5 text-dark">Order Confirmed!</strong><br>
                                <span class="fs-4 text-primary fw-bold">Order ID: <?= $order_id ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="fs-5 text-medium mb-4 slide-up">
                        Your order has been successfully placed and is being prepared with care.<br>
                        We'll contact you soon with delivery details and confirmation.
                    </p>
                <?php else: ?>
                    <p class="fs-5 text-medium mb-4 slide-up">
                        Your order has been successfully placed and is being prepared with care.
                    </p>
                <?php endif; ?>

                <!-- Next Steps -->
                <div class="row g-3 mb-4 slide-up">
                    <div class="col-md-4">
                        <div class="step-card glass-card rounded-4 p-3 hover-glow h-100">
                            <i class="bi bi-clock fs-1 text-primary mb-2"></i>
                            <h6 class="fw-bold text-dark">Preparation</h6>
                            <small class="text-medium">Your order is being prepared</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="step-card glass-card rounded-4 p-3 hover-glow h-100">
                            <i class="bi bi-truck fs-1 text-primary mb-2"></i>
                            <h6 class="fw-bold text-dark">Delivery</h6>
                            <small class="text-medium">Out for delivery soon</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="step-card glass-card rounded-4 p-3 hover-glow h-100">
                            <i class="bi bi-house-check fs-1 text-primary mb-2"></i>
                            <h6 class="fw-bold text-dark">Enjoy</h6>
                            <small class="text-medium">Delivered to your doorstep</small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4 slide-up">
                    <a href="?page=home" class="btn btn-primary rounded-pill px-4 py-3 hover-glow">
                        <i class="bi bi-house me-2"></i>Back to Home
                    </a>
                    <a href="?page=review" class="btn btn-outline-primary rounded-pill px-4 py-3 hover-glow">
                        <i class="bi bi-cart me-2"></i>View Order Details
                    </a>
                </div>

                <!-- Additional Information -->
                <div class="mt-4 pt-3 border-top slide-up">
                    <div class="row g-3 text-start">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope text-primary me-2"></i>
                                <small class="text-medium">Confirmation email sent to your address</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-telephone text-primary me-2"></i>
                                <small class="text-medium">We'll contact you for delivery updates</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support Information -->
                <div class="support-info glass-card rounded-4 p-4 mt-4 slide-up">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bi bi-headset me-3 fs-2 text-primary"></i>
                        <div class="text-start">
                            <strong class="text-dark">Need help?</strong><br>
                            Contact us at 
                            <a href="tel:+251967318674" class="text-primary fw-bold">+251-967-318-674</a> 
                            or email 
                            <a href="mailto:marsilasepastry@gmail.com" class="text-primary fw-bold">marsilasepastry@gmail.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add some celebratory effects
document.addEventListener('DOMContentLoaded', function() {
    // Add confetti effect
    setTimeout(() => {
        createConfetti();
    }, 500);
});

function createConfetti() {
    const colors = ['#C2865A', '#4A2E2B', '#F8E9D2', '#8B6B5E', '#6B3E2C'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.cssText = `
            position: fixed;
            width: 10px;
            height: 10px;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            top: -10px;
            left: ${Math.random() * 100}%;
            opacity: ${Math.random() * 0.5 + 0.5};
            border-radius: 2px;
            z-index: 1000;
            pointer-events: none;
        `;
        
        document.body.appendChild(confetti);
        
        // Animate confetti
        const animation = confetti.animate([
            { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
            { transform: `translateY(${window.innerHeight}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
        ], {
            duration: Math.random() * 3000 + 2000,
            easing: 'cubic-bezier(0.1, 0.8, 0.3, 1)'
        });
        
        animation.onfinish = () => {
            confetti.remove();
        };
    }
}
</script>

<style>
.success-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.success-card:hover {
    transform: translateY(-5px);
}

.success-icon .icon-circle {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
    color: var(--primary-light);
    font-size: 3rem;
    animation: bounce 1s ease-in-out;
}

.order-confirmation, .step-card, .support-info {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.order-confirmation:hover, .step-card:hover, .support-info:hover {
    transform: translateY(-2px);
}

.step-card {
    transition: all 0.3s ease;
}

.step-card:hover {
    transform: translateY(-3px) scale(1.02);
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
    40% {transform: translateY(-10px);}
    60% {transform: translateY(-5px);}
}

.confetti {
    animation: confetti-fall linear forwards;
}

@keyframes confetti-fall {
    to {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

.slide-up {
    animation: slideUp 0.8s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>