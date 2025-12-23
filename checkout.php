<?php
require_once __DIR__ . '/includes/init.php';
include __DIR__ . '/navbar.php';

// Только авторизованный пользователь
$user = $auth->user();
if (!$user) {
    redirect('login.php?return=' . urlencode(BASE_URL . '/checkout.php'));
}

$items = $cart->items();
$total = $cart->total();

$errors  = [];
$success = false;

if (is_post()) {
    if (!$items) {
        $errors[] = 'Корзина пуста.';
    }

    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $paymentMethod   = trim($_POST['payment_method'] ?? '');

    if ($shippingAddress === '') {
        $errors[] = 'Укажите адрес доставки.';
    }
    if ($paymentMethod === '') {
        $errors[] = 'Выберите способ оплаты.';
    }

    if (!$errors) {
        // Создание заказа
        $db->query(
            "INSERT INTO orders (user_id,total_amount,status,shipping_address,payment_method)
             VALUES (?,?,?,?,?)",
            [$user['id'], $total, 'created', $shippingAddress, $paymentMethod]
        );
        $orderId = (int)$db->lastInsertId();

        // Позиции заказа и списание остатков
        foreach ($items as $item) {
            $db->query(
                "INSERT INTO order_items (order_id,product_id,size_id,quantity,price)
                 VALUES (?,?,?,?,?)",
                [
                    $orderId,
                    $item['id'],
                    $item['size_id'],
                    $item['quantity'],
                    $item['price']
                ]
            );

            $db->query(
                "UPDATE product_sizes
                 SET quantity = quantity - ?
                 WHERE id = ? AND quantity >= ?",
                [$item['quantity'], $item['size_id'], $item['quantity']]
            );
        }

        // Очистка корзины
        $cart->clear();
        $success = true;
    }
}
?>

<section class="section">
    <div class="container section-header">
        <h2>Оформление заказа</h2>
    </div>
</section>

<section class="section">
    <div class="container" style="display:grid; grid-template-columns:minmax(0,1.3fr) minmax(0,0.9fr); gap:2rem;">
        <!-- Форма доставки/оплаты -->
        <div>
            <div class="form-card" style="margin:0;">
                <?php if ($errors): ?>
                    <div class="alert alert-error">
                        <?= h(implode(' ', $errors)) ?>
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success">
                        Заказ успешно оформлен! Историю заказов можно посмотреть в разделе «Заказы».
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                    <form method="post">
                        <h2>Данные доставки</h2>

                        <div class="form-group">
                            <label for="shipping_address">Адрес доставки</label>
                            <textarea id="shipping_address" name="shipping_address"
                                      class="form-control"
                                      style="border-radius:16px; min-height:80px; resize:vertical; padding-top:0.7rem; padding-bottom:0.7rem;"><?= h($_POST['shipping_address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Способ оплаты</label>
                            <select id="payment_method" name="payment_method" class="form-control">
                                <option value="">Выберите...</option>
                                <option value="card"   <?= (($_POST['payment_method'] ?? '') === 'card') ? 'selected' : '' ?>>Банковская карта</option>
                                <option value="cash"   <?= (($_POST['payment_method'] ?? '') === 'cash') ? 'selected' : '' ?>>Наличными курьеру</option>
                                <option value="online" <?= (($_POST['payment_method'] ?? '') === 'online') ? 'selected' : '' ?>>Онлайн-оплата</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.8rem;">
                            Подтвердить заказ
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Сводка заказа -->
        <div>
            <div class="form-card" style="margin:0;">
                <h2>Ваш заказ</h2>
                <?php if (!$items): ?>
                    <p class="form-help">Корзина пуста.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:0.7rem;">
                        <?php foreach ($items as $item): ?>
                            <div style="display:flex; justify-content:space-between; gap:0.7rem;">
                                <div>
                                    <div style="font-size:0.9rem;"><?= h($item['name']) ?></div>
                                    <div class="form-help">
                                        размер <?= h($item['size']) ?> · <?= (int)$item['quantity'] ?> шт.
                                    </div>
                                </div>
                                <div style="text-align:right; font-size:0.9rem;">
                                    <?= number_format($item['subtotal'], 2, ',', ' ') ?> ₽
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr style="border-color:rgba(148,163,184,0.3); margin:1rem 0;">
                    <div style="display:flex; justify-content:space-between; font-weight:600;">
                        <span>Итого к оплате</span>
                        <span><?= number_format($total, 2, ',', ' ') ?> ₽</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace. Все права защищены.</span>
        <span>Безопасное оформление заказа и списание со склада.</span>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/script.js"></script>
<script>
// Дополнительного JS для checkout не требуется — всё на PHP.
// Этот блок оставлен на будущее (валидаторы, маска телефона и т.п.).
</script>
</body>
</html>
