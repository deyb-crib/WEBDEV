<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/scope.php';
$hospitalAdmin = requireHospitalAdmin();
$hospitalPageTitle = 'Hospital Patients';
$connection = getMySqlConnection();
$statement = $connection->prepare("SELECT u.id, u.name, u.email, COUNT(a.id) AS booking_count, MAX(a.created_at) AS last_booking FROM users u INNER JOIN appointments a ON a.user_id = u.id AND a.hospital_id = :hospital_id WHERE u.role = 'patient' GROUP BY u.id ORDER BY last_booking DESC");
$statement->execute(['hospital_id' => currentHospitalId()]);
$patients = $statement->fetchAll();
require __DIR__ . '/includes/header.php';
?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Patient</th><th>Email</th><th>Bookings</th><th>Last Booking</th></tr></thead><tbody><?php foreach ($patients as $patient): ?><tr><td><?= htmlspecialchars($patient['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($patient['email'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) $patient['booking_count'] ?></td><td><?= htmlspecialchars($patient['last_booking'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$patients): ?><p class="admin-empty">No patients have booked this hospital.</p><?php endif; ?></div><?php require __DIR__ . '/includes/footer.php'; ?>