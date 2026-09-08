<aside class="admin-sidebar" id="admin-sidebar">
    <a class="admin-brand" href="dashboard.php"><img src="../images/katoc-logo.png" alt="KATOC"><span>ADMIN</span></a>
    <nav class="admin-nav" aria-label="Admin navigation">
        <?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
        <?php foreach ([
            'dashboard.php' => ['Dashboard', 'grid'],
            'centers.php' => ['Dialysis Centers', 'plus'],
            'hospital_admins.php' => ['Hospital Admins', 'users'],
            'schedules.php' => ['Availability & Schedules', 'calendar'],
            'bookings.php' => ['Bookings', 'clipboard'],
            'users.php' => ['Patients / Users', 'users'],
            'notifications.php' => ['Notifications', 'bell'],
            'reports.php' => ['Reports', 'chart'],
            'settings.php' => ['Settings', 'settings'],
        ] as $page => [$label, $icon]): ?>
            <a class="admin-nav-link <?= $currentPage === $page ? 'is-active' : '' ?>" href="<?= htmlspecialchars($page, ENT_QUOTES, 'UTF-8') ?>"><span class="admin-nav-icon" aria-hidden="true"><?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </nav>
    <a class="admin-logout" href="logout.php">Logout</a>
</aside>
