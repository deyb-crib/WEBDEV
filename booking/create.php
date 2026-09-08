<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/../config/request.php';

$isAjax = strtolower(requestScalar($_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest'
    || str_contains(requestScalar($_SERVER, 'HTTP_ACCEPT'), 'application/json')
    || requestScalar($_POST, 'format') === 'json';

if (!isset($_SESSION['user'])) {
    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Please log in to submit an appointment request.']);
        exit;
    }
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$center = requestText($_POST, 'center', 150);
$location = requestText($_POST, 'location', 200);
$hours = requestText($_POST, 'hours', 100);
$dialysisType = requestEnum($_POST, 'dialysis_type', [
    'Hemodialysis',
    'Peritoneal Dialysis',
    'Home Hemodialysis',
    'Continuous Ambulatory Peritoneal Dialysis',
    'Automated Peritoneal Dialysis',
]);
$patientName = requestText($_POST, 'patient_name', 100);
$patientContact = requestText($_POST, 'patient_contact', 30);
$patientEmail = strtolower(requestText($_POST, 'patient_email', 254));
$emergencyName = requestText($_POST, 'emergency_name', 100);
$emergencyContact = requestText($_POST, 'emergency_contact', 30);
$notes = requestText($_POST, 'notes', 1000);
$dateObject = requestDate($_POST, 'date');
$date = $dateObject?->format('Y-m-d') ?? '';
$session = requestEnum($_POST, 'session', ['Morning', 'Afternoon', 'Evening']);
$time = requestEnum($_POST, 'time', ['08:00', '10:00', '13:00', '15:00', '18:00']);
$allowedTypes = [
    'Hemodialysis',
    'Peritoneal Dialysis',
    'Home Hemodialysis',
    'Continuous Ambulatory Peritoneal Dialysis',
    'Automated Peritoneal Dialysis',
];
$allowedSessions = ['Morning', 'Afternoon', 'Evening'];
$timeSlots = [
    '08:00' => 'Morning',
    '10:00' => 'Morning',
    '13:00' => 'Afternoon',
    '15:00' => 'Afternoon',
    '18:00' => 'Evening',
];

$hospitalId = 0;
$facilityTypes = '';
$facility = null;

try {
    $facilityStatement = getMySqlConnection()->prepare("SELECT id, address, operating_hours, dialysis_types FROM dialysis_centers WHERE name = :name AND status = 'Active' LIMIT 1");
    $facilityStatement->execute(['name' => $center]);
    $facility = $facilityStatement->fetch();
} catch (Throwable $exception) {
    $facility = getFallbackCenterByName($center);
}

if ($facility) {
    $hospitalId = (int) ($facility['id'] ?? 0);
    $location = (string) ($facility['address'] ?? $location);
    $hours = (string) ($facility['operating_hours'] ?? $hours);
    $facilityTypes = (string) ($facility['dialysis_types'] ?? $facilityTypes);
}

$offeredTypes = array_filter(array_map('trim', explode(',', $facilityTypes)));

$configuredCenters = getConfiguredDialysisCenters();
if (isset($configuredCenters[$center])) {
    $location = $configuredCenters[$center]['location'];
    $hours = $configuredCenters[$center]['hours'];
}

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
$validContact = static fn (string $value): bool => (bool) preg_match('/^[0-9+() .-]{7,30}$/', $value);
$validName = static fn (string $value): bool => (bool) preg_match('/^[\p{L}\p{M} .\'\-]{2,100}$/u', $value);

$saved = false;
$reference = '';
$message = '';

if ($hospitalId <= 0 || $center === '' || $location === '' || !$dateObject || $dateObject->format('Y-m-d') !== $date || $date < date('Y-m-d')
    || !in_array($dialysisType, $allowedTypes, true) || !in_array($dialysisType, $offeredTypes, true) || !in_array($session, $allowedSessions, true)
    || !array_key_exists($time, $timeSlots) || $timeSlots[$time] !== $session || !$timeIsWithinHours
    || !$validName($patientName) || !$validContact($patientContact) || !filter_var($patientEmail, FILTER_VALIDATE_EMAIL)
    || !$validName($emergencyName) || !$validContact($emergencyContact)) {
    http_response_code(422);
    $message = 'Please provide valid appointment details and complete all required fields.';
} else {
    if (!isValidCsrfToken(requestScalar($_POST, 'csrf_token'))) {
        http_response_code(403);
        $message = 'Invalid request token. Please return to the booking page and try again.';
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        exit($message);
    }

    try {
        $appointment = createMySqlAppointment([
            'user_id' => (int) $_SESSION['user']['id'],
            'hospital_id' => $hospitalId,
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
        $message = 'Your dialysis appointment request has been submitted successfully.';
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

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $saved,
        'message' => $message,
        'reference' => $reference,
        'appointment' => $saved ? [
            'reference' => $reference,
            'center' => $center,
            'location' => $location,
            'date' => $date,
            'time' => $time,
            'dialysis_type' => $dialysisType,
            'session' => $session,
            'patient_name' => $patientName,
            'status' => 'Pending Confirmation',
        ] : null,
    ]);
    exit;
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
            <h1>APPOINTMENT BOOKED</h1>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <div class="booking-confirmation">
                <p><strong>Appointment Reference:</strong><span><?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Dialysis Center:</strong><span><?= htmlspecialchars($center, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Location:</strong><span><?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Date:</strong><span><?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Time:</strong><span><?= htmlspecialchars($time, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Session:</strong><span><?= htmlspecialchars($session, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Dialysis Type:</strong><span><?= htmlspecialchars($dialysisType, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Patient:</strong><span><?= htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8') ?></span></p>
                <p><strong>Status:</strong><span>Pending Confirmation</span></p>
            </div>
            <div class="booking-result-actions">
                <a class="availability-primary" href="../dashboard/dashboard.php#appointments">VIEW MY APPOINTMENTS</a>
                <a class="availability-secondary" href="../index.php">BACK TO HOME</a>
            </div>
        <?php else: ?>
            <h1><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></h1>
                <a class="availability-primary" href="../index.php#services">Back to Find a Center</a>
        <?php endif; ?>
    </main>
</body>
</html>