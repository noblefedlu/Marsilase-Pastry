<?php
// admin/settings.php
require_once 'config.php';
requireAdminAuth();

// Handle settings update
if (isset($_POST['update_settings'])) {
    $site_name = sanitizeInput($_POST['site_name']);
    $site_email = sanitizeInput($_POST['site_email']);
    $site_phone = sanitizeInput($_POST['site_phone']);
    $site_address = sanitizeInput($_POST['site_address']);
    $business_hours = sanitizeInput($_POST['business_hours']);
    $delivery_fee = floatval($_POST['delivery_fee']);
    $min_order_amount = floatval($_POST['min_order_amount']);
    $delivery_radius = intval($_POST['delivery_radius']);
    
    // In a real application, you'd store these in a settings table
    // For now, we'll use session to demonstrate
    $_SESSION['site_settings'] = [
        'site_name' => $site_name,
        'site_email' => $site_email,
        'site_phone' => $site_phone,
        'site_address' => $site_address,
        'business_hours' => $business_hours,
        'delivery_fee' => $delivery_fee,
        'min_order_amount' => $min_order_amount,
        'delivery_radius' => $delivery_radius
    ];
    
    $_SESSION['success_message'] = "Settings updated successfully!";
    logAdminAction('Settings Update', 'Updated website settings');
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $admin_id = $_SESSION['admin_id'];
    $result = $conn->query("SELECT password_hash FROM admin_users WHERE id = $admin_id");
    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($current_password, $row['password_hash'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $conn->query("UPDATE admin_users SET password_hash = '$new_password_hash' WHERE id = $admin_id");
                    
                    $_SESSION['success_message'] = "Password changed successfully!";
                    logAdminAction('Password Change', 'Admin changed their password');
                } else {
                    $_SESSION['error_message'] = "New password must be at least 8 characters long.";
                }
            } else {
                $_SESSION['error_message'] = "New passwords do not match.";
            }
        } else {
            $_SESSION['error_message'] = "Current password is incorrect.";
        }
    }
}

// Get current settings (in real app, fetch from database)
$current_settings = $_SESSION['site_settings'] ?? [
    'site_name' => 'Marsilase Pastry',
    'site_email' => 'info@marsilasepastry.com',
    'site_phone' => '+251-911-223344',
    'site_address' => 'Bole Road, Addis Ababa, Ethiopia',
    'business_hours' => '8:00 AM - 10:00 PM',
    'delivery_fee' => 50.00,
    'min_order_amount' => 200.00,
    'delivery_radius' => 20
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .settings-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            transition: all 0.3s;
        }
        
        .settings-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .nav-pills .nav-link {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            color: #64748b;
            font-weight: 500;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #f56e10, #e7540a);
            color: white;
        }
        
        .nav-pills .nav-link i {
            width: 20px;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Settings</h2>
                <p class="text-muted mb-0">Manage your website and account settings</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <div class="row">
            <!-- Settings Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="card settings-card">
                    <div class="card-body">
                        <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#general" type="button">
                                <i class="bi bi-gear"></i> General Settings
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#delivery" type="button">
                                <i class="bi bi-truck"></i> Delivery Settings
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#account" type="button">
                                <i class="bi bi-person"></i> Account Settings
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#notifications" type="button">
                                <i class="bi bi-bell"></i> Notifications
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#backup" type="button">
                                <i class="bi bi-cloud-arrow-down"></i> Backup & Restore
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="col-lg-9">
                <div class="tab-content" id="settingsContent">
                    <!-- General Settings Tab -->
                    <div class="tab-pane fade show active" id="general">
                        <div class="card settings-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>General Settings</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Site Name</label>
                                            <input type="text" name="site_name" class="form-control" 
                                                   value="<?= htmlspecialchars($current_settings['site_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Email</label>
                                            <input type="email" name="site_email" class="form-control" 
                                                   value="<?= htmlspecialchars($current_settings['site_email']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Phone</label>
                                            <input type="text" name="site_phone" class="form-control" 
                                                   value="<?= htmlspecialchars($current_settings['site_phone']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Hours</label>
                                            <input type="text" name="business_hours" class="form-control" 
                                                   value="<?= htmlspecialchars($current_settings['business_hours']) ?>" 
                                                   placeholder="e.g., 8:00 AM - 10:00 PM">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Business Address</label>
                                            <textarea name="site_address" class="form-control" rows="3"><?= htmlspecialchars($current_settings['site_address']) ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="update_settings" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-2"></i>Save General Settings
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Settings Tab -->
                    <div class="tab-pane fade" id="delivery">
                        <div class="card settings-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Delivery Settings</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Delivery Fee (ETB)</label>
                                            <input type="number" name="delivery_fee" class="form-control" 
                                                   value="<?= $current_settings['delivery_fee'] ?>" step="0.01" min="0">
                                            <div class="form-text">Standard delivery charge for orders</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Minimum Order Amount (ETB)</label>
                                            <input type="number" name="min_order_amount" class="form-control" 
                                                   value="<?= $current_settings['min_order_amount'] ?>" step="0.01" min="0">
                                            <div class="form-text">Minimum amount for order placement</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Delivery Radius (km)</label>
                                            <input type="number" name="delivery_radius" class="form-control" 
                                                   value="<?= $current_settings['delivery_radius'] ?>" min="1">
                                            <div class="form-text">Maximum delivery distance from your location</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Free Delivery Threshold (ETB)</label>
                                            <input type="number" name="free_delivery_threshold" class="form-control" 
                                                   value="500" step="0.01" min="0" readonly>
                                            <div class="form-text">Orders above this amount get free delivery</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="enableDelivery" checked>
                                                <label class="form-check-label fw-semibold" for="enableDelivery">Enable Delivery Service</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="update_settings" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-2"></i>Save Delivery Settings
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings Tab -->
                    <div class="tab-pane fade" id="account">
                        <div class="card settings-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Account Settings</h5>
                            </div>
                            <div class="card-body">
                                <!-- Profile Information -->
                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-3">Profile Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" value="<?= $_SESSION['admin_name'] ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" value="<?= $_SESSION['admin_username'] ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Role</label>
                                            <input type="text" class="form-control" value="<?= ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])) ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Last Login</label>
                                            <input type="text" class="form-control" value="<?= date('M j, Y g:i A') ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Change Password -->
                                <hr>
                                <h6 class="fw-semibold mb-3">Change Password</h6>
                                <form method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Current Password</label>
                                            <input type="password" name="current_password" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">New Password</label>
                                            <input type="password" name="new_password" class="form-control" required>
                                            <div class="form-text">Minimum 8 characters</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="change_password" class="btn btn-primary">
                                                <i class="bi bi-key me-2"></i>Change Password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Tab -->
                    <div class="tab-pane fade" id="notifications">
                        <div class="card settings-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-bell me-2"></i>Notification Settings</h5>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold mb-3">Email Notifications</h6>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="newOrderEmail" checked>
                                            <label class="form-check-label fw-semibold" for="newOrderEmail">New Order Notifications</label>
                                            <div class="form-text">Receive email when a new order is placed</div>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="contactEmail" checked>
                                            <label class="form-check-label fw-semibold" for="contactEmail">Contact Form Submissions</label>
                                            <div class="form-text">Receive email when someone submits contact form</div>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="lowStockEmail">
                                            <label class="form-check-label fw-semibold" for="lowStockEmail">Low Stock Alerts</label>
                                            <div class="form-text">Receive email when product stock is low</div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h6 class="fw-semibold mb-3">SMS Notifications</h6>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="newOrderSMS">
                                            <label class="form-check-label fw-semibold" for="newOrderSMS">New Order SMS Alerts</label>
                                            <div class="form-text">Receive SMS when a new order is placed</div>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="urgentSMS">
                                            <label class="form-check-label fw-semibold" for="urgentSMS">Urgent Order Alerts</label>
                                            <div class="form-text">Receive SMS for urgent/same-day orders</div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>Save Notification Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Tab -->
                    <div class="tab-pane fade" id="backup">
                        <div class="card settings-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-cloud-arrow-down me-2"></i>Backup & Restore</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center">
                                                <i class="bi bi-cloud-arrow-down display-4 text-primary mb-3"></i>
                                                <h5>Backup Database</h5>
                                                <p class="text-muted mb-3">Create a backup of your current database</p>
                                                <button type="button" class="btn btn-primary">
                                                    <i class="bi bi-download me-2"></i>Create Backup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body text-center">
                                                <i class="bi bi-cloud-arrow-up display-4 text-success mb-3"></i>
                                                <h5>Restore Database</h5>
                                                <p class="text-muted mb-3">Restore from a previous backup</p>
                                                <input type="file" class="form-control mb-3" accept=".sql,.zip">
                                                <button type="button" class="btn btn-success">
                                                    <i class="bi bi-upload me-2"></i>Restore Backup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h6 class="fw-semibold mb-3">Recent Backups</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Backup Date</th>
                                                    <th>File Size</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>No backups available</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Password strength indicator
        document.querySelector('input[name="new_password"]').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('passwordStrength');
            
            if (!strengthIndicator) {
                const strengthDiv = document.createElement('div');
                strengthDiv.id = 'passwordStrength';
                strengthDiv.className = 'mt-2';
                this.parentNode.appendChild(strengthDiv);
            }
            
            let strength = 0;
            let feedback = '';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    feedback = '<span class="text-danger">Weak password</span>';
                    break;
                case 2:
                    feedback = '<span class="text-warning">Moderate password</span>';
                    break;
                case 3:
                    feedback = '<span class="text-info">Good password</span>';
                    break;
                case 4:
                    feedback = '<span class="text-success">Strong password</span>';
                    break;
            }
            
            document.getElementById('passwordStrength').innerHTML = feedback;
        });
    </script>
</body>
</html>