<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/config/database.php';

$databaseCenters = [];
$centerImages = [
    'Love Center' => 'images/hospital1.png',
    'Nephrology Center of Dumaguete City Dialysis, Inc.' => 'images/hospital2.png',
    'HemoCent' => 'images/hospital3.png',
];
try {
    $centerConnection = getMySqlConnection();
    $databaseCenters = $centerConnection->query("SELECT name, address, operating_hours, dialysis_types, image_path FROM dialysis_centers WHERE status = 'Active' ORDER BY name")->fetchAll();
} catch (Throwable $exception) {
    $databaseCenters = [];
}

if (!$databaseCenters) {
    $databaseCenters = array_map(
        static fn (array $center): array => [
            'name' => $center['name'],
            'address' => $center['address'],
            'operating_hours' => $center['operating_hours'],
            'dialysis_types' => $center['dialysis_types'],
            'image_path' => $center['image_path'] ?? null,
        ],
        getActiveFallbackCenters()
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Dialysis Hospitals | KATOC</title>
    <link rel="stylesheet" href="style.css?v=20260908-booking-layout">
    <script src="script.js?v=20260908-booking-input-layer" defer></script>
</head>
<body class="all-hospitals-page">
    <header class="hospitals-header">
        <a href="index.php" class="hospitals-brand">KATOC</a>
        <div class="hospitals-actions">
            <?php if ($user): ?>
                <a href="dashboard/dashboard.php">Dashboard</a>
                <a href="logout/logout.php">Log out</a>
            <?php else: ?>
                <a href="login/login.php">Log in</a>
                <a href="login/signup.php" class="hospitals-signup">Sign up</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="hospitals-main">
        <div class="hospitals-intro">
            <span class="section-eyebrow">KATOC CARE NETWORK</span>
            <h1>All dialysis hospitals</h1>
            <p>Browse available dialysis centers, compare their details, and choose the care location that fits your needs.</p>
            <a href="index.php#services" class="hospitals-back">Search centers <span>&rarr;</span></a>
        </div>

        <section class="centers-section hospitals-list" id="centers" aria-label="All dialysis hospitals">
            <div class="centers-header">
                <h2 class="centers-title">AVAILABLE CARE CENTERS</h2>
                <span class="hospitals-count"><?= count($databaseCenters) ?: 8 ?> centers</span>
            </div>
            <div class="centers-grid">
                <?php if ($databaseCenters): ?>
                    <?php foreach ($databaseCenters as $center): ?>
                        <?php $imagePath = $center['image_path'] ?: ($centerImages[$center['name']] ?? null); ?>
                        <article class="center-card">
                            <div class="card-image-wrapper<?= empty($imagePath) ? ' card-image-placeholder' : '' ?>">
                                <?php if ($imagePath): ?><img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($center['name'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?>Photo coming soon<?php endif; ?>
                                <div class="card-gradient-overlay"></div>
                            </div>
                            <div class="center-info-box">
                                <h3 class="center-name"><?= htmlspecialchars($center['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="center-location"><?= htmlspecialchars($center['address'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="center-hours"><?= htmlspecialchars($center['operating_hours'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="center-service"><?= htmlspecialchars($center['dialysis_types'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <button type="button" class="see-availability availability-trigger" data-center="<?= htmlspecialchars($center['name'], ENT_QUOTES, 'UTF-8') ?>" data-location="<?= htmlspecialchars($center['address'], ENT_QUOTES, 'UTF-8') ?>" data-hours="<?= htmlspecialchars($center['operating_hours'], ENT_QUOTES, 'UTF-8') ?>" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                <article class="center-card">
                    <div class="card-image-wrapper">
                        <img src="images/hospital1.png" alt="Love Center">
                        <div class="card-gradient-overlay"></div>
                    </div>
                    <div class="center-info-box">
                        <h3 class="center-name">Love Center</h3>
                        <p class="center-location">Dumaguete City, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Hemodialysis Available</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="Love Center" data-location="Dumaguete City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>

                <article class="center-card">
                    <div class="card-image-wrapper">
                        <img src="images/hospital2.png" alt="Nephrology Center">
                        <div class="card-gradient-overlay"></div>
                    </div>
                    <div class="center-info-box">
                        <h3 class="center-name">Nephrology Center of Dumaguete City Dialysis, Inc.</h3>
                        <p class="center-location">Dumaguete City, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Hemodialysis Available</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="Nephrology Center of Dumaguete City Dialysis, Inc." data-location="Dumaguete City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>

                <article class="center-card">
                    <div class="card-image-wrapper">
                        <img src="images/hospital3.png" alt="HemoCent">
                        <div class="card-gradient-overlay"></div>
                    </div>
                    <div class="center-info-box">
                        <h3 class="center-name">HemoCent</h3>
                        <p class="center-location">Valencia, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Hemodialysis Available</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="HemoCent" data-location="Valencia, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>

                <article class="center-card">
                    <div class="card-image-wrapper card-image-placeholder" aria-label="Hospital photo coming soon">Photo coming soon</div>
                    <div class="center-info-box">
                        <h3 class="center-name">Sibulan Dialysis Center</h3>
                        <p class="center-location">Sibulan, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Hemodialysis Available</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="Sibulan Dialysis Center" data-location="Sibulan, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>

                <article class="center-card">
                    <div class="card-image-wrapper card-image-placeholder" aria-label="Hospital photo coming soon">Photo coming soon</div>
                    <div class="center-info-box">
                        <h3 class="center-name">Bais Community Hospital</h3>
                        <p class="center-location">Bais City, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Hemodialysis Available</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="Bais Community Hospital" data-location="Bais City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>

                <article class="center-card">
                    <div class="card-image-wrapper card-image-placeholder" aria-label="Hospital photo coming soon">Photo coming soon</div>
                    <div class="center-info-box">
                        <h3 class="center-name">Tanjay Renal Care Center</h3>
                        <p class="center-location">Tanjay City, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Hemodialysis Available</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="Tanjay Renal Care Center" data-location="Tanjay City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>

                <article class="center-card">
                    <div class="card-image-wrapper card-image-placeholder" aria-label="Hospital photo coming soon">Photo coming soon</div>
                    <div class="center-info-box">
                        <h3 class="center-name">Bayawan Medical Center</h3>
                        <p class="center-location">Bayawan City, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Hemodialysis Available</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="Bayawan Medical Center" data-location="Bayawan City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>

                <article class="center-card">
                    <div class="card-image-wrapper card-image-placeholder" aria-label="Hospital photo coming soon">Photo coming soon</div>
                    <div class="center-info-box">
                        <h3 class="center-name">Dumaguete Kidney Institute</h3>
                        <p class="center-location">Dumaguete City, Negros Oriental</p>
                        <p class="center-hours">8:00 am - 5:00 pm</p>
                        <p class="center-service">Peritoneal Dialysis Support</p>
                    </div>
                    <button type="button" class="see-availability availability-trigger" data-center="Dumaguete Kidney Institute" data-location="Dumaguete City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">See Availability <span>&rarr;</span></button>
                </article>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div class="availability-modal" id="availability-modal" aria-hidden="true">
        <div class="availability-modal-backdrop" data-close-availability-modal="true"></div>
        <div class="availability-modal-card" role="dialog" aria-modal="true" aria-labelledby="availability-modal-title">
            <button type="button" class="availability-close" aria-label="Close availability dialog" data-close-availability-modal="true">&times;</button>
            <div class="availability-icon" aria-hidden="true">+</div>
            <p class="availability-label" id="availability-modal-label">Member access required</p>
            <h3 id="availability-modal-title">Sign in or sign up first</h3>
            <p class="availability-copy" id="availability-modal-copy">Please log in or create an account to check dialysis center availability and book an appointment.</p>
            <div class="availability-details" id="availability-details" hidden>
                <p><strong>Location</strong> <span id="availability-location"></span></p>
                <p><strong>Operating Hours</strong> <span id="availability-hours"></span></p>
                <p class="availability-open-status">Availability is currently open for requests.</p>
            </div>
            <form class="availability-booking-form" id="availability-booking-form" action="booking/create.php" method="post" hidden>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="center" id="availability-booking-center">
                <input type="hidden" name="location" id="availability-booking-location">
                <input type="hidden" name="hours" id="availability-booking-hours">
                <div class="availability-booking-main">
                <section class="booking-step" id="availability-booking-details-step">
                    <h4>Appointment Details</h4>
                <fieldset>
                    <legend>Dialysis Type</legend>
                    <label><input type="radio" name="dialysis_type" value="Hemodialysis" required> Hemodialysis</label>
                    <label><input type="radio" name="dialysis_type" value="Peritoneal Dialysis"> Peritoneal Dialysis</label>
                    <label><input type="radio" name="dialysis_type" value="Home Hemodialysis"> Home Hemodialysis</label>
                    <label><input type="radio" name="dialysis_type" value="Continuous Ambulatory Peritoneal Dialysis"> Continuous Ambulatory Peritoneal Dialysis</label>
                    <label><input type="radio" name="dialysis_type" value="Automated Peritoneal Dialysis"> Automated Peritoneal Dialysis</label>
                </fieldset>
                <label for="availability-booking-date">Preferred date</label>
                <input type="date" name="date" id="availability-booking-date" required>
                <label for="availability-booking-session">Preferred Session</label>
                <select name="session" id="availability-booking-session" required>
                    <option value="">Select a session</option>
                    <option value="Morning">Morning</option>
                    <option value="Afternoon">Afternoon</option>
                    <option value="Evening">Evening</option>
                </select>
                <div class="availability-slot-heading"><strong>Preferred Time</strong><span>Available time slots</span></div>
                <div class="availability-time-slots" id="availability-time-slots" role="group" aria-label="Available appointment times"></div>
                <input type="hidden" name="time" id="availability-booking-time" required>
                <div class="availability-slot-legend" aria-label="Time slot status"><span><i class="is-available"></i> Available</span><span><i class="is-full"></i> Fully booked</span><span><i class="is-selected"></i> Selected</span></div>
                </section>
                <section class="booking-step" id="availability-patient-step" hidden>
                    <h4>Patient Information</h4>
                    <label for="availability-patient-name">Full Name</label>
                    <input type="text" name="patient_name" id="availability-patient-name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ .'-]+" required>
                    <label for="availability-patient-contact">Contact Number</label>
                    <input type="tel" name="patient_contact" id="availability-patient-contact" maxlength="30" pattern="[0-9+() .-]{7,30}" placeholder="Enter contact number" required>
                    <label for="availability-patient-email">Email Address</label>
                    <input type="email" name="patient_email" id="availability-patient-email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="254" required>
                    <h4>Emergency Contact</h4>
                    <label for="availability-emergency-name">Name</label>
                    <input type="text" name="emergency_name" id="availability-emergency-name" minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ .'-]+" required>
                    <label for="availability-emergency-contact">Contact Number</label>
                    <input type="tel" name="emergency_contact" id="availability-emergency-contact" maxlength="30" pattern="[0-9+() .-]{7,30}" required>
                    <label for="availability-notes">Additional Notes <span>(optional)</span></label>
                    <textarea name="notes" id="availability-notes" maxlength="1000" placeholder="Enter any important information you would like the dialysis center to know."></textarea>
                    <button type="button" class="availability-primary" id="availability-summary-button">Review Appointment</button>
                </section>
                <section class="booking-step booking-summary" id="availability-summary-step" hidden>
                    <h4>Appointment Summary</h4>
                    <p><strong>Dialysis Center:</strong> <span data-summary="center"></span></p>
                    <p><strong>Location:</strong> <span data-summary="location"></span></p>
                    <p><strong>Dialysis Type:</strong> <span data-summary="dialysis_type"></span></p>
                    <p><strong>Date:</strong> <span data-summary="date"></span></p>
                    <p><strong>Session:</strong> <span data-summary="session"></span></p>
                    <p><strong>Time:</strong> <span data-summary="time"></span></p>
                    <p><strong>Patient:</strong> <span data-summary="patient_name"></span></p>
                    <p><strong>Contact:</strong> <span data-summary="patient_contact"></span></p>
                    <div class="booking-summary-actions">
                        <button type="button" class="availability-cancel" id="availability-back-button">Back / Edit</button>
                        <button type="submit" class="availability-primary">Book Appointment</button>
                    </div>
                </section>
                </div>
                <aside class="availability-booking-aside" aria-label="Booking details">
                    <span class="availability-aside-eyebrow">BOOKING DETAILS</span>
                    <h4 id="availability-aside-center">Dialysis Center</h4>
                    <dl>
                        <div><dt>Location</dt><dd id="availability-aside-location" data-summary="location"></dd></div>
                        <div><dt>Operating hours</dt><dd id="availability-aside-hours" data-summary="hours"></dd></div>
                        <div><dt>Dialysis type</dt><dd data-summary="dialysis_type">Not selected</dd></div>
                        <div><dt>Date</dt><dd data-summary="date">Not selected</dd></div>
                        <div><dt>Session</dt><dd data-summary="session">Not selected</dd></div>
                        <div><dt>Time</dt><dd data-summary="time">Not selected</dd></div>
                        <div><dt>Patient</dt><dd data-summary="patient_name">Not entered</dd></div>
                    </dl>
                    <p class="availability-aside-note">Your selected appointment details will appear here.</p>
                    <button type="button" class="availability-primary availability-aside-action" id="availability-review-button">Continue to review</button>
                </aside>
            </form>
            <div class="availability-actions" id="availability-actions">
                <button type="button" class="availability-cancel" data-close-availability-modal="true">Maybe later</button>
                <?php if (!$user): ?>
                    <a href="login/login.php" class="availability-secondary">Log in</a>
                    <a href="login/signup.php" class="availability-primary">Create account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
