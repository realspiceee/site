<?php
// api/cart.php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$action    = $data['action']    ?? '';
$productId = (int)($data['product_id'] ?? 0);
$sizeId    = (int)($data['size_id'] ?? 0);
$qty       = (int)($data['quantity'] ?? 1);

try {
    switch ($action) {
        case 'add':
            if ($productId <= 0 || $sizeId <= 0) {
                throw new Exception('Некорректные данные товара.');
            }
            $cart->add($productId, $sizeId, max(1, $qty));
            break;

        case 'update':
            if ($productId <= 0 || $sizeId <= 0) {
                throw new Exception('Некорректные данные товара.');
            }
            $cart->update($productId, $sizeId, max(0, $qty));
            break;

        case 'remove':
            if ($productId <= 0 || $sizeId <= 0) {
                throw new Exception('Некорректные данные товара.');
            }
            $cart->update($productId, $sizeId, 0);
            break;

        case 'clear':
            $cart->clear();
            break;

        default:
            throw new Exception('Неизвестное действие.');
    }

    echo json_encode([
        'success'    => true,
        'cart_count' => getCartCount(),
        'total'      => $cart->total(),
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ]);
}
