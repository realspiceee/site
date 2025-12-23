<?php
require_once __DIR__ . '/includes/init.php';
include __DIR__ . '/navbar.php';
?>

<section class="hero">
    <div class="container hero-inner">
        <div class="hero-content">
            <h1>Премиальный магазин обуви</h1>
            <p>Минималистичный интерфейс, лучшие бренды и удобный заказ — как сайт за миллион рублей.</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary">Перейти в каталог</a>
        </div>
        <div class="hero-card">
            <span class="hero-label">Новинка</span>
            <h2>Nike Air Max 90</h2>
            <p>Культовая модель с обновлённым комфортом и премиальными материалами.</p>
            <span class="hero-price">от 129.99 ₽</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container section-header">
        <h2>Популярные модели</h2>
        <a href="<?= BASE_URL ?>/catalog.php" class="link-muted">Смотреть всё</a>
    </div>
    <div class="container products-grid">
        <?php
        $stmt = $db->query("
            SELECT p.*,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
            FROM products p
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT 6
        ");
        $products = $stmt->fetchAll();

        foreach ($products as $product):
            $img = $product['main_image'] ?: 'no-image.png';
        ?>
        <article class="product-card">
            <div class="product-image-wrap">
                <img src="<?= BASE_URL ?>/assets/images/<?= h($img) ?>" alt="<?= h($product['name']) ?>">
            </div>
            <div class="product-body">
                <h3><?= h($product['name']) ?></h3>
                <p class="product-meta">
                    <?= h($product['brand']) ?> · <?= h($product['category']) ?>
                </p>
                <p class="product-price">
                    <?= number_format($product['price'], 2, ',', ' ') ?> ₽
                </p>
                <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['id'] ?>" class="btn btn-sm btn-outline">
                    Подробнее
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace. Все права защищены.</span>
        <span>Разработка и дизайн под премиальный уровень.</span>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
