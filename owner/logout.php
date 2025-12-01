<?php
session_start();

// Log owner activity before logout
if (isset($_SESSION['owner_id'])) {
    require_once '../common/connection.php';
    
    // You can log logout activity here if needed
    // $log_stmt = $conn->prepare("INSERT INTO owner_activity_log (owner_id, activity_type) VALUES (?, 'logout')");
    // $log_stmt->bind_param("i", $_SESSION['owner_id']);
    // $log_stmt->execute();
    // $log_stmt->close();
    
    $conn->close();
}

// Clear all owner session variables
unset($_SESSION['owner_logged_in']);
unset($_SESSION['owner_id']);
unset($_SESSION['owner_username']);
unset($_SESSION['owner_full_name']);
unset($_SESSION['owner_email']);
unset($_SESSION['owner_security_level']);

// Destroy the session completely
session_destroy();

// Redirect to owner login page
header('Location: login.php');
exit;
?>