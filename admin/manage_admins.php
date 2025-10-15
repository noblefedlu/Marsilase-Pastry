<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: login.php');
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
                $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $password_hash, $full_name, $role);
                
                if ($stmt->execute()) {
                    $message = 'Admin created successfully';
                } else {
                    $error = 'Failed to create admin: ' . $stmt->error;
                }
            }
            $stmt->close();
        }
    } elseif ($action === 'update_status') {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if ($admin_id === $_SESSION['admin_id']) {
            $error = 'You cannot deactivate your own account';
        } else {
            $stmt = $conn->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
            $stmt->bind_param("ii", $is_active, $admin_id);
            
            if ($stmt->execute()) {
                $message = 'Admin status updated successfully';
            } else {
                $error = 'Failed to update admin status';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete_admin') {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        
        if ($admin_id === $_SESSION['admin_id']) {
            $error = 'You cannot delete your own account';
        } else {
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
}

$admins = [];
$result = $conn->query("SELECT id, username, full_name, role, is_active, created_at FROM admins ORDER BY created_at DESC");
if ($result) {
    $admins = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #8B4513;
            --secondary: #D4A574;
            --light: #FFF8F0;
            --dark: #5D4037;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
    </style>
</head>
<body class="admin-panel">
    <nav class="navbar navbar-dark admin-header">
        <div class="container">
            <span class="navbar-brand fw-bold">
                <i class="bi bi-people me-2"></i>Manage Administrators
            </span>
            <div>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="fw-bold mb-4 text-dark">Administrator Management</h2>
        
        <?php if ($message): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Create Admin Form -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-plus me-2"></i>Create New Admin
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="create_admin">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" class="form-control" name="full_name" required
                                       placeholder="Enter full name">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control" name="username" required
                                       placeholder="Choose username">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control" name="password" required
                                       placeholder="Set password" minlength="6">
                                <div class="form-text small">Minimum 6 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Role</label>
                                <select class="form-select" name="role">
                                    <option value="admin">Administrator</option>
                                    <option value="moderator">Moderator</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-person-plus me-2"></i>Create Admin Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Admins List -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-2"></i>Administrators List
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                        <td class="fw-semibold"><?= htmlspecialchars($admin['full_name']) ?></td>
                                        <td>
                                            <code><?= htmlspecialchars($admin['username']) ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $admin['role'] === 'super_admin' ? 'danger' : 'primary' ?>">
                                                <i class="bi bi-<?= $admin['role'] === 'super_admin' ? 'shield-shaded' : 'person' ?> me-1"></i>
                                                <?= ucfirst(str_replace('_', ' ', $admin['role'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($admin['id'] !== $_SESSION['admin_id']): ?>
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
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Active
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?= date('M j, Y', strtotime($admin['created_at'])) ?>
                                        </td>
                                        <td>
                                            <?php if ($admin['id'] !== $_SESSION['admin_id'] && $admin['role'] !== 'super_admin'): ?>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>