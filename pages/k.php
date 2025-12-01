<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOBA Patisserie & Bakery</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #fef9f5;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fef9f5 0%, #fff8f0 100%);
            overflow: hidden;
            padding: 2rem 0;
        }
        
        .container-narrow {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            width: 100%;
        }
        
        .hero-content {
            text-align: center;
            z-index: 2;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: #333;
            letter-spacing: 1px;
        }
        
        .hero-highlight {
            color: #d4a574;
            display: block;
            font-size: 4.5rem;
            margin-top: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            display: inline-block;
        }
        
        /* Glass effect overlay */
        .glass-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.3) 0%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(255, 255, 255, 0.3) 100%
            );
            border-radius: 8px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6),
                inset 0 -1px 0 rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 1;
            pointer-events: none;
        }
        
        /* Frosted glass background */
        .glass-bg {
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            z-index: 0;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: #8b7355;
            margin-bottom: 2.5rem;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
        }
        
        @media (min-width: 576px) {
            .btn-container {
                flex-direction: row;
            }
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.85rem 2rem;
            font-weight: 500;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .btn:hover::before {
            opacity: 1;
        }
        
        .btn-primary {
            background: #d4a574;
            color: white;
            border: 2px solid #d4a574;
        }
        
        .btn-primary:hover {
            background: #c1915f;
            border-color: #c1915f;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
        }
        
        .btn-secondary {
            background: transparent;
            color: #8b7355;
            border: 2px solid #8b7355;
        }
        
        .btn-secondary:hover {
            background: #8b7355;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(139, 115, 85, 0.3);
        }
        
        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .floating-element {
            position: absolute;
            font-size: 2rem;
            color: rgba(212, 165, 116, 0.15);
            animation: float 8s ease-in-out infinite;
            z-index: 1;
        }
        
        .element-1 {
            top: 15%;
            left: 8%;
            animation-delay: 0s;
        }
        
        .element-2 {
            top: 65%;
            left: 5%;
            animation-delay: 1.5s;
        }
        
        .element-3 {
            top: 25%;
            right: 8%;
            animation-delay: 3s;
        }
        
        .element-4 {
            top: 75%;
            right: 5%;
            animation-delay: 4.5s;
        }
        
        .element-5 {
            top: 45%;
            left: 12%;
            animation-delay: 2s;
        }
        
        .element-6 {
            top: 55%;
            right: 12%;
            animation-delay: 5s;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-25px) rotate(5deg);
            }
            100% {
                transform: translateY(0) rotate(0deg);
            }
        }
        
        .hero-decoration {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(212, 165, 116, 0.05);
            z-index: 0;
        }
        
        .decoration-1 {
            top: -100px;
            right: -50px;
            width: 300px;
            height: 300px;
        }
        
        .decoration-2 {
            bottom: -100px;
            left: -50px;
            width: 250px;
            height: 250px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-highlight {
                font-size: 3.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.3rem;
            }
            
            .floating-element {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-highlight {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
                letter-spacing: 1px;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
            
            .floating-element {
                font-size: 1.25rem;
            }
        }
        
        .features-preview {
            margin-top: 4rem;
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .feature-preview {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #8b7355;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .feature-preview i {
            color: #d4a574;
        }
        
        /* Enhanced glass effect for KOBA text */
        .koba-container {
            position: relative;
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
        }
        
        .koba-text {
            font-size: 4.5rem;
            font-weight: 700;
            color: #d4a574;
            text-shadow: 
                2px 2px 4px rgba(0, 0, 0, 0.1),
                0 0 20px rgba(255, 255, 255, 0.5);
            position: relative;
            z-index: 2;
        }
        
        .koba-glass {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.4) 0%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(255, 255, 255, 0.4) 100%
            );
            border-radius: 8px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8),
                inset 0 -1px 0 rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1;
            pointer-events: none;
        }
        
        .koba-reflections {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                transparent 0%,
                rgba(255, 255, 255, 0.3) 50%,
                transparent 100%
            );
            border-radius: 8px;
            z-index: 3;
            pointer-events: none;
            animation: shine 3s ease-in-out infinite;
        }
        
        @keyframes shine {
            0%, 100% {
                opacity: 0.5;
                transform: translateX(-100%);
            }
            50% {
                opacity: 0.8;
            }
            100% {
                transform: translateX(100%);
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container-narrow">
            <div class="hero-content">
                <h1 class="hero-title">
                    WELCOME TO<br>
                    <div class="koba-container">
                        <div class="koba-glass"></div>
                        <div class="koba-reflections"></div>
                        <span class="koba-text">KOBA</span>
                    </div>
                </h1>
                <p class="hero-subtitle">
                    PATISSERIE & BAKERY
                </p>
                <div class="btn-container">
                    <a href="?page=all-products" class="btn btn-primary">
                        <i class="bi bi-star me-2"></i>
                        EXPLORE KOBA
                    </a>
                    <a href="?page=contact" class="btn btn-secondary">
                        <i class="bi bi-telephone me-2"></i>
                        Contact us
                    </a>
                </div>
                
                <div class="features-preview">
                    <div class="feature-preview">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Fresh Daily</span>
                    </div>
                    <div class="feature-preview">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Premium Ingredients</span>
                    </div>
                    <div class="feature-preview">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Custom Orders</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-background">
            <div class="hero-decoration decoration-1"></div>
            <div class="hero-decoration decoration-2"></div>
            
            <div class="floating-element element-1">
                <i class="bi bi-cake2"></i>
            </div>
            <div class="floating-element element-2">
                <i class="bi bi-cupcake"></i>
            </div>
            <div class="floating-element element-3">
                <i class="bi bi-balloon-heart"></i>
            </div>
            <div class="floating-element element-4">
                <i class="bi bi-gift"></i>
            </div>
            <div class="floating-element element-5">
                <i class="bi bi-flower1"></i>
            </div>
            <div class="floating-element element-6">
                <i class="bi bi-stars"></i>
            </div>
        </div>
    </section>
</body>
</html>