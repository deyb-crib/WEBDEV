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

$center = trim((string) ($_GET['center'] ?? ''));
$date = trim((string) ($_GET['date'] ?? ''));
$bookedTimes = [];
$dateObject = DateTime::createFromFormat('Y-m-d', $date);

if (!array_key_exists($center, getConfiguredDialysisCenters()) || !$dateObject || $dateObject->format('Y-m-d') !== $date) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid center or date.']);
    exit;
}

try {
    $statement = getMySqlConnection()->prepare(
        "SELECT appointment_time FROM appointments
         WHERE center_name = :center_name AND appointment_date = :appointment_date AND status <> 'Cancelled'"
    );
    $statement->execute(['center_name' => $center, 'appointment_date' => $date]);
    foreach ($statement->fetchAll() as $appointment) {
        $bookedTimes[] = substr((string) $appointment['appointment_time'], 0, 5);
    }
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load availability.']);
    exit;
}

echo json_encode(['bookedTimes' => array_values(array_unique($bookedTimes))]);