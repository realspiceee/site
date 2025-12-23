<?php
require_once __DIR__ . '/../includes/init.php';

// Менеджер и админ
$auth->requireRole(['admin','manager']);

$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// Удаление товара
if ($action === 'delete' && $id > 0) {
    $db->query("DELETE FROM products WHERE id = ?", [$id]);
    redirect('products.php');
}

// Создание/редактирование
$errors  = [];
$success = false;
$product = [
    'name'        => '',
    'description' => '',
    'category'    => '',
    'brand'       => '',
    'price'       => '',
    'color'       => '',
    'material'    => '',
    'status'      => 'active',
];

if ($id > 0) {
    $product = $db->query("SELECT * FROM products WHERE id = ?", [$id])->fetch() ?: $product;
}

// Сохранение товара
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['name']        = trim($_POST['name'] ?? '');
    $product['description'] = trim($_POST['description'] ?? '');
    $product['category']    = trim($_POST['category'] ?? '');
    $product['brand']       = trim($_POST['brand'] ?? '');
    $product['price']       = (float)($_POST['price'] ?? 0);
    $product['color']       = trim($_POST['color'] ?? '');
    $product['material']    = trim($_POST['material'] ?? '');
    $product['status']      = $_POST['status'] ?? 'active';

    if ($product['name'] === '') {
        $errors[] = 'Название обязательно.';
    }
    if ($product['price'] <= 0) {
        $errors[] = 'Цена должна быть больше нуля.';
    }

    if (!$errors) {
        if ($id > 0) {
            $db->query("
                UPDATE products
                SET name = ?, description = ?, category = ?, brand = ?, price = ?, color = ?, material = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ", [
                $product['name'], $product['description'], $product['category'], $product['brand'],
                $product['price'], $product['color'], $product['material'], $product['status'], $id
            ]);
        } else {
            $db->query("
                INSERT INTO products (name,description,category,brand,price,color,material,status)
                VALUES (?,?,?,?,?,?,?,?)
            ", [
                $product['name'], $product['description'], $product['category'], $product['brand'],
                $product['price'], $product['color'], $product['material'], $product['status']
            ]);
            $id = (int)$db->lastInsertId();
        }
        $success = true;
    }
}

// Размеры
$sizes = [];
if ($id > 0) {
    $sizes = $db->query("SELECT * FROM product_sizes WHERE product_id = ? ORDER BY size", [$id])->fetchAll();
}

// Добавление/обновление размеров
if (isset($_POST['sizes']) && $id > 0) {
    // простая логика: чистим и вставляем заново
    $db->query("DELETE FROM product_sizes WHERE product_id = ?", [$id]);
    foreach ($_POST['sizes'] as $row) {
        $size = (float)($row['size'] ?? 0);
        $qty  = (int)($row['quantity'] ?? 0);
        if ($size > 0 && $qty >= 0) {
            $db->query("INSERT INTO product_sizes (product_id,size,quantity) VALUES (?,?,?)", [$id, $size, $qty]);
        }
    }
    $sizes = $db->query("SELECT * FROM product_sizes WHERE product_id = ? ORDER BY size", [$id])->fetchAll();
}

// Список товаров для таблицы
$products = $db->query("
    SELECT p.*,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
    FROM products p
    ORDER BY p.created_at DESC
")->fetchAll();

include __DIR__ . '/../navbar.php';
?>

<section class="section">
    <div class="container section-header">
        <h2>Товары</h2>
        <a href="products.php" class="link-muted">Список</a>
    </div>
</section>

<section class="section">
    <div class="container" style="display:grid; grid-template-columns:minmax(0,1.4fr) minmax(0,1fr); gap:2rem;">
        <!-- Форма редактирования -->
        <div>
            <div class="form-card" style="margin:0;">
                <h2><?= $id ? 'Редактировать товар' : 'Новый товар' ?></h2>

                <?php if ($errors): ?>
                    <div class="alert alert-error"><?= h(implode(' ', $errors)) ?></div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success">Товар сохранён.</div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Название</label>
                        <input class="form-control" name="name" value="<?= h($product['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Описание</label>
                        <textarea class="form-control" name="description" style="border-radius:16px; min-height:80px;"><?= h($product['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Категория</label>
                        <input class="form-control" name="category" value="<?= h($product['category']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Бренд</label>
                        <input class="form-control" name="brand" value="<?= h($product['brand']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Цена</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="price"
                               value="<?= h($product['price']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Цвет</label>
                        <input class="form-control" name="color" value="<?= h($product['color']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Материал</label>
                        <input class="form-control" name="material" value="<?= h($product['material']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Статус</label>
                        <select class="form-control" name="status">
                            <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Активен</option>
                            <option value="hidden" <?= $product['status'] === 'hidden' ? 'selected' : '' ?>>Скрыт</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.8rem;">
                        Сохранить
                    </button>
                </form>

                <?php if ($id): ?>
                    <hr style="border-color:rgba(148,163,184,0.3); margin:1.2rem 0;">
                    <h3>Размеры и остатки</h3>
                    <form method="post">
                        <div id="sizes-container" style="display:flex; flex-direction:column; gap:0.5rem;">
                            <?php if ($sizes): ?>
                                <?php foreach ($sizes as $row): ?>
                                    <div style="display:flex; gap:0.5rem;">
                                        <input type="number" step="0.5" min="0" name="sizes[][size]"
                                               class="form-control" style="max-width:90px;"
                                               value="<?= h($row['size']) ?>" placeholder="Размер">
                                        <input type="number" min="0" name="sizes[][quantity]"
                                               class="form-control" style="max-width:120px;"
                                               value="<?= h($row['quantity']) ?>" placeholder="Кол-во">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="display:flex; gap:0.5rem;">
                                    <input type="number" step="0.5" min="0" name="sizes[][size]"
                                           class="form-control" style="max-width:90px;" placeholder="Размер">
                                    <input type="number" min="0" name="sizes[][quantity]"
                                           class="form-control" style="max-width:120px;" placeholder="Кол-во">
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" style="margin-top:0.7rem;"
                                onclick="addSizeRow()">Добавить размер</button>
                        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:0.7rem;">
                            Сохранить размеры
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Таблица товаров -->
        <div>
            <div class="form-card" style="margin:0;">
                <h2>Список товаров</h2>
                <?php if (!$products): ?>
                    <p class="form-help">Товаров пока нет.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:0.6rem;">
                        <?php foreach ($products as $p): ?>
                            <article class="product-card">
                                <div style="display:flex; gap:0.8rem;">
                                    <div class="product-image-wrap" style="max-width:80px; height:80px;">
                                        <img src="<?= BASE_URL ?>/assets/images/<?= h($p['main_image'] ?: 'no-image.png') ?>"
                                             alt="<?= h($p['name']) ?>" style="height:100%; object-fit:cover;">
                                    </div>
                                    <div style="flex:1;">
                                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                            <div>
                                                <h3><?= h($p['name']) ?></h3>
                                                <p class="product-meta">
                                                    <?= h($p['brand']) ?> · <?= h($p['category']) ?>
                                                </p>
                                            </div>
                                            <div style="text-align:right;">
                                                <div class="product-price">
                                                    <?= number_format($p['price'], 2, ',', ' ') ?> ₽
                                                </div>
                                                <div class="form-help">
                                                    <?= h($p['status']) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                                            <a href="products.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline">
                                                Редактировать
                                            </a>
                                            <a href="products.php?action=delete&id=<?= (int)$p['id'] ?>"
                                               class="btn btn-sm btn-outline"
                                               onclick="return confirm('Удалить товар?');">
                                                Удалить
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace.</span>
        <span>Управление товарами и размерами.</span>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/script.js"></script>
<script>
function addSizeRow() {
    const container = document.getElementById('sizes-container');
    const row = document.createElement('div');
    row.style.display = 'flex';
    row.style.gap = '0.5rem';
    row.innerHTML = `
        <input type="number" step="0.5" min="0" name="sizes[][size]"
               class="form-control" style="max-width:90px;" placeholder="Размер">
        <input type="number" min="0" name="sizes[][quantity]"
               class="form-control" style="max-width:120px;" placeholder="Кол-во">
    `;
    container.appendChild(row);
}
</script>
</body>
</html>
