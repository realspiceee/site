<?php
header('Content-Type: application/json');
require_once '../includes/init.php';

$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Требуется авторизация']);
    exit;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    // если админ/менеджер – видит все заказы, иначе только свои
    if ($auth->hasRole('manager')) {
        $orders = $db->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
    } else {
        $orders = $db->query("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$user['id']])->fetchAll();
    }
    echo json_encode(['success' => true, 'orders' => $orders]);
} else {
    echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
}
