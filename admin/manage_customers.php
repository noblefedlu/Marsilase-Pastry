<?php
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

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../?page=admin-login');
    exit;
}

$message = '';
$error = '';

// Get all unique customers from orders
$customers = $conn->query("
    SELECT 
        customer_phone,
        customer_name,
        customer_email,
        COUNT(*) as order_count,
        SUM(total_amount) as total_spent,
        MAX(created_at) as last_order_date
    FROM orders 
    GROUP BY customer_phone, customer_name, customer_email
    ORDER BY last_order_date DESC
")->fetch_all(MYSQLI_ASSOC);

// Get customer details if specific customer is selected
$customer_details = null;
$customer_orders = [];
if (isset($_GET['phone'])) {
    $phone = $_GET['phone'];
    $customer_details = $conn->query("
        SELECT DISTINCT customer_phone, customer_name, customer_email 
        FROM orders 
        WHERE customer_phone = '$phone'
    ")->fetch_assoc();
    
    $customer_orders = $conn->query("
        SELECT * FROM orders 
        WHERE customer_phone = '$phone' 
        ORDER BY created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Customer Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['phone']) && $customer_details): ?>
                    <!-- Customer Details View -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <h6><?= htmlspecialchars($customer_details['customer_name']) ?></h6>
                                    <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($customer_details['customer_phone']) ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($customer_details['customer_email']) ?></p>
                                    <p class="mb-1"><strong>Total Orders:</strong> <?= count($customer_orders) ?></p>
                                    <p class="mb-0"><strong>Total Spent:</strong> ETB <?= number_format(array_sum(array_column($customer_orders, 'total_amount')), 2) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Order History</h5>
                                    <a href="?page=admin-customers" class="btn btn-sm btn-outline-secondary">Back to List</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($customer_orders)): ?>
                                        <p class="text-muted">No orders found.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Order #</th>
                                                        <th>Date</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($customer_orders as $order): ?>
                                                    <tr>
                                                        <td><?= $order['order_number'] ?></td>
                                                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                                        <td>ETB <?= number_format($order['total_amount'], 2) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= 
                                                                $order['status'] === 'delivered' ? 'success' : 
                                                                ($order['status'] === 'pending' ? 'warning' : 'secondary')
                                                            ?>">
                                                                <?= ucfirst($order['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="../?page=admin-orders&id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Customers List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">All Customers (<?= count($customers) ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($customers)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-people display-1 text-muted"></i>
                                    <p class="text-muted mt-3">No customers found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Orders</th>
                                                <th>Total Spent</th>
                                                <th>Last Order</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($customer['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($customer['customer_phone']) ?></td>
                                                <td><?= htmlspecialchars($customer['customer_email']) ?></td>
                                                <td><?= $customer['order_count'] ?></td>
                                                <td>ETB <?= number_format($customer['total_spent'], 2) ?></td>
                                                <td><?= date('M j, Y', strtotime($customer['last_order_date'])) ?></td>
                                                <td>
                                                    <a href="?phone=<?= urlencode($customer['customer_phone']) ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>