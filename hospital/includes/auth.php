<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function requireHospitalAdmin(): array
{
    if (!isset($_SESSION['hospital_admin']) || !is_array($_SESSION['hospital_admin'])) {
        header('Location: ../admin/login.php');
        exit;
    }

    $role = strtoupper((string) ($_SESSION['hospital_admin']['role'] ?? ''));
    $hospitalId = filter_var($_SESSION['hospital_admin']['hospital_id'] ?? null, FILTER_VALIDATE_INT);
    if ($role !== 'HOSPITAL_ADMIN' || !$hospitalId) {
        http_response_code(403);
        exit('Hospital Admin access required.');
    }

    return $_SESSION['hospital_admin'];
}

function currentHospitalId(): int
{
    $admin = requireHospitalAdmin();
    return (int) $admin['hospital_id'];
}