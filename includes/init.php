<?php
// includes/init.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/functions.php';

$db   = new Database();
$auth = new Auth();
$user = $auth->getCurrentUser();
$cart = new Cart();
