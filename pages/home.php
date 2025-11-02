
<div class="container-fluid px-0">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">Marsilase Pastries</h1>
                <p class="hero-subtitle">Where every bite tells a story of craftsmanship and passion</p>
                <div class="hero-buttons">
                    <?php 
                    $cart_count = 0;
                    if (isset($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $cart_count += $item['quantity'] ?? 1;
                        }
                    }
                    ?>
                    <a href="?page=review" class="btn btn-primary">
                        <i class="bi bi-cart3"></i>
                        View Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#featured-cakes" class="btn btn-outline-primary">
                        Explore Menu
                    </a>
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="currentColor"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="currentColor"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="currentColor"></path>
            </svg>
        </div>
    </section>

    <!-- Quick Features -->
    <section class="features-section py-5 glass-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card hover-glow">
                        <div class="feature-icon">
                            <i class="bi bi-star"></i>
                        </div>
                        <h4>Premium Quality</h4>
                        <p class="text-medium">Finest ingredients for exceptional taste</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card hover-glow">
                        <div class="feature-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h4>Fresh Daily</h4>
                        <p class="text-medium">Baked fresh every morning</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card hover-glow">
                        <div class="feature-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h4>Fast Delivery</h4>
                        <p class="text-medium">Quick delivery across the city</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Cakes Section -->
    <section id="featured-cakes" class="py-5">
        <div class="container">
            <h2 class="section-title">Our Signature Cakes</h2>
            <p class="section-subtitle">Handcrafted with love and the finest ingredients</p>

            <div class="product-grid">
                <!-- Featured Cake 1 -->
                <div class="product-card hover-glow">
                    <div class="product-image" style="background: linear-gradient(135deg, #C2865A 0%, #8B6B5E 100%);">
                        <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Chocolate Fantasy Cake">
                        <div class="product-badge">Best Seller</div>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">Chocolate Fantasy</h3>
                        <p class="product-description">Rich chocolate cake with creamy chocolate frosting and decadent toppings</p>
                        <div class="product-price">ETB 1,200</div>
                        <a href="?page=customize-cake&cake_id=1" class="btn btn-primary w-100 hover-glow">
                            <i class="bi bi-magic me-2"></i>Customize & Order
                        </a>
                    </div>
                </div>

                <!-- Featured Cake 2 -->
                <div class="product-card hover-glow">
                    <div class="product-image" style="background: linear-gradient(135deg, #F8E9D2 0%, #C2865A 100%);">
                        <img src="https://images.unsplash.com/photo-1558306783-4e1738d4dc3c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Vanilla Dream Cake">
                        <div class="product-badge">Popular</div>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">Vanilla Dream</h3>
                        <p class="product-description">Light vanilla sponge with buttercream frosting and fresh fruit decorations</p>
                        <div class="product-price">ETB 1,100</div>
                        <a href="?page=customize-cake&cake_id=2" class="btn btn-primary w-100 hover-glow">
                            <i class="bi bi-magic me-2"></i>Customize & Order
                        </a>
                    </div>
                </div>

                <!-- Featured Cake 3 -->
                <div class="product-card hover-glow">
                    <div class="product-image" style="background: linear-gradient(135deg, #FFB6C1 0%, #FF69B4 100%);">
                        <img src="https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Red Velvet Delight">
                        <div class="product-badge">New</div>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">Red Velvet Delight</h3>
                        <p class="product-description">Classic red velvet with cream cheese frosting and elegant decorations</p>
                        <div class="product-price">ETB 1,350</div>
                        <a href="?page=customize-cake&cake_id=3" class="btn btn-primary w-100 hover-glow">
                            <i class="bi bi-magic me-2"></i>Customize & Order
                        </a>
                    </div>
                </div>
            </div>

            <!-- View All Menu Button -->
            <div class="text-center mt-5">
                <button class="btn btn-outline-primary btn-lg hover-glow" id="viewAllMenu">
                    <i class="bi bi-chevron-down me-2"></i>View All Menu
                </button>
            </div>

            <!-- Expanded Menu (Hidden by Default) -->
            <div id="expandedMenu" class="expanded-menu" style="display: none; opacity: 0;">
                <div class="product-grid mt-5">
                    <!-- Additional Cake 4 -->
                    <div class="product-card hover-glow">
                        <div class="product-image" style="background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);">
                            <img src="https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Caramel Crunch Cake">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">Caramel Crunch</h3>
                            <p class="product-description">Moist caramel cake with crunchy toppings and caramel drizzle</p>
                            <div class="product-price">ETB 1,250</div>
                            <a href="?page=customize-cake&cake_id=4" class="btn btn-primary w-100 hover-glow">
                                <i class="bi bi-magic me-2"></i>Customize & Order
                            </a>
                        </div>
                    </div>

                    <!-- Additional Cake 5 -->
                    <div class="product-card hover-glow">
                        <div class="product-image" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);">
                            <img src="https://images.unsplash.com/photo-1571115764595-644a1f56a55c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Lemon Zest Cake">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">Lemon Zest</h3>
                            <p class="product-description">Tangy lemon cake with citrus frosting and lemon zest</p>
                            <div class="product-price">ETB 1,150</div>
                            <a href="?page=customize-cake&cake_id=5" class="btn btn-primary w-100 hover-glow">
                                <i class="bi bi-magic me-2"></i>Customize & Order
                            </a>
                        </div>
                    </div>

                    <!-- Additional Cake 6 -->
                    <div class="product-card hover-glow">
                        <div class="product-image" style="background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%);">
                            <img src="https://images.unsplash.com/photo-1519861155730-0a1d6d4ebf02?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Strawberry Bliss Cake">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">Strawberry Bliss</h3>
                            <p class="product-description">Fresh strawberry cake with cream and real strawberry pieces</p>
                            <div class="product-price">ETB 1,300</div>
                            <a href="?page=customize-cake&cake_id=6" class="btn btn-primary w-100 hover-glow">
                                <i class="bi bi-magic me-2"></i>Customize & Order
                            </a>
                        </div>
                    </div>

                    <!-- Additional Cake 7 -->
                    <div class="product-card hover-glow">
                        <div class="product-image" style="background: linear-gradient(135deg, #4B0082 0%, #9370DB 100%);">
                            <img src="https://images.unsplash.com/photo-1535254973040-607b474cb50d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Blueberry Cheesecake">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">Blueberry Cheesecake</h3>
                            <p class="product-description">Creamy cheesecake with blueberry compote and graham crust</p>
                            <div class="product-price">ETB 1,400</div>
                            <a href="?page=customize-cake&cake_id=7" class="btn btn-primary w-100 hover-glow">
                                <i class="bi bi-magic me-2"></i>Customize & Order
                            </a>
                        </div>
                    </div>

                    <!-- Additional Cake 8 -->
                    <div class="product-card hover-glow">
                        <div class="product-image" style="background: linear-gradient(135deg, #654321 0%, #A0522D 100%);">
                            <img src="https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Coffee Mocha Cake">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">Coffee Mocha</h3>
                            <p class="product-description">Rich coffee-flavored cake with mocha buttercream</p>
                            <div class="product-price">ETB 1,280</div>
                            <a href="?page=customize-cake&cake_id=8" class="btn btn-primary w-100 hover-glow">
                                <i class="bi bi-magic me-2"></i>Customize & Order
                            </a>
                        </div>
                    </div>

                    <!-- Additional Cake 9 -->
                    <div class="product-card hover-glow">
                        <div class="product-image" style="background: linear-gradient(135deg, #FFE4E1 0%, #FFB6C1 100%);">
                            <img src="https://images.unsplash.com/photo-1571115764595-644a1f56a55c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Raspberry White Chocolate">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">Raspberry White Chocolate</h3>
                            <p class="product-description">White chocolate cake with raspberry filling and cream</p>
                            <div class="product-price">ETB 1,450</div>
                            <a href="?page=customize-cake&cake_id=9" class="btn btn-primary w-100 hover-glow">
                                <i class="bi bi-magic me-2"></i>Customize & Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 glass-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title text-start">Our Story</h2>
                    <p class="lead text-medium mb-4">
                        For over a decade, Marsilase Pastries has been crafting unforgettable moments through our exquisite cakes and desserts.
                    </p>
                    <p class="text-medium mb-4">
                        Our master bakers combine traditional techniques with innovative flavors to create cakes that not only look stunning but taste extraordinary. Every ingredient is carefully selected, and every cake is baked with passion.
                    </p>
                    <div class="feature-list">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span class="text-medium">100% Natural Ingredients</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span class="text-medium">Handcrafted Daily</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-5"></i>
                            <span class="text-medium">Custom Designs Available</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-image rounded-4 shadow-card-lg hover-glow" style="height: 500px; background: linear-gradient(135deg, #C2865A 0%, #8B6B5E 100%); overflow: hidden;">
                        <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Our Bakery" class="w-100 h-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5">
        <div class="container">
            <h2 class="section-title">Sweet Words From Our Customers</h2>
            <p class="section-subtitle">Don't just take our word for it</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card glass-card rounded-4 p-4 hover-glow">
                        <div class="rating mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="testimonial-text text-medium mb-4">
                            "The Chocolate Fantasy cake was the highlight of my daughter's birthday! Everyone couldn't stop talking about how delicious it was."
                        </p>
                        <div class="customer-info d-flex align-items-center">
                            <div class="customer-avatar rounded-circle me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #C2865A 0%, #8B6B5E 100%);"></div>
                            <div>
                                <h6 class="mb-0 text-dark">Sarah M.</h6>
                                <small class="text-muted">Addis Ababa</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card glass-card rounded-4 p-4 hover-glow">
                        <div class="rating mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="testimonial-text text-medium mb-4">
                            "I've ordered from Marsilase multiple times for office events. The cakes are always fresh, beautiful, and absolutely delicious!"
                        </p>
                        <div class="customer-info d-flex align-items-center">
                            <div class="customer-avatar rounded-circle me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #C2865A 0%, #8B6B5E 100%);"></div>
                            <div>
                                <h6 class="mb-0 text-dark">Michael T.</h6>
                                <small class="text-muted">Business Owner</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card glass-card rounded-4 p-4 hover-glow">
                        <div class="rating mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="testimonial-text text-medium mb-4">
                            "The customization options are amazing! They created exactly what I envisioned for our wedding cake. Perfect in every way."
                        </p>
                        <div class="customer-info d-flex align-items-center">
                            <div class="customer-avatar rounded-circle me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #C2865A 0%, #8B6B5E 100%);"></div>
                            <div>
                                <h6 class="mb-0 text-dark">Elena K.</h6>
                                <small class="text-muted">Newlywed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 glass-section">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <p class="section-subtitle">We'd love to hear from you</p>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="contact-card glass-card rounded-4 p-5 hover-glow">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="contact-info text-center p-4">
                                    <i class="bi bi-geo-alt-fill fs-1 text-primary mb-3"></i>
                                    <h5>Visit Our Store</h5>
                                    <p class="text-medium">Bole Road, Addis Ababa</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info text-center p-4">
                                    <i class="bi bi-telephone-fill fs-1 text-primary mb-3"></i>
                                    <h5>Call Us</h5>
                                    <p class="text-medium">+251 911 234 567</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <p class="text-medium mb-4">Open daily from 8:00 AM to 8:00 PM</p>
                            <a href="tel:+251911234567" class="btn btn-primary hover-glow">
                                <i class="bi bi-telephone me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // View All Menu functionality
    document.getElementById('viewAllMenu').addEventListener('click', function() {
        const expandedMenu = document.getElementById('expandedMenu');
        const button = this;
        
        if (expandedMenu.style.display === 'none') {
            // Show expanded menu
            expandedMenu.style.display = 'block';
            setTimeout(() => {
                expandedMenu.style.opacity = '1';
                expandedMenu.style.transition = 'opacity 0.6s ease';
                button.innerHTML = '<i class="bi bi-chevron-up me-2"></i>Show Less';
            }, 10);
            
            // Animate product cards in expanded menu
            setTimeout(() => {
                const productCards = expandedMenu.querySelectorAll('.product-card');
                productCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add('visible');
                    }, index * 200);
                });
            }, 300);
        } else {
            // Hide expanded menu
            expandedMenu.style.opacity = '0';
            setTimeout(() => {
                expandedMenu.style.display = 'none';
                button.innerHTML = '<i class="bi bi-chevron-down me-2"></i>View All Menu';
            }, 600);
        }
    });

    // Initialize product cards animation
    document.addEventListener('DOMContentLoaded', function() {
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('visible');
            }, index * 300);
        });
    });
</script>

<style>
    .hero-wave {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        overflow: hidden;
        line-height: 0;
        transform: rotate(180deg);
    }

    .hero-wave svg {
        position: relative;
        display: block;
        width: calc(100% + 1.3px);
        height: 80px;
        color: var(--primary-medium);
    }

    .expanded-menu {
        transition: opacity 0.6s ease;
    }

    .testimonial-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .testimonial-card:hover {
        transform: translateY(-5px);
    }

    .contact-info {
        transition: transform 0.3s ease;
    }

    .contact-info:hover {
        transform: translateY(-3px);
    }

    .about-image {
        transition: transform 0.3s ease;
    }

    .about-image:hover {
        transform: scale(1.02);
    }
</style>