<?php
define('DB_PATH', __DIR__ . '/../database.db');
define('UPLOAD_DIR', __DIR__ . '/../assets/images/');
define('SESSION_TIMEOUT', 3600);

session_start();

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Инициализация БД
require_once 'database.php';
$db = new Database();

// Тестовые пользователи
$adminCount = $db->query("SELECT COUNT(*) FROM users WHERE email = 'admin@store.com'")->fetchColumn();
if ($adminCount == 0) {
    $db->query("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)", [
        'admin@store.com', password_hash('admin123', PASSWORD_BCRYPT), 'Администратор', 'admin'
    ]);
    $db->query("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)", [
        'manager@store.com', password_hash('manager123', PASSWORD_BCRYPT), 'Менеджер', 'manager'
    ]);
    $db->query("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)", [
        'user@test.com', password_hash('user123', PASSWORD_BCRYPT), 'Тестовый Пользователь', 'user'
    ]);
}

// Тестовые товары
$productCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
if ($productCount == 0) {
    $products = [
        ['Кроссовки Nike Air Max 90', 'Комфортные кроссовки', 'мужская', 'Nike', 5990, 'Черный', 'Кожа, текстиль'],
        ['Ботинки Timberland', 'Зимние ботинки', 'мужская', 'Timberland', 12990, 'Коричневый', 'Кожа'],
        ['Туфли Adidas Stan Smith', 'Классические кроссовки', 'женская', 'Adidas', 6990, 'Белый', 'Текстиль'],
        ['Сандалии Crocs', 'Летние сандалии', 'детская', 'Crocs', 2990, 'Голубой', 'Резина'],
        ['Кеды Vans Old Skool', 'Классические кеды', 'мужская', 'Vans', 4990, 'Черный/Белый', 'Кожа']
    ];
    
    foreach ($products as $p) {
        $db->query("INSERT INTO products (name, description, category, brand, price, color, material, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')", $p);
        $pid = $db->query("SELECT last_insert_rowid()")->fetchColumn();
        
        $sizes = [39,40,41,42,43,44,45];
        foreach ($sizes as $size) {
            $db->query("INSERT INTO product_sizes (product_id, size, quantity) VALUES (?, ?, ?)", [$pid, $size, rand(5,20)]);
        }
        
        $db->query("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)", 
                  [$pid, 'assets/images/no-image.png']);
    }
}
?>
