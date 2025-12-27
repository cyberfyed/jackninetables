<?php
require_once 'config/config.php';
require_once 'classes/User.php';

$success = false;
$error = '';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid verification link.';
} else {
    $db = new Database();
    $user = new User($db->connect());
    $result = $user->verifyEmail($token);

    if ($result['success']) {
        $success = true;
    } else {
        $error = $result['error'];
    }
}

$pageTitle = 'Verify Email';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <?php if ($success): ?>
                <div style="font-size: 3rem; margin-bottom: 1rem;">&#9827;</div>
                <h1>Email Verified!</h1>
                <p>Your email has been successfully verified.</p>
            <?php else: ?>
                <h1>Verification Failed</h1>
                <p>We couldn't verify your email address.</p>
            <?php endif; ?>
        </div>

        <div class="auth-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>You're all set!</strong><br>
                    You now have full access to all features including saving designs and requesting quotes.
                </div>
                <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-block btn-lg">Start Designing</a>
            <?php else: ?>
                <div class="alert alert-error">
                    <?= sanitize($error) ?>
                </div>
                <?php if (isLoggedIn()): ?>
                    <p style="text-align: center; margin-bottom: 1rem;">Need a new verification link?</p>
                    <a href="<?= SITE_URL ?>/resend-verification.php" class="btn btn-primary btn-block">Resend Verification Email</a>
                <?php else: ?>
                    <p style="text-align: center; margin-bottom: 1rem;">Please log in to request a new verification email.</p>
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary btn-block">Log In</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            <p><a href="<?= SITE_URL ?>">Return to Home</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
