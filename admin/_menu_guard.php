<?php
if (!defined('ADMIN_MENU_INCLUDE')) {
    $menu = basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
    $menus = ['dashboard', 'invoices', 'clients', 'users', 'payments', 'audit', 'backups', 'profile', 'settings'];
    if (!in_array($menu, $menus, true)) {
        http_response_code(404);
        exit;
    }
    define('ADMIN_MENU_INCLUDE', true);
    define('ADMIN_INITIAL_PAGE', $menu);
    require __DIR__ . '/index.php';
    exit;
}
