<?php
require_once 'includes/functions.php';
$user = getCurrentUser();
$cartCount = getCartCount();
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">👟 ShoeStore</a>
        
        <div class="nav-search">
            <form method="GET" action="index.php">
                <input type="text" name="search" placeholder="Поиск товаров..." value="<?= h($_GET['search'] ?? '') ?>">
                <button type="submit">🔍</button>
            </form>
        </div>
        
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="cart.php">🛒 <?= $cartCount ?></a>
            
            <?php if ($user): ?>
                <a href="profile.php">👤 <?= h($user['name']) ?></a>
                <a href="orders.php">Заказы</a>
                <?php if ($user['role'] == 'admin' || $user['role'] == 'manager'): ?>
                    <a href="admin/dashboard.php" class="admin-link">⚙️ Админка</a>
                <?php endif; ?>
                <a href="index.php?logout=1">Выйти</a>
            <?php else: ?>
                <a href="login.php">Войти</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
