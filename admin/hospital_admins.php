<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$adminUser = requireSuperAdmin();
$adminPageTitle = 'Hospital Admins';
$hospitalAdmins = $adminConnection->query(
    "SELECT u.id, u.name, u.email, u.account_status, c.id AS hospital_id, c.name AS hospital_name
     FROM hospital_admins ha
     INNER JOIN users u ON u.id = ha.user_id
     INNER JOIN dialysis_centers c ON c.id = ha.hospital_id
     ORDER BY u.name"
)->fetchAll();
$hospitals = $adminConnection->query("SELECT id, name FROM dialysis_centers WHERE status = 'Active' ORDER BY name")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="admin-grid">
    <section class="admin-panel">
        <div class="admin-panel-heading"><h2>Create Hospital Admin</h2></div>
        <form method="post" action="actions/create_hospital_admin.php" class="admin-form-grid">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="admin-field">Name<input name="name" maxlength="100" required></label>
            <label class="admin-field">Email<input type="email" name="email" maxlength="191" required></label>
            <label class="admin-field">Hospital<select name="hospital_id" required><option value="">Select hospital</option><?php foreach ($hospitals as $hospital): ?><option value="<?= (int) $hospital['id'] ?>"><?= htmlspecialchars($hospital['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label class="admin-field">Temporary password<input type="password" name="password" minlength="8" required></label>
            <button class="admin-btn" type="submit">Create Hospital Admin</button>
        </form>
    </section>
</div>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Hospital</th><th>Status</th><th>Action</th></tr></thead>
        <tbody><?php foreach ($hospitalAdmins as $hospitalAdmin): ?><tr><td><?= htmlspecialchars($hospitalAdmin['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($hospitalAdmin['email'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($hospitalAdmin['hospital_name'], ENT_QUOTES, 'UTF-8') ?></td><td><span class="admin-status admin-status-<?= strtolower($hospitalAdmin['account_status']) ?>"><?= htmlspecialchars($hospitalAdmin['account_status'], ENT_QUOTES, 'UTF-8') ?></span></td><td><form method="post" action="actions/update_hospital_admin.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="user_id" value="<?= (int) $hospitalAdmin['id'] ?>"><input type="hidden" name="status" value="<?= $hospitalAdmin['account_status'] === 'Active' ? 'Inactive' : 'Active' ?>"><button class="admin-btn admin-btn-secondary" type="submit"><?= $hospitalAdmin['account_status'] === 'Active' ? 'Deactivate' : 'Activate' ?></button></form></td></tr><?php endforeach; ?></tbody>
    </table>
    <?php if (!$hospitalAdmins): ?><p class="admin-empty">No hospital admins created yet.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>