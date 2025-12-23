<?php
require_once __DIR__ . '/includes/init.php';
include __DIR__ . '/navbar.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('catalog.php');
}

// Получаем товар
$product = $db->query("
    SELECT p.*,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
    FROM products p
    WHERE p.id = ?
", [$id])->fetch();

if (!$product || $product['status'] !== 'active') {
    redirect('catalog.php');
}

// Все изображения
$images = $db->query("
    SELECT * FROM product_images
    WHERE product_id = ?
    ORDER BY is_main DESC, id ASC
", [$id])->fetchAll();

// Размеры с остатками
$sizes = $db->query("
    SELECT * FROM product_sizes
    WHERE product_id = ? AND quantity > 0
    ORDER BY size
", [$id])->fetchAll();
?>

<section class="section">
    <div class="container" style="display:grid; grid-template-columns:minmax(0,1.1fr) minmax(0,1fr); gap:2rem;">
        <!-- Галерея -->
        <div>
            <div class="product-image-wrap" style="border-radius:24px;">
                <?php
                $mainImg = $product['main_image'] ?: 'no-image.png';
                ?>
                <img id="product-main-image"
                     src="<?= BASE_URL ?>/assets/images/<?= h($mainImg) ?>"
                     alt="<?= h($product['name']) ?>">
            </div>
            <?php if (count($images) > 1): ?>
                <div style="display:flex; gap:0.6rem; margin-top:0.7rem; flex-wrap:wrap;">
                    <?php foreach ($images as $img): ?>
                        <button type="button"
                                class="thumb-btn"
                                data-image="<?= BASE_URL ?>/assets/images/<?= h($img['image_url']) ?>"
                                style="border:none; padding:0; background:transparent;">
                            <div class="product-image-wrap"
                                 style="width:80px; height:80px; border-radius:14px; overflow:hidden;">
                                <img src="<?= BASE_URL ?>/assets/images/<?= h($img['image_url']) ?>"
                                     alt="" style="height:100%; object-fit:cover;">
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Информация о товаре -->
        <div>
            <h1 style="margin-top:0; margin-bottom:0.4rem;"><?= h($product['name']) ?></h1>
            <p class="product-meta" style="margin-bottom:0.6rem;">
                <?= h($product['brand']) ?> · <?= h($product['category']) ?> · <?= h($product['color']) ?>
            </p>
            <p class="product-price" style="font-size:1.4rem; margin-bottom:1rem;">
                <?= number_format($product['price'], 2, ',', ' ') ?> ₽
            </p>

            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.2rem;">
                <?= nl2br(h($product['description'])) ?>
            </p>

            <form id="product-add-form" onsubmit="return false;">
                <div class="form-group">
                    <label>Размер</label>
                    <?php if ($sizes): ?>
                        <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                            <?php foreach ($sizes as $size): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline size-option"
                                        data-size-id="<?= (int)$size['id'] ?>">
                                    <?= h($size['size']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="selected-size-id">
                    <?php else: ?>
                        <p class="form-help">Нет доступных размеров.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="margin-top:1rem; display:flex; gap:0.7rem; align-items:center;">
                    <div>
                        <label for="qty">Количество</label>
                        <input type="number" id="qty" class="form-control" value="1" min="1" style="max-width:100px;">
                    </div>

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="btn-add-to-cart"
                        data-add-to-cart="1"
                        data-product-id="<?= (int)$product['id'] ?>"
                        data-size-id=""
                        data-quantity="1"
                        data-qty-input="qty">
                        Добавить в корзину
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace. Все права защищены.</span>
        <span>Продуманная карточка товара с размерами.</span>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/script.js"></script>
<script>
// Переключение изображения
document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('product-main-image');
    document.querySelectorAll('.thumb-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const src = btn.getAttribute('data-image');
            if (src && mainImg) mainImg.src = src;
        });
    });

    // Выбор размера
    const sizeButtons = document.querySelectorAll('.size-option');
    const hiddenSize  = document.getElementById('selected-size-id');
    const addBtn      = document.getElementById('btn-add-to-cart');

    sizeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeButtons.forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline');
            });
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-primary');

            const sizeId = btn.getAttribute('data-size-id');
            if (hiddenSize) hiddenSize.value = sizeId;
            if (addBtn) addBtn.setAttribute('data-size-id', sizeId);
        });
    });
});
</script>
</body>
</html>
