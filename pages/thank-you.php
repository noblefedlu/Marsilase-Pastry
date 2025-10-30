<?php
$order_id = $_GET['order_id'] ?? '';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-card-lg border-0 rounded-lg p-5 text-center fade-in-up hover-glow">
                <!-- Success Icon -->
                <div class="mb-4">
                    <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="bi bi-check-circle-fill text-white display-4"></i>
                    </div>
                </div>
                
                <!-- Main Message -->
                <h2 class="fw-bold text-success mb-4 display-font">🎉 Thank You for Your Order!</h2>

                <?php if ($order_id): ?>
                    <div class="alert alert-primary fade-in-up mb-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi bi-info-circle me-2 fs-5"></i>
                            <div class="text-start">
                                <strong>Order Confirmed!</strong><br>
                                <span class="fs-5">Order ID: <span class="text-primary fw-bold"><?= $order_id ?></span></span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="fs-5 text-muted slide-up mb-4">
                        Your order has been successfully placed and is being prepared with care.<br>
                        We'll contact you soon with delivery details and confirmation.
                    </p>
                <?php else: ?>
                    <p class="fs-5 text-muted slide-up mb-4">
                        Your order has been successfully placed and is being prepared with care.
                    </p>
                <?php endif; ?>

                <!-- Next Steps -->
                <div class="row g-3 mb-4 slide-up">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light hover-glow h-100">
                            <div class="card-body py-3">
                                <i class="bi bi-clock text-primary fs-2 mb-2"></i>
                                <h6 class="fw-bold">Preparation</h6>
                                <small class="text-muted">Your order is being prepared</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light hover-glow h-100">
                            <div class="card-body py-3">
                                <i class="bi bi-truck text-primary fs-2 mb-2"></i>
                                <h6 class="fw-bold">Delivery</h6>
                                <small class="text-muted">Out for delivery soon</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light hover-glow h-100">
                            <div class="card-body py-3">
                                <i class="bi bi-house-check text-primary fs-2 mb-2"></i>
                                <h6 class="fw-bold">Enjoy</h6>
                                <small class="text-muted">Delivered to your doorstep</small>
                            </div>
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
                                <small class="text-muted">Confirmation email sent to your address</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-telephone text-primary me-2"></i>
                                <small class="text-muted">We'll contact you for delivery updates</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support Information -->
                <div class="alert alert-light mt-4 slide-up">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bi bi-headset me-2 text-primary"></i>
                        <div>
                            <strong>Need help?</strong> Contact us at 
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
    const colors = ['#d4af37', '#b8941f', '#8b4513', '#e67e22', '#f9f5f0'];
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
.confetti {
    animation: confetti-fall linear forwards;
}

@keyframes confetti-fall {
    to {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}
</style>