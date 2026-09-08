<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/request.php';
require_once __DIR__ . '/../includes/scope.php';

requireHospitalAdmin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) {
    http_response_code(403);
    exit('Invalid request.');
}

$bookingId = filter_var(requestScalar($_POST, 'appointment_id'), FILTER_VALIDATE_INT);
$status = requestEnum($_POST, 'status', ['Confirmed', 'Rejected', 'Completed', 'Cancelled']);
$rejectionReason = requestText($_POST, 'rejection_reason', 500);
if (!$bookingId || $status === '' || ($status === 'Rejected' && $rejectionReason === '')) {
    header('Location: ../bookings.php?message=Choose a valid booking action and rejection reason.');
    exit;
}

$connection = getMySqlConnection();
$statement = $connection->prepare(
    "UPDATE appointments
     SET status = :status, rejection_reason = :rejection_reason
     WHERE id = :id AND hospital_id = :hospital_id
       AND status IN ('Pending Confirmation', 'Confirmed')"
);
$statement->execute([
    'status' => $status,
    'rejection_reason' => $status === 'Rejected' ? $rejectionReason : null,
    'id' => $bookingId,
    'hospital_id' => currentHospitalId(),
]);

$message = $statement->rowCount() === 1 ? 'Booking updated successfully.' : 'Booking was not found or cannot be updated.';
header('Location: ../bookings.php?message=' . rawurlencode($message));
exit;