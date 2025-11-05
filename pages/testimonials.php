[file name]: pages/testimonials.php
[file content begin]
<div class="section">
    <div class="container-narrow">
        <div class="text-center mb-5" data-animate>
            <h1 class="display-4 display-font mb-3">Customer Testimonials</h1>
            <p class="text-lead">Hear what our delighted customers have to say about their Marsilase experience</p>
        </div>

        <div class="row g-4 mb-5">
            <?php
            $testimonials = [
                [
                    'name' => 'Sarah M.',
                    'location' => 'Addis Ababa',
                    'rating' => 5,
                    'content' => 'The Chocolate Fantasy cake was absolutely incredible! It made my daughter\'s birthday party unforgettable. The quality and taste were beyond expectations.',
                    'image' => 'bi-person'
                ],
                [
                    'name' => 'Michael T.',
                    'location' => 'Business Owner',
                    'rating' => 5,
                    'content' => 'I\'ve ordered multiple times for corporate events, and Marsilase never disappoints. Professional service, stunning presentation, and exceptional taste every time.',
                    'image' => 'bi-person'
                ],
                [
                    'name' => 'Elena K.',
                    'location' => 'Newlywed',
                    'rating' => 5,
                    'content' => 'Our wedding cake was a masterpiece! The team understood our vision perfectly and delivered beyond our dreams. Every guest complimented the taste and design.',
                    'image' => 'bi-person'
                ],
                [
                    'name' => 'David P.',
                    'location' => 'Regular Customer',
                    'rating' => 5,
                    'content' => 'As a regular customer, I can confidently say Marsilase consistently delivers excellence. Their attention to detail and customer service is unmatched.',
                    'image' => 'bi-person'
                ],
                [
                    'name' => 'Amina J.',
                    'location' => 'Event Planner',
                    'rating' => 5,
                    'content' => 'I work with Marsilase for all my client events. Their reliability, creativity, and exceptional quality make them my go-to pastry shop.',
                    'image' => 'bi-person'
                ],
                [
                    'name' => 'Thomas R.',
                    'location' => 'Food Critic',
                    'rating' => 5,
                    'content' => 'Having tasted pastries across the city, Marsilase stands out for their perfect balance of flavor, texture, and visual appeal. Truly exceptional craftsmanship.',
                    'image' => 'bi-person'
                ]
            ];
            ?>

            <?php foreach ($testimonials as $testimonial): ?>
            <div class="col-lg-4" data-animate>
                <div class="card h-100">
                    <div class="card-body">
                        <!-- Rating -->
                        <div class="mb-3">
                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                <i class="bi bi-star-fill text-warning"></i>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Testimonial Content -->
                        <p class="testimonial-content mb-4">"<?= $testimonial['content'] ?>"</p>
                        
                        <!-- Author -->
                        <div class="testimonial-author">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3" 
                                     style="width: 50px; height: 50px;">
                                    <i class="<?= $testimonial['image'] ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?= $testimonial['name'] ?></h6>
                                    <small class="text-muted"><?= $testimonial['location'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA Section -->
        <div class="card bg-primary text-white text-center">
            <div class="card-body py-5">
                <h2 class="display-6 display-font mb-3">Ready to Experience Marsilase?</h2>
                <p class="mb-4 opacity-75">Join our satisfied customers and taste the difference today.</p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="?page=home" class="btn btn-light">
                        <i class="bi bi-star me-2"></i>
                        Order Now
                    </a>
                    <a href="?page=contact" class="btn btn-outline-light">
                        <i class="bi bi-chat me-2"></i>
                        Get In Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.testimonial-content {
    font-style: italic;
    line-height: 1.7;
    color: var(--neutral-700);
}

.btn-outline-light {
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    background: transparent;
}

.btn-outline-light:hover {
    background: white;
    color: var(--primary-600);
}

.opacity-75 {
    opacity: 0.75;
}
</style>
[file content end]