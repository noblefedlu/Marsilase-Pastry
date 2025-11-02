
<header class="app-header navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="?page=home">
            <i class="bi bi-cake2 me-2"></i>
            Marsilase Pastries
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'home') ? 'active' : '' ?>" href="?page=home">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#featured-cakes">
                        <i class="bi bi-cake me-1"></i>Menu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#about">
                        <i class="bi bi-info-circle me-1"></i>About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#testimonials">
                        <i class="bi bi-chat-quote me-1"></i>Testimonials
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">
                        <i class="bi bi-telephone me-1"></i>Contact
                    </a>
                </li>
            </ul>
            
            <div class="navbar-nav">
                <?php
                $cart_count = 0;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $cart_count += $item['quantity'] ?? 1;
                    }
                }
                ?>
                <a href="?page=review" class="nav-link position-relative">
                    <i class="bi bi-cart3 fs-5"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</header>