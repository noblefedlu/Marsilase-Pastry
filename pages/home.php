<div class="container-fluid px-4">
    <!-- Hero Section -->
    <div class="hero-section fade-in-up">
        <div class="hero-content text-center">
            <h1 class="hero-title">Artisanal Pastries & Desserts</h1>
            <p class="hero-subtitle">Handcrafted with passion, delivered with perfection. Experience the finest cakes, ice creams, and beverages in town.</p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <?php 
                $cart_count = 0;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $cart_count += $item['quantity'] ?? 1;
                    }
                }
                ?>
                <a href="?page=review" class="btn btn-light btn-lg fw-bold px-4 py-3">
                    <i class="bi bi-cart3 me-2"></i>View Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="badge bg-primary ms-2"><?= $cart_count ?> items</span>
                    <?php endif; ?>
                </a>
                <a href="#cakes-section" class="btn btn-outline-light btn-lg px-4 py-3">
                    <i class="bi bi-arrow-down me-2"></i>Explore Menu
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row g-4 mb-5 fade-in-up">
        <div class="col-md-4">
            <div class="text-center p-4">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-star-fill text-white fs-4"></i>
                </div>
                <h5 class="fw-bold text-primary">Premium Quality</h5>
                <p class="text-muted">Only the finest ingredients selected for exceptional taste and quality.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-clock-fill text-white fs-4"></i>
                </div>
                <h5 class="fw-bold text-primary">Fresh Daily</h5>
                <p class="text-muted">All our products are made fresh daily to ensure maximum freshness.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-truck text-white fs-4"></i>
                </div>
                <h5 class="fw-bold text-primary">Fast Delivery</h5>
                <p class="text-muted">Quick and reliable delivery to satisfy your cravings in no time.</p>
            </div>
        </div>
    </div>

    <!-- Cakes Section -->
    <section id="cakes-section" class="fade-in-up">
        <h2 class="section-title">
            <i class="bi bi-cake2 me-3"></i>Our Signature Cakes
        </h2>
        <p class="section-subtitle">Celebrate every moment with our handcrafted cakes, baked to perfection and decorated with love.</p>

        <div class="product-grid">
            <?php if (!empty($cakes)): ?>
                <?php foreach ($cakes as $cake): ?>
                <div class="product-card">
                    <div class="product-image" style="background: <?= $cake['color'] ?? '#8B4513' ?>">
                        <i class="bi bi-cake2"></i>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?= htmlspecialchars($cake['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($cake['description']) ?></p>
                        <div class="product-price">Birr <?= number_format($cake['price'], 2) ?></div>
                        <button type="button" class="btn btn-primary w-100" 
                                onclick="selectProduct('cake', '<?= $cake['id'] ?>')">
                            <i class="bi bi-magic me-2"></i>Customize Cake
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-5">
                            <i class="bi bi-cake display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Cakes Coming Soon</h4>
                            <p class="text-muted">We're preparing something sweet for you!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ice Creams Section -->
    <section id="icecreams-section" class="fade-in-up mt-5">
        <h2 class="section-title">
            <i class="bi bi-ice-cream me-3"></i>Creamy Delights
        </h2>
        <p class="section-subtitle">Indulge in our premium ice creams, crafted with real ingredients and endless flavor possibilities.</p>

        <div class="product-grid">
            <?php if (!empty($ice_creams)): ?>
                <?php foreach ($ice_creams as $ice_cream): ?>
                <div class="product-card">
                    <div class="product-image" style="background: <?= $ice_cream['color'] ?? '#D4A574' ?>">
                        <i class="bi bi-ice-cream"></i>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?= htmlspecialchars($ice_cream['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($ice_cream['description']) ?></p>
                        <div class="product-price">Birr <?= number_format($ice_cream['price'], 2) ?></div>
                        <button type="button" class="btn btn-primary w-100" 
                                onclick="selectProduct('ice_cream', '<?= $ice_cream['id'] ?>')">
                            <i class="bi bi-magic me-2"></i>Customize Ice Cream
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-5">
                            <i class="bi bi-ice-cream display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Ice Creams Coming Soon</h4>
                            <p class="text-muted">Chilling our recipes for the perfect scoop!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Soft Drinks Section -->
    <section id="softdrinks-section" class="fade-in-up mt-5">
        <h2 class="section-title">
            <i class="bi bi-cup-straw me-3"></i>Refreshing Beverages
        </h2>
        <p class="section-subtitle">Quench your thirst with our selection of cool, refreshing soft drinks and beverages.</p>

        <div class="product-grid">
            <?php if (!empty($soft_drinks)): ?>
                <?php foreach ($soft_drinks as $drink): ?>
                <div class="product-card">
                    <div class="product-image" style="background: <?= $drink['color'] ?? '#FF6B6B' ?>">
                        <i class="bi bi-cup-straw"></i>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?= htmlspecialchars($drink['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($drink['description']) ?></p>
                        <div class="product-price">Birr <?= number_format($drink['price'], 2) ?></div>
                        <button type="button" class="btn btn-primary w-100" 
                                onclick="selectProduct('soft_drink', '<?= $drink['id'] ?>')">
                            <i class="bi bi-magic me-2"></i>Customize Drink
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-5">
                            <i class="bi bi-cup-straw display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Drinks Coming Soon</h4>
                            <p class="text-muted">Mixing up some refreshing surprises!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Hot Drinks Section -->
    <section id="hotdrinks-section" class="fade-in-up mt-5">
        <h2 class="section-title">
            <i class="bi bi-cup-hot me-3"></i>Warm Comforts
        </h2>
        <p class="section-subtitle">Warm your soul with our carefully brewed hot beverages, perfect for any time of day.</p>

        <div class="product-grid">
            <?php if (!empty($hot_drinks)): ?>
                <?php foreach ($hot_drinks as $drink): ?>
                <div class="product-card">
                    <div class="product-image" style="background: <?= $drink['color'] ?? '#8B4513' ?>">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title"><?= htmlspecialchars($drink['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($drink['description']) ?></p>
                        <div class="product-price">Birr <?= number_format($drink['price'], 2) ?></div>
                        <button type="button" class="btn btn-primary w-100" 
                                onclick="selectProduct('hot_drink', '<?= $drink['id'] ?>')">
                            <i class="bi bi-magic me-2"></i>Customize Drink
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-5">
                            <i class="bi bi-cup-hot display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Hot Drinks Coming Soon</h4>
                            <p class="text-muted">Brewing something special for you!</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <div class="text-center my-5 py-5 fade-in-up">
        <h3 class="text-gradient fw-bold mb-3">Ready to Satisfy Your Cravings?</h3>
        <p class="text-muted mb-4">Create your perfect order with our easy customization options.</p>
        <a href="#cakes-section" class="btn btn-primary btn-lg px-5 py-3">
            <i class="bi bi-arrow-up-circle me-2"></i>Start Ordering
        </a>
    </div>
</div>

<script>
function selectProduct(type, id) {
    switch(type) {
        case 'cake':
            window.location.href = `?page=customize-cake&cake_id=${id}`;
            break;
        case 'ice_cream':
            window.location.href = `?page=customize-icecream&icecream_id=${id}`;
            break;
        case 'soft_drink':
            window.location.href = `?page=customize-softdrink&drink_id=${id}`;
            break;
        case 'hot_drink':
            window.location.href = `?page=customize-hotdrink&drink_id=${id}`;
            break;
    }
}

// Add scroll animations
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);

    document.querySelectorAll('section').forEach(section => {
        observer.observe(section);
    });
});
</script>