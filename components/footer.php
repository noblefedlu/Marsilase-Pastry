<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marsila's Pastry - Footer Redesign</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f9f5f0;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .main-content {
            padding: 2rem;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .demo-notice {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .demo-notice h1 {
            font-family: 'Playfair Display', serif;
            color: #8B4513;
            margin-bottom: 1rem;
        }

        .demo-notice p {
            color: #666;
            line-height: 1.6;
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #2C1810 0%, #1A0F08 100%);
            color: #FDF6E3;
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,70 Q50,60 100,70 L100,100 L0,100 Z" fill="rgba(212, 175, 55, 0.05)"/></svg>');
            background-size: cover;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h5 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            color: #FDF6E3;
            position: relative;
            padding-bottom: 0.8rem;
        }

        .footer-section h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: #D4AF37;
            border-radius: 2px;
        }

        .brand-section h5 {
            display: flex;
            align-items: center;
            font-size: 1.6rem;
        }

        .brand-section h5 i {
            margin-right: 10px;
            color: #D4AF37;
            font-size: 1.8rem;
        }

        .footer-description {
            color: rgba(253, 246, 227, 0.8);
            line-height: 1.7;
            margin-bottom: 1.8rem;
            font-size: 1.05rem;
        }

        .contact-info {
            margin-top: 1.5rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            color: rgba(253, 246, 227, 0.8);
        }

        .contact-item i {
            color: #D4AF37;
            margin-right: 12px;
            margin-top: 3px;
            font-size: 1.1rem;
            width: 20px;
        }

        .footer-links {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .footer-link {
            color: rgba(253, 246, 227, 0.7);
            text-decoration: none;
            display: block;
            padding: 0.6rem 0;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 0;
            font-weight: 400;
        }

        .footer-link:hover {
            color: #D4AF37;
            transform: translateX(8px);
        }

        .footer-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 1px;
            background: #D4AF37;
            transition: width 0.3s ease;
        }

        .footer-link:hover::before {
            width: 20px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.8rem;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: rgba(212, 175, 55, 0.1);
            color: #D4AF37;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .social-link:hover {
            background: #D4AF37;
            color: #2C1810;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
        }

        .newsletter-form {
            margin-top: 1.5rem;
        }

        .form-group {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .newsletter-input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.05);
            color: #FDF6E3;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .newsletter-input:focus {
            outline: none;
            border-color: #D4AF37;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }

        .newsletter-input::placeholder {
            color: rgba(253, 246, 227, 0.5);
        }

        .newsletter-btn {
            padding: 0.8rem 1.5rem;
            background: #D4AF37;
            color: #2C1810;
            border: none;
            border-radius: 30px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .newsletter-btn:hover {
            background: #e6c257;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .footer-divider {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent);
            margin: 2.5rem 0 1.5rem;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-copyright {
            color: rgba(253, 246, 227, 0.6);
            font-size: 0.95rem;
        }

        .footer-legal {
            display: flex;
            gap: 1.5rem;
        }

        .legal-link {
            color: rgba(253, 246, 227, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .legal-link:hover {
            color: #D4AF37;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .footer {
                padding: 3rem 0 1.5rem;
                text-align: center;
            }
            
            .footer-section h5::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer-link:hover {
                transform: translateX(0);
            }
            
            .social-links {
                justify-content: center;
            }
            
            .form-group {
                flex-direction: column;
            }
            
            .newsletter-btn {
                width: 100%;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-legal {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 2.5rem;
            }
        }
    </style>
</head>
<body>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Brand Section -->
                <div class="footer-section brand-section">
                    <h5><i class="fas fa-crown"></i>Marsilase Pastry</h5>
                    <p class="footer-description">Luxury handcrafted pastries in Nazrät, Ethiopia. Where Ethiopian heritage meets French pastry artistry. Elegance in every bite.</p>
                    
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Follow us on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Follow us on Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Follow us on TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Follow us on Pinterest">
                            <i class="fab fa-pinterest"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#menu" class="footer-link">Our Menu</a></li>
                        <li><a href="#about" class="footer-link">Our Story</a></li>
                        <li><a href="#gallery" class="footer-link">Gallery</a></li>
                        <li><a href="#testimonials" class="footer-link">Testimonials</a></li>
                        <li><a href="#contact" class="footer-link">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="footer-section">
                    <h5>Our Services</h5>
                    <ul class="footer-links">
                        <li><a href="#wedding" class="footer-link">Wedding Cakes</a></li>
                        <li><a href="#corporate" class="footer-link">Corporate Events</a></li>
                        <li><a href="#custom" class="footer-link">Custom Orders</a></li>
                        <li><a href="#delivery" class="footer-link">Delivery & Pickup</a></li>
                        <li><a href="#catering" class="footer-link">Catering Services</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="footer-section">
                    <div class="opening-hours" style="margin-top: 1.5rem;">
                        <p style="color: rgba(253, 246, 227, 0.8); font-size: 0.95rem;">
                            <strong>Opening Hours:</strong><br>
                            Mon - Sun: 7:00 AM - 7:00 PM
                        </p>
                    </div>
                </div>
            </div>
            
            <hr class="footer-divider">
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; 2025 Marsila's Pastry — Elegance in Every Bite. All rights reserved.</p>
                </div>
                <div class="footer-legal">
                    <a href="#" class="legal-link">Privacy Policy</a>
                    <a href="#" class="legal-link">Terms of Service</a>
                    <a href="#" class="legal-link">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>