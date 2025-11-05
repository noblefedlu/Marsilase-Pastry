<!-- admin/sidebar.php -->
<div class="sidebar" id="sidebar">
    <div class="p-4">
        <!-- Brand -->
        <div class="text-center mb-5">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                 style="width: 60px; height: 60px;">
                <i class="bi bi-cake2 text-white fs-4"></i>
            </div>
            <h5 class="text-white mb-1">Marsilase Pastry</h5>
            <small class="text-muted">Admin Panel</small>
        </div>

        <!-- Navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-muted px-3">SALES</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                    <i class="bi bi-cart"></i>Orders
                    <?php if ($pending_orders > 0): ?>
                        <span class="badge bg-warning float-end"><?= $pending_orders ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>" href="customers.php">
                    <i class="bi bi-people"></i>Customers
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-muted px-3">PRODUCTS</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" href="products.php">
                    <i class="bi bi-cake"></i>Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>" href="categories.php">
                    <i class="bi bi-tags"></i>Categories
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-muted px-3">CONTENT</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>" href="messages.php">
                    <i class="bi bi-chat-left-text"></i>Messages
                    <?php if (isset($unread_messages) && $unread_messages > 0): ?>
                        <span class="badge bg-danger float-end"><?= $unread_messages ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>" href="reviews.php">
                    <i class="bi bi-star"></i>Reviews
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-muted px-3">ANALYTICS</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="bi bi-graph-up"></i>Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : '' ?>" href="analytics.php">
                    <i class="bi bi-bar-chart"></i>Analytics
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-muted px-3">SYSTEM</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                    <i class="bi bi-gear"></i>Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-users.php' ? 'active' : '' ?>" href="admin-users.php">
                    <i class="bi bi-shield-check"></i>Admin Users
                </a>
            </li>
        </ul>

        <!-- Logout -->
        <div class="mt-5 pt-4 border-top border-secondary">
            <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>Logout
            </a>
        </div>
    </div>
</div>

<!-- Mobile Toggle Button -->
<button class="btn btn-primary d-md-none position-fixed" id="sidebarToggle" 
        style="top: 1rem; left: 1rem; z-index: 1050;">
    <i class="bi bi-list"></i>
</button>