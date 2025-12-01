<?php ob_start(); ?>
<?php
session_start();
include 'config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Enhanced database queries with error handling
try {
    // Fetch cakes with categories
    $cakes_result = $conn->query("
        SELECT c.*, cat.name as category 
        FROM cakes c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        WHERE c.is_active = TRUE
    ");
    
    if ($cakes_result) {
        $cakes = $cakes_result->fetch_all(MYSQLI_ASSOC) ?: [];
    } else {
        $cakes = [];
        error_log("Cakes query failed: " . $conn->error);
    }

    // Fetch products with categories
    $products_result = $conn->query("
        SELECT p.*, cat.name as category 
        FROM products p 
        LEFT JOIN categories cat ON p.category_id = cat.id 
        WHERE p.is_active = TRUE
    ");
    
    if ($products_result) {
        $products = $products_result->fetch_all(MYSQLI_ASSOC) ?: [];
    } else {
        $products = [];
        error_log("Products query failed: " . $conn->error);
    }

    // Combine cakes and products for display
    $all_products = array_merge($cakes, $products);

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $cakes = [];
    $products = [];
    $all_products = [];
    $cake_sizes = [];
}

// Debug: Log what we found
error_log("Total products found: " . count($all_products));
error_log("Total cakes found: " . count($cakes));
error_log("Total non-cake products found: " . count($products));

// Set current page
$current_page = $_GET['page'] ?? 'home';

// Rating calculation function - ONLY DECLARE IF NOT ALREADY DECLARED
if (!function_exists('calculateStarRating')) {
    function calculateStarRating($totalRatings, $averageRating) {
        if ($totalRatings >= 2) {
            // Show full stars based on average rating (round to nearest whole number)
            $fullStars = round($averageRating);
            return [
                'stars' => $fullStars,
                'hasHalf' => false,
                'display' => round($averageRating, 1)
            ];
        } elseif ($totalRatings === 1) {
            // Show half star for single rating
            $fullStars = floor($averageRating);
            return [
                'stars' => $fullStars,
                'hasHalf' => true,
                'display' => $averageRating
            ];
        } else {
            // No ratings yet
            return [
                'stars' => 0,
                'hasHalf' => false,
                'display' => 0
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marsilas Pastry | Artisanal Cakes & Desserts in Addis Ababa</title>
    <meta name="description" content="Premium custom cakes, pastries & desserts made fresh daily with love. Fast delivery in Addis Ababa.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" as="style">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            /* Font System */
            --font-display: 'Playfair Display', serif; /* New variable name for headings */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; /* New variable name for body/UI */
            
            /* Color System */
            --primary-50: #FFF6E9;  /* Soft Cream */
            --primary-100: #5F372B; /* Chocolate Brown */
            --primary-200: #4A2B22; /* Darker Brown */
            --primary-300: #5F372B;
            --primary-400: #4A2B22;
            --primary-500: #5F372B;
            --primary-600: #4A2B22;
            --primary-700: #5F372B;
            --primary-800: #4A2B22;
            --primary-900: #3A231F;
            
            --light-caramel: #F8E9D2;
            --warm-chocolate: #C2865A;
            --deep-espresso: #4A2E2B;
            --cream: #FFF6E9;
            --gold-accent: #D4A373;
            
            --neutral-50: #FFF6E9;
            --neutral-100: #FFF6E9;
            --neutral-200: #F5E6D6;
            --neutral-300: #E8D9C8;
            --neutral-400: #D4C4B0;
            --neutral-500: #74422b;
            --neutral-600: #8B4D25;
            --neutral-700: #5F372B;
            --neutral-800: #4A2B22;
            --neutral-900: #3A231F;
            
            --gold-accent: #D4A373;
            --text-dark: #5F372B;
            --text-light: #FFF6E9;
            --text-muted: #6B6B6B;
            
            /* Spacing */
            --space-xs: 0.5rem;
            --space-sm: 1rem;
            --space-md: 1.5rem;
            --space-lg: 2rem;
            --space-xl: 3rem;
            --space-2xl: 4rem;
            --space-3xl: 6rem;
            
            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(95, 55, 43, 0.1);
            --shadow-md: 0 4px 6px rgba(95, 55, 43, 0.1), 0 1px 3px rgba(95, 55, 43, 0.08);
            --shadow-lg: 0 10px 15px rgba(95, 55, 43, 0.1), 0 4px 6px rgba(95, 55, 43, 0.05);
            --shadow-xl: 0 20px 25px rgba(95, 55, 43, 0.1), 0 10px 10px rgba(95, 55, 43, 0.04);
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            
            /* Transitions */
            --transition-fast: 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body { 
            font-family: var(--font-primary);
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--primary-50);
            overflow-x: hidden;
            font-weight: 500;
        }

        /* Typography */
        .display-font {
            font-family: var(--font-display) !important;
            font-weight: 700 !important;
            line-height: 1.2;
        }

        h1, h2, h3, h4, h5, h6 { 
            font-family: var(--font-display) !important;
            font-weight: 700 !important; 
            line-height: 1.2;
            color: var(--text-dark);
        }

        .text-lead {
            font-size: 1.25rem;
            color: var(--text-muted);
            line-height: 1.7;
            font-family: var(--font-primary);
        }

        /* Buttons, Navigation, Form Elements */
        .btn, nav, input, select, textarea, .badge { 
            font-family: var(--font-primary) !important;
        }

        /* Layout Components */
        .section {
            padding: var(--space-3xl) 0;
            position: relative;
            background: var(--primary-50);
        }

        .section-sm {
            padding: var(--space-2xl) 0;
        }

        .container-narrow {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--space-md);
        }

        /* Header */
        .navbar {
            padding: var(--space-sm) 0;
            background: var(--primary-100);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--primary-200);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: var(--transition-base);
        }

        .navbar.scrolled {
            background: var(--primary-100);
            box-shadow: var(--shadow-md);
        }

        .navbar-brand {
            font-family: var(--font-display) !important;
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .nav-link {
            font-weight: 500;
            color: white;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-md);
            transition: var(--transition-fast);
            text-decoration: none;
            font-family: var(--font-primary);
        }

        .nav-link:hover {
            color: var(--primary-50);
            background: rgba(255, 246, 233, 0.1);
        }

        .nav-link.active {
            color: var(--primary-50);
            background: rgba(255, 246, 233, 0.1);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            background: var(--primary-50);
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(95, 55, 43, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 163, 115, 0.05) 0%, transparent 50%);
            animation: float 6s ease-in-out infinite;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            margin-bottom: var(--space-lg);
            color: var(--primary-100);
            font-family: var(--font-display) !important;
        }

        .hero-subtitle {
            font-size: clamp(1.125rem, 2.5vw, 1.5rem);
            color: var(--text-muted);
            margin-bottom: var(--space-2xl);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-family: var(--font-primary);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            padding: 0.875rem 1.75rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--radius-lg);
            transition: var(--transition-base);
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-family: var(--font-primary);
        }

        .btn-primary {
            background: var(--primary-100);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
            background: var(--primary-200);
        }

        .btn-secondary {
            background: white;
            color: var(--text-dark);
            border: 1px solid var(--neutral-300);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--text-dark);
            border-color: var(--neutral-400);
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--neutral-200);
            overflow: hidden;
            transition: var(--transition-base);
            color: var(--text-dark);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            padding: var(--space-lg);
            border-bottom: 1px solid var(--neutral-200);
            background: white;
            color: var(--text-dark);
        }

        .card-body {
            padding: var(--space-lg);
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-lg);
            margin: var(--space-2xl) 0;
        }

        .product-card {
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--neutral-200);
            transition: var(--transition-base);
            position: relative;
            color: var(--text-dark);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .product-image {
            height: 240px;
            background: var(--neutral-100);
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-slow);
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: var(--space-sm);
            right: var(--space-sm);
            background: var(--primary-100);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-lg);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-family: var(--font-primary);
        }

        .product-content {
            padding: var(--space-lg);
        }

        .product-title {
            font-size: 1.25rem;
            margin-bottom: var(--space-xs);
            color: var(--text-dark);
            font-family: var(--font-display);
        }

        .product-description {
            color: var(--text-muted);
            margin-bottom: var(--space-md);
            line-height: 1.6;
            font-family: var(--font-primary);
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gold-accent);
            margin-bottom: var(--space-md);
            font-family: var(--font-primary);
        }

        /* Features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--space-lg);
            margin: var(--space-2xl) 0;
        }

        .feature-card {
            text-align: center;
            padding: var(--space-xl);
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--neutral-200);
            transition: var(--transition-base);
            color: var(--text-dark);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-100);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-md);
            color: white;
            font-size: 1.5rem;
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
            font-family: var(--font-primary);
        }

        /* Product Content */
        .product-content {
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .product-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--deep-espresso);
            margin-bottom: 0.75rem;
            line-height: 1.3;
            font-family: var(--font-display);
        }

        .product-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-family: var(--font-primary);
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
            font-family: var(--font-primary);
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
            font-family: var(--font-primary);
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
            font-family: var(--font-primary);
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

        /* Utility Classes */
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
            font-family: var(--font-primary);
        }

        .btn-espresso:hover {
            background: var(--warm-chocolate);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 46, 43, 0.3);
        }

        /* Form elements */
        .form-control, .form-select {
            border-color: var(--neutral-300);
            background: white;
            color: var(--text-dark);
            font-family: var(--font-primary);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-100);
            box-shadow: 0 0 0 0.2rem rgba(95, 55, 43, 0.1);
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

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
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .section {
                padding: var(--space-2xl) 0;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 992px) {
            .col-lg-4 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 768px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .product-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .btn-order {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>

<main>
    <?php
    switch ($current_page) {
        case 'home':
            include 'pages/home.php';
            break;
        case 'customize-cake':
            include 'pages/customize-cake.php';
            break;
        case 'category':
            include 'pages/category.php';
            break;
        case 'about':
            include 'pages/about.php';
            break;
        case 'testimonials':
            include 'pages/testimonials.php';
            break;
        case 'contact':
            include 'pages/contact.php';
            break;
        case 'review':
            include 'pages/review.php';
            break;
        case 'customer-info':
            include 'pages/customer-info.php';
            break;
        case 'thank-you':
            include 'pages/thank-you.php';
            break;
        
        // ===== ADMIN ROUTES =====
        case 'admin':
        case 'admin-login':
            include 'admin/login.php';
            break;
        case 'admin-dashboard':
            include 'admin/index.php';
            break;
        case 'admin-orders':
            include 'admin/order_details.php';
            break;
        case 'admin-products':
            include 'admin/products.php';
            break;
        case 'admin-manage-admins':
            include 'admin/manage_admins.php';
            break;
        case 'admin-change-password':
            include 'admin/change_password.php';
            break;
        case 'admin-update-status':
            include 'admin/update_status.php';
            break;
        case 'admin-debug-orders':
            include 'admin/debug_orders.php';
            break;
        case 'admin-logout':
            include 'admin/logout.php';
            break;
        // ===== END ADMIN ROUTES =====
        
        default:
            include 'pages/home.php';
            break;
    }
    ?>
</main>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Enhanced Add to Cart functionality
        document.addEventListener('DOMContentLoaded', function() {
            // For regular products
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
                    
                    fetch('cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('✅ ' + productName + ' added to cart!', 'success');
                            updateCartCount();
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
            
            function updateCartCount() {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    const currentCount = parseInt(cartCount.textContent) || 0;
                    cartCount.textContent = currentCount + 1;
                    cartCount.style.display = 'inline';
                } else {
                    // Create cart count if it doesn't exist
                    const cartLink = document.querySelector('a[href="?page=review"]');
                    if (cartLink) {
                        const newCount = document.createElement('span');
                        newCount.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count';
                        newCount.textContent = '1';
                        newCount.style.display = 'inline';
                        cartLink.appendChild(newCount);
                    }
                }
            }
            
            function showToast(message, type) {
                // Remove existing toasts
                document.querySelectorAll('.custom-toast').forEach(toast => toast.remove());
                
                const toast = document.createElement('div');
                toast.className = `custom-toast alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
                toast.style.top = '20px';
                toast.style.right = '20px';
                toast.style.zIndex = '9999';
                toast.style.padding = '12px 20px';
                toast.style.borderRadius = '8px';
                toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                toast.textContent = message;
                
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => toast.remove(), 500);
                }, 3000);
            }

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

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.fade-in-up');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            animatedElements.forEach(el => {
                el.style.animationPlayState = 'paused';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>