<!-- Hero Section -->
<section class="hero">
    <div class="container-narrow">
        <div class="hero-content">
            <h1 class="hero-title display-font">
                Marsilase Cakes &<br>
                <span class="text-gradient">Exquisite Desserts</span>
            </h1>
            <p class="hero-subtitle">
                Handcrafted with passion, delivered with perfection. Experience the finest cakes and desserts in Addis Ababa.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="?page=products" class="btn btn-primary">
                    <i class="bi bi-star me-2"></i>
                    Explore Our Creations
                </a>
                <a href="?page=review" class="btn btn-secondary">
                    <i class="bi bi-cart3 me-2"></i>
                    View Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="badge bg-primary ms-2"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="container-narrow">
        <div class="text-center mb-5" data-animate>
            <h2 class="display-4 display-font mb-3">Why You Choose Marsilase?</h2>
            <p class="text-lead">Experience the difference that quality ingredients and expert craftsmanship make.</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card" data-animate>
                <div class="feature-icon">
                    <i class="bi bi-star"></i>
                </div>
                <h4>Premium Quality</h4>
                <p class="text-muted mb-0">Only the finest ingredients sourced from trusted suppliers.</p>
            </div>
            
            <div class="feature-card" data-animate>
                <div class="feature-icon">
                    <i class="bi bi-clock"></i>
                </div>
                <h4>Fresh Daily</h4>
                <p class="text-muted mb-0">Baked fresh every morning to ensure optimal taste and quality.</p>
            </div>
            
            <div class="feature-card" data-animate>
                <div class="feature-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h4>Fast Delivery</h4>
                <p class="text-muted mb-0">Quick and reliable delivery across Addis Ababa.</p>
            </div>
            
            <div class="feature-card" data-animate>
                <div class="feature-icon">
                    <i class="bi bi-palette"></i>
                </div>
                <h4>Custom Designs</h4>
                <p class="text-muted mb-0">Fully customizable cakes for your special occasions.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section id="featured-products" class="section bg-light">
    <div class="container-narrow">
        <div class="text-center mb-5" data-animate>
            <h2 class="display-4 display-font mb-3">Featured Creations</h2>
            <p class="text-lead">Discover our most beloved cakes, crafted with passion and perfection</p>
        </div>
        
        <div class="product-grid">
            <?php 
            // Get featured cakes (limit to 6)
            $featured_cakes = array_filter($cakes, function($cake) {
                return ($cake['is_featured'] ?? false) || ($cake['is_featured'] ?? 0) == 1;
            });
            $featured_cakes = array_slice($featured_cakes, 0, 6);
            
            foreach ($featured_cakes as $cake): 
            ?>
            <div class="product-card" data-animate>
                <div class="product-image">
                    <img src="<?= htmlspecialchars($cake['image_url'] ?? 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') ?>" 
                         alt="<?= htmlspecialchars($cake['name'] ?? 'Cake') ?>" 
                         loading="lazy">
                    <?php if (($cake['is_featured'] ?? false) || ($cake['is_featured'] ?? 0) == 1): ?>
                    <div class="product-badge">Featured</div>
                    <?php endif; ?>
                    <div class="product-overlay">
                        <a href="?page=customize-cake&cake_id=<?= $cake['id'] ?? '' ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            Customize
                        </a>
                    </div>
                </div>
                <div class="product-content">
                    <h3 class="product-title"><?= htmlspecialchars($cake['name'] ?? 'Unknown Product') ?></h3>
                    <p class="product-description"><?= htmlspecialchars($cake['description'] ?? 'No description available') ?></p>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-half text-warning"></i>
                            <span class="text-muted small ms-1">(4.5)</span>
                        </div>
                        <div class="product-category">
                            <span class="badge bg-light text-dark"><?= htmlspecialchars(ucfirst($cake['category'] ?? 'general')) ?></span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="product-price">ETB <?= number_format($cake['price'] ?? 0, 2) ?></div>
                        <a href="?page=customize-cake&cake_id=<?= $cake['id'] ?? '' ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-cart-plus me-1"></i>
                            Order Now
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5" data-animate>
            <a href="?page=all-products" class="btn btn-secondary">
                View All Products
                <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section">
    <div class="container-narrow">
        <div class="row align-items-center">
            <div class="col-lg-6" data-animate>
                <h2 class="display-4 display-font mb-4">Our Story</h2>
                <p class="text-lead mb-4">
                    Since 2023, Marsilase Pastry has been at the heart of Addis Ababa's dessert scene, 
                    creating unforgettable moments through our exquisite cakes and pastries.
                </p>
                <p class="mb-4">
                    Our master pastry chefs combine traditional techniques with innovative flavors, 
                    using only the finest ingredients to create desserts that not only look stunning 
                    but taste extraordinary.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-2"></i>
                        <span>100% Natural Ingredients</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-2"></i>
                        <span>Handcrafted Daily</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-2"></i>
                        <span>Custom Designs</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-animate>
                <div class="card">
                    <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Our Bakery" 
                         class="card-img-top"
                         loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section id="testimonials" class="section bg-light">
    <div class="container-narrow">
        <div class="text-center mb-5">
            <h2 class="testimonials-title">WHAT OUR CUSTOMERS SAY</h2>
        </div>
        
        <div class="testimonials-scroll-container">
            <div class="testimonials-scroll-track">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "If you have some food for diabetic clients and other special needs, that would be great."
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "I highly recommend this place."
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "I love the white and dark chocolat."
                    </div>
                </div>
                
                <!-- Duplicate cards for seamless loop -->
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "If you have some food for diabetic clients and other special needs, that would be great."
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "I highly recommend this place."
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "I love the white and dark chocolat."
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section">
    <div>
        <div class="text-center mb-5">
            <h2 class="testimonials-title">Get in Touch</h2>
        </div>
    <div class="container-narrow">
        <div class="contact-container">
            <!-- Left Side - Contact Info -->
            <div class="contact-info">
                <div class="contact-header">
                    <h3>RESERVATIONS | ORDERS | ENQUIRIES</h3>
                </div>
                
                <div class="phone-numbers">
                    <div class="phone-item">
                        <strong>Main Branch:</strong> +251 941 000 022
                    </div>
                    <div class="phone-item">
                        <strong>Second Branch:</strong> +251 968 186 308
                    </div>
                    <div class="phone-item">
                        <!-- <strong>:</strong> +251 900 898 989 -->
                    </div>
                </div>

                <div class="divider"></div>

                <div class="email-section">
                    <strong>marsilasepastry@gmail.com</strong>
                </div>

                <div class="community-section">
                    <h4>JOIN OUR COMMUNITY</h4>
                    <p>Stay updated with our latest creations and offers</p>
                </div>
            </div>

            <!-- Right Side - Contact Form -->
            <div class="contact-form">
                <form id="contactForm" action="submit_contact.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <input type="email" class="form-control" name="email" placeholder="E-mail Address" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" class="form-control" name="phone" placeholder="Phone" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        SUBMIT MESSAGE
                    </button>

                    <div id="formMessage" class="form-message"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
:root {
    --orange-cream: #FFA500;
    --orange-light: #FFB74D;
    --orange-dark: #F57C00;
    --cream: #FFF8E1;
    --white: #FFFFFF;
}

/* Testimonials Horizontal Scroll Styles */
.testimonials-title {
    font-size: 2rem;
    font-weight: 300;
    color: #333;
    margin-bottom: 3rem;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.testimonials-scroll-container {
    overflow: hidden;
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 0;
}

.testimonials-scroll-track {
    display: flex;
    gap: 2rem;
    animation: scrollTestimonials 30s linear infinite;
    width: max-content;
}

.testimonial-card {
    background: var(--white);
    padding: 2.5rem 2rem;
    border-radius: 8px;
    text-align: center;
    border: 2px solid var(--cream);
    min-width: 350px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    border-color: var(--orange-light);
}

.testimonial-content {
    font-style: italic;
    color: #555;
    line-height: 1.6;
    font-size: 1.1rem;
    position: relative;
}

.testimonial-content::before {
    content: '"';
    font-size: 4rem;
    color: var(--orange-light);
    position: absolute;
    top: -2rem;
    left: -1rem;
    font-family: serif;
    line-height: 1;
}

@keyframes scrollTestimonials {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(calc(-350px * 3 - 2rem * 3));
    }
}

/* Pause animation on hover */
.testimonials-scroll-container:hover .testimonials-scroll-track {
    animation-play-state: paused;
}

/* Contact Section Styles - No Glow Effects */
.contact-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
    max-width: 1000px;
    margin: 0 auto;
}

.contact-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 2rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.phone-numbers {
    margin-bottom: 2rem;
}

.phone-item {
    margin-bottom: 0.75rem;
    font-size: 1rem;
    color: #555;
}

.phone-item strong {
    color: var(--orange-dark);
    font-weight: 600;
}

.divider {
    height: 1px;
    background: var(--cream);
    margin: 2rem 0;
}

.email-section {
    margin-bottom: 2rem;
}

.email-section strong {
    font-size: 1.1rem;
    color: var(--orange-dark);
    font-weight: 600;
}

.community-section h4 {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.community-section p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.social-links {
    display: flex;
    gap: 10px;
}

.social-btn {
    width: 40px;
    height: 40px;
    border: 2px solid var(--orange-light);
    background: var(--white);
    color: var(--orange-cream);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.social-btn:hover {
    background: var(--orange-cream);
    color: var(--white);
}

/* Form Styles - No Glow Effects */
.contact-form {
    background: var(--white);
    padding: 2rem;
    border-radius: 8px;
    border: 2px solid var(--cream);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--cream);
    border-radius: 4px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: var(--white);
}

.form-control:focus {
    outline: none;
    border-color: var(--orange-cream);
}

.form-control::placeholder {
    color: #999;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.submit-btn {
    width: 100%;
    background: var(--orange-cream);
    color: var(--white);
    border: none;
    padding: 1rem 2rem;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    background: var(--orange-dark);
}

.submit-btn:disabled {
    background: var(--orange-light);
    cursor: not-allowed;
}

.form-message {
    margin-top: 1rem;
    padding: 0.75rem;
    border-radius: 4px;
    text-align: center;
    font-weight: 500;
}

.form-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.form-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .contact-form {
        padding: 1.5rem;
    }
    
    .testimonial-card {
        min-width: 280px;
        padding: 2rem 1.5rem;
    }
    
    @keyframes scrollTestimonials {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(calc(-280px * 3 - 2rem * 3));
        }
    }
}

@media (max-width: 480px) {
    .contact-header h3 {
        font-size: 1rem;
    }
    
    .phone-item {
        font-size: 0.9rem;
    }
    
    .testimonials-title {
        font-size: 1.25rem;
    }
    
    .testimonial-card {
        min-width: 250px;
        padding: 1.5rem 1rem;
    }
    
    .testimonial-content {
        font-size: 1rem;
    }
    
    .testimonial-content::before {
        font-size: 3rem;
        top: -1.5rem;
        left: -0.5rem;
    }
}

/* Existing Styles from Your Home Page */
.bg-light {
    background: var(--neutral-50) !important;
}

.opacity-75 {
    opacity: 0.75;
}

.btn-outline-light {
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    background: transparent;
}

.btn-outline-light:hover {
    background: white;
    color: var(--primary-600);
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.product-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
    border: 1px solid var(--neutral-200);
    position: relative;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--primary-500);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-content {
    padding: 1.5rem;
}

.product-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--neutral-900);
    line-height: 1.3;
}

.product-description {
    color: var(--neutral-600);
    font-size: 0.875rem;
    margin-bottom: 1rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.product-rating {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
}

.product-category .badge {
    font-size: 0.7rem;
    font-weight: 500;
}

.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-600);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(var(--primary-rgb), 0.3);
}

.rounded-3 {
    border-radius: 12px !important;
}

.badge {
    font-size: 0.8rem;
    font-weight: 500;
}
</style>

<script>
// Testimonials Horizontal Scroll JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const scrollTrack = document.querySelector('.testimonials-scroll-track');
    const scrollContainer = document.querySelector('.testimonials-scroll-container');
    
    // Pause animation on hover
    scrollContainer.addEventListener('mouseenter', function() {
        scrollTrack.style.animationPlayState = 'paused';
    });
    
    scrollContainer.addEventListener('mouseleave', function() {
        scrollTrack.style.animationPlayState = 'running';
    });
    
    // Touch swipe functionality for mobile
    let startX = 0;
    let scrollLeft = 0;
    
    scrollContainer.addEventListener('touchstart', function(e) {
        startX = e.touches[0].pageX - scrollContainer.offsetLeft;
        scrollLeft = scrollTrack.scrollLeft;
        scrollTrack.style.animation = 'none';
    });
    
    scrollContainer.addEventListener('touchmove', function(e) {
        if (!startX) return;
        const x = e.touches[0].pageX - scrollContainer.offsetLeft;
        const walk = (x - startX) * 2;
        scrollTrack.style.transform = `translateX(${walk}px)`;
    });
    
    scrollContainer.addEventListener('touchend', function() {
        startX = 0;
        // Resume animation after a delay
        setTimeout(() => {
            scrollTrack.style.animation = 'scrollTestimonials 30s linear infinite';
        }, 3000);
    });

    // Contact Form JavaScript
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const formMessage = document.getElementById('formMessage');

    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'SENDING...';
        formMessage.textContent = '';
        formMessage.className = 'form-message';

        // Simulate form submission
        setTimeout(() => {
            showFormMessage('Thank you! Your message has been sent successfully. We\'ll get back to you within 24 hours.', 'success');
            contactForm.reset();
            
            submitBtn.disabled = false;
            submitBtn.textContent = 'SUBMIT MESSAGE';
        }, 2000);
    });

    function showFormMessage(message, type) {
        formMessage.textContent = message;
        formMessage.className = `form-message ${type}`;
    }

    // Form validation on input
    const formInputs = contactForm.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.style.borderColor = '#c3e6cb';
            } else {
                this.style.borderColor = '#f5c6cb';
            }
        });
    });
});
</script>