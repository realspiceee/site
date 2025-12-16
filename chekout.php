<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$user = requireLogin();
$cart = new Cart();
$cartData = $cart->getItems();

if (empty($cartData['items'])) {
    redirect('cart.php');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - ShoeStore</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container">
        <h1>📦 Оформление заказа</h1>
        
        <div class="checkout-grid">
            <div class="checkout-order">
                <h2>Ваши товары</h2>
                <?php foreach ($cartData['items'] as $item): ?>
                <div class="checkout-item">
                    <span><?= h($item['name']) ?> (<?= $item['size'] ?>)</span>
                    <span>×<?= $item['quantity'] ?> = <?= number_format($item['total_price'], 0) ?> ₽</span>
                </div>
                <?php endforeach; ?>
                <div class="checkout-total">
                    <strong>Итого: <?= number_format($cartData['total'], 0) ?> ₽</strong>
                </div>
            </div>
            
            <form method="POST" class="checkout-form">
                <h2>Доставка</h2>
                <div class="form-group">
                    <label>Способ доставки:</label>
                    <select name="delivery" required>
                        <option value="courier">Курьер (500 ₽)</option>
                        <option value="pickup">Самовывоз (бесплатно)</option>
                    </select>
                </div>
                
                <h2>Адрес</h2>
                <div class="form-group">
                    <input type="text" name="address" placeholder="Улица, дом, квартира" required>
                </div>
                
                <h2>Оплата</h2>
                <div class="form-group">
                    <label>Способ оплаты:</label>
                    <select name="payment" required>
                        <option value="card">Картой онлайн</option>
                        <option value="cash">Наличными курьеру</option>
                    </select>
                </div>
                
                <button type="submit" name="create_order" class="btn btn-success btn-large">
                    Подтвердить заказ
                </button>
            </form>
        </div>
    </main>

    <?php
    if (isset($_POST['create_order'])) {
        // Создание заказа
        $db = new Database();
        $total = $cartData['total'];
        $address = $_POST['address'];
        $payment = $_POST['payment'];
        
        $db->query("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)", 
                  [$user['id'], $total, $address, $payment]);
        $orderId = $db->query("SELECT last_insert_rowid()")->fetchColumn();
        
        // Перенос товаров из корзины в заказ
        $cartId = $cart->getCartId();
        $items = $db->query("SELECT * FROM cart_items WHERE cart_id = ?", [$cartId])->fetchAll();
        foreach ($items as $item) {
            $db->query("INSERT INTO order_items (order_id, product_id, size_id, quantity, price) VALUES (?, ?, ?, ?, ?)", 
                      [$orderId, $item['product_id'], $item['size_id'], $item['quantity'], $cartData['items'][0]['price']]);
            
            // Списание со склада
            $db->query("UPDATE product_sizes SET quantity = quantity - ? WHERE id = ?", 
                      [$item['quantity'], $item['size_id']]);
        }
        
        // Очистка корзины
        $cart->clear();
        
        echo "<script>alert('Заказ #' . $orderId . ' успешно создан!'); window.location='orders.php';</script>";
    }
    ?>

    <script src="assets/script.js"></script>
</body>
</html>
