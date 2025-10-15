<div class="container my-5">
    <div class="hero-section text-center mb-5 rounded">
        <h1 class="display-4 fw-bold mb-3">Sweeten Your Celebrations</h1>
        <p class="lead mb-4 fs-5">Custom cakes, ice cream, and beverages crafted with passion</p>
        <?php 
        $cart_count = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $cart_count += $item['quantity'] ?? 1;
            }
        }
        ?>
        <a href="?page=review" class="btn btn-light btn-lg rounded-pill px-4 fw-bold">
            View Cart 
            <?php if ($cart_count > 0): ?>
                <span class="badge bg-primary ms-2"><?= $cart_count ?> items</span>
            <?php endif; ?>
            <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Cakes Section -->
        <div class="col-12 text-center mb-4" id="cakes-section">
            <h2 class="section-title"><i class="bi bi-cake2 me-2"></i>Our Cakes</h2>
            <p class="text-muted fs-5">Handcrafted cakes for every special moment</p>
        </div>

        <?php if (!empty($cakes)): ?>
            <?php foreach ($cakes as $cake): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card">
                    <div class="product-image" style="background: <?= $cake['color'] ?? '#8B4513' ?>; background-size: cover;"></div>
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($cake['name']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($cake['description']) ?></p>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="price fw-bold text-primary fs-5">Birr <?= number_format($cake['price'], 2) ?></span>
                            <button type="button" class="btn btn-primary rounded-pill px-3" 
                                    onclick="selectProduct('cake', '<?= $cake['id'] ?>')">
                                Customize <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5">
                        <i class="bi bi-cake display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No Cakes Available</h4>
                        <p class="text-muted">We're currently preparing our cake collection. Please check back soon!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Ice Creams Section -->
        <div class="col-12 text-center mt-5 mb-4" id="icecreams-section">
            <h2 class="section-title"><i class="bi bi-ice-cream me-2"></i>Our Ice Creams</h2>
            <p class="text-muted fs-5">Creamy delights in every scoop</p>
        </div>

        <?php if (!empty($ice_creams)): ?>
            <?php foreach ($ice_creams as $ice_cream): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card">
                    <div class="product-image" style="background: <?= $ice_cream['color'] ?? '#D4A574' ?>; background-size: cover;"></div>
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($ice_cream['name']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($ice_cream['description']) ?></p>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="price fw-bold text-primary fs-5">Birr <?= number_format($ice_cream['price'], 2) ?></span>
                            <button type="button" class="btn btn-primary rounded-pill px-3" 
                                    onclick="selectProduct('ice_cream', '<?= $ice_cream['id'] ?>')">
                                Customize <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5">
                        <i class="bi bi-ice-cream display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No Ice Creams Available</h4>
                        <p class="text-muted">Our ice cream selection is being updated. Please check back later!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Soft Drinks Section -->
        <div class="col-12 text-center mt-5 mb-4" id="softdrinks-section">
            <h2 class="section-title"><i class="bi bi-cup-straw me-2"></i>Soft Drinks</h2>
            <p class="text-muted fs-5">Refreshing beverages for every taste</p>
        </div>

        <?php if (!empty($soft_drinks)): ?>
            <?php foreach ($soft_drinks as $drink): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card">
                    <div class="product-image" style="background: <?= $drink['color'] ?? '#FF6B6B' ?>; background-size: cover;"></div>
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($drink['name']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($drink['description']) ?></p>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="price fw-bold text-primary fs-5">Birr <?= number_format($drink['price'], 2) ?></span>
                            <button type="button" class="btn btn-primary rounded-pill px-3" 
                                    onclick="selectProduct('soft_drink', '<?= $drink['id'] ?>')">
                                Customize <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5">
                        <i class="bi bi-cup-straw display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No Soft Drinks Available</h4>
                        <p class="text-muted">We're refreshing our drink menu. Please check back soon!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hot Drinks Section -->
        <div class="col-12 text-center mt-5 mb-4" id="hotdrinks-section">
            <h2 class="section-title"><i class="bi bi-cup-hot me-2"></i>Hot Drinks</h2>
            <p class="text-muted fs-5">Warm beverages to comfort your soul</p>
        </div>

        <?php if (!empty($hot_drinks)): ?>
            <?php foreach ($hot_drinks as $drink): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card h-100 product-card">
                    <div class="product-image" style="background: <?= $drink['color'] ?? '#8B4513' ?>; background-size: cover;"></div>
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($drink['name']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($drink['description']) ?></p>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="price fw-bold text-primary fs-5">Birr <?= number_format($drink['price'], 2) ?></span>
                            <button type="button" class="btn btn-primary rounded-pill px-3" 
                                    onclick="selectProduct('hot_drink', '<?= $drink['id'] ?>')">
                                Customize <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5">
                        <i class="bi bi-cup-hot display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No Hot Drinks Available</h4>
                        <p class="text-muted">Our hot beverage selection is being prepared. Please check back later!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
</script>