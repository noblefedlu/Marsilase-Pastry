<?php // components/header.php – FULLY MATCHED TO YOUR DARK & GOLD FOOTER ?>
<nav id="navbar" class="fixed-top d-flex align-items-center" style="height:64px; transition:all .4s ease;">
    <div class="container-narrow d-flex align-items-center justify-content-between w-100 px-3">

        <!-- Logo – Rich Gold & Deep Chocolate -->
        <a href="?page=home" class="d-flex align-items-center gap-2 text-decoration-none">
            <i class="bi bi-cake2-fill" style="font-size:1.7rem; color:#D4A373;"></i>
            <span style="font-family:'Dancing Script',cursive; font-size:2.1rem; font-weight:700; color:#FDF4E6; letter-spacing:-0.5px;">
                Marsilas
            </span>
        </a>

        <!-- Desktop Navigation -->
        <div class="d-none d-lg-flex align-items-center gap-5">
            <a href="?page=home" class="nav-link <?= $current_page==='home'?'active':'' ?>">Home</a>

            <div class="dropdown">
                <a href="#" class="nav-link dropdown-toggle <?= in_array($current_page,['category'])?'active':'' ?>" 
                   data-bs-toggle="dropdown" aria-expanded="false">
                    Products
                </a>
                <ul class="dropdown-menu shadow-lg border-0 mt-2" style="border-radius:12px; min-width:220px;">
                    <?php
                    $cats = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
                    while ($cat = $cats->fetch_assoc()): ?>
                        <li><a class="dropdown-item py-2 px-3" href="?page=category&category_id=<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a></li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <a href="#about"        class="nav-link <?= $current_page==='about'?'active':'' ?>">About Us</a>
            <a href="#testimonials" class="nav-link <?= $current_page==='testimonials'?'active':'' ?>">Testimonials</a>
            <a href="#contact"      class="nav-link <?= $current_page==='contact'?'active':'' ?>">Contact</a>
        </div>

        <!-- Cart + Mobile Menu -->
        <div class="d-flex align-items-center gap-4">
            <a href="?page=review" class="position-relative">
                <i class="bi bi-bag-heart-fill" style="font-size:1.5rem; color:#D4A373;"></i>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                          style="background:#D4A373; color:#1a0f0a; font-size:0.7rem; font-weight:600;">
                        <?= count($_SESSION['cart']) ?>
                    </span>
                <?php endif; ?>
            </a>

            <button class="d-lg-none btn p-0" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                <i class="bi bi-list" style="font-size:1.9rem; color:#D4A373;"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Offcanvas – Dark & Luxurious -->
<div class="offcanvas offcanvas-end" id="mobileNav" style="background:#1a0f0a; width:320px;">
    <div class="offcanvas-header border-bottom border-gold">
        <h5 class="offcanvas-title" style="font-family:'Dancing Script',cursive; color:#D4A373; font-size:2rem;">Marsilas</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body pt-4">
        <div class="d-flex flex-column gap-4 fs-5">
            <a href="?page=home" class="text-white opacity-90">Home</a>
            <a href="#about" class="text-white opacity-90">About Us</a>
            <a href="#testimonials" class="text-white opacity-90">Testimonials</a>
            <a href="#contact" class="text-white opacity-90">Contact</a>
            <hr class="border-gold opacity-30">
            <a href="?page=review" class="btn btn-gold text-dark fw-semibold">View Cart (<?= count($_SESSION['cart'] ?? []) ?>)</a>
        </div>
    </div>
</div>

<!-- PERFECT MATCHING CSS – DARK CHOCOLATE + GOLD -->
<style>
#navbar {
    background: #5F372B;        /* Deep chocolate */
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(212,163,115,0.2);
    z-index: 9999;
}
#navbar.scrolled {
    height: 56px;
    background: #5F372B; /* Darker on scroll */
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

/* Navigation Links */
.nav-link {
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    font-size: 0.95rem;
    color: #f0e6d9 !important;
    padding: 0.5rem 0.9rem !important;
    position: relative;
    transition: all 0.3s ease;
}
.nav-link:hover,
.nav-link.active {
    color: #f5e4d2ff !important;
}
.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -8px; left: 50%;
    width: 24px; height: 2px;
    background: #D4A373;
    transform: translateX(-50%);
    border-radius: 1px;
}

/* Dropdown */
.dropdown-menu {
    background: #5F372B;
    border: 1px solid #D4A373;
    border-radius: 12px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.5);
}
.dropdown-item {
    color: #f0e6d9;
    font-family: 'Quicksand', sans-serif;
    font-weight: 500;
    padding: 0.7rem 1.2rem;
}
.dropdown-item:hover {
    background: #5F372B;
    color: #D4A373;
}

/* Golden Accents */
.border-gold { border-color: #5F372B!important; }
.btn-gold {
    background: #D4A373;
    color: #5F372B;
    border-radius: 50px;
    padding: 0.7rem 1.5rem;
}
.btn-gold:hover {
    background: #e0b88a;
    transform: translateY(-2px);
}

/* Golden line on scroll */
#navbar.scrolled::after {
    content: '';
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #D4A373, transparent);
}
</style>

<script>
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
});
</script>