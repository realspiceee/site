<?php
require_once 'includes/init.php';

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where  = "WHERE p.status = 'active'";
$params = [];

// фильтры
if (!empty($_GET['category'])) {
    $where .= " AND p.category = ?";
    $params[] = $_GET['category'];
}
if (!empty($_GET['brand'])) {
    $where .= " AND p.brand LIKE ?";
    $params[] = '%' . $_GET['brand'] . '%';
}
if (!empty($_GET['min_price'])) {
    $where .= " AND p.price >= ?";
    $params[] = (float)$_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $where .= " AND p.price <= ?";
    $params[] = (float)$_GET['max_price'];
}
if (!empty($_GET['search'])) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}

// сортировка
$orderBy = 'p.created_at DESC';
if (!empty($_GET['sort'])) {
    if ($_GET['sort'] === 'price_asc')  $orderBy = 'p.price ASC';
    if ($_GET['sort'] === 'price_desc') $orderBy = 'p.price DESC';
}

$total = $db->query("SELECT COUNT(*) FROM products p $where", $params)->fetchColumn();
$totalPages = max(1, ceil($total / $limit));

array_push($params, $limit, $offset);
$products = $db->query("
    SELECT p.*, pi.image_url,
           (SELECT SUM(quantity) FROM product_sizes ps WHERE ps.product_id = p.id) AS total_stock
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
    $where
    ORDER BY $orderBy
    LIMIT ? OFFSET ?
", $params)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог – ShoeStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="site-body">
<header class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">👟 ShoeStore</a>
        <nav class="nav-links">
            <a href="index.php">Главная</a>
            <a href="catalog.php" class="active">Каталог</a>
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
    <div class="page-header">
        <h1>Каталог обуви</h1>
        <p>Подбери пару по фильтрам: категория, бренд, цена и поиск по названию.</p>
    </div>

    <section class="filters-card">
        <form method="get" class="filters-grid">
            <div class="filter-field">
                <label>Категория</label>
                <select name="category">
                    <option value="">Все</option>
                    <option value="мужская" <?= ($_GET['category'] ?? '')=='мужская'?'selected':''; ?>>Мужская</option>
                    <option value="женская" <?= ($_GET['category'] ?? '')=='женская'?'selected':''; ?>>Женская</option>
                    <option value="детская" <?= ($_GET['category'] ?? '')=='детская'?'selected':''; ?>>Детская</option>
                </select>
            </div>
            <div class="filter-field">
                <label>Бренд</label>
                <input type="text" name="brand" value="<?= h($_GET['brand'] ?? ''); ?>" placeholder="Nike, Adidas…">
            </div>
            <div class="filter-field">
                <label>Цена от</label>
                <input type="number" name="min_price" value="<?= h($_GET['min_price'] ?? ''); ?>">
            </div>
            <div class="filter-field">
                <label>Цена до</label>
                <input type="number" name="max_price" value="<?= h($_GET['max_price'] ?? ''); ?>">
            </div>
            <div class="filter-field">
                <label>Поиск</label>
                <input type="text" name="search" value="<?= h($_GET['search'] ?? ''); ?>" placeholder="Название или описание">
            </div>
            <div class="filter-field">
                <label>Сортировка</label>
                <select name="sort">
                    <option value="">По умолчанию</option>
                    <option value="price_asc"  <?= ($_GET['sort'] ?? '')=='price_asc'?'selected':''; ?>>Цена ↑</option>
                    <option value="price_desc" <?= ($_GET['sort'] ?? '')=='price_desc'?'selected':''; ?>>Цена ↓</option>
                </select>
            </div>
            <div class="filters-actions">
                <button type="submit" class="btn btn-primary">Применить</button>
                <a href="catalog.php" class="btn btn-secondary">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="products-section">
        <?php if (!$products): ?>
            <p>По заданным параметрам ничего не найдено.</p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                    <article class="product-card">
                        <a href="product.php?id=<?= $p['id']; ?>" class="product-link">
                            <div class="product-image">
                                <img src="<?= $p['image_url'] ?: 'assets/images/no-image.png'; ?>"
                                     alt="<?= h($p['name']); ?>">
                                <?php if ((int)$p['total_stock'] === 0): ?>
                                    <span class="badge">Нет в наличии</span>
                                <?php endif; ?>
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
            </div>
        <?php endif; ?>
    </section>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $qs = $_GET; $qs['page'] = $i; ?>
                <a href="?<?= http_build_query($qs); ?>"
                   class="page-link <?= $i == $page ? 'active' : ''; ?>"><?= $i; ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</main>
</body>
</html>
