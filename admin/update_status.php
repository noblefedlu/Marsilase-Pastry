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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Order status updated successfully!";
        } else {
            $_SESSION['error'] = "❌ Failed to update order status: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "❌ Database error: " . $conn->error;
    }
    
    header("Location: ../?page=admin-orders&id=" . $order_id);
    exit;
} else {
    header("Location: ../?page=admin-orders");
    exit;
}
?>