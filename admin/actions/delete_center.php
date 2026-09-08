<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/db.php'; requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) { http_response_code(403); exit('Invalid request.'); }
$id = filter_var(requestScalar($_POST, 'center_id'), FILTER_VALIDATE_INT); if (!$id) { header('Location: ../centers.php?message=Invalid center.'); exit; }
$stmt = $adminConnection->prepare("UPDATE dialysis_centers SET status = 'Inactive' WHERE id = :id"); $stmt->execute(['id' => $id]); header('Location: ../centers.php?message=Dialysis center deactivated successfully.'); exit;
