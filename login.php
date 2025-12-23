<?php
require_once __DIR__ . '/includes/init.php';

// Выход
if (isset($_GET['logout'])) {
    $auth->logout();
    redirect('index.php');
}

$errors = [];

if (is_post()) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = $auth->login($email, $password);
    if ($result['success']) {
        // куда вернуть пользователя после логина
        $return = $_GET['return'] ?? (BASE_URL . '/index.php');
        // если return без домена, добавим BASE_URL только один раз
        if (!str_starts_with($return, BASE_URL)) {
            $return = BASE_URL . '/' . ltrim($return, '/');
        }
        header('Location: ' . $return);
        exit;
    } else {
        $errors[] = $result['error'] ?? 'Ошибка входа.';
    }
}

include __DIR__ . '/navbar.php';
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1>Вход в аккаунт</h1>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?= h(implode(' ', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        class="form-control"
                        type="email"
                        id="email"
                        name="email"
                        value="<?= h($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input
                        class="form-control"
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.8rem;">
                    Войти
                </button>

                <p class="form-help" style="margin-top:0.8rem;">
                    Нет аккаунта? <a href="<?= BASE_URL ?>/register.php">Зарегистрироваться</a>
                </p>
            </form>
        </div>
    </div>
</section>

</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace.</span>
        <span>Безопасный вход с bcrypt.</span>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
