<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$db = new Database();
$auth = new Auth();
$user = $auth->getCurrentUser();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Товар не найден');
}

$product = $db->query("SELECT * FROM products WHERE id = ?", [$id])->fetch();
$sizes = $db->query("SELECT * FROM product_sizes WHERE product_id = ? ORDER BY size", [$id])->fetchAll();
$images = $db->query("SELECT * FROM product_images WHERE product_id = ?", [$id])->fetchAll();

if (!$product) {
    die('Товар не найден');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($product['name']) ?> - ShoeStore</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container">
        <div class="product-detail">
            <div class="product-gallery">
                <?php foreach ($images as $img): ?>
                    <img src="<?= $img['image_url'] ?>" alt="Фото <?= h($product['name']) ?>" 
                         class="<?= $img['is_main'] ? 'main-image' : 'thumb-image' ?>">
                <?php endforeach; ?>
            </div>
            
            <div class="product-info">
                <h1><?= h($product['name']) ?></h1>
                <div class="price-big"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</div>
                
                <div class="product-meta">
                    <p><strong>Бренд:</strong> <?= h($product['brand']) ?></p>
                    <p><strong>Категория:</strong> <?= h($product['category']) ?></p>
                    <p><strong>Цвет:</strong> <?= h($product['color']) ?></p>
                    <p><strong>Материал:</strong> <?= h($product['material']) ?></p>
                </div>
                
                <form class="add-to-cart-form" method="POST">
                    <div class="size-selector">
                        <label>Размер:</label>
                        <select name="size" id="size-select" required>
                            <option value="">Выберите размер</option>
                            <?php foreach ($sizes as $size): ?>
                                <option value="<?= $size['size'] ?>" 
                                    data-stock="<?= $size['quantity'] ?>"
                                    <?= $size['quantity'] == 0 ? 'disabled' : '' ?>>
                                    <?= $size['size'] ?> EU (<?= $size['quantity'] ?> шт)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="quantity-selector">
                        <label>Количество:</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $sizes[0]['quantity'] ?? 1 ?>">
                    </div>
                    <button type="submit" class="btn btn-primary add-to-cart" data-product-id="<?= $id ?>">
                        🛒 Добавить в корзину
                    </button>
                </form>
                
                <div class="description">
                    <h3>Описание</h3>
                    <p><?= nl2br(h($product['description'])) ?></p>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/script.js"></script>
</body>
</html>
