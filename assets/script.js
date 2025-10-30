// Enhanced JavaScript for Marsilase Pastry

// Initialize all interactive elements
document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeFormEnhancements();
    initializeProductInteractions();
});

// Animation initialization
function initializeAnimations() {
    // Add hover effects to all interactive elements
    const interactiveElements = document.querySelectorAll('.btn, .product-card, .card, .nav-link, .form-control, .form-select');
    interactiveElements.forEach(element => {
        if (!element.classList.contains('no-hover-glow')) {
            element.classList.add('hover-glow');
        }
    });
    
    // Initialize scroll animations
    checkVisibility();
    
    // Add intersection observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    // Observe all animatable elements
    document.querySelectorAll('.product-card, .testimonial, .slide-up, .fade-in-up').forEach(el => {
        observer.observe(el);
    });
}

// Form enhancements
function initializeFormEnhancements() {
    // Add floating labels to form inputs
    const formInputs = document.querySelectorAll('.form-control');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
        
        // Check initial state
        if (input.value) {
            input.parentElement.classList.add('focused');
        }
    });
}

// Product interactions
function initializeProductInteractions() {
    // Add to cart animations
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Add ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = event.clientX - rect.left - size / 2;
            const y = event.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.5);
                border-radius: 50%;
                position: absolute;
                animation: ripple 0.6s ease-out;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// Enhanced toast notifications
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = type === 'success' ? 'bi-check-circle-fill' : 
                 type === 'error' ? 'bi-exclamation-triangle-fill' : 
                 'bi-info-circle-fill';
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${icon} me-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    toast.className = `toast ${type} active`;
    toast.classList.add('active');
    
    setTimeout(() => {
        toast.classList.remove('active');
    }, 3000);
}

// Enhanced loader with progress simulation
function showLoader(message = 'Loading...') {
    const loader = document.getElementById('loader');
    loader.innerHTML = `
        <div class="text-center">
            <div class="spinner mb-3"></div>
            <p class="text-dark fw-semibold">${message}</p>
        </div>
    `;
    loader.classList.add('active');
}

function hideLoader() {
    const loader = document.getElementById('loader');
    loader.classList.remove('active');
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Enhanced image loading with fade-in
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('fade-in-up');
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', lazyLoadImages);
} else {
    lazyLoadImages();
}

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .toast.success {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }
    
    .toast.error {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    }
    
    .toast.info {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    }
    
    .form-group {
        position: relative;
    }
    
    .form-group.focused label {
        transform: translateY(-25px) scale(0.85);
        color: var(--primary);
    }
`;
document.head.appendChild(style);