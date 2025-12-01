<?php
session_start();
require_once '../common/connection.php';
requireOwner();
requirePermission('manage_admins');

// Only full security level owners can manage admins
if (!checkOwnerPermission('manage_admins')) {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_admin') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'admin';
        
        if (empty($username) || empty($password) || empty($full_name)) {
            $error = 'All fields are required';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Username already exists';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->bind_param("ssss", $username, $password_hash, $full_name, $role);
                
                if ($stmt->execute()) {
                    $message = 'Admin created successfully';
                } else {
                    $error = 'Failed to create admin: ' . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
    elseif ($action === 'update_status') {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_active, $admin_id);
        
        if ($stmt->execute()) {
            $message = 'Admin status updated successfully';
        } else {
            $error = 'Failed to update admin status';
        }
        $stmt->close();
    }
    elseif ($action === 'delete_admin') {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        
        if ($stmt->execute()) {
            $message = 'Admin deleted successfully';
        } else {
            $error = 'Failed to delete admin';
        }
        $stmt->close();
    }
}

// Get all admins
$admins = [];
$result = $conn->query("SELECT id, username, full_name, role, is_active, created_at FROM admins ORDER BY is_active DESC, created_at DESC");
if ($result) {
    $admins = $result->fetch_all(MYSQLI_ASSOC);
}

// Get pending approval count
$pending_count = $conn->query("SELECT COUNT(*) as count FROM admins WHERE is_active = 0")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Marsilase Pastry Admin</title>
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
        
        .badge-super-admin { background: #dc3545; }
        .badge-admin { background: #0d6efd; }
        .badge-moderator { background: #6c757d; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg owner-nav">
        <div class="container">
            <span class="navbar-brand text-white">
                <i class="bi bi-people me-2"></i>Manage Administrators
            </span>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="nav-link text-white me-3">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <a href="owner_management.php" class="nav-link text-white me-3">
                    <i class="bi bi-person-gear me-1"></i>Owners
                </a>
                <a href="logout.php" class="nav-link text-white">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Administrator Management</h2>
            <div>
                <?php if ($pending_count > 0): ?>
                <span class="badge bg-warning me-2"><?= $pending_count ?> pending approval</span>
                <?php endif; ?>
                <button class="btn btn-owner" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="bi bi-person-plus me-2"></i>Create Admin
                </button>
            </div>
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

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>Administrators List
                </h5>
                <span class="badge bg-primary"><?= count($admins) ?> admin<?= count($admins) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($admins)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <h5 class="text-muted">No administrators found</h5>
                        <p class="text-muted">Get started by creating the first admin account.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($admin['full_name']) ?></div>
                                </td>
                                <td>
                                    <code class="text-primary"><?= htmlspecialchars($admin['username']) ?></code>
                                </td>
                                <td>
                                    <span class="badge badge-<?= str_replace('_', '-', $admin['role']) ?>">
                                        <i class="bi bi-<?= $admin['role'] === 'super_admin' ? 'shield-shaded' : ($admin['role'] === 'admin' ? 'person-gear' : 'person') ?> me-1"></i>
                                        <?= ucfirst(str_replace('_', ' ', $admin['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" 
                                                   <?= $admin['is_active'] ? 'checked' : '' ?>
                                                   onchange="this.form.submit()"
                                                   style="transform: scale(1.2);">
                                        </div>
                                    </form>
                                </td>
                                <td class="text-muted small">
                                    <?= date('M j, Y', strtotime($admin['created_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($admin['role'] !== 'super_admin'): ?>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this admin? This action cannot be undone.')">
                                        <input type="hidden" name="action" value="delete_admin">
                                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                title="Delete Admin">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
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

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create_admin">
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
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select class="form-select" name="role" required>
                                <option value="admin">Administrator</option>
                                <option value="moderator">Moderator</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-owner">Create Admin</button>
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
    </script>
</body>
</html>
<?php $conn->close(); ?>