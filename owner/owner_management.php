<?php
session_start();
require_once '../common/connection.php';
requireOwner();

// Only full security level owners can manage other owners
if ($_SESSION['owner_security_level'] !== 'full') {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_owner') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $security_level = $_POST['security_level'] ?? 'limited';
        
        if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
            $error = 'All fields are required';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            $stmt = $conn->prepare("SELECT id FROM owners WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Username or email already exists';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO owners (username, password_hash, full_name, email, security_level, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $username, $password_hash, $full_name, $email, $security_level, $_SESSION['owner_id']);
                
                if ($stmt->execute()) {
                    $new_owner_id = $stmt->insert_id;
                    
                    // Set default permissions based on security level
                    $default_permissions = [];
                    
                    if ($security_level === 'limited') {
                        $default_permissions = ['view_reports', 'manage_products', 'manage_orders'];
                    } elseif ($security_level === 'financial_only') {
                        $default_permissions = ['view_reports', 'financial_reports'];
                    } elseif ($security_level === 'full') {
                        $default_permissions = ['manage_products', 'manage_admins', 'view_reports', 'manage_orders', 'system_settings', 'financial_reports'];
                    }
                    
                    if (!empty($default_permissions)) {
                        $perm_stmt = $conn->prepare("INSERT INTO owner_permissions (owner_id, permission_key, permission_value) VALUES (?, ?, 1)");
                        
                        foreach ($default_permissions as $permission) {
                            $perm_stmt->bind_param("is", $new_owner_id, $permission);
                            $perm_stmt->execute();
                        }
                        $perm_stmt->close();
                    }
                    
                    $message = 'Owner account created successfully';
                } else {
                    $error = 'Failed to create owner account: ' . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
    
    elseif ($action === 'update_status') {
        $owner_id = intval($_POST['owner_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($owner_id === $_SESSION['owner_id']) {
            $error = 'You cannot deactivate your own account';
        } else {
            $stmt = $conn->prepare("UPDATE owners SET is_active = ? WHERE id = ?");
            $stmt->bind_param("ii", $is_active, $owner_id);
            
            if ($stmt->execute()) {
                $message = 'Owner status updated successfully';
            } else {
                $error = 'Failed to update owner status';
            }
            $stmt->close();
        }
    }
    
    elseif ($action === 'update_permissions') {
        $owner_id = intval($_POST['owner_id'] ?? 0);
        
        // Prevent owners from modifying their own permissions
        if ($owner_id === $_SESSION['owner_id']) {
            $error = 'You cannot modify your own permissions';
        } else {
            // Get all possible permissions
            $possible_permissions = ['manage_products', 'manage_admins', 'view_reports', 'manage_orders', 'system_settings', 'financial_reports'];
            
            // First, reset all permissions to 0
            $reset_stmt = $conn->prepare("UPDATE owner_permissions SET permission_value = 0 WHERE owner_id = ?");
            $reset_stmt->bind_param("i", $owner_id);
            $reset_stmt->execute();
            $reset_stmt->close();
            
            // Then set the selected permissions to 1
            if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
                foreach ($_POST['permissions'] as $permission => $value) {
                    if (in_array($permission, $possible_permissions)) {
                        $stmt = $conn->prepare("INSERT INTO owner_permissions (owner_id, permission_key, permission_value) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE permission_value = 1");
                        $stmt->bind_param("is", $owner_id, $permission);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
            
            $message = 'Permissions updated successfully';
        }
    }
    
    elseif ($action === 'delete_owner') {
        $owner_id = intval($_POST['owner_id'] ?? 0);
        
        if ($owner_id === $_SESSION['owner_id']) {
            $error = 'You cannot delete your own account';
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Delete permissions first
                $stmt = $conn->prepare("DELETE FROM owner_permissions WHERE owner_id = ?");
                $stmt->bind_param("i", $owner_id);
                $stmt->execute();
                $stmt->close();
                
                // Delete owner
                $stmt = $conn->prepare("DELETE FROM owners WHERE id = ?");
                $stmt->bind_param("i", $owner_id);
                
                if ($stmt->execute()) {
                    $conn->commit();
                    $message = 'Owner account deleted successfully';
                } else {
                    throw new Exception('Failed to delete owner account');
                }
                $stmt->close();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Failed to delete owner account: ' . $e->getMessage();
            }
        }
    }
}

// Get all owners
$owners = [];
$result = $conn->query("
    SELECT o.*, creator.username as created_by_name 
    FROM owners o 
    LEFT JOIN owners creator ON o.created_by = creator.id 
    ORDER BY o.is_active DESC, o.created_at DESC
");
if ($result) {
    $owners = $result->fetch_all(MYSQLI_ASSOC);
}

// Get permissions for each owner
$owner_permissions = [];
foreach ($owners as $owner) {
    $perm_result = $conn->query("SELECT permission_key, permission_value FROM owner_permissions WHERE owner_id = {$owner['id']}");
    $permissions = $perm_result->fetch_all(MYSQLI_ASSOC);
    $owner_permissions[$owner['id']] = [];
    foreach ($permissions as $perm) {
        $owner_permissions[$owner['id']][$perm['permission_key']] = (bool)$perm['permission_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Management - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --owner-primary: #2c3e50;
            --owner-secondary: #34495e;
            --owner-accent: #e74c3c;
        }
        
        .owner-nav { 
            background: var(--owner-primary); 
        }
        
        .btn-owner {
            background: var(--owner-primary);
            color: white;
            border: none;
        }
        
        .btn-owner:hover {
            background: var(--owner-secondary);
            color: white;
        }
        
        .security-badge-full { background: #28a745; }
        .security-badge-limited { background: #ffc107; color: #000; }
        .security-badge-financial_only { background: #17a2b8; }
        
        .permission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .permission-item {
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        
        .permission-active {
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        
        .actions-column {
            width: 200px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg owner-nav">
        <div class="container">
            <span class="navbar-brand text-white">
                <i class="bi bi-person-gear me-2"></i>Owner Management
            </span>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="nav-link text-white me-3">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <?php if (checkPermission('manage_admins')): ?>
                <a href="manage_admins.php" class="nav-link text-white me-3">
                    <i class="bi bi-people me-1"></i>Admins
                </a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link text-white">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Owner Account Management</h2>
            <button class="btn btn-owner" data-bs-toggle="modal" data-bs-target="#addOwnerModal">
                <i class="bi bi-person-plus me-2"></i>Add Owner
            </button>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Owners List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>System Owners</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($owners)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <h5 class="text-muted">No owners found</h5>
                        <p class="text-muted">Create the first owner account to get started.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Contact</th>
                                <th>Security Level</th>
                                <th>Status</th>
                                <th>Permissions</th>
                                <th>Last Login</th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($owners as $owner): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($owner['full_name']) ?></div>
                                    <small class="text-muted">@<?= htmlspecialchars($owner['username']) ?></small>
                                    <?php if ($owner['id'] === $_SESSION['owner_id']): ?>
                                    <span class="badge bg-primary ms-1">You</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($owner['email']) ?></div>
                                    <?php if ($owner['phone']): ?>
                                    <small class="text-muted"><?= htmlspecialchars($owner['phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge security-badge-<?= $owner['security_level'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $owner['security_level'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($owner['id'] !== $_SESSION['owner_id']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" 
                                                   <?= $owner['is_active'] ? 'checked' : '' ?>
                                                   onchange="this.form.submit()">
                                        </div>
                                    </form>
                                    <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <?php
                                        $active_permissions = array_filter($owner_permissions[$owner['id']]);
                                        if (!empty($active_permissions)) {
                                            echo implode(', ', array_keys($active_permissions));
                                        } else {
                                            echo '<span class="text-muted">No permissions</span>';
                                        }
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($owner['last_login']): ?>
                                    <small class="text-muted">
                                        <?= date('M j, Y g:i A', strtotime($owner['last_login'])) ?>
                                    </small>
                                    <?php else: ?>
                                    <small class="text-muted">Never</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#permissionsModal<?= $owner['id'] ?>">
                                            <i class="bi bi-shield-check"></i> Permissions
                                        </button>
                                        <?php if ($owner['id'] !== $_SESSION['owner_id']): ?>
                                        <button class="btn btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal<?= $owner['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Permissions Modal for each owner -->
                            <div class="modal fade" id="permissionsModal<?= $owner['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Permissions for <?= htmlspecialchars($owner['full_name']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_permissions">
                                            <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    Uncheck all permissions to restrict access completely.
                                                </div>
                                                <div class="permission-grid">
                                                    <?php
                                                    $possible_permissions = [
                                                        'manage_products' => 'Manage Products',
                                                        'manage_admins' => 'Manage Admins', 
                                                        'view_reports' => 'View Reports',
                                                        'manage_orders' => 'Manage Orders',
                                                        'system_settings' => 'System Settings',
                                                        'financial_reports' => 'Financial Reports'
                                                    ];
                                                    
                                                    $current_permissions = $owner_permissions[$owner['id']] ?? [];
                                                    
                                                    foreach ($possible_permissions as $key => $label): 
                                                        $is_checked = isset($current_permissions[$key]) ? $current_permissions[$key] : false;
                                                    ?>
                                                    <div class="permission-item <?= $is_checked ? 'permission-active' : '' ?>">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="permissions[<?= $key ?>]" 
                                                                   id="perm_<?= $owner['id'] ?>_<?= $key ?>"
                                                                   <?= $is_checked ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="perm_<?= $owner['id'] ?>_<?= $key ?>">
                                                                <?= $label ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-owner">Update Permissions</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Confirmation Modal -->
                            <?php if ($owner['id'] !== $_SESSION['owner_id']): ?>
                            <div class="modal fade" id="deleteModal<?= $owner['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Delete Owner Account</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="delete_owner">
                                            <input type="hidden" name="owner_id" value="<?= $owner['id'] ?>">
                                            <div class="modal-body">
                                                <div class="alert alert-danger">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                    <strong>Warning:</strong> This action cannot be undone!
                                                </div>
                                                <p>Are you sure you want to delete the owner account for <strong><?= htmlspecialchars($owner['full_name']) ?></strong>?</p>
                                                <p class="text-muted">This will permanently remove all their permissions and access to the system.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete Owner</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Owner Modal -->
    <div class="modal fade" id="addOwnerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Owner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create_owner">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Security Level</label>
                            <select class="form-select" name="security_level">
                                <option value="limited">Limited Access</option>
                                <option value="financial_only">Financial Only</option>
                                <option value="full">Full Access</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-owner">Create Owner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Update permission item styling when checkboxes change
        document.querySelectorAll('.permission-item input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const permissionItem = this.closest('.permission-item');
                if (this.checked) {
                    permissionItem.classList.add('permission-active');
                } else {
                    permissionItem.classList.remove('permission-active');
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>