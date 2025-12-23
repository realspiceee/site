<?php
// navbar.php
require_once __DIR__ . '/includes/init.php';

// текущий пользователь
$user = $auth->user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= h(SITE_TITLE) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= BASE_URL ?>/index.php" class="logo">
            <span class="logo-mark">S</span>
            <span class="logo-text">ShoeSpace</span>
        </a>

        <div class="header-actions">
            <!-- Кнопка Каталог -->
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-outline">
                Каталог
            </a>

            <!-- Кнопка Заказы -->
            <a href="<?= BASE_URL ?>/orders.php" class="btn btn-outline">
                Заказы
            </a>

            <!-- Кнопка Корзина -->
            <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline" style="padding-left:0.8rem; padding-right:0.8rem;">
                <span class="icon-cart" style="margin-right:0.4rem;"></span>
                <span>Корзина</span>
                <span class="cart-count" id="cart-count"><?= getCartCount(); ?></span>
            </a>

            <?php if ($user): ?>
                <!-- Выпадающее меню пользователя -->
                <div class="user-menu">
                    <span class="user-name"><?= h($user['name']) ?></span>
                    <div class="user-menu-dropdown">
                        <a href="<?= BASE_URL ?>/profile.php">Профиль</a>
                        <a href="<?= BASE_URL ?>/orders.php">Мои заказы</a>
                        <a href="<?= BASE_URL ?>/login.php?logout=1">Выйти</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- ВХОД -->
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline">
                    Войти
                </a>
                <!-- РЕГИСТРАЦИЯ -->
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">
                    Регистрация
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main">
