<?php
// includes/init.php

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cart.php';

// создаём один общий экземпляр БД
$db = new Database();

// передаём БД в Auth
$auth = new Auth($db);

// и затем передаём и БД, и Auth в Cart
$cart = new Cart($db, $auth);

// дальше в других файлах используем только $db, $auth, $cart
