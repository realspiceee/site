<?php
require_once __DIR__ . '/includes/init.php';

// Только авторизованный
$user = $auth->user();
if (!$user) {
    redirect('login.php?return=' . urlencode('orders.php'));
}

include __DIR__ . '/navbar.php';

// Если админ или менеджер — видит все заказы, иначе только свои
if (in_array($user['role'], ['admin','manager'], true)) {
    $orders = $db->query("
        SELECT o.*, u.email, u.name
        FROM orders o
        JOIN users u ON u.id = o.user_id
        ORDER BY o.created_at DESC
    ")->fetchAll();
} else {
    $orders = $db->query("
        SELECT o.*, u.email, u.name
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ", [$user['id']])->fetchAll();
}
?>

<section class="section">
    <div class="container section-header">
        <h2><?= in_array($user['role'], ['admin','manager'], true) ? 'Все заказы' : 'Мои заказы' ?></h2>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (!$orders): ?>
            <p class="form-help">Заказов пока нет.</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1rem;">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $items = $db->query("
                        SELECT oi.*, p.name, s.size
                        FROM order_items oi
                        JOIN products p ON p.id = oi.product_id
                        JOIN product_sizes s ON s.id = oi.size_id
                        WHERE oi.order_id = ?
                    ", [$order['id']])->fetchAll();
                    ?>
                    <article class="product-card">
                        <div style="display:flex; justify-content:space-between; gap:1rem;">
                            <div>
                                <div style="font-size:0.9rem; font-weight:600;">
                                    Заказ #<?= (int)$order['id'] ?>
                                </div>
                                <div class="form-help">
                                    от <?= h($order['created_at']) ?> · статус: <?= h($order['status']) ?>
                                </div>
                                <?php if (in_array($user['role'], ['admin','manager'], true)): ?>
                                    <div class="form-help">
                                        Клиент: <?= h($order['name']) ?> (<?= h($order['email']) ?>)
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:0.9rem;">Сумма</div>
                                <div style="font-weight:600; font-size:1.1rem;">
                                    <?= number_format($order['total_amount'], 2, ',', ' ') ?> ₽
                                </div>
                            </div>
                        </div>

                        <?php if ($items): ?>
                            <hr style="border-color:rgba(148,163,184,0.3); margin:0.7rem 0;">
                            <div style="display:flex; flex-direction:column; gap:0.4rem;">
                                <?php foreach ($items as $item): ?>
                                    <div style="display:flex; justify-content:space-between; font-size:0.85rem;">
                                        <span>
                                            <?= h($item['name']) ?> · размер <?= h($item['size']) ?>
                                            · <?= (int)$item['quantity'] ?> шт.
                                        </span>
                                        <span>
                                            <?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?> ₽
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace.</span>
        <span>История заказов для пользователя и админов.</span>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
