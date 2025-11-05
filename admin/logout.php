<?php
// admin/logout.php
require_once 'config.php';

// Log logout activity
if (isset($_SESSION['admin_id'])) {
    logAdminAction('Logout', 'Admin logged out of the system');
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
?>