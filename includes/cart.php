<?php
// includes/cart.php

class Cart {
    private Database $db;
    private Auth $auth;

    public function __construct(Database $db, Auth $auth)
    {
        $this->db   = $db;
        $this->auth = $auth;
        $this->ensureSessionCart();
    }

    private function ensureSessionCart(): void
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'items' => [] // ключ: "productId-sizeId" => quantity
            ];
        }
    }

    public function add(int $productId, int $sizeId, int $qty = 1): void
    {
        $this->ensureSessionCart();
        $key = $productId . '-' . $sizeId;
        if (!isset($_SESSION['cart']['items'][$key])) {
            $_SESSION['cart']['items'][$key] = 0;
        }
        $_SESSION['cart']['items'][$key] += max(1, $qty);
    }

    public function update(int $productId, int $sizeId, int $qty): void
    {
        $this->ensureSessionCart();
        $key = $productId . '-' . $sizeId;
        if ($qty <= 0) {
            unset($_SESSION['cart']['items'][$key]);
        } else {
            $_SESSION['cart']['items'][$key] = $qty;
        }
    }

    public function clear(): void
    {
        $_SESSION['cart'] = ['items' => []];
    }

    /**
     * Детализированный список товаров в корзине
     */
    public function items(): array
    {
        $this->ensureSessionCart();
        if (empty($_SESSION['cart']['items'])) {
            return [];
        }

        $result = [];
        foreach ($_SESSION['cart']['items'] as $key => $qty) {
            [$productId, $sizeId] = array_map('intval', explode('-', $key));

            $product = $this->db->query("
                SELECT p.*,
                       s.size,
                       s.id AS size_id,
                       (SELECT image_url
                          FROM product_images
                         WHERE product_id = p.id AND is_main = 1
                         LIMIT 1) AS main_image
                FROM products p
                JOIN product_sizes s ON s.product_id = p.id
                WHERE p.id = ? AND s.id = ?
            ", [$productId, $sizeId])->fetch();

            if ($product) {
                $product['quantity'] = $qty;
                $product['subtotal'] = $qty * (float)$product['price'];
                $result[] = $product;
            }
        }
        return $result;
    }

    /**
     * Общая сумма по корзине
     */
    public function total(): float
    {
        $sum = 0;
        foreach ($this->items() as $item) {
            $sum += $item['subtotal'];
        }
        return $sum;
    }
}
