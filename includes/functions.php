<?php
// includes/functions.php

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function current_url(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return strtok($uri, '?');
}

function active_link(string $path): string {
    $current = current_url();
    $full    = BASE_URL . $path;
    return $current === $full ? 'nav-link-active' : '';
}

/**
 * Общее количество товаров в корзине (по сессии)
 */
function getCartCount(): int {
    if (!isset($_SESSION['cart']['items']) || !is_array($_SESSION['cart']['items'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart']['items'] as $qty) {
        $total += (int)$qty;
    }
    return $total;
}
