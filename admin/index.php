
<?php
session_start();
include '../common/connection.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['admin_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Admin can only view orders, not manage products
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? '';

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

// Get counts for stats
$status_counts = [];
$result = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
    }
}

$pending_orders = $status_counts['pending'] ?? 0;
$confirmed_orders = $status_counts['confirmed'] ?? 0;
$cancelled_orders = $status_counts['cancelled'] ?? 0;
$today_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];

// Calculate revenue stats
$today_revenue = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetch_assoc()['revenue'] ?? 0;
$total_revenue = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'cancelled'")->fetch_assoc()['revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --background: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
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
        .admin-container {
            display: flex;
            min-height: 100vh;
            max-width: 100vw;
        }

        /* Sidebar - Made more compact */
        .sidebar {
            width: 220px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-primary);
        }

        .brand-icon {
            width: 28px;
            height: 28px;
            background: var(--primary);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
        }

        .nav-section {
            padding: 0.25rem 0;
        }

        .nav-title {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            padding: 0 1.25rem 0.5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
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
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Main Content - Adjusted for compact layout */
        .main-content {
            flex: 1;
            margin-left: 220px;
            padding: 0;
            max-width: calc(100vw - 220px);
            overflow-x: hidden;
            transition: margin-left 0.3s ease, max-width 0.3s ease;
        }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 1.5rem;
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
            font-size: 1.3rem;
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
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Burger Menu */
        .burger-menu {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-primary);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: var(--radius);
            transition: background-color 0.2s;
        }

        .burger-menu:hover {
            background: var(--primary-light);
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Content Area - Made more compact */
        .content-area {
            padding: 1.5rem;
            max-width: 100%;
        }

        /* Stats Grid - More compact cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .stat-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.875rem;
            font-size: 1.2rem;
        }

        .stat-icon.primary { background: var(--primary-light); color: var(--primary); }
        .stat-icon.success { background: #ecfdf5; color: var(--success); }
        .stat-icon.warning { background: #fffbeb; color: var(--warning); }
        .stat-icon.error { background: #fef2f2; color: var(--error); }

        .stat-title {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin: 0;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0.2rem 0;
            line-height: 1.2;
        }

        .stat-change {
            font-size: 0.75rem;
            color: var(--success);
            font-weight: 500;
        }

        /* Quick Stats Section - More compact */
        .quick-stats-section {
            margin-bottom: 1.5rem;
        }

        .quick-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .quick-stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .quick-stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .quick-stat-title {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin: 0;
        }

        .quick-stat-value {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0.3rem 0;
        }

        .quick-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .quick-stat-icon.primary { background: var(--primary-light); color: var(--primary); }
        .quick-stat-icon.success { background: #ecfdf5; color: var(--success); }

        /* Orders Table - More compact */
        .table-card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
            max-width: 100%;
        }

        .table-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .table {
            margin: 0;
            font-size: 0.85rem;
        }

        .table th {
            background: var(--background);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 1rem;
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            white-space: nowrap;
        }

        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 16px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-pending { background: #fffbeb; color: var(--warning); }
        .status-confirmed { background: #ecfdf5; color: var(--success); }
        .status-cancelled { background: #fef2f2; color: var(--error); }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-icon {
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
            font-size: 0.8rem;
        }

        .form-select {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
            height: 28px;
            width: 120px;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .sidebar {
                width: 200px;
            }
            
            .main-content {
                margin-left: 200px;
                max-width: calc(100vw - 200px);
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                max-width: 100vw;
            }
            
            .burger-menu {
                display: block;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 1rem;
            }
            
            .table {
                font-size: 0.8rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .form-select {
                width: 100px;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                padding: 0.75rem 1rem;
            }
            
            .page-title h1 {
                font-size: 1.1rem;
            }
            
            .user-info {
                flex-direction: column;
                gap: 0.25rem;
                text-align: right;
            }
            
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="brand">
                    <div class="brand-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    Marsilase
                </div>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Main</div>
                <a href="#" class="nav-item active">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
                <!-- <a href="messages.php" class="nav-item">
                    <i class="bi bi-chat-dots"></i>
                    Messages
                </a> -->
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Settings</div>
                <!-- <a href="change_password.php" class="nav-item">
                    <i class="bi bi-key"></i>
                    Password
                </a> -->
                <a href="logout.php" class="nav-item">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-content">
                    <div class="d-flex align-items-center">
                        <button class="burger-menu me-3" id="burgerMenu">
                            <i class="bi bi-list"></i>
                        </button>
                        <div class="page-title">
                            <h1>Dashboard</h1>
                        </div>
                    </div>
                    <div class="user-menu">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?= strtoupper(substr($_SESSION['admin_full_name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight: 500; font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['admin_full_name'] ?? 'Admin') ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">Administrator</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon primary">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="stat-title">Total Orders</div>
                                <div class="stat-value"><?= $total_orders ?></div>
                                <div class="stat-change">
                                    <i class="bi bi-arrow-up"></i>
                                    All time orders
                                </div>
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
                                <div class="stat-change">
                                    <i class="bi bi-arrow-up"></i>
                                    Total revenue
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon warning">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="stat-title">Pending Orders</div>
                                <div class="stat-value"><?= $pending_orders ?></div>
                                <div class="stat-change">
                                    Needs attention
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon error">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="stat-title">Cancelled Orders</div>
                                <div class="stat-value"><?= $cancelled_orders ?></div>
                                <div class="stat-change">
                                    Cancelled orders
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Section -->
                <div class="quick-stats-section">
                    <div class="quick-stats-grid">
                        <div class="quick-stat-card">
                            <div class="quick-stat-header">
                                <div>
                                    <div class="quick-stat-title">Today's Orders</div>
                                    <div class="quick-stat-value"><?= $today_orders ?></div>
                                </div>
                                <div class="quick-stat-icon primary">
                                    <i class="bi bi-cart-plus"></i>
                                </div>
                            </div>
                        </div>

                        <div class="quick-stat-card">
                            <div class="quick-stat-header">
                                <div>
                                    <div class="quick-stat-title">Today's Revenue</div>
                                    <div class="quick-stat-value">ETB <?= number_format($today_revenue, 2) ?></div>
                                </div>
                                <div class="quick-stat-icon success">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                        </div>

                        <div class="quick-stat-card">
                            <div class="quick-stat-header">
                                <div>
                                    <div class="quick-stat-title">Confirmed Orders</div>
                                    <div class="quick-stat-value"><?= $confirmed_orders ?></div>
                                </div>
                                <div class="quick-stat-icon success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Recent Orders</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-tertiary);">
                                            <i class="bi bi-inbox" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                                            No orders found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($orders, 0, 8) as $order): ?>
                                    <tr>
                                        <td style="font-weight: 600;">#<?= $order['order_number'] ?></td>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td><?= $order['customer_phone'] ?></td>
                                        <td style="font-weight: 600;">ETB <?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-icon" title="View Order">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <select class="form-select" onchange="updateStatus(<?= $order['id'] ?>, this.value)">
                                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                            </div>
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
        // Mobile sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const burgerMenu = document.getElementById('burgerMenu');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content');

            function toggleSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }

            burgerMenu.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);

            // Close sidebar when clicking on a nav item on mobile
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        toggleSidebar();
                    }
                });
            });

            // Close sidebar on window resize if it becomes desktop view
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Status update functionality
        function updateStatus(orderId, status) {
            if (confirm(`Change order status to "${status}"?`)) {
                const selectElement = event.target;
                const originalValue = selectElement.value;
                selectElement.disabled = true;
                
                fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_id=${orderId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the status badge
                        const row = selectElement.closest('tr');
                        const statusBadge = row.querySelector('.status-badge');
                        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        statusBadge.className = `status-badge status-${status}`;
                        
                        // Show success message
                        showToast('Status updated successfully', 'success');
                    } else {
                        showToast(data.message, 'error');
                        selectElement.value = originalValue;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Network error updating status', 'error');
                    selectElement.value = originalValue;
                })
                .finally(() => {
                    selectElement.disabled = false;
                });
            } else {
                event.target.value = '<?= $order['status'] ?? 'pending' ?>';
            }
        }

        function showToast(message, type) {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '1060';
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast after hide
            toast.addEventListener('hidden.bs.toast', () => {
                document.body.removeChild(toast);
            });
        }

        // Auto-refresh every 2 minutes
        setTimeout(() => {
            window.location.reload();
        }, 120000);
    </script>
</body>
</html>
<?php $conn->close(); ?>