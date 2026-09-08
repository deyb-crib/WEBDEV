<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/request.php';

if (!isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) {
    http_response_code(403);
    exit('Invalid request token.');
}

$userId = (int) $_SESSION['user']['id'];
$appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
$action = requestEnum($_POST, 'action', ['cancel', 'reschedule']);
$message = 'Unable to update the appointment.';

try {
    if (!$appointmentId) {
        throw new RuntimeException('invalid_appointment');
    }

    if ($action === 'cancel') {
        $message = cancelMySqlAppointment($appointmentId, $userId)
            ? 'Appointment cancelled.'
            : 'This appointment can no longer be cancelled.';
    } elseif ($action === 'reschedule') {
        $dateObject = requestDate($_POST, 'date');
        $date = $dateObject?->format('Y-m-d') ?? '';
        $time = requestEnum($_POST, 'time', ['08:00', '10:00', '13:00', '15:00', '18:00']);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $date || $date < date('Y-m-d') || $time === '') {
            $message = 'Please choose a valid future date and time.';
        } else {
            call_user_func('updateMySqlAppointmentSchedule', $appointmentId, $userId, $date, $time);
            $message = 'Appointment rescheduled.';
        }
    }
} catch (RuntimeException $exception) {
    $message = $exception->getMessage() === 'slot_unavailable'
        ? 'This time slot is no longer available. Please select another time.'
        : 'This appointment can no longer be changed.';
} catch (PDOException $exception) {
    $message = 'Unable to update the appointment right now.';
}

header('Location: dashboard.php?message=' . rawurlencode($message));
exit;