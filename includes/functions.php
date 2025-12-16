<?php
// includes/functions.php

if (!function_exists('h')) {
    function h($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        require_once __DIR__ . '/auth.php';
        $auth = new Auth();
        return $auth->getCurrentUser();
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin() {
        $user = getCurrentUser();
        if (!$user) {
            $return = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
            header('Location: login.php?return=' . $return);
            exit;
        }
        return $user;
    }
}

if (!function_exists('requireRole')) {
    function requireRole($role) {
        require_once __DIR__ . '/auth.php';
        $auth = new Auth();
        if (!$auth->hasRole($role)) {
            http_response_code(403);
            die('Доступ запрещён');
        }
    }
}

if (!function_exists('redirectTo')) {
    function redirectTo($url) {
        header('Location: ' . $url);
        exit;
    }
}
