
<?php
session_start();
require_once '../common/connection.php';
requireOwner();
requirePermission('view_reports');

$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? '';

// Build query
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE 1=1";

$params = [];
$types = '';

if ($status_filter !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($date_filter)) {
    $query .= " AND DATE(o.created_at) = ?";
    $params[] = $date_filter;
    $types .= 's';
}

$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

global $conn;
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get status counts
$status_counts = [];
$result = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
    }
}

// Get today's orders
$today_orders_result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
$today_orders = $today_orders_result ? $today_orders_result->fetch_assoc()['count'] : 0;

// Total revenue
$total_revenue_result = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0) as revenue 
    FROM orders 
    WHERE status IN ('delivered', 'completed', 'paid', 'confirmed')
    OR (status NOT IN ('pending', 'cancelled', 'refunded') AND total_amount > 0)
");
$total_revenue = $total_revenue_result ? $total_revenue_result->fetch_assoc()['revenue'] : 0;

if ($total_revenue == 0) {
    $total_revenue_result2 = $conn->query("
        SELECT COALESCE(SUM(total_amount), 0) as revenue 
        FROM orders 
        WHERE status NOT IN ('pending', 'cancelled')
    ");
    $total_revenue = $total_revenue_result2 ? $total_revenue_result2->fetch_assoc()['revenue'] : 0;
}

if ($total_revenue == 0) {
    $total_revenue_result3 = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders");
    $total_revenue = $total_revenue_result3 ? $total_revenue_result3->fetch_assoc()['revenue'] : 0;
}

// Get owner stats
$owner_stats_result = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM owners WHERE is_active = 1) as active_owners,
        (SELECT COUNT(*) FROM admins WHERE is_active = 1) as active_admins,
        (SELECT COUNT(*) FROM products WHERE is_active = 1) as active_products,
        (SELECT COUNT(*) FROM cakes WHERE is_active = 1) as active_cakes
");
$owner_stats = $owner_stats_result ? $owner_stats_result->fetch_assoc() : [
    'active_owners' => 0, 
    'active_admins' => 0, 
    'active_products' => 0, 
    'active_cakes' => 0
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard | Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a365d;
            --primary-dark: #2d3748;
            --primary-light: #e2e8f0;
            --secondary: #718096;
            --success: #38a169;
            --warning: #d69e2e;
            --error: #e53e3e;
            --accent: #d53f8c;
            --background: #f7fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --text-tertiary: #a0aec0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --radius: 8px;
            --radius-lg: 12px;
        }

        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Layout */
        .owner-container {
            display: flex;
            min-height: 100vh;
            max-width: 100vw;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--primary);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .nav-section {
            padding: 1.5rem 0;
        }

        .nav-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            padding: 0 1.5rem 0.75rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--primary-light);
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: var(--shadow-md);
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 0;
            max-width: calc(100vw - 240px);
            overflow-x: hidden;
            transition: margin-left 0.3s ease, max-width 0.3s ease;
        }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-primary);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .security-badge {
            background: var(--accent);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
            max-width: 100%;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.3rem;
        }

        .stat-icon.primary { background: var(--primary-light); color: var(--primary); }
        .stat-icon.success { background: #f0fff4; color: var(--success); }
        .stat-icon.warning { background: #fffbeb; color: var(--warning); }
        .stat-icon.error { background: #fed7d7; color: var(--error); }
        .stat-icon.accent { background: #fed7e2; color: var(--accent); }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0.25rem 0;
            line-height: 1.2;
        }

        .stat-description {
            font-size: 0.8rem;
            color: var(--text-tertiary);
            margin: 0;
        }

        /* System Overview Grid */
        .system-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .system-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            text-align: center;
        }

        .system-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .system-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0.5rem 0;
        }

        .system-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        /* Orders Table */
        .table-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
            max-width: 100%;
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }

        .table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .table {
            margin: 0;
            font-size: 0.875rem;
        }

        .table th {
            background: var(--background);
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            white-space: nowrap;
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending { background: #fffbeb; color: var(--warning); }
        .status-confirmed { background: #f0fff4; color: var(--success); }
        .status-delivered { background: #ebf8ff; color: var(--primary); }
        .status-cancelled { background: #fed7d7; color: var(--error); }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
            font-size: 0.875rem;
        }

        /* Debug Info */
        .debug-info {
            background: #fffaf0;
            border: 1px solid #fed7aa;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 220px;
            }
            
            .main-content {
                margin-left: 220px;
                max-width: calc(100vw - 220px);
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                max-width: 100vw;
            }
            
            .main-content.menu-open {
                margin-left: 240px;
                max-width: calc(100vw - 240px);
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .mobile-overlay.active {
                display: block;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .system-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 1rem;
            }
            
            .system-grid {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.8rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
            
            .sidebar {
                width: 280px;
            }
            
            .main-content.menu-open {
                margin-left: 280px;
                max-width: calc(100vw - 280px);
            }
        }

        @media (max-width: 576px) {
            .topbar {
                padding: 0.75rem 1rem;
            }
            
            .page-title h1 {
                font-size: 1.25rem;
            }
            
            .user-info {
                flex-direction: column;
                gap: 0.25rem;
                text-align: right;
            }
            
            .mobile-menu-toggle {
                top: 0.5rem;
                left: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="owner-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="brand">
                    <div class="brand-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    Owner Panel
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Dashboard</div>
                <a href="#" class="nav-item active">
                    <i class="bi bi-speedometer2"></i>
                    Overview
                </a>
                <?php if (checkPermission('view_reports')): ?>
                <a href="reports.php" class="nav-item">
                    <i class="bi bi-graph-up"></i>
                    Reports
                </a>
                <?php endif; ?>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Management</div>
                <?php if (checkPermission('manage_products')): ?>
                <a href="products.php" class="nav-item">
                    <i class="bi bi-box-seam"></i>
                    Products
                </a>
                <?php endif; ?>
                <?php if (checkPermission('manage_admins')): ?>
                <a href="manage_admins.php" class="nav-item">
                    <i class="bi bi-people"></i>
                    Manage Admins
                </a>
                <?php endif; ?>
                <?php if ($_SESSION['owner_security_level'] === 'full'): ?>
                <a href="owner_management.php" class="nav-item">
                    <i class="bi bi-person-gear"></i>
                    Owner Settings
                </a>
                <?php endif; ?>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Navigation</div>
                <a href="logout.php" class="nav-item">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-content">
                    <div class="page-title">
                        <h1>Dashboard</h1>
                    </div>
                    <div class="user-menu">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['owner_full_name'] ?? 'O', 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight: 500;"><?= htmlspecialchars($_SESSION['owner_full_name'] ?? 'Owner') ?></div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                                    Owner <span class="security-badge"><?= $_SESSION['owner_security_level'] ?? 'full' ?> access</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Main Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon primary">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="stat-title">Total Orders</div>
                                <div class="stat-value"><?= array_sum($status_counts) ?></div>
                                <div class="stat-description">All time orders</div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon success">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="stat-title">Total Revenue</div>
                                <div class="stat-value">ETB <?= number_format($total_revenue, 2) ?></div>
                                <div class="stat-description">Completed orders revenue</div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon warning">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="stat-title">Today's Orders</div>
                                <div class="stat-value"><?= $today_orders ?></div>
                                <div class="stat-description">New orders today</div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon accent">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="stat-title">Active Products</div>
                                <div class="stat-value"><?= $owner_stats['active_products'] + $owner_stats['active_cakes'] ?></div>
                                <div class="stat-description">Products & Cakes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Overview -->
                <div class="system-grid">
                    <div class="system-card">
                        <div class="system-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="system-value"><?= $owner_stats['active_owners'] ?></div>
                        <div class="system-label">Active Owners</div>
                    </div>

                    <div class="system-card">
                        <div class="system-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="system-value"><?= $owner_stats['active_admins'] ?></div>
                        <div class="system-label">Active Admins</div>
                    </div>

                    <div class="system-card">
                        <div class="system-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="system-value"><?= $status_counts['pending'] ?? 0 ?></div>
                        <div class="system-label">Pending Orders</div>
                    </div>

                    <div class="system-card">
                        <div class="system-icon">
                            <i class="bi bi-cookie"></i>
                        </div>
                        <div class="system-value"><?= $owner_stats['active_products'] ?></div>
                        <div class="system-label">Regular Products</div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Recent Orders</h3>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <span class="badge bg-primary"><?= count($orders) ?> orders total</span>
                            <span class="badge bg-success">ETB <?= number_format($total_revenue, 2) ?> revenue</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-tertiary);">
                                            <i class="bi bi-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                            No orders found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($orders, 0, 10) as $order): ?>
                                    <tr>
                                        <td style="font-weight: 600;">#<?= $order['order_number'] ?></td>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $order['item_count'] ?> items</span>
                                        </td>
                                        <td style="font-weight: 600; color: var(--primary);">ETB <?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <?php if (checkPermission('manage_orders')): ?>
                                            <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-icon" title="View Order" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.8rem;">No access</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu functionality
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        function toggleMobileMenu() {
            sidebar.classList.toggle('mobile-open');
            mainContent.classList.toggle('menu-open');
            mobileOverlay.classList.toggle('active');
            
            // Update hamburger icon
            const icon = mobileMenuToggle.querySelector('i');
            if (sidebar.classList.contains('mobile-open')) {
                icon.className = 'bi bi-x';
            } else {
                icon.className = 'bi bi-list';
            }
        }

        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
        mobileOverlay.addEventListener('click', toggleMobileMenu);

        // Close menu when clicking on nav items (mobile)
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    toggleMobileMenu();
                }
            });
        });

        // Update current time every minute
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            const timeElement = document.querySelector('.current-time');
            if (timeElement) {
                timeElement.innerHTML = '<i class="bi bi-clock me-1"></i>' + timeString;
            }
        }
        
        setInterval(updateCurrentTime, 60000);
        
        // Auto-refresh orders every 2 minutes
        setTimeout(() => {
            window.location.reload();
        }, 120000);
    </script>
</body>
</html>
<?php $conn->close(); ?>