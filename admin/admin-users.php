<?php
// admin/admin-users.php
require_once 'config.php';
requireAdminAuth();

// Only super admin can access this page
if ($_SESSION['admin_role'] !== 'super_admin') {
    header('Location: dashboard.php');
    exit;
}

// Handle admin user actions
if (isset($_POST['add_admin'])) {
    $username = sanitizeInput($_POST['username']);
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if username already exists
    $check_stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error_message'] = "Username already exists!";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $_SESSION['error_message'] = "Password must be at least 8 characters long!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $password_hash, $full_name, $email, $role);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Admin user added successfully!";
            logAdminAction('Admin User Added', "Added new admin: $username ($role)");
        } else {
            $_SESSION['error_message'] = "Error adding admin user: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

if (isset($_POST['update_admin'])) {
    $admin_id = $_POST['admin_id'];
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE admin_users SET full_name = ?, email = ?, role = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssii", $full_name, $email, $role, $is_active, $admin_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Admin user updated successfully!";
        logAdminAction('Admin User Updated', "Updated admin user ID: $admin_id");
    } else {
        $_SESSION['error_message'] = "Error updating admin user: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_GET['toggle_status'])) {
    $admin_id = $_GET['toggle_status'];
    $result = $conn->query("SELECT is_active FROM admin_users WHERE id = $admin_id");
    if ($result && $row = $result->fetch_assoc()) {
        $new_status = $row['is_active'] ? 0 : 1;
        $conn->query("UPDATE admin_users SET is_active = $new_status WHERE id = $admin_id");
        $_SESSION['success_message'] = "Admin status updated!";
        logAdminAction('Admin Status Toggled', "Toggled admin user ID: $admin_id to " . ($new_status ? 'active' : 'inactive'));
    }
}

// Get all admin users
$admins = $conn->query("SELECT * FROM admin_users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - Marsilase Pastry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Admin Users Management</h2>
                <p class="text-muted mb-0">Manage administrative access and permissions</p>
            </div>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="bi bi-person-plus me-2"></i>Add Admin User
                </button>
            </div>
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

        <!-- Admin Users Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Admin Users (<?= count($admins) ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Admin User</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($admin['full_name']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($admin['email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($admin['username']) ?></code>
                                    <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                    <span class="badge bg-info ms-1">You</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $admin['role'] === 'super_admin' ? 'danger' : 
                                        ($admin['role'] === 'admin' ? 'primary' : 'secondary')
                                    ?>">
                                        <?= ucfirst(str_replace('_', ' ', $admin['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $admin['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $admin['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never' ?>
                                </td>
                                <td>
                                    <?= date('M j, Y', strtotime($admin['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editAdminModal"
                                                onclick="editAdmin(<?= htmlspecialchars(json_encode($admin)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                        <a href="?toggle_status=<?= $admin['id'] ?>" 
                                           class="btn btn-outline-<?= $admin['is_active'] ? 'warning' : 'success' ?>"
                                           title="<?= $admin['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="bi bi-power"></i>
                                        </a>
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

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Admin User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role *</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="moderator">Moderator</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                                <div class="form-text">Password must be at least 8 characters long</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_admin" class="btn btn-primary">Add Admin User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="admin_id" id="editAdminId">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Admin User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control" id="editUsername" readonly>
                                <div class="form-text">Username cannot be changed</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <input type="text" name="full_name" id="editFullName" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" id="editEmail" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role *</label>
                                <select name="role" id="editRole" class="form-select" required>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="admin">Admin</option>
                                    <option value="moderator">Moderator</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="editIsActive">
                                    <label class="form-check-label fw-semibold" for="editIsActive">Active User</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_admin" class="btn btn-primary">Update Admin User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editAdmin(admin) {
            document.getElementById('editAdminId').value = admin.id;
            document.getElementById('editUsername').value = admin.username;
            document.getElementById('editFullName').value = admin.full_name;
            document.getElementById('editEmail').value = admin.email || '';
            document.getElementById('editRole').value = admin.role;
            document.getElementById('editIsActive').checked = admin.is_active == 1;
        }
    </script>
</body>
</html>