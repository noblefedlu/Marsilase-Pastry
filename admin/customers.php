<?php
// admin/customers.php
require_once 'config.php';
requireAdminAuth();

// Get search and filter parameters
$search_term = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'recent';

// Build query to get customers with their order counts and total spending
$query = "
    SELECT 
        o.customer_name,
        o.customer_email,
        o.customer_phone,
        COUNT(o.id) as order_count,
        SUM(o.total_amount) as total_spent,
        MAX(o.created_at) as last_order_date
    FROM orders o
    WHERE 1=1
";

$params = [];
$types = '';

if (!empty($search_term)) {
    $query .= " AND (o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ?)";
    $search_like = "%$search_term%";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= 'sss';
}

$query .= " GROUP BY o.customer_email, o.customer_name, o.customer_phone";

// Add sorting
switch ($sort_by) {
    case 'name':
        $query .= " ORDER BY o.customer_name ASC";
        break;
    case 'orders':
        $query .= " ORDER BY order_count DESC";
        break;
    case 'spending':
        $query .= " ORDER BY total_spent DESC";
        break;
    case 'recent':
    default:
        $query .= " ORDER BY last_order_date DESC";
        break;
}

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get customer statistics
$total_customers = count($customers);
$repeat_customers = array_filter($customers, function($customer) {
    return $customer['order_count'] > 1;
});
$repeat_customer_count = count($repeat_customers);
$repeat_rate = $total_customers > 0 ? ($repeat_customer_count / $total_customers) * 100 : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .customer-card {
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }
        
        .customer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #f56e10, #e7540a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Customer Management</h2>
                <p class="text-muted mb-0">Manage and view customer information</p>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Customer Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-people text-primary fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $total_customers ?></h4>
                            <span class="text-muted">Total Customers</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-repeat text-success fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= $repeat_customer_count ?></h4>
                            <span class="text-muted">Repeat Customers</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-arrow-repeat text-info fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold"><?= number_format($repeat_rate, 1) ?>%</h4>
                            <span class="text-muted">Repeat Rate</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                            <i class="bi bi-star text-warning fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <?= $total_customers > 0 ? number_format(array_sum(array_column($customers, 'total_spent')) / $total_customers, 2) : '0' ?>
                            </h4>
                            <span class="text-muted">Avg. Spend (ETB)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search customers by name, email, or phone..." 
                                       value="<?= htmlspecialchars($search_term) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if (!empty($search_term)): ?>
                            <a href="customers.php" class="btn btn-outline-secondary">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <select name="sort" class="form-select" onchange="window.location.href = 'customers.php?sort=' + this.value">
                            <option value="recent" <?= $sort_by === 'recent' ? 'selected' : '' ?>>Sort by Recent</option>
                            <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Sort by Name</option>
                            <option value="orders" <?= $sort_by === 'orders' ? 'selected' : '' ?>>Sort by Orders</option>
                            <option value="spending" <?= $sort_by === 'spending' ? 'selected' : '' ?>>Sort by Spending</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers List -->
        <div class="row">
            <?php foreach ($customers as $customer): ?>
            <div class="col-xl-6 mb-4">
                <div class="card customer-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar me-3">
                                <?= strtoupper(substr($customer['customer_name'], 0, 1)) ?>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($customer['customer_name']) ?></h5>
                                <p class="text-muted mb-1">
                                    <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($customer['customer_email']) ?>
                                </p>
                                <?php if ($customer['customer_phone']): ?>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($customer['customer_phone']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="fw-bold text-primary fs-5"><?= $customer['order_count'] ?></div>
                                    <small class="text-muted">Orders</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="fw-bold text-success fs-5">ETB <?= number_format($customer['total_spent'], 2) ?></div>
                                    <small class="text-muted">Total Spent</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="fw-bold text-info fs-5">
                                        <?= $customer['order_count'] > 0 ? 'ETB ' . number_format($customer['total_spent'] / $customer['order_count'], 2) : '0' ?>
                                    </div>
                                    <small class="text-muted">Avg. Order</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Last order: 
                                <?= $customer['last_order_date'] ? date('M j, Y', strtotime($customer['last_order_date'])) : 'Never' ?>
                            </small>
                            <div class="btn-group">
                                <a href="orders.php?search=<?= urlencode($customer['customer_email']) ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-cart"></i> View Orders
                                </a>
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="viewCustomerDetails(<?= htmlspecialchars(json_encode($customer)) ?>)">
                                    <i class="bi bi-eye"></i> Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($customers)): ?>
        <div class="text-center py-5">
            <i class="bi bi-people display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">No customers found</h4>
            <p class="text-muted">
                <?= empty($search_term) ? 'Customers will appear here once they place orders.' : 'No customers match your search criteria.' ?>
            </p>
            <?php if (!empty($search_term)): ?>
            <a href="customers.php" class="btn btn-primary">Clear Search</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer Details Modal -->
    <div class="modal fade" id="customerDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="customerDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewCustomerDetails(customer) {
            const modalContent = document.getElementById('customerDetailsContent');
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Contact Information</h6>
                        <p><strong>Name:</strong> ${customer.customer_name}</p>
                        <p><strong>Email:</strong> ${customer.customer_email}</p>
                        <p><strong>Phone:</strong> ${customer.customer_phone || 'Not provided'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Order Statistics</h6>
                        <p><strong>Total Orders:</strong> ${customer.order_count}</p>
                        <p><strong>Total Spent:</strong> ETB ${parseFloat(customer.total_spent).toFixed(2)}</p>
                        <p><strong>Average Order:</strong> ETB ${(customer.total_spent / customer.order_count).toFixed(2)}</p>
                        <p><strong>Last Order:</strong> ${customer.last_order_date ? new Date(customer.last_order_date).toLocaleDateString() : 'Never'}</p>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <a href="orders.php?search=${encodeURIComponent(customer.customer_email)}" class="btn btn-primary">
                        <i class="bi bi-cart me-2"></i>View All Orders
                    </a>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
            modal.show();
        }
    </script>
</body>
</html>