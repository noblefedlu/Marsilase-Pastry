<section id="about" class="about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content">
                <h2>Our Story</h2>
                <p class="lead">For over a decade, Marsilase Pastries has been creating moments of joy through our artisanal baked goods.</p>
                <p>Founded in 2010, we combine traditional techniques with innovative flavors to bring you the finest pastries in Addis Ababa. Every product is crafted with love, attention to detail, and the highest quality ingredients.</p>
                <div class="about-stats">
                    <div class="stat">
                        <span class="stat-number">12+</span>
                        <span class="stat-label">Years Experience</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">50K+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">100+</span>
                        <span class="stat-label">Products</span>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <div class="image-frame">
                    <i class="bi bi-shop-window"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.about-section {
    padding: 100px 0;
    background: var(--gray);
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.about-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    font-family: 'Playfair Display', serif;
    color: var(--dark);
}

.about-content .lead {
    font-size: 1.2rem;
    color: var(--primary);
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.about-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-top: 2rem;
}

.stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-light);
}

.about-image {
    text-align: center;
}

.image-frame {
    width: 100%;
    height: 400px;
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 4rem;
}

@media (max-width: 768px) {
    .about-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .about-stats {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>