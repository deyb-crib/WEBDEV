<?php
$adminPageTitle = $adminPageTitle ?? 'Admin Dashboard';
$adminUser = $adminUser ?? requireAdmin();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?> | KATOC Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-page">
<?php require __DIR__ . '/sidebar.php'; ?>
<div class="admin-shell">
    <header class="admin-topbar">
        <button class="admin-menu-toggle" type="button" aria-label="Open admin navigation">Menu</button>
        <div><span class="admin-kicker">KATOC CARE NETWORK</span><h1><?= htmlspecialchars($adminPageTitle, ENT_QUOTES, 'UTF-8') ?></h1></div>
        <span class="admin-user-label"><?= htmlspecialchars($adminUser['name'] ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?></span>
    </header>
    <main class="admin-main">
        <?php if (!empty($_GET['message'])): ?><div class="admin-flash" role="status"><?= htmlspecialchars(requestText($_GET, 'message', 255), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
