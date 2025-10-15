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
    $cakes = []; // Ensure it's always an array
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
    $cake_flavors = ['Vanilla', 'Chocolate', 'Strawberry']; // Default flavors
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
    <title>Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #8B4513;
            --secondary: #D4A574;
            --accent: #FF6B6B;
            --light: #FFF8F0;
            --dark: #5D4037;
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
        }

        body {
            background-color: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
        }

        .app-header { 
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .hero-section { 
            background: linear-gradient(rgba(139, 69, 19, 0.85), rgba(93, 64, 55, 0.85)), 
                        url('https://images.unsplash.com/photo-1555507036-ab794f27d2e9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            border-radius: 16px;
            padding: 4rem 2rem;
            margin-bottom: 3rem;
        }

        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
            color: var(--primary);
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .product-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            background: white;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .product-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .product-thumb {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background-size: cover;
            background-position: center;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--dark);
            border-color: var(--dark);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }

        .btn-success:hover {
            background-color: #3d8b40;
            border-color: #3d8b40;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .customization-option {
            margin-bottom: 1.5rem;
        }

        .option-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .option-title i {
            margin-right: 8px;
        }

        .flavor-btn, .topping-btn, .size-btn, .extra-btn, .temp-btn {
            transition: all 0.2s ease;
            margin: 4px;
            border-radius: 20px;
            font-weight: 500;
        }

        .summary-sticky {
            position: sticky;
            top: 20px;
            background: white;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .quantity-display {
            min-width: 50px;
            text-align: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .order-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .order-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }

        .admin-panel {
            background-color: #f8f9fa;
        }

        .admin-card {
            border-left: 4px solid var(--primary);
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }

        .nav-link {
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--secondary) !important;
        }

        .badge-primary {
            background-color: var(--primary);
        }

        .footer {
            background-color: var(--dark);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1rem;
            }
            
            .section-title:after {
                width: 60px;
            }
            
            .product-card {
                margin-bottom: 1.5rem;
            }
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
?>