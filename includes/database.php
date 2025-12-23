<?php
// includes/database.php

class Database {
    private PDO $pdo;

    public function __construct()
    {
        $dsn = 'sqlite:' . DB_PATH;
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->initSchema();
    }

    private function initSchema(): void
    {
        $this->pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            name TEXT NOT NULL,
            phone TEXT,
            role TEXT CHECK(role IN ('admin','manager','user')) DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_blocked INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            category TEXT,
            brand TEXT,
            price REAL NOT NULL,
            color TEXT,
            material TEXT,
            status TEXT CHECK(status IN ('active','hidden')) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS product_sizes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            size REAL NOT NULL,
            quantity INTEGER DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS product_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            image_url TEXT NOT NULL,
            is_main INTEGER DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS carts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            session_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS cart_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cart_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            size_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'created',
            shipping_address TEXT,
            payment_method TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            size_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );
        ");

        // начальный админ
      // начальный админ
    $hasAdmin = $this->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'] ?? 0;
    if ($hasAdmin == 0) {
        $passAdmin   = password_hash('admin123', PASSWORD_BCRYPT);
        $passManager = password_hash('manager123', PASSWORD_BCRYPT);
        $passUser    = password_hash('user123', PASSWORD_BCRYPT);

        $this->query(
            "INSERT INTO users (email,password,name,role) VALUES (?,?,?,?)",
            ['admin@store.com', $passAdmin, 'Администратор', 'admin']
        );
        $this->query(
            "INSERT INTO users (email,password,name,role) VALUES (?,?,?,?)",
            ['manager@store.com', $passManager, 'Менеджер', 'manager']
        );
        $this->query(
            "INSERT INTO users (email,password,name,role) VALUES (?,?,?,?)",
            ['user@test.com', $passUser, 'Покупатель', 'user']
        );
    }


        // начальные товары
        $hasProducts = $this->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'] ?? 0;
        if ($hasProducts == 0) {
            $this->seedProducts();
        }
    }

    /**
     * Первоначальное заполнение БД товарами и картинками
     * Картинки должны лежать в assets/images/ с указанными именами (.png)
     */
    private function seedProducts(): void
    {
        $products = [
            [
                'Nike Air Max 90',
                'Легендарные кроссовки на каждый день.',
                'Мужская обувь', 'Nike', 129.99, 'Чёрный', 'Кожа'
            ],
            [
                'Adidas Ultraboost',
                'Премиальные беговые кроссовки с максимальным комфортом.',
                'Мужская обувь', 'Adidas', 189.99, 'Белый', 'Текстиль'
            ],
            [
                'Timberland Classic',
                'Зимние ботинки премиум-класса для города и природы.',
                'Мужская обувь', 'Timberland', 209.99, 'Коричневый', 'Нубук'
            ]
        ];

        foreach ($products as $p) {
            $this->query(
                "INSERT INTO products (name,description,category,brand,price,color,material) VALUES (?,?,?,?,?,?,?)",
                $p
            );
            $productId = (int)$this->lastInsertId();

            // размеры
            foreach ([40, 41, 42, 43, 44] as $size) {
                $this->query(
                    "INSERT INTO product_sizes (product_id,size,quantity) VALUES (?,?,?)",
                    [$productId, $size, 10]
                );
            }

            // главное изображение (.png)
            $mainImage = match ($p[0]) {
                'Nike Air Max 90'   => 'nike_airmax_90.png',
                'Adidas Ultraboost' => 'adidas_ultraboost.png',
                default             => 'timberland_classic.png'
            };

            $this->query(
                "INSERT INTO product_images (product_id,image_url,is_main) VALUES (?,?,1)",
                [$productId, $mainImage]
            );
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
