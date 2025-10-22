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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8B4513;
            --primary-dark: #654321;
            --primary-light: #A0522D;
            --secondary: #D4A574;
            --accent: #FF6B6B;
            --light: #FFF8F0;
            --light-alt: #FDF6E3;
            --dark: #5D4037;
            --text: #2D3748;
            --text-light: #718096;
            --success: #48BB78;
            --warning: #ED8936;
            --danger: #F56565;
            --border: #E2E8F0;
            --shadow: 0 10px 25px rgba(0,0,0,0.05);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.1);
            --radius: 16px;
            --radius-sm: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, var(--light-alt) 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header Styles */
        .app-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.75rem;
            background: linear-gradient(135deg, #fff 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--secondary);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 70%;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, 
                rgba(139, 69, 19, 0.9) 0%, 
                rgba(93, 64, 55, 0.9) 100%),
                url('https://images.unsplash.com/photo-1555507036-ab794f27d2e9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            border-radius: var(--radius);
            padding: 6rem 2rem;
            margin: 2rem auto;
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
            background: linear-gradient(45deg, 
                rgba(139, 69, 19, 0.4) 0%, 
                rgba(212, 165, 116, 0.3) 50%, 
                rgba(139, 69, 19, 0.4) 100%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Section Titles */
        .section-title {
            position: relative;
            padding-bottom: 1rem;
            margin: 4rem 0 3rem;
            text-align: center;
            font-weight: 700;
            color: var(--primary);
            font-size: 2.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 2px;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Product Cards */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .product-card {
            background: white;
            border: none;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
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
            height: 220px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary) 100%);
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(255,107,107,0.3);
        }

        .product-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .product-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        /* Buttons */
        .btn {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 69, 19, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #38A169 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(72, 187, 120, 0.4);
        }

        .btn-outline-primary {
            color: var(--primary);
            border: 2px solid var(--primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(255,107,107,0.3);
        }

        /* Customization Pages */
        .customization-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .option-group {
            margin-bottom: 2rem;
        }

        .option-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .option-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .option-btn {
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            background: white;
            color: var(--text);
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .option-btn:hover, .option-btn.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.2);
        }

        /* Summary Card */
        .summary-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            position: sticky;
            top: 2rem;
        }

        .summary-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }

        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border);
        }

        /* Review Page */
        .cart-item {
            background: white;
            border-radius: var(--radius-sm);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--dark) 100%);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }

        .footer h5 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--secondary);
        }

        .footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary);
        }

        /* Loading Animations */
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
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .hero-section {
                padding: 4rem 1rem;
                margin: 1rem;
            }
        }

        /* Utility Classes */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bg-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .shadow-card {
            box-shadow: var(--shadow);
        }

        .shadow-card-lg {
            box-shadow: var(--shadow-lg);
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
            case 'customize-icecream':
                include 'pages/customize-icecream.php';
                break;
            case 'customize-softdrink':
                include 'pages/customize-softdrink.php';
                break;
            case 'customize-hotdrink':
                include 'pages/customize-hotdrink.php';
                break;
            case 'review':
                include 'pages/review.php';
                break;
            case 'thank-you':
                include 'pages/thank-you.php';
                break;
            default:
                include 'pages/home.php';
        }
        ?>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/app.js"></script>
</body>
</html>
<?php 
if (isset($conn)) {
    $conn->close();
}