<?php $hospitalAdmin = $hospitalAdmin ?? requireHospitalAdmin(); ?>
<aside class="admin-sidebar" id="hospital-sidebar">
    <a class="admin-brand" href="dashboard.php"><img src="../images/katoc-logo.png" alt="KATOC"><span>HOSPITAL</span></a>
    <nav class="admin-nav" aria-label="Hospital Admin navigation">
        <a class="admin-nav-link" href="dashboard.php"><span class="admin-nav-icon">grid</span>Dashboard</a>
        <a class="admin-nav-link" href="bookings.php"><span class="admin-nav-icon">clipboard</span>Bookings</a>
        <a class="admin-nav-link" href="patients.php"><span class="admin-nav-icon">users</span>Patients</a>
        <a class="admin-nav-link" href="dashboard.php"><span class="admin-nav-icon">calendar</span>Schedules</a>
    </nav>
    <a class="admin-logout" href="logout.php">Logout</a>
</aside>