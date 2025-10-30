<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex align-items-center mb-4">
                <a href="?page=home" class="btn btn-outline-primary me-3 hover-glow">
                    <i class="bi bi-arrow-left me-2"></i>Back to Home
                </a>
                <div>
                    <h1 class="fw-bold text-gradient display-font mb-2">Our Complete Menu</h1>
                    <p class="text-muted mb-0">Discover all our premium handcrafted cakes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Cakes Grid -->
    <div class="product-grid">
        <?php foreach ($cakes as $cake): ?>
        <div class="product-card fade-in-up">
            <div class="product-image" style="background: <?= $cake['color'] ?>;">
                <?php if (!empty($cake['image_url'])): ?>
                    <img src="<?= $cake['image_url'] ?>" alt="<?= htmlspecialchars($cake['name']) ?>">
                <?php else: ?>
                    <i class="bi bi-cake2"></i>
                <?php endif; ?>
                <?php if ($cake['is_featured']): ?>
                <?php else: ?>
                <?php endif; ?>
            </div>
            <div class="product-content">
                <h3 class="product-title"><?= htmlspecialchars($cake['name']) ?></h3>
                <p class="product-description"><?= htmlspecialchars($cake['description']) ?></p>
                <div class="product-price">Birr <?= number_format($cake['price'], 2) ?></div>
                <a href="?page=customize-cake&cake_id=<?= $cake['id'] ?>" class="btn btn-primary w-100">
                    <i class="bi bi-gear me-2"></i>Customize Order
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="card border-0 bg-light py-4">
                <div class="card-body">
                    <h4 class="text-primary mb-3">Can't find what you're looking for?</h4>
                    <p class="text-muted mb-4">We specialize in custom cake designs. Let us create something unique for your special occasion!</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="?page=contact" class="btn btn-primary px-4">
                            <i class="bi bi-telephone me-2"></i>Contact Us
                        </a>
                        <a href="?page=home#about" class="btn btn-outline-primary px-4">
                            <i class="bi bi-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Enhanced full menu page styling */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2.5rem;
        margin: 3rem 0;
    }

    .product-card {
        background: var(--white);
        border: none;
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        opacity: 0;
        transform: translateY(30px);
    }

    .product-card.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .product-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: var(--shadow-lg);
    }

    .product-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        z-index: 2;
    }

    .product-image {
        height: 240px;
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3.5rem;
        overflow: hidden;
        transition: var(--transition);
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .product-card:hover .product-image img {
        transform: scale(1.1);
    }

    .product-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--secondary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(139,69,19,0.3);
        z-index: 3;
    }

    .product-content {
        padding: 2rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.75rem;
        font-family: 'Playfair Display', serif;
    }

    .product-description {
        color: var(--text-light);
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        flex-grow: 1;
        line-height: 1.6;
    }

    .product-price {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 1.5rem;
    }

    /* Animation for page load */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
    }

    /* Staggered animations for cards */
    .product-card:nth-child(1) { animation-delay: 0.1s; }
    .product-card:nth-child(2) { animation-delay: 0.2s; }
    .product-card:nth-child(3) { animation-delay: 0.3s; }
    .product-card:nth-child(4) { animation-delay: 0.4s; }
    .product-card:nth-child(5) { animation-delay: 0.5s; }
    .product-card:nth-child(6) { animation-delay: 0.6s; }
    .product-card:nth-child(7) { animation-delay: 0.7s; }
    .product-card:nth-child(8) { animation-delay: 0.8s; }
    .product-card:nth-child(9) { animation-delay: 0.9s; }

    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .d-flex.flex-wrap {
            flex-direction: column;
            align-items: center;
        }
        
        .d-flex.flex-wrap .btn {
            width: 100%;
            max-width: 300px;
            margin-bottom: 1rem;
        }
    }
</style>

<script>
    // Animation for page load
    document.addEventListener('DOMContentLoaded', function() {
        // Add visible class to cards with staggered delay
        const cards = document.querySelectorAll('.product-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('visible');
            }, index * 100 + 300);
        });
        
        // Scroll to top of page
        window.scrollTo(0, 0);
    });
</script>