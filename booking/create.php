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

$center = trim((string) ($_POST['center'] ?? ''));
$location = trim((string) ($_POST['location'] ?? ''));
$hours = trim((string) ($_POST['hours'] ?? ''));
$dialysisType = trim((string) ($_POST['dialysis_type'] ?? ''));
$patientName = trim((string) ($_POST['patient_name'] ?? ''));
$patientContact = trim((string) ($_POST['patient_contact'] ?? ''));
$patientEmail = trim((string) ($_POST['patient_email'] ?? ''));
$emergencyName = trim((string) ($_POST['emergency_name'] ?? ''));
$emergencyContact = trim((string) ($_POST['emergency_contact'] ?? ''));
$notes = trim((string) ($_POST['notes'] ?? ''));
$date = trim((string) ($_POST['date'] ?? ''));
$session = trim((string) ($_POST['session'] ?? ''));
$time = trim((string) ($_POST['time'] ?? ''));
$dateObject = DateTime::createFromFormat('Y-m-d', $date);
$allowedTypes = ['Hemodialysis', 'Peritoneal Dialysis'];
$allowedSessions = ['Morning', 'Afternoon', 'Evening'];
$timeSlots = [
    '08:00' => 'Morning',
    '10:00' => 'Morning',
    '13:00' => 'Afternoon',
    '15:00' => 'Afternoon',
    '18:00' => 'Evening',
];
$hourMatches = preg_match_all('/\d{1,2}:\d{2}\s*(?:am|pm)/i', $hours, $matches) ? $matches[0] : [];
$toMinutes = static function (string $value): ?int {
    $parts = date_parse(strtolower(trim($value)));
    if (($parts['error_count'] ?? 1) > 0 || !isset($parts['hour'], $parts['minute'])) {
        return null;
    }

    return ((int) $parts['hour'] * 60) + (int) $parts['minute'];
};
$opening = $toMinutes($hourMatches[0] ?? '');
$closing = $toMinutes($hourMatches[1] ?? '');
$selectedMinutes = $time !== '' ? ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2) : null;
$timeIsWithinHours = $selectedMinutes !== null && $opening !== null && $closing !== null
    && $selectedMinutes >= $opening && $selectedMinutes <= $closing;
$saved = false;
$reference = '';
require_once __DIR__ . '/../config/database.php';

if (!isConfiguredDialysisCenter($center, $location, $hours) || $center === '' || $location === '' || !$dateObject || $dateObject->format('Y-m-d') !== $date || $date < date('Y-m-d')
    || !in_array($dialysisType, $allowedTypes, true) || !in_array($session, $allowedSessions, true)
    || !array_key_exists($time, $timeSlots) || $timeSlots[$time] !== $session || !$timeIsWithinHours
    || $patientName === '' || $patientContact === '' || !filter_var($patientEmail, FILTER_VALIDATE_EMAIL)
    || $emergencyName === '' || $emergencyContact === '' || strlen($notes) > 1000) {
    http_response_code(422);
    $message = 'Please choose a valid center, date, and time.';
} else {
    if (!isValidCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid request token. Please return to the booking page and try again.');
    }

    try {
        $appointment = createMySqlAppointment([
            'user_id' => (int) $_SESSION['user']['id'],
            'center_name' => $center,
            'center_location' => $location,
            'dialysis_type' => $dialysisType,
            'appointment_date' => $date,
            'session' => $session,
            'appointment_time' => $time,
            'patient_name' => $patientName,
            'contact_number' => $patientContact,
            'email' => $patientEmail,
            'emergency_contact_name' => $emergencyName,
            'emergency_contact_number' => $emergencyContact,
            'notes' => $notes,
        ]);
        $reference = $appointment['reference'];
        $saved = true;
        $message = 'Your dialysis appointment request has been submitted successfully. The dialysis center will review your request.';
    } catch (RuntimeException $exception) {
        if ($exception->getMessage() === 'slot_unavailable') {
            http_response_code(409);
            $message = 'This time slot is no longer available. Please select another time.';
        } else {
            http_response_code(500);
            $message = 'We could not save your appointment request. Please try again.';
        }
    } catch (PDOException $exception) {
        http_response_code(500);
        $message = 'We could not save your appointment request. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Request | KATOC</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <main class="booking-result" id="appointment-request">
        <span class="section-eyebrow">KATOC CARE NETWORK</span>
        <?php if ($saved): ?>
            <h1>Appointment Request Submitted</h1>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <div class="booking-confirmation">
                <p><strong>Reference Number</strong><span><?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Dialysis Center</strong><span><?= htmlspecialchars($center, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Date</strong><span><?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Time</strong><span><?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Dialysis Type</strong><span><?= htmlspecialchars($dialysisType, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Status</strong><span>Pending Confirmation</span></p>
            </div>
            <div class="booking-result-actions">
                <a class="availability-primary" href="#appointment-request">View Appointment</a>
                <a class="availability-secondary" href="../index.php">Back to Home</a>
            </div>
        <?php else: ?>
            <h1><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></h1>
            <a class="availability-primary" href="../index.php#services">Back to Home</a>
        <?php endif; ?>
    </main>
</body>
</html>