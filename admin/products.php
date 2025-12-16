<?php
require_once '../includes/init.php';
requireRole('manager');

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['id'] ?? 0);
    $data = [
        $_POST['name'],
        $_POST['description'],
        $_POST['category'],
        $_POST['brand'],
        (float)$_POST['price'],
        $_POST['color'],
        $_POST['material'],
        $_POST['status'] ?? 'active'
    ];

    if ($id > 0) {
        $data[] = $id;
        $db->query("
            UPDATE products
            SET name = ?, description = ?, category = ?, brand = ?, price = ?, color = ?, material = ?, status = ?
            WHERE id = ?
        ", $data);
    } else {
        $db->query("
            INSERT INTO products (name, description, category, brand, price, color, material, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", $data);
        $id = $db->query("SELECT last_insert_rowid()")->fetchColumn();

        // базовые размеры
        foreach ([36,37,38,39,40,41,42,43,44,45] as $size) {
            $db->query("INSERT INTO product_sizes (product_id, size, quantity) VALUES (?, ?, 0)", [$id, $size]);
        }
    }

    // загрузка главного изображения
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = 'assets/images/product_' . $id . '.' . $ext;
        $path = dirname(__DIR__) . '/' . $fileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $path);

        $db->query("UPDATE product_images SET is_main = 0 WHERE product_id = ?", [$id]);
        $db->query("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)", [$id, $fileName]);
    }

    header('Location: products.php');
    exit;
}

$products = $db->query("
    SELECT p.*, 
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS image_url
    FROM products p
    ORDER BY p.created_at DESC
")->fetchAll();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editProduct = $editId ? $db->query("SELECT * FROM products WHERE id = ?", [$editId])->fetch() : null;
$sizes = $editId ? $db->query("SELECT * FROM product_sizes WHERE product_id = ? ORDER BY size", [$editId])->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление товарами - Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="admin-nav">
    <div class="nav-brand">📦 Товары</div>
    <div>
        <a href="dashboard.php">Панель</a>
        <a href="../index.php">На сайт</a>
    </div>
</nav>

<main class="container">
    <h1>Товары</h1>

    <section class="admin-form-block">
        <h2><?= $editProduct ? 'Редактировать товар' : 'Добавить товар' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $editProduct['id'] ?? 0 ?>">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required value="<?= h($editProduct['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" rows="3"><?= h($editProduct['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Категория</label>
                <input type="text" name="category" value="<?= h($editProduct['category'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Бренд</label>
                <input type="text" name="brand" value="<?= h($editProduct['brand'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Цена</label>
                <input type="number" step="0.01" name="price" required value="<?= h($editProduct['price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Цвет</label>
                <input type="text" name="color" value="<?= h($editProduct['color'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Материал</label>
                <input type="text" name="material" value="<?= h($editProduct['material'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Статус</label>
                <select name="status">
                    <option value="active" <?= ($editProduct['status'] ?? '') == 'active' ? 'selected' : '' ?>>Активен</option>
                    <option value="hidden" <?= ($editProduct['status'] ?? '') == 'hidden' ? 'selected' : '' ?>>Скрыт</option>
                </select>
            </div>
            <div class="form-group">
                <label>Главное изображение</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>

        <?php if ($editProduct): ?>
            <h3>Остатки по размерам</h3>
            <table class="admin-table">
                <tr><th>Размер</th><th>Количество</th></tr>
                <?php foreach ($sizes as $s): ?>
                    <tr>
                        <td><?= h($s['size']) ?></td>
                        <td><?= h($s['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Список товаров</h2>
        <table class="admin-table">
            <tr>
                <th>ID</th><th>Фото</th><th>Название</th><th>Бренд</th><th>Цена</th><th>Статус</th><th></th>
            </tr>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><img src="../<?= $p['image_url'] ?: 'assets/images/no-image.png' ?>" style="height:40px"></td>
                    <td><?= h($p['name']) ?></td>
                    <td><?= h($p['brand']) ?></td>
                    <td><?= number_format($p['price'], 0, ',', ' ') ?> ₽</td>
                    <td><?= h($p['status']) ?></td>
                    <td><a href="products.php?edit=<?= $p['id'] ?>">Редактировать</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>
</body>
</html>
