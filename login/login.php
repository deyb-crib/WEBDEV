<?php
require __DIR__ . '/login_function.php';
$authMessage = $_SESSION['auth_message'] ?? '';
unset($_SESSION['auth_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | KATOC</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-login-page">
    <main class="auth-shell">
        <section class="auth-visual">
            <div class="auth-logo-stage">
                <img src="../images/katoc-logo2.png" alt="KATOC" class="auth-logo">
            </div>
            <div class="auth-visual-content">
                <span class="auth-eyebrow">KIDNEY ACCESS AND TREATMENT OPERATION CENTER</span>
                <h1>Your care starts here.</h1>
                <p>Sign in to find dialysis centers, review care information, and manage your appointment requests.</p>
            </div>
        </section>

        <section class="auth-form-panel">
            <div class="auth-form-wrap">
                <img src="../images/katoc-logo2.png" alt="KATOC" class="auth-form-logo-mark">
                <h2>Log in to KATOC</h2>
                <p class="auth-intro">Access your dialysis care information and appointment requests.</p>

                <?php if ($authMessage): ?>
                    <div class="auth-message" role="status">
                        <?= htmlspecialchars($authMessage, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="auth-error" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="login.php">
                    <div class="form-field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" autocomplete="current-password" required>
                        <a href="#" class="forgot-password">Forgot password?</a>
                        <label class="password-toggle" for="password-visibility">
                            <input type="checkbox" id="password-visibility">
                            <span>Show password</span>
                        </label>
                        <small id="password-status" class="password-status" role="status"></small>
                    </div>
                    <button type="submit" class="auth-submit">Get started</button>
                </form>

                <div class="social-divider"><span>or sign in with</span></div>
                <div class="social-actions">
                    <button type="button" class="social-button" aria-label="Sign in with Google">G</button>
                    <button type="button" class="social-button" aria-label="Sign in with Facebook">f</button>
                    <button type="button" class="social-button" aria-label="Sign in with Apple">&#63743;</button>
                </div>

                <p class="auth-switch">Don't have an account? <a href="signup.php">Sign up</a></p>
                <a href="../index.php" class="auth-back">Back to homepage</a>
            </div>
        </section>
    </main>
    <script src="auth.js"></script>
</body>
</html>
