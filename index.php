<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
// KATOC landing page.
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>KATOC | Kidney Access and Treatment Operation Center</title>

    <link rel="stylesheet" href="style.css?v=20260908-booking-layout">
</head>

<body class="<?= $user ? 'has-dashboard-sidebar' : '' ?>">

    <!-- =========================
         KATOC HERO SECTION
    ========================== -->

    <section class="hero" id="hero-home">

        <!-- NAVIGATION -->
        <nav class="navbar">

            <!-- KATOC ICON -->
            <a href="#hero-home" class="nav-logo">
                <img src="images/katoc-icon.png" alt="KATOC">
            </a>

            <!-- NAVIGATION LINKS -->
            <div class="nav-links">
                <a href="#hero-home">Home</a>
                <a href="#why-katoc">Why KATOC</a>
                <a href="about/about.php">About</a>
            </div>

            <!-- LOGIN / SIGN UP -->
            <div class="nav-account">
                <?php if ($user): ?>
                    <a href="dashboard/dashboard.php" class="nav-user" aria-label="Open dashboard" title="Open dashboard">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 21c.7-4 3.3-6 8-6s7.3 2 8 6"></path>
                        </svg>
                    </a>
                    <a href="dashboard/dashboard.php" class="login">Dashboard</a>
                <?php else: ?>
                    <a href="login/login.php" class="login">Log in</a>
                    <a href="login/signup.php" class="signup">Sign up</a>
                <?php endif; ?>
            </div>

        </nav>

        <?php if ($user): ?>
            <aside class="home-dashboard-sidebar" aria-label="Dashboard navigation">
                <div class="home-sidebar-heading">
                    <span class="home-sidebar-eyebrow">PATIENT MENU</span>
                    <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <a class="home-sidebar-link is-active" href="dashboard/dashboard.php" aria-current="page">Dashboard</a>
                <a class="home-sidebar-link" href="#services" data-section="services">Find a center</a>
                <a class="home-sidebar-link" href="#appointment" data-section="appointment">Appointments</a>
                <a class="home-sidebar-link" href="#kidney-care" data-section="kidney-care">Kidney care guide</a>
                <a class="home-sidebar-logout" href="logout/logout.php">Log out</a>
            </aside>
        <?php endif; ?>

        <!-- =========================
             HERO CONTENT
        ========================== -->

        <div class="hero-content">

            <!-- LEFT WHITE CARD -->
            <div class="katoc-info">
                <img src="images/katoc-logo.png" alt="KATOC Logo" class="katoc-logo">
                <p class="description">
                    <span>KATOC</span> is a digital platform that helps patients and
                    their families easily locate dialysis centers and access essential
                    information about nearby facilities.
                </p>
                <div class="hero-buttons">
                    <a href="#services" class="services-btn">
                        <span>Our Services</span>
                        <strong>→</strong>
                    </a>
                    <a href="#about" class="about-btn">
                        <span>About Us</span>
                        <strong>→</strong>
                    </a>
                </div>
            </div>

            <!-- RIGHT MEDICAL VISUAL -->
            <div class="medical-visual">
                <img src="images/dialysis.png" alt="Dialysis machine" class="dialysis-image">
                <div class="red-overlay"></div>
            </div>

            <!-- OVERLAY IMAGES -->
            <img src="images/syringe.png" alt="Syringe" class="syringe">
            <img src="images/kidney.png" alt="Kidney" class="kidney">

        </div>

    </section>

    <section class="center-search-section" id="services">
        <div class="search-copy">
            <span class="section-eyebrow">FIND CARE NEAR YOU</span>
            <h1>Find a Dialysis Center Near You</h1>
            <p>Search for dialysis centers based on your location and treatment needs.</p>
        </div>
        <form class="center-search-form" id="center-search-form">
            <label class="search-field">
                <span class="search-field-icon">+</span>
                <input type="search" id="center-search-input" placeholder="Search by city or location..." aria-label="Search by city or location">
            </label>
            <button type="button" class="location-button" id="use-location-button">Use my location</button>
            <button type="submit" class="search-submit">Find centers <span>&rarr;</span></button>
        </form>
        <div class="search-filters" aria-label="Center search filters">
            <button type="button" class="filter-chip" data-filter="location" aria-pressed="false">Location</button>
            <button type="button" class="filter-chip" data-filter="facility" aria-pressed="false">Center or hospital</button>
            <button type="button" class="filter-chip" data-filter="type" aria-pressed="false">Dialysis type</button>
            <button type="button" class="filter-chip" data-filter="hours" aria-pressed="false">Operating hours</button>
        </div>
        <p class="search-status" id="search-status" role="status"></p>
    </section>

    <section class="features-container" id="about">
        <div class="feature-card">
            <div class="card-header">
                <h3>FIND A CENTER</h3>
            </div>
            <div class="card-body">
                <h4>Find Dialysis Centers Near You</h4>
                <p>Search and locate dialysis centers based on your location.</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="card-header">
                <h3>CENTER INFORMATION</h3>
            </div>
            <div class="card-body">
                <h4>Know Before You Go</h4>
                <p>View important information about dialysis centers, including location, contact details, and available services.</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="card-header">
                <h3>EASY NAVIGATION</h3>
            </div>
            <div class="card-body">
                <h4>Get There With Ease</h4>
                <p>Use location and map features to help you find your way to your chosen dialysis center.</p>
            </div>
        </div>

        <div class="feature-card">
            <div class="card-header">
                <h3>KIDNEY CARE</h3>
            </div>
            <div class="card-body">
                <h4>Learn About Dialysis</h4>
                <p>Access helpful information and resources about dialysis and kidney care.</p>
            </div>
        </div>
    </section>

    <section class="how-it-works-section">
        <div class="section-heading-row">
            <div>
                <span class="section-eyebrow">A SIMPLE WAY TO START</span>
                <h2>How KATOC Works</h2>
            </div>
            <p>Everything you need to move from searching to planning your care.</p>
        </div>
        <div class="workflow-grid">
            <article class="workflow-card">
                <span class="workflow-number">01</span>
                <h3>Find a Center</h3>
                <p>Search for dialysis centers near your location.</p>
            </article>
            <article class="workflow-card">
                <span class="workflow-number">02</span>
                <h3>Check Information</h3>
                <p>Compare services, hours, location, and center details.</p>
            </article>
            <article class="workflow-card">
                <span class="workflow-number">03</span>
                <h3>Request an Appointment</h3>
                <p>Choose a center and take the next step in your care.</p>
            </article>
        </div>
    </section>

    <section class="locator-section" id="locator">
        <div class="locator-header">
            <div>
                <span class="section-eyebrow">DIALYSIS CENTERS NEAR YOU</span>
                <h2>Locate care with confidence</h2>
            </div>
            <div class="locator-actions">
                <button type="button" class="outline-action map-open-action" id="open-map-button">View map <span aria-hidden="true">↗</span></button>
                <a href="#centers" class="outline-action">View all centers <span>&rarr;</span></a>
            </div>
        </div>
        <div class="locator-map" aria-label="Map showing dialysis center locations">
            <iframe
                title="Map of dialysis centers in Dumaguete"
                src="https://www.openstreetmap.org/export/embed.html?bbox=123.285%2C9.285%2C123.32%2C9.325&amp;layer=mapnik&amp;marker=9.307%2C123.305"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

    <dialog class="map-dialog" id="map-dialog" aria-labelledby="map-dialog-title">
        <div class="map-dialog-header">
            <div>
                <span class="section-eyebrow">DUMAGUETE CARE NETWORK</span>
                <h2 id="map-dialog-title">Dialysis centers near you</h2>
            </div>
            <button type="button" class="map-dialog-close" id="close-map-button" aria-label="Close map">&times;</button>
        </div>
        <div class="map-frame">
            <iframe
                title="Map of dialysis centers in Dumaguete"
                src="https://www.openstreetmap.org/export/embed.html?bbox=123.285%2C9.285%2C123.32%2C9.325&amp;layer=mapnik&amp;marker=9.307%2C123.305"
                loading="lazy"></iframe>
        </div>
        <a class="map-external-link" href="https://www.openstreetmap.org/?mlat=9.307&amp;mlon=123.305#map=14/9.307/123.305" target="_blank" rel="noopener noreferrer">Open in a new tab <span aria-hidden="true">&rarr;</span></a>
    </dialog>

    <section class="centers-section" id="centers">
        <img src="images/blood-cells.png" alt="" class="bg-cells">

        <div class="centers-header">
            <h2 class="centers-title">FIND YOUR MOST SUITABLE PLACE, WE CARE FOR YOUR HEALTH</h2>
            <a href="all-hospitals.php" class="view-all-btn">
                VIEW ALL <span>&rarr;</span>
            </a>
        </div>

        <div class="centers-grid">
            <div class="center-card" data-location="dumaguete city negros oriental" data-facility="center" data-type="hemodialysis" data-hours="open">
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
                <button type="button" class="see-availability availability-trigger" data-center="Love Center" data-location="Dumaguete City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">
                    See Availability <span>&rarr;</span>
                </button>
            </div>

            <div class="center-card" data-location="dumaguete city negros oriental" data-facility="hospital center" data-type="hemodialysis" data-hours="open">
                <div class="card-image-wrapper">
                    <img src="images/hospital2.png" alt="Nephrology Center">
                    <div class="card-gradient-overlay"></div>
                </div>
                <div class="center-info-box">
                    <h3 class="center-name">NEPHROLOGY CENTER OF DUMAGUETE CITY DIALYSIS, INC.</h3>
                    <p class="center-location">Dumaguete City, Negros Oriental</p>
                    <p class="center-hours">8:00 am - 5:00 pm</p>
                    <p class="center-service">Hemodialysis Available</p>
                </div>
                <button type="button" class="see-availability availability-trigger" data-center="Nephrology Center of Dumaguete City Dialysis, Inc." data-location="Dumaguete City, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">
                    See Availability <span>&rarr;</span>
                </button>
            </div>

            <div class="center-card" data-location="valencia negros oriental" data-facility="center" data-type="hemodialysis" data-hours="open">
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
                <button type="button" class="see-availability availability-trigger" data-center="HemoCent" data-location="Valencia, Negros Oriental" data-hours="8:00 am - 5:00 pm" data-logged-in="<?= $user ? 'true' : 'false' ?>">
                    See Availability <span>&rarr;</span>
                </button>
            </div>

        </div>
    </section>

    <div class="availability-modal" id="availability-modal" aria-hidden="true">
        <div class="availability-modal-backdrop" data-close-availability-modal="true"></div>
        <div class="availability-modal-card" role="dialog" aria-modal="true" aria-labelledby="availability-modal-title">
            <button type="button" class="availability-close" aria-label="Close sign-up prompt" data-close-availability-modal="true">&times;</button>
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

    <!-- =========================
         KIDNEY HEALTH INFORMATION
    ========================== -->
    <section class="kidney-care-section" id="kidney-care">
        <div class="kidney-care-heading">
            <span class="section-eyebrow">KIDNEY CARE GUIDE</span>
            <h2>Understand kidney disease and dialysis</h2>
        </div>

        <div class="kidney-care-cards">
            <article class="care-card">
                <div class="care-card-icon">+</div>
                <div class="care-card-heading">
                    <h3>What is kidney disease?</h3>
                </div>
                <p>
                    Kidney disease happens when the kidneys cannot properly remove waste
                    and extra fluid from the blood. Early checkups can help protect kidney function.
                </p>
                <a href="#kidney-care" class="care-link">Learn more <span>&rarr;</span></a>
            </article>

            <article class="care-card">
                <div class="care-card-icon">!</div>
                <div class="care-card-heading">
                    <h3>Know the warning signs</h3>
                </div>
                <p>
                    Swelling, changes in urination, tiredness, nausea, or shortness of breath
                    may need medical attention. Talk to a healthcare professional about any concerns.
                </p>
                <a href="#kidney-care" class="care-link">Learn more <span>&rarr;</span></a>
            </article>

            <article class="care-card">
                <div class="care-card-icon">✓</div>
                <div class="care-card-heading">
                    <h3>Preparing for dialysis</h3>
                </div>
                <p>
                    Ask about treatment schedules, center services, transportation, and what to
                    bring. Your care team can guide you through every step of your treatment plan.
                </p>
                <a href="#centers" class="care-link">Find a center <span>&rarr;</span></a>
            </article>
        </div>
    </section>

    <section class="dialysis-options-section" id="dialysis-options">
        <div class="section-heading-row options-heading">
            <div>
                <span class="section-eyebrow">KNOW YOUR OPTIONS</span>
                <h2>Types of Dialysis</h2>
            </div>
            <p>Learn the basics and discuss the best treatment approach with your healthcare team.</p>
        </div>
        <div class="options-grid">
            <article class="option-card">
                <span class="option-label">01 / MACHINE-BASED</span>
                <h3>Hemodialysis</h3>
                <p>Blood passes through a filter, called a dialyzer, to remove waste and extra fluid. It is often provided at a center several times each week.</p>
                <a href="#kidney-care" class="text-action">Learn about care <span>&rarr;</span></a>
            </article>
            <article class="option-card option-card-light">
                <span class="option-label">02 / HOME-BASED</span>
                <h3>Peritoneal Dialysis</h3>
                <p>A cleansing fluid uses the lining of the abdomen to remove waste and extra fluid. It can be done manually or with a machine at home.</p>
                <a href="#kidney-care" class="text-action">Learn about care <span>&rarr;</span></a>
            </article>
            <article class="option-card option-card-soft">
                <span class="option-label">03 / FLEXIBLE SCHEDULE</span>
                <h3>Home Hemodialysis</h3>
                <p>Hemodialysis performed at home with training and support from a care team. Some schedules use shorter or more frequent treatments.</p>
                <a href="#kidney-care" class="text-action">Explore home care <span>&rarr;</span></a>
            </article>
            <article class="option-card option-card-light">
                <span class="option-label">04 / HOSPITAL CARE</span>
                <h3>Continuous Renal Replacement Therapy</h3>
                <p>CRRT is a slower, continuous form of dialysis used in hospitals for some critically ill patients who need close monitoring.</p>
                <a href="#kidney-care" class="text-action">Ask your care team <span>&rarr;</span></a>
            </article>
        </div>
        <div class="dialysis-help-panel">
            <div>
                <span class="option-label">BEFORE YOU DECIDE</span>
                <h3>Choose with your care team</h3>
                <p>The right option depends on your health, schedule, home support, access needs, and personal preferences.</p>
            </div>
            <ul>
                <li>Where will treatment take place?</li>
                <li>How often and how long will treatment be?</li>
                <li>What training, supplies, and support are needed?</li>
            </ul>
        </div>
    </section>

    <section class="why-katoc-section" id="why-katoc">
        <div class="why-katoc-heading">
            <span class="section-eyebrow">WHY PATIENTS USE KATOC</span>
            <h2>Care information, made easier to reach.</h2>
        </div>
        <div class="benefits-grid">
            <article class="benefit-item"><span class="benefit-icon">+</span><h3>Easy to Find</h3><p>Locate dialysis centers based on your location.</p></article>
            <article class="benefit-item"><span class="benefit-icon">i</span><h3>Reliable Information</h3><p>Access important center details in one place.</p></article>
            <article class="benefit-item"><span class="benefit-icon">→</span><h3>Convenient Access</h3><p>Request appointments without searching everywhere.</p></article>
            <article class="benefit-item"><span class="benefit-icon">♥</span><h3>Patient-Focused</h3><p>Designed to make finding dialysis care simpler.</p></article>
        </div>
    </section>

    <section class="appointment-cta-section" id="appointment">
        <div>
            <span class="section-eyebrow">YOUR NEXT STEP</span>
            <h2>Ready to find your dialysis care?</h2>
            <p>Find a center, check its services, and request an appointment with ease.</p>
        </div>
        <div class="appointment-actions">
            <a href="#centers" class="primary-action">Find a center <span>&rarr;</span></a>
            <a href="#appointment" class="secondary-action">Request an appointment <span>&rarr;</span></a>
        </div>
    </section>

    <section class="faq-section" id="faq">
        <div class="faq-heading">
            <span class="section-eyebrow">NEED TO KNOW</span>
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="faq-list">
            <details class="faq-item">
                <summary>What is dialysis?</summary>
                <p>Dialysis is a treatment that helps remove waste and extra fluid from the blood when the kidneys are not working properly.</p>
            </details>
            <details class="faq-item">
                <summary>How do I find a dialysis center?</summary>
                <p>Use the search area above to look for centers by city or location, then review the center information provided.</p>
            </details>
            <details class="faq-item">
                <summary>What information can I see about a center?</summary>
                <p>You can review location, operating hours, available services, and contact information.</p>
            </details>
            <details class="faq-item">
                <summary>Can I request an appointment through KATOC?</summary>
                <p>KATOC helps you find a center and request an appointment. The center will confirm availability and treatment details.</p>
            </details>
            <details class="faq-item">
                <summary>What are the different types of dialysis?</summary>
                <p>The two common types are hemodialysis and peritoneal dialysis. Your healthcare team can explain which option may be appropriate for you.</p>
            </details>
        </div>
    </section>

    <!-- =========================
         CTA / FOOTER SECTION
    ========================== -->
    <section class="cta-footer-wrapper">
        <img src="images/vessel-cells.png" alt="Blood vessel and cells" class="vessel-graphic">
        <img src="images/kidney-outline.png" alt="KATOC Kidney Logo" class="large-kidney-logo">

        <div class="cta-footer-card">
            <div class="footer-logo-container">
                <img src="images/katoc-logo2.png" alt="KATOC Logo" class="katoc-logo2">
            </div>

            <div class="footer-inner-card">
                <h2 class="cta-heading">YOUR CARE STARTS HERE</h2>
                <p class="cta-subtext">
                    Finding dialysis care doesn't have to be difficult. KATOC helps you discover dialysis centers, learn about their services, and request an appointment—all in one place.
                </p>

                <div class="footer-columns">
                    <div class="footer-col">
                        <span class="pill-badge">Find Us</span>
                        <ul class="footer-links">
                            <li><a href="#hero-home">Home</a></li>
                            <li><a href="#services">Our services</a></li>
                            <li><a href="about/about.php">About Us</a></li>
                            <li><a href="#appointment">Contact Us</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <span class="pill-badge">Contact Us</span>
                        <ul class="footer-info">
                            <li>Phone No.: 09192266069</li>
                            <li>Gmail: markdaveopena968@gmail.com</li>
                            <li>Facebook: KATOC PH</li>
                            <li class="highlight-text">Book for Appointment NOW</li>
                        </ul>
                    </div>
                </div>
            </div>

            <p class="footer-tagline">KATOC • Find. Book. Care.</p>
        </div>
    </section>

    <script src="script.js?v=20260908-booking-input-layer"></script>

</body>
</html>
