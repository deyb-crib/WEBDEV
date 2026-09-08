<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (isset($_SESSION['admin'])) { header('Location: dashboard.php'); exit; }
$errors = []; $name = ''; $email = ''; $databaseUnavailable = false; $adminConnection = null;
try { require_once __DIR__ . '/includes/db.php'; } catch (PDOException $exception) { $databaseUnavailable = true; $errors[] = 'The admin database is unavailable. Please start MySQL/WAMP and try again.'; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$databaseUnavailable && $adminConnection instanceof PDO) {
    $name = requestText($_POST, 'name', 100); $email = strtolower(requestText($_POST, 'email', 191));
    $password = requestScalar($_POST, 'password'); $confirm = requestScalar($_POST, 'confirm_password');
    if (strlen($name) < 2 || !preg_match('/^[\p{L}\p{M} .\'\-]+$/u', $name)) $errors[] = 'Enter a valid full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8 || $password !== $confirm) $errors[] = 'Passwords must match and be at least 8 characters.';
    if (!$errors) { $stmt=$adminConnection->prepare('INSERT INTO users (name,email,password_hash,role,account_status) VALUES (:name,:email,:hash,\'admin\',\'Active\')'); try {$stmt->execute(['name'=>$name,'email'=>$email,'hash'=>password_hash($password,PASSWORD_DEFAULT)]); header('Location: login.php?message=Admin account created.'); exit;} catch(PDOException $exception){$errors[]='That email address is already registered.';} }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Sign Up | KATOC</title><link rel="stylesheet" href="css/admin.css"></head><body class="admin-auth"><main class="admin-auth-card"><span class="admin-kicker">KATOC CARE NETWORK</span><h1>Create admin account</h1><p>Create an administrator account to manage the KATOC platform.</p><?php if($errors): ?><div class="admin-error" role="alert"><?php foreach($errors as $error): ?><div><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endforeach; ?></div><?php endif; ?><form method="post"><label class="admin-field">Full name<input name="name" maxlength="100" value="<?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?>" required></label><label class="admin-field">Email<input type="email" name="email" maxlength="191" value="<?= htmlspecialchars($email,ENT_QUOTES,'UTF-8') ?>" required></label><label class="admin-field">Password<input type="password" name="password" minlength="8" required></label><label class="admin-field">Confirm password<input type="password" name="confirm_password" minlength="8" required></label><button class="admin-btn" type="submit">Create admin account</button></form><p><a href="login.php">Back to admin login</a></p></main></body></html>
