<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireSuperAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) {
    http_response_code(403);
    exit('Invalid request.');
}

$name = requestText($_POST, 'name', 100);
$email = strtolower(requestText($_POST, 'email', 191));
$hospitalId = filter_var(requestScalar($_POST, 'hospital_id'), FILTER_VALIDATE_INT);
$password = requestScalar($_POST, 'password');
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$hospitalId || strlen($password) < 8) {
    header('Location: ../hospital_admins.php?message=Please provide valid hospital admin details.');
    exit;
}

$hospitalCheck = $adminConnection->prepare("SELECT id FROM dialysis_centers WHERE id = :id AND status = 'Active'");
$hospitalCheck->execute(['id' => $hospitalId]);
if (!$hospitalCheck->fetchColumn()) {
    header('Location: ../hospital_admins.php?message=Select an active hospital.');
    exit;
}

try {
    $adminConnection->beginTransaction();
    $userStatement = $adminConnection->prepare("INSERT INTO users (name, email, password_hash, role, account_status) VALUES (:name, :email, :password_hash, 'HOSPITAL_ADMIN', 'Active')");
    $userStatement->execute(['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    $userId = (int) $adminConnection->lastInsertId();
    $linkStatement = $adminConnection->prepare('INSERT INTO hospital_admins (user_id, hospital_id, status) VALUES (:user_id, :hospital_id, \'Active\')');
    $linkStatement->execute(['user_id' => $userId, 'hospital_id' => $hospitalId]);
    $adminConnection->commit();
    header('Location: ../hospital_admins.php?message=Hospital admin created successfully.');
} catch (PDOException $exception) {
    if ($adminConnection->inTransaction()) $adminConnection->rollBack();
    header('Location: ../hospital_admins.php?message=That email is already registered or the account could not be created.');
}
exit;