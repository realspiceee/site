<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/cart.php';
require_once '../includes/functions.php';

requireRole('manager');
$db = new Database();
$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - ShoeStore</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-brand">⚙️ Админ панель</div>
        <div>
            <span>👤 <?= h($user['name']) ?> (<?= $user['role'] ?>)</span>
            <a href="../index.php">В магазин</a>
            <a href="../?logout=1">Выйти</a>
        </div>
    </nav>

    <main class="container admin-dashboard">
        <h1>Панель управления</h1>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3>Товаров</h3>
                <div class="stat-number"><?= $db->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn() ?></div>
            </div>
            <div class="stat-card">
                <h3>Заказов</h3>
                <div class="stat-number"><?= $db->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?></div>
            </div>
            <div class="stat-card">
                <h3>Пользователей</h3>
                <div class="stat-number"><?= $db->query("SELECT COUNT(*) FROM users")->fetchColumn() ?></div>
            </div>
        </div>
        
        <div class="admin-actions">
            <?php if ($user['role'] == 'admin'): ?>
                <a href="users.php" class="btn">👥 Управление пользователями</a>
            <?php endif; ?>
            <a href="products.php" class="btn">📦 Управление товарами</a>
        </div>
    </main>
</body>
</html>
