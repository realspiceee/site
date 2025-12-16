<?php
require_once 'database.php';
require_once 'auth.php';

class Cart {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
    }
    
    public function getCartId() {
        $user = $this->auth->getCurrentUser();
        $sessionId = session_id();
        
        $where = $user ? 'user_id = ?' : 'session_id = ?';
        $param = $user ? [$user['id']] : [$sessionId];
        
        $cart = $this->db->query(
            "SELECT id FROM carts WHERE $where ORDER BY id DESC LIMIT 1", 
            $param
        )->fetch();
        
        if (!$cart) {
            $this->db->query(
                "INSERT INTO carts (user_id, session_id) VALUES (?, ?)",
                [$user['id'] ?? null, $sessionId]
            );
            return $this->db->query("SELECT last_insert_rowid()")->fetchColumn();
        }
        return $cart['id'];
    }
    
    public function addItem($productId, $size, $quantity = 1) {
        $cartId = $this->getCartId();
        
        $sizeData = $this->db->query(
            "SELECT id, quantity FROM product_sizes WHERE product_id = ? AND size = ? AND quantity >= ?",
            [$productId, $size, $quantity]
        )->fetch();
        
        if (!$sizeData) {
            throw new Exception('Выбранный размер недоступен или недостаточно товара на складе');
        }
        
        $sizeId = $sizeData['id'];
        
        $existing = $this->db->query(
            "SELECT id, quantity FROM cart_items 
             WHERE cart_id = ? AND product_id = ? AND size_id = ?",
            [$cartId, $productId, $sizeId]
        )->fetch();
        
        if ($existing) {
            $newQuantity = $existing['quantity'] + $quantity;
            if ($newQuantity > $sizeData['quantity']) {
                throw new Exception('Недостаточно товара на складе');
            }
            $this->db->query(
                "UPDATE cart_items SET quantity = ? WHERE id = ?",
                [$newQuantity, $existing['id']]
            );
        } else {
            $this->db->query(
                "INSERT INTO cart_items (cart_id, product_id, size_id, quantity) VALUES (?, ?, ?, ?)",
                [$cartId, $productId, $sizeId, $quantity]
            );
        }
    }
    
    public function getItems() {
        $cartId = $this->getCartId();
        $items = $this->db->query("
            SELECT ci.id, ci.product_id, ci.size_id, ci.quantity, 
                   ps.size, p.name, p.price, p.brand, pi.image_url
            FROM cart_items ci
            JOIN product_sizes ps ON ci.size_id = ps.id
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
            WHERE ci.cart_id = ?
            ORDER BY p.name
        ", [$cartId])->fetchAll();
        
        $total = 0;
        foreach ($items as &$item) {
            $item['total_price'] = $item['price'] * $item['quantity'];
            $total += $item['total_price'];
        }
        
        return ['items' => $items, 'total' => $total, 'count' => count($items)];
    }
    
    public function updateQuantity($itemId, $quantity) {
        if ($quantity <= 0) {
            $this->removeItem($itemId);
            return;
        }
        
        $this->db->query("UPDATE cart_items SET quantity = ? WHERE id = ?", [$quantity, $itemId]);
    }
    
    public function removeItem($itemId) {
        $this->db->query("DELETE FROM cart_items WHERE id = ?", [$itemId]);
    }
    
    public function clear() {
        $cartId = $this->getCartId();
        $this->db->query("DELETE FROM cart_items WHERE cart_id = ?", [$cartId]);
    }
    
    public function getCount() {
        $cartId = $this->getCartId();
        return $this->db->query(
            "SELECT SUM(quantity) FROM cart_items WHERE cart_id = ?", [$cartId]
        )->fetchColumn() ?: 0;
    }
}
?>
