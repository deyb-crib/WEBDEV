<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireSuperAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) {
    http_response_code(403);
    exit('Invalid request.');
}

$userId = filter_var(requestScalar($_POST, 'user_id'), FILTER_VALIDATE_INT);
$status = requestEnum($_POST, 'status', ['Active', 'Inactive']);
if (!$userId || $status === '') {
    header('Location: ../hospital_admins.php?message=Invalid hospital admin update.');
    exit;
}

$statement = $adminConnection->prepare("UPDATE users u INNER JOIN hospital_admins ha ON ha.user_id = u.id SET u.account_status = :status, ha.status = :status WHERE u.id = :user_id AND u.role = 'HOSPITAL_ADMIN'");
$statement->execute(['status' => $status, 'user_id' => $userId]);
header('Location: ../hospital_admins.php?message=Hospital admin status updated.');
exit;