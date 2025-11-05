<?php
// cart.php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle different actions
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_to_cart':
            addToCart();
            break;
        case 'get_cart':
            getCart();
            break;
        case 'update_cart':
            updateCart();
            break;
        case 'remove_from_cart':
            removeFromCart();
            break;
        case 'clear_cart':
            clearCart();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function addToCart() {
    // Validate required fields
    $required_fields = ['product_type', 'product_id', 'product_name', 'quantity', 'unit_price', 'total_price'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Generate unique cart item ID
    $cart_item_id = uniqid('item_');
    
    // Prepare cart item
    $cart_item = [
        'cart_item_id' => $cart_item_id,
        'product_type' => $_POST['product_type'],
        'product_id' => $_POST['product_id'],
        'product_name' => $_POST['product_name'],
        'flavor' => $_POST['flavor'] ?? 'Custom',
        'size' => $_POST['size'] ?? 'Standard',
        'quantity' => intval($_POST['quantity']),
        'special_notes' => $_POST['special_notes'] ?? '',
        'unit_price' => floatval($_POST['unit_price']),
        'total_price' => floatval($_POST['total_price']),
        'added_at' => date('Y-m-d H:i:s')
    ];

    // Add to cart
    $_SESSION['cart'][$cart_item_id] = $cart_item;

    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'cart_count' => count($_SESSION['cart']),
        'cart_item' => $cart_item
    ]);
}

function getCart() {
    $cart_items = $_SESSION['cart'] ?? [];
    $total_items = 0;
    $total_price = 0;

    foreach ($cart_items as $item) {
        $total_items += $item['quantity'];
        $total_price += $item['total_price'];
    }

    echo json_encode([
        'success' => true,
        'cart_items' => array_values($cart_items),
        'total_items' => $total_items,
        'total_price' => $total_price
    ]);
}

function updateCart() {
    $cart_item_id = $_POST['cart_item_id'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);

    if (empty($cart_item_id) || !isset($_SESSION['cart'][$cart_item_id])) {
        throw new Exception('Cart item not found');
    }

    if ($quantity <= 0) {
        // Remove item if quantity is 0 or less
        unset($_SESSION['cart'][$cart_item_id]);
    } else {
        // Update quantity and total price
        $_SESSION['cart'][$cart_item_id]['quantity'] = $quantity;
        $_SESSION['cart'][$cart_item_id]['total_price'] = $quantity * $_SESSION['cart'][$cart_item_id]['unit_price'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully'
    ]);
}

function removeFromCart() {
    $cart_item_id = $_POST['cart_item_id'] ?? '';

    if (empty($cart_item_id) || !isset($_SESSION['cart'][$cart_item_id])) {
        throw new Exception('Cart item not found');
    }

    unset($_SESSION['cart'][$cart_item_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart'
    ]);
}

function clearCart() {
    $_SESSION['cart'] = [];
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared successfully'
    ]);
}
?>