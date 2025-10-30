<section id="hero" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="hero-title">Marsilase Pastry</h1>
                <p class="hero-subtitle">Premium handcrafted cakes made with love and the finest ingredients. Experience elegance in every bite.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#menu" class="btn btn-primary btn-lg">
                        <i class="bi bi-cake2 me-2"></i>Order Now
                    </a>
                    <a href="#about" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="hero-visual">
                    <i class="bi bi-cake2 display-1 text-gradient" style="font-size: 8rem;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Section -->
<section id="menu" class="menu-section py-5">
    <div class="container">
        <h2 class="section-title">Our Premium Cakes</h2>
        <p class="section-subtitle">Handcrafted with love and the finest ingredients</p>
        
        <!-- Initial Cakes Display (First 3 cakes only) -->
        <div class="product-grid">
            <?php 
            $cake_count = 0;
            foreach ($cakes as $cake): 
                $cake_count++;
                if ($cake_count > 3) break; // Show only first 3 cakes initially
            ?>
            <div class="product-card fade-in-up">
                <div class="product-image" style="background: <?= $cake['color'] ?>;">
                    <?php if (!empty($cake['image_url'])): ?>
                        <img src="<?= $cake['image_url'] ?>" alt="<?= htmlspecialchars($cake['name']) ?>">
                    <?php else: ?>
                        <i class="bi bi-cake2"></i>
                    <?php endif; ?>
                </div>
                <div class="product-content">
                    <h3 class="product-title"><?= htmlspecialchars($cake['name']) ?></h3>
                    <p class="product-description"><?= htmlspecialchars($cake['description']) ?></p>
                    <div class="product-price">Birr <?= number_format($cake['price'], 2) ?></div>
                    <a href="?page=customize-cake&cake_id=<?= $cake['id'] ?>" class="btn btn-primary w-100">
                        <i class="bi bi-gear me-2"></i>Add to Cart
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- View All Menu Button -->
        <?php if (count($cakes) > 3): ?>
        <div class="text-center mt-5">
            <a href="?page=full-menu" class="btn btn-outline-primary btn-lg px-5 py-3" id="view-all-btn">
                <i class="bi bi-grid-3x3-gap me-2"></i>View All Menu
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- About Section -->
<section id="about" class="about-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="section-title">About Marsilase Pastry</h2>
                <p class="section-subtitle">Elegance in Every Bite</p>
                <p class="text-muted">Founded in 2010, Marsilase Pastry has been dedicated to crafting premium handcrafted cakes that bring joy to every celebration. Our passion for baking and commitment to quality ingredients ensure that each cake is a masterpiece of flavor and design.</p>
                <p class="text-muted">From classic flavors to innovative creations, we tailor each cake to your unique vision. Experience the art of fine baking with Marsilase Pastry.</p>
            </div>
            <div class="col-lg-6 text-center">
                <div class="about-visual">
                    <i class="bi bi-shop-window display-1 text-gradient" style="font-size: 8rem;"></i>
                </div>
            </div>
        </div>  
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Why Choose Marsilase Pastry?</h2>
        <p class="section-subtitle">Excellence in every detail</p>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-star"></i>
                    </div>
                    <h4>Premium Quality</h4>
                    <p class="text-muted">We use only the finest ingredients to create exceptional cakes that delight your senses.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4>Free Delivery</h4>
                    <p class="text-muted">Enjoy complimentary delivery for orders over Birr 500. Fresh to your doorstep.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Quality Guarantee</h4>
                    <p class="text-muted">100% satisfaction guaranteed. We stand behind the quality of every cake we create.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<?php include 'testimonials.php'; ?>

<!-- Contact Section -->
<section id="contact" class="contact-section py-5">
    <div class="container">
        <h2 class="section-title">Get In Touch</h2>
        <p class="section-subtitle">We'd love to hear from you</p>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-card-lg">
                    <div class="card-body p-5">
                        <div class="row text-center">
                            <div class="col-md-4 mb-4">
                                <div class="feature-icon mx-auto" style="width: 60px; height: 60px;">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <h5>Call Us</h5>
                                <p class="text-muted">+251-967-318-674</p>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="feature-icon mx-auto" style="width: 60px; height: 60px;">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <h5>Email Us</h5>
                                <p class="text-muted">marsilasepastry@gmail.com</p>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="feature-icon mx-auto" style="width: 60px; height: 60px;">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <h5>Visit Us</h5>
                                <p class="text-muted">Narzät, Ethiopia</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Enhanced button styling */
    #view-all-btn {
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid var(--primary);
        font-weight: 600;
        font-size: 1.1rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    #view-all-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
        background: var(--primary);
        color: var(--dark);
    }
</style>