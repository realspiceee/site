<?php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($name, $email, $phone, $password) {
        if ($this->findUserByEmail($email)) {
            throw new Exception('Пользователь с таким email уже существует');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Пароль должен содержать минимум 6 символов');
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->db->query(
            "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')",
            [$name, $email, $phone, $hashedPassword]
        );
        return true;
    }
    
    public function login($email, $password) {
        $user = $this->findUserByEmail($email);
        if (!$user || !password_verify($password, $user['password']) || $user['is_blocked']) {
            throw new Exception('Неверный email или пароль');
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        return $user;
    }
    
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) return null;
        
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            $this->logout();
            return null;
        }
        
        $_SESSION['last_activity'] = time();
        return $this->db->query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']])->fetch();
    }
    
    public function logout() {
        session_destroy();
        session_start();
    }
    
    public function hasRole($requiredRole) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        $roleWeights = ['admin' => 3, 'manager' => 2, 'user' => 1];
        return ($roleWeights[$user['role']] ?? 0) >= ($roleWeights[$requiredRole] ?? 0);
    }
    
    public function findUserByEmail($email) {
        return $this->db->query("SELECT * FROM users WHERE email = ?", [$email])->fetch();
    }
    
    public function findUserById($id) {
        return $this->db->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();
    }
}
?>
