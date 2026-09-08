<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
$adminUser = requireAdmin();
$adminPageTitle = 'Dashboard';
$stats = [];
try {
    $stats['centers'] = (int) $adminConnection->query("SELECT COUNT(*) FROM dialysis_centers")->fetchColumn();
    $stats['active_centers'] = (int) $adminConnection->query("SELECT COUNT(*) FROM dialysis_centers WHERE status = 'Active'")->fetchColumn();
    $stats['users'] = (int) $adminConnection->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
    $stats['pending'] = (int) $adminConnection->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending Confirmation'")->fetchColumn();
    $stats['confirmed'] = (int) $adminConnection->query("SELECT COUNT(*) FROM appointments WHERE status = 'Confirmed'")->fetchColumn();
    $stats['cancelled'] = (int) $adminConnection->query("SELECT COUNT(*) FROM appointments WHERE status = 'Cancelled'")->fetchColumn();
    $recent = $adminConnection->query('SELECT a.appointment_reference, a.center_name, a.appointment_date, a.status, u.name AS patient_name FROM appointments a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 6')->fetchAll();
    $statusRows = $adminConnection->query('SELECT status, COUNT(*) AS total FROM appointments GROUP BY status ORDER BY total DESC')->fetchAll();
} catch (PDOException $exception) { $recent = []; $statusRows = []; $stats = array_fill_keys(['centers','active_centers','users','pending','confirmed','cancelled'], 0); }
require __DIR__ . '/includes/header.php';
?><section class="admin-stats"><?php foreach ([['centers','Total Dialysis Centers'],['active_centers','Active Dialysis Centers'],['users','Registered Users'],['pending','Pending Bookings'],['confirmed','Confirmed Bookings'],['cancelled','Cancelled Bookings']] as [$key,$label]): ?><article class="admin-stat"><span class="admin-stat-label"><?= $label ?></span><strong class="admin-stat-value"><?= (int) $stats[$key] ?></strong></article><?php endforeach; ?></section><section class="admin-grid"><article class="admin-panel"><div class="admin-panel-heading"><h2>Recent booking activity</h2><a class="admin-btn admin-btn-secondary" href="bookings.php">View all</a></div><?php if (!$recent): ?><p class="admin-empty">No booking activity yet.</p><?php else: ?><div class="admin-list"><?php foreach ($recent as $row): ?><div class="admin-list-item"><span><strong><?= htmlspecialchars($row['patient_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></strong><br><?= htmlspecialchars($row['center_name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($row['appointment_reference'], ENT_QUOTES, 'UTF-8') ?></span><span class="admin-status"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?></div><?php endif; ?></article><article class="admin-panel"><div class="admin-panel-heading"><h2>Booking status</h2><a class="admin-btn admin-btn-secondary" href="reports.php">Reports</a></div><div class="admin-chart"><?php foreach ($statusRows as $row): ?><div class="admin-bar"><span><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span><span class="admin-bar-track"><span class="admin-bar-fill" style="width:<?= min(100, (int) $row['total'] * 10) ?>%"></span></span><strong><?= (int) $row['total'] ?></strong></div><?php endforeach; ?></div></article></section><?php require __DIR__ . '/includes/footer.php'; ?>
