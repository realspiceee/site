<?php
require_once __DIR__ . '/includes/init.php';

// Только авторизованный
$user = $auth->user();
if (!$user) {
    redirect('login.php?return=' . urlencode('profile.php'));
}

$errors  = [];
$success = false;

if (is_post()) {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $errors[] = 'Имя не может быть пустым.';
    }

    if (!$errors) {
        $db->query(
            "UPDATE users SET name = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$name, $phone, $user['id']]
        );
        $success = true;
        // обновим данные пользователя
        $user = $auth->user();
    }
}

include __DIR__ . '/navbar.php';
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1>Личный кабинет</h1>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?= h(implode(' ', $errors)) ?>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    Профиль обновлён.
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Email (логин)</label>
                    <input class="form-control" type="email" value="<?= h($user['email']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="name">Имя</label>
                    <input class="form-control" type="text" id="name" name="name"
                           value="<?= h($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input class="form-control" type="text" id="phone" name="phone"
                           value="<?= h($user['phone'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.8rem;">
                    Сохранить
                </button>
            </form>

            <p class="form-help" style="margin-top:1rem;">
                Роль: <?= h($user['role']) ?>
            </p>
        </div>
    </div>
</section>

</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace.</span>
        <span>Управление профилем пользователя.</span>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
