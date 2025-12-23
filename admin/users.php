<?php
require_once __DIR__ . '/../includes/init.php';

// Только админ
$auth->requireRole(['admin']);

$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// Блокировка / разблокировка
if ($action === 'toggle_block' && $id > 0) {
    $u = $db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();
    if ($u) {
        $new = $u['is_blocked'] ? 0 : 1;
        $db->query("UPDATE users SET is_blocked = ? WHERE id = ?", [$new, $id]);
    }
    redirect('users.php');
}

// Изменение роли
if ($action === 'change_role' && $id > 0 && isset($_GET['role'])) {
    $role = $_GET['role'];
    if (in_array($role, ['admin','manager','user'], true)) {
        $db->query("UPDATE users SET role = ? WHERE id = ?", [$role, $id]);
    }
    redirect('users.php');
}

// Список пользователей
$roleFilter = $_GET['role'] ?? '';

$sql = "SELECT * FROM users";
$params = [];
if ($roleFilter && in_array($roleFilter, ['admin','manager','user'], true)) {
    $sql    .= " WHERE role = ?";
    $params[] = $roleFilter;
}
$sql .= " ORDER BY created_at DESC";

$users = $db->query($sql, $params)->fetchAll();

include __DIR__ . '/../navbar.php';
?>

<section class="section">
    <div class="container section-header">
        <h2>Пользователи</h2>
        <form method="get" style="margin:0;">
            <select name="role" class="form-control" onchange="this.form.submit();" style="min-width:160px;">
                <option value="">Все роли</option>
                <option value="admin"   <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Администраторы</option>
                <option value="manager" <?= $roleFilter === 'manager' ? 'selected' : '' ?>>Менеджеры</option>
                <option value="user"    <?= $roleFilter === 'user' ? 'selected' : '' ?>>Пользователи</option>
            </select>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (!$users): ?>
            <p class="form-help">Пользователи не найдены.</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:0.7rem;">
                <?php foreach ($users as $u): ?>
                    <article class="product-card">
                        <div style="display:flex; justify-content:space-between; gap:1rem;">
                            <div>
                                <div style="font-size:0.95rem; font-weight:600;">
                                    <?= h($u['name']) ?>
                                    <?php if ($u['is_blocked']): ?>
                                        <span style="font-size:0.75rem; color:#fecaca;">(заблокирован)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-help">
                                    <?= h($u['email']) ?> · <?= h($u['phone'] ?? '') ?>
                                </div>
                                <div class="form-help">
                                    Создан: <?= h($u['created_at']) ?>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div class="form-help">Роль</div>
                                <div style="margin-bottom:0.4rem;">
                                    <strong><?= h($u['role']) ?></strong>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:0.3rem; align-items:flex-end;">
                                    <div>
                                        <a href="users.php?action=change_role&id=<?= (int)$u['id'] ?>&role=admin"
                                           class="btn btn-sm btn-outline">Admin</a>
                                        <a href="users.php?action=change_role&id=<?= (int)$u['id'] ?>&role=manager"
                                           class="btn btn-sm btn-outline">Manager</a>
                                        <a href="users.php?action=change_role&id=<?= (int)$u['id'] ?>&role=user"
                                           class="btn btn-sm btn-outline">User</a>
                                    </div>
                                    <a href="users.php?action=toggle_block&id=<?= (int)$u['id'] ?>"
                                       class="btn btn-sm btn-outline">
                                        <?= $u['is_blocked'] ? 'Разблокировать' : 'Заблокировать' ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> ShoeSpace.</span>
        <span>Управление пользователями и ролями (только админ).</span>
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/script.js"></script>
</body>
</html>
