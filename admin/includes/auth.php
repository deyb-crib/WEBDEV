<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function requireSuperAdmin(): array
{
    if (!isset($_SESSION['admin']) || !is_array($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }

    $role = strtoupper((string) ($_SESSION['admin']['role'] ?? ''));
    if (!in_array($role, ['ADMIN', 'SUPER_ADMIN'], true)) {
        http_response_code(403);
        exit('Super Admin access required.');
    }

    return $_SESSION['admin'];
}

function requireAdmin(): array
{
    return requireSuperAdmin();
}
