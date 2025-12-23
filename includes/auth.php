<?php
// includes/auth.php

class Auth {
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Текущий пользователь или null
     */
    public function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $stmt = $this->db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $user = $stmt->fetch();

        // Если пользователя нет или он заблокирован — выходим из аккаунта
        if (!$user || (int)$user['is_blocked'] === 1) {
            $this->logout();
            return null;
        }

        return $user;
    }

    /**
     * Регистрация нового пользователя
     */
    public function register(string $name, string $email, string $phone, string $password, string $passwordConfirm): array
    {
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный email.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Пароли не совпадают.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Пароль должен быть не менее 6 символов.';
        }

        // Проверка уникальности email
        $exists = $this->db->query("SELECT id FROM users WHERE email = ?", [$email])->fetch();
        if ($exists) {
            $errors[] = 'Пользователь с таким email уже существует.';
        }

        if ($name === '') {
            $errors[] = 'Имя не может быть пустым.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->query(
            "INSERT INTO users (email,password,name,phone,role) VALUES (?,?,?,?,?)",
            [$email, $hash, $name, $phone, 'user']
        );

        return ['success' => true];
    }

    /**
     * Авторизация по email и паролю
     */
    public function login(string $email, string $password): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Неверный email или пароль.'];
        }

        if ((int)$user['is_blocked'] === 1) {
            return ['success' => false, 'error' => 'Аккаунт заблокирован администратором.'];
        }

        $_SESSION['user_id'] = $user['id'];

        return ['success' => true, 'user' => $user];
    }

    /**
     * Выход из аккаунта
     */
    public function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    /**
     * Требование роли (или списка ролей).
     * При отсутствии доступа — редирект на login.php.
     */
    public function requireRole(array $roles): void
    {
        $user = $this->user();
        if (!$user || !in_array($user['role'], $roles, true)) {
            redirect('login.php?return=' . urlencode(current_url()));
        }
    }
}
