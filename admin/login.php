<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (isset($_SESSION['admin'])) { header('Location: dashboard.php'); exit; }
$errors = [];
$databaseUnavailable = false;
$adminConnection = null;
try {
    require_once __DIR__ . '/includes/db.php';
} catch (PDOException $exception) {
    $databaseUnavailable = true;
    $errors[] = 'The admin database is unavailable. Please start MySQL/WAMP and try again.';
}
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$databaseUnavailable && $adminConnection instanceof PDO) {
    $email = strtolower(requestText($_POST, 'email', 191));
    $password = requestScalar($_POST, 'password');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') $errors[] = 'Enter a valid admin email and password.';
    if (!$errors) {
        $statement = $adminConnection->prepare("SELECT u.id, u.name, u.email, u.password_hash, u.role, u.account_status, ha.hospital_id FROM users u LEFT JOIN hospital_admins ha ON ha.user_id = u.id AND ha.status = 'Active' WHERE u.email = :email AND (u.role IN ('admin', 'SUPER_ADMIN') OR (u.role = 'HOSPITAL_ADMIN' AND ha.hospital_id IS NOT NULL)) LIMIT 1");
        $statement->execute(['email' => $email]);
        $admin = $statement->fetch();
        if (!$admin || $admin['account_status'] !== 'Active' || !password_verify($password, $admin['password_hash'])) {
            $errors[] = 'Invalid admin credentials.';
        } else {
            session_regenerate_id(true);
            if (strtoupper((string) $admin['role']) === 'HOSPITAL_ADMIN') {
                $_SESSION['hospital_admin'] = ['id' => (int) $admin['id'], 'name' => $admin['name'], 'email' => $admin['email'], 'role' => 'HOSPITAL_ADMIN', 'hospital_id' => (int) $admin['hospital_id']];
                header('Location: ../hospital/dashboard.php'); exit;
            }
            $_SESSION['admin'] = ['id' => (int) $admin['id'], 'name' => $admin['name'], 'email' => $admin['email'], 'role' => 'SUPER_ADMIN'];
            header('Location: dashboard.php'); exit;
        }
    }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Login | KATOC</title><link rel="stylesheet" href="css/admin.css"></head><body class="admin-auth"><main class="admin-auth-card"><span class="admin-kicker">KATOC CARE NETWORK</span><h1>Admin sign in</h1><p>Manage dialysis centers, bookings, schedules, and patients.</p><?php if ($errors): ?><div class="admin-error" role="alert"><?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endforeach; ?></div><?php endif; ?><form method="post"><label class="admin-field">Email<input type="email" name="email" maxlength="191" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required></label><label class="admin-field">Password<input type="password" name="password" required></label><button class="admin-btn" type="submit">Sign in to admin</button></form><p><a href="../hospital/login.php">Hospital Admin login</a> | <a href="../login/login.php">Sign in as User</a></p></main></body></html>
