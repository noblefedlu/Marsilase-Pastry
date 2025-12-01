<?php
session_start();
include '../common/connection.php';

// Check if user is admin OR owner
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$is_owner = isset($_SESSION['owner_logged_in']) && $_SESSION['owner_logged_in'] === true;

if (!$is_admin && !$is_owner) {
    header('Location: ../admin/login.php');
    exit;
}

// If owner, check if they have manage_orders permission
if ($is_owner) {
    // Simple permission check for owners
    $owner_id = $_SESSION['owner_id'];
    $perm_stmt = $conn->prepare("SELECT permission_value FROM owner_permissions WHERE owner_id = ? AND permission_key = 'manage_orders'");
    $perm_stmt->bind_param("i", $owner_id);
    $perm_stmt->execute();
    $perm_result = $perm_stmt->get_result();
    $has_permission = $perm_result->num_rows > 0 && $perm_result->fetch_assoc()['permission_value'] == 1;
    $perm_stmt->close();
    
    if (!$has_permission && $_SESSION['owner_security_level'] !== 'full') {
        header('Location: ../owner/index.php?error=access_denied');
        exit;
    }
}

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    die("Order ID is required");
}

// Get order details with error handling
$order_query = "SELECT o.* FROM orders o WHERE o.id = ?";
$stmt = $conn->prepare($order_query);

if (!$stmt) {
    die("Error preparing order query: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found");
}

// Get order items with simplified query (removed image columns)
$items_query = "SELECT oi.*, 
                COALESCE(p.name, c.name) as product_name
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id AND oi.product_type = 'product'
                LEFT JOIN cakes c ON oi.product_id = c.id AND oi.product_type = 'cake'
                WHERE oi.order_id = ?";
                
$stmt = $conn->prepare($items_query);

if (!$stmt) {
    die("Error preparing items query: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Determine user type for navigation
$user_type = $is_admin ? 'admin' : 'owner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?= ucfirst($user_type) ?> Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #2c3e50;
            --owner-primary: #34495e;
        }
        
        .user-badge {
            background: #e74c3c;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .status-badge-pending { background: #ffc107; color: #000; }
        .status-badge-confirmed { background: #17a2b8; color: #fff; }
        .status-badge-preparing { background: #fd7e14; color: #fff; }
        .status-badge-ready { background: #20c997; color: #fff; }
        .status-badge-delivered { background: #28a745; color: #fff; }
        .status-badge-cancelled { background: #dc3545; color: #fff; }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg" style="background: <?= $user_type === 'admin' ? 'var(--admin-primary)' : 'var(--owner-primary)' ?>;">
        <div class="container">
            <span class="navbar-brand text-white">
                <i class="bi bi-cart me-2"></i>Order Details
                <span class="user-badge ms-2"><?= ucfirst($user_type) ?></span>
            </span>
            <div class="navbar-nav ms-auto">
                <?php if ($user_type === 'admin'): ?>
                <!-- Admin Navigation -->
                <a href="index.php" class="nav-link text-white me-3">
                    <i class="bi bi-speedometer2 me-1"></i>Admin Dashboard
                </a>
                <?php else: ?>
                <!-- Owner Navigation -->
                <a href="../owner/reports.php" class="nav-link text-white me-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to Reports
                </a>
                <a href="../owner/index.php" class="nav-link text-white me-3">
                    <i class="bi bi-speedometer2 me-1"></i>Owner Dashboard
                </a>
                <?php endif; ?>
                <a href="../logout.php" class="nav-link text-white">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Toast Container for Messages -->
    <div class="toast-container">
        <div id="statusToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Status updated successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Order<?= $order['order_number'] ?></h2>
            <div class="d-flex align-items-center gap-3">
                <div class="badge status-badge-<?= $order['status'] ?> fs-6" id="currentStatusBadge">
                    <?= ucfirst($order['status']) ?>
                </div>
                <div class="text-muted">
                    <small>Viewed as: <strong><?= ucfirst($user_type) ?></strong></small>
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email'] ?? 'Not provided') ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($order['delivery_address'] ?? 'Not provided') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Order Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        <p><strong>Delivery Date:</strong> <?= date('M j, Y', strtotime($order['delivery_date'] ?? $order['created_at'])) ?></p>
                        <p><strong>Delivery Time:</strong> <?= htmlspecialchars($order['delivery_time'] ?? 'Not specified') ?></p>
                        <p><strong>Total Amount:</strong> <span class="fw-bold text-primary">ETB <?= number_format($order['total_amount'], 2) ?></span></p>
                        <p><strong>Payment Method:</strong> <?= ucfirst($order['payment_method'] ?? 'Not specified') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Items (<?= count($order_items) ?>)</h5>
                <span class="badge bg-primary">Total: ETB <?= number_format($order['total_amount'], 2) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($order_items)): ?>
                    <p class="text-muted text-center py-3">No items found in this order.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                foreach ($order_items as $item): 
                                    $subtotal += $item['total_price'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></div>
                                        <?php if (!empty($item['customization'])): ?>
                                        <small class="text-muted d-block">Custom: <?= htmlspecialchars($item['customization']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($item['size'])): ?>
                                        <small class="text-muted d-block">Size: <?= htmlspecialchars($item['size']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($item['flavor'])): ?>
                                        <small class="text-muted d-block">Flavor: <?= htmlspecialchars($item['flavor']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= ucfirst($item['product_type'] ?? 'product') ?></span>
                                    </td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>ETB <?= number_format($item['unit_price'], 2) ?></td>
                                    <td class="fw-bold">ETB <?= number_format($item['total_price'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                    <td class="fw-bold">ETB <?= number_format($subtotal, 2) ?></td>
                                </tr>
                                <?php if (isset($order['delivery_fee']) && $order['delivery_fee'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Delivery Fee:</td>
                                    <td>ETB <?= number_format($order['delivery_fee'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Tax:</td>
                                    <td>ETB <?= number_format($order['tax_amount'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                    <td class="fw-bold">ETB <?= number_format($order['total_amount'], 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Notes -->
        <?php if (!empty($order['special_instructions'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Special Instructions</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($order['special_instructions'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status Update (Only for Admins) -->
        <?php if ($user_type === 'admin'): ?>
        <?php endif; ?>

        <div class="mt-4">
            <?php if ($user_type === 'admin'): ?>
            <?php else: ?>
            <a href="../owner/reports.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Reports
            </a>
            <?php endif; ?>
            
            <?php if ($user_type === 'admin'): ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status update functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusForm = document.getElementById('statusForm');
            const statusSelect = document.getElementById('statusSelect');
            const updateStatusBtn = document.getElementById('updateStatusBtn');
            const currentStatusBadge = document.getElementById('currentStatusBadge');
            const statusToast = new bootstrap.Toast(document.getElementById('statusToast'));
            const toastMessage = document.getElementById('toastMessage');

            if (statusForm) {
                statusForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const orderId = formData.get('order_id');
                    const newStatus = formData.get('status');
                    
                    // Show loading state
                    updateStatusBtn.disabled = true;
                    updateStatusBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Updating...';
                    
                    fetch('update_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update status badge
                            currentStatusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                            currentStatusBadge.className = `badge status-badge-${newStatus} fs-6`;
                            
                            // Show success message
                            toastMessage.textContent = data.message;
                            statusToast._element.className = 'toast align-items-center text-white bg-success border-0';
                            statusToast.show();
                        } else {
                            // Show error message
                            toastMessage.textContent = data.message;
                            statusToast._element.className = 'toast align-items-center text-white bg-danger border-0';
                            statusToast.show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastMessage.textContent = 'Network error updating status';
                        statusToast._element.className = 'toast align-items-center text-white bg-danger border-0';
                        statusToast.show();
                    })
                    .finally(() => {
                        // Restore button state
                        updateStatusBtn.disabled = false;
                        updateStatusBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Status';
                    });
                });
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>