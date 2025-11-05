<?php ob_start(); ?>
<?php
session_start();
include 'config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch all data
$cakes = $conn->query("SELECT * FROM cakes WHERE is_active = TRUE")->fetch_all(MYSQLI_ASSOC) ?: [];
$cake_sizes = $conn->query("SELECT * FROM cake_sizes")->fetch_all(MYSQLI_ASSOC) ?: [];

// Set current page
$current_page = $_GET['page'] ?? 'home';

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marsilase Pastry | Artisanal Cakes & Desserts</title>
    <meta name="description" content="Premium artisanal cakes and desserts crafted with passion. Custom orders, fast delivery, and exceptional quality.">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" as="style">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* Color System */
            --primary-50: #fef7f0;
            --primary-100: #feecd8;
            --primary-200: #fcd4a8;
            --primary-300: #fab270;
            --primary-400: #f88b37;
            --primary-500: #f56e10;
            --primary-600: #e7540a;
            --primary-700: #bf3d0a;
            --primary-800: #983110;
            --primary-900: #7a2a10;
            
            --neutral-50: #f8fafc;
            --neutral-100: #f1f5f9;
            --neutral-200: #e2e8f0;
            --neutral-300: #cbd5e1;
            --neutral-400: #94a3b8;
            --neutral-500: #64748b;
            --neutral-600: #475569;
            --neutral-700: #334155;
            --neutral-800: #1e293b;
            --neutral-900: #0f172a;
            
            /* Typography */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-display: 'Playfair Display', serif;
            
            /* Spacing */
            --space-xs: 0.5rem;
            --space-sm: 1rem;
            --space-md: 1.5rem;
            --space-lg: 2rem;
            --space-xl: 3rem;
            --space-2xl: 4rem;
            --space-3xl: 6rem;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            
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
            color: var(--neutral-700);
            background: var(--neutral-50);
            overflow-x: hidden;
        }

        /* Typography */
        .display-font {
            font-family: var(--font-display);
            font-weight: 600;
            line-height: 1.2;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-display);
            font-weight: 600;
            line-height: 1.2;
            color: var(--neutral-900);
        }

        .text-lead {
            font-size: 1.25rem;
            color: var(--neutral-600);
            line-height: 1.7;
        }

        /* Layout Components */
        .section {
            padding: var(--space-3xl) 0;
            position: relative;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--neutral-200);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: var(--transition-base);
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-md);
        }

        .navbar-brand {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-600);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .nav-link {
            font-weight: 500;
            color: var(--neutral-700);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-md);
            transition: var(--transition-fast);
            text-decoration: none;
        }

        .nav-link:hover {
            color: var(--primary-600);
            background: var(--primary-50);
        }

        .nav-link.active {
            color: var(--primary-600);
            background: var(--primary-50);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--neutral-50) 100%);
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
                radial-gradient(circle at 20% 80%, rgba(245, 110, 16, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(251, 191, 36, 0.05) 0%, transparent 50%);
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
            background: linear-gradient(135deg, var(--neutral-900) 0%, var(--primary-600) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: clamp(1.125rem, 2.5vw, 1.5rem);
            color: var(--neutral-600);
            margin-bottom: var(--space-2xl);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
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
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: var(--neutral-700);
            border: 1px solid var(--neutral-300);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--neutral-700);
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
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            padding: var(--space-lg);
            border-bottom: 1px solid var(--neutral-200);
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
            background: var(--primary-500);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-lg);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .product-content {
            padding: var(--space-lg);
        }

        .product-title {
            font-size: 1.25rem;
            margin-bottom: var(--space-xs);
            color: var(--neutral-900);
        }

        .product-description {
            color: var(--neutral-600);
            margin-bottom: var(--space-md);
            line-height: 1.6;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-600);
            margin-bottom: var(--space-md);
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
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-md);
            color: white;
            font-size: 1.5rem;
        }

        /* Testimonials */
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-lg);
        }

        .testimonial-card {
            background: white;
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--neutral-200);
        }

        .testimonial-content {
            font-style: italic;
            color: var(--neutral-700);
            margin-bottom: var(--space-md);
            line-height: 1.7;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .author-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--neutral-300);
        }

        /* Footer */
        .footer {
            background: var(--neutral-900);
            color: var(--neutral-300);
            padding: var(--space-3xl) 0 var(--space-2xl);
        }

        .footer a {
            color: var(--neutral-300);
            text-decoration: none;
            transition: var(--transition-fast);
        }

        .footer a:hover {
            color: white;
        }

        /* Utility Classes */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .bg-gradient {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
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

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--neutral-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--neutral-400);
            border-radius: var(--radius-md);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--neutral-500);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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
            case 'all-products':
                include 'pages/all-products.php';
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
                include 'admin/dashboard.php';
                break;
            case 'admin-orders':
                include 'admin/order_details.php';
                break;
            case 'admin-products':
                include 'admin/manage_products.php';
                break;
            case 'admin-manage-admins':
                include 'admin/manage_admins.php';
                break;
            case 'admin-change-password':
                include 'admin/change_password.php';
                break;
            // case 'admin-register':
                // include 'admin/register.php';
                // break;
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

    <!-- Footer -->
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

        // Loading states
        function setLoading(element, isLoading) {
            if (isLoading) {
                element.classList.add('loading');
                element.disabled = true;
            } else {
                element.classList.remove('loading');
                element.disabled = false;
            }
        }

        // Toast notification system
        function showToast(message, type = 'info') {
            // Implementation for toast notifications
            console.log(`[${type.toUpperCase()}] ${message}`);
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in animation to elements with data-animate attribute
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in-up');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('[data-animate]').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>