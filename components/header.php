    <nav class="navbar" id="navbar">
        <div class="container-narrow">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand" href="?page=home">
                    <i class="bi bi-cake2"></i>
                    Marsilase Pastry
                </a>
                
                <div class="d-none d-md-flex align-items-center gap-3">
                    <a class="nav-link <?= $current_page === 'home' ? 'active' : '' ?>" href="?page=home">Home</a>
                    <a class="nav-link" href="?page=products">Products</a>
                    <a class="nav-link" href="?page=about">About</a>
                    <a class="nav-link" href="?page=testimonials">Testimonials</a>
                    <a class="nav-link" href="?page=contact">Contact</a>

                    <?php
                    $cart_count = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        $cart_count += $item['quantity'] ?? 1;
                    }
                    ?>
                    <a href="?page=review" class="nav-link position-relative">
                        <i class="bi bi-cart3"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cart_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <button class="d-md-none btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div class="collapse d-md-none mt-3" id="mobileMenu">
                <div class="d-flex flex-column gap-2">
                    <a class="nav-link <?= $current_page === 'home' ? 'active' : '' ?>" href="?page=home">Home</a>
                    <a class="nav-link" href="#products">Products</a>
                    <a class="nav-link" href="#about">About</a>
                    <a class="nav-link" href="#testimonials">Testimonials</a>
                    <a class="nav-link" href="#contact">Contact</a>
                    <a href="?page=review" class="nav-link">
                        <i class="bi bi-cart3 me-2"></i>Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="badge bg-danger ms-2"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>