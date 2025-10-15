<?php
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'] ?? 1;
    }
}

$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top app-header">
    <div class="container">
        <a class="navbar-brand fw-bold" href="?page=home">
            <i class="bi bi-cake2 me-2"></i>Marsilase Pastry
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="?page=home#cakes-section">
                        <i class="bi bi-cake2 me-1"></i> Cakes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=home#icecreams-section">
                        <i class="bi bi-ice-cream me-1"></i> Ice Creams
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=home#softdrinks-section">
                        <i class="bi bi-cup-straw me-1"></i> Soft Drinks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=home#hotdrinks-section">
                        <i class="bi bi-cup-hot me-1"></i> Hot Drinks
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if ($is_admin): ?>
                    <a href="admin/index.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="admin/login.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-person-gear me-1"></i> Admin
                    </a>
                <?php endif; ?>
                
                <a href="?page=review" class="btn btn-light position-relative">
                    <i class="bi bi-cart3"></i> 
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</nav>