<section id="contact" class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Get In Touch</h2>
                <p>We'd love to hear from you. Reach out for orders, inquiries, or just to say hello!</p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <strong>Address</strong>
                            <p>Addis Ababa, Ethiopia</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-telephone"></i>
                        <div>
                            <strong>Phone</strong>
                            <p>+251-XXX-XXXX</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-envelope"></i>
                        <div>
                            <strong>Email</strong>
                            <p>marsilasepastries@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-clock"></i>
                        <div>
                            <strong>Hours</strong>
                            <p>Mon-Sun: 7:00 AM - 9:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <form id="contactForm">
                    <div class="form-group">
                        <input type="text" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea placeholder="Your Message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.contact-section {
    padding: 100px 0;
    background: var(--white);
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
}

.contact-info h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-family: 'Playfair Display', serif;
    color: var(--dark);
}

.contact-info > p {
    font-size: 1.1rem;
    color: var(--text-light);
    margin-bottom: 2rem;
}

.contact-details {
    space-y: 1.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.contact-item i {
    color: var(--primary);
    font-size: 1.2rem;
    margin-top: 0.2rem;
}

.contact-item strong {
    display: block;
    color: var(--dark);
    margin-bottom: 0.2rem;
}

.contact-item p {
    color: var(--text-light);
    margin: 0;
}

.contact-form {
    background: var(--gray);
    padding: 2.5rem;
    border-radius: var(--radius);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 1rem;
    transition: var(--transition);
    background: var(--white);
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .contact-form {
        padding: 2rem;
    }
}
</style>

<script>
// Contact form handling
document.getElementById('contactForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    showLoader();
    
    setTimeout(() => {
        hideLoader();
        showToast('Thank you for your message! We will get back to you soon.');
        this.reset();
    }, 1500);
});
</script>