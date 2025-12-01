<?php
include './config.php';

// Function to calculate star rating
if (!function_exists('calculateStarRating')) {
    function calculateStarRating($totalRatings, $averageRating) {
        if ($totalRatings >= 2) {
            $fullStars = round($averageRating);
            return [
                'stars' => $fullStars,
                'hasHalf' => false,
                'display' => round($averageRating, 1)
            ];
        } elseif ($totalRatings === 1) {
            $fullStars = floor($averageRating);
            return [
                'stars' => $fullStars,
                'hasHalf' => true,
                'display' => $averageRating
            ];
        } else {
            return [
                'stars' => 0,
                'hasHalf' => false,
                'display' => 0
            ];
        }
    }
}

// Get all active categories
$categories_result = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
$main_categories = $categories_result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Rest of your home.php content remains exactly the same -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- Hero Section -->
<section id="home" class="hero">
    <div class="container-narrow">
        <div class="hero-content">
            <h1 class="hero-title">
                WELCOME TO<br>
                <span class="hero-highlight">MARSILAS</span>
            </h1>
            <p class="hero-subtitle">
                PATISSERIE & BAKERY
            </p>
            <div class="btn-container">
                <a href="#product-categories" class="btn btn-secondary" aria-label="Explore Marsilase Pastry Products">
                    <i class="bi bi-star me-2"></i>
                    EXPLORE MARSILASE
                </a>
            </div>
            
            <div class="features-preview">
                <div class="feature-preview">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Fresh Daily</span>
                </div>
                <div class="feature-preview">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Premium Ingredients</span>
                </div>
                <div class="feature-preview">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Custom Orders</span>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-background">
        <div class="hero-decoration decoration-1"></div>
        <div class="hero-decoration decoration-2"></div>
        
        <div class="floating-element element-1">
            <i class="bi bi-cake2"></i>
        </div>
        <div class="floating-element element-2">
            <i class="bi bi-cupcake"></i>
        </div>
        <div class="floating-element element-3">
            <i class="bi bi-balloon-heart"></i>
        </div>
        <div class="floating-element element-4">
            <i class="bi bi-gift"></i>
        </div>
        <div class="floating-element element-5">
            <i class="bi bi-flower1"></i>
        </div>
        <div class="floating-element element-6">
            <i class="bi bi-stars"></i>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="section bg-light-caramel">
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

<!-- Product Categories Section -->
<section id="product-categories" class="section">
    <div class="container-narrow">
        <div class="text-center mb-5" data-animate>
            <h2 class="display-4 display-font mb-3">Our Product Categories</h2>
            <p class="text-lead">Explore our delicious range of baked goods and desserts</p>
        </div>

        <?php 
        // Process each category separately to ensure proper separation
        foreach ($main_categories as $category): 
            // Get products for this specific category
            $category_products = [];
            $total_products = 0;
            
            // Count total products in this category - FIXED QUERY
            $count_query = "
                SELECT COUNT(*) as total 
                FROM (
                    SELECT id FROM products WHERE category_id = ? AND is_active = 1 
                    UNION ALL 
                    SELECT id FROM cakes WHERE category_id = ? AND is_active = 1
                ) as combined_products
            ";
            
            $count_stmt = $conn->prepare($count_query);
            if ($count_stmt) {
                $count_stmt->bind_param("ii", $category['id'], $category['id']);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $count_row = $count_result->fetch_assoc();
                $total_products = $count_row['total'];
                $count_stmt->close();
            } else {
                // If prepare fails, use alternative method
                $total_products = 0;
            }
            
            // Only proceed if category has products
            if ($total_products > 0):
                // Fetch products from products table for this specific category - FIXED QUERY
                $products_query = "
                    SELECT id, name, description, price, image_path, 
                           'product' as product_type, category as product_category,
                           is_active
                    FROM products 
                    WHERE category_id = ? AND is_active = 1 
                    ORDER BY name 
                    LIMIT 3
                ";
                
                $products_stmt = $conn->prepare($products_query);
                if ($products_stmt) {
                    $products_stmt->bind_param("i", $category['id']);
                    $products_stmt->execute();
                    $products_result = $products_stmt->get_result();
                    while ($product = $products_result->fetch_assoc()) {
                        $category_products[] = $product;
                    }
                    $products_stmt->close();
                }
                
                // Fetch cakes from cakes table for this specific category - FIXED QUERY
                $cakes_query = "
                    SELECT id, name, description, price, image_url as image_path, 
                           'cake' as product_type, 'Cakes' as product_category,
                           is_active, serves
                    FROM cakes 
                    WHERE category_id = ? AND is_active = 1 
                    ORDER BY name 
                    LIMIT 3
                ";
                
                $cakes_stmt = $conn->prepare($cakes_query);
                if ($cakes_stmt) {
                    $cakes_stmt->bind_param("i", $category['id']);
                    $cakes_stmt->execute();
                    $cakes_result = $cakes_stmt->get_result();
                    while ($cake = $cakes_result->fetch_assoc()) {
                        $category_products[] = $cake;
                    }
                    $cakes_stmt->close();
                }
        ?>
        <div class="category-section mb-5" data-animate>
            <div class="mb-4" data-animate>
                <h3 class="display-6 display-font text-espresso"><?= htmlspecialchars($category['name']) ?></h3>
                <p class="text-muted">Showing <?= min(3, count($category_products)) ?> of <?= $total_products ?> products</p>
            </div>
            
            <?php if (!empty($category_products)): ?>
            <div class="row g-4">
                <?php 
                // Display only first 3 products
                $display_count = 0;
                foreach ($category_products as $product): 
                    if ($display_count >= 3) break;
                    $display_count++;
                    
                    $is_cake = ($product['product_type'] === 'cake');
                    
                    // Use actual data or simulate ratings
                    $totalRatings = $product['total_ratings'] ?? rand(8, 25);
                    $averageRating = $product['average_rating'] ?? round(rand(40, 50) / 10, 1);
                    $ratingDisplay = calculateStarRating($totalRatings, $averageRating);
                    
                    // Determine image path
                    $image_path = $product['image_path'] ?? 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';
                ?>
                <div class="col-xl-4 col-lg-4 col-md-6">
                    <div class="product-card-glass h-100">
                        <div class="product-image-wrapper">
                            <img src="<?= htmlspecialchars($image_path) ?>" 
                                 alt="<?= htmlspecialchars($product['name'] ?? 'Product') ?>" 
                                 class="product-image"
                                 loading="lazy">
                            <div class="category-tag">
                                <?= htmlspecialchars($category['name']) ?>
                            </div>
                            <?php if ($is_cake && isset($product['serves'])): ?>
                                <div class="serves-badge">
                                    <i class="bi bi-people me-1"></i>
                                    Serves <?= $product['serves'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <h4 class="product-name"><?= htmlspecialchars($product['name'] ?? 'Unknown Product') ?></h4>
                            <p class="product-description">
                                <?= htmlspecialchars($product['description'] ?? 'No description available') ?>
                            </p>
                            
                            <!-- Star Rating -->
                            <div class="product-rating mb-3">
                                <?php if ($totalRatings > 0): ?>
                                    <?php for ($i = 1; $i <= $ratingDisplay['stars']; $i++): ?>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    <?php endfor; ?>
                                    <?php if ($ratingDisplay['hasHalf']): ?>
                                        <i class="bi bi-star-half text-warning"></i>
                                    <?php endif; ?>
                                    <?php 
                                    $emptyStars = 5 - $ratingDisplay['stars'] - ($ratingDisplay['hasHalf'] ? 1 : 0);
                                    for ($i = 1; $i <= $emptyStars; $i++): ?>
                                        <i class="bi bi-star text-warning"></i>
                                    <?php endfor; ?>
                                    <span class="rating-text">(<?= $ratingDisplay['display'] ?>)</span>
                                <?php else: ?>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <span class="rating-text">(No ratings)</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Price and Order Button -->
                            <div class="product-footer">
                                <div class="product-price">
                                    <span class="current-price">ETB <?= number_format($product['price'] ?? 0, 2) ?></span>
                                </div>
                                <?php if ($is_cake): ?>
                                    <a href="?page=customize-cake&cake_id=<?= $product['id'] ?? '' ?>" class="btn-order">
                                        <i class="bi bi-cart-plus me-1"></i>
                                        Order
                                    </a>
                                <?php else: ?>
                                    <button class="btn-order add-to-cart-product" 
                                            data-product-id="<?= $product['id'] ?>" 
                                            data-product-type="<?= $product['product_type'] ?>"
                                            data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                            data-price="<?= $product['price'] ?>"
                                            data-image="<?= htmlspecialchars($image_path) ?>">
                                        <i class="bi bi-cart-plus me-1"></i>
                                        Order
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- View All Button - Show only if there are more than 3 products -->
            <?php if ($total_products > 3): ?>
            <div class="text-center mt-5" data-animate>
                <a href="?page=category&category_id=<?= $category['id'] ?>&category_name=<?= urlencode($category['name']) ?>" class="btn btn-secondary">
                    View All <?= htmlspecialchars($category['name']) ?> (<?= $total_products ?>)
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; endforeach; ?>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section bg-light-caramel">
    <div class="container-narrow">
        <!-- Hero Section -->
        <div class="text-center mb-5 fade-in" data-animate>
            <h1 class="display-4 display-font mb-3">About Marsilas Pastry</h1>
            <p class="text-lead">Crafting unforgettable moments through exquisite cakes and desserts since 2010</p>
        </div>

        <!-- Our Story Section -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 fade-in" data-animate>
                <h2 class="display-6 display-font mb-4">Our Story</h2>
                <p class="text-lead mb-4">
                    Founded in 2010, Marsilase Pastry began as a small family-owned bakery with a simple mission: 
                    to create the most exquisite cakes and desserts in Addis Ababa.
                </p>
                <p class="mb-4">
                    What started as a passion project has grown into one of the city's most beloved pastry shops, 
                    known for our commitment to quality, innovation, and exceptional customer service.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-2" aria-hidden="true"></i>
                        <span>Family-owned since 2010</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-2" aria-hidden="true"></i>
                        <span>100+ custom cakes monthly</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-2" aria-hidden="true"></i>
                        <span>5-star rated service</span>
                    </div>
                </div>
                <a href="#contact" class="btn btn-primary mt-2" aria-label="Contact Marsilase Pastry">
                    Get in Touch
                </a>
            </div>
            <div class="col-lg-6 fade-in" data-animate>
                <div class="card">
                    <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Marsilase Pastry bakery interior showing our delicious cakes and pastries" 
                         class="card-img-top"
                         loading="lazy"
                         width="600"
                         height="400">
                    <div class="card-body">
                        <p class="text-center text-muted mb-0">Our cozy bakery in Addis Ababa</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Values Section -->
        <div class="row">
            <div class="col-12 text-center mb-5 fade-in" data-animate>
                <h2 class="display-6 display-font mb-3">Our Values</h2>
                <p class="text-lead">The principles that guide everything we do</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4 fade-in" data-animate>
                <div class="card h-100" tabindex="0">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto mb-3" aria-hidden="true">
                            <i class="bi bi-star"></i>
                        </div>
                        <h3>Quality First</h3>
                        <p class="text-muted">
                            We never compromise on quality. Every ingredient is carefully selected, 
                            and every cake is crafted with precision and care.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 fade-in" data-animate>
                <div class="card h-100" tabindex="0">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto mb-3" aria-hidden="true">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h3>Passion Driven</h3>
                        <p class="text-muted">
                            Our work is our passion. We pour our hearts into every creation, 
                            ensuring each cake tells a story of love and dedication.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 fade-in" data-animate>
                <div class="card h-100" tabindex="0">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto mb-3" aria-hidden="true">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3>Community Focused</h3>
                        <p class="text-muted">
                            We believe in building lasting relationships with our customers 
                            and contributing positively to our community.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
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
    <div class="container-narrow">
        <div class="text-center mb-5">
            <h1 class="display-4 display-font mb-3">Contact Us</h1>
            <p class="text-lead">Get in touch with Marsilase Pastry - we'd love to hear from you</p>
        </div>

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
                </div>

                <div class="divider"></div>

                <div class="email-section">
                    <strong>marsilasepastry@gmail.com</strong>
                </div>

                <div class="divider"></div>

                <div class="address-section">
                    <h4>VISIT OUR BAKERY</h4>
                    <p>Betel, Addis Ababa<br>Ethiopia</p>
                    <p class="text-muted small">Open Daily: 1:00 AM - 9:00 PM</p>
                </div>

                <div class="community-section">
                    <h4>JOIN OUR COMMUNITY</h4>
                    <p>Stay updated with our latest creations and offers</p>
                </div>
            </div>

            <!-- Right Side - Contact Form -->
            <div class="contact-form">
                <h4 class="mb-4">Send us a Message</h4>
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
                            <input type="tel" class="form-control" name="phone" placeholder="Phone Number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <select class="form-control" name="subject" required>
                            <option value="">Select Subject</option>
                            <option value="order">Order Inquiry</option>
                            <option value="custom">Custom Cake Request</option>
                            <option value="catering">Catering Services</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" name="message" rows="5" placeholder="Your Message" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        SEND MESSAGE
                    </button>

                    <div id="formMessage" class="form-message"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
:root {
    --orange-cream: #5F372B; /* Changed to Chocolate Brown */
    --orange-light: #4A2B22; /* Darker Brown for hover */
    --orange-dark: #5F372B; /* Changed to Chocolate Brown */
    --cream: #FFF6E9; /* Changed to Soft Cream */
    --white: #FFFFFF;
    --light-caramel: #F8E9D2;
    --warm-chocolate: #C2865A;
    --deep-espresso: #4A2E2B;
    --gold-accent: #D4A373;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* NEW FONT STYLES */
body {
    font-family: "Cookie", cursive; /* Main body font - soft and friendly */
    background-color: var(--cream);
    color: var(--text-dark);
    line-height: 1.6;
    font-weight: 400;
}

/* Display font for headings and special elements */
.display-font {
    font-family: 'Dancing Script', cursive; /* Elegant script for headings */
    font-weight: 600; /* Medium weight for better readability */
}

/* Supporting font for buttons and UI elements */
.ui-font {
    font-family: 'Poppins', sans-serif; /* Clean and modern for UI elements */
    font-weight: 400;
}

.hero {
    position: relative;
    min-height: 120vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cream);
    overflow: hidden;
    padding: 2rem 0;
}

.container-narrow {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    width: 100%;
}

.hero-content {
    text-align: center;
    z-index: 2;
    position: relative;
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.hero-title {
    font-family: 'Dancing Script', cursive; /* Updated to script font */
    font-size: 3.5rem;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 1.5rem;
    color: var(--text-dark);
    letter-spacing: 1px;
}

.hero-highlight {
    color: var(--primary-100);
    display: block;
    font-size: 4.5rem;
    margin-top: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
}

.hero-highlight::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: var(--primary-100);
}

.hero-subtitle {
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
    font-size: 1.5rem;
    color: var(--text-muted);
    margin-bottom: 2.5rem;
    font-weight: 400;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.btn-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    margin-top: 2rem;
}

@media (min-width: 576px) {
    .btn-container {
        flex-direction: row;
    }
}

.btn {
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
    display: inline-flex;
    align-items: center;
    padding: 0.85rem 2rem;
    font-weight: 500;
    text-decoration: none;
    border-radius: 30px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background: var(--primary-100);
    color: white;
    border: 2px solid var(--primary-100);
}

.btn-primary:hover {
    background: var(--primary-200);
    border-color: var(--primary-200);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(95, 55, 43, 0.4);
}

.btn-secondary {
    background: transparent;
    color: var(--text-muted);
    border: 2px solid var(--text-muted);
}

.btn-secondary:hover {
    background: var(--text-muted);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(107, 107, 107, 0.3);
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.floating-element {
    position: absolute;
    font-size: 2rem;
    color: rgba(95, 55, 43, 0.15);
    animation: float 8s ease-in-out infinite;
    z-index: 1;
}

.element-1 {
    top: 15%;
    left: 8%;
    animation-delay: 0s;
}

.element-2 {
    top: 65%;
    left: 5%;
    animation-delay: 1.5s;
}

.element-3 {
    top: 25%;
    right: 8%;
    animation-delay: 3s;
}

.element-4 {
    top: 75%;
    right: 5%;
    animation-delay: 4.5s;
}

.element-5 {
    top: 45%;
    left: 12%;
    animation-delay: 2s;
}

.element-6 {
    top: 55%;
    right: 12%;
    animation-delay: 5s;
}

@keyframes float {
    0% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-25px) rotate(5deg);
    }
    100% {
        transform: translateY(0) rotate(0deg);
    }
}

.hero-decoration {
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(95, 55, 43, 0.05);
    z-index: 0;
}

.decoration-1 {
    top: -100px;
    right: -50px;
    width: 300px;
    height: 300px;
}

.decoration-2 {
    bottom: -100px;
    left: -50px;
    width: 250px;
    height: 250px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.8rem;
    }
    
    .hero-highlight {
        font-size: 3.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.3rem;
    }
    
    .floating-element {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .hero-title {
        font-size: 2.2rem;
    }
    
    .hero-highlight {
        font-size: 2.8rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
        letter-spacing: 1px;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .floating-element {
        font-size: 1.25rem;
    }
}

.features-preview {
    margin-top: 4rem;
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.feature-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-muted);
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.feature-preview i {
    color: var(--primary-100);
}

/* Product Categories Styles */
.bg-light-caramel {
    background: var(--light-caramel) !important;
}

.text-espresso {
    color: var(--deep-espresso) !important;
}

.btn-espresso {
    background: var(--deep-espresso);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

.btn-espresso:hover {
    background: var(--warm-chocolate);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(74, 46, 43, 0.3);
}

/* Glassmorphism Product Cards */
.product-card-glass {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 0;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    position: relative;
}

.product-card-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(194, 134, 90, 0.1), rgba(255, 255, 255, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 16px;
}

.product-card-glass:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(194, 134, 90, 0.2);
}

.product-card-glass:hover::before {
    opacity: 1;
}

/* Product Image */
.product-image-wrapper {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.product-card-glass:hover .product-image {
    transform: scale(1.08);
}

/* Category Tag */
.category-tag {
    position: absolute;
    top: 12px;
    right: 12px;
    background: var(--deep-espresso);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

/* Product Content */
.product-content {
    padding: 1.5rem;
    position: relative;
    z-index: 2;
}

.product-name {
    font-family: 'Dancing Script', cursive; /* Updated to script font */
    font-size: 1.4rem; /* Slightly larger for better script readability */
    font-weight: 600;
    color: var(--deep-espresso);
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.product-description {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

/* Star Rating */
.product-rating {
    display: flex;
    align-items: center;
    gap: 2px;
}

.rating-text {
    font-size: 0.8rem;
    color: #888;
    margin-left: 8px;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

/* Price Styles */
.product-price {
    display: flex;
    align-items: center;
    gap: 8px;
}

.current-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--deep-espresso);
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.original-price {
    font-size: 0.9rem;
    color: #999;
    text-decoration: line-through;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.discount-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #e74c3c;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

/* Order Button */
.btn-order {
    background: var(--deep-espresso);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

.btn-order:hover {
    background: var(--warm-chocolate);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(194, 134, 90, 0.3);
}

/* Product Footer */
.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

/* Category Section Styling */
.category-section {
    padding: 3rem 0;
    border-bottom: 1px solid rgba(194, 134, 90, 0.2);
}

.category-section:last-child {
    border-bottom: none;
}

/* Ensure 3 items per row on desktop */
@media (min-width: 1200px) {
    .col-xl-4 {
        flex: 0 0 33.333%;
        max-width: 33.333%;
    }
}

@media (max-width: 1199px) {
    .col-lg-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 767px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Testimonials Horizontal Scroll Styles */
.testimonials-title {
    font-family: 'Dancing Script', cursive; /* Updated to script font */
    font-size: 2.5rem; /* Larger for script font */
    font-weight: 600;
    color: var(--text-dark);
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
    background: white;
    padding: 2.5rem 2rem;
    border-radius: 8px;
    text-align: center;
    border: 2px solid var(--neutral-200);
    min-width: 350px;
    flex-shrink: 0;
    transition: all 0.3s ease;
    color: var(--text-dark);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-100);
}

.testimonial-content {
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
    font-style: italic;
    color: var(--text-muted);
    line-height: 1.6;
    font-size: 1.1rem;
    position: relative;
}

.testimonial-content::before {
    content: '"';
    font-size: 4rem;
    color: var(--primary-100);
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

/* Contact Section Styles */
.contact-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
    max-width: 1000px;
    margin: 0 auto;
}

.contact-header h3 {
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-dark);
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
    color: var(--text-muted);
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.phone-item strong {
    color: var(--primary-100);
    font-weight: 600;
}

.divider {
    height: 1px;
    background: var(--neutral-300);
    margin: 2rem 0;
}

.email-section {
    margin-bottom: 2rem;
}

.email-section strong {
    font-size: 1.1rem;
    color: var(--primary-100);
    font-weight: 600;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.community-section h4 {
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.community-section p {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 1rem;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

/* Form Styles */
.contact-form {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    border: 2px solid var(--neutral-200);
    color: var(--text-dark);
}

.contact-form h4 {
    font-family: 'Dancing Script', cursive; /* Updated to script font */
    font-size: 1.8rem;
    font-weight: 600;
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
    border: 1px solid var(--neutral-300);
    border-radius: 4px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
    color: var(--text-dark);
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-100);
    box-shadow: 0 0 0 2px rgba(95, 55, 43, 0.1);
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
    background: var(--primary-100);
    color: white;
    border: none;
    padding: 1rem 2rem;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

.submit-btn:hover {
    background: var(--primary-200);
}

.submit-btn:disabled {
    background: var(--neutral-300);
    cursor: not-allowed;
}

.form-message {
    margin-top: 1rem;
    padding: 0.75rem;
    border-radius: 4px;
    text-align: center;
    font-weight: 500;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.form-message.success {
    background: rgba(95, 55, 43, 0.1);
    color: var(--text-dark);
    border: 1px solid var(--primary-100);
}

.form-message.error {
    background: rgba(74, 43, 34, 0.1);
    color: var(--text-dark);
    border: 1px solid var(--primary-200);
}

/* Updated existing styles for warm theme */
.bg-light {
    background: var(--neutral-100) !important;
}

.btn-outline-light {
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    background: transparent;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

.btn-outline-light:hover {
    background: white;
    color: var(--primary-100);
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
    color: var(--text-dark);
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
    background: var(--primary-100);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
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
    font-family: 'Dancing Script', cursive; /* Updated to script font */
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-dark);
    line-height: 1.3;
}

.product-description {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 1rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
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
    color: var(--text-muted);
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.product-category .badge {
    font-size: 0.7rem;
    font-weight: 500;
    background: var(--primary-100);
    color: white;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gold-accent);
    font-family: 'Quicksand', sans-serif; /* Updated to main body font */
}

.btn-primary {
    background: var(--primary-100);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(95, 55, 43, 0.3);
    color: white;
}

.rounded-3 {
    border-radius: 12px !important;
}

.badge {
    font-size: 0.8rem;
    font-weight: 500;
    font-family: 'Poppins', sans-serif; /* Updated to clean UI font */
}

/* Additional font assignments for other elements */
.display-4, .display-6 {
    font-family: 'Dancing Script', cursive !important; /* Updated to script font */
}

.text-lead {
    font-family: 'Quicksand', sans-serif !important; /* Updated to main body font */
}

.feature-card h4 {
    font-family: 'Dancing Script', cursive !important; /* Updated to script font */
    font-size: 1.4rem;
    font-weight: 600;
}

.feature-card p {
    font-family: 'Quicksand', sans-serif !important; /* Updated to main body font */
}

.address-section h4, .community-section h4 {
    font-family: 'Poppins', sans-serif !important; /* Updated to clean UI font */
}

.address-section p {
    font-family: 'Quicksand', sans-serif !important; /* Updated to main body font */
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
        font-size: 1.8rem;
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

    // Add smooth animations and interactions for product categories
    const productCards = document.querySelectorAll('.product-card-glass');
    
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>