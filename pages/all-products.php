<!-- Header Section -->
<section class="section">
    <div class="container-narrow">
        <div class="d-flex align-items-center mb-5">
            <a href="?page=home" class="btn btn-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="display-4 display-font mb-2">All Products</h1>
                <p class="text-muted">Discover our complete collection of exquisite cakes and desserts</p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search products...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="sortSelect">
                            <option value="name">Sort by Name</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="featured">Featured First</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterSelect">
                            <option value="all">All Products</option>
                            <option value="featured">Featured Only</option>
                            <option value="chocolate">Chocolate</option>
                            <option value="fruit">Fruit</option>
                            <option value="vanilla">Vanilla</option>
                            <option value="special">Special Occasion</option>
                            <option value="caramel">Caramel</option>
                            <option value="coffee">Coffee</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="product-grid" id="productGrid">
            <?php foreach ($cakes as $cake): ?>
            <div class="product-card" 
                 data-category="<?= htmlspecialchars($cake['category'] ?? 'general') ?>" 
                 data-featured="<?= (($cake['is_featured'] ?? false) || ($cake['is_featured'] ?? 0) == 1) ? 'true' : 'false' ?>">
                <div class="product-image">
                    <img src="<?= htmlspecialchars($cake['image_url'] ?? 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') ?>" 
                         alt="<?= htmlspecialchars($cake['name'] ?? 'Cake') ?>" 
                         loading="lazy">
                    <?php if (($cake['is_featured'] ?? false) || ($cake['is_featured'] ?? 0) == 1): ?>
                    <div class="product-badge">Featured</div>
                    <?php endif; ?>
                    <div class="product-overlay">
                        <a href="?page=customize-cake&cake_id=<?= $cake['id'] ?? '' ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            Customize
                        </a>
                    </div>
                </div>
                <div class="product-content">
                    <h3 class="product-title"><?= htmlspecialchars($cake['name'] ?? 'Unknown Product') ?></h3>
                    <p class="product-description"><?= htmlspecialchars($cake['description'] ?? 'No description available') ?></p>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-half text-warning"></i>
                            <span class="text-muted small ms-1">(4.5)</span>
                        </div>
                        <div class="product-category">
                            <span class="badge bg-light text-dark"><?= htmlspecialchars(ucfirst($cake['category'] ?? 'general')) ?></span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="product-price">ETB <?= number_format($cake['price'] ?? 0, 2) ?></div>
                        <a href="?page=customize-cake&cake_id=<?= $cake['id'] ?? '' ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-cart-plus me-1"></i>
                            Order
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Category Stats -->
        <!-- <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Browse by Category</h4>
                <div class="row g-3">
                    <div class="col-md-2 col-4">
                        <a href="javascript:void(0)" class="category-filter card text-center p-3 text-decoration-none active" data-category="all">
                            <i class="bi bi-grid-3x3-gap display-6 text-primary mb-2"></i>
                            <h6 class="mb-0">All</h6>
                            <small class="text-muted"><?= count($cakes) ?> items</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-4">
                        <a href="javascript:void(0)" class="category-filter card text-center p-3 text-decoration-none" data-category="featured">
                            <i class="bi bi-star display-6 text-primary mb-2"></i>
                            <h6 class="mb-0">Featured</h6>
                            <small class="text-muted">
                                <?= count(array_filter($cakes, function($cake) { 
                                    return ($cake['is_featured'] ?? false) || ($cake['is_featured'] ?? 0) == 1; 
                                })) ?> items
                            </small>
                        </a>
                    </div>
                    <div class="col-md-2 col-4">
                        <a href="javascript:void(0)" class="category-filter card text-center p-3 text-decoration-none" data-category="chocolate">
                            <i class="bi bi-cup-hot display-6 text-primary mb-2"></i>
                            <h6 class="mb-0">Chocolate</h6>
                            <small class="text-muted">
                                <?= count(array_filter($cakes, function($cake) { 
                                    return ($cake['category'] ?? '') === 'chocolate'; 
                                })) ?> items
                            </small>
                        </a>
                    </div>
                    <div class="col-md-2 col-4">
                        <a href="javascript:void(0)" class="category-filter card text-center p-3 text-decoration-none" data-category="fruit">
                            <i class="bi bi-apple display-6 text-primary mb-2"></i>
                            <h6 class="mb-0">Fruit</h6>
                            <small class="text-muted">
                                <?= count(array_filter($cakes, function($cake) { 
                                    return ($cake['category'] ?? '') === 'fruit'; 
                                })) ?> items
                            </small>
                        </a>
                    </div>
                    <div class="col-md-2 col-4">
                        <a href="javascript:void(0)" class="category-filter card text-center p-3 text-decoration-none" data-category="vanilla">
                            <i class="bi bi-droplet display-6 text-primary mb-2"></i>
                            <h6 class="mb-0">Vanilla</h6>
                            <small class="text-muted">
                                <?= count(array_filter($cakes, function($cake) { 
                                    return ($cake['category'] ?? '') === 'vanilla'; 
                                })) ?> items
                            </small>
                        </a>
                    </div>
                    <div class="col-md-2 col-4">
                        <a href="javascript:void(0)" class="category-filter card text-center p-3 text-decoration-none" data-category="special">
                            <i class="bi bi-gift display-6 text-primary mb-2"></i>
                            <h6 class="mb-0">Special</h6>
                            <small class="text-muted">
                                <?= count(array_filter($cakes, function($cake) { 
                                    return ($cake['category'] ?? '') === 'special'; 
                                })) ?> items
                            </small>
                        </a>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Quick Stats
        <div class="row mt-5">
            <div class="col-md-3 col-6 text-center">
                <div class="card border-0 bg-light">
                    <div class="card-body py-4">
                        <i class="bi bi-cake display-6 text-primary mb-2"></i>
                        <h4 class="mb-1"><?= count($cakes) ?>+</h4>
                        <p class="text-muted mb-0">Products</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="card border-0 bg-light">
                    <div class="card-body py-4">
                        <i class="bi bi-star display-6 text-primary mb-2"></i>
                        <h4 class="mb-1">4.8/5</h4>
                        <p class="text-muted mb-0">Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="card border-0 bg-light">
                    <div class="card-body py-4">
                        <i class="bi bi-clock display-6 text-primary mb-2"></i>
                        <h4 class="mb-1">2-4h</h4>
                        <p class="text-muted mb-0">Ready Time</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center">
                <div class="card border-0 bg-light">
                    <div class="card-body py-4">
                        <i class="bi bi-truck display-6 text-primary mb-2"></i>
                        <h4 class="mb-1">Free</h4>
                        <p class="text-muted mb-0">Delivery >500</p>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const filterSelect = document.getElementById('filterSelect');
    const productGrid = document.getElementById('productGrid');
    const productCards = document.querySelectorAll('.product-card');
    const categoryFilters = document.querySelectorAll('.category-filter');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        productCards.forEach(card => {
            const title = card.querySelector('.product-title').textContent.toLowerCase();
            const description = card.querySelector('.product-description').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Sort functionality
    sortSelect.addEventListener('change', function() {
        const sortValue = this.value;
        const cards = Array.from(productCards);
        
        cards.sort((a, b) => {
            switch(sortValue) {
                case 'name':
                    return a.querySelector('.product-title').textContent.localeCompare(b.querySelector('.product-title').textContent);
                
                case 'price-low':
                    const priceA = parseFloat(a.querySelector('.product-price').textContent.replace('ETB ', '')) || 0;
                    const priceB = parseFloat(b.querySelector('.product-price').textContent.replace('ETB ', '')) || 0;
                    return priceA - priceB;
                
                case 'price-high':
                    const priceAHigh = parseFloat(a.querySelector('.product-price').textContent.replace('ETB ', '')) || 0;
                    const priceBHigh = parseFloat(b.querySelector('.product-price').textContent.replace('ETB ', '')) || 0;
                    return priceBHigh - priceAHigh;
                
                case 'featured':
                    const featuredA = a.dataset.featured === 'true';
                    const featuredB = b.dataset.featured === 'true';
                    return featuredB - featuredA;
                
                default:
                    return 0;
            }
        });

        // Re-append sorted cards
        cards.forEach(card => productGrid.appendChild(card));
    });

    // Filter functionality
    filterSelect.addEventListener('change', function() {
        applyFilter(this.value);
    });

    // Category filter buttons
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const category = this.dataset.category;
            filterSelect.value = category;
            applyFilter(category);
            
            // Update active state
            categoryFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
        });
    });

    function applyFilter(filterValue) {
        productCards.forEach(card => {
            const category = card.dataset.category;
            const featured = card.dataset.featured;
            
            switch(filterValue) {
                case 'all':
                    card.style.display = 'block';
                    break;
                case 'featured':
                    card.style.display = featured === 'true' ? 'block' : 'none';
                    break;
                case 'chocolate':
                    card.style.display = category === 'chocolate' ? 'block' : 'none';
                    break;
                case 'fruit':
                    card.style.display = category === 'fruit' ? 'block' : 'none';
                    break;
                case 'vanilla':
                    card.style.display = category === 'vanilla' ? 'block' : 'none';
                    break;
                case 'special':
                    card.style.display = category === 'special' ? 'block' : 'none';
                    break;
                case 'caramel':
                    card.style.display = category === 'caramel' ? 'block' : 'none';
                    break;
                case 'coffee':
                    card.style.display = category === 'coffee' ? 'block' : 'none';
                    break;
                default:
                    card.style.display = 'block';
            }
        });
    }
});
</script>

<style>
/* Your existing CSS styles remain the same */
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
    background: var(--primary-500);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
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
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--neutral-900);
    line-height: 1.3;
}

.product-description {
    color: var(--neutral-600);
    font-size: 0.875rem;
    margin-bottom: 1rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
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
}

.product-category .badge {
    font-size: 0.7rem;
    font-weight: 500;
}

.product-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-600);
}

/* Category filter cards */
.category-filter {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.category-filter:hover,
.category-filter.active {
    border-color: var(--primary-500);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.category-filter.active {
    background: var(--primary-50) !important;
}

/* Search and filter styles */
.input-group-text {
    background: var(--neutral-50) !important;
    border-color: var(--neutral-200) !important;
}

.form-control, .form-select {
    border-color: var(--neutral-200);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.1);
}

/* Quick stats cards */
.bg-light {
    background: var(--neutral-50) !important;
}

@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .product-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .category-filter {
        padding: 1rem !important;
    }
}

@media (max-width: 576px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
    
    .card-body .row {
        flex-direction: column;
    }
    
    .card-body .col-md-6,
    .card-body .col-md-3 {
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>