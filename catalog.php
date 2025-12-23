<?php
require_once __DIR__ . '/includes/init.php';
include __DIR__ . '/navbar.php';

// Параметры фильтров из GET
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 12;
$offset    = ($page - 1) * $perPage;

$category  = trim($_GET['category'] ?? '');
$brand     = trim($_GET['brand'] ?? '');
$color     = trim($_GET['color'] ?? '');
$size      = trim($_GET['size'] ?? '');
$priceFrom = (float)($_GET['price_from'] ?? 0);
$priceTo   = (float)($_GET['price_to'] ?? 0);
$search    = trim($_GET['q'] ?? '');
$sort      = $_GET['sort'] ?? 'new';

// Формирование WHERE
$where  = ["p.status = 'active'"];
$params = [];

if ($category !== '') {
    $where[]  = 'p.category = ?';
    $params[] = $category;
}
if ($brand !== '') {
    $where[]  = 'p.brand = ?';
    $params[] = $brand;
}
if ($color !== '') {
    $where[]  = 'p.color = ?';
    $params[] = $color;
}
if ($size !== '') {
    $where[]  = 'EXISTS (SELECT 1 FROM product_sizes s WHERE s.product_id = p.id AND s.size = ? AND s.quantity > 0)';
    $params[] = $size;
}
if ($priceFrom > 0) {
    $where[]  = 'p.price >= ?';
    $params[] = $priceFrom;
}
if ($priceTo > 0) {
    $where[]  = 'p.price <= ?';
    $params[] = $priceTo;
}
if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Сортировка
$orderBy = 'p.created_at DESC';
if ($sort === 'price_asc') {
    $orderBy = 'p.price ASC';
} elseif ($sort === 'price_desc') {
    $orderBy = 'p.price DESC';
} elseif ($sort === 'popular') {
    $orderBy = 'p.created_at DESC'; // упрощенно
}

// Подсчёт общего количества
$countSql = "SELECT COUNT(*) AS c FROM products p {$whereSql}";
$total    = (int)$db->query($countSql, $params)->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $perPage));

// Получение товаров
$listSql = "
    SELECT p.*,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
    FROM products p
    {$whereSql}
    ORDER BY {$orderBy}
    LIMIT {$perPage} OFFSET {$offset}
";
$products = $db->query($listSql, $params)->fetchAll();

// Для фильтров получим уникальные бренды/категории/цвета/размеры
$allBrands    = $db->query("SELECT DISTINCT brand FROM products WHERE status = 'active' AND brand IS NOT NULL ORDER BY brand")->fetchAll();
$allCategories= $db->query("SELECT DISTINCT category FROM products WHERE status = 'active' AND category IS NOT NULL ORDER BY category")->fetchAll();
$allColors    = $db->query("SELECT DISTINCT color FROM products WHERE status = 'active' AND color IS NOT NULL ORDER BY color")->fetchAll();
$allSizes     = $db->query("SELECT DISTINCT size FROM product_sizes ORDER BY size")->fetchAll();

function selected($a, $b) {
    return (string)$a === (string)$b ? 'selected' : '';
}
?>

<section class="section">
    <div class="container section-header">
        <h2>Каталог обуви</h2>
    </div>
</section>

<section class="section">
    <div class="container" style="display:flex; gap:1.5rem; align-items:flex-start;">
        <!-- Фильтры -->
        <aside style="flex:0 0 260px; max-width:260px;">
            <form method="get" class="form-card" style="margin:0;">
                <h2>Фильтры</h2>

                <div class="form-group">
                    <label for="q">Поиск</label>
                    <input type="text" class="form-control" id="q" name="q"
                           value="<?= h($search) ?>" placeholder="Название или описание">
                </div>

                <div class="form-group">
                    <label>Категория</label>
                    <select name="category" class="form-control">
                        <option value="">Все</option>
                        <?php foreach ($allCategories as $row): ?>
                            <option value="<?= h($row['category']) ?>" <?= selected($category, $row['category']) ?>>
                                <?= h($row['category']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Бренд</label>
                    <select name="brand" class="form-control">
                        <option value="">Все</option>
                        <?php foreach ($allBrands as $row): ?>
                            <option value="<?= h($row['brand']) ?>" <?= selected($brand, $row['brand']) ?>>
                                <?= h($row['brand']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Цвет</label>
                    <select name="color" class="form-control">
                        <option value="">Все</option>
                        <?php foreach ($allColors as $row): ?>
                            <option value="<?= h($row['color']) ?>" <?= selected($color, $row['color']) ?>>
                                <?= h($row['color']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Размер</label>
                    <select name="size" class="form-control">
                        <option value="">Все</option>
                        <?php foreach ($allSizes as $row): ?>
                            <option value="<?= h($row['size']) ?>" <?= selected($size, $row['size']) ?>>
                                <?= h($row['size']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="display:flex; gap:0.5rem;">
                    <div style="flex:1;">
                        <label>Цена от</label>
                        <input type="number" step="0.01" min="0" name="price_from"
                               class="form-control" value="<?= $priceFrom ?: '' ?>">
                    </div>
                    <div style="flex:1;">
                        <label>до</label>
                        <input type="number" step="0.01" min="0" name="price_to"
                               class="form-control" value="<?= $priceTo ?: '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Сортировка</label>
                    <select name="sort" class="form-control">
                        <option value="new" <?= selected($sort, 'new') ?>>Сначала новые</option>
                        <option value="price_asc" <?= selected($sort, 'price_asc') ?>>Цена по возрастанию</option>
                        <option value="price_desc" <?= selected($sort, 'price_desc') ?>>Цена по убыванию</option>
                        <option value="popular" <?= selected($sort, 'popular') ?>>Популярность</option>
                    </select>
                </div>

                <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">Показать</button>
                    <a href="catalog.php" class="btn btn-outline" style="flex:1; text-align:center;">Сбросить</a>
                </div>
            </form>
        </aside>

        <!-- Список товаров -->
        <div style="flex:1;">
            <?php if ($total === 0): ?>
                <p class="form-help">По выбранным фильтрам товаров не найдено.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product):
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
                            <a href="product.php?id=<?= (int)$product['id'] ?>" class="btn btn-sm btn-outline">
                                Подробнее
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <!-- Пагинация -->
                <?php if ($totalPages > 1): ?>
                    <div style="margin-top:1.5rem; display:flex; justify-content:center; gap:0.4rem; flex-wrap:wrap;">
                        <?php for ($p = 1; $p <= $totalPages; $p++): 
                            $q = $_GET;
                            $q['page'] = $p;
                            $url = 'catalog.php?' . http_build_query($q);
                            $isCurrent = $p === $page;
                        ?>
                            <a href="<?= h($url) ?>"
                               class="btn btn-sm <?= $isCurrent ? 'btn-primary' : 'btn-outline' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace. Все права защищены.</span>
        <span>Каталог оптимизирован под фильтры и скорость.</span>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
