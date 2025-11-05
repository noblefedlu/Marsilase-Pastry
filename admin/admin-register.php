<?php
// admin/admin-register.php
session_start();

// Define database credentials directly
$db_config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'marsilase_pastry'
];

// Create database connection
$conn = new mysqli(
    $db_config['servername'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['dbname']
);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$error = '';
$success = '';
$full_name = '';
$username = '';
$email = '';
$role = 'admin';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_register') {
    // Get form data with proper validation
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    
    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
        if ($stmt === false) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Username or email already registered!";
            } else {
                // Hash password and insert new admin
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $insert_stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
                if ($insert_stmt === false) {
                    $error = "Database error: " . $conn->error;
                } else {
                    $insert_stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $role);
                    
                    if ($insert_stmt->execute()) {
                        $success = "Admin account created successfully! You can now <a href='login.php' class='alert-link'>login here</a>.";
                        // Clear form
                        $full_name = $username = $email = '';
                        $role = 'admin';
                    } else {
                        $error = "Registration failed. Please try again. Error: " . $insert_stmt->error;
                    }
                    $insert_stmt->close();
                }
            }
            $stmt->close();
        }
    }
}

$conn->close();

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
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
            --cream: #FFF5E1;
            --orange: #FF914D;
            --white: #FFFFFF;
            --brown: #3A2E1F;
            --light-orange: #FFE8D6;
        }
        
        body {
            background: var(--cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }
        
        .register-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--orange) 0%, #E5813D 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .register-body {
            padding: 2rem;
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
        
        .brand-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .login-link {
            color: var(--orange);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .login-link:hover {
            color: #E5813D;
            text-decoration: underline;
        }
        
        .back-to-home {
            color: var(--brown);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .back-to-home:hover {
            color: var(--orange);
        }
        
        .password-strength {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #fd7e14; }
        .strength-strong { color: #198754; }
        
        .alert-link {
            color: #0f5132;
            text-decoration: underline;
        }
        
        .alert-link:hover {
            color: #0c4128;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <!-- Back to Home Link -->
            <div class="text-start">
                <a href="../index.php" class="back-to-home">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Home
                </a>
            </div>

            <!-- Register Card -->
            <div class="register-card">
                <div class="register-header">
                    <div class="brand-logo">
                        <i class="bi bi-cake2"></i>
                    </div>
                    <h4 class="mb-1">Marsilase Pastry</h4>
                    <p class="mb-0 opacity-75">Admin Registration</p>
                </div>
                
                <div class="register-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="registerForm">
                        <input type="hidden" name="action" value="admin_register">
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="full_name" name="full_name" 
                                       placeholder="Enter your full name" value="<?= htmlspecialchars($full_name) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-at text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="username" name="username" 
                                       placeholder="Enter your username" value="<?= htmlspecialchars($username) ?>" required>
                            </div>
                            <div class="form-text">Username must be unique and cannot be changed later.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control border-start-0" id="email" name="email" 
                                       placeholder="Enter your email" value="<?= htmlspecialchars($email) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="moderator" <?= $role === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            </select>
                            <div class="form-text">
                                <small>
                                    <strong>Admin:</strong> Full access • 
                                    <strong>Moderator:</strong> Limited access • 
                                    <strong>Super Admin:</strong> All privileges
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="password" name="password" 
                                       placeholder="Enter your password" required>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock-fill text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm your password" required>
                            </div>
                            <div class="password-match" id="passwordMatch"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3 py-2 fw-semibold">
                            <i class="bi bi-person-plus me-2"></i>
                            Register Admin Account
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-2">Already have an admin account?</p>
                            <a href="login.php" class="login-link fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                Login to Existing Account
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="bi bi-shield-lock me-1"></i>
                    Secure admin registration • All activities are logged
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            const form = document.getElementById('registerForm');
            
            // Password strength checker
            function checkPasswordStrength(value) {
                if (value.length === 0) {
                    return { strength: '', class: '' };
                } else if (value.length < 6) {
                    return { strength: 'Weak - at least 6 characters required', class: 'strength-weak' };
                } else if (value.length < 8) {
                    return { strength: 'Medium', class: 'strength-medium' };
                } else {
                    // Check for strong password (mix of characters)
                    const hasUpperCase = /[A-Z]/.test(value);
                    const hasLowerCase = /[a-z]/.test(value);
                    const hasNumbers = /\d/.test(value);
                    const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                    
                    const strengthCount = [hasUpperCase, hasLowerCase, hasNumbers, hasSpecialChars].filter(Boolean).length;
                    
                    if (strengthCount >= 3) {
                        return { strength: 'Strong', class: 'strength-strong' };
                    } else {
                        return { strength: 'Medium - add uppercase, numbers, or special characters', class: 'strength-medium' };
                    }
                }
            }
            
            // Password match checker
            function checkPasswordMatch() {
                if (password.value === '' && confirmPassword.value === '') {
                    passwordMatch.textContent = '';
                    passwordMatch.className = 'password-match';
                } else if (password.value !== confirmPassword.value) {
                    passwordMatch.textContent = 'Passwords do not match';
                    passwordMatch.className = 'password-match strength-weak';
                } else {
                    passwordMatch.textContent = 'Passwords match';
                    passwordMatch.className = 'password-match strength-strong';
                }
            }
            
            // Event listeners
            password.addEventListener('input', function() {
                const strengthInfo = checkPasswordStrength(this.value);
                passwordStrength.textContent = strengthInfo.strength;
                passwordStrength.className = 'password-strength ' + strengthInfo.class;
                checkPasswordMatch();
            });
            
            confirmPassword.addEventListener('input', checkPasswordMatch);
            
            // Form submission handler
            form.addEventListener('submit', function(e) {
                // Client-side validation
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Error: Passwords do not match!');
                    return;
                }
                
                if (password.value.length < 6) {
                    e.preventDefault();
                    alert('Error: Password must be at least 6 characters long!');
                    return;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
                submitBtn.disabled = true;
            });
            
            // Real-time username validation
            const usernameInput = document.getElementById('username');
            usernameInput.addEventListener('input', function() {
                const username = this.value.trim();
                if (username.length > 0 && !/^[a-zA-Z0-9_]+$/.test(username)) {
                    this.style.borderColor = '#dc3545';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                } else {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }
            });
        });
    </script>
</body>
</html>