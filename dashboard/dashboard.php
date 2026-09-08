<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../login/login.php');
    exit;
}

$user = $_SESSION['user'];
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/request.php';

$appointments = [];
$upcomingAppointments = [];
$pastAppointments = [];
$dashboardMessage = requestText($_GET, 'message', 300);
try {
    $appointments = getMySqlAppointmentsForUser((int) $user['id']);
    $today = date('Y-m-d');
    foreach ($appointments as $appointment) {
        $isPast = $appointment['appointment_date'] < $today
            || in_array($appointment['status'], ['Completed', 'Cancelled'], true);

        if ($isPast) {
            $pastAppointments[] = $appointment;
        } elseif ($appointment['appointment_date'] >= $today && $appointment['status'] !== 'Cancelled') {
            $upcomingAppointments[] = $appointment;
        }
    }
} catch (PDOException $exception) {
    $dashboardMessage = 'Appointments are temporarily unavailable. Please try again later.';
}
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
            <a class="sidebar-link" href="dashboard.php#appointments">Appointments</a>
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

        <section class="dashboard-appointments" id="appointments">
            <div class="dashboard-section-heading">
                <div>
                    <span class="dashboard-eyebrow">APPOINTMENTS</span>
                    <h2>Your appointment requests</h2>
                </div>
            </div>
            <?php if ($dashboardMessage !== ''): ?>
                <p class="dashboard-message" role="status"><?= htmlspecialchars($dashboardMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php foreach ([
                [
                    'title' => 'Upcoming appointments',
                    'items' => $upcomingAppointments,
                    'upcoming' => true,
                    'emptyTitle' => 'No upcoming appointments',
                    'emptyText' => "You don't have any upcoming dialysis appointments.",
                ],
                [
                    'title' => 'Past appointments',
                    'items' => $pastAppointments,
                    'upcoming' => false,
                    'emptyTitle' => 'No past appointments',
                    'emptyText' => 'Your completed or previous appointments will appear here.',
                ],
            ] as $appointmentGroup): ?>
                <div class="appointment-group <?= $appointmentGroup['upcoming'] ? 'appointment-group-upcoming' : 'appointment-group-past' ?>">
                    <h3 class="appointment-group-title"><?= htmlspecialchars($appointmentGroup['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if (!$appointmentGroup['items']): ?>
                        <div class="dashboard-empty appointment-empty-state">
                            <strong><?= htmlspecialchars($appointmentGroup['emptyTitle'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <p><?= htmlspecialchars($appointmentGroup['emptyText'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($appointmentGroup['upcoming']): ?>
                                <a class="appointment-action" href="../index.php#services">Find a Dialysis Center</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="appointment-list">
                    <?php foreach ($appointmentGroup['items'] as $appointment): ?>
                        <?php $statusClass = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $appointment['status'])); ?>
                        <article class="appointment-card">
                            <div class="appointment-card-header">
                                <div>
                                    <span class="appointment-reference"><?= htmlspecialchars($appointment['appointment_reference'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <h3><?= htmlspecialchars($appointment['center_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                </div>
                                <span class="appointment-status status-<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($appointment['status'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="appointment-card-details">
                                <p><strong>Location</strong><span><?= htmlspecialchars($appointment['center_location'], ENT_QUOTES, 'UTF-8') ?></span></p>
                                <p><strong>Date</strong><span><?= htmlspecialchars(date('F j, Y', strtotime($appointment['appointment_date'])), ENT_QUOTES, 'UTF-8') ?></span></p>
                                <p><strong>Time</strong><span><?= htmlspecialchars(date('g:i A', strtotime($appointment['appointment_time'])), ENT_QUOTES, 'UTF-8') ?></span></p>
                                <p><strong>Dialysis Type</strong><span><?= htmlspecialchars($appointment['dialysis_type'], ENT_QUOTES, 'UTF-8') ?></span></p>
                                <p><strong>Patient Name</strong><span><?= htmlspecialchars($appointment['patient_name'], ENT_QUOTES, 'UTF-8') ?></span></p>
                            </div>
                            <details class="appointment-details">
                                <summary>View Details</summary>
                                <p>Patient: <?= htmlspecialchars($appointment['patient_name'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p>Contact: <?= htmlspecialchars($appointment['contact_number'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p>Session: <?= htmlspecialchars($appointment['session'], ENT_QUOTES, 'UTF-8') ?></p>
                            </details>
                            <?php if ($appointmentGroup['upcoming'] && in_array($appointment['status'], ['Pending Confirmation', 'Confirmed'], true)): ?>
                                <div class="appointment-actions">
                                    <form method="post" action="appointment_action.php" class="cancel-appointment-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="appointment-action appointment-action-danger">Cancel Appointment</button>
                                    </form>
                                    <form method="post" action="appointment_action.php" class="appointment-reschedule-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                        <input type="hidden" name="action" value="reschedule">
                                        <input type="date" name="date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($appointment['appointment_date'], ENT_QUOTES, 'UTF-8') ?>" required>
                                        <select name="time" required>
                                            <?php foreach (['08:00', '10:00', '13:00', '15:00'] as $time): ?>
                                                <option value="<?= $time ?>" <?= substr($appointment['appointment_time'], 0, 5) === $time ? 'selected' : '' ?>><?= date('g:i A', strtotime($time)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="appointment-action">Reschedule</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                            </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
    <div class="cancel-modal" id="cancel-modal" aria-hidden="true">
        <div class="cancel-modal-backdrop" data-close-cancel-modal="true"></div>
        <div class="cancel-modal-card" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
            <h2 id="cancel-modal-title">Cancel Appointment?</h2>
            <p>Are you sure you want to cancel this dialysis appointment?</p>
            <div class="cancel-modal-actions">
                <button type="button" class="appointment-action" data-close-cancel-modal="true">Keep Appointment</button>
                <button type="button" class="appointment-action appointment-action-danger" id="confirm-cancel-appointment">Cancel Appointment</button>
            </div>
        </div>
    </div>
    <script>
        (() => {
            const header = document.querySelector('.dashboard-header');
            if (!header) {
                return;
            }

            let previousScrollY = window.scrollY || 0;

            const updateHeaderState = () => {
                const currentScrollY = window.scrollY || 0;
                const shouldHideHeader = currentScrollY > 30 && currentScrollY > previousScrollY;

                header.classList.toggle('is-hidden', shouldHideHeader);
                header.style.pointerEvents = shouldHideHeader ? 'none' : 'auto';
                document.body.classList.toggle('dashboard-header-hidden', shouldHideHeader);
                previousScrollY = currentScrollY;
            };

            window.addEventListener('scroll', updateHeaderState, { passive: true });
        })();
    </script>
</body>
</html>
