[file name]: thank-you.php
[file content begin]
<?php
// pages/thank-you.php
session_start();

// Define the root directory and config path
$root_dir = dirname(dirname(__FILE__));
$config_path = $root_dir . '/config.php';

// Check if config file exists before requiring it
if (!file_exists($config_path)) {
    die("Configuration file not found. Please check if config.php exists in the root directory.");
}

require_once $config_path;

// Check database connection
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

$order_id = $_GET['order_id'] ?? '';
$order = null;

// Try to get order from session first
if (isset($_SESSION['orders'][$order_id])) {
    $order = $_SESSION['orders'][$order_id];
} else {
    // If not in session, try to get from database
    if (!empty($order_id)) {
        $stmt = $conn->prepare("
            SELECT o.* 
            FROM orders o 
            WHERE o.order_number = ?
        ");
        
        if ($stmt) {
            $stmt->bind_param("s", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $db_order = $result->fetch_assoc();
            $stmt->close();
            
            if ($db_order) {
                // Get order items
                $stmt = $conn->prepare("
                    SELECT * FROM order_items 
                    WHERE order_id = ?
                ");
                
                if ($stmt) {
                    $stmt->bind_param("i", $db_order['id']);
                    $stmt->execute();
                    $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                    
                    $order = [
                        'order_id' => $db_order['order_number'],
                        'customer_info' => [
                            'name' => $db_order['customer_name'],
                            'phone' => $db_order['customer_phone'],
                            'email' => $db_order['customer_email']
                        ],
                        'delivery_info' => [
                            'address' => $db_order['delivery_address'],
                            'date' => $db_order['delivery_date'],
                            'time' => $db_order['delivery_time']
                        ],
                        'order_items' => $order_items,
                        'pricing' => [
                            'total' => $db_order['total_amount']
                        ],
                        'order_status' => $db_order['status']
                    ];
                }
            }
        }
    }
}

if (empty($order_id) || empty($order)) {
    echo '
    <div class="section">
        <div class="container-narrow text-center">
            <div class="card">
                <div class="card-body py-5">
                    <i class="bi bi-exclamation-triangle display-1 text-warning mb-3"></i>
                    <h3 class="mb-3">Order Not Found</h3>
                    <p class="text-muted mb-4">The order you are looking for could not be found.</p>
                    <a href="?page=home" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>';
    
    // Don't close connection here as it might not be established
    if (isset($conn) && $conn) {
        $conn->close();
    }
    return;
}
?>

<style>
.bg-light {
    background: var(--neutral-100) !important;
    color: var(--text-dark) !important;
}

.bg-light p, .bg-light strong {
    color: var(--text-dark) !important;
}

.text-muted {
    color: var(--text-muted) !important;
}

.badge {
    background: var(--primary-100) !important;
}
</style>

<div class="section">
    <div class="container-narrow text-center">
        <div class="card">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill display-1 text-success mb-3"></i>
                    <h1 class="display-5 display-font mb-3">Order Confirmed!</h1>
                    <p class="lead text-muted mb-4">Thank you for your order. We're preparing it with care.</p>
                </div>

                <div class="row justify-content-center mb-5">
                    <div class="col-md-8">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="mb-3">Order Details</h5>
                                <div class="row text-start">
                                    <div class="col-md-6">
                                        <p><strong>Order ID:</strong><br><?= $order['order_id'] ?></p>
                                        <p><strong>Customer Name:</strong><br><?= htmlspecialchars($order['customer_info']['name']) ?></p>
                                        <p><strong>Phone:</strong><br><?= htmlspecialchars($order['customer_info']['phone']) ?></p>
                                        <p><strong>Email:</strong><br><?= htmlspecialchars($order['customer_info']['email']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Delivery Date:</strong><br><?= date('F j, Y', strtotime($order['delivery_info']['date'])) ?></p>
                                        <p><strong>Delivery Time:</strong><br><?= $order['delivery_info']['time'] ?></p>
                                        <p><strong>Status:</strong><br>
                                            <span class="badge bg-<?= 
                                                $order['order_status'] === 'delivered' ? 'success' : 
                                                ($order['order_status'] === 'pending' ? 'warning' : 'secondary')
                                            ?>">
                                                <?= ucfirst($order['order_status']) ?>
                                            </span>
                                        </p>
                                        <p><strong>Total Amount:</strong><br>ETB <?= number_format($order['pricing']['total'], 2) ?></p>
                                    </div>
                                </div>
                                
                                <?php if (!empty($order['delivery_info']['address'])): ?>
                                <div class="mt-3">
                                    <p><strong>Delivery Address:</strong><br><?= nl2br(htmlspecialchars($order['delivery_info']['address'])) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-center">
                    <a href="?page=home" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}