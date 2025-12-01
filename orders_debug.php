<?php
// orders_debug.php - Debug version to see what's happening
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Log everything for debugging
error_log("=== ORDER DEBUG START ===");
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION cart: " . print_r($_SESSION['cart'] ?? [], true));

// Check if cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    error_log("Cart is empty");
    echo "CART_EMPTY";
    exit;
}

error_log("=== ORDER DEBUG END ===");

// Return simple success for testing
echo json_encode([
    'success' => true,
    'message' => 'Debug mode - order would be processed',
    'order_id' => 'DEBUG-' . time()
]);
?>