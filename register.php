<?php
require_once 'includes/init.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if ($name === '' || $email === '' || $pass1 === '') {
        $error = 'Заполните все обязательные поля.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Пароли не совпадают.';
    } else {
        try {
            $auth->register($name, $email, $phone, $pass1);
            redirectTo('login.php');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация – ShoeStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <a href="index.php">👟 ShoeStore</a>
        </div>
        <h1 class="auth-title">Создать аккаунт</h1>
        <p class="auth-subtitle">Сохраняйте корзину и отслеживайте свои заказы.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error); ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <div class="form-group">
                <label>Имя</label>
                <input type="text" name="name" required value="<?= h($_POST['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= h($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Телефон (необязательно)</label>
                <input type="text" name="phone" value="<?= h($_POST['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Повтор пароля</label>
                <input type="password" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Зарегистрироваться</button>
        </form>

        <div class="auth-footer">
            <span>Уже есть аккаунт?</span> <a href="login.php">Войти</a>
        </div>
    </div>
</div>
</body>
</html>
