<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/db.php'; requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) { http_response_code(403); exit('Invalid request.'); }
$id=filter_var(requestScalar($_POST,'user_id'),FILTER_VALIDATE_INT); $status=requestEnum($_POST,'status',['Active','Inactive']); if(!$id||$status===''){header('Location: ../users.php?message=Invalid user update');exit;}
$stmt=$adminConnection->prepare("UPDATE users SET account_status=:status WHERE id=:id AND role='patient'");$stmt->execute(['status'=>$status,'id'=>$id]);header('Location: ../users.php?message=User account updated successfully.');exit;
