<?php
require_once 'includes/init.php';

// 8 свежих товаров для главной
$products = $db->query("
    SELECT p.*, pi.image_url
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
    WHERE p.status = 'active'
    ORDER BY p.created_at DESC
    LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ShoeStore – Магазин обуви</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="site-body">
<header class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">👟 ShoeStore</a>
        <nav class="nav-links">
            <a href="index.php" class="active">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="cart.php">Корзина (<?= $cart->getCount(); ?>)</a>
            <?php if ($user): ?>
                <a href="profile.php"><?= h($user['name']); ?></a>
                <a href="orders.php">Заказы</a>
                <a href="index.php?logout=1">Выход</a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="page-wrapper">
    <section class="hero">
        <div class="hero-text">
            <h1>Твой цифровой магазин обуви</h1>
            <p>Кроссовки, ботинки и кеды от топ‑брендов. Удобный каталог, быстрая доставка и аккуратный личный кабинет.</p>
            <div class="hero-actions">
                <a href="catalog.php" class="btn btn-primary">Перейти в каталог</a>
                <a href="#popular" class="btn btn-secondary">Популярные пары</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/no-image.png" alt="Обувь">
        </div>
    </section>

    <section id="popular" class="products-section">
        <div class="section-header">
            <h2>Популярные товары</h2>
            <a href="catalog.php" class="link-more">Смотреть весь каталог →</a>
        </div>

        <div class="products-grid">
            <?php foreach ($products as $p): ?>
                <article class="product-card">
                    <a href="product.php?id=<?= $p['id']; ?>" class="product-link">
                        <div class="product-image">
                            <img src="<?= $p['image_url'] ?: 'assets/images/no-image.png'; ?>"
                                 alt="<?= h($p['name']); ?>">
                        </div>
                        <div class="product-body">
                            <h3><?= h($p['name']); ?></h3>
                            <p class="product-meta">
                                <?= h($p['brand']); ?>
                                <?php if (!empty($p['category'])): ?>
                                    • <?= h($p['category']); ?>
                                <?php endif; ?>
                            </p>
                            <p class="product-price"><?= number_format($p['price'], 0, ',', ' '); ?> ₽</p>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
            <?php if (!$products): ?>
                <p>Пока нет товаров для отображения.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
if (isset($_GET['logout'])) {
    $auth->logout();
    redirectTo('index.php');
}
?>
</body>
</html>
