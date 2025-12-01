<?php
session_start();
require_once '../common/connection.php';
requireOwner();

$product_id = $_GET['id'] ?? 0;

global $conn;

// Get product info
$stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get reviews
$reviews = [];
$stmt = $conn->prepare("
    SELECT r.*, COALESCE(c.name, 'Anonymous') as customer_name 
    FROM reviews r 
    LEFT JOIN customers c ON r.customer_id = c.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get average rating
$avg_result = $conn->query("
    SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
    FROM reviews 
    WHERE product_id = $product_id
");
$avg_rating = $avg_result ? $avg_result->fetch_assoc() : ['avg_rating' => 0, 'total_reviews' => 0];
?>

<div class="reviews-container">
    <h6 class="fw-bold text-primary mb-3">
        Reviews for: <?= htmlspecialchars($product['name']) ?>
    </h6>
    
    <!-- Rating Summary -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h2 class="text-primary fw-bold mb-0">
                        <?= number_format($avg_rating['avg_rating'] ?? 0, 1) ?>
                    </h2>
                    <div class="rating-stars mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= round($avg_rating['avg_rating'] ?? 0) ? '-fill' : '' ?> fs-5"></i>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted"><?= $avg_rating['total_reviews'] ?? 0 ?> reviews</small>
                </div>
                <div class="col-md-8">
                    <p class="text-muted mb-0">Customer feedback and ratings for this product.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <?php if (empty($reviews)): ?>
        <div class="text-center py-4">
            <i class="bi bi-chat-text display-4 text-muted mb-3"></i>
            <h5 class="text-muted">No reviews yet</h5>
            <p class="text-muted">This product hasn't received any reviews yet.</p>
        </div>
    <?php else: ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-semibold mb-1"><?= htmlspecialchars($review['customer_name']) ?></h6>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                                <small class="text-muted ms-2"><?= date('M j, Y', strtotime($review['created_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($review['comment'])): ?>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>