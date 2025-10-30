<?php
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'] ?? 1;
    }
}
?>
<nav class="navbar navbar-expand-lg app-header" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand" href="?page=home">
           <h5><i class="fas fa-crown"> </i>Marsilase Pastry</h5>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="#menu" class="nav-link scroll-link <?= $current_page === 'home' ? 'active' : '' ?>">
                        Menu
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#about" class="nav-link scroll-link <?= $current_page === 'about' ? 'active' : '' ?>">
                        About
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#testimonials" class="nav-link scroll-link <?= $current_page === 'testimonials' ? 'active' : '' ?>">
                        Testimonials
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nav-link scroll-link <?= $current_page === 'contact' ? 'active' : '' ?>">
                        Contact
                    </a>
                </li>
            </ul>
            
            <div class="navbar-nav">
                <a href="?page=review" class="nav-link position-relative">
                    <i class="bi bi-cart3 me-1"></i>Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
.app-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    box-shadow: 0 2px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(139, 69, 19, 0.1);
}

.app-header.scrolled {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(25px);
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    padding: 0.7rem 0;
}

.navbar-brand {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 1.8rem;
    color: #8B4513;
    transition: all 0.3s ease;
}

.navbar-brand:hover {
    color: #A0522D;
    transform: translateY(-1px);
}

.nav-link {
    font-weight: 500;
    color: #333 !important;
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover {
    background: rgba(139, 69, 19, 0.08);
    color: #8B4513 !important;
    transform: translateY(-1px);
}

.nav-link.active {
    color: #8B4513 !important;
    font-weight: 600;
}

.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 20px;
    height: 2px;
    background: #D4AF37;
    border-radius: 1px;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    border-radius: 12px;
    padding: 0.5rem;
    margin-top: 0.5rem;
}

.dropdown-item {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: rgba(139, 69, 19, 0.08);
    color: #8B4513;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #D4AF37;
    color: #2C1810;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(212, 175, 55, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.navbar-toggler {
    border: none;
    padding: 0.25rem 0.5rem;
}

.navbar-toggler:focus {
    box-shadow: none;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28139, 69, 19, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .navbar-nav {
        text-align: center;
        margin: 1rem 0;
    }
    
    .nav-link {
        margin: 0.25rem 0;
        padding: 0.75rem 1rem;
    }
    
    .dropdown-menu {
        text-align: center;
        box-shadow: none;
        border: 1px solid rgba(139, 69, 19, 0.1);
        margin: 0.5rem 0;
    }
}

@media (max-width: 576px) {
    .navbar-brand {
        font-size: 1.5rem;
    }
    
    .app-header {
        padding: 0.8rem 0;
    }
    
    .app-header.scrolled {
        padding: 0.5rem 0;
    }
}
</style>

<script>
// Scroll effect for header
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('mainNavbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Close mobile menu when clicking on a link
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse.classList.contains('show')) {
                navbarToggler.click();
            }
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('.scroll-link').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            
            // If clicking on Menu and we're not on home page, redirect to home first
            if (targetId === '#menu' && !window.location.href.includes('page=home')) {
                window.location.href = '?page=home' + targetId;
                return;
            }
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Calculate the offset to account for fixed header
                const headerHeight = document.getElementById('mainNavbar').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update URL without page reload
                if (history.pushState) {
                    history.pushState(null, null, targetId);
                }
            }
        });
    });
    
    // Highlight active section in navigation
    function highlightActiveSection() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.scroll-link');
        
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            const headerHeight = document.getElementById('mainNavbar').offsetHeight;
            
            if (window.scrollY >= (sectionTop - headerHeight - 100)) {
                currentSection = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }
    
    window.addEventListener('scroll', highlightActiveSection);
    
    // Handle direct anchor links on page load
    if (window.location.hash) {
        const targetElement = document.querySelector(window.location.hash);
        if (targetElement) {
            setTimeout(() => {
                const headerHeight = document.getElementById('mainNavbar').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }, 100);
        }
    }
});
</script>