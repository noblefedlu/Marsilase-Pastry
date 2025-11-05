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

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../?page=admin-login');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "❌ Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "❌ New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "❌ New password must be at least 6 characters long!";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['admin_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            $stmt->close();
            
            if ($admin && password_verify($current_password, $admin['password_hash'])) {
                // Update password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admins SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $new_password_hash, $_SESSION['admin_id']);
                    
                    if ($stmt->execute()) {
                        $message = "✅ Password changed successfully!";
                        // Clear form
                        $_POST = array();
                    } else {
                        $error = "❌ Failed to change password. Please try again.";
                    }
                    $stmt->close();
                } else {
                    $error = "❌ Database error: " . $conn->error;
                }
            } else {
                $error = "❌ Current password is incorrect!";
            }
        } else {
            $error = "❌ Database error: " . $conn->error;
        }
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
        :root {
            --cream: #FFF5E1;
            --orange: #FF914D;
            --white: #FFFFFF;
            --brown: #3A2E1F;
            --light-orange: #FFE8D6;
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }
        
        body {
            background: var(--cream);
            color: var(--brown);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .security-card {
            background: linear-gradient(135deg, var(--white) 0%, var(--light-orange) 100%);
            border-radius: 16px;
            padding: 2rem;
            border: none;
        }
        
        .btn-primary {
            background: var(--orange);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #E5813D;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 145, 77, 0.4);
        }
        
        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 145, 77, 0.25);
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .security-icon {
            width: 80px;
            height: 80px;
            background: var(--orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">Change Password</h1>
                        <p class="text-muted mb-0">Update your account password</p>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <div class="security-card fade-in">
                            <div class="security-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            
                            <h4 class="text-center mb-4">Update Your Password</h4>

                            <?php if (!empty($message)): ?>
                                <div class="alert alert-success glass-card border-0 mb-4">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?= $message ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger glass-card border-0 mb-4">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-4">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required
                                           placeholder="Enter your current password">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" required
                                           placeholder="Enter new password" minlength="6"
                                           id="newPassword">
                                    <div class="form-text">Minimum 6 characters</div>
                                    <div class="password-strength" id="passwordStrength"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required
                                           placeholder="Confirm new password"
                                           id="confirmPassword">
                                    <div class="form-text" id="passwordMatch"></div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-key me-2"></i>
                                    Change Password
                                </button>
                            </form>
                            
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="text-muted mb-3">Password Requirements:</h6>
                                <ul class="list-unstyled small text-muted">
                                    <li><i class="bi bi-check-circle text-success me-2"></i> Minimum 6 characters</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i> Should be different from current password</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i> Use a combination of letters and numbers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            
            function checkPasswordStrength(password) {
                let strength = 0;
                if (password.length >= 6) strength += 25;
                if (password.length >= 8) strength += 25;
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[0-9]/.test(password)) strength += 25;
                
                return strength;
            }
            
            function updatePasswordStrength() {
                const password = newPassword.value;
                const strength = checkPasswordStrength(password);
                
                let color = '#dc3545'; // red
                let text = 'Weak';
                
                if (strength >= 50) {
                    color = '#ffc107'; // yellow
                    text = 'Medium';
                }
                if (strength >= 75) {
                    color = '#198754'; // green
                    text = 'Strong';
                }
                
                passwordStrength.style.background = color;
                passwordStrength.style.width = strength + '%';
                
                if (password.length > 0) {
                    passwordStrength.parentElement.querySelector('.form-text').textContent = text;
                }
            }
            
            function checkPasswordMatch() {
                const newPass = newPassword.value;
                const confirmPass = confirmPassword.value;
                
                if (confirmPass.length === 0) {
                    passwordMatch.textContent = '';
                    passwordMatch.className = 'form-text';
                } else if (newPass === confirmPass) {
                    passwordMatch.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> Passwords match';
                    passwordMatch.className = 'form-text text-success';
                } else {
                    passwordMatch.innerHTML = '<i class="bi bi-x-circle text-danger me-1"></i> Passwords do not match';
                    passwordMatch.className = 'form-text text-danger';
                }
            }
            
            newPassword.addEventListener('input', updatePasswordStrength);
            newPassword.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);
        });
    </script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>