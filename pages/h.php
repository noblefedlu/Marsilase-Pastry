<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOBA Pastry - Our Story & Take Care</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            line-height: 1.6;
            background-color: #fff;
        }
        
        .section {
            padding: 100px 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .our-story-section {
            background-color: #fff;
        }
        
        .take-care-section {
            background-color: #f9f7f4;
        }
        
        .content-wrapper {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 80px;
        }
        
        .text-content {
            flex: 1;
            min-width: 400px;
        }
        
        .image-content {
            flex: 1;
            min-width: 400px;
        }
        
        .bakery-image {
            width: 100%;
            height: 500px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 700;
            color: #000;
            margin-bottom: 30px;
            line-height: 1.3;
            letter-spacing: 0.5px;
        }
        
        .our-story-title {
            text-transform: uppercase;
            font-size: 36px;
        }
        
        .subtitle {
            font-size: 20px;
            color: #8B6B4D;
            margin-bottom: 25px;
            line-height: 1.5;
            font-weight: 300;
        }
        
        .description {
            font-size: 16px;
            color: #333;
            margin-bottom: 30px;
            line-height: 1.7;
            font-weight: 400;
        }
        
        .more-about-btn {
            display: inline-flex;
            align-items: center;
            color: #8B4513;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .more-about-btn:hover {
            color: #A0522D;
            transform: translateX(5px);
        }
        
        .more-about-btn::after {
            content: '→';
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        
        .more-about-btn:hover::after {
            transform: translateX(3px);
        }
        
        /* Take Care Section Styles */
        .take-care-content {
            text-align: center;
        }
        
        .take-care-title {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .changing-text {
            position: relative;
            display: inline-block;
            width: 120px;
            height: 50px;
            margin-left: 8px;
        }
        
        .text-variant {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 0.5s ease;
            font-size: 42px;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
        }
        
        .text-variant.active {
            opacity: 1;
        }
        
        .highlight-box {
            margin: 30px 0;
            padding-left: 0;
        }
        
        .highlight-text {
            font-size: 16px;
            color: #333;
            line-height: 1.7;
            font-weight: 400;
            position: relative;
            text-align: left;
        }
        
        .dropdown-arrow {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 2px solid #8B4513;
            border-radius: 50%;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
        }
        
        .dropdown-arrow:hover {
            background-color: #8B4513;
            transform: translateY(5px);
        }
        
        .dropdown-arrow::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            border-right: 2px solid #8B4513;
            border-bottom: 2px solid #8B4513;
            transform: translate(-50%, -60%) rotate(45deg);
            transition: all 0.3s ease;
        }
        
        .dropdown-arrow:hover::after {
            border-color: white;
            transform: translate(-50%, -50%) rotate(45deg);
        }
        
        @media (max-width: 968px) {
            .content-wrapper {
                flex-direction: column;
                gap: 50px;
            }
            
            .text-content, .image-content {
                min-width: 100%;
            }
            
            h2 {
                font-size: 36px;
            }
            
            .our-story-title {
                font-size: 32px;
            }
        }
        
        @media (max-width: 768px) {
            .section {
                padding: 60px 20px;
            }
            
            .changing-text {
                width: 100px;
                height: 45px;
            }
            
            .text-variant {
                font-size: 36px;
            }
            
            .bakery-image {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- Our Story Section -->
    <section id="our-story" class="section our-story-section">
        <div class="container">
            <div class="content-wrapper">
                <div class="text-content">
                    <h2 class="our-story-title">OUR STORY</h2>
                    <p class="subtitle">
                        KOBA is a story of craftsmanship, excellence, and shared indulgence.
                    </p>
                    <p class="description">
                        We are a pastry and bakery place in Addis, where you'll ever find the tastiest, freshest, and most delicious handmade treats.
                    </p>
                    <a href="#take-care" class="more-about-btn">MORE ABOUT US</a>
                </div>
                
                <div class="image-content">
                    <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" 
                         alt="KOBA Bakery Interior" 
                         class="bakery-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Take Care Section -->
    <section id="take-care" class="section take-care-section">
        <div class="container">
            <div class="take-care-content">
                <h2 class="take-care-title">
                    Take 
                    <div class="changing-text">
                        <span class="text-variant active">Cake</span>
                        <span class="text-variant">Care</span>
                    </div>
                </h2>
                
                <p class="description">
                    Not only are our pastry chefs highly skilled, but they also have a great deal of passion for what they do. To create unique and tasty pastries that you won't find anywhere else, they are always experimenting with new tastes and ingredients.
                </p>
                
                <div class="highlight-box">
                    <p class="highlight-text">
                        Attention to detail is the secret to making pastries that are genuinely remarkable. Every aspect of our pastries is meticulously planned and constructed, from the ideal sweetness balance to the texture and presentation.
                    </p>
                </div>
                
                <div class="dropdown-arrow" onclick="scrollToNext()"></div>
            </div>
        </div>
    </section>

    <script>
        // Text animation between "Cake" and "Care"
        const textVariants = document.querySelectorAll('.text-variant');
        let currentIndex = 0;
        
        function toggleText() {
            // Hide current text
            textVariants[currentIndex].classList.remove('active');
            
            // Move to next text
            currentIndex = (currentIndex + 1) % textVariants.length;
            
            // Show next text
            textVariants[currentIndex].classList.add('active');
        }
        
        // Start the animation
        setInterval(toggleText, 3000);
        
        // More About Us button functionality
        document.querySelector('.more-about-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('take-care').scrollIntoView({
                behavior: 'smooth'
            });
        });
        
        // Dropdown arrow functionality
        function scrollToNext() {
            // Scroll back to Our Story section
            document.getElementById('our-story').scrollIntoView({
                behavior: 'smooth'
            });
        }
        
        // Add bounce animation to arrow
        const arrow = document.querySelector('.dropdown-arrow');
        setInterval(() => {
            arrow.style.transform = 'translateY(0)';
            setTimeout(() => {
                arrow.style.transform = 'translateY(5px)';
            }, 1500);
        }, 3000);
    </script>
</body>
</html>