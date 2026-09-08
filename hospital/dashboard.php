<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/scope.php';
$hospitalAdmin = requireHospitalAdmin();
$hospitalPageTitle = 'Hospital Dashboard';
$hospitalId = currentHospitalId();
$connection = getMySqlConnection();
$hospitalStatement = $connection->prepare("SELECT name FROM dialysis_centers WHERE id = :hospital_id AND status = 'Active'");
$hospitalStatement->execute(['hospital_id' => $hospitalId]);
$hospitalName = (string) ($hospitalStatement->fetchColumn() ?: 'Assigned Hospital');
$stats = [];
foreach (['Total Bookings' => '', 'Pending Requests' => " AND a.status = 'Pending Confirmation'", 'Confirmed Bookings' => " AND a.status = 'Confirmed'", 'Rejected Bookings' => " AND a.status = 'Rejected'", 'Completed Bookings' => " AND a.status = 'Completed'", 'Cancelled Bookings' => " AND a.status = 'Cancelled'"] as $label => $filter) {
    $statement = $connection->prepare("SELECT COUNT(*) FROM appointments a WHERE a.hospital_id = :hospital_id$filter");
    $statement->execute(['hospital_id' => $hospitalId]);
    $stats[$label] = (int) $statement->fetchColumn();
}
$recent = $connection->prepare('SELECT a.appointment_reference, a.patient_name, a.dialysis_type, a.appointment_date, a.appointment_time, a.status FROM appointments a WHERE a.hospital_id = :hospital_id ORDER BY a.created_at DESC LIMIT 8');
$recent->execute(['hospital_id' => $hospitalId]);
$recentBookings = $recent->fetchAll();
require __DIR__ . '/includes/header.php';
?><section class="admin-panel"><div class="admin-panel-heading"><h2>Welcome, <?= htmlspecialchars($hospitalName, ENT_QUOTES, 'UTF-8') ?></h2></div><div class="admin-stats"><?php foreach ($stats as $label => $value): ?><article class="admin-stat"><span class="admin-stat-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span><strong class="admin-stat-value"><?= $value ?></strong></article><?php endforeach; ?></div></section><section class="admin-panel" style="margin-top:22px"><div class="admin-panel-heading"><h2>Recent Booking Requests</h2></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Booking ID</th><th>Patient</th><th>Dialysis Type</th><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody><?php foreach ($recentBookings as $booking): ?><tr><td><?= htmlspecialchars($booking['appointment_reference'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($booking['patient_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($booking['dialysis_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($booking['appointment_date'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($booking['appointment_time'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$recentBookings): ?><p class="admin-empty">No booking requests for this hospital.</p><?php endif; ?></div></section><?php require __DIR__ . '/includes/footer.php'; ?>