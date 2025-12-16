<?php
require_once 'includes/init.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
        $return = $_GET['return'] ?? 'index.php';
        redirectTo($return);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход – ShoeStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <a href="index.php">👟 ShoeStore</a>
        </div>
        <h1 class="auth-title">Вход в аккаунт</h1>
        <p className="auth-subtitle">Чтобы перейти к корзине и заказам, авторизуйтесь.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error); ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required
                       value="<?= h($_POST['email'] ?? ''); ?>" placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-full">Войти</button>
        </form>

        <div class="auth-footer">
            <span>Нет аккаунта?</span> <a href="register.php">Зарегистрироваться</a>
        </div>

        <div class="auth-demo">
            <p>Тестовые аккаунты:</p>
            <p>Admin: <code>admin@store.com</code> / <code>admin123</code></p>
            <p>User: <code>user@test.com</code> / <code>user123</code></p>
        </div>
    </div>
</div>
</body>
</html>
