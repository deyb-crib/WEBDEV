<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (isset($_SESSION['hospital_admin'])) { header('Location: dashboard.php'); exit; }
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/request.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(requestText($_POST, 'email', 191));
    $password = requestScalar($_POST, 'password');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') $errors[] = 'Enter a valid email and password.';
    if (!$errors) {
        try {
            $connection = getMySqlConnection();
            $statement = $connection->prepare("SELECT u.id, u.name, u.email, u.password_hash, u.account_status, ha.hospital_id FROM users u INNER JOIN hospital_admins ha ON ha.user_id = u.id AND ha.status = 'Active' WHERE u.email = :email AND u.role = 'HOSPITAL_ADMIN' LIMIT 1");
            $statement->execute(['email' => $email]);
            $admin = $statement->fetch();
            if (!$admin || $admin['account_status'] !== 'Active' || !password_verify($password, $admin['password_hash'])) $errors[] = 'Invalid hospital admin credentials.';
            else { session_regenerate_id(true); $_SESSION['hospital_admin'] = ['id' => (int) $admin['id'], 'name' => $admin['name'], 'email' => $admin['email'], 'role' => 'HOSPITAL_ADMIN', 'hospital_id' => (int) $admin['hospital_id']]; header('Location: dashboard.php'); exit; }
        } catch (PDOException $exception) { $errors[] = 'The hospital admin database is unavailable.'; }
    }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Hospital Admin Login | KATOC</title><link rel="stylesheet" href="../admin/css/admin.css"></head><body class="admin-auth"><main class="admin-auth-card"><span class="admin-kicker">KATOC CARE NETWORK</span><h1>Hospital Admin sign in</h1><p>Manage bookings and schedules for your hospital.</p><?php if ($errors): ?><div class="admin-error" role="alert"><?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endforeach; ?></div><?php endif; ?><form method="post"><label class="admin-field">Email<input type="email" name="email" maxlength="191" required></label><label class="admin-field">Password<input type="password" name="password" required></label><button class="admin-btn" type="submit">Sign in</button></form><p><a href="../admin/login.php">Back to main admin login</a> | <a href="../login/login.php">Sign in as User</a></p></main></body></html>