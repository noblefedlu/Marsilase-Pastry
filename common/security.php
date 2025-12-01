<?php
// Additional security functions

function logAdminAction($action, $details = '') {
    global $conn;
    
    $admin_id = $_SESSION['admin_id'] ?? null;
    $admin_name = $_SESSION['admin_full_name'] ?? 'Unknown';
    
    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, admin_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $admin_id, $admin_name, $action, $details, $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    $stmt->close();
}

function checkBruteForce($username, $max_attempts = 5, $lockout_time = 900) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->bind_param("si", $username, $lockout_time);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['attempts'] >= $max_attempts;
}

function recordLoginAttempt($username, $success) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO login_attempts (username, success, ip_address) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $username, $success, $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    $stmt->close();
}
?>