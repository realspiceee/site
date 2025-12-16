<?php
require_once 'includes/init.php';

$user = requireLogin();
$message = '';
$error   = '';

// обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {
        $error = 'Имя и email обязательны.';
    } else {
        // проверка уникальности email
        $exists = $db->query(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$email, $user['id']]
        )->fetch();

        if ($exists) {
            $error = 'Такой email уже используется другим пользователем.';
        } else {
            $db->query(
                "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?",
                [$name, $email, $phone, $user['id']]
            );
            $message = 'Профиль обновлён.';
            // обновляем данные текущего пользователя
            $user = $db->query("SELECT * FROM users WHERE id = ?", [$user['id']])->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет – ShoeStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">👟 ShoeStore</a>
        <nav class="nav-links">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="cart.php">Корзина (<?= $cart->getCount(); ?>)</a>
            <a href="profile.php" class="active"><?= h($user['name']); ?></a>
            <a href="orders.php">Заказы</a>
            <a href="index.php?logout=1">Выход</a>
        </nav>
    </div>
</header>

<main class="container">
    <h1>Личный кабинет</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= h($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= h($error); ?></div>
    <?php endif; ?>

    <section class="profile-grid">
        <form method="post" class="profile-form">
            <h2>Профиль</h2>

            <div class="form-group">
                <label>Имя</label>
                <input type="text" name="name" value="<?= h($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= h($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Телефон</label>
                <input type="text" name="phone" value="<?= h($user['phone']); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        </form>

        <div class="profile-side">
            <h2>Информация</h2>
            <p><strong>Роль:</strong> <?= h($user['role']); ?></p>
            <p><strong>Дата регистрации:</strong> <?= h($user['created_at'] ?? ''); ?></p>
            <p><a href="orders.php" class="btn btn-secondary">История заказов</a></p>
        </div>
    </section>
</main>
</body>
</html>
