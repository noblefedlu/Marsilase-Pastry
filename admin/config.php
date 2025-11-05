<?php
// admin/config.php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'marsilase_pastry');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Admin authentication check function
function requireAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

// Log admin activity
function logAdminAction($action, $description = '') {
    global $conn;
    
    $admin_id = $_SESSION['admin_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $admin_id, $action, $description, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// File upload function
function uploadFile($file, $upload_dir = '../uploads/') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $filename, 'path' => str_replace('../', '', $target_path)];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

// Security functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateOrderNumber() {
    return 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
}
?>