<?php
require_once __DIR__ . '/includes/init.php';

$errors  = [];
$success = false;

if (is_post()) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if ($name === '') {
        $errors[] = 'Укажите имя.';
    }

    $result = $auth->register($name, $email, $phone, $password, $confirm);
    if (!$result['success']) {
        $errors = array_merge($errors, $result['errors']);
    } else {
        $success = true;
    }
}

include __DIR__ . '/navbar.php';
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1>Регистрация</h1>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?= h(implode(' ', $errors)) ?>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    Аккаунт создан. Теперь вы можете <a href="login.php">войти</a>.
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="post">
                    <div class="form-group">
                        <label for="name">Имя</label>
                        <input class="form-control" type="text" id="name" name="name"
                               value="<?= h($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="form-control" type="email" id="email" name="email"
                               value="<?= h($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <input class="form-control" type="text" id="phone" name="phone"
                               value="<?= h($_POST['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input class="form-control" type="password" id="password" name="password" required>
                        <p class="form-help">Минимум 6 символов.</p>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Подтверждение пароля</label>
                        <input class="form-control" type="password" id="password_confirm" name="password_confirm" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.8rem;">
                        Создать аккаунт
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace.</span>
        <span>Регистрация с валидацией и bcrypt.</span>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
