<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/db.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) { http_response_code(403); exit('Invalid request.'); }
$id = filter_var(requestScalar($_POST, 'appointment_id'), FILTER_VALIDATE_INT); $status = requestEnum($_POST, 'status', ['Pending Confirmation','Confirmed','Completed','Cancelled']);
if (!$id || $status === '') { header('Location: ../bookings.php?message=Invalid booking update'); exit; }
$stmt = $adminConnection->prepare('UPDATE appointments SET status = :status WHERE id = :id'); $stmt->execute(['status' => $status, 'id' => $id]);
$note = $adminConnection->prepare('INSERT INTO admin_notifications (type, message) VALUES (:type, :message)'); $note->execute(['type' => 'Booking update', 'message' => 'Booking #' . $id . ' changed to ' . $status]);
header('Location: ../bookings.php?message=Booking updated successfully.'); exit;
