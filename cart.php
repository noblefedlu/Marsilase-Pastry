
<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST['action'] === 'add_to_cart') {
    try {
        $item = [
            'product_type' => $_POST['product_type'] ?? '',
            'product_id' => $_POST['product_id'] ?? '',
            'product_name' => $_POST['product_name'] ?? '',
            'flavor' => $_POST['flavor'] ?? '',
            'size' => $_POST['size'] ?? '',
            'toppings' => $_POST['toppings'] ?? '[]',
            'quantity' => intval($_POST['quantity'] ?? 1),
            'special_notes' => $_POST['special_notes'] ?? '',
            'unit_price' => floatval($_POST['unit_price'] ?? 0),
            'total_price' => floatval($_POST['total_price'] ?? 0)
        ];
        
        // Validate required fields
        if (empty($item['product_type']) || empty($item['product_id']) || empty($item['product_name'])) {
            throw new Exception('Missing required product information');
        }
        
        if ($item['quantity'] < 1 || $item['quantity'] > 10) {
            throw new Exception('Invalid quantity. Please select between 1 and 10 items.');
        }
        
        if ($item['unit_price'] < 0 || $item['total_price'] < 0) {
            throw new Exception('Invalid price information');
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add item to cart
        $_SESSION['cart'][] = $item;
        
        // Calculate total cart count
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $cart_item) {
            $cart_count += $cart_item['quantity'];
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Item added to cart successfully!',
            'cart_count' => $cart_count,
            'cart_total' => count($_SESSION['cart'])
        ]);
        
    } catch (Exception $e) {
        error_log('Cart add error: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    
} elseif ($_POST['action'] === 'remove_from_cart') {
    try {
        $index = intval($_POST['index'] ?? -1);
        
        if (!isset($_SESSION['cart'][$index])) {
            throw new Exception('Item not found in cart');
        }
        
        // Remove item from cart
        array_splice($_SESSION['cart'], $index, 1);
        
        // Calculate total cart count
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $cart_item) {
            $cart_count += $cart_item['quantity'];
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Item removed from cart',
            'cart_count' => $cart_count,
            'cart_total' => count($_SESSION['cart'])
        ]);
        
    } catch (Exception $e) {
        error_log('Cart remove error: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    
} elseif ($_POST['action'] === 'clear_cart') {
    try {
        $_SESSION['cart'] = [];
        echo json_encode([
            'success' => true, 
            'message' => 'Cart cleared successfully',
            'cart_count' => 0,
            'cart_total' => 0
        ]);
        
    } catch (Exception $e) {
        error_log('Cart clear error: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid action'
    ]);
}
?>