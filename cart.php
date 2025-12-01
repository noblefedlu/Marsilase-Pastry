<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_to_cart') {
        $product_type = $_POST['product_type'] ?? '';
        $product_id = $_POST['product_id'] ?? '';
        $product_name = $_POST['product_name'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 1);
        $unit_price = floatval($_POST['unit_price'] ?? 0);
        $total_price = floatval($_POST['total_price'] ?? 0);
        $image = $_POST['image'] ?? '';
        
        // Validate required fields
        if (empty($product_type) || empty($product_id) || empty($product_name) || $unit_price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product data']);
            exit;
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $cart_item_id = $product_type . '_' . $product_id;
        
        // Check if item already exists in cart
        if (isset($_SESSION['cart'][$cart_item_id])) {
            // Update quantity if item exists
            $_SESSION['cart'][$cart_item_id]['quantity'] += $quantity;
            $_SESSION['cart'][$cart_item_id]['total_price'] = $_SESSION['cart'][$cart_item_id]['quantity'] * $unit_price;
        } else {
            // Add new item to cart
            $_SESSION['cart'][$cart_item_id] = [
                'cart_item_id' => $cart_item_id,
                'product_type' => $product_type,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'total_price' => $total_price,
                'image' => $image,
                'added_at' => date('Y-m-d H:i:s')
            ];
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added to cart', 
            'cart_count' => count($_SESSION['cart']),
            'item_name' => $product_name
        ]);
        exit;
    }
    
    if ($action === 'clear_cart') {
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>