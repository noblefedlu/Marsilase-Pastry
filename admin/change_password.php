<?php
session_start();
include '../config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        if (password_verify($current_password, $admin['password_hash'])) {
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_password_hash, $_SESSION['admin_id']);
            
            if ($update_stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
            }
            $update_stmt->close();
        } else {
            $error = 'Current password is incorrect';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: #8B4513;
        }
        
        .navbar-brand {
            color: white;
            font-weight: 600;
        }
        
        .change-password-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 20px;
        }
        
        .change-password-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .form-control {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: #8B4513;
            box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.1);
        }
        
        .btn-primary {
            background: #8B4513;
            border: none;
            padding: 12px;
            border-radius: 6px;
            width: 100%;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #654321;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: #8B4513;
        }
        
        .alert {
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .nav-link {
            color: white !important;
        }
        
        .nav-link:hover {
            color: #f8f9fa !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-shield-lock me-2"></i>Change Password
            </span>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="nav-link me-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="change-password-container">
            <div class="change-password-card">
                <h3 class="text-center mb-4">Change Password</h3>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <form method="POST" id="changePasswordForm">
                    <div class="mb-3 password-container">
                        <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Current Password" required>
                        <button type="button" class="password-toggle" data-target="currentPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="mb-3 password-container">
                        <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="New Password" required>
                        <button type="button" class="password-toggle" data-target="newPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div class="mb-3 password-container">
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm New Password" required>
                        <button type="button" class="password-toggle" data-target="confirmPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Toggle eye icon
                    if (type === 'password') {
                        this.innerHTML = '<i class="bi bi-eye"></i>';
                    } else {
                        this.innerHTML = '<i class="bi bi-eye-slash"></i>';
                    }
                });
            });
            
            // Clear form on success
            <?php if ($success): ?>
            document.getElementById('changePasswordForm').reset();
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php 
if (isset($conn) && $conn) {
    $conn->close(); 
}
?>