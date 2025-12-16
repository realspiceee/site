<?php
require_once 'includes/init.php';

$user = requireLogin();
$cart = new Cart();
$cartData = $cart->getItems();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - ShoeStore</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container">
        <h1>🛒 Корзина покупок</h1>
        
        <?php if (empty($cartData['items'])): ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста</p>
                <a href="index.php" class="btn">Продолжить покупки</a>
            </div>
        <?php else: ?>
            <div class="cart-table">
                <?php foreach ($cartData['items'] as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="<?= $item['image_url'] ?: 'assets/images/no-image.png' ?>" alt="<?= h($item['name']) ?>">
                    </div>
                    <div class="cart-item-details">
                        <h3><a href="product.php?id=<?= $item['product_id'] ?>"><?= h($item['name']) ?></a></h3>
                        <p>Размер: <?= $item['size'] ?> | Бренд: <?= h($item['brand']) ?></p>
                    </div>
                    <div class="cart-item-quantity">
                        <input type="number" value="<?= $item['quantity'] ?>" min="1" 
                               data-item-id="<?= $item['id'] ?>" class="qty-input">
                    </div>
                    <div class="cart-item-price"><?= number_format($item['price'], 0, ',', ' ') ?> ₽</div>
                    <div class="cart-item-total"><?= number_format($item['total_price'], 0, ',', ' ') ?> ₽</div>
                    <button class="btn btn-danger remove-item" data-item-id="<?= $item['id'] ?>">Удалить</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="total-section">
                    <h2>Итого: <?= number_format($cartData['total'], 0, ',', ' ') ?> ₽</h2>
                    <a href="checkout.php" class="btn btn-primary btn-large">Оформить заказ</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="assets/script.js"></script>
</body>
</html>
