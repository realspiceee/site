<?php
require_once __DIR__ . '/../includes/init.php';

// Только менеджер/админ
$auth->requireRole(['admin','manager']);
$user = $auth->user();

include __DIR__ . '/../navbar.php';

// Статистика
$stats = [
    'users'   => (int)$db->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'],
    'orders'  => (int)$db->query("SELECT COUNT(*) AS c FROM orders")->fetch()['c'],
    'revenue' => (float)($db->query("SELECT SUM(total_amount) AS s FROM orders WHERE status IN ('paid','processing','shipped','delivered')")->fetch()['s'] ?? 0),
    'products'=> (int)$db->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'],
];
$latestOrders = $db->query("
    SELECT o.*, u.email
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<section class="section">
    <div class="container section-header">
        <h2>Админ-панель</h2>
        <span class="form-help">Роль: <?= h($user['role']) ?></span>
    </div>
</section>

<section class="section">
    <div class="container" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem;">
        <article class="product-card">
            <h3>Пользователи</h3>
            <p class="product-price"><?= $stats['users'] ?></p>
            <p class="form-help">Всего зарегистрированных аккаунтов.</p>
        </article>
        <article class="product-card">
            <h3>Товары</h3>
            <p class="product-price"><?= $stats['products'] ?></p>
            <p class="form-help">Активные и скрытые позиции.</p>
        </article>
        <article class="product-card">
            <h3>Заказы</h3>
            <p class="product-price"><?= $stats['orders'] ?></p>
            <p class="form-help">Общее количество заказов.</p>
        </article>
        <article class="product-card">
            <h3>Выручка</h3>
            <p class="product-price">
                <?= number_format($stats['revenue'], 2, ',', ' ') ?> ₽
            </p>
            <p class="form-help">Оплаченные и обработанные заказы.</p>
        </article>
    </div>
</section>

<section class="section">
    <div class="container">
        <h3 style="margin-bottom:0.8rem;">Последние заказы</h3>
        <?php if (!$latestOrders): ?>
            <p class="form-help">Пока нет заказов.</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:0.7rem;">
                <?php foreach ($latestOrders as $order): ?>
                    <article class="product-card">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-size:0.9rem; font-weight:600;">
                                    Заказ #<?= (int)$order['id'] ?>
                                </div>
                                <div class="form-help">
                                    <?= h($order['created_at']) ?> · <?= h($order['email']) ?>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:0.85rem;">Статус</div>
                                <div style="font-size:0.9rem;"><?= h($order['status']) ?></div>
                                <div style="font-weight:600; margin-top:0.2rem;">
                                    <?= number_format($order['total_amount'], 2, ',', ' ') ?> ₽
                                </div>
                            </div>
                        </div>
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
        <span>Дашборд администратора и менеджера.</span>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
