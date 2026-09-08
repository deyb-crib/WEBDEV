<?php
require __DIR__ . '/signup_function.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up | KATOC</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-signup-page">
    <main class="auth-shell">
        <section class="auth-visual">
            <img src="../images/katoc-logo2.png" alt="KATOC" class="auth-logo">
            <img src="../images/kidney.png" alt="Kidney illustration" class="auth-kidney">
            <div class="auth-visual-content">
                <span class="auth-eyebrow">KIDNEY ACCESS AND TREATMENT OPERATION CENTER</span>
                <h1>Find care with confidence.</h1>
                <p>Create your KATOC account to save your care journey and request appointments more easily.</p>
            </div>
        </section>

        <section class="auth-form-panel">
            <div class="auth-form-wrap">
                <h2>Create your account</h2>
                <p class="auth-intro">Join KATOC and make dialysis care easier to reach.</p>

                <?php if ($errors): ?>
                    <div class="auth-error" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="signup.php">
                    <div class="form-field">
                        <label for="name">Full name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" autocomplete="name" minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÖØ-öø-ÿ .'-]+" required>
                    </div>
                    <div class="form-field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" maxlength="254" autocomplete="email" required>
                    </div>
                    <div class="form-field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <div class="form-field">
                        <label for="confirm_password">Confirm password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="auth-submit">Create account</button>
                </form>

                <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
                <a href="../index.php" class="auth-back">Back to homepage</a>
            </div>
        </section>
    </main>
</body>
</html>
