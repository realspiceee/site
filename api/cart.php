<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../includes/config.php';
require_once '../includes/cart.php';

try {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_GET;
    $cart = new Cart();
    
    switch ($input['action'] ?? '') {
        case 'add':
            $cart->addItem($input['product_id'], $input['size'], $input['quantity'] ?? 1);
            echo json_encode([
                'success' => true,
                'count' => $cart->getCount(),
                'message' => 'Товар добавлен в корзину'
            ]);
            break;
            
        case 'update':
            $cart->updateQuantity($input['item_id'], $input['quantity']);
            echo json_encode(['success' => true]);
            break;
            
        case 'remove':
            $cart->removeItem($input['item_id']);
            echo json_encode(['success' => true]);
            break;
            
        case 'get':
            $data = $cart->getItems();
            echo json_encode($data);
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
