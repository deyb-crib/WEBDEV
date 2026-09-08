<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/request.php';

$center = requestText($_GET, 'center', 150);
$dateObject = requestDate($_GET, 'date');
$date = $dateObject?->format('Y-m-d') ?? '';
$bookedTimes = [];
$connection = null;

$facility = null;

try {
    $connection = getMySqlConnection();
    $facilityStatement = $connection->prepare("SELECT address, operating_hours FROM dialysis_centers WHERE name = :name AND status = 'Active' LIMIT 1");
    $facilityStatement->execute(['name' => $center]);
    $facility = $facilityStatement->fetch();
} catch (Throwable $exception) {
    $facility = getFallbackCenterByName($center);
}

if (!$facility || !$dateObject || $dateObject->format('Y-m-d') !== $date) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid center or date.']);
    exit;
}

try {
    if ($connection instanceof PDO) {
        $statement = $connection->prepare(
            "SELECT appointment_time FROM appointments
             WHERE center_name = :center_name AND appointment_date = :appointment_date AND status <> 'Cancelled'"
        );
        $statement->execute(['center_name' => $center, 'appointment_date' => $date]);
        foreach ($statement->fetchAll() as $appointment) {
            $bookedTimes[] = substr((string) $appointment['appointment_time'], 0, 5);
        }
    } else {
        $appointments = getDatabaseConnection()->getAppointments();
        foreach ($appointments as $appointment) {
            if ((string) ($appointment['center_name'] ?? '') !== $center) {
                continue;
            }

            if ((string) ($appointment['appointment_date'] ?? '') !== $date) {
                continue;
            }

            if (strtolower((string) ($appointment['status'] ?? '')) === 'cancelled') {
                continue;
            }

            $bookedTimes[] = substr((string) ($appointment['appointment_time'] ?? ''), 0, 5);
        }
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load availability.']);
    exit;
}

echo json_encode([
    'center' => $center,
    'location' => $facility['address'],
    'hours' => $facility['operating_hours'],
    'bookedTimes' => array_values(array_unique($bookedTimes)),
]);