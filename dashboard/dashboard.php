<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../login/login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | KATOC</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body class="dashboard-page">
    <header class="dashboard-header">
        <a href="../index.php" class="dashboard-header-phrase">Your care starts here.</a>
        <div class="dashboard-account">
            <button class="dashboard-user" type="button" aria-label="Open dashboard menu" aria-controls="dashboard-sidebar" aria-expanded="false" title="Open dashboard menu">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21c.7-4 3.3-6 8-6s7.3 2 8 6"></path>
                </svg>
            </button>
        </div>
    </header>

    <aside class="dashboard-sidebar" id="dashboard-sidebar" aria-hidden="true">
        <a class="dashboard-sidebar-brand" href="../index.php">
            <img src="../images/katoc-logo.png" alt="KATOC">
        </a>
        <div class="sidebar-heading">
            <div>
                <span class="dashboard-eyebrow">PATIENT MENU</span>
                <h2><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            </div>
            <button class="sidebar-close" type="button" aria-label="Close dashboard menu">&times;</button>
        </div>
        <nav aria-label="Dashboard navigation">
            <a class="sidebar-link is-active" href="dashboard.php">Dashboard</a>
            <a class="sidebar-link" href="../index.php#services">Find a center</a>
            <a class="sidebar-link" href="../index.php#appointment">Appointments</a>
            <a class="sidebar-link" href="../index.php#kidney-care">Kidney care guide</a>
        </nav>
        <a class="sidebar-logout" href="../logout/logout.php">Log out</a>
    </aside>
    <div class="sidebar-backdrop" aria-hidden="true"></div>

    <main class="dashboard-main">
        <section class="dashboard-welcome">
            <div>
                <span class="dashboard-eyebrow">PATIENT DASHBOARD</span>
                <h1>Welcome back, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>.</h1>
                <p>Find a dialysis center, review care information, and manage your next step with KATOC.</p>
            </div>
            <img src="../images/kidney.png" alt="Kidney illustration" class="dashboard-kidney">
        </section>

        <section class="dashboard-grid">
            <a href="../index.php#services" class="dashboard-card">
                <span class="dashboard-card-icon">+</span>
                <h2>Find a center</h2>
                <p>Search dialysis centers near your location.</p>
                <span class="dashboard-card-link">Explore centers &rarr;</span>
            </a>
            <a href="../index.php#appointment" class="dashboard-card">
                <span class="dashboard-card-icon">&#128197;</span>
                <h2>Request an appointment</h2>
                <p>Choose a center and prepare your next care request.</p>
                <span class="dashboard-card-link">Start request &rarr;</span>
            </a>
            <a href="../index.php#kidney-care" class="dashboard-card">
                <span class="dashboard-card-icon">i</span>
                <h2>Kidney care guide</h2>
                <p>Learn about kidney disease and dialysis options.</p>
                <span class="dashboard-card-link">Read guide &rarr;</span>
            </a>
        </section>
    </main>
    <script src="dashboard.js"></script>
</body>
</html>
