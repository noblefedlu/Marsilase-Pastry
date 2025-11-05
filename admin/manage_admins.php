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

// Check admin authentication and super admin role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: ../?page=admin-login');
    exit;
}

// Handle admin actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_admin') {
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $full_name = $_POST['full_name'];
            $role = $_POST['role'];

            $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $username, $password, $full_name, $role);
                if ($stmt->execute()) {
                    $message = "Admin added successfully!";
                } else {
                    $error = "Failed to add admin: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }

        if ($_POST['action'] === 'update_admin') {
            $id = $_POST['id'];
            $full_name = $_POST['full_name'];
            $role = $_POST['role'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $stmt = $conn->prepare("UPDATE admins SET full_name=?, role=?, is_active=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param("ssii", $full_name, $role, $is_active, $id);
                if ($stmt->execute()) {
                    $message = "Admin updated successfully!";
                } else {
                    $error = "Failed to update admin: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Don't allow deleting yourself
    if ($id != $_SESSION['admin_id']) {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Admin deleted successfully!";
            } else {
                $error = "Failed to delete admin: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    } else {
        $error = "You cannot delete your own account!";
    }
}

// Get all admins
$admins_result = $conn->query("SELECT * FROM admins ORDER BY created_at DESC");
if ($admins_result) {
    $admins = $admins_result->fetch_all(MYSQLI_ASSOC);
} else {
    $admins = [];
    $error = "Failed to load admins: " . $conn->error;
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
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Administrators</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminModal">
                        <i class="bi bi-person-plus me-1"></i>
                        Add New Admin
                    </button>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Administrators</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($admin['id']) ?></td>
                                        <td><?= htmlspecialchars($admin['username']) ?></td>
                                        <td><?= htmlspecialchars($admin['full_name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $admin['role'] === 'super_admin' ? 'danger' : 
                                                ($admin['role'] === 'admin' ? 'primary' : 'secondary')
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $admin['role'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($admin['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($admin['created_at'])) ?></td>
                                        <td>
                                            <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                <a href="?edit=<?= $admin['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=delete&id=<?= $admin['id'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this admin?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Admin Modal -->
    <div class="modal fade" id="adminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_admin">
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>