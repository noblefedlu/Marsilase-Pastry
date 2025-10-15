<?php
session_start();
header('Content-Type: application/json');

if ($_POST['action'] === 'add_to_cart') {
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
    
    $_SESSION['cart'][] = $item;
    
    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    
} elseif ($_POST['action'] === 'remove_from_cart') {
    $index = intval($_POST['index']);
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>