<?php
// KATOC landing page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>KATOC | Kidney Access and Treatment Operation Center</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- =========================
         KATOC HERO SECTION
    ========================== -->

    <section class="hero">

        <!-- NAVIGATION -->
        <nav class="navbar">

            <!-- KATOC ICON -->
            <a href="#" class="nav-logo">
                <img src="images/katoc-icon.png" alt="KATOC">
            </a>

            <!-- NAVIGATION LINKS -->
            <div class="nav-links">
                <a href="#">Home</a>
                <a href="#">Why KATOC</a>
                <a href="#">About</a>
            </div>

            <!-- LOGIN / SIGN UP -->
            <div class="nav-account">
                <a href="#" class="login">Log in</a>

                <a href="#" class="signup">
                    Sign up
                </a>
            </div>

        </nav>

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

    <section class="features-container">
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

    <section class="centers-section">
        <img src="images/blood-cells.png" alt="" class="bg-cells">

        <div class="centers-header">
            <h2 class="centers-title">FIND YOUR MOST SUITABLE PLACE, WE CARE FOR YOUR HEALTH</h2>
            <a href="#all-centers" class="view-all-btn">
                VIEW ALL <span>&rarr;</span>
            </a>
        </div>

        <div class="centers-grid">
            <div class="center-card">
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
                <a href="#availability" class="see-availability">
                    See Availability <span>&rarr;</span>
                </a>
            </div>

            <div class="center-card">
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
                <a href="#availability" class="see-availability">
                    See Availability <span>&rarr;</span>
                </a>
            </div>

            <div class="center-card">
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
                <a href="#availability" class="see-availability">
                    See Availability <span>&rarr;</span>
                </a>
            </div>
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
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Our services</a></li>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Contact Us</a></li>
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

    <script src="script.js"></script>

</body>
</html>
