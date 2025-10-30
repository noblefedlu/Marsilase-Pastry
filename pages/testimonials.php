<section id="testimonials" class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2>Customer Love</h2>
            <p>What our customers say about us</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <div class="stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p>"The Golden Celebration Cake made our anniversary absolutely magical! Exceptional quality and taste."</p>
                </div>
                <div class="testimonial-author">
                    <strong>Sarah M.</strong>
                    <span>Regular Customer</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <div class="stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p>"Butter croissants are perfection! Fresh, flaky, and buttery. My morning routine is now complete."</p>
                </div>
                <div class="testimonial-author">
                    <strong>Michael T.</strong>
                    <span>Food Enthusiast</span>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <div class="stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p>"The Royal Cupcakes were the highlight of my daughter's birthday. Beautiful and incredibly delicious!"</p>
                </div>
                <div class="testimonial-author">
                    <strong>Elena K.</strong>
                    <span>Happy Parent</span>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.testimonials-section {
    padding: 100px 0;
    background: var(--light);
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.testimonial-card {
    background: var(--white);
    padding: 2.5rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stars {
    color: var(--secondary);
    margin-bottom: 1rem;
}

.testimonial-content p {
    font-style: italic;
    color: var(--text);
    margin-bottom: 1.5rem;
    line-height: 1.7;
}

.testimonial-author {
    border-top: 1px solid var(--border);
    padding-top: 1rem;
}

.testimonial-author strong {
    display: block;
    color: var(--dark);
}

.testimonial-author span {
    font-size: 0.9rem;
    color: var(--text-light);
}

@media (max-width: 768px) {
    .testimonials-grid {
        grid-template-columns: 1fr;
    }
}
</style>