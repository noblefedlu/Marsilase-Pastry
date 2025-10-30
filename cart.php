<?php
session_start();
header('Content-Type: application/json');

if ($_POST['action'] === 'add_to_cart') {
    try {
        $item = [
            'product_type' => $_POST['product_type'],
            'product_id' => $_POST['product_id'],
            'product_name' => $_POST['product_name'],
            'flavor' => $_POST['flavor'],
            'size' => $_POST['size'] ?? '',
            'size_label' => $_POST['size_label'] ?? '',
            'toppings' => $_POST['toppings'],
            'quantity' => intval($_POST['quantity']),
            'special_notes' => $_POST['special_notes'],
            'unit_price' => floatval($_POST['unit_price']),
            'total_price' => floatval($_POST['total_price'])
        ];
        
        // Validate required fields
        if (empty($item['product_type']) || empty($item['product_id']) || empty($item['product_name'])) {
            throw new Exception('Missing required product information');
        }
        
        if ($item['quantity'] < 1 || $item['quantity'] > 10) {
            throw new Exception('Invalid quantity');
        }
        
        if ($item['unit_price'] < 0 || $item['total_price'] < 0) {
            throw new Exception('Invalid price information');
        }
        
        $_SESSION['cart'][] = $item;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Item added to cart',
            'cart_count' => count($_SESSION['cart'])
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    
} elseif ($_POST['action'] === 'remove_from_cart') {
    try {
        $index = intval($_POST['index']);
        if (isset($_SESSION['cart'][$index])) {
            array_splice($_SESSION['cart'], $index, 1);
            echo json_encode([
                'success' => true, 
                'message' => 'Item removed from cart',
                'cart_count' => count($_SESSION['cart'])
            ]);
        } else {
            throw new Exception('Item not found in cart');
        }
    } catch (Exception $e) {
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