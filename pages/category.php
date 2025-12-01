<?php
// ===============================================================
// FILE: pages/category.php
// DESCRIPTION: Displays products for a specific category, using the home.php style and ignoring discount prices.
// ===============================================================

include './config.php';

// Function to calculate star rating (copied from home.php for independence)
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

$category_id = intval($_GET['category_id'] ?? 0);

// Get category info
$stmt = $conn->prepare("SELECT name, description FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category_result = $stmt->get_result();
$category = $category_result->fetch_assoc();
$stmt->close();

if (!$category) {
    // Category not found, redirect to home
    header('Location: ?page=home');
    exit;
}

$category_name = $category['name'];
$category_description = $category['description'] ?? 'Discover our premium selection of ' . htmlspecialchars(strtolower($category_name));

$category_products = [];

// Fetch products from the products table (Removed discount_price from SELECT)
$products_stmt = $conn->prepare("
    SELECT id, name, description, price, image_path, 
           'product' as product_type, category_id as category_id
    FROM products 
    WHERE category_id = ? AND is_active = 1 
    ORDER BY name
");
$products_stmt->bind_param("i", $category_id);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
if ($products_result) {
    while ($product = $products_result->fetch_assoc()) {
        $category_products[] = $product;
    }
}
$products_stmt->close();

// Fetch cakes from the cakes table (Removed discount_price AND serves from SELECT)
$cakes_stmt = $conn->prepare("
    SELECT id, name, description, price, image_url as image_path, 
           'cake' as product_type, category_id as category_id
    FROM cakes 
    WHERE category_id = ? AND is_active = 1 
    ORDER BY name
");
$cakes_stmt->bind_param("i", $category_id);
$cakes_stmt->execute();
$cakes_result = $cakes_stmt->get_result();
if ($cakes_result) {
    while ($cake = $cakes_result->fetch_assoc()) {
        $category_products[] = $cake;
    }
}
$cakes_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category_name) ?> - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Color Palette (Matched to home.php) --- */
        :root {
            --orange-cream: #1a0f0a;
            --orange-light: #4A2B22;
            --orange-dark: #5F372B;
            --cream: #FFF6E9;
            --white: #FFFFFF;
            --light-caramel: #F8E9D2;
            --warm-chocolate: #C2865A;
            --deep-espresso: #4A2E2B;
            --gold-accent: #D4A373;
        }

        /* --- Global Styles --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cream);
            color: var(--deep-espresso);
            overflow-x: hidden;
        }

        .display-font {
            font-family: 'Playfair Display', serif;
            color: var(--deep-espresso);
        }

        .section {
            padding: 5rem 0;
        }
        
        .container-narrow {
            max-width: 1140px; 
            margin: auto;
            padding: 0 15px;
        }

        /* --- Custom Button Styles (Matched to home.php) --- */
        .btn-espresso {
            background-color: var(--deep-espresso);
            color: var(--cream);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-espresso:hover {
            background-color: var(--orange-dark);
            color: var(--cream);
        }

        /* --- Product Card Styles (Matching home.php's product-card-glass) --- */
        .product-card-glass {
            background: var(--white);
            border: 1px solid var(--light-caramel);
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .product-card-glass:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(194, 134, 90, 0.15);
        }

        .product-image-wrapper {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card-glass:hover .product-image {
            transform: scale(1.05);
        }

        .product-content {
            padding: 1rem 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--deep-espresso);
        }

        .product-description {
            font-size: 0.9rem;
            color: #6c757d;
            flex-grow: 1;
            margin-bottom: 1rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--light-caramel);
        }

        .current-price {
            font-weight: 700;
            color: var(--warm-chocolate);
            font-size: 1.2rem;
        }

        .category-tag {
            position: absolute;
            top: 12px;
            right: 12px;
            background-color: var(--deep-espresso);
            color: var(--cream);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* REMOVED .serves-badge CSS */

        /* --- ORDER BUTTON COLOR --- */
        .btn-order {
            background-color: var(--orange-dark);
            color: var(--cream);
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
        }
        .btn-order:hover {
            background-color: var(--deep-espresso);
            color: var(--cream);
        }

        /* --- Header and Breadcrumb --- */
        .category-header-section {
            padding-top: 6rem; 
            padding-bottom: 3rem;
            background-color: var(--light-caramel);
        }
        
        .breadcrumb-item a {
            color: var(--deep-espresso);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--warm-chocolate);
        }

        /* --- No Products Message --- */
        .no-products-message {
            border: 2px dashed var(--warm-chocolate);
            border-radius: 12px;
            padding: 4rem;
            background-color: var(--white);
        }
        
        @keyframes spinner {
            to {transform: rotate(360deg);}
        }
        .spinner {
            display: inline-block;
            animation: spinner .6s linear infinite;
        }
    </style>
</head>
<body>

<div class="category-header-section">
    <div class="container-narrow">
        <div class="d-flex align-items-center mb-5">
            <a href="?page=home" class="btn btn-espresso me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="?page=home" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($category_name) ?></li>
                    </ol>
                </nav>
                <h1 class="display-4 display-font mb-2 text-espresso"><?= htmlspecialchars($category_name) ?></h1>
                <p class="text-lead text-muted"><?= htmlspecialchars($category_description) ?></p>
            </div>
        </div>
        
        <?php if (empty($category_products)): ?>
            <div class="text-center py-5 no-products-message">
                <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                <h3 class="mb-3 display-font text-deep-espresso">No Products Found</h3>
                <p class="text-muted mb-4">We couldn't find any products in this category.</p>
                <a href="?page=home" class="btn btn-espresso">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Home
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($category_products as $product):
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
                                <?= htmlspecialchars($category_name) ?>
                            </div>
                            <?php 
                            // REMOVED 'SERVES' BADGE DISPLAY BLOCK
                            ?>
                        </div>
                        <div class="product-content d-flex flex-column">
                            <h4 class="product-name"><?= htmlspecialchars($product['name'] ?? 'Unknown Product') ?></h4>
                            <p class="product-description flex-grow-1">
                                <?= htmlspecialchars($product['description'] ?? 'No description available') ?>
                            </p>
                            
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
                                        <i class="bi bi-star text-muted"></i>
                                    <?php endfor; ?>
                                    <span class="rating-text text-muted small">(<?= $ratingDisplay['display'] ?>)</span>
                                <?php else: ?>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <i class="bi bi-star text-muted"></i>
                                    <span class="rating-text text-muted small">(No ratings)</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-footer mt-auto">
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
                                            data-price="<?= $product['price'] ?? 0 ?>"
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
            
            <div class="mt-5 text-center">
                <p class="text-muted mb-4">
                    Showing <?= count($category_products) ?> premium <?= htmlspecialchars(strtolower($category_name)) ?> products
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="?page=home" class="btn btn-espresso">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Categories
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// --- Add to Cart AJAX Functionality ---
document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-product');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productType = this.getAttribute('data-product-type');
            const productName = this.getAttribute('data-product-name');
            const price = parseFloat(this.getAttribute('data-price'));
            const image = this.getAttribute('data-image');
            
            // Add to cart via AJAX
            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('product_type', productType);
            formData.append('product_id', productId);
            formData.append('product_name', productName);
            formData.append('quantity', 1);
            formData.append('unit_price', price);
            formData.append('total_price', price);
            formData.append('image', image);
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Adding...';
            this.disabled = true;
            
            fetch('../cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ ' + productName + ' added to cart!', 'success');
                } else {
                    showToast('❌ Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('❌ Network error adding to cart', 'error');
            })
            .finally(() => {
                // Restore button state
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });
    
    // Simple Toast Notification for feedback
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); opacity: 0; transition: opacity 0.5s ease;';
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Fade in
        setTimeout(() => {
            toast.style.opacity = '1';
        }, 10);
        
        // Fade out
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});
</script>
</body>
</html>
<?php $conn->close(); ?>