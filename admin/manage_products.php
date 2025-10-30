<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Product management logic here...
// This would allow admins to add/edit products with images
?>