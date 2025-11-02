
<?php ob_start(); ?>
<?php
session_start();
include 'config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize all product arrays to empty arrays
$cakes = [];
$ice_creams = [];
$soft_drinks = [];
$hot_drinks = [];
$cake_sizes = [];
$cake_flavors = [];
$ice_cream_flavors = [];
$soft_drink_flavors = [];
$hot_drink_flavors = [];
$toppings = [];

// Fetch cakes with error handling
$result = $conn->query("SELECT * FROM cakes WHERE is_active = TRUE");
if ($result && $result->num_rows > 0) {
    $cakes = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $cakes = [];
}

// Fetch ice creams with error handling
$result = $conn->query("SELECT * FROM ice_creams WHERE is_active = TRUE");
if ($result && $result->num_rows > 0) {
    $ice_creams = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $ice_creams = [];
}

// Fetch soft drinks with error handling
$result = $conn->query("SELECT * FROM soft_drinks WHERE is_active = TRUE");
if ($result && $result->num_rows > 0) {
    $soft_drinks = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $soft_drinks = [];
}

// Fetch hot drinks with error handling
$result = $conn->query("SELECT * FROM hot_drinks WHERE is_active = TRUE");
if ($result && $result->num_rows > 0) {
    $hot_drinks = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $hot_drinks = [];
}

// Fetch cake sizes with error handling
$result = $conn->query("SELECT * FROM cake_sizes");
if ($result && $result->num_rows > 0) {
    $cake_sizes = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $cake_sizes = [];
}

// Fetch cake flavors with error handling
$result = $conn->query("SELECT name FROM flavors WHERE type = 'cake'");
if ($result && $result->num_rows > 0) {
    $cake_flavors = array_column($result->fetch_all(MYSQLI_NUM), 0);
} else {
    $cake_flavors = ['Vanilla', 'Chocolate', 'Strawberry'];
}

// Fetch ice cream flavors with error handling
$result = $conn->query("SELECT name FROM flavors WHERE type = 'ice_cream'");
if ($result && $result->num_rows > 0) {
    $ice_cream_flavors = array_column($result->fetch_all(MYSQLI_NUM), 0);
} else {
    $ice_cream_flavors = ['Vanilla', 'Chocolate', 'Strawberry'];
}

// Fetch soft drink flavors with error handling
$result = $conn->query("SELECT name FROM flavors WHERE type = 'soft_drink'");
if ($result && $result->num_rows > 0) {
    $soft_drink_flavors = array_column($result->fetch_all(MYSQLI_NUM), 0);
} else {
    $soft_drink_flavors = ['Original', 'Cola', 'Orange', 'Lemon'];
}

// Fetch hot drink flavors with error handling
$result = $conn->query("SELECT name FROM flavors WHERE type = 'hot_drink'");
if ($result && $result->num_rows > 0) {
    $hot_drink_flavors = array_column($result->fetch_all(MYSQLI_NUM), 0);
} else {
    $hot_drink_flavors = ['Regular', 'Strong', 'Light'];
}

// Fetch toppings with error handling
$result = $conn->query("SELECT name FROM toppings");
if ($result && $result->num_rows > 0) {
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $toppings = array_column($rows, 'name');
} else {
    $toppings = ['Chocolate Sauce', 'Caramel', 'Nuts', 'Sprinkles', 'Whipped Cream'];
}

// Set current page with default value
$current_page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marsilase Pastry - Premium Cakes & Desserts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-light: #F8E9D2; /* 60% - Light Caramel */
            --primary-medium: #C2865A; /* 30% - Warm Chocolate */
            --primary-dark: #4A2E2B; /* 10% - Deep Espresso */
            --text-dark: #4A2E2B;
            --text-medium: #6B3E2C;
            --text-light: #8B6B5E;
            --white: #FFFFFF;
            --shadow: 0 8px 32px rgba(74, 46, 43, 0.08);
            --shadow-lg: 0 16px 48px rgba(74, 46, 43, 0.12);
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --radius: 20px;
            --radius-sm: 12px;
            --glass: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-light);
            color: var(--text-medium);
            line-height: 1.7;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .display-font {
            font-family: 'Playfair Display', serif;
            color: var(--text-dark);
        }

        /* Glassmorphism Effects */
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
        }

        .glass-section {
            background: linear-gradient(135deg, rgba(248, 233, 210, 0.9) 0%, rgba(255, 255, 255, 0.8) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Header Styles */
        .app-header {
            background: rgba(248, 233, 210, 0.95) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(194, 134, 90, 0.2);
            transition: var(--transition);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary-dark) !important;
            transition: var(--transition);
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
            color: var(--primary-medium) !important;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            margin: 0 0.25rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            position: relative;
            color: var(--text-dark) !important;
        }

        .nav-link:hover {
            background: rgba(194, 134, 90, 0.1);
            transform: translateY(-2px);
            color: var(--primary-dark) !important;
        }

        .nav-link.active {
            color: var(--primary-dark) !important;
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 2px;
            background: var(--primary-dark);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-light) 0%, rgba(194, 134, 90, 0.1) 100%);
            padding: 8rem 0 6rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(194, 134, 90, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(74, 46, 43, 0.05) 0%, transparent 50%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary-dark);
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            font-weight: 400;
            margin-bottom: 2.5rem;
            color: var(--text-medium);
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        /* Section Titles */
        .section-title {
            position: relative;
            padding-bottom: 1rem;
            margin: 5rem 0 3rem;
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 2.8rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
            border-radius: 2px;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-medium);
            font-size: 1.2rem;
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        /* Product Cards */
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
            background: linear-gradient(135deg, var(--white) 0%, var(--primary-light) 100%);
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
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
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
            background: var(--primary-dark);
            color: var(--primary-light);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(74, 46, 43, 0.3);
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
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            font-family: 'Playfair Display', serif;
        }

        .product-description {
            color: var(--text-medium);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            flex-grow: 1;
            line-height: 1.6;
        }

        .product-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-medium);
            margin-bottom: 1.5rem;
        }

        /* Buttons */
        .btn {
            font-weight: 600;
            padding: 0.875rem 2rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(74, 46, 43, 0.3);
            color: var(--primary-light);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 46, 43, 0.4);
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
            color: var(--primary-light);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(194, 134, 90, 0.3);
            color: var(--primary-light);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(194, 134, 90, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
            color: var(--primary-light);
        }

        .btn-outline-primary {
            color: var(--primary-dark);
            border: 2px solid var(--primary-dark);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-dark);
            color: var(--primary-light);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(74, 46, 43, 0.3);
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary-medium);
            color: var(--primary-light);
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(194, 134, 90, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Loading Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 1s ease-out;
        }

        /* Hover Glow Effect */
        .hover-glow {
            transition: var(--transition);
        }

        .hover-glow:hover {
            box-shadow: 0 10px 30px rgba(194, 134, 90, 0.2);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--primary-dark);
            color: var(--primary-light);
            padding: 1rem 2rem;
            border-radius: 50px;
            box-shadow: var(--shadow-lg);
            z-index: 3000;
            transition: var(--transition);
            opacity: 0;
            backdrop-filter: blur(10px);
        }

        .toast.active {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* Loading Animation */
        .loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(248, 233, 210, 0.9);
            z-index: 4000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .loader.active {
            display: flex;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--primary-light);
            border-top: 4px solid var(--primary-medium);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .hero-section {
                padding: 6rem 0 4rem;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
            }
        }

        /* Utility Classes */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bg-gradient {
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
        }

        .shadow-card {
            box-shadow: var(--shadow);
        }

        .shadow-card-lg {
            box-shadow: var(--shadow-lg);
        }

        /* Features Section */
        .feature-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(194, 134, 90, 0.1);
            background: linear-gradient(135deg, var(--white) 0%, var(--primary-light) 100%);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--primary-light);
            font-size: 2rem;
            transition: var(--transition);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
            color: var(--primary-light);
            padding: 4rem 0 2rem;
            margin-top: 6rem;
        }

        .footer h5 {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--primary-light);
            font-family: 'Playfair Display', serif;
        }

        .footer a {
            color: rgba(248, 233, 210, 0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer a:hover {
            color: var(--primary-light);
            transform: translateX(5px);
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    
    <main class="app-main">
        <?php
        switch ($current_page) {
            case 'home':
                include 'pages/home.php';
                break;
            case 'customize-cake':
                include 'pages/customize-cake.php';
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
            case 'about':
                include 'pages/about.php';
                break;
            case 'testimonials':
                include 'pages/testimonials.php';
                break;
            case 'contact':
                include 'pages/contact.php';
                break;
            default:
                include 'pages/home.php';
                break;
        }
        ?>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <!-- Loading Animation -->
    <div class="loader" id="loader">
        <div class="spinner"></div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toast notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('active');
            
            setTimeout(() => {
                toast.classList.remove('active');
            }, 3000);
        }
        
        // Loader functions
        function showLoader() {
            document.getElementById('loader').classList.add('active');
        }
        
        function hideLoader() {
            document.getElementById('loader').classList.remove('active');
        }
        
        // Scroll animations
        function checkVisibility() {
            const productCards = document.querySelectorAll('.product-card');
            const testimonials = document.querySelectorAll('.testimonial');
            
            productCards.forEach(card => {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    card.classList.add('visible');
                }
            });
            
            testimonials.forEach(testimonial => {
                const rect = testimonial.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    testimonial.classList.add('visible');
                }
            });
        }
        
        window.addEventListener('scroll', checkVisibility);
        window.addEventListener('load', checkVisibility);
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.app-header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(248, 233, 210, 0.98)';
                header.style.backdropFilter = 'blur(20px)';
                header.style.boxShadow = '0 5px 20px rgba(74, 46, 43, 0.1)';
            } else {
                header.style.background = 'rgba(248, 233, 210, 0.95)';
                header.style.backdropFilter = 'blur(20px)';
                header.style.boxShadow = '0 5px 15px rgba(74, 46, 43, 0.08)';
            }
        });

        // Add smooth scrolling for anchor links
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

        // Initialize animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to all interactive elements
            const interactiveElements = document.querySelectorAll('.btn, .product-card, .card, .feature-card');
            interactiveElements.forEach(element => {
                element.classList.add('hover-glow');
            });
            
            // Initialize scroll animations
            checkVisibility();
        });
    </script>
</body>
</html>
<?php 
if (isset($conn)) {
    $conn->close();
}
ob_end_flush();
?>