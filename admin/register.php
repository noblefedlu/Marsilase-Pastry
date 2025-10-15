<?php
session_start();
require_once '../config.php';

// Clear any admin session when accessing register page
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_full_name']);
unset($_SESSION['admin_role']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        if (!$conn) {
            $error = 'Database connection failed: ' . mysqli_connect_error();
        } else {
            $check_stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            
            if ($check_stmt === false) {
                $error = 'Failed to prepare statement: ' . $conn->error;
            } else {
                $check_stmt->bind_param("s", $username);
                
                if (!$check_stmt->execute()) {
                    $error = 'Failed to execute query: ' . $check_stmt->error;
                    $check_stmt->close();
                } else {
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $error = 'Username already exists';
                        $check_stmt->close();
                    } else {
                        $check_stmt->close();
                        
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $role = 'admin';
                        
                        $insert_stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
                        
                        if ($insert_stmt === false) {
                            $error = 'Failed to prepare insert statement: ' . $conn->error;
                        } else {
                            $insert_stmt->bind_param("ssss", $username, $password_hash, $full_name, $role);
                            
                            if ($insert_stmt->execute()) {
                                // SUCCESS: Redirect to login page instead of auto-login
                                header('Location: login.php?registered=1');
                                exit;
                                
                            } else {
                                $error = 'Registration failed: ' . $insert_stmt->error;
                            }
                            $insert_stmt->close();
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #8B4513;
            --secondary: #D4A574;
            --light: #FFF8F0;
            --dark: #5D4037;
        }
        
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            border-bottom: 4px solid var(--secondary);
        }
        
        .auth-body {
            padding: 2rem;
            background: white;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 600;
            padding: 12px;
        }
        
        .btn-primary:hover {
            background-color: var(--dark);
            border-color: var(--dark);
        }
        
        .brand-logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .back-to-site {
            background-color: var(--secondary);
            border-color: var(--secondary);
            color: var(--dark);
            font-weight: 600;
        }
        
        .back-to-site:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .security-notice {
            background-color: #e8f4fd;
            border: 1px solid #b6e0fe;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .auth-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary);
        }
    </style>
</head>
<body class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h4 class="mb-1 fw-bold">Admin Account Request</h4>
            <p class="mb-0 opacity-75">Request administrator access</p>
        </div>
        
        <div class="auth-body">
            <div class="security-notice">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle text-primary me-2 fs-5"></i>
                    <div>
                        <strong class="d-block">Administrator Access Required</strong>
                        <small class="d-block">This form is for requesting administrator privileges. Approval may be required.</small>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle me-2"></i>
                <div>
                    <strong>Registration Successful</strong>
                    <div class="small">Please login with your new credentials.</div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <div>
                    <strong>Registration Failed</strong>
                    <div class="small"><?= htmlspecialchars($error) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" id="registrationForm">
                <div class="mb-3">
                    <label for="full_name" class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>Full Name
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person-badge text-primary"></i>
                        </span>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?= htmlspecialchars($full_name ?? '') ?>" 
                               placeholder="Enter your full name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">
                        <i class="bi bi-at me-1"></i>Username
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person text-primary"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($username ?? '') ?>" 
                               placeholder="Choose a username" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-key me-1"></i>Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock text-primary"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Create a secure password" required minlength="6">
                    </div>
                    <div class="form-text">Password must be at least 6 characters long.</div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label fw-semibold">
                        <i class="bi bi-key-fill me-1"></i>Confirm Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill text-primary"></i>
                        </span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password" required>
                    </div>
                    <div class="invalid-feedback" id="confirmPasswordError">Passwords do not match</div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3 fw-bold py-2">
                    <i class="bi bi-person-plus me-2"></i>Request Admin Access
                </button>
                
                <div class="d-grid gap-2">
                    <a href="../index.php" class="btn back-to-site w-100">
                        <i class="bi bi-arrow-left me-2"></i>Back to Main Site
                    </a>
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none text-primary fw-semibold small">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Already have an account? Login here
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            
            function validatePasswords() {
                if (confirmPassword.value && password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                    confirmPasswordError.style.display = 'block';
                    return false;
                } else {
                    confirmPassword.classList.remove('is-invalid');
                    confirmPasswordError.style.display = 'none';
                    return true;
                }
            }
            
            confirmPassword.addEventListener('input', validatePasswords);
            password.addEventListener('input', validatePasswords);
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                if (!validatePasswords()) {
                    isValid = false;
                    confirmPassword.focus();
                }
                
                if (password.value.length < 6) {
                    isValid = false;
                    password.setCustomValidity('Password must be at least 6 characters long.');
                    password.focus();
                } else {
                    password.setCustomValidity('');
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php 
if (isset($conn) && $conn) {
    $conn->close(); 
}
?>