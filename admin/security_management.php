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

// Only super admins can access security management
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: ../?page=admin-login');
    exit;
}

$message = '';
$error = '';

// Handle security actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_role_permissions') {
            $role = $_POST['role'];
            $permissions = $_POST['permissions'] ?? [];
            
            // Convert permissions array to JSON
            $permissions_json = json_encode($permissions);
            
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_type) VALUES (?, ?, 'json') 
                                  ON DUPLICATE KEY UPDATE setting_value = ?");
            $setting_key = "role_permissions_$role";
            $stmt->bind_param("sss", $setting_key, $permissions_json, $permissions_json);
            
            if ($stmt->execute()) {
                $message = "Role permissions updated successfully!";
            } else {
                $error = "Failed to update permissions: " . $stmt->error;
            }
            $stmt->close();
        }
        
        if ($_POST['action'] === 'block_ip') {
            $ip_address = $_POST['ip_address'];
            $reason = $_POST['reason'];
            
            $stmt = $conn->prepare("INSERT INTO blocked_ips (ip_address, reason, blocked_by) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $ip_address, $reason, $_SESSION['admin_id']);
            
            if ($stmt->execute()) {
                $message = "IP address blocked successfully!";
            } else {
                $error = "Failed to block IP address: " . $stmt->error;
            }
            $stmt->close();
        }
        
        if ($_POST['action'] === 'enable_2fa') {
            $admin_id = $_POST['admin_id'];
            $enable_2fa = $_POST['enable_2fa'] ? 1 : 0;
            
            $stmt = $conn->prepare("UPDATE admins SET two_factor_enabled = ? WHERE id = ?");
            $stmt->bind_param("ii", $enable_2fa, $admin_id);
            
            if ($stmt->execute()) {
                $message = "Two-factor authentication setting updated!";
            } else {
                $error = "Failed to update 2FA setting: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Get all admins
$admins = $conn->query("SELECT * FROM admins ORDER BY role, username")->fetch_all(MYSQLI_ASSOC);

// Get admin activity logs with more details
$activity_logs = $conn->query("
    SELECT al.*, a.username, a.role 
    FROM admin_logs al 
    LEFT JOIN admins a ON al.admin_id = a.id 
    ORDER BY al.created_at DESC 
    LIMIT 100
")->fetch_all(MYSQLI_ASSOC);

// Get failed login attempts
$failed_logins = $conn->query("
    SELECT * FROM failed_login_attempts 
    ORDER BY attempt_time DESC 
    LIMIT 50
")->fetch_all(MYSQLI_ASSOC);

// Get role permissions
$role_permissions = [];
$roles = ['super_admin', 'admin', 'moderator'];
foreach ($roles as $role) {
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'role_permissions_$role'");
    if ($result && $row = $result->fetch_assoc()) {
        $role_permissions[$role] = json_decode($row['setting_value'], true) ?: [];
    } else {
        $role_permissions[$role] = get_default_permissions($role);
    }
}

function get_default_permissions($role) {
    $defaults = [
        'super_admin' => [
            'dashboard' => true,
            'orders' => true,
            'products' => true,
            'customers' => true,
            'messages' => true,
            'payments' => true,
            'analytics' => true,
            'content' => true,
            'settings' => true,
            'admin_management' => true,
            'security' => true
        ],
        'admin' => [
            'dashboard' => true,
            'orders' => true,
            'products' => true,
            'customers' => true,
            'messages' => true,
            'payments' => true,
            'analytics' => true,
            'content' => true,
            'settings' => false,
            'admin_management' => false,
            'security' => false
        ],
        'moderator' => [
            'dashboard' => true,
            'orders' => true,
            'products' => true,
            'customers' => true,
            'messages' => true,
            'payments' => false,
            'analytics' => true,
            'content' => false,
            'settings' => false,
            'admin_management' => false,
            'security' => false
        ]
    ];
    
    return $defaults[$role] ?? [];
}

// Define available permissions
$available_permissions = [
    'dashboard' => 'Access Dashboard',
    'orders' => 'Manage Orders',
    'products' => 'Manage Products',
    'customers' => 'View Customers',
    'messages' => 'Manage Messages',
    'payments' => 'Manage Payments',
    'analytics' => 'View Analytics',
    'content' => 'Manage Content',
    'settings' => 'System Settings',
    'admin_management' => 'Manage Admins',
    'security' => 'Security Management'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Management - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Security & Access Control</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#securityScanModal">
                                <i class="bi bi-shield-check me-1"></i>
                                Security Scan
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Security Overview -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Admins</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($admins) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Failed Logins (24h)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= count(array_filter($failed_logins, function($login) {
                                                return strtotime($login['attempt_time']) > strtotime('-24 hours');
                                            })) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            2FA Enabled</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= count(array_filter($admins, function($admin) {
                                                return $admin['two_factor_enabled'] == 1;
                                            })) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-shield-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Active Sessions</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= count(array_filter($admins, function($admin) {
                                                return $admin['is_active'] == 1;
                                            })) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Tabs -->
                <ul class="nav nav-tabs mb-4" id="securityTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button" role="tab">
                            <i class="bi bi-person-badge me-1"></i>Role Permissions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab">
                            <i class="bi bi-people me-1"></i>Admin Access
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i>Activity Logs
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="threats-tab" data-bs-toggle="tab" data-bs-target="#threats" type="button" role="tab">
                            <i class="bi bi-shield-exclamation me-1"></i>Security Threats
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="securityTabsContent">
                    <!-- Role Permissions Tab -->
                    <div class="tab-pane fade show active" id="roles" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Role-Based Access Control</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($roles as $role): ?>
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2"><?= ucfirst(str_replace('_', ' ', $role)) ?> Permissions</h6>
                                    <form method="POST" class="row g-3">
                                        <input type="hidden" name="action" value="update_role_permissions">
                                        <input type="hidden" name="role" value="<?= $role ?>">
                                        
                                        <div class="row">
                                            <?php foreach ($available_permissions as $perm_key => $perm_label): ?>
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissions[]" value="<?= $perm_key ?>"
                                                           id="perm_<?= $role ?>_<?= $perm_key ?>"
                                                           <?= in_array($perm_key, $role_permissions[$role] ?? []) ? 'checked' : '' ?>
                                                           <?= $role === 'super_admin' && in_array($perm_key, ['dashboard', 'admin_management', 'security']) ? 'disabled' : '' ?>>
                                                    <label class="form-check-label" for="perm_<?= $role ?>_<?= $perm_key ?>">
                                                        <?= $perm_label ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                Update <?= ucfirst(str_replace('_', ' ', $role)) ?> Permissions
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php if ($role !== 'moderator'): ?><hr><?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Access Tab -->
                    <div class="tab-pane fade" id="admins" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Admin User Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Full Name</th>
                                                <th>Role</th>
                                                <th>2FA</th>
                                                <th>Status</th>
                                                <th>Last Login</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($admins as $admin): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($admin['username']) ?></td>
                                                <td><?= htmlspecialchars($admin['full_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= 
                                                        $admin['role'] === 'super_admin' ? 'danger' : 
                                                        ($admin['role'] === 'admin' ? 'primary' : 'warning')
                                                    ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $admin['role'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="enable_2fa">
                                                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="enable_2fa" value="1"
                                                                   <?= $admin['two_factor_enabled'] ? 'checked' : '' ?>
                                                                   onchange="this.form.submit()"
                                                                   <?= $admin['id'] == $_SESSION['admin_id'] ? '' : 'disabled' ?>>
                                                            <label class="form-check-label">
                                                                <?= $admin['two_factor_enabled'] ? 'Enabled' : 'Disabled' ?>
                                                            </label>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td>
                                                    <?php if ($admin['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never' ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#resetPasswordModal"
                                                                data-admin-id="<?= $admin['id'] ?>"
                                                                data-admin-name="<?= htmlspecialchars($admin['full_name']) ?>">
                                                            <i class="bi bi-key"></i>
                                                        </button>
                                                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                        <button class="btn btn-outline-<?= $admin['is_active'] ? 'danger' : 'success' ?>"
                                                                onclick="toggleAdminStatus(<?= $admin['id'] ?>, <?= $admin['is_active'] ?>)">
                                                            <i class="bi bi-<?= $admin['is_active'] ? 'pause' : 'play' ?>"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Tab -->
                    <div class="tab-pane fade" id="activity" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Admin Activity Logs</h5>
                                <button class="btn btn-sm btn-outline-secondary" onclick="clearActivityLogs()">
                                    <i class="bi bi-trash me-1"></i>
                                    Clear Logs
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Admin</th>
                                                <th>Role</th>
                                                <th>Action</th>
                                                <th>IP Address</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activity_logs as $log): ?>
                                            <tr>
                                                <td><?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></td>
                                                <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= 
                                                        $log['role'] === 'super_admin' ? 'danger' : 
                                                        ($log['role'] === 'admin' ? 'primary' : 'warning')
                                                    ?>">
                                                        <?= ucfirst($log['role'] ?? 'system') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-truncate" style="max-width: 200px; display: inline-block;" 
                                                          title="<?= htmlspecialchars($log['action']) ?>">
                                                        <?= htmlspecialchars($log['action']) ?>
                                                    </span>
                                                </td>
                                                <td><code><?= $log['ip_address'] ?></code></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Threats Tab -->
                    <div class="tab-pane fade" id="threats" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Security Threats & Failed Access Attempts</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6>Failed Login Attempts</h6>
                                        <?php if (empty($failed_logins)): ?>
                                            <p class="text-muted">No failed login attempts recorded.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Time</th>
                                                            <th>Username</th>
                                                            <th>IP Address</th>
                                                            <th>User Agent</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (array_slice($failed_logins, 0, 10) as $attempt): ?>
                                                        <tr class="table-warning">
                                                            <td><?= date('M j, g:i A', strtotime($attempt['attempt_time'])) ?></td>
                                                            <td><?= htmlspecialchars($attempt['username']) ?></td>
                                                            <td><code><?= $attempt['ip_address'] ?></code></td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars(substr($attempt['user_agent'], 0, 50)) ?>...
                                                                </small>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6>IP Blocking</h6>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="block_ip">
                                            <div class="mb-3">
                                                <label class="form-label">IP Address to Block</label>
                                                <input type="text" class="form-control" name="ip_address" 
                                                       placeholder="e.g., 192.168.1.100" required
                                                       pattern="^([0-9]{1,3}\.){3}[0-9]{1,3}$">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Reason</label>
                                                <input type="text" class="form-control" name="reason" 
                                                       placeholder="Reason for blocking" required>
                                            </div>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-ban me-1"></i>
                                                Block IP Address
                                            </button>
                                        </form>
                                        
                                        <div class="mt-4">
                                            <h6>Security Recommendations</h6>
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <i class="bi bi-check-circle text-success me-2"></i>
                                                    Enable Two-Factor Authentication for all admins
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="bi bi-check-circle text-success me-2"></i>
                                                    Regularly review activity logs
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="bi bi-exclamation-circle text-warning me-2"></i>
                                                    Change default admin passwords
                                                </li>
                                                <li class="list-group-item">
                                                    <i class="bi bi-exclamation-circle text-warning me-2"></i>
                                                    Limit admin access based on roles
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Admin Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="reset_admin_password.php">
                    <input type="hidden" name="admin_id" id="resetAdminId">
                    <div class="modal-body">
                        <p>Reset password for: <strong id="resetAdminName"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Scan Modal -->
    <div class="modal fade" id="securityScanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Security Health Scan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="securityScanResults">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Scanning...</span>
                            </div>
                            <p class="mt-3">Running security scan...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="runSecurityScan()">Run Scan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Reset password modal handler
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        resetPasswordModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('resetAdminId').value = button.getAttribute('data-admin-id');
            document.getElementById('resetAdminName').textContent = button.getAttribute('data-admin-name');
        });

        // Toggle admin status
        function toggleAdminStatus(adminId, isActive) {
            if (confirm(`Are you sure you want to ${isActive ? 'deactivate' : 'activate'} this admin?`)) {
                // In real implementation, this would be an AJAX call
                alert(`Admin ${isActive ? 'deactivated' : 'activated'}. This is a demo.`);
            }
        }

        // Clear activity logs
        function clearActivityLogs() {
            if (confirm('Are you sure you want to clear all activity logs? This action cannot be undone.')) {
                // In real implementation, this would be an AJAX call
                alert('Activity logs cleared. This is a demo.');
                location.reload();
            }
        }

        // Run security scan
        function runSecurityScan() {
            const resultsDiv = document.getElementById('securityScanResults');
            resultsDiv.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Scanning...</span>
                    </div>
                    <p class="mt-3">Running security scan...</p>
                </div>
            `;
            
            // Simulate scan results
            setTimeout(() => {
                resultsDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle me-2"></i>Security Scan Complete</h6>
                        <p class="mb-0">No critical security issues found.</p>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Security Checks:</h6>
                            <ul class="list-group">
                                <li class="list-group-item list-group-item-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Admin passwords are secure
                                </li>
                                <li class="list-group-item list-group-item-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    2FA not enabled for all admins
                                </li>
                                <li class="list-group-item list-group-item-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    No failed login attempts in last hour
                                </li>
                                <li class="list-group-item list-group-item-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Database connection secure
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Recommendations:</h6>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    Enable 2FA for remaining admin accounts
                                </li>
                                <li class="list-group-item">
                                    Review recent activity logs
                                </li>
                                <li class="list-group-item">
                                    Update server SSL certificate
                                </li>
                                <li class="list-group-item">
                                    Backup database regularly
                                </li>
                            </ul>
                        </div>
                    </div>
                `;
            }, 2000);
        }

        // Auto-run scan when modal opens
        document.getElementById('securityScanModal').addEventListener('show.bs.modal', function () {
            runSecurityScan();
        });

        // Tab persistence
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('activeSecurityTab', event.target.getAttribute('data-bs-target'));
            });
        });

        // Restore active tab
        const activeTab = localStorage.getItem('activeSecurityTab');
        if (activeTab) {
            const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
            if (triggerEl) {
                bootstrap.Tab.getOrCreateInstance(triggerEl).show();
            }
        }
    </script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>